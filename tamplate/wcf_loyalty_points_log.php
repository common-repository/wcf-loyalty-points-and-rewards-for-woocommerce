<?php
defined( 'ABSPATH' ) || exit;

/**
 * Adding WP List table class if it's not available.
 */
if (!class_exists('WP_List_Table')) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class Wcf_Customer_List_Log.
 * @see WP_List_Table
 */
class Wcf_Customer_List_Log extends WP_List_Table {
  //define data set for WP_List_Table => data
  //prepare_items

  public function prepare_items(){
$orderby = !empty ($_GET['orderby']) ? trim (sanitize_sql_orderby ($_GET['orderby']) ): "";
$order = !empty ($_GET['order']) ? trim (sanitize_sql_orderby ($_GET['order']) ): "";
//$order = isset($_GET['order']) ? trim ($_GET['order']) : "";
$wcf_datas = $this->wp_list_table_data($orderby, $order);
      $this->items = $wcf_datas;
      //pagination 
$per_page = 10;
$current_page = $this->get_pagenum();
$total_items =COUNT($wcf_datas);

$this->set_pagination_args(array(
"total_items" => $total_items,
"per_page" => $per_page
));
$this->items = array_slice($wcf_datas, (($current_page - 1) * $per_page), $per_page);


      $columns = $this->get_columns();
      $hidden = array();
      $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, $hidden, $sortable);
  }
 public function wp_list_table_data($orderby = '', $order = ''){
  global $wpdb;
// this adds the prefix which is set by the user upon instillation of wordpress
$table_name = $wpdb->prefix . "wcf_loyalty_points_logs";
// this will get the data from your table

  if($orderby == 'order_id' && $order == 'asc'){
    $wcf_retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" ) ;
   
$data = json_decode(json_encode($wcf_retrieve_data), true);
  
  }elseif($orderby == 'order_id' && $order == 'desc'){

    $wcf_retrieve_data =  $wpdb->get_results( "SELECT * FROM $table_name" ) ;
   
    $data = json_decode(json_encode($wcf_retrieve_data), true);
  }else{
    $wcf_retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" ) ;
    
    $data = json_decode(json_encode($wcf_retrieve_data), true);
  }
 
return $data;
 }
 
 //sorting for base on
  public function get_sortable_columns(){
    return array(
			'id'  => array( 'id', false ),
      'order_id'  => array( 'order_id', false ),
      'cus_name'  => array( 'cus_name', false ),
      'email'  => array( 'email', false ),
      'order_amt'  => array( 'order_amt', false ),
      'points_earned'  => array( 'points_earned', false ),
      'datetime'  => array( 'datetime', false )

		);
  }


  //get_columns
  public function get_columns(){
$columns = array(
  "cb" => "<input type='checkbox' />",
  "id" => "ID",
  "order_id" => "Order id",
  "cus_name" => "Customer name",
  "email" => "Email",
  "order_amt" => "Order amt",
  "points_earned" => "Points earned",
  "datetime" => "Date",
 
);
return $columns;
}
//check box set 
public function column_cb ($item){
return sprintf('<input type="checkbox" name="post[]" value="%s"/>', $item['id']);
}
  //column_default
  public function column_default($item, $columns_name){
    $result = '';
switch ($columns_name){
  case 'id':
    $result = $item['id'];
    break;
 case 'order_id':
      $result = $item['order_id'];
      break;
  case 'cus_name':
    $result = $item['cus_name'];
    break;
    case 'email':
      $result = $item['email'];
      break;
      case 'order_amt':
        $result = $item['order_amt'];
        break;
        case 'points_earned':
          $result = $item['points_earned'];
          break;
          case 'datetime':
            $result = $item['datetime'];
            break;
 }
 return $result; 
}

//code for add edit and delete button 

}

function Wcf_List_Table(){
  $wcf_list_table = new Wcf_Customer_List_Log();
  ?>
  <div class="wrap">
		<h2><?php esc_html_e( 'All LOG', 'admin-table-tut' ); ?></h2>
		<form id="wcf_log_data" method="GET">
			<input type="hidden" name="page" value="" />

			<?php
			$wcf_list_table->prepare_items();
		//	$wcf_list_table->search_box( 'Search', 'search' );
			$wcf_list_table->display();
}
      Wcf_List_Table();
			?>
		</form>
	</div>