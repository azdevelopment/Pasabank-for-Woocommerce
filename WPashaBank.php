<?php
/*
Plugin Name: Pashabank
Description: Pashabank for wordpress.
Version: 1.0
Author: development.az
*/
define('pasha_logo', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/');
add_action('plugins_loaded', 'init_your_gateway_class', 204);
function init_your_gateway_class()
{
    /**
     * Class WPashaBank
     * @property $id
     */
    class WPashaBank extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id = 'pashabank';
            $this->icon = pasha_logo . 'logo-bank.png'; 
            $this->title = $this->get_option('title');
            $this->has_fields = false;
            $this->method_title = 'PashaBank ödəniş';
            $this->method_description = 'PashaBank ilə ödəniş';
            $this->init_form_fields();
            $this->init_settings();
            add_action( 'woocommerce_api_' . strtolower( get_class( $this ) ), array( $this, 'check_pashabank_response' ) );
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));
        }

        /**
         * Receipt Page
         **/
        function receipt_page($order)
        {
                    echo '<p>' . __('Thank you for your order, please click the button below to pay with Pashabank Money.', 'wc-pashabank') . '</p>';
                    echo $this->generate_form($order);

        }

        function generate_form($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);
            return ' 
            <!DOCTYPE html> <html> <style type="text/css"> form { margin: 0 auto; width: 400px; }
            form div + div { margin-top: 1em; } label { display: inline-block; width: 90px; text-align: right; } input, 
            select { width: 200px; } .button { padding-left: 90px; } button { margin-top: 1em; align: center; margin-left: 2em; }
             </style> <title>Test purchase</title> <body> <h1 align="center">Pasha Bank </h1> 
             <form name="returnform" method="post" action="/payment.php">
    
              <input type="hidden" name="amount" value="' . $order->order_total . '">
               <input type="hidden" name="order_id" value="' . $order_id . '"> 
              <input type="hidden" value="944" name="currency"> 
               <input type="hidden" name="language" value="en">
              <input type="hidden" id="description" name="description" value="' . $order->get_customer_ip_address() . '"> 
              <div class="button">
                 <button type="submit" id="submit_yandexmoney_payment_form" name="submit">Submit payment</button> </div> </form> </body>
                 </html>
                 <script>
                 		jQuery(function(){
					jQuery("body").block({
						message: "'.__('Thank you for your order. We are now redirecting you to PashaBank Payment Gateway to make a payment.', 'wc-yandexmoney').'",
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
					</script>
            ';
        }

        function init_form_fields()
        {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'wc-pashabank'),
                    'type' => 'checkbox',
                    'label' => __('Enable Pashabank Payment', 'wc-pashabank'),
                    'default' => 'yes'
                ),
                'title' => array(
                    'title' => __('Title', 'wc-pashabank'),
                    'type' => 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'wc-pashabank'),
                    'default' => __('Cheque Payment', 'wc-pashabank'),
                    'desc_tip' => true,
                ),
                'description' => array(
                    'title' => __('Customer Message', 'wc-pashabank'),
                    'type' => 'textarea',
                    'default' => ''
                )
            );
        }

        /**
         * Admin Panel Options
         * - Options for bits like 'title' and availability on a country-by-country basis
         **/
        public function admin_options()
        {
            echo '<h3>' . __('Pashabank', 'wc-pashabank') . '</h3>';
            echo '<p>' . __('Pashabank works by sending the user to Pashabank to enter their payment information.', 'wc-pashabank') . '</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        /**
         * @param int $order_id
         * @return array
         */
        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);

            $checkout_payment_url = $order->get_checkout_payment_url(true);

            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => add_query_arg(
                    'order',
                    $order->id,
                    add_query_arg(
                        'key',
                        $order->order_key,
                        $checkout_payment_url
                    )
                )
            );
        }

        function check_pashabank_response()
        {
          echo "hello world";
        }

    }

    function add_your_gateway_class($methods)
    {
        $methods[] = 'WPashaBank';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_your_gateway_class');
}