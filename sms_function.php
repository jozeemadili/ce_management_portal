<?php
public static function internet_sms($phone_number, $message,$user_id,$sendername='CETZ')
{
//$sms_balance = DB::table('sms_balance')->where('user_id', $user_id)->first();
// $sms_balance = Sms_record::where("user_id", $user_id)->sum(DB::raw('bought - spent'));

// $credit_balance=isset($sms_balance) ? $sms_balance:0;
// if($sms_balance < 0 ){

// Log::info('You do not have enough credit balance');
// $sms_response= array(
// 'code' => 00,
// 'request_id' => 0,
// 'message' => 'You do not have enough credit balance'
// );
// return $sms_response;
// }

// if ($phone_number != '' && config('bahari.sms_gw.enabled') == 'YES') {

//$phone = str_replace('+', "", $phone_number);
$phone=preg_replace('/^(?:\+?255|0)?/','255', $phone_number);

$secret_key = config('bahari.sms_gw.secret');
$api_key = config('bahari.sms_gw.key');
// The data to send to the API
$posthData = array(
'source_addr' => $sendername,
'encoding' => 0,
'schedule_time' => '',
'message' => $message,
'recipients' => [array('recipient_id' => '1', 'dest_addr' => $phone)]
);

//.... Api url
$Url = config('bahari.sms_gw.gateway_url');

// Setup cURL
$ch = curl_init($Url);
error_reporting(E_ALL);
ini_set('display_errors', 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt_array($ch, array(
CURLOPT_POST => TRUE,
CURLOPT_RETURNTRANSFER => TRUE,
CURLOPT_HTTPHEADER => array(
'Authorization:Basic ' . base64_encode("$api_key:$secret_key"),
'Content-Type: application/json'
),
CURLOPT_POSTFIELDS => json_encode($posthData)
));

// Send the request
$response = curl_exec($ch);
if ($response === FALSE) {
die(curl_error($ch));
}

$sms_response=json_decode($response,true);
//Log::info($sms_response);




} else {
$sms_response= array(
'code' => 0,
'request_id' => 0,
'message' => 'Phone number not valid'
);

}
return $sms_response;

