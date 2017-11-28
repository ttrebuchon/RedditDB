<?php


class SiteUser
{
    public static function ValidUsername($name)
    {
        //Check if alphanumeric only
        if (!ctype_alnum($name))
        {
            return false;
        }


        return true;
    }

    public static function HashPassword($password)
    {
        if (!function_exists('crypt'))
        {
            trigger_error("Crypt must be loaded for password_hash to function", E_USER_WARNING);
            return null;
        }

        $cost = 10;
        $raw_salt_len = 16;
        $required_salt_len = 22;
        $hash_format = sprintf("$2y$%02d$", $cost);

        $salt = str_replace('+', '.', base64_encode(SiteUser::generate_entropy($required_salt_len)));
        $salt = substr($salt, 0, $required_salt_len);
        $hash = $hash_format . $salt;

        $ret = crypt($password, $hash);
        if (!is_string($ret) || strlen($ret) <= 13) {
            return false;
        }
        return $ret;
    }

    private static function generate_entropy($bytes)
    {
        $buffer = '';
        $buffer_valid = false;
        if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
            $buffer = mcrypt_create_iv($bytes, MCRYPT_DEV_URANDOM);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
            $buffer = openssl_random_pseudo_bytes($bytes);
            if ($buffer) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid && is_readable('/dev/urandom')) {
            $f = fopen('/dev/urandom', 'r');
            $read = strlen($buffer);
            while ($read < $bytes) {
                $buffer .= fread($f, $bytes - $read);
                $read = strlen($buffer);
            }
            fclose($f);
            if ($read >= $bytes) {
                $buffer_valid = true;
            }
        }
        if (!$buffer_valid || strlen($buffer) < $bytes) {
            $bl = strlen($buffer);
            for ($i = 0; $i < $bytes; $i++) {
                if ($i < $bl) {
                    $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
                } else {
                    $buffer .= chr(mt_rand(0, 255));
                }
            }
        }
        return $buffer;
    }
}

?>