<!DOCTYPE html>
<html lang="en">
<body>
<div class="container">
 <h3>Total points : <?php 
global $wpdb, $current_user;
wp_get_current_user();
$table_name = $wpdb->prefix . "wcf_loyalty_points";
$wcf_got_useremail = $current_user->user_email;

$current_points = $wpdb->get_var(
		$wpdb->prepare("SELECT `points` FROM `$table_name` WHERE `email` = %s", $wcf_got_useremail)
);
echo (!empty($current_points) ? $current_points : '0' );
 ?> points</h3>
  <table class="table" border="1">
    <thead>
      <tr>
        <th>Order Id</th>
        <th>Product Name</th>
        <th>Amount</th>
        <th>Points earned</th>
       <th>Date</th>
      </tr>
    </thead>
<?php
//global $wpdb;
$table_name = $wpdb->prefix . "wcf_loyalty_points_logs";
$wcf_retrieve_data =$wpdb->get_results("SELECT * FROM $table_name");
$wcf_rewards_log = json_decode(json_encode($wcf_retrieve_data), true);

$order_statuses = array('wc-on-hold', 'wc-processing', 'wc-completed');
$customer_user_id = get_current_user_id();
$customer_orders = wc_get_orders( array(
    'meta_key' => '_customer_user',
    'meta_value' => $customer_user_id,
    'post_status' => $order_statuses,
    'points_earned' => $wcf_rewards_log,
    'numberposts' => -1
) );
foreach($customer_orders as $order ){
    $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
    foreach($order->get_items() as $item_id => $item){
$oid = $item['order_id'];
 $points = $wpdb->get_var( $wpdb->prepare("SELECT `points_earned` FROM `$table_name` WHERE `order_id`= %s" , $oid) );
   
$date = $wpdb->get_var( $wpdb->prepare("SELECT `datetime` FROM `$table_name` WHERE `order_id` = %d" , $oid) );
 
		$ndate = strtotime($date);
      echo  '<tr>
      <td>'.$oid.'</td>
     <td>' .$item['name'].' </td>
     <td>' .$item['total'].' </td>
	 <td>'; echo !empty($points) ? $points : '0'; echo '</td>
     <td>'; echo !empty($date) ? date('Y-m-d',$ndate) : 'No Record'; echo' </td>
            </tr>'; 
    }
}
?> 
  </table>
</div>
</body>
</html>