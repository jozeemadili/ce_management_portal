<?php
namespace App\TIRAClient\Scripts\Classes;

use Illuminate\Support\Facades\Config;

class MotorVerificationReq
{
  public static function submit($MotorCategory = "1", $MotorRegistrationNumber = null, $MotorChassisNumber = null)
  {
    $endpoint     = Config::get('tira.endpoints.MotorVerificationReq');
    $requestId    = date("YmdHis");
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');

    $content="<MotorVerificationReq>
                <VerificationHdr>
                  <RequestId>".$requestId."</RequestId>
                  <CompanyCode>".$clientCode."</CompanyCode>
                  <SystemCode>".$systemCode."</SystemCode>
                </VerificationHdr>
                <VerificationDtl>
                  <MotorCategory>".$MotorCategory."</MotorCategory>
                  <MotorRegistrationNumber>".$MotorRegistrationNumber."</MotorRegistrationNumber>
                  <MotorChassisNumber>".$MotorChassisNumber."</MotorChassisNumber>
                </VerificationDtl>
                </MotorVerificationReq>";

      return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
  }
}
