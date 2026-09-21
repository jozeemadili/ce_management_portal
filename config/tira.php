<?php
$baseUrl =  'http://41.59.86.178:8091';
// $baseUrl =  'https://api.tira.go.tz:8091';

return [
    'tiraclient'										=>array(
        'clientCode'									=>'OAC008',
        'clientKey'										=>'xRah1zzjGA7XTb',
        'certPassword'							    	=>'password',
        'systemCode'									=>'TP_INFOMATS_001',
        'callBackUrl'									=>'https://d1b5-197-250-227-32.eu.ngrok.io/api/v1/tira/callbacks',
        // 'callBackUrl'							    =>'http://143.198.74.44:8000/api/v1/tira/callbacks',
        'certPath'										=>'Certificates/tiramisclientprivate.pfx',
        // 'certPath'								    =>'/root/PROJECTS/policypro-apis/public/Certificates/tiramisclientprivate.pfx'
    ),
    'endpoints'											=>array(
        'MotorVerificationReq'				            =>$baseUrl.'/dispatch/api/motor/verification/v1/request',
        'MotorCoverNoteRefReq'				            =>$baseUrl.'/ecovernote/api/covernote/non-life/motor/v2/request',
        'CoverNoteVerificationReq'		                =>$baseUrl.'/ecovernote/api/covernote/verification/min/v1/request',
        'ClaimNotificationRefReq'		            	=>$baseUrl.'/eclaim/api/claim/claim-notification/v1/request',
        'ClaimIntimationReq'				        	=>$baseUrl.'/eclaim/api/claim/claim-intimation/v1/request',
        'ClaimAssessmentReq'					        =>$baseUrl.'/eclaim/api/claim/claim-assessment/v1/request',
        'DischargeVoucherReq'					        =>$baseUrl.'/eclaim/api/claim/claim-dischargevoucher/v1/request',
        'ClaimPaymentReq'						    	=>$baseUrl.'/eclaim/api/claim/claim-payment/v1/request',
        'ClaimRejectionReq'						        =>$baseUrl.'/eclaim/api/claim/claim-rejection/v1/request',
        'PolicyReq'										=>$baseUrl.'/ecovernote/api/policy/v1/request',
        'CoverNoteRefReq'                               =>$baseUrl.'/ecovernote/api/covernote/non-life/other/v2/request',
    ),
];


// return [
//     'tiraclient'										=>array(
//         'clientCode'									=>'OC1013',
//         'clientKey'										=>'1Xr@Jnq74&cYaSl2',
//         'certPassword'							    	=>'password',
//         'systemCode'									=>'LSYS_INFOMATS_001',
//         'callBackUrl'									=>'https://9818-197-250-99-135.eu.ngrok.io/api/v1/tira/callbacks',
//         // 'callBackUrl'							    =>'http://143.198.74.44:8000/api/v1/tira/callbacks',
//         'certPath'										=>'Certificates/tiramisclientprivate.pfx',
//         // 'certPath'								    =>'/root/PROJECTS/policypro-apis/public/Certificates/tiramisclientprivate.pfx'
//     ),
//     'endpoints'											=>array(
//         'MotorVerificationReq'				            =>$baseUrl.'/dispatch/api/motor/verification/v1/request',
//         'MotorCoverNoteRefReq'				            =>$baseUrl.'/ecovernote/api/covernote/non-life/motor/v2/request',
//         'CoverNoteVerificationReq'		                =>$baseUrl.'/ecovernote/api/covernote/verification/min/v1/request',
//         'ClaimNotificationRefReq'		            	=>$baseUrl.'/eclaim/api/claim/claim-notification/v1/request',
//         'ClaimIntimationReq'				        	=>$baseUrl.'/eclaim/api/claim/claim-intimation/v1/request',
//         'ClaimAssessmentReq'					        =>$baseUrl.'/eclaim/api/claim/claim-assessment/v1/request',
//         'DischargeVoucherReq'					        =>$baseUrl.'/eclaim/api/claim/claim-dischargevoucher/v1/request',
//         'ClaimPaymentReq'						    	=>$baseUrl.'/eclaim/api/claim/claim-payment/v1/request',
//         'ClaimRejectionReq'						        =>$baseUrl.'/eclaim/api/claim/claim-rejection/v1/request',
//         'PolicyReq'										=>$baseUrl.'/ecovernote/api/policy/v1/request',
//         'CoverNoteRefReq'                               =>$baseUrl.'/ecovernote/api/covernote/non-life/other/v2/request',
//     ),
// ];
