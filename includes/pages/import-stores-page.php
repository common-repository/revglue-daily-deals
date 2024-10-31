<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_ddeals_store_import_page()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$sql_1 = "SELECT MAX(date) FROM $categories_table";
	$last_updated_category = $wpdb->get_var($sql_1);
	$last_updated_category = ( $last_updated_category ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_category ) ) : '-' );
	$sql = "SELECT MAX(date) FROM $stores_table";
	$last_updated_store = $wpdb->get_var($sql);
	$last_updated_store = ( $last_updated_store ? date( 'l , d-M-Y , h:i A', strtotime( $last_updated_store ) ) : '-' );
	$sql_2 = "SELECT count(*) as categories FROM $categories_table";
	$count_category = $wpdb->get_results($sql_2);
	$sql_3 = "SELECT count(*) as stores FROM $stores_table";
	$count_store = $wpdb->get_results($sql_3);
	$sql4 = "SELECT *FROM $project_table WHERE project LIKE 'Daily Deals UK'";
	$project_detail = $wpdb->get_results($sql4);
	// pre($project_detail);
	$rows = $wpdb->num_rows;
	$qry_response = '';
	if( !empty ( $rows ) )
	{
			if($project_detail[0]->expiry_date=="Free" && $project_detail[0]->partner_iframe_id!="" )
		{
			// echo "0";
		$sub_id = $project_detail[0]->subcription_id;
		$qry_response = "<div class='panel-white mgBot'>";

		$qry_response .= "<p><b>Your RevEmbed Free Daily Deals subscription is ". $project_detail[0]->status. ". &nbsp;</b><img  class='tick-icon' src=".RGDDEALS__PLUGIN_URL. "admin/images/ticks_icon.png"." /></p>";
		$qry_response .= "<p><b>Name = </b> RevEmbed Data </p>";

		$qry_response .= "<p><b>Project = </b>".$project_detail[0]->project." </p>";

		$qry_response .= "<p><b>Email = </b>".$project_detail[0]->email."</p>";

		$qry_response .= "</div>";

		}
		else{ 
			// echo "1";
		$sub_id = $project_detail[0]->subcription_id;
		$qry_response = "<div class='panel-white mgBot'>"; 
		$qry_response .= "<p><b>Your Daily Deals subscription is ".$project_detail[0]->status.".  </b><img  class='tick-icon' src=".RGDDEALS__PLUGIN_URL. 'admin/images/ticks_icon.png'." /> </p>"; 
		$qry_response .= "<p><b>Name = </b>".$project_detail[0]->user_name."</p>";
		$qry_response .= "<p><b>Project = </b>".$project_detail[0]->project."</p>";
		$qry_response .= "<p><b>Email = </b>".$project_detail[0]->email."</p>";
		$qry_response .= "<p><b>Expiry Date =</b> ". date("d-M-Y", strtotime( $project_detail[0]->expiry_date ))."</p>";
		$qry_response .= "</div>";

		}

	}
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Import RevGlue Stores</h1>
		<div style="clear:both;"></div>
		<hr/>
		<form id="subscription_form" method="post">
			<table class="inline-table">
				<tr>
					<td style="text-align:right;padding-right: 10px;">
						<label>Subscription ID:</label>
					</td>
					<td>
						<input id="rg_ddeal_sub_id" type="text" name="rg_ddeal_sub_id" class="regular-text revglue-input lg-input">
					</td>
					<td style="text-align:right;padding-right: 10px;">
						<label >RevGlue Email:</label>
					</td>
					<td>
						<input id="rg_ddeal_sub_email" type="text" name="rg_ddeal_sub_email" class="regular-text revglue-input lg-input">
					</td>
					<td style="text-align:right;padding-right: 10px;">
						<label >RevGlue Password:</label>
					</td>
					<td>
						<input id="rg_ddeal_sub_password" type="password" name="rg_ddeal_sub_password" class="regular-text revglue-input lg-input">
					</td>
					<td style="text-align:right;padding-right: 10px;">
						<button id="rg_ddeal_sub_activate" class="button-primary float-left" style="margin-right:5px;">Validate Account</button>
					</td>	
				</tr>
				<tr>
					<td colspan="7">
						<span id="subscription_error"></span>
					</td>
				</tr>
			</table>
		</form>
		<div id="sub_loader" align="center" style="display:none"><img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/loading.gif" /></div>
		<hr>
		<div id="subscription_response"><?php echo $qry_response; ?></div>
		<h3>RevGlue Stores Data Set</h3>
		<div class="sub_page_table">
			<table class="widefat revglue-admin-table">
				<thead>
					<tr>
						<th style="width:15%;">Data Type</th>
						<th style="width:25%;">No. of Entries</th>
						<th style="width:40%;">Last Updated</th>
						<th style="width:20%;">Action</th>
					</tr>	
				</thead>
					<tr>
						<td>Stores</td>
						<td class="store-counts" >
							<span id="rg_store_count"><?php esc_html_e( $count_store[0]->stores ); ?>
								
							</span>
						</td>
						<td class="store-dates">
							<span id="rg_store_date"><?php  echo $last_updated_store =="" ? "-": esc_html_e( date('l , d-M-Y , h:i A', strtotime($last_updated_store)) ) ; ?>
								
							</span>
						</td>
						<td class="store-table">
							<div class="store-import-links">
								<a href='rg_stores_import' class="rg_ddeals_open_import_popup">Import</a> | <a href='rg_stores_delete' class="rg_ddeals_open_delete_popup">Delete</a>
								
							</div>
							<div id="store_import_loader" align="center" style="display:none">
								<img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/loading.gif"> please wait..
							</div>
							
							
							<div id="rg_ddeals_import_popup" style="background: #ececec; min-width:350px; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; border: 1px solid #ccc">This request will validate your API key and update current data. 
							Your current data will be removed and updated with latest data set.
							Please click on confirm if you wish to run the process.<br/>
							<a href="" id="rg_ddeal_import" class="rg_ddeals_start_import">Import</a> | <a href="javascript:{}" onClick="jQuery('#rg_ddeals_import_popup').hide()">Cancel</a>
							</div>
							


							<div id="rg_ddeals_delete_popup" style="background: #ececec; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; min-width:350px; border: 1px solid #ccc; z-index: 9999;">This request will delete all your current data. Please confirm if you wish to run the process. You will have to import again.<br/>

							<a href="rg_stores_delete" id="rg_stores_delete"  class="rg_ddeals_start_delete">Delete</a> | <a href="javascript:{}" onClick="jQuery('#rg_ddeals_delete_popup').hide()">Cancel</a>
							</div>
							
						</td>
					</tr>	
					<tr>
						<td>Deal Categories</td>
						<td><span id="rg_category_count"><?php esc_html_e( $count_category[0]->categories ); ?></span></td>
						<td><span id="rg_category_date"><?php  echo $last_updated_category =="" ? "-": esc_html_e( date('l , d-M-Y , h:i A', strtotime($last_updated_category)) ) ; ?></span></td>
						<td>
						<div class="categories-import-links">
							<a href='rg_categories_import' class="rg_ddeals_open_import_popup">Import</a> | <a href='rg_categories_delete' class="rg_ddeals_open_delete_popup">Delete</a>
						</div>
						
						<div id="category_import_loader" align="center" style="display:none">
								<img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/loading.gif"> please wait..
							</div>
							
						</td>
					</tr>
			</table>
		</div>
		
	</div>
	<div class="panel-white">
			<h4>Setup Auto Import</h4>
			<p>If you wish to setup auto import of RevGlue Stores Data then go to your server panel and setup CRON JOB. Your server may ask you path for the file to setup. The file path for auto data update is provided below. Import time will depand upon the size of data and server time out.</p> 
		</div>
		<table class="form-table">
			<tr>
				<th><label title="File Path">File Path:</label></th>
				<td><input type="text" class="regular-text revglue-input lg-input" value="<?php echo site_url() . '/revglue-daily-deals/auto_import_data/'; ?>">
				  </td>
			  </tr>
		</table>
	</div>
	<?php
}
?>