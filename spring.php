<?php

require_once 'Couriers/Spring.php';

use Couriers\HttpRequestException;
use Couriers\HttpValidationException;
use Couriers\Spring;

$order = [
    'sender_company' => 'BaseLinker',
    'sender_fullname' => 'Jan Kowalski',
    'sender_address' => 'Kopernika 10',
    'sender_city' => 'Gdansk',
    'sender_postalcode' => '80208',
    'sender_email' => '',
    'sender_phone' => '666666666',

    'delivery_company' => 'Spring GDS',
    'delivery_fullname' => 'Maud Driant',
    'delivery_address' => 'Strada Foisorului, Nr. 16, Bl. F11C, Sc. 1, Ap. 10',
    'delivery_city' => 'Bucuresti, Sector 3',
    'delivery_postalcode' => '031179',
    'delivery_country' => 'RO',
    'delivery_email' => 'john@doe.com',
    'delivery_phone' => '555555555',
];

$params = [
    'api_key' => 'f16753b55cac6c6e',
    'label_format' => 'PDF',
    'service' => 'PPTT',
];

$springCourier = new Spring($params['api_key']);

try {
    $trackingNumber = $springCourier->newPackage($order, $params);
    $shippingLabel = $springCourier->packagePDF($trackingNumber);
    header('Content-type: application/pdf');
    header('Content-Disposition: attachment; filename="etykieta.pdf"');
    echo $shippingLabel;
} catch (HttpRequestException $e) {
    header('Content-type: application/json');
    switch ($e->getCode()) {
        case 400:
            header('HTTP/1.1 400 Bad Request');
            break;
        case 422:
            header('HTTP/1.1 422 Unprocessable Entity');
            break;
        case 500:
            header('HTTP/1.1 500 Internal Server Error');
            break;
        case 503:
            header("HTTP/1.1 503 Service Unavailable");
            break;
    }

    echo $e->getMessage();
} catch (HttpValidationException $e) {
    header('Content-type: application/json');
    echo $e->getMessage();
}
