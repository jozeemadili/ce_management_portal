<?php
namespace App\TIRAClient\Scripts\Classes;

use Illuminate\Support\Facades\Config;

class ClaimNotificationRefReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $TranCompanyCode = null, $ClaimNotificationNumber = null, $CoverNoteReferenceNumber = null, $ClaimReportDate = null, $ClaimFormDullyFilled = null, $LossDate = null, $LossNature = null, $LossType = null, $LossLocation = null, $OfficerName = null, $OfficerTitle = null)
  {
    $endpoint     = Config::get('tira.endpoints.ClaimNotificationRefReq');
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');
    $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');


    $content="<ClaimNotificationRefReq>
                  <ClaimNotificationHdr>
                    <RequestId>".$requestId."</RequestId>
                    <CompanyCode>".$clientCode."</CompanyCode>
                    <SystemCode>".$systemCode."</SystemCode>
                    <CallBackUrl>".$callBackUrl."</CallBackUrl>
                    <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                    <TranCompanyCode>".$TranCompanyCode."</TranCompanyCode>
                  </ClaimNotificationHdr>
                  <ClaimNotificationDtl>
                    <ClaimNotificationNumber>".$ClaimNotificationNumber."</ClaimNotificationNumber>
                    <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                    <ClaimReportDate>".$ClaimReportDate."</ClaimReportDate>
                    <ClaimFormDullyFilled>".$ClaimFormDullyFilled."</ClaimFormDullyFilled>
                    <LossDate>".$LossDate."</LossDate>
                    <LossNature>".$LossNature."</LossNature>
                    <LossType>".$LossType."</LossType>
                    <LossLocation>".$LossLocation."</LossLocation>
                    <OfficerName>".$OfficerName."</OfficerName>
                    <OfficerTitle>".$OfficerTitle."</OfficerTitle>
                  </ClaimNotificationDtl>
              </ClaimNotificationRefReq>";

      //Utils::saveLogs("ClaimNotificationRefReq", $content);

      return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
  }
}
