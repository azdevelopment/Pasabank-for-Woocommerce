<?php require_once('wp-load.php'); ?>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$ca =$_SERVER['DOCUMENT_ROOT']."/PSroot.pem";
$key = $_SERVER['DOCUMENT_ROOT']."/rsa_key_pair.pem";
$cert =  $_SERVER['DOCUMENT_ROOT']."/certificate.0017073.pem";
$merchant_handler = "https://ecomm.pashabank.az:18443/ecomm2/MerchantHandler";
$trans_id = filter_input(INPUT_POST, 'trans_id');
// Опциональный параметр платежа amount, может быть использован для частичного
// реверсала, если его значение меньше оригинала
$order_id = filter_input(INPUT_POST, 'order_id');
if (strlen($trans_id) != 20 ||
    base64_encode(base64_decode($trans_id)) != $trans_id) {
    // error
}
$params['command'] = "C";
$params['trans_id'] = $trans_id;
$params['order_id'] = $order_id;
// IP адрес Клиента
if (filter_input(INPUT_SERVER, 'REMOTE_ADDR') != null) {
    $params['client_ip_addr'] = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
} elseif (filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR') != null) {
    $params['client_ip_addr'] =
        filter_input(INPUT_SERVER, 'HTTP_X_FORWARDED_FOR');
} elseif (filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP') != null) {
    $params['client_ip_addr'] = filter_input(INPUT_SERVER, 'HTTP_CLIENT_IP');
} else {
    // should never happen
    $params['client_ip_addr'] = "10.10.10.10";
}

$qstring = http_build_query($params);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $merchant_handler);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $qstring);

curl_setopt($ch, CURLOPT_SSLCERT, $cert);

curl_setopt($ch, CURLOPT_SSLKEY, $key);

curl_setopt($ch, CURLOPT_SSLKEYTYPE, "PEM");

curl_setopt($ch, CURLOPT_CAPATH, $ca);

curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");

curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
$result = curl_exec($ch);
curl_close($ch);
$r_hm = array();
$r_arr = array();
$r_arr = explode("\n", $result);

for ($i = 0; $i < count($r_arr); $i++) {
    $param = explode(":", $r_arr[$i])[0];
    $value = substr(explode(":", $r_arr[$i])[1], 1);
    $r_hm[$param] = $value;
}
global $woocommerce;
$order = new WC_Order( $order_id );
if ($r_hm["RESULT"] == "OK") {

    if ($r_hm["RESULT_CODE"] == "000") {  
        $order->payment_complete();
        $order->update_status('completed', 'payment success');
        $order->add_order_note(__('Payment successfully paid through PashaBank.', 'wc-pashabank'));
        $woocommerce -> cart -> empty_cart();
         wp_redirect( (new WC_Payment_Gateway_CC())->get_return_url($order));

    } else {
        $order->add_order_note(__('Payment is not paid through PashaBank.', 'wc-pashabank'));
        wc_add_notice( __('Payment error:', 'wc-pashabank') . 'Payment is not paid through PashaBank', 'error' );
        wp_redirect( (new WC_Payment_Gateway_CC())->get_return_url($order) );
    }
} elseif ($r_hm["RESULT"] == "FAILED") {
    $order->add_order_note(__('Payment is not paid through PashaBank.', 'wc-pashabank'));
    get_header();
    echo $r_hm["RESULT_CODE"].'    <script>
                 		jQuery(function(){ 
					jQuery("body").block({
						message: "'.__('Payment is not paid through PashaBank..', 'wc-pashabank').'",
						overlayCSS: {
							background		: "#fff",
							opacity			: 0.6
						},
						css: {
							padding			: 20,
							textAlign		: "center",
							color			: "#555",
							border			: "3px solid #aaa",
							backgroundColor	: "#fff",
							cursor			: "wait",
							lineHeight		: "32px"
						}
					});
					jQuery("#submit_yandexmoney_payment_form").click();});
					</script>';
   get_footer();

}else {
    $order->add_order_note(__('Payment is not paid through PashaBank.', 'wc-pashabank'));
    get_header();
    echo $r_hm["RESULT_CODE"]. '    <script>
                 		jQuery(function(){
					jQuery("body").block({
						message: "'.__('Payment is not paid through PashaBank..', 'wc-pashabank').'",
						overlayCSS: {
							background		: "#fff",
							opacity			: 0.6
						},
						css: {
							padding			: 20,
							textAlign		: "center",
							color			: "#555",
							border			: "3px solid #aaa",
							backgroundColor	: "#fff",
							cursor			: "wait",
							lineHeight		: "32px"
						}
					});
					jQuery("#submit_yandexmoney_payment_form").click();});
					</script>';
    get_footer();
 }


