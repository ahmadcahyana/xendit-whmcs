<?php
require 'xendit/vendor/autoload.php';
use WHMCS\Billing\Invoice;
if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * @return array
 */
function xendit_MetaData()
{
    return array(
        'DisplayName' => 'Xendit Payment Gateway Module',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCreditCardInput' => true,
        'TokenisedStorage' => false,
    );
}

/**
 * @return array
 */
function xendit_config()
{

    $configs = array(
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Xendit Payment Gateway Module',
        ),
        'Pembatas-Description-Payment-Gateway' => array(
            'FriendlyName' => '',
            'Type' => 'hidden',
            'Size' => '72',
            'Default' => '',
            'Description' => '<img src="../modules/gateways/xendit/logo.png" width="70" align="left" style="padding-right:12px;" /> Xendit is an online payment gateway that processes payments through many different payment methods</span>',
        ),
        'secretKey' => array(
            'FriendlyName' => 'Secret Key',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),
        'callbackToken' => array(
            'FriendlyName' => 'Callback Verification Token',
            'Type' => 'password',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter secret key here',
        ),
        'paymentfee' => array(
            'FriendlyName'  => 'Payment Fee',
            'Type'          => 'text',
            'Size'          => '100',
            'Default'       => '4950',
            'Description'   => 'Fixed Amount Payment Fee Will Added to Invoice and Pay by Client. (Default: 4.500 + PPN 10% (From 4.500*10% = 450) = 4.950).',
        ),
        'expired' => array(
            'FriendlyName'  => 'Time Invoice Expired',
            'Type'          => 'dropdown',
            'Options'       => array(
                '300'       => '5 Minutes',
                '900'       => '15 Minutes',
                '1800'      => '30 Minutes',
                '3600'      => '1 Hour',
                '10800'     => '3 Hours',
                '21600'     => '6 Hours',
                '43200'     => '12 Hours',
                '86400'     => '1 Day',
                '259200'    => '3 Days',
            ),
            'Description'   => 'Select Duration Time For Invoice Expired.',
        ),
        'sendemail' => array(
            'FriendlyName' => 'Send Invoice Email',
            'Type' => 'yesno',
            'Description' => 'Allow Xendit Send Email Invoice to Client',
        ),
    );
    return $configs;
}

/**
 * @param $params
 * @return string
 */
function xendit_link($params)
{

    // Gateway Configuration Parameters
    $secretKey = $params['secretKey'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $paymentfee = $params['paymentfee'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];
    $invoiceDuration = $params['expired'];
    $invoice = localAPI(/**command*/'GetInvoice', /**postData*/['invoiceid' => $invoiceId]);

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];


    \Xendit\Xendit::setApiKey($secretKey);
    try {
        $items = array();
        foreach ($invoice['items']['item'] as $item) {
            $item_price = (float) $item['amount'];
            $items[] = [
                'quantity' => 1,
                'name' => $item['description'],
                'price' => $item_price,
            ];
        }
        $redirectUrl = invoice_url($invoiceId);
        $invoiceParam = '/?external_id=' . $invoiceId;
        $getInvoice = \Xendit\Invoice::retrieve($invoiceParam);
        if (empty($getInvoice)) {
            $invoiceData = [
                'external_id' => (string)$invoiceId,
                'payer_email' => $email,
                'description' => $description,
                'items' => $items,
                'fees' => array(['type' => 'Payment Fee', 'value' => (float)$paymentfee]),
                'amount' => $amount + (float)$paymentfee,
                'invoice_duration' => $invoiceDuration,
                'success_redirect_url' => $redirectUrl,
                'failure_redirect_url' => $redirectUrl,
                'customer' => ['given_names' => $firstname . '' . $lastname, 'mobile_number' => $phone]
            ];
            $createInvoice = \Xendit\Invoice::create($invoiceData);
            $url = $createInvoice['invoice_url'];
        } else {
            $url = $getInvoice[0]['invoice_url'];
        }

    } catch (\Xendit\Exceptions\ApiException $ae) {
        var_dump($ae);
    }

    $postfields = array();
    $postfields['invoice_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address1'] = $address1;
    $postfields['address2'] = $address2;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['postcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone'] = $phone;
    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['return_url'] = $returnUrl;

    $htmlOutput = '<form method="post" action="' . $url . '">';
    foreach ($postfields as $k => $v) {
        $htmlOutput .= '<input type="hidden" name="' . $k . '" value="' . urlencode($v) . '" />';
    }
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}

/**
 * @param $invoiceId
 * @return string
 */
function invoice_url($invoiceId)
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        $link = "https";
    else $link = "http";
    $link .= "://";
    $link .= $_SERVER['HTTP_HOST'];

    return $link . '/viewinvoice.php?id=' . $invoiceId;
}
