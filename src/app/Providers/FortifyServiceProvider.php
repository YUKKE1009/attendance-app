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

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // 管理者URLの時だけ、独自のバリデーションRequestを使うように指示
        $this->app->bind(\Laravel\Fortify\Http\Requests\LoginRequest::class, function ($app) {
            if ($app->make('request')->is('admin/*')) {
                return $app->make(\App\Http\Requests\AdminLoginRequest::class);
            }
            return new \Laravel\Fortify\Http\Requests\LoginRequest($app->make('request')->all());
        });

        // ログイン試行時のガード切り替え
        $this->app->resolving(\Laravel\Fortify\Http\Requests\LoginRequest::class, function ($request, $app) {
            if ($app->make('request')->is('admin/*')) {
                config(['fortify.guard' => 'admin']);
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // 会員登録画面
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // ログイン画面
        Fortify::loginView(function () {
            if (request()->is('admin/*')) {
                return view('admin.login');
            }
            return view('auth.login');
        });

        // 管理者用の認証ロジックを追加 (FN016対応)
        Fortify::authenticateUsing(function (LoginRequest $request) {
            if ($request->is('admin/*')) {
                $admin = Admin::where('email', $request->email)->first();

                if ($admin && Hash::check($request->password, $admin->password)) {
                    return $admin;
                }

                throw ValidationException::withMessages([
                    Fortify::username() => 'ログイン情報が登録されていません',
                ]);
            }

            // 一般ユーザー用の認証
            $user = \App\Models\User::where('email', $request->email)->first();
            if ($user && Hash::check($request->password, $user->password)) {
                return $user;
            }
            return null;
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())) . '|' . $request->ip());
            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });
    }
}
