<?php

namespace ntentan\middleware\auth;

/**
 * The authentication user
 */
interface AuthUserModel
{
    function getPassword(string $username): string;
    function getSessionData(string $username): array;
}