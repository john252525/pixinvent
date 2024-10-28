<?php

if (! function_exists('phone_format')) {

    function phone_format($phone): ?int
    {
        $phone = trim($phone);
        $phone = (strlen($phone) === 10) ? "7$phone" : $phone;

        $result = preg_replace(
            [
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{3})[-|\s]?(\d{3})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{2})[-|\s]?(\d{2})[-|\s]?(\d{2})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{4})[-|\s]?\)[-|\s]?(\d{3})[-|\s]?(\d{3})/',
                '/[\+]?([7|8])[-|\s]?(\d{4})[-|\s]?(\d{3})[-|\s]?(\d{3})/',
                '/[\+]?([7|8])[-|\s]?\([-|\s]?(\d{3})[-|\s]?\)[-|\s]?(\d{4})[-|\s]?(\d{3})/',
            ],
            [
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4$5',
                '7$2$3$4',
                '7$2$3$4',
                '7$2$3$4',
            ],
            $phone
        );

        return preg_match('/\d{11}/', $result) ? $result : null;
    }
}

if (! function_exists('objectToArray')) {

    function objectToArray($obj)
    {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }
        if (is_array($obj)) {
            return array_map('objectToArray', $obj);
        } else {
            return $obj;
        }
    }
}

if (! function_exists('get_money')) {

    function get_money($amount, $currency = 'RUB')
    {
        return $amount / 100;
    }
}

if (! function_exists('put_money')) {

    function put_money($amount, $currency = 'RUB')
    {
        return $amount * 100;
    }
}

if (! function_exists('mail_from_domain')) {

    function mail_from_domain(): string {
        $data = config('email_by_domain');
        $domain = request()->host();

        if (array_key_exists($domain, $data)) {
            return $data[$domain];
        }

        throw new Exception('Domain doesnt exist');
    }
}
