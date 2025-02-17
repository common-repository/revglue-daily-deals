<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
function rg_ddeals_admin_enqueue()
{
	global $hook_suffix;
	// List of Plugin Pages
	$rg_ddeals_hook_suffixes = array(
		'toplevel_page_revglue-dashboard',
		'revglue-daily-deals_page_revglue-import-stores',
		'revglue-daily-deals_page_revglue-import-banners',
		'revglue-daily-deals_page_revglue-import-ddeals',
		'revglue-daily-deals_page_revglue-stores',
		'revglue-daily-deals_page_revglue-categories',
		'revglue-daily-deals_page_revglue-banners',
		'revglue-daily-deals_page_revglue-ddeals'
	);
	// Only enqueue if current page is one of plugin pages
	if ( in_array( $hook_suffix, $rg_ddeals_hook_suffixes ) ) 
	{
		// Enqueue Admin Styles
		wp_register_style( 'rg-ddeals-chosen', RGDDEALS__PLUGIN_URL . 'admin/css/chosen.css' );
		wp_enqueue_style( 'rg-ddeals-chosen' );
		wp_register_style( 'rg-ddeals-confirm', RGDDEALS__PLUGIN_URL . 'admin/css/jquery-confirm.css' );
		wp_enqueue_style( 'rg-ddeals-confirm' );
		wp_register_style( 'rg-ddeals-confirm-bundled', RGDDEALS__PLUGIN_URL . 'admin/css/bundled.css' );
		wp_enqueue_style( 'rg-ddeals-confirm-bundled' );
		wp_register_style( 'rg-ddeals-jqueryui', RGDDEALS__PLUGIN_URL . 'admin/css/jquery-ui.min.css' );
		wp_enqueue_style( 'rg-ddeals-jqueryui' );
		wp_register_style( 'rg-ddeals-main', RGDDEALS__PLUGIN_URL . 'admin/css/admin_style.css' );
		wp_enqueue_style( 'rg-ddeals-main' );
		wp_register_style( 'rg-ddeals-checkbox', RGDDEALS__PLUGIN_URL . 'admin/css/iphone_style.css' );
		wp_enqueue_style( 'rg-ddeals-checkbox' );
		wp_register_style( 'rg-ddeals-datatables', RGDDEALS__PLUGIN_URL . 'admin/css/jquery.dataTables.css' );
		wp_enqueue_style( 'rg-ddeals-datatables' );
		wp_register_style( 'rg-ddeals-fontawesome', RGDDEALS__PLUGIN_URL . 'admin/css/font-awesome.css' );
		wp_enqueue_style( 'rg-ddeals-fontawesome' );
		// Enqueue Admin Scripts
		wp_register_script( 'rg-ddeals-chosen', RGDDEALS__PLUGIN_URL . 'admin/js/chosen.jquery.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-chosen' );
		wp_register_script( 'rg-ddeals-datatables', RGDDEALS__PLUGIN_URL . 'admin/js/jquery.dataTables.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-datatables' );

		wp_register_script( 'rg-ddeals-notify', RGDDEALS__PLUGIN_URL . 'admin/js/notify.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-notify' );

		wp_register_script( 'rg-ddeals-unveil', RGDDEALS__PLUGIN_URL . 'admin/js/jquery.unveil.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-unveil' );
		
		wp_register_script( 'rg-ddeals-checkbox', RGDDEALS__PLUGIN_URL . 'admin/js/iphone-style-checkboxes.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-checkbox' );
		wp_register_script( 'rg-ddeals-confirm', RGDDEALS__PLUGIN_URL . 'admin/js/jquery-confirm.js', array ( 'jquery' ) );
		wp_enqueue_script( 'rg-ddeals-confirm' );
		wp_register_script( 'rg-ddeals-main', RGDDEALS__PLUGIN_URL . 'admin/js/main.js', array ( 'jquery', 'jquery-form' ) );
		wp_enqueue_script( 'rg-ddeals-main' );
		wp_localize_script( 'rg-ddeals-main', 'MyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
		wp_enqueue_media();
	}
}
add_action( 'admin_enqueue_scripts', 'rg_ddeals_admin_enqueue' );
function rg_ddeals_admin_actions() 
{
	add_menu_page('RevGlue Daily Deals', 'RevGlue Daily Deals', 'manage_options', 'revglue-dashboard', 'rg_ddeals_main_page', RGDDEALS__PLUGIN_URL .'admin/images/menuicon.png' );
	add_submenu_page('revglue-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'revglue-dashboard', 'rg_ddeals_main_page');
	add_submenu_page('revglue-dashboard', 'Import Stores', 'Import Stores', 'manage_options', 'revglue-import-stores', 'rg_ddeals_store_import_page');
	add_submenu_page('revglue-dashboard', 'Stores', 'Stores', 'manage_options', 'revglue-stores', 'rg_ddeals_store_listing_page');
	add_submenu_page('revglue-dashboard', 'Deal Categories', 'Deal Categories', 'manage_options', 'revglue-categories', 'rg_ddeals_category_listing_page');
	add_submenu_page('revglue-dashboard', 'Import Daily Deals', 'Import Daily Deals', 'manage_options', 'revglue-import-ddeals', 'rg_ddeals_import_page');
	add_submenu_page('revglue-dashboard', 'Daily Deals', 'Daily Deals', 'manage_options', 'revglue-ddeals', 'rg_ddeals_listing_page');
}
add_action( 'admin_menu', 'rg_ddeals_admin_actions' );
function rg_ddeals_create_directory_structures( $dir_structure_array )
{
	$upload = wp_upload_dir();
	$base_dir = $upload['basedir'];
	foreach( $dir_structure_array as $single_dir )
	{
		$create_dir = $base_dir.'/'.$single_dir;
		if ( ! is_dir( $create_dir ) ) 
		{
			mkdir( $create_dir, 0755 );
		}
		$base_dir = $create_dir;
	}
}
function rg_ddeals_remove_directory_structures()
{
	$upload = wp_upload_dir();
	$base_dir = $upload['basedir'].'\revglue';
	rg_ddeals_folder_cleanup($base_dir);
}
function rg_ddeals_folder_cleanup( $dirpath )
{
	if( substr( $dirpath, strlen($dirpath) - 1, 1 ) != '/' )
	{
        $dirpath .= '/';
    }
	$files = glob($dirpath . '*', GLOB_MARK);
	foreach( $files as $file )
	{
		if( is_dir( $file ) )
		{
			deleteDir($file);
		}
		else
		{
			unlink($file);
        }
    }
	rmdir($dirpath);
}
function rg_ddeals_auto_import_data()
{
    $auto_var = basename( $_SERVER["REQUEST_URI"] );
	if ( $auto_var ==  'auto_import_data') 
	{
		include( RGDDEALS__PLUGIN_DIR . 'includes/auto-import-data.php');
	}
}
add_action( 'template_redirect', 'rg_ddeals_auto_import_data' );
function revglue_dd_total_deals_by_cat_id($catid){
	global $wpdb;
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
	$sql = "SELECT COUNT(*) as countofDeals FROM $ddeals_table WHERE FIND_IN_SET('$catid',`category_ids`) ";
	$countofDeals = $wpdb->get_var($sql);
	return $countofDeals;
}
function rg_ddeals_populate_recursive_categories( $category_object, $parent_title, &$counter )
{
	global $wpdb;
	$categories_table = $wpdb->prefix.'rg_categories';
	$sql = "SELECT *FROM $categories_table WHERE `parent` = $category_object->rg_category_id ORDER BY `title` ASC";
	$subcategories = $wpdb->get_results($sql);
	if ( !empty($parent_title) )
	{
		$title = $parent_title.'->'. $category_object->title;
		$strong_title = $parent_title.'-><strong>'.$category_object->title.'</strong>';
	} else 
	{
		$title = $category_object->title;
		$strong_title = '<strong>'.$title.'</strong>';
	}
	?><tr class="ui-state-default">
		<td>
			<?php esc_html_e( $counter ); ?>
		</td>
		<td style="text-align:left;">
			<?php _e( $strong_title ); ?>
		</td>
		<td style="text-align:left;">
			<?php echo revglue_dd_total_deals_by_cat_id($category_object->rg_category_id); ?>
		</td>
		<?php 
			$themename= 	get_option('themename');
			if( $themename =='bluedeals' ){
		?>
		<td style="text-align:left;">
			<div class="revglue-banner-thumb   rg_category_logo_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">
				<?php 
				$logourl = $category_object->logo_image_url;
				 if (is_numeric(substr($logourl, 0, 1))) {
					?><a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_logo" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img style="width: 71px;" alt="image" src="<?php echo  REVGLUE__STORE_ICONS.'/'.$logourl.'.png' ; ?>"><?php
				} else { ?>
				<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_logo" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img style="width: 71px;" alt="image" src="<?php  esc_html_e( $category_object->logo_image_url ) ; ?>">
				<?php }
				?>
			</div>
		</td>
				<?php } ?>
		<td style="text-align:left;">
			<div class="revglue-banner-thumb   rg_store_icon_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">
				<?php 
				$iconurl = $category_object->icon_url;
				 if (is_numeric(substr($iconurl, 0, 1))) {
					?><a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_icons" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img style="width: 71px;" alt="image" src="<?php echo  REVGLUE__STORE_ICONS.'/'.$iconurl.'.png' ; ?>"><?php
				} else { ?>
				<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="image_url" class="rg_category_delete_icons" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img  style="width: 71px;" alt="image" src="<?php  esc_html_e( $category_object->icon_url ) ; ?>">
				<?php }
				?>
			</div>
		</td>
		<td style="text-align:left;">
				<?php  $imageurl = $category_object->image_url;
				 if (is_numeric(substr($imageurl, 0, 1))) { ?>
				<div class="revglue-banner-thumb   rg_store_image_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">
					<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="icon_url" class="rg_category_delete_images" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img alt="image" src="<?php echo  REVGLUE__CATEGORY_BANNERS.'/'. $imageurl.'.jpg' ; ?>">
				</div>
					<?php
				} else { ?>
				<div class="revglue-banner-thumb   rg_store_image_thumb_<?php esc_attr_e( $category_object->rg_category_id ); ?>">
					<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" data-type="icon_url" class="rg_category_delete_images" href="javascript;"><i class="fa fa-times" aria-hidden="true"></i></a>
					<img alt="image" src="<?php esc_html_e( $category_object->image_url) ; ?>">
				</div>
			 <?php	}
				?>
			</div>
		</td>
		<td>
			<?php 
			if( $category_object->header_category_tag == 'yes' )
			{
				$checked = 'checked="checked"';
			} else
			{
				$checked = '';
			}
			if ($category_object->parent == "0"){
			?>
			<input <?php esc_attr_e( $checked ); ?> type="checkbox" id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_store_cat_tag_head" />
			<?php } ?>
		</td>
		<td>
			<?php 
			if( $category_object->popular_category_tag == 'yes' )
			{
				$checked = 'checked="checked"';
			} else
			{
				$checked = '';
			}
			?>
			<input <?php esc_attr_e( $checked ); ?> type="checkbox" id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" class="rg_store_cat_tag" />
		</td>
		<td>
			<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" style="border-right: 1px solid #595959;" class="rg_add_category_icon rg_add_category_icon_<?php esc_attr_e($category_object->rg_category_id ); ?>" href="javascript;">
				<?php if(!empty($category_object->icon_url))
				{
					esc_html_e( 'Edit Icon' );
				} else 
				{
					esc_html_e( 'Add Icon' );
				}
				?>
			</a>
			<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" style=" margin-left: 5px; border-right: 1px solid #595959;" class="rg_add_category_image rg_add_category_image_<?php esc_attr_e( $category_object->rg_category_id ); ?>" href="javascript;">
				<?php if(!empty($category_object->image_url))
				{
					esc_html_e( 'Edit Image' );
				} else 
				{
					esc_html_e( 'Add Image' );
				}
				?>
			</a>
			<?php 
			$themename= 	get_option('themename');
			if( $themename =='bluedeals' ){
		?>
			<a id="<?php esc_attr_e( $category_object->rg_category_id ); ?>" style=" margin-left: 5px;" class="rg_add_category_logo rg_add_category_logo_<?php esc_attr_e( $category_object->rg_category_id ); ?>" href="javascript;">
				<?php if(!empty($category_object->logo_image_url))
				{
					esc_html_e( 'Edit Logo' );
				} else 
				{
					esc_html_e( 'Add Logo' );
				}
				?>
			</a>
			<?php } ?>
		</td>
	</tr><?php
	if( !empty( $subcategories ) )
	{
		foreach( $subcategories as $single_cateogory )
		{
			++$counter;
			rg_ddeals_populate_recursive_categories( $single_cateogory, $title, $counter );
		}
	}
}
 function rg_admin_notice_if_user_has_not_subscription_id() {
		global $wpdb;
		$rg_projects_table = $wpdb->prefix.'rg_projects'; 
		$sql = "SELECT  email FROM $rg_projects_table where email !='' limit 1";
		$email = $wpdb->get_var($sql);
		$admin_page = get_current_screen();
		if ($email =='' && $admin_page->base == "dashboard" ) {
		echo '<div class="notice notice-success customstyle  subscriptiondone ">  ';
		echo  '<p>Please read the instructions on  <a href=\"admin.php?page=revglue-dashboard\" target=\"_blank\">RevGlue Dashbaord</a> for importing your RevGlue projects data. </p>';
		echo  '</div>';  
		} 
}
add_action( 'admin_notices', 'rg_admin_notice_if_user_has_not_subscription_id' );
/*function remove_core_updates(){
global $wp_version;return(object) array('last_checked'=> time(),'version_checked'=> $wp_version,);
}*/
//add_filter('pre_site_transient_update_core','remove_core_updates'); //hide updates for WordPress itself
//add_filter('pre_site_transient_update_plugins','remove_core_updates'); //hide updates for all plugins
//add_filter('pre_site_transient_update_themes','remove_core_updates'); //hide updates for all themes	
/**************************************************************************************************
*
* Remove Wordpress dashboard default widgets
*
***************************************************************************************************/
function rg_remove_default_widgets(){
	remove_action('welcome_panel', 'wp_welcome_panel');
	remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
	remove_meta_box( 'dashboard_quick_press',   'dashboard', 'side' );      //Quick Press widget
	remove_meta_box( 'dashboard_recent_drafts', 'dashboard', 'side' );      //Recent Drafts
	remove_meta_box( 'dashboard_primary',       'dashboard', 'side' );      //WordPress.com Blog
	remove_meta_box( 'dashboard_incoming_links','dashboard', 'normal' );    //Incoming Links
	remove_meta_box( 'dashboard_plugins',       'dashboard', 'normal' );    //Plugins
	remove_meta_box('dashboard_activity', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'rg_remove_default_widgets');
?>