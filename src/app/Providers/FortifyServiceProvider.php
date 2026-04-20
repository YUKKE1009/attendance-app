<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Http\Requests\LoginRequest;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Responses\LoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(LoginRequest::class, function ($app) {
            // スラッシュなしのURLにも対応
            if ($app->make('request')->is('admin*')) {
                return $app->make(\App\Http\Requests\AdminLoginRequest::class);
            }
            return new LoginRequest($app->make('request')->all());
        });

        $this->app->resolving(LoginRequest::class, function ($request, $app) {
            if ($app->make('request')->is('admin*')) {
                config(['fortify.guard' => 'admin']);
            }
        });
    }

    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        Fortify::registerView(fn() => view('auth.register'));

        Fortify::loginView(function () {
            return request()->is('admin*') ? view('admin.login') : view('auth.login');
        });

        // --- メール認証用のビューを指定 ---
        Fortify::verifyEmailView(function () {
            return view('auth.verify-email');
        });

        Fortify::authenticateUsing(function (LoginRequest $request) {
            if ($request->is('admin*') || config('fortify.guard') === 'admin') {
                $admin = Admin::where('email', $request->email)->first();
                if ($admin && Hash::check($request->password, $admin->password)) {
                    return $admin;
                }
                throw ValidationException::withMessages([
                    Fortify::username() => 'ログイン情報が登録されていません',
                ]);
            }

            $user = \App\Models\User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });

        // ログイン後のリダイレクト制御を有効化
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            LoginResponse::class
        );

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
