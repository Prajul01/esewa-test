<?php

namespace App\Services;

use GuzzleHttp\Client;

class SMS
{
    public function sendSMS($number, $message)
    {
        $client = new Client([
            'timeout' => 7
        ]);
        $res = $client->request('GET', 'url', [
            'query' => [
                'username' => 'username',
                'password' => 'password',
                'mobileNumber' => $number,
                'message' => $message
            ]
        ]);
        return $res;
    }

    public function isValidNumber($number)
    {
        $check = substr($number, 0, 3);
        if ($check == '984' || $check == '985' || $check == '986') {
            return true;
        }
        return false;
    }
}
