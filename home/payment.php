<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
    $ca =$_SERVER['DOCUMENT_ROOT']."/PSroot.pem";
    $key_pem = $_SERVER['DOCUMENT_ROOT']."/rsa_key_pair.pem";
    $cert =  $_SERVER['DOCUMENT_ROOT']."/certificate.0017073.pem";

    $merchant_handler = "https://ecomm.pashabank.az:18443/ecomm2/MerchantHandler";

    $client_handler = "https://ecomm.pashabank.az:8463/ecomm2/ClientHandler";
    $amount = filter_input(INPUT_POST, 'amount');
    $order = filter_input(INPUT_POST, 'order_id');
    $amount = number_format(($amount), 2, '', '') * 1.70; 
    $amount = (int) $amount;
    if (!is_numeric($amount) || strlen($amount) < 1 || strlen($amount) > 12) {
        die('amount false');
    }
    $currency = filter_input(INPUT_POST, 'currency');
    if (!is_numeric($currency) || strlen($currency) != 3) {

    }
    $description = filter_input(INPUT_POST, 'description');
    if (strlen($description) > 125) {

    }
    $language = filter_input(INPUT_POST, 'language');
    if (!ctype_alpha($language)) {

    }

    $params['command'] = "V";
    $params['amount'] = $amount;
    $params['currency'] = $currency;
    $params['description'] = $description;
    $params['language'] = $language;
    $params['msg_type'] = "SMS";
    $params['order_id'] = $order;
    if (filter_input(INPUT_SERVER, 'REMOTE_ADDR') != null) {
        $params['client_ip_addr'] = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
    }
    elseif (filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR') != null) {
        $params['client_ip_addr'] = filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
    }
    elseif (filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP') != null) {
        $params['client_ip_addr'] = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
    }
    else {
        $params['client_ip_addr'] = "127.0.0.1";
    }
    $qstring = http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $merchant_handler);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, $qstring);

    curl_setopt($ch, CURLOPT_SSLCERT, $cert);

    curl_setopt($ch, CURLOPT_SSLKEY, $key_pem);

    curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");

//curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $password);

    curl_setopt($ch, CURLOPT_CAPATH, $ca);

    curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");

    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
    $result = curl_exec($ch);
    if (curl_error($ch)) {
        echo curl_error($ch) . "<br>";
        echo "Error code: " . curl_errno($ch);
        curl_close($ch);
    }
    curl_close($ch);
    $trans_ref = explode(' ', $result)[1];
    $trans_ref = urlencode($trans_ref);

    $client_url = $client_handler . "?trans_id=" . $trans_ref;
    header('Location: ' . $client_url);
    exit;

}