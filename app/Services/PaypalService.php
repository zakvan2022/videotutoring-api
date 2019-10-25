<?php

namespace App\Services;

use Illuminate\Http\Request;
use GuzzleHttp;

use Exception;

class PaypalService
{
    static $accessToken = null;

    /**
     * set AccessToken
     */
    public static function setToken() {
        $client = new \GuzzleHttp\Client();
        
        $url = "https://api.sandbox.paypal.com/v1/oauth2/token";
        $auth=[
            env("PAYPAL_SANDBOX_CLIENT_ID", "AV1FuVQ7rXk9z1rasquizO24nllNFjP9cTWLnLFzdSTBpCS5jPGIH_fPy7UnW_AODzqhrN_ExIvb2rcQ"),
            env("PAYPAL_SANDBOX_SECRET", "EIy7paj8hDboj-EEkEQh2icpCfAPKGsoR12pVtTl9NGG8lkXFbPkC2GB9neo8cDuJCbNTwPiZGFJU8mw")
        ];
        $form_params['grant_type'] = "client_credentials";
    
        $response = $client->post($url,  ['auth'=>$auth, 'form_params'=>$form_params]);
        $response = json_decode($response->getBody(), true);
        $tokenType = $response["token_type"];
        $accessToken = $response["access_token"];
        self::$accessToken = "{$tokenType} {$accessToken}";
    }
    
    /**
     * get customer information
     * @param {string} email
     * @param {number} amount
     * @param {string} description
     * @return {customer object} 
     */
    public function withdraw($email, $amount, $description) {
        $client = new \GuzzleHttp\Client();
        $url = "https://api.sandbox.paypal.com/v1/payments/payouts";
        $time = time();
        $sender_batch_id = "VideoTutoring_Payouts_{$time}";
        $param = [
            'sender_batch_header' => [
                'sender_batch_id' => $sender_batch_id,
                "email_subject"=> "You have a payout!",
                "email_message"=> $description
            ],
            'items' => [
                [
                    'recipient_type' => 'EMAIL',
                    'amount' => [
                        'value' => $amount,
                        'currency' => 'USD'
                    ],
                    'note' => $description,
                    'sender_item_id' => $time,
                    'receiver'=>$email
                ]
            ]
        ];

        $data = json_encode($param);

        try {
            $response = $client->post($url,  [
                'headers'=>[
                    'Authorization'=> self::$accessToken,
                    'content-type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'body' => $data,
            ]);
            $response = json_decode($response->getBody(), true);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $message = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new Exception($message['details'][0]['issue']);
        }
        return  $response;
    }

    private function setHeaders()
    {
        $headers = [
            'X-PAYPAL-SECURITY-USERID'      => env('PAYPAL_SANDBOX_USERID', 'sb-hxvlp232243_api1.business.example.com'),
            'X-PAYPAL-SECURITY-PASSWORD'    => env('PAYPAL_SANDBOX_PASSWORD', 'MPM4LLET27S8A8DY'),
            'X-PAYPAL-SECURITY-SIGNATURE'   => env('PAYPAL_SANDBOX_SIGNATURE', 'A4YjEWGDpXRVIkz7E.s5.MVcnZK5ADyo5EBrkp84CpTRRIvj3TnacRFk'),
            'X-PAYPAL-REQUEST-DATA-FORMAT'  => 'JSON',
            'X-PAYPAL-RESPONSE-DATA-FORMAT' => 'JSON',
            'X-PAYPAL-APPLICATION-ID'       => env('PAYPAL-SANDBOX-ID', 'APP-80W284485P519543T')
        ];
        return $headers;
    }

    /**
     * send PreApproval Request with @email
     * @param {string} senderEmail
     * @param {maxAmoutPerPayment} 
     * @param {maxNumberOfPayments} 
     * @param {maxTotalAmountOfAllPayments} 
     * @param {currencyCode} 
     * @param {startingDate} 
     * @param {endingDate} 
     * @param {returnUrl} 
     * @param {cancelUrl} 
     * @param {pinType}
     * @param {errorLanguage}
     * @return {string} redirect_url for approval from user
     */
    public function requestPreApproval(
        $senderEmail, 
        $startingDate,
        $endingDate,
        $maxAmountPerPayment = '200.00',
        $maxNumberOfPayments = '30',
        $maxTotalAmountOfAllPayments = '1500',
        $currencyCode = 'USD',
        $returnUrl = 'https://example.com/success',
        $cancelUrl = 'https://example.com/cancel',
        $pinType = 'NOT_REQUIRED',
        $errorLanguage = 'en_US'
    ) {
        $body = [
            'senderEmail' => $senderEmail,
            'maxAmountPerPayment' => $maxAmountPerPayment,
            'maxNumberOfPayments' => $maxNumberOfPayments,
            'maxTotalAmountOfAllPayments' => $maxTotalAmountOfAllPayments,
            'currencyCode' => $currencyCode,
            'startingDate' => $startingDate,
            'endingDate' => $endingDate,
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
            'pinType' => $pinType,
            'requestEnvelope' => [
                'errorLanguage' => $errorLanguage
            ]
        ];
        $body = json_encode($body);
        
        $client = new \GuzzleHttp\Client();
        $url = "https://svcs.sandbox.paypal.com/AdaptivePayments/Preapproval";
        try {
            $response = $client->post($url,  [
                'headers' => $this->setHeaders(),
                'body' => $body
            ]);
            $response = json_decode($response->getBody()->getContents(), true);

            if ($response['responseEnvelope']['ack'] === 'Success')
                return $response['preapprovalKey'];
            else
                throw new Exception($response['error'][0]['message']);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $message = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new Exception($message['details'][0]['issue']);
        }
        return  $response;
    }

    /**
     * Get Information About PreApproval
     * @param {string} preapprovalKey
     * @return {object}
     */
    public function getPreApprovalDetails($preapprovalKey) {
        $body = [
            'preapprovalKey' => $preapprovalKey,
            'requestEnvelope' => [
                'errorLanguage' => 'en_US'
            ]
        ];
        $body = json_encode($body);

        $client = new \GuzzleHttp\Client();
        $url = "https://svcs.sandbox.paypal.com/AdaptivePayments/PreapprovalDetails";
        try {
            $response = $client->post($url,  [
                'headers' => $this->setHeaders(),
                'body' => $body
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $message = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new Exception($message['details'][0]['issue']);
        }
        return  $response;
    }

    public function executeAdaptivePay(
        $senderEmail,
        $receiverEmail,
        $preapprovalKey,
        $amount,
        $returnUrl = 'https://example.com/success',
        $cancelUrl = 'https://example.com/cancel',
        $actionType = 'PAY',
        $currencyCode = 'USD',
        $errorLanguage = 'en_US'
    ) {
        $body = [
            'senderEmail' => $senderEmail,
            'preapprovalKey' => $preapprovalKey,
            'receiverList' => [
                'receiver' => [
                    [
                        'amount' => $amount,
                        'email' => $receiverEmail
                    ]
                ]
            ],
            'returnUrl' => $returnUrl,
            'cancelUrl' => $cancelUrl,
            'actionType' => $actionType,
            'currencyCode' => $currencyCode,
            'requestEnvelope' => [
                'errorLanguage' => $errorLanguage
            ]
        ];
        $body = json_encode($body);

        $client = new \GuzzleHttp\Client();
        $url = "https://svcs.sandbox.paypal.com/AdaptivePayments/Pay";
        
        try {
            $response = $client->post($url,  [
                'headers' => $this->setHeaders(),
                'body' => $body
            ]);
            $response = json_decode($response->getBody()->getContents(), true);
            if ($response['responseEnvelope']['ack'] === 'Success')
                return $response;
            else
                throw new Exception($response['error'][0]['message']);
        } catch (GuzzleHttp\Exception\ClientException $e) {
            $message = json_decode($e->getResponse()->getBody()->getContents(), true);
            throw new Exception($message['details'][0]['issue']);
        }
        return  $response;
    }
}