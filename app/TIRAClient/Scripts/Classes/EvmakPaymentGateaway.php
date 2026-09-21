<?php
namespace App\TIRAClient\Scripts\Classes;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;

class EvmakPaymentGateaway
{
    public static function pay($refNo, $amount, $payerMssdn, $mnoType = "Mpesa",  $product)
    {
            try
            {
                    $user               = Config::get('payments.evmak.user');
                    $api_source         = Config::get('payments.evmak.api_source');
                    $callback           = Config::get('payments.evmak.callback');
                    $hash               = Config::get('payments.evmak.hash');

                    $newtwork           = $mnoType == "TigoPesa" ? Config::get('payments.evmak.mno_types.tigopesa') : Config::get('payments.evmak.mno_types.mpesa');

                    $message = array(
                        "api_source"     => $api_source,
                        "api_to"         => $newtwork['code'],
                        "amount"         => floatval($amount),
                        "product"        => $product,
                        "callback"       => $callback,
                        "hash"           => $hash,
                        "user"           => $user,
                        "mobileNo"       => $payerMssdn,
                        "reference"      => $refNo
                    );
                    $data_string = json_encode($message);
                    $ch = curl_init($newtwork['endpoint']);
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt(
                        $ch,
                        CURLOPT_HTTPHEADER,
                        array(
                            'Content-Type: application/json',
                            'Content-Length: ' . strlen($data_string)
                        )
                    );

                $response = curl_exec($ch);

                Utils::saveLogs("SUCCESS_EVMAK_PAYMENT_RESPONSE", $response."\n PAYLOAD :\n".json_encode($message));

                return $response;
        }
        catch(Exception $e)
        {
            Utils::saveLogs("FAILURE_EVMAK_PAYMENT_RESPONSE", $e->getMessage());
            return $e->getMessage();
        }
    }
}
