<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_ddeals_banner_import_page()
{
	global $wpdb;
	$banner_table = $wpdb->prefix.'rg_banner';
	$project_table = $wpdb->prefix.'rg_projects';
	$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
	$count_banner = $wpdb->get_results($sql1);
	$sql2 = "SELECT *FROM $project_table WHERE project LIKE 'Banners UK';";
	$project_detail = $wpdb->get_row($sql2, ARRAY_A);
	//pre($project_detail);
	$rows = $wpdb->num_rows;
	$qry_response = '';
	if( !empty ( $rows ) )
	{ 
		$sub_id = $project_detail['subcription_id'];
		$qry_response = "<div class='panel-white mgBot'>";
		$qry_response .= "<p><b>Your Daily Deals subscription is ".$project_detail['status'].".  </b><img  class='tick-icon' src=".RGDDEALS__PLUGIN_URL. 'admin/images/ticks_icon.png'." /> </p>"; 
		$qry_response .= "<p><b>Name = </b>".$project_detail['user_name']."</p>";
		$qry_response .= "<p><b>Project = </b>".$project_detail['project']."</p>";
		$qry_response .= "<p><b>Email = </b>".$project_detail['email']."</p>";
		$qry_response .= "<p><b>Expiry Date =</b> ".date("d-M-Y", strtotime( $project_detail['expiry_date'] ))."</p>";
		$qry_response .= "</div>";
	}
	?><div class="rg-admin-container">
		<h1 class="rg-admin-heading ">Import RevGlue Banners</h1>
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
		<h3>RevGlue Banners Data Set</h3>
		<div class="sub_page_table">
			<table class="widefat revglue-admin-table">
				<thead>
					<tr>
						<th style="width:15%;">Data Type</th>
						<th style="width:25%;">No. of Banners</th>
						<th style="width:20%;">Action</th>
					</tr>	
				</thead>
					<tr>
						<td>Banners</td>
						<td><span id="rg_banner_count"><?php esc_html_e( $count_banner[0]->banner ); ?></span></td>
						<td class="store-table">
							<a href='javascript:void(0);' class="rg_ddeals_open_import_popup rg_ddaily_banners">Import</a> | <a href='javascript:void(0);'   class="rg_ddeals_open_delete_popup rg_ddaily_banners">Delete</a>
							<div id="rg_ddeals_import_popup" style="background: #ececec; min-width:350px; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; border: 1px solid #ccc">This request will validate your API key and update current data. 
							Your current data will be removed and updated with latest data set.
							Please click on confirm if you wish to run the process.<br/>
							<a href="rg_banners_import"  id="rg_banner_import" data-rg_banner_import="rg_banners_import" class="rg_ddeals_start_import rg_ddaily_banners" >Import</a> | <a href="javascript:{}" onClick="jQuery('#rg_ddeals_import_popup').hide()">Cancel</a>
							</div>
							<div id="rg_ddeals_delete_popup" style="background: #ececec; right: 5%; margin: 5px 0; padding: 10px; position: absolute; bottom:20px; display:none; border-radius: 8px; min-width:350px; border: 1px solid #ccc">This request will delete all your current data. Please confirm if you wish to run the process. You will have to import again.<br/>
							<a href="javascript:void(0);" data-rg_banner_delete="rg_banners_delete"  id="rg_banner_delete" class="rg_ddeals_start_delete rg_ddaily_banners" >Delete</a> | <a href="javascript:{}" onClick="jQuery('#rg_ddeals_delete_popup').hide()">Cancel</a>
							</div>
						</td>
					</tr>
			</table>
		</div>
		<div id="store_loader" align="center" style="display:none"><img src="<?php echo RGDDEALS__PLUGIN_URL; ?>/admin/images/loading.gif" /></div>
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