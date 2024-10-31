<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_ddeals_import_page()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
	$sql  = "SELECT $stores_table.*, COUNT( $ddeals_table.rg_store_id ) as total_deals, MAX( $ddeals_table.date ) as last_updated FROM $stores_table ";
	$sql .= "LEFT JOIN $ddeals_table ON $stores_table.rg_store_id = $ddeals_table.rg_store_id ";
	$sql .= "GROUP BY $stores_table.rg_store_id ORDER BY $stores_table.title";
	$stores = $wpdb->get_results($sql);
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Import Daily Deals</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="text-right">You can filter by RG ID, Title, Last Imported, Count.</div>
		<table id="ddeals_admin_screen" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>RG ID</th>
					<th>Store Logo</th>
					<th>Title</th>
					<th>Last Imported</th>
					<th>Number of Deals</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>RG ID</th>
					<th>Store Logo</th>
					<th>Title</th>
					<th>Last Imported</th>
					<th>Number of Deals</th>
					<th>Actions</th>
				</tr>
			</tfoot>
		</table>
	</div><?php
}
?>