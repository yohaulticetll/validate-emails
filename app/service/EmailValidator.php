<?php

namespace App\Service;

/**
 * Class EmailValidator
 * @package App\Service
 */
class EmailValidator
{
    /**
     * @param string $email
     * @return bool
     */
    public function emailIsValid(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $email)) {
            return false;
        }

        $domain = explode('@', $email);
        if (!isset($domain[1]) || sizeof($domain) > 2) {
            return false;
        }

        if (!checkdnsrr($domain[1], 'MX')) {
            return false;
        }

        return true;
    }
}