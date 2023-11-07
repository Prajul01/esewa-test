<?php

namespace App\Services;

use App\Models\destinations;

use GuzzleHttp\Client;
use DOMDocument;
use DOMXPath;
use Sunra\PhpSimple\HtmlDomParser;
use Ramsey\Uuid\Uuid;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

class PaymentService
{



    public function esewaPayment($ticket_details)
    {
        try {
            $transaction_uuid = Uuid::uuid4()->toString();
            $secretKey = '8gBm/:&EnhH.1/q';
            $total_amount = 100;
            $product_code = 'EPAYTEST';

            $dataToSign = "total_amount=$total_amount,transaction_uuid=$transaction_uuid,product_code=$product_code";
            $signature = hash_hmac('sha256', $dataToSign, $secretKey, true);
            $encodedSignature = base64_encode($signature);
            $data = [
                'amount' => $total_amount,
                'tax_amount' => 0,
                'total_amount' => $total_amount,
                'transaction_uuid' => $transaction_uuid,
                'product_code' => $product_code,
                'product_service_charge' => 0,
                'product_delivery_charge' => 0,
                'success_url' => 'https://esewa.com.np',
                'failure_url' => 'https://google.com',
                'signed_field_names' => 'total_amount,transaction_uuid,product_code',
                'signature' => $encodedSignature,
            ];
            $ch = curl_init();
            if ($ch === false) {
                dd('cURL initialization failed');
            }
            curl_setopt($ch, CURLOPT_URL, "https://rc-epay.esewa.com.np/api/epay/main/v2/form");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
            $response = curl_exec($ch);
            curl_close($ch);
            if ($response === false) {
                return  'respnse is false';
            }
        else {
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode === 200) {
                echo "HTTP Status Code: " . $httpCode . "\n";
                echo "Response Body: " . $response;
            } else {
                dd($httpCode);
            }
            }

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



}
