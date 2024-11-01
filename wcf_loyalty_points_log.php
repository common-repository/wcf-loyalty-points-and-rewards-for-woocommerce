<?php
/**
* Retrieve customer’s data from the database
*
* @param int $per_page
* @param int $page_number
*
* @return mixed
*/
function get_customers( $per_page = 5, $page_number = 1 ) {
global $wpdb;
$table_name = $wpdb->prefix . "wcf_loyalty_points_logs";
$sql  = $wpdb->get_results( "SELECT * FROM $table_name" );
if ( ! empty(sanitize_sql_orderby( $_REQUEST['orderby'] ) ) ) {
$sql .= ' ORDER BY ' . sanitize_sql_orderby ( $_REQUEST['orderby'] );
$sql .= ! empty(sanitize_sql_orderby( $_REQUEST['order'] ) )? ' ' . sanitize_sql_orderby ( $_REQUEST['order'] ) : ' ASC';
}
$sql .= " LIMIT $per_page";
$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
$result = $wpdb->get_results( $sql, 'ARRAY_A' );
return $result;
}
?>