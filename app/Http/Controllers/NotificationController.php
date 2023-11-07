<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function sendSmsNotificaition()
    {
        $basic  = new \Vonage\Client\Credentials\Basic("0a2b65fa", "BdI7Z4463UWJp12W");
        $client = new \Vonage\Client($basic);
 
        $response = $client->sms()->send(
            new \Vonage\SMS\Message\SMS("9779814948102","9779746459220", 'A text message sent using the Nexmo SMS API')
        );
        $message = $response->current();
        if ($message->getStatus() == 0) {
            return response()->json([
                'message' => "Sucessful"
            ]);
        } else {
            return response()->json([
                'message' => "Failed"
            ]);
        }
    }
}