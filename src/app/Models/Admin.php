<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = ['email', 'password'];
    // 管理者は単体で動くことが多いですが、認証用モデルとして機能させます
}
