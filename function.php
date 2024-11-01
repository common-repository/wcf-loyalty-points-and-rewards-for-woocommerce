<?php
function wcf_rewards_referral_enqueue_custom_admin_style() {
	wp_enqueue_style( 'wcf_loyalty_points_custom', plugin_dir_url( __FILE__ ) . 'asset/css/wcf_rewards_style.css', false, '1.0.0' );
	wp_enqueue_style( 'wcf_lo', plugin_dir_url( __FILE__ ) . 'asset/css/bootstrap-min.css', false, '5.1.3' );
}
add_action( 'admin_enqueue_scripts', 'wcf_rewards_referral_enqueue_custom_admin_style' );
function wcf_rewards_point_custom_enqueue_script() {
	wp_enqueue_script( 'wcfcode_script', plugin_dir_url( __FILE__ ) . 'asset/js/wcf_rewards.js', false, '1.0.0');
  
}
add_action('wp_enqueue_scripts','wcf_rewards_point_custom_enqueue_script');
add_action('admin_enqueue_scripts', 'wcf_rewards_point_custom_enqueue_script');

add_action( 'woocommerce_review_order_before_payment', 'wcf_rewards_switch_toggle_fee_on_checkout', 20 );
function wcf_rewards_switch_toggle_fee_on_checkout() {

    echo '<div class="switch-toggle-wrapper">
    <span>' . __("Redeem store points:", "woocommerce") . ' </span>
    <label class="switch">
        <input type="checkbox" name="company_discount" id="company_discount">
        <span class="slider round"></span>
    </label>
    </div>';
}

add_action( 'wp_footer', 'wcf_rewards_checkout_toggle_discount_script' );
function wcf_rewards_checkout_toggle_discount_script() {
    if( is_checkout() && ! is_wc_endpoint_url() ) :

    if( WC()->session->__isset('enable_discount') ) {
        WC()->session->__unset('enable_discount');
    }
    ?>
    <script type="text/javascript">
    jQuery( function($){
        if (typeof wc_checkout_params === 'undefined')
            return false;

        $('form.checkout').on('change', 'input[name="company_discount"]', function(){
            console.log('toggle');
            var toggle = $(this).prop('checked') === true ? '1' : '0';
            $.ajax( {
                type: 'POST',
                url: wc_checkout_params.ajax_url,
                data: {
                    'action': 'enable_discount',
                    'discount_toggle': toggle,
                },
                success: function (result) {
                    $('body').trigger('update_checkout');
                },
            });
        });
    });
    </script>
    <?php
    endif;
}

// Ajax receiver: Set a WC_Session variable
add_action( 'wp_ajax_enable_discount', 'wcf_rewards_checkout_enable_discount_ajax' );
add_action( 'wp_ajax_nopriv_enable_discount', 'wcf_rewards_checkout_enable_discount_ajax' );
function wcf_rewards_checkout_enable_discount_ajax() {
    if ( isset($_POST['discount_toggle']) ) {
        WC()->session->set('enable_discount', esc_attr($_POST['discount_toggle']) ? true : false );
        echo esc_attr($_POST['discount_toggle']);
    }
    wp_die();
}

// Set the discount
add_action( 'woocommerce_cart_calculate_fees', 'wcf_rewards_checkout_set_discount', 20, 1 );
function wcf_rewards_checkout_set_discount( $cart ) {
    if ( ( is_admin() && ! defined('DOING_AJAX') ) || ! is_checkout() )
        return;
	$discount_method = get_option('wcf_loyalty_points_calc_method');
	if($discount_method == 'point_perc') {
		$subtotal   = WC()->cart->get_subtotal();
		$percentage = 10;
		$discount   = $subtotal * $percentage / 100; 

		// Give 10% discount if and when the switch is toggled
		if( WC()->session->get('enable_discount') ) {
			$cart->add_fee( sprintf( __( 'Company Discount (%s)', 'woocommerce'), $percentage .'%' ), -$discount );
		}
	}
}

//add loyalty points hook

add_action( 'woocommerce_checkout_update_order_meta', 'wcf_rewards_add_loyalty_points',  10, 1  );
function wcf_rewards_add_loyalty_points($order_id) {
	global $wpdb , $woocommerce;
	$wcf_currency = get_woocommerce_currency_symbol();
	$order = wc_get_order( $order_id );
	$order_amount = get_post_meta($order_id, '_order_total', true);
	$billing_email = get_post_meta($order_id, '_billing_email', true);
    $billing_first_name = $order->get_billing_first_name();
	$amount = $order->get_total();
	$p_method = get_option('wcf_loyalty_points_calc_method');
	$value = get_option('wcf_loyalty_points_distribution_value');
	$table = $wpdb->prefix.'wcf_loyalty_points';
	if($p_method == 'point_fixed') {
	global $wpdb;
    $wcf_reward_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE email = '$billing_email'") );
		if($wcf_reward_count != 0){
			$current_points = $wpdb->get_var( $wpdb->prepare("SELECT `points` FROM `$table` WHERE `email` = '$billing_email'"));
			$wpdb->update( $wpdb->prepare($table,
			array(
				'points' => $current_points+$value
			),
			array(
				'email' => $billing_email
			)
		));
			
		} else {
			$wpdb->insert($wpdb->prefix."wcf_loyalty_points",
				array(
            	'email'=> $billing_email,
            	'points'=>$value,
            	)
        	);
			
		}
        $wpdb->insert($wpdb->prefix."wcf_loyalty_points_logs",
				array(
					'order_id' => $order_id,
                    'cus_name' => $billing_first_name,
					'order_amt' => " $wcf_currency  $order_amount",
						'email'  => $billing_email,
						'points_earned' => $value
						
					));
	} else if($p_method == 'point_perc') {
		$points = $amount*$value/100;
		global $wpdb;
		$wcf_reward_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE email = '$billing_email'") );
		if($wcf_reward_count == 0) {
			$wpdb->insert( $wpdb->prepare($table,
				array(
            	'email'=> $billing_email,
            	'points'=>$points,
            	)
        	));
		} else {
			$current_points = $wpdb->get_var( $wpdb->prepare("SELECT  (points) as total FROM `$table` WHERE `email` = '$billing_email'"));
			$wpdb->update( $wpdb->prepare($table,
				array(
					'points' => $current_points+$points
				),
				array(
					'email' => $billing_email
				)
			));
		
		}
        	$wpdb->insert($wpdb->prefix."wcf_loyalty_points_logs",
				
			array(
				'order_id' => $order_id,
                 'cus_name' => $billing_first_name,
				'order_amt' => " $wcf_currency  $order_amount",
					'email'  => $billing_email,
					'points_earned' => $points
					
				));
	} else { //dynamic calculation
		$points = $amount*$value;
		global $wpdb;
		$wcf_reward_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM `$table` WHERE email = '$billing_email'"));
     if($wcf_reward_count == 0) {
			$wpdb->insert($table,
				array(
            	'email'=> $billing_email,
            	'points'=>$points,
            	)
        	);
		} else {
			$current_points = $wpdb->get_var( $wpdb->prepare("SELECT  (points) as total FROM `$table` WHERE `email` = '$billing_email'"));
			$wpdb->update( $wpdb->prepare($table,
				array(
					'points' => $current_points+$points
				),
				array(
					'email' => $billing_email
				)
			));
		
		}
        	$wpdb->insert($wpdb->prefix."wcf_loyalty_points_logs",
				
			array(
				'order_id' => $order_id,
                 'cus_name' => $billing_first_name,
				'order_amt' => " $wcf_currency  $order_amount",
					'email'  => $billing_email,
					'points_earned' => $points
					
				));
	}
}
//add menu into my-account page sidebar

add_filter ( 'woocommerce_account_menu_items', 'wcf_rewards_log_history_link', 40 );
function wcf_rewards_log_history_link( $menu_links ){
	
	$menu_links = array_slice( $menu_links, 0, 2, true ) 
	+ array( 'wcf-loyalty-rewards-point' => 'Loyalty Points' )
	+ array_slice( $menu_links, 2, NULL, true );
	
	return $menu_links;

}

add_action( 'init', 'wcf_rewards_loyality' );
function wcf_rewards_loyality() {
	add_rewrite_endpoint( 'wcf-loyalty-rewards-point', EP_PAGES );

}

add_action( 'woocommerce_account_wcf-loyalty-rewards-point_endpoint', 'wcf_my_account_endpoint_content' );
function wcf_my_account_endpoint_content() {
	
	include ('wcf-loyalty-rewards-point.php');

}
//add  point  on  cart page
 $wcf_options = get_option( 'wcf_loyalty_enable_loyalty_points' );
	if($wcf_options == true){
add_action( 'woocommerce_cart_collaterals', 'wcf_loyality_cart_point');
function wcf_loyality_cart_point() {
	// Get cart total
    $cart_total = WC()->cart->get_cart_contents_total();

	$wcf_p_method = get_option('wcf_loyalty_points_calc_method');
	$wcf_point_options = get_option( 'wcf_loyalty_points_distribution_value' );
	//$amount = $order->get_total();
	
	if($wcf_p_method == 'point_fixed'){ ?>
	<div class="woocommerce-info">
		<a href="#" class="showcoupon">Please login to availble <?php echo esc_attr($wcf_point_options) ; ?> point from this order</a>	</div>
<?php
	}elseif($wcf_p_method == 'point_perc'){ 
		$wcf_points = $cart_total*$wcf_point_options/100;
		?>

		<div class="woocommerce-info">
		<a href="#" class="showcoupon">Please login to availble <?php echo esc_attr($wcf_points); ?> point from this order</a>	</div>
<?php	}else{
	$points = $cart_total*$wcf_point_options;
	?>
		<div class="woocommerce-info">
		<a href="#" class="showcoupon">Please login to availble <?php echo esc_attr($points) ;?> point from this order</a>	</div>

<?php	} 
}

}
	//code for shortcode genrate 
	add_shortcode('wcf_rewads_log_table', 'wcf_loyality_rewads_log_table');
	function wcf_loyality_rewads_log_table() {
		
		//include('wcf_loyalty_points_log.php');
		include('wcf-loyalty-rewards-point.php');
	
		
	}

	
	?>