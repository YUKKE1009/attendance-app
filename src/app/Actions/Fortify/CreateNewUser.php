<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * バリデーションして、新しく登録されたユーザーを作成する
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        // 1. RegisterRequest インスタンスを作成
        $request = new RegisterRequest();

        // 2. RegisterRequest で定義したルールとメッセージを使ってバリデーション実行
        // これで設計書の「FN002」「FN003」を同時に満たせます
        Validator::make($input, $request->rules(), $request->messages())->validate();

        // 3. ユーザー作成
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
