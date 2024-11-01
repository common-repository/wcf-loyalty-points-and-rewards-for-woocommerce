<?php
global $woocommerce;
if(isset($_POST['submit_rewards'])) {
	if(!empty($_POST['enable_loyalty'])) {
		update_option('wcf_loyalty_enable_loyalty_points', '1');
	} else {
		update_option('wcf_loyalty_enable_loyalty_points', '0');
	}
	if(!empty($_POST['pur_th_points'])) {
		update_option('wcf_loyalty_enable_loyalty_purchase_via_point', '1');
	} else {
		update_option('wcf_loyalty_enable_loyalty_purchase_via_point', '0');
	}
	update_option('wcf_loyalty_points_conv_points', sanitize_textarea_field($_POST['conv_points']));
	update_option('wcf_loyalty_points_conv_money', sanitize_textarea_field($_POST['conv_money']));
	update_option('wcf_loyalty_points_calc_method', sanitize_textarea_field($_POST['points_calc']));
	update_option('wcf_loyalty_points_distribution_value', sanitize_textarea_field($_POST['points_bb']));
	update_option('wcf_loyalty_points_expiration_days', sanitize_textarea_field($_POST['points_exp']));
}
?>
<div id="Home" class="tabcontent logo-container">
	<div class="container  my-4">
		<h4>WCF Loyalty Points And Rewards</h4>
		<button onclick="location.href='/wp-admin/admin.php?page=wcf-loyalty-rewards-log'"  type="button" class="wcf_logs btn btn-primary">Logs</button>
		
		<hr>
		<form id="wcf_rewards" method="POST" action="" enctype="multipart/form-data">
			<section class="border p-3">
				<div>
					<?php
					if(get_option('wcf_loyalty_enable_loyalty_points') == '0') { ?>
						<input type="checkbox" id="enable_loyalty" name="enable_loyalty" >
					<?php } else {?>
						<input type="checkbox" id="enable_loyalty" name="enable_loyalty" checked>
					<?php }
					?>
					<label for="enable_loyalty">Enable Loyality Points  &#38; Rewards</label>
				</div>
				</section><br>
			<h5>Enbale Purchase Through Points</h5>
			<section class="border p-3">
				<div>
					<?php
					if(get_option('wcf_loyalty_enable_loyalty_purchase_via_point') == '0') { ?>
						<input type="checkbox" id="pur_th_points" name="pur_th_points" >
					<?php } else {?>
						<input type="checkbox" id="pur_th_points" name="pur_th_points" checked>
					<?php }
					?>
					<label for="pur_th_points">Purchase Products Through Points</label>
				</div>
			</section>
			<section class="border p-3">
				<input type="text" id="conv_points" name="conv_points" <?php if(!empty(get_option('wcf_loyalty_points_conv_points'))) { echo esc_html( 'value='.get_option('wcf_loyalty_points_conv_points')); } ?>> Points = <?php echo esc_html( get_woocommerce_currency_symbol() ); ?>
				<input type="text" id="conv_money" name="conv_money" <?php if(!empty(get_option('wcf_loyalty_points_conv_money'))) { echo esc_html( 'value='.get_option('wcf_loyalty_points_conv_money'));} ?>> </section><br>
			<h5>Point Calculation</h5>
			<section class="border p-3">
				<div class="flex-for-tool">
					<select class="form-control" id="points_calc" name="points_calc">
						<option name="point_fixed" value="point_fixed" <?php if(get_option('wcf_loyalty_points_calc_method') == 'point_fixed') { echo 'selected';} ?>>Fixed Points</option>
						<option name="point_perc" value="point_perc" <?php if(get_option('wcf_loyalty_points_calc_method') == 'point_perc') { echo 'selected';} ?>>Percentage</option>
						<option name="point_order" value="point_order" <?php if(get_option('wcf_loyalty_points_calc_method') == 'point_order') { echo 'selected';} ?>>Dynamic</option>
					</select>
					<div class="tooltips"><span class="dashicons dashicons-editor-help"></span>
					  <span class="tooltiptext">
						  <ul>
							  <li>
								  <strong>Fixed Points:</strong>
								  Add fixed number of points for every purchase.
							  </li>
							  <li>
								  <strong>Percentage:</strong>
								  Add points in percentage of total order amount.
							  </li>
							  <li>
								  <strong>Dynamic:</strong>
								  Add x number of points for every <?php echo get_woocommerce_currency_symbol(); ?> spent.
							  </li>
						  </ul>
						</span>
					</div>
				</div>
				<br>
				<input type="text" name="points_bb" <?php if(!empty(get_option('wcf_loyalty_points_distribution_value'))) { echo 'value='.esc_html(get_option('wcf_loyalty_points_distribution_value'));} ?>> Points
				<br><br>
				<label for="points_expiry">Points Expiration: </label>
				<input type="text" id="points_expiry" name="points_exp" <?php if(!empty(get_option('wcf_loyalty_points_expiration_days'))) { echo 'value='.esc_html(get_option('wcf_loyalty_points_expiration_days'));} ?>> Days
				<br>
				<br>
				<button type="submit" class="btn btn-primary" name="submit_rewards">Save</button>
			</section>
		</form>

	<?php 
		
		echo "
		<section class='border p-3'>
		<div class='row'>
   
		
		  <div class='col-sm-6' ><h4 class='wcf_test_code'>shortcode for log table </h4></div>
		  <div class='col-sm-6' ><span> [wcf_rewads_log_table] </span></div>
		</div>
	
	
		
		 </section>
		 "; ?>
	</div>
</div>