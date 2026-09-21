<?php
namespace App\TIRAClient\Scripts\Classes;

use Exception;
use Illuminate\Support\Facades\Config;

class DischargeVoucherReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $ClaimAssessmentNumber = null, $ClaimReferenceNumber = null, $CoverNoteReferenceNumber = null, $DischargeVoucherDate = null, $CurrencyCode = "TZS", $ExchangeRate = 1.00, $ClaimOfferCommunicationDate = null, $ClaimOfferAmount = null, $ClaimantResponseDate = null, $AdjustmentDate = null, $AdjustmentReason = null, $AdjustmentAmount = null, $ReconciliationDate = null, $ReconciliationSummary = null, $ReconciledAmount = null, $OfferAccepted = "Y", $Claimants = array() )
  {
    try
    {
            $endpoint     = Config::get('tira.endpoints.DischargeVoucherReq');
            $clientCode   = Config::get('tira.tiraclient.clientCode');
            $systemCode   = Config::get('tira.tiraclient.systemCode');
            $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

            $whoClaims = "";
            foreach ($Claimants as $key => $data)
            {
            $whoClaims .= "<".$key.">".$data."</".$key.">";
            }

            $content="<DischargeVoucherReq>
                        <DischargeVoucherHdr>
                        <RequestId>".$requestId."</RequestId>
                        <CompanyCode>".$clientCode."</CompanyCode>
                        <SystemCode>".$systemCode."</SystemCode>
                        <CallBackUrl>".$callBackUrl."</CallBackUrl>
                        <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                        </DischargeVoucherHdr>
                        <DischargeVoucherDtl>
                        <DischargeVoucherNumber>".$requestId."</DischargeVoucherNumber>
                        <ClaimAssessmentNumber>".$ClaimAssessmentNumber."</ClaimAssessmentNumber>
                        <ClaimReferenceNumber>".$ClaimReferenceNumber."</ClaimReferenceNumber>
                        <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                        <DischargeVoucherDate>".$DischargeVoucherDate."</DischargeVoucherDate>
                        <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                        <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                        <ClaimOfferCommunicationDate>".$ClaimOfferCommunicationDate."</ClaimOfferCommunicationDate>
                        <ClaimOfferAmount>".$ClaimOfferAmount."</ClaimOfferAmount>
                        <ClaimantResponseDate>".$ClaimantResponseDate."</ClaimantResponseDate>
                        <AdjustmentDate>".$AdjustmentDate."</AdjustmentDate>
                        <AdjustmentReason>".$AdjustmentReason."</AdjustmentReason>
                        <AdjustmentAmount>".$AdjustmentAmount."</AdjustmentAmount>
                        <ReconciliationDate>".$ReconciliationDate."</ReconciliationDate>
                        <ReconciliationSummary>".$ReconciliationSummary."</ReconciliationSummary>
                        <ReconciledAmount>".$ReconciledAmount."</ReconciledAmount>
                        <OfferAccepted>".$OfferAccepted."</OfferAccepted>
                        <Claimants><Claimant>".$whoClaims."</Claimant></Claimants>
                    </DischargeVoucherDtl>
                    </DischargeVoucherReq>";

            //Utils::saveLogs("DischargeVoucherReq", $content);
            return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
          }
          catch(Exception $e)
          {
              Utils::saveLogs("DischargeVoucherReqReqException", $e->getMessage());
              return $e->getMessage();
          }
    }
}
