<?php
namespace App\TIRAClient\Scripts\Classes;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

use Illuminate\Support\Facades\Config;
class TIRAClient
{
    public static function sendRequest($content, $endPoint)
    {
        $certPassword   = Config::get('tira.tiraclient.certPassword');
        $certPath       = Config::get('tira.tiraclient.certPath');
        $clientCode     = Config::get('tira.tiraclient.clientCode');
        $clientKey      = Config::get('tira.tiraclient.clientKey');

        if (!$cert_store = file_get_contents($certPath))
        {
            return "Error: Unable to read the certifiate file\n";
        }
        else
        {
            if (openssl_pkcs12_read($cert_store, $cert_info, $certPassword))
            {
                openssl_sign($content, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");
                $signature = base64_encode($signature);
                $data = "<TiraMsg>".$content."<MsgSignature>".$signature."</MsgSignature></TiraMsg>";

                Utils::saveLogs("TiraRequestBody", $data);

                $client = new Client();
                $headers = ['ClientCode' => $clientCode, 'ClientKey' => $clientKey, 'Content-Type' => 'application/xml', 'Content-Length' =>strlen($data)];
                $request = new Request('POST', $endPoint, $headers, $data);
                $res = $client->sendAsync($request)->wait();
                return $res->getBody();
            }
            else
            {
                Utils::saveLogs("openssl_error_string", openssl_error_string());
                return "Error: Unable to read the cert store";
            }
        }
    }
    public static function sendEsbRequest($body,$endPoint)
    {
    
        $Bearer  = 'eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxIiwiaWF0IjoxNzAxNDQxNTE2LCJleHAiOjE3MDE0NDMzMTYsImlzcyI6IkVmcmlzIEdhdGV3YXkifQ.hiNHJj0EcHywA8fEYp9lvnB51fYATM321pqIotnh8oSavR87vy0ehyLJjRWxJ0vyA2B4yPkSEQFZis8zCIMVxw';
        $client = new Client(['verify' => false]);
        $headers = ['Content-Type' => 'application/json','Authorization' => 'Bearer '.$Bearer.''];
        $request = new Request('POST', $endPoint, $headers, $body);
        $res = $client->sendAsync($request)->wait();
        return $res->getBody();
    }
    
    public static function getMsgSignature($content)
    {
        $certPassword   = Config::get('tira.tiraclient.certPassword');
        $certPath       = Config::get('tira.tiraclient.certPath');
        if (!$cert_store = file_get_contents($certPath))
        {
            return "Error: Unable to read the certifiate file\n";
        }
        else
        {
            if (openssl_pkcs12_read($cert_store, $cert_info, $certPassword))
            {
                openssl_sign($content, $signature, $cert_info['pkey'], "sha1WithRSAEncryption");
                $signature = base64_encode($signature);
                return "<MsgSignature>".$signature."</MsgSignature>";
            }
            else
            {
                Utils::saveLogs("openssl_error_string", openssl_error_string());
                return "Error: Unable to read the cert store";
            }
        }
    }

    function getDataString($inputstr,$datatag)
    {
        $datastartpos = strpos($inputstr, $datatag);
        $dataendpos = strrpos($inputstr, $datatag);
        $data=substr($inputstr,$datastartpos - 1,$dataendpos + strlen($datatag)+2 - $datastartpos);
        return $data;
    }

    function getSignatureString($inputstr,$sigtag)
    {
        $sigstartpos = strpos($inputstr, $sigtag);
        $sigendpos = strrpos($inputstr, $sigtag);
        $signature=substr($inputstr,$sigstartpos + strlen($sigtag)+1,$sigendpos - $sigstartpos -strlen($sigtag)-3);
        return $signature;
    }


}
?>
