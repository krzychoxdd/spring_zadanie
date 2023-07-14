<?php

namespace Couriers;

use Exception;

class HttpValidationException extends Exception
{
}

class HttpRequestException extends Exception
{
}

class Spring
{
    private string $apiUrl = "https://mtapi.net/?testMode=1";
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function newPackage(array $order, array $params): string
    {
        $senderAddressChunks = str_split($order["sender_address"], 30);
        $deliveryAddressChunks = str_split($order["delivery_address"], 30);
        $this->validateFields($order, $senderAddressChunks, $deliveryAddressChunks);
        $requestData = [
            "Apikey" => $this->apiKey,
            "Command" => "OrderShipment",
            "Shipment" => [
                "LabelFormat" => $params["label_format"],
                "Service" => $params["service"],
                "Weight" => "1",
                "Value" => "20",
                "Currency" => "PLN",
                "DangerousGoods" => "N",
                "ConsignorAddress" => [
                    "Name" => $order["sender_fullname"],
                    "Company" => $order["sender_company"],
                    "City" => $order["sender_city"],
                    "Zip" => $order["sender_postalcode"],
                    "Country" => "PL",
                    "AddressLine1" => $senderAddressChunks[0],
                    "AddressLine2" => $senderAddressChunks[1] ?? "",
                    "AddressLine3" => $senderAddressChunks[2] ?? "",
                ],
                "ConsigneeAddress" => [
                    "Name" => $order["delivery_fullname"],
                    "Company" => $order["delivery_company"],
                    "AddressLine1" => $deliveryAddressChunks[0],
                    "AddressLine2" => $deliveryAddressChunks[1] ?? "",
                    "AddressLine3" => $deliveryAddressChunks[2] ?? "",
                    "City" => $order["delivery_city"],
                    "Zip" => $order["delivery_postalcode"],
                    "Country" => $order["delivery_country"],
                    "Phone" => $order["delivery_phone"],
                    "Email" => $order["delivery_email"],
                ],
                "Products" => [
                    [
                        "Description" => "foo",
                        "HsCode" => "1234567",
                        "Quantity" => "1",
                        "Value" => "10",
                        "Weight" => "1",
                    ],
                ],
            ],
        ];
        $response = $this->sendRequest($requestData);

        return $response["Shipment"]["TrackingNumber"];
    }

    private function validateFields(array $order, array $senderAddressChunks, array $deliveryAddressChunks): void
    {
        $errors = [];
        if (strlen($order["sender_address"]) < 1) {
            $errors["sender"]["sender_address"] = "Sender address is empty";
        }
        if (strlen($order["delivery_address"]) < 1) {
            $errors["delivery"]["delivery_address"] = "Delivery address is empty";
        }
        if (count($senderAddressChunks) > 3) {
            $errors["sender"]["sender_address"] = "Sender address maximum 90 characters";
        }
        if (count($deliveryAddressChunks) > 3) {
            $errors["delivery"]["delivery_address"] = "Delivery address maximum 90 characters";
        }
        if (strlen($order["sender_fullname"]) > 30) {
            $errors["sender"]["sender_fullname"] = "Sender full name maximum 30 characters";
        }
        if (strlen($order["delivery_fullname"]) > 30) {
            $errors["delivery"]["delivery_fullname"] = "Delivery full name maximum 30 characters";
        }
        if (strlen($order["sender_company"]) > 30) {
            $errors["sender"]["sender_company"] = "Sender company maximum 30 characters";
        }
        if (strlen($order["delivery_company"]) > 30) {
            $errors["delivery"]["delivery_company"] = "Delivery company maximum 30 characters";
        }
        if (strlen($order["sender_city"]) > 30) {
            $errors["sender"]["sender_city"] = "Sender city maximum 30 characters";
        }
        if (strlen($order["delivery_city"]) > 30) {
            $errors["delivery"]["delivery_city"] = "Delivery city maximum 30 characters";
        }
        if (strlen($order["sender_postalcode"]) > 20) {
            $errors["sender"]["sender_postalcode"] = "Sender postal code maximum 20 characters";
        }
        if (strlen($order["delivery_postalcode"]) > 20) {
            $errors["delivery"]["delivery_postalcode"] = "Delivery postal code maximum 20 characters";
        }
        if (strlen($order["sender_phone"]) > 15) {
            $errors["sender"]["sender_phone"] = "Sender phone maximum 15 characters";
        }
        if (strlen($order["delivery_phone"]) > 15) {
            $errors["delivery"]["sender_phone"] = "Delivery phone maximum 15 characters";
        }
        if (!empty($validationBag)) {
            throw new HttpValidationException(json_encode($errors), 400);
        }
    }

    private function sendRequest(array $data): array
    {
        $url = $this->apiUrl;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        $responseJson = curl_exec($ch);
        $responseData = json_decode($responseJson, true);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $this->handleErrorFromResponse($responseData, $httpStatus);

        return $responseData;
    }

    private function handleErrorFromResponse($response, int $httpStatus): void
    {
        if ($httpStatus === 0) {
            throw new HttpRequestException(msg_err("Spring api connection error"), 503);
        }
        if ($response["ErrorLevel"] === 1) {
            throw new HttpRequestException(msg_err("Error 1: ", $response["Error"]), 422);
        } elseif ($response["ErrorLevel"] === 10) {
            throw new HttpRequestException(msg_err("Error 10: ", $response["Error"]), 400);
        }
        if ($response["ErrorLevel"] !== 0 || $httpStatus != 200) {
            throw new HttpRequestException(msg_err("Unhandled error: ", $response["Error"]), 500);
        }
    }

    public function packagePDF(string $trackingNumber): string
    {
        $requestData = [
            "Apikey" => $this->apiKey,
            "Command" => "GetShipmentLabel",
            "Shipment" => [
                "LabelFormat" => "PDF",
                "TrackingNumber" => $trackingNumber,
            ],
        ];
        $response = $this->sendRequest($requestData);
        if (empty($response["Shipment"]["LabelImage"])) {
            throw new HttpRequestException(msg_err("Failed to generate shipping label"), 500);
        }

        return base64_decode($response["Shipment"]["LabelImage"]);
    }
}

function msg_err(string $msg, string $api_msg = ""): string
{
    return json_encode(["error" => $msg . $api_msg]);
}
