<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class ClaimRejectionReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $ClaimReferenceNumber = null, $ClaimIntimationNumber = null, $CoverNoteReferenceNumber = null,  $RejectionDate = null, $RejectionReason = null,$ClaimResultedLitigation = null, $ClaimAmount = null, $CurrencyCode = "TZS", $ExchangeRate = 1.00,  $Claimants = array())
  {
    try
    {
        $endpoint     = Config::get('tira.endpoints.ClaimRejectionReq');
        $clientCode   = Config::get('tira.tiraclient.clientCode');
        $systemCode   = Config::get('tira.tiraclient.systemCode');
        $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

        $whoClaims = "";
        foreach ($Claimants as $key => $data)
        {
          $whoClaims .= "<".$key.">".$data."</".$key.">";
        }

        $content="<ClaimRejectionReq>
                    <ClaimRejectionHdr>
                    <RequestId>".$requestId."</RequestId>
                    <CompanyCode>".$clientCode."</CompanyCode>
                    <SystemCode>".$systemCode."</SystemCode>
                    <CallBackUrl>".$callBackUrl."</CallBackUrl>
                    <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                    </ClaimRejectionHdr>
                    <ClaimRejectionDtl>
                    <ClaimRejectionNumber>".$requestId."</ClaimRejectionNumber>
                    <ClaimReferenceNumber>".$ClaimReferenceNumber."</ClaimReferenceNumber>
                    <ClaimIntimationNumber>".$ClaimIntimationNumber."</ClaimIntimationNumber>
                    <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                    <RejectionDate>".$RejectionDate."</RejectionDate>
                    <RejectionReason>".$RejectionReason."</RejectionReason>
                    <ClaimResultedLitigation>".$ClaimResultedLitigation."</ClaimResultedLitigation>
                    <ClaimAmount>".$ClaimAmount."</ClaimAmount>
                    <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                    <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                    <Claimants><Claimant>".$whoClaims."</Claimant></Claimants>
                </ClaimRejectionDtl>
                </ClaimRejectionReq>";

                //Utils::saveLogs("ClaimRejectionReq", $content);
                return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
            }
            catch(Exception $e)
            {
                Utils::saveLogs("ClaimRejectionReqReqException", $e->getMessage());
                return $e->getMessage();
            }
        }
}
