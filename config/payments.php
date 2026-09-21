<?php
return [
    'evmak'				=> array(
        'api_source'	=> 'POLICY-PRO',
        // 'callback'	    => 'https://mainbima.co.tz/infomats/payments.php',
        'callback'	    => 'http://143.198.74.44:8000/api/v1/payments/callbacks',
        'hash'          =>  md5('insurance|' . date('d-m-Y')),
        'user'          => "insurance",
        'mno_types'     => array(
                        'mpesa'     => array(
                            'code'     => 'Mpesa',
                            'endpoint' => 'https://vodaapi.evmak.com/test/',
                        ),
                        'tigopesa'     => array(
                            'code'     => 'TigoPesa',
                            'endpoint' => 'https://mno.evmak.com/tigo/test/',
                        ),
                    ),
    'minimum_payment_amount' => 1000
    ),
];
