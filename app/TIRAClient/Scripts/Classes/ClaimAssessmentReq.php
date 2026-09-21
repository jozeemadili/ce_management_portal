<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class ClaimAssessmentReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $ClaimIntimationNumber = null, $ClaimReferenceNumber = null, $CoverNoteReferenceNumber = null, $AssessmentReceivedDate = null, $AssessmentReportSummary = null, $CurrencyCode = "TZS", $ExchangeRate = 1.00, $AssessmentAmount = null, $ApprovedClaimAmount = null, $ClaimApprovalDate = null, $ClaimApprovalAuthority = null, $IsReAssessment = "N", $Claimants = array())
  {
    try
    {

    $endpoint     = Config::get('tira.endpoints.ClaimAssessmentReq');
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');
    $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

    $whoClaims = "";
    foreach ($Claimants as $key => $data)
    {
      $whoClaims .= "<".$key.">".$data."</".$key.">";
    }


    $content="<ClaimAssessmentReq>
                <ClaimAssessmentHdr>
                <RequestId>".$requestId."</RequestId>
                <CompanyCode>".$clientCode."</CompanyCode>
                <SystemCode>".$systemCode."</SystemCode>
                <CallBackUrl>".$callBackUrl."</CallBackUrl>
                <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                </ClaimAssessmentHdr>
                <ClaimAssessmentDtl>
                <ClaimAssessmentNumber>".$requestId."</ClaimAssessmentNumber>
                <ClaimIntimationNumber>".$ClaimIntimationNumber."</ClaimIntimationNumber>
                <ClaimReferenceNumber>".$ClaimReferenceNumber."</ClaimReferenceNumber>
                <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                <AssessmentReceivedDate>".$AssessmentReceivedDate."</AssessmentReceivedDate>
                <AssessmentReportSummary>".$AssessmentReportSummary."</AssessmentReportSummary>
                <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                <AssessmentAmount>".$AssessmentAmount."</AssessmentAmount>
                <ApprovedClaimAmount>".$ApprovedClaimAmount."</ApprovedClaimAmount>
                <ClaimApprovalDate>".$ClaimApprovalDate."</ClaimApprovalDate>
                <ClaimApprovalAuthority>".$ClaimApprovalAuthority."</ClaimApprovalAuthority>
                <IsReAssessment>".$IsReAssessment."</IsReAssessment>
                <Claimants><Claimant>".$whoClaims."</Claimant></Claimants>
              </ClaimAssessmentDtl>
              </ClaimAssessmentReq>";

      //Utils::saveL ogs("ClaimAssessmentReq", $content);
      return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
    }
    catch(Exception $e)
    {
        Utils::saveLogs("ClaimAssessmentReqException", $e->getMessage());
        return $e->getMessage();
    }
  }
}
