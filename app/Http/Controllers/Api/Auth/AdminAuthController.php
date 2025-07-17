<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\Auth\AuthController;


class AdminAuthController extends AuthController
{
    protected function getUserType(): string
    {
        return 'admin';
    }
}
