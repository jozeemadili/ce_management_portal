<?php
namespace App\TIRAClient\Scripts\Classes;

use App\Models\Policy;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class JubileeClient
{
    public static function submitPolicyDetails($policy_id) 
    {
            try 
            {
                    $Utils = new Utils();
                    
                    // $SystemCode                       = Config::get('tira.tiraclient.systemCode');
                    $SystemCode                          = "NBC-AGENTS";
                    //$url                                 = "http://jubileeapiinterface.jubileetanzania.co.tz:8090/api/v2/motor/transaction";
                    $url                                 = "https://jubileeapiinterface.jubileetanzania.co.tz:8443/api/v2/motor/transaction";
                    // $apiKey                              = "NaGNCbK7o2NsnzzBcU77juOgNBSchi3zEL7mtZgWQ1A5uLL9=";
                    $apiKey                              = "BAgbr2BJ89MTXFX86xQ80aALDcQrpD99BSI9hKPv2jJWER--";
                    $callbackUrl                         = "https://policypro.co.tz/api/v1/jubilee/callbacks";
                
                    $user = Auth::user();
                    $Policy = Policy::with('quotation.risk.product')->with('quotation.payment')->with('quotation.vehicle')->with('quotation.customer')->find($policy_id);

                    $Quotation                           = $Policy['quotation'];
                    $Vehicle                             = $Quotation['vehicle'];
                    $Risk                                = $Quotation['risk'];
                    $Product                             = $Risk['product'];
                    $ProductCode                         = $Product['code'];
                    $Customer                            = $Quotation['customer'];
                    $Payment                             = $Quotation['payment'];

                    //Params
                    $RequestId                           = $Policy['reference_number']; 
                    $BrokerId                            = "14"; // Maintained by Jubilee
                    $BranchId                            = "9"; // Maintained by Jubilee
                    $ProductinJubilee                    = $Utils->getJubileeProductNumber($ProductCode);
                    $CoverType                           = $Utils->getJubileeCoverType($Risk['name']);
                    $UsageType                           = $Utils->getJubileeMotorUsageType($Vehicle['motor_usage']);
                    $RiskNoteNumber                      = $Quotation['reference_number'];
                    $DebitNoteNumber                     = $Quotation['reference_number'];   
                    $IsVATExempt                         = $Quotation['is_tax_exempted'];
                    $ReceiptDate                         = date_format((date_create($Payment['created_at'])), 'Y-m-d');
                    $ReceiptNo                           = $Payment['reference_number']; 
                    $ReceiptReferenceNo                  = $Payment['reference_number'];
                    $ReceiptAmount                       = $Payment['paid_amount'];
                    $BankCode                            = "NBC"; //have to ask
                    $IssueDate                           = date_format((date_create($Quotation['created_at'])), 'Y-m-d');
                    $CoverNoteStartDate                  = date_format((date_create($Quotation['start_date'])), 'Y-m-d');
                    $CoverNoteEndDate                    = date_format((date_create($Quotation['end_date'])), 'Y-m-d');;
                    $PaymentMode                         = $Utils->getJubileePaymentMode($Payment['method']);
                    $CurrencyCode                        = $Payment['currency_code'];
                    $CustomerName                        = strtoupper($Customer['first_name']." ".$Customer['last_name']);
                    $CustomerBirthDate                   = date_format((date_create($Customer['dob'])), 'Y-m-d');
                    $CustomerIdType                      = intval($Customer['id_type']);
                    $CustomerIdNumber                    = $Customer['id_number'];
                    $CustomerTIN                         = $Customer['id_type'] == 7 ? $Customer['tin'] : null;
                    $customerVRN                         = $Customer['vrn'] != null ? $Customer['vrn'] : null;
                    $customerGender                      = substr($Customer['gender'], 0, 1);
                    $customerCountry                     = $Customer['country_code'];
                    $customerRegion                      = $Customer['ward']['district']['region']['name'];
                    $customerDistrict                    = $Customer['ward']['district']['name'];
                    $customerPhoneNumber                 = "255".$Customer['mobile'];
                    $customerAddress                     = $Customer['postal_address'];
                    $customerEmailAddress                = $Customer['email'];
                    $customerType                        = intval($Customer['type'])-1; // 0 Individual, 1 Corporate
                    $sumInsured                          = $Quotation['sum_insured'];
                    $premiumExcludingVat                 = $Quotation['total_premium_excluding_tax'];
                    $vatPercentage                       = $Risk['tax_rate'];
                    $vatAmount                           = $Quotation['premium_including_tax'] - $Quotation['total_premium_excluding_tax'];
                    $PremiumIncludingVat                 = $Quotation['premium_including_tax'];
                    $TiraProductCode                     = $ProductCode;
                    $TiraRiskCode                        = $Risk['code'];
                    $TiraStickerNumber                   = $Quotation['sticker_number'];
                    $TiraCoverNoteReferenceNumber        = $Quotation['covernote_reference_number'];
                    $RegistrationNumber                  = $Vehicle['registration_number'];
                    $BodyType                            = $Vehicle['body_type'];
                    $SeatingCapacity                     = $Vehicle['sitting_capacity'];
                    $ChassisNumber                       = $Vehicle['chassis_number'];
                    $Make                                = $Vehicle['make'];
                    $Model                               = $Vehicle['model'];
                    $ModelNumber                         = $Vehicle['model_number'];
                    $Color                               = $Vehicle['color'];
                    $EngineNumber                        = $Vehicle['engine_number'];
                    $EngineCapacity                      = intVal($Vehicle['engine_capacity']);
                    $FuelUsed                            = $Vehicle['fuel_used'];
                    $NumberOfAxles                       = $Vehicle['number_of_axles'];
                    $AxleDistance                        = $Vehicle['axle_distance'];
                    $YearOfManufacture                   = $Vehicle['year_of_manufacture'];
                    $TareWeight                          = $Vehicle['tare_weight'];
                    $GrossWeight                         = $Vehicle['gross_weight'];

                    $data = array(
                                    'RequestId' => $RequestId,
                                    'SystemCode' => $SystemCode,
                                    'BrokerId' => $BrokerId,
                                    'BranchId' => $BranchId,
                                    'Product' => $ProductinJubilee,
                                    'TiraProductCode' => $TiraProductCode,
                                    'RiskNoteNumber' => $RiskNoteNumber,
                                    'DebitNoteNumber' => $DebitNoteNumber,
                                    "FleetSize"     => 1,
                                    "FleetId"       => "0",
                                    "ReceiptDate" => $ReceiptDate,
                                    "ReceiptNo" => $ReceiptNo,
                                    "ReceiptReferenceNo" => $ReceiptReferenceNo,
                                    "ReceiptAmount" => strval($ReceiptAmount),
                                    "BankCode" => $BankCode,
                                    "IssueDate" => $IssueDate,
                                    "CoverNoteStartDate" => $CoverNoteStartDate,
                                    "CoverNoteEndDate" => $CoverNoteEndDate,
                                    "PaymentMode" => $PaymentMode,
                                    "CurrencyCode" => $CurrencyCode,
                                    "CustomerName" => $CustomerName,
                                    "CustomerBirthDate" => $CustomerBirthDate,
                                    "CustomerIdType" => $CustomerIdType,
                                    "CustomerIdNumber" => $CustomerIdNumber,
                                    "CustomerTIN" => $CustomerTIN,
                                    "CustomerVRN" => $customerVRN,
                                    "CustomerGender" => $customerGender,
                                    "CustomerCountry" => $customerCountry,
                                    "CustomerRegion" => $customerRegion,
                                    "CustomerDistrict" => $customerDistrict,
                                    "CustomerPhoneNumber" => $customerPhoneNumber,
                                    "CustomerAddress" => $customerAddress,
                                    "CustomerEmailAddress" => $customerEmailAddress,
                                    "CustomerType" => $customerType,
                                    "RiskDetails" =>[
                                    array(
                                        'CoverType' => $CoverType,
                                        'UsageType' => $UsageType,
                                        'RiskNoteNumber' => $RiskNoteNumber,
                                        'DebitNoteNumber' => $DebitNoteNumber,
                                        "FleetEntryNo"=> 1,
                                        "IsVATExempt" => $IsVATExempt,
                                        "SumInsured"  => $sumInsured,
                                        "PremiumExcludingVat" => $premiumExcludingVat,
                                        "VatPercentage" => $vatPercentage,
                                        "VatAmount" => $vatAmount,
                                        "PremiumIncludingVat" => $PremiumIncludingVat,
                                        // "TiraProductCode" => $TiraProductCode,
                                        "TiraRiskCode" => $TiraRiskCode,
                                        "TiraRiskCode" => $TiraRiskCode,
                                        "TiraStickerNumber" => $TiraStickerNumber,
                                        "TiraCoverNoteReferenceNumber" => $TiraCoverNoteReferenceNumber,
                                        "RegistrationNumber" => $RegistrationNumber,
                                        "BodyType" => $BodyType,
                                        "SeatingCapacity" => $SeatingCapacity,
                                        "ChassisNumber"=> $ChassisNumber,
                                        "Make" => $Make,
                                        "Model" => $Model,
                                        "ModelNumber" => $ModelNumber,
                                        "Color" => $Color,
                                        "EngineNumber" => $EngineNumber,
                                        "EngineCapacity" => $EngineCapacity,
                                        "FuelUsed" => $FuelUsed,
                                        "NumberOfAxles" => $NumberOfAxles,
                                        "AxleDistance" => $AxleDistance,
                                        "YearOfManufacture" => $YearOfManufacture,
                                        "TareWeight" => $TareWeight,
                                        "GrossWeight" => $GrossWeight,
                                    )],
                                    "CallbackUrl" => $callbackUrl
                    );

                    $payload = json_encode($data);

                    $Utils->saveLogs("JubileePostPayload", $payload);

                    $ch = curl_init($url);

                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLINFO_HEADER_OUT, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: Api-Key '.$apiKey,
                        'Content-Length: ' . strlen($payload))
                    );

                    $result = curl_exec($ch);

                    curl_close($ch);

                    $headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
                    $Utils->saveLogs("JubileePostResults", "Results : \n".$result."\nHeaders :\n".$headers);

                    $response = json_decode($result);

                    $Policy->insurer_response = str_replace('"', '', json_encode($response->message));
                    $Policy->insurer_response_misclaneous = json_encode($response);
                    $Policy->save();

                    return $response;
        }
        catch(Exception $e)
        {
            $Utils->saveLogs("JubileePostExceptional", json_encode($e->getTrace()));
        }
    }

}
