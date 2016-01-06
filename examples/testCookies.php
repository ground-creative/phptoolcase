<?php

class SignedCookie {
    private $prifix = '$x$';
    private $privateKey;

    function __construct($privateKey) {
        $this->privateKey = $privateKey;
    }

    function setCookie($name, $value, $expire, $path = null, $domain = null, $secure = null, $httponly = null) {
        $value = $value === null ? $value : $this->hash($value, mcrypt_create_iv(2, MCRYPT_DEV_URANDOM));
        return setcookie($name, $value, $expire, $path, $domain, $secure, $httponly);
    }

    function getCookie($name, $ignore = false) {
        if (! isset($_COOKIE[$name]) || empty($_COOKIE[$name]))
            return null; // does not exist

        if ($ignore === false) {
            if (substr($_COOKIE[$name], 0, 3) !==  $this->prifix)
                return - 1; // modified

            $data = pack("H*", substr($_COOKIE[$name], 3)); // Unpack hex

            $value = substr($data, 32, - 2); // Get Value
            $rand = substr($data, - 2, 2); // Get Random prifix

            if ($this->hash($value, $rand) !== $_COOKIE[$name])
                return - 1; // modified

            return $value;
        }
        return $_COOKIE[$name];
    }

    function hash($value, $suffix) {
        // Added random suffix to help the hash keep changing
        return $this->prifix . bin2hex(hash_hmac('sha256', $value . $suffix, $this->privateKey, true) . $value . $suffix);
    }
}

