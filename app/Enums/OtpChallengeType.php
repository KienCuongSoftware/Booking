<?php

namespace App\Enums;

enum OtpChallengeType: string
{
    case Register = 'register';
    case PasswordChange = 'password_change';
}
