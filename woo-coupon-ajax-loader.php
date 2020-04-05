<?php
/**
 * Plugin Name: Woo Coupon AJAX Loader
 * Plugin URI: https://limoncello.design/woo-coupon-ajax-loader
 * Description: Creates a shortcode that enables to load selected coupons to cart
 * Version: 1.0.0
 * Author: DoroNess
 * Author URI: https://limoncello.design/
 * License: GPLv2 or later
 */

 /*
    TODO:https://pippinsplugins.com/shortcodes-101-using-template-files-better-shortcodes/
    TODO:https://docs.woocommerce.com/document/create-a-plugin/
 */

function ajx_coupon_loader_activated () {
    /*
        TODO:Dispaly activation massage
        https://tommcfarlin.com/plugin-activation-message/

        
        //TODO:Check if woo installed
            https://docs.woocommerce.com/document/create-a-plugin/    
            https://wordpress.stackexchange.com/questions/193907/how-to-check-if-a-plugin-woocommerce-is-activate_plugin( $plugin:string, $redirect:string, $network_wide:boolean, $silent:boolean )
        TODO: enqeue scripts and styles
        Use plugins_url( 'myscript.js', _FILE_ );
        https://developer.wordpress.org/plugins/plugin-basics/determining-plugin-and-content-directories/

    */
}
 
register_activation_hook( __FILE__, 'ajx_coupon_loader_activated' );

function ajx_coupon_loader_deactivated() {

}
register_deactivation_hook( __FILE__, 'ajx_coupon_loader_deactivated' );

function ajx_coupon_loader_scripts() {   
    wp_enqueue_script( 'coupon-loader.js', plugin_dir_url( __FILE__ ) . 'js/coupon-loader.js', array('jquery', 'wp-util' ), null, true);
    wp_enqueue_style( 'coupon-loader.css', plugin_dir_url( __FILE__ ) . 'css/coupon-loader.css', null, 'all' );
    wp_localize_script('coupon-loader.js','coupon_loader_data',
    [   
        'security'  => wp_create_nonce( 'coupon-loader-key' ),
        'ajax_url'  => admin_url( 'admin-ajax.php' ),
    ]);
}
add_action('wp_enqueue_scripts', 'ajx_coupon_loader_scripts');




// Create the shortcode | https://generatewp.com/shortcodes/
function coupon_loader_handler( $atts ) {
    
    //load + localize scrtipt
    
    
	// Attributes
	$atts = shortcode_atts(
		array(
			'text'      => 'Click To Apply Coupon',
            'coupon'    => '0',
            'redirect'  => 'no'  
		),
		$atts
    );
    $coupon_code    = $atts["coupon"];
    $btn_txt        = $atts["text"];
    $redirect       = $atts["redirect"];
    $btn_string = "<span class='coupon-loader-btn-wrapper'>
        <button class='coupon-loader-btn coupon-loader-$coupon_code' data-redirect='$redirect' data-coupon='$coupon_code'>$btn_txt</button><span class='coupon-loader-message'></span></span>";
	return $btn_string;
}
add_shortcode( 'coupon-loader', 'coupon_loader_handler' );



//Ajax handler 
function load_the_coupon() {
    //check nonce - check the security value in your call back and handle it appropriately.
    if ( ! check_ajax_referer( 'coupon-loader-key', 'coupon_key', false ) ) {
        wp_send_json_error( 'Invalid security token sent.' );
        wp_die();
    }
    //get coupon data from ajax and implement to cart
    $coupon_code = $_REQUEST["coupon_code"];
    $redirect    = true;
    //handle errors
    if (!$coupon_code)
        wp_send_json_error( 'coupon not set', 404);
    //Add to cart
    WC()->cart->add_discount( $coupon_code );
    // TODO: Check if coupon exists and valid. if not - prevent redirect
    // https://stackoverflow.com/questions/38253877/check-if-woocommerce-coupon-already-exists
    // https://stackoverflow.com/questions/39745791/woocommerce-check-if-coupon-is-valid
    $cart_url = WC()->cart->get_cart_url();  
    //Send response
    $notice = strip_tags(wc_print_notices(true)) ;
    wp_send_json_success( array($notice, $cart_url));
}

add_action('wp_ajax_load_coupon', 'load_the_coupon');
add_action('wp_ajax_nopriv_load_coupon', 'load_the_coupon');

?>
