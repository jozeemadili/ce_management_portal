<?php
$baseUrl =  'http://jubileeapiinterface.jubileetanzania.co.tz:8090/api/v2';
// $baseUrl =  'https://jubileeapiinterface.jubileetanzania.co.tz:8443/api/v2';
return [
            'endpoints'   => array('submit_motor' => $baseUrl.'/motor/transaction'),
            'secrets'     => array('Api_Key'      => 'NaGNCbK7o2NsnzzBcU77juOgNBSchi3zEL7mtZgWQ1A5uLL9='),
            'callbackUrl' => 'https://policypro.co.tz/api/v1/jubilee/callbacks'
       ];

