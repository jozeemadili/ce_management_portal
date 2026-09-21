<?php
namespace App\TIRAClient\Scripts\Classes;

use Illuminate\Support\Facades\Config;

class PolicyReq
{
  public static function submit($requestId, $InsurerCompanyCode = null, $CoverNoteReferenceNumber = null, $PolicyOperativeClause = null, $SpecialConditions = null, $Exclusions = null)
  {
    $endpoint     = Config::get('tira.endpoints.PolicyReq');
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');
    $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

    $content="<PolicyReq>
                <PolicyHdr>
                <RequestId>".$requestId."</RequestId>
                <CompanyCode>".$clientCode."</CompanyCode>
                <SystemCode>".$systemCode."</SystemCode>
                <CallBackUrl>".$callBackUrl."</CallBackUrl>
                <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                </PolicyHdr>
                <PolicyDtl>
                <PolicyNumber>".$requestId."</PolicyNumber>
                <PolicyOperativeClause>".$PolicyOperativeClause."</PolicyOperativeClause>
                <SpecialConditions>".$SpecialConditions."</SpecialConditions>
                <Exclusions>".$Exclusions."</Exclusions>
                <AppliedCoverNotes>
                <CoverNoteReferenceNumber>".$CoverNoteReferenceNumber."</CoverNoteReferenceNumber>
                </AppliedCoverNotes>
              </PolicyDtl>
              </PolicyReq>";

      return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
  }
}
