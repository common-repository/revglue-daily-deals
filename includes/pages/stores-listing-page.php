<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

function rg_ddeals_store_listing_page()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	
	$sql = "SELECT *FROM $stores_table";
	$stores = $wpdb->get_results($sql);
	
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Stores</h1>
		<div style="clear:both;"></div>
		<hr/>
		<div class="text-right">You can filter by RG ID, Network, MID, Name, Country.</div>
		<table id="stores_admin_screen" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>RG ID</th>
					<th>Network</th>
					<th>MID</th>
					<th>Logo</th>
					<th>Name</th>
					<th>Country</th>
					<th>Affiliate network link</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>RG ID</th>
					<th>Network</th>
					<th>MID</th>
					<th>Logo</th>
					<th>Name</th>
					<th>Country</th>
					<th>Affiliate network link</th>
				</tr>
			</tfoot>
			<tbody><?php        
				foreach ( $stores as $single_store ) 
				{
					?><tr class="ui-state-default">
						<td>
							<?php esc_html_e( $single_store->rg_store_id ); ?>
						</td>
						<td>
							<?php esc_html_e( $single_store->affiliate_network ); ?>
						</td>
						<td>
							<?php esc_html_e( $single_store->mid ); ?>
						</td>
						<td>
							<div class="revglue-banner-thumb"><img class="revglue-unveil" src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/loading.gif" data-src=<?php echo esc_url( $single_store->image_url ); ?> /></div>
						</td>
						<td>
							<?php esc_html_e( $single_store->title ); ?>
						</td>
						<td>
							<?php esc_html_e( $single_store->store_base_country ); ?>
						</td>
						<td class="store-table">
							<!-- <a class="rg_store_link_pop_up" id="<?php esc_html_e( $single_store->rg_store_id )  ?>"  href="<?php echo esc_url( str_replace("subid-value", "",$single_store->affiliate_network_link )); ?>" target="_blank">
								<img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/linkicon.png" style="width:50px;"/> 
							</a> -->

							<a class="rg_store_link_pop_up" id="<?php esc_html_e( $single_store->rg_store_id )  ?>" title="<?php echo esc_url( str_replace("subid-value", "",$single_store->affiliate_network_link )); ?>" href="<?php echo esc_url( str_replace("subid-value", "",$single_store->affiliate_network_link )); ?>" target="_blank">
								<img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/linkicon.png" style="width:50px;"/> </a>
						</td>
					</tr>
					<?php 
				} 
			?></tbody>
		</table>
	</div><?php
}
?>