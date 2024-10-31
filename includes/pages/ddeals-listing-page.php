<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function rg_ddeals_listing_page()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
	$categories_table = $wpdb->prefix.'rg_categories';
	
	if( isset($_REQUEST['deal_id']) )
	{
		if( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'approve' )
		{
			$wpdb->update( 
				$ddeals_table, 
				array( 
					'status' => 'active'
				), 
				array( 'rg_id' =>  sanitize_text_field($_REQUEST['deal_id'])  ) 
			);				
		} else if( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'disapprove' )
		{
			$wpdb->update( 
				$ddeals_table, 
				array( 
					'status' => 'inactive'
				), 
				array( 'rg_id' => sanitize_text_field($_REQUEST['deal_id']) )
			);		
		}
	}
	
	
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Daily Deals</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="text-right">You can filter by Title, Brand, City, Price, RRP, Category id.</div>
		<table id="ddeals_admin_screen_listing" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>RG ID</th>
						<th>Title</th>
						<th>Product Image</th>
						<th>Deeplink</th>
						<th>Brand</th>
						<th>City</th>
						<th>Price</th>
						<th>RRP</th> 
						<th>Category ID</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>RG ID</th>
						<th>Title</th>
						<th>Product Image</th>
						<th>Deeplink</th>
						<th>Brand</th>
						<th>City</th>
						<th>Price</th>
						<th>RRP</th> 
						<th>Category ID</th>
					</tr>
				</tfoot>
				
			</table>
	 </div><?php
}
?>