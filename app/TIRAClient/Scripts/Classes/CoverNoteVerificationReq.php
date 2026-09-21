<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class CoverNoteVerificationReq
{
  public static function submit($CoverNoteReferenceNumber = null, $StickerNumber = null, $MotorRegistrationNumber = null, $MotorChassisNumber = null)
  {
    libxml_use_internal_errors(true);
    try{
    $endpoint     = Config::get('tira.endpoints.CoverNoteVerificationReq');
    $requestId    = date("YmdHis");
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');

    $content="<CoverNoteVerificationReq>
                <VerificationHdr>
                <RequestId>".$requestId."</RequestId>
                <CompanyCode>".$clientCode."</CompanyCode>
                <SystemCode>".$systemCode."</SystemCode>
                </VerificationHdr>
                <VerificationDtl>
                <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                <StickerNumber>".$StickerNumber."</StickerNumber>
                <MotorRegistrationNumber>".$MotorRegistrationNumber."</MotorRegistrationNumber>
                <MotorChassisNumber>".$MotorChassisNumber."</MotorChassisNumber>
                </VerificationDtl>
            </CoverNoteVerificationReq>";

      $response = json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint)));
      return $response;
    }
    catch(Exception $e)
    {
        Utils::saveLogs("CoverNoteVerificationReqException", $e->getMessage());
        return $e->getMessage();
    }
  }
}
