<?php
namespace App\TIRAClient\Scripts\Classes;

use App\Models\CONFIGURATION;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
class EsbClient
{
    public static function SendesbRequest($body,$endPoint)
    {
        // dd($endPoint,$body);
       try
       {
        $client = new Client();
        $Bearer  = session()->get('accessLogToken');
        $client = new Client(['verify' => false]);
        $headers = ['Content-Type' => 'application/json','Authorization' => 'Bearer '.$Bearer.''];
        $request = new Request('POST', $endPoint, $headers, $body);
        $res = $client->sendAsync($request)->wait();
        $result = [
            'status_code' => $res->getStatusCode(),
            'body' => json_decode($res->getBody()->getContents()),
        ];
        $resultJson = json_encode($res);
        return $result;
        
       } 
       catch (RequestException $e) {
        // To catch exactly error 401 use 
        $result = [
            'status_code' => $e->getResponse()->getStatusCode(),
            'body' => json_decode($e->getResponse()->getBody()->getContents()),
        ];
        return $result;
       }
       catch(Exception $e)
       {
           Utils::saveLogs("FAILURE_LOGIN_RESPONSE", $e->getMessage());
           return $e->getMessage();
       } 
     }   
     public static function removeAfterFirstSlash($url) {
        // Parse the URL to get its components
        $parsedUrl = parse_url($url);
    
        // Reconstruct the URL up to the path
        $resultUrl = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    
        // Include port if it's present
        if (isset($parsedUrl['port'])) {
            $resultUrl .= ':' . $parsedUrl['port'];
        }
    
        return $resultUrl;
    }
    public static function SendesbRequestbodyAcc($body, $endPoint)
    {
        try 
        {
            $client = new Client([
                'verify' => false // ⚠️ This disables SSL cert verification
            ]);
            
            $headers = [
                'Content-Type' => 'application/json'
            ];
        
            // If $body is an array, encode it
            if (is_array($body)) {
                $body = json_encode($body);
            }
            $request = new Request('POST', $endPoint, $headers, $body);
            $res = $client->sendAsync($request)->wait();
            // dd($res->getBody()->getContents());

            return $res->getBody()->getContents(); // ✅ Return actual response body string
           
        } 
        catch (RequestException $e) 
        {
            $data = [
                'message' => $e->getMessage(),
                'request' => (string) $e->getRequest()->getBody(),
                'response' => optional($e->getResponse())->getBody()->getContents(),
            ];
            // dd($data);
            \Log::error('Guzzle Request Failed', $data);
            
            // dd($data);
        }

        
    }
     public static function SendesbRequesturl($endPoint)
    {  
            
            $base_url= CONFIGURATION::find(1);
            $path_url=$base_url->BASE_URL.''.$endPoint;
            $base_uri=self::removeAfterFirstSlash($base_url->BASE_URL);
            // dd($path_url);
            $client = new Client([
                'base_uri' => $base_uri.'/',
                'verify' => false, // Disable SSL certificate verification
            ]);
            $request = new Request('POST', $path_url);
            $res = $client->sendAsync($request)->wait();
            // $result= $res->getBody();
            $result=json_decode($res->getBody()->getContents());
            // dd($result);
            return $result;
       
     } 
     public static function SendesbRequestbody($body,$endPoint)
     {
             $base_url= CONFIGURATION::find(1);
             $path_url=$base_url->BASE_URL.''.$endPoint;
             $headers = ['Content-Type' => 'application/json'];
             $base_uri=self::removeAfterFirstSlash($base_url->BASE_URL);
                $client = new Client([
                    'base_uri' => $base_uri.'/',
                    'verify' => false, // Disable SSL certificate verification
                ]);
             $request = new Request('POST', $path_url, $headers, $body);
             $res = $client->sendAsync($request)->wait();
             // $result= $res->getBody();
             $result=json_decode($res->getBody()->getContents());
            //  dd($result);
             return $result;
        
      }  
     public static function sendEsbRequestget($endPoint)
    {
       try
       {
       // $endPoint='http://172.16.3.3:30008/api/v1/users?page=0&size=10';
        $client = new Client();
        $Bearer  = session()->get('accessLogToken');
        $client = new Client(['verify' => false]);
        $headers = ['Authorization' => 'Bearer '.$Bearer.''];

        $request = new Request('GET', $endPoint, $headers);
        $res = $client->sendAsync($request)->wait();
        $result = [
            'status_code' => $res->getStatusCode(),
            'body' => json_decode($res->getBody()->getContents()),
        ];
        $resultJson = json_encode($result);
        // dd($result);
        return $result;
        
       } 
       catch (RequestException $e) {
        // To catch exactly error 401 use 
        $result = [
            'status_code' => $e->getResponse()->getStatusCode(),
            'body' => json_decode($e->getResponse()->getBody()->getContents()),
        ];
        return $result;
       }
       
       catch(Exception $e)
       {
           Utils::saveLogs("FAILURE_LOGIN_RESPONSE", $e->getMessage());
           return $e->getMessage();
       } 
     }     
}
?>
