<?php
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';
require_once '../xendit/vendor/autoload.php';

$gatewayModuleName = basename(__FILE__, '.php');
$gatewayParams = getGatewayVariables($gatewayModuleName);
\Xendit\Xendit::setApiKey($gatewayParams['secretKey']);
$xenditXCallbackToken = $gatewayParams['callbackToken'];
$reqHeaders = getallheaders();
$xIncomingCallbackTokenHeader = isset($reqHeaders['X-CALLBACK-TOKEN']) ? $reqHeaders['X-CALLBACK-TOKEN'] : "";

if ($xIncomingCallbackTokenHeader === $xenditXCallbackToken) {
    $rawRequestInput = file_get_contents("php://input");
    $arrRequestInput = json_decode($rawRequestInput, true);

    $success = $arrRequestInput['status'] == 'PAID';
    $invoiceId = $arrRequestInput['external_id'];
    $transactionId = $arrRequestInput['id'];
    $paymentAmount = $arrRequestInput['paid_amount'];
    $paymentFee = $arrRequestInput['fees_paid_amount'];
    $transactionStatus = $success ? 'Success' : 'Failure';
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);
    checkCbTransID($transactionId);
    logTransaction($gatewayParams['name'], $_POST, $transactionStatus);
    if ($success) {
        addInvoicePayment(
            $invoiceId,
            $transactionId,
            $paymentAmount,
            $paymentFee,
            $gatewayModuleName
        );
    }

} else {
    http_response_code(403);
}
