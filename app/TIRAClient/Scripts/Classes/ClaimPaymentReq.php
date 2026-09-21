<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class ClaimPaymentReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $ClaimReferenceNumber = null, $ClaimIntimationNumber = null, $CoverNoteReferenceNumber = null,  $PaymentDate = null, $PaidAmount = null,$PaymentMode = null, $PartiesNotified = "Y", $NetPremiumEarned = null, $ClaimResultedLitigation = null, $LitigationReason = null, $CurrencyCode = "TZS", $ExchangeRate = 1.00,  $Claimants = array())
  {
    try
    {

            $endpoint     = Config::get('tira.endpoints.ClaimPaymentReq');
            $clientCode   = Config::get('tira.tiraclient.clientCode');
            $systemCode   = Config::get('tira.tiraclient.systemCode');
            $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

            $whoClaims = "";
            foreach ($Claimants as $key => $data)
            {
              $whoClaims .= "<".$key.">".$data."</".$key.">";
            }

            $content="<ClaimPaymentReq>
                        <ClaimPaymentHdr>
                        <RequestId>".$requestId."</RequestId>
                        <CompanyCode>".$clientCode."</CompanyCode>
                        <SystemCode>".$systemCode."</SystemCode>
                        <CallBackUrl>".$callBackUrl."</CallBackUrl>
                        <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                        </ClaimPaymentHdr>
                        <ClaimPaymentDtl>
                        <ClaimPaymentNumber>".$requestId."</ClaimPaymentNumber>
                        <ClaimReferenceNumber>".$ClaimReferenceNumber."</ClaimReferenceNumber>
                        <ClaimIntimationNumber>".$ClaimIntimationNumber."</ClaimIntimationNumber>
                        <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                        <PaymentDate>".$PaymentDate."</PaymentDate>
                        <PaidAmount>".$PaidAmount."</PaidAmount>
                        <PaymentMode>".$PaymentMode."</PaymentMode>
                        <PartiesNotified>".$PartiesNotified."</PartiesNotified>
                        <NetPremiumEarned>".$NetPremiumEarned."</NetPremiumEarned>
                        <ClaimResultedLitigation>".$ClaimResultedLitigation."</ClaimResultedLitigation>
                        <LitigationReason>".$LitigationReason."</LitigationReason>
                        <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                        <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                        <Claimants><Claimant>".$whoClaims."</Claimant></Claimants>
                    </ClaimPaymentDtl>
                    </ClaimPaymentReq>";

            //Utils::saveLogs("ClaimPaymentReq", $content);
            return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
        }
        catch(Exception $e)
        {
            Utils::saveLogs("ClaimPaymentReqReqException", $e->getMessage());
            return $e->getMessage();
        }
    }
}
