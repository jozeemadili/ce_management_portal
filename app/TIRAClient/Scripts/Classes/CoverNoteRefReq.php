<?php
namespace App\TIRAClient\Scripts\Classes;

use Illuminate\Support\Facades\Config;

class CoverNoteRefReq
{
  public static function submit($requestId, $InsurerCompanyCode, $TranCompanyCode, $CoverNoteType = null, $SalePointCode = null, $CoverNoteStartDate = null, $CoverNoteEndDate = null, $CoverNoteDesc = null, $OperativeClause = null, $PaymentMode = null, $CurrencyCode = null, $ExchangeRate = null, $TotalPremiumExcludingTax = null, $TotalPremiumIncludingTax = null, $CommisionPaid = null, $CommisionRate = null, $OfficerName = null, $OfficerTitle = null, $ProductCode = null, $RiskCode = null, $SumInsured = null, $SumInsuredEquivalent = null, $PremiumRate = null, $PremiumBeforeDiscount = null, $PremiumAfterDiscount = null, $PremiumExcludingTaxEquivalent = null, $PremiumIncludingTax = null, $TaxCode = null, $IsTaxExempted = null, $TaxRate = null, $TaxAmount = null, $SubjectMatterReference = null, $SubjectMatterDesc = null, $PolicyHolderName = null, $PolicyHolderBirthDate = null, $PolicyHolderType = null, $PolicyHolderIdNumber = null, $PolicyHolderIdType = null, $Gender = null, $CountryCode = null, $Region = null, $District = null, $Street = null, $PolicyHolderPhoneNumber = null, $PolicyHolderFax = null, $PostalAddress = null, $EmailAddress = null, $DiscountsOffered = null, $CoverNoteAddons = null, $PrevCoverNoteReferenceNumber = null, $EndorsementType = null, $EndorsementReason = null, $EndorsementPremiumEarned = null)
  {
    $endpoint     = Config::get('tira.endpoints.CoverNoteRefReq');
    $clientCode   = Config::get('tira.tiraclient.clientCode');
    $systemCode   = Config::get('tira.tiraclient.systemCode');
    $callBackUrl  = Config::get('tira.tiraclient.callBackUrl');

    $PolicyHolderPhoneNumber = "0".$PolicyHolderPhoneNumber;

    $content="<CoverNoteRefReq>
                <CoverNoteHdr>
                    <RequestId>".$requestId."</RequestId>
                    <CompanyCode>".$clientCode."</CompanyCode>
                    <SystemCode>".$systemCode."</SystemCode>
                    <CallBackUrl>".$callBackUrl."</CallBackUrl>
                    <InsurerCompanyCode>".$InsurerCompanyCode."</InsurerCompanyCode>
                    <TranCompanyCode>".$TranCompanyCode."</TranCompanyCode>
                    <CoverNoteType>".$CoverNoteType."</CoverNoteType>
                </CoverNoteHdr>
                <CoverNoteDtl>
                    <CoverNoteNumber>".$requestId."</CoverNoteNumber>
                    <PrevCoverNoteReferenceNumber>".$PrevCoverNoteReferenceNumber."</PrevCoverNoteReferenceNumber>
                    <SalePointCode>".$SalePointCode."</SalePointCode>
                    <CoverNoteStartDate>".$CoverNoteStartDate."</CoverNoteStartDate>
                    <CoverNoteEndDate>".$CoverNoteEndDate."</CoverNoteEndDate>
                    <CoverNoteDesc>".$CoverNoteDesc."</CoverNoteDesc>
                    <OperativeClause>".$OperativeClause."</OperativeClause>
                    <PaymentMode>".$PaymentMode."</PaymentMode>
                    <CurrencyCode>".$CurrencyCode."</CurrencyCode>
                    <ExchangeRate>".$ExchangeRate."</ExchangeRate>
                    <TotalPremiumExcludingTax>".$TotalPremiumExcludingTax."</TotalPremiumExcludingTax>
                    <TotalPremiumIncludingTax>".$TotalPremiumIncludingTax."</TotalPremiumIncludingTax>
                    <CommisionPaid>".$CommisionPaid."</CommisionPaid>
                    <CommisionRate>".$CommisionRate."</CommisionRate>
                    <OfficerName>".$OfficerName."</OfficerName>
                    <OfficerTitle>".$OfficerTitle."</OfficerTitle>
                    <ProductCode>".$ProductCode."</ProductCode>
                    <EndorsementType>".$EndorsementType."</EndorsementType>
                    <EndorsementReason>".$EndorsementReason."</EndorsementReason>
                    <EndorsementPremiumEarned>".$EndorsementPremiumEarned."</EndorsementPremiumEarned>
                    <RisksCovered>
                        <RiskCovered>
                            <RiskCode>".$RiskCode."</RiskCode>
                            <SumInsured>".$SumInsured."</SumInsured>
                            <SumInsuredEquivalent>".$SumInsuredEquivalent."</SumInsuredEquivalent>
                            <PremiumRate>".$PremiumRate."</PremiumRate>
                            <PremiumBeforeDiscount>".$PremiumBeforeDiscount."</PremiumBeforeDiscount>
                            <PremiumAfterDiscount>".$PremiumAfterDiscount."</PremiumAfterDiscount>
                            <PremiumExcludingTaxEquivalent>".$PremiumExcludingTaxEquivalent."</PremiumExcludingTaxEquivalent>
                            <PremiumIncludingTax>".$PremiumIncludingTax."</PremiumIncludingTax>
                            <DiscountsOffered></DiscountsOffered>
                            <TaxesCharged>
                                <TaxCharged>
                                    <TaxCode>".$TaxCode."</TaxCode>
                                    <IsTaxExempted>".$IsTaxExempted."</IsTaxExempted>
                                    <TaxExemptionType></TaxExemptionType>
                                    <TaxExemptionReference></TaxExemptionReference>
                                    <TaxRate>".$TaxRate."</TaxRate>
                                    <TaxAmount>".$TaxAmount."</TaxAmount>
                                </TaxCharged>
                            </TaxesCharged>
                        </RiskCovered>
                    </RisksCovered>
                    <SubjectMattersCovered>
                        <SubjectMatter>
                            <SubjectMatterReference>".$SubjectMatterReference."</SubjectMatterReference>
                            <SubjectMatterDesc>".$SubjectMatterDesc."</SubjectMatterDesc>
                        </SubjectMatter>
                    </SubjectMattersCovered>
                    <CoverNoteAddons>".$CoverNoteAddons."</CoverNoteAddons>
                    <PolicyHolders>
                        <PolicyHolder>
                            <PolicyHolderName>".$PolicyHolderName."</PolicyHolderName>
                            <PolicyHolderBirthDate>".$PolicyHolderBirthDate."</PolicyHolderBirthDate>
                            <PolicyHolderType>".$PolicyHolderType."</PolicyHolderType>
                            <PolicyHolderIdNumber>".$PolicyHolderIdNumber."</PolicyHolderIdNumber>
                            <PolicyHolderIdType>".$PolicyHolderIdType."</PolicyHolderIdType>
                            <Gender>".$Gender."</Gender>
                            <CountryCode>".$CountryCode."</CountryCode>
                            <Region>".$Region."</Region>
                            <District>".$District."</District>
                            <Street>".$Street."</Street>
                            <PolicyHolderPhoneNumber>".$PolicyHolderPhoneNumber."</PolicyHolderPhoneNumber>
                            <PolicyHolderFax>".$PolicyHolderFax."</PolicyHolderFax>
                            <PostalAddress>".$PostalAddress."</PostalAddress>
                            <EmailAddress>".$EmailAddress."</EmailAddress>
                        </PolicyHolder>
                    </PolicyHolders>
                </CoverNoteDtl>
            </CoverNoteRefReq>";

    //   Utils::saveLogs("CoverNoteRefReq", $content."\n".$endpoint);
      return (json_encode(simplexml_load_string(TIRAClient::sendRequest($content, $endpoint))));
  }

}
