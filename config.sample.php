<?php

$brands = array(
    'zz' => array(
        'brandcode' => 'ZZ',
        'name' => 'Test API',
        'api' => '',
        'key' => '',
        'secret' => ''
    )
);

$brandcode = filter_input(INPUT_GET, 'brandcode');
if (!$brandcode) {
    $brandcode = 'zz';
}

// Connect to the api
\tabs\api\client\ApiClient::factory(
    $brands[$brandcode]['api'],
    $brands[$brandcode]['key'],
    $brands[$brandcode]['secret']
);