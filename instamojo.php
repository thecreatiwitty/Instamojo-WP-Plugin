<?php

/*
Plugin Name: WP-Instamojo (Based on WooCommerce)
Author: Siddhesh Tamhanekar
Email: tamhanekar.siddhesh95@gmail.com
Version: 1.0.0
Description: WP-Instamojo helps you seamlessly integrate the popular Instamojo Payment Gateway to your WooCommerce based website. Payment collection was never so easy with WordPress.
*/


# add meta rows in below plugin
add_filter( 'plugin_row_meta', 'custom_plugin_row_meta', 10, 2 );
function custom_plugin_row_meta( $links, $file ) {

	if ( strpos( $file, 'instamojo.php' ) !== false ) {
		$new_links = array(
					'At <a href="http://www.thecreatiwitty.com" target="_blank"> Creatiwitty Designs Solutions</a>',
					'<a href="mailto:support@thecreatiwitty.com">Support</a>'
				);
		
		$links = array_merge( $links, $new_links );
	}
	
	return $links;
}

# add the settings link at plugin actions
add_filter('plugin_action_links_'.plugin_basename(__FILE__),'instamojo_new_links');
 function instamojo_new_links($links)
 {	
	$mylinks = array(
		'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_instamojo' ) . '">Settings</a>'
		);
	return array_merge( $links, $mylinks );
 }

# activate session
function session_init() {
	if (!session_id()) 
	{
		session_start();
	}
}
add_action( 'init', 'session_init' );

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here

add_action( 'plugins_loaded', 'instamojo_class_init' );
function instamojo_class_init(){
		
	class WC_Gateway_Instamojo extends WC_Payment_Gateway {
	
		public function __construct(){
			$this->id                 = 'instamojo';
			$this->has_fields         = false;
			$this->order_button_text  = __( 'Proceed to Pay', 'instamojo' );
			$this->icon = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAABHNCSVQICAgIfAhkiAAAAeZJREFUOI19krtuE0EUhv9zdmd3LYSECEi20WJBBVTAM8RGioREFwrAtNBQ2E4DNU3WFNSQyJYtpUsaF7kg3gAQUPACdoQgEsQ49to7cyiIE+2uzUinmTPfN/9cKFfZvkfEzwgYYNYgUpGYyveg9HVmGwAuVXY3OHNmWSYhBJLgGaInQ2PM7f36nW9JAaPTetkNivfN8KgBywKMjpXoCcCcIeZP2ZXd62mB0BV02ofd+uJjGYVNUl46pzEgYpcFH5ISBmEMzz2LTvtH91WxLGHYmCkRA2L2khIGAGgNWNYFdFo//yUZNUm585J4LPiYq2xfOxWcSOyF0yTjBjkZgO14EYNtxyXmL/nazk07tsNJkvZBd2lxIV/d+0UkN4SgE6cBAbaAV+KC45jwvPN41yjzgXorF8e3mEgnlwmEyYgXFxAByga4/8BvXv0jOflMcIHE3wAIbCmYcPDcTsHOUbmwVhhE2WgL2gCShsl2oMN+tbdaqvPxHGDbgBo98t8UfuscNiHzYAUzCWu91VJ9+goEpQA1fFhY9/smjy0x+j/wuNYLisF0lkHkQA6f+muX+1FWNiHzYCcFT8PDf/J+Wc7xhuhoxoUBZCmYKKxOY8d6+erOXYBbINEEmBQNOEbkxX5Qej2jh79RaeQT2vwcPgAAAABJRU5ErkJggg==';
			$this->method_title       = __( 'WP-Instamojo ', 'instamojo' );
			//$this->method_description = ''

			//$this->title = __('Instamojo','instamojo');
				
			$this->init_form_fields();
			$this->init_settings();
			$this->title = $this->get_option( 'title' );
			
			// hook for saving the options of payment gateway
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}
		
		
		public function init_form_fields(){
			
			$checkout_url = site_url()."/checkout/";
			$this->form_fields = array(
				'enabled' => array(
					'title' => __( 'Enable/Disable', 'woocommerce' ),
					'type' => 'checkbox',
					'label' => __( 'Enable Instamojo Payment', 'woocommerce' ),
					'default' => 'yes'
				),
				'title' => array(
					'title' => __( 'Title*', 'woocommerce' ),
					'type' => 'text',
					'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
					'default' => __( 'Instamojo', 'woocommerce' ),
					'desc_tip'      => true,
				),
				'description' => array(
					'title' => __( 'Customer Message', 'woocommerce' ),
					'type' => 'textarea',
					'default' => ''
				),
				'payment_link' =>array(
					'title' => __('Payment Link*','instamojo' ),
					'type' => 'text',
					'default' => '',
					
				),
				'private_salt' =>array(
					'title' => __('Private Salt*','instamojo'),
					'type' => 'text',
					'default' => '',
					'description' =>''
				),
				'api_key' =>array(
					'title' => __('Private Api Key*','instamojo'),
					'type' => 'text',
					'default' => '',
				),
				'auth_token' =>array(
					'title' => __('Private Auth Token*','instamojo'),
					'type' => 'text',
					'default' => '',
				),
				'custom_field' =>array(
					'title' => __('Custom Field*','instamojo'),
					'type' => 'text',
					'default' => '',
				),
				'custom_redirection_url' =>array(
					'title' => __('Custom Redirection Url*','instamojo'),
					'type' => 'text',
					'default' => $checkout_url,
					'desc_tip'=> true,
					'description' => __('Please don\'t change the value in this field. Copy this url and paste into Payment Link\'s Custom Redirection Url field ','Instamojo')
				),
				
			);
		}
		
		function process_payment($order_id)
		{
			global $woocommerce;
			$order = new WC_Order( $order_id );
			$link = $this->get_option('payment_link');
			$custom_field = $this->get_option('custom_field');
			$key = $this->get_option('private_salt');
			
			$amount = $this->get_order_total();
			$billing_email =  $order->billing_email;
			$delivery_name = $order->billing_first_name ." ".$order->billing_last_name;
			$billing_tel = trim($order->billing_phone,"+");
			
			
			if(strlen($delivery_name) >19):
				$name = explode(' ',$delivery_name);
				$name[0] = $name[0][0];
				$delivery_name = implode(" ",$name);
				//print_R($name);
			if(strlen($delivery_name) >19):	
				$delivery_name = substr($delivery_name,0,19);
			endif;
			endif;
			
			//echo $delivery_name;			

			//$amount =0;
			//$auth_no = mdf($amount.$email.$phone);
			$data_arr["data_amount"] = $amount;
			$data_arr["data_name"] = $delivery_name;
			$data_arr["data_phone"] = $billing_tel;
			$data_arr["data_email"] = $billing_email;
			
			$custom_field = "data_".$custom_field;
			$custom_field1= strtolower($custom_field);
			$data_arr[$custom_field1] = $order_id ;
			ksort($data_arr);
			print_R($data_arr);
			$str=implode("|",$data_arr);
			//echo $str;
			//$args.= "&data_readonly=data_postcode&data_postcode=$postal_code";
			$str=hash_hmac("sha1", $str, $key);
			//exit;
			$link.= "?embed=form&";
			$link.="data_readonly=data_email&data_readonly=data_amount&data_readonly=data_phone&data_readonly=data_name&data_readonly={$custom_field}&data_hidden={$custom_field}";
			
			$link.="&data_amount=$amount&data_name=$delivery_name&data_email=$billing_email&data_phone=$billing_tel&{$custom_field}=$order_id&data_sign=$str";
			
			$_SESSION["order_id"] = $order_id;
		//exit;
			return array(
			'result' =>'success',
			'redirect'	=> $link
		);
			
		}
	
	}
	
}



function add_instamojo_in_payment_gateways( $methods ) {
	$methods[] = 'WC_Gateway_Instamojo'; 
	return $methods;
}

add_filter( 'woocommerce_payment_gateways', 'add_instamojo_in_payment_gateways' );


add_action('template_redirect','check_for_checkout_page');

function check_for_checkout_page(){
	global $post;
	if($post->post_content == '[woocommerce_checkout]'):
		if(isset($_GET["payment_id"]) and $_GET["payment_id"] !='' )
		{
		$i = new WC_Gateway_Instamojo();
		$api_key = $i->get_option('api_key');
		$api_token = $i->get_option('auth_token');
		$custom_field = $i->get_option('custom_field');
		
		//echo $api_key . "  ".$api_token."  ".$custom_field;
		$payment_url = 'https://www.instamojo.com/api/1.1/payments/'.$_GET["payment_id"]."/?api_key=$api_key&auth_token=$api_token";
		
		$payment_details = file_get_contents($payment_url);
		//var_dump($payment_details);exit;
		
		if($payment_details){
			
		$payment_details = json_decode($payment_details);
		//echo "<pre>";
		//print_r($payment_details);
				objtoarr($payment_details);
			
			//echo "<pre>";
			//print_r($payment_details);
			//echo $custom_field;
			
			$order_id = $payment_details['payment']['custom_fields'][$custom_field]["value"];
			//echo "<pre>";
			//var_dump($_SESSION);//."<br>";
			//echo $order_id;
			//exit;
			if($_SESSION['order_id'] == $order_id):
				///unset($_SESSION['order_id']);
				
				$order = new WC_Order($order_id);
				//print_R($order);
				global $woocommerce;
				$order->payment_complete($_GET["payment_id"]);
				//print_R($order);
				
				//exit;
				wp_safe_redirect( $i->get_return_url($order));
				else:
			
			
			endif;
			
		}else
		{
		
			if(extension_loaded  ('openssl'))
			{
				echo "pls enable your openssl extension";
			}else
			{	
				$w = stream_get_wrappers();
				if(!in_array('https',$w))
					echo "pls enable https wrapper on your server";
			}
			//$w = stream_get_wrappers();
			//echo 'openssl: ',  extension_loaded  ('openssl') ? 'yes':'no', "\n";
			///echo 'http wrapper: ', in_array('http', $w) ? 'yes':'no', "\n";
			//echo 'https wrapper: ', in_array('https', $w) ? 'yes':'no', "\n";
			//echo '<pre>';
			//echo 'wrappers: ', var_dump($w);
		}
		
		}
	endif;
	
}

function objtoarr(&$obj){
	if(is_object($obj) or is_array($obj)){
	foreach($obj as &$val):
		if(is_object($val)):
			$val = $val;
				objtoarr($val);
		endif;
	endforeach;
	$obj = (array)$obj;
	}
}
}
else
{
	function my_admin_notice() {
    ?>
    <div class="updated">
        <p><?php _e( '<b>WP-Instamojo</b> Plugin requires WooCommerce to be Installed First!', 'my-text-domain' ); ?></p>
    </div>
    <?php
}
add_action( 'admin_notices', 'my_admin_notice' );
}
