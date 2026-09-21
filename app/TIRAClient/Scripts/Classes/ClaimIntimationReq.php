<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class ClaimIntimationReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $ClaimReferenceNumber = null, $CoverNoteReferenceNumber = null, $ClaimIntimationDate = null, $CurrencyCode = "TZS", $ExchangeRate = 1.00, $ClaimEstimatedAmount = null, $ClaimReserveAmount = null, $ClaimReserveMethod = null, $LossAssessmentOption = null, $AssessorName = null, $AssessorIdNumber = null, $AssessorIdType = null, $Claimants = array())
  {
    try
    {
        $endpoint     = Config::get('tira.endpoints.ClaimIntimationReq');
        $clientCode   = Config::get('tira.tiraclient.clientCode');
        $systemCode   = Config::get('tira.tiraclient.systemCode');
        $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

        $whoClaims = "";
        foreach ($Claimants as $key => $data)
        {
          $whoClaims .= "<".$key.">".$data."</".$key.">";
        }

        $content="<ClaimIntimationReq>
                    <ClaimIntimationHdr>
                    <RequestId>".$requestId."</RequestId>
                    <CompanyCode>".$clientCode."</CompanyCode>
                    <SystemCode>".$systemCode."</SystemCode>
                    <CallBackUrl>".$callBackUrl."</CallBackUrl>
                    <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                  </ClaimIntimationHdr>
                  <ClaimIntimationDtl>
                    <ClaimIntimationNumber>".$requestId."</ClaimIntimationNumber>
                    <ClaimReferenceNumber>".$ClaimReferenceNumber."</ClaimReferenceNumber>
                    <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                    <ClaimIntimationDate>".$ClaimIntimationDate."</ClaimIntimationDate>
                    <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                    <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                    <ClaimEstimatedAmount>".$ClaimEstimatedAmount."</ClaimEstimatedAmount>
                    <ClaimReserveAmount>".$ClaimReserveAmount."</ClaimReserveAmount>
                    <ClaimReserveMethod>".$ClaimReserveMethod."</ClaimReserveMethod>
                    <LossAssessmentOption>".$LossAssessmentOption."</LossAssessmentOption>
                    <AssessorName>".$AssessorName."</AssessorName>
                    <AssessorIdNumber>".$AssessorIdNumber."</AssessorIdNumber>
                    <AssessorIdType>".$AssessorIdType."</AssessorIdType>
                    <Claimants><Claimant>".$whoClaims."</Claimant></Claimants>
                  </ClaimIntimationDtl>
                  </ClaimIntimationReq>";
          //Utils::saveLogs("ClaimIntimationReq", $content);
          return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
    }
    catch(Exception $e)
    {
        Utils::saveLogs("ClaimIntimationReqException", $e->getMessage());
        return $e->getMessage();
    }
  }
}
