<?php
// Exit if accessed directly 
if ( !defined( 'ABSPATH' ) ) exit;
function revglue_ddeals_subscription_validate() 
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$sanitized_sub_id	= sanitize_text_field( $_POST['sub_id'] );
	$sanitized_email	= sanitize_email( $_POST['sub_email'] );
	$password  			= $_POST['sub_pass'];
	//die(RGCOUPON__API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id");
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGDDEALS__API_URL . "api/validate_subscription_key/$sanitized_email/$password/$sanitized_sub_id", array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$result = $resp_from_server['response']['result'];
	 // pre($result);
	 //die;
	$iFrameid =$result['iframe_id'];
	$data=array();
	if($iFrameid!=""){
		$data=array( 
			'subcription_id' 				=> $sanitized_sub_id, 
			'user_name' 					=> $result['user_name'], 
			'email' 						=> $result['email'], 
			'project' 						=> $result['project'],
			'project' => $result['project'] == "Coupons" ? str_replace ("Coupons", "Daily Deals UK", $result['project']) : $result['project'],
 
			'expiry_date' 					=> $result['expiry_date'], 
			'partner_iframe_id' 			=> $result['iframe_id'], 
			'password' 						=> $password, 
			'status' 						=> $result['status']
		) ;
	}else{
			$data=array( 
			'subcription_id' 				=> $sanitized_sub_id, 
			'user_name' 					=> $result['user_name'], 
			'email' 						=> $result['email'], 
			'project' 						=> $result['project'], 
			'expiry_date' 					=> $result['expiry_date'],
			'password' 						=> $password, 
			'status' 						=> $result['status']
			) ;
	}
	$string = '';
	if( $resp_from_server['response']['success'] == true )
	{
		$sql = "Select * FROM $project_table Where project like '".$result['project']."' and status = 'active'";
	    $execute_query = $wpdb->get_results( $sql );
	     // pre($execute_query);
		$rows = $wpdb->num_rows;
		if( empty ( $rows ) ) 
		{
			  //echo $wpdb->last_query;
			// die();
			  // pre($execute_query);
			 // die;
				$string .= "<div class='panel-white mgBot'>";
				if($iFrameid!=""){
					$string .= "<p><b>Your RevEmbed Free Daily Deals Subscription is ". $result['status'].". </b><img  class='tick-icon' src=".RGDDEALS__PLUGIN_URL. 'admin/images/ticks_icon.png'." />  </p>";
					$string .= "<p><b>Name = </b>RevEmbed Data</p>";
					$string .= "<p><b>Project = </b>Daily Deals UK</p>";
					$string .= "<p><b>Email = </b>".$result['email']."</p>";
				} else{
					$string .= "<p><b>Your Daily Deals subscription is ". $result['status'].". </b><img  class='tick-icon' src=".RGDDEALS__PLUGIN_URL. 'admin/images/ticks_icon.png'." />  </p>";
					$string .= "<p><b>Name = </b>".$result['user_name']."</p>";
					$string .= "<p><b>Project = </b>".$result['project']."</p>";
					$string .= "<p><b>Email = </b>".$result['email']."</p>";
					$string .= "<p><b>Expiry Date = </b>".date("d-M-Y", strtotime($result['expiry_date']))."</p>";
			$string .= "</div>";
				}
				$wpdb->insert(
				$project_table, 
				$data
			);
			} else {
					$string .= "<div style='color: green;'>You already have subscription of this project, thankyou! </div>";

				}
			
		} else 
		{
			$string .= "<p>&raquo; Your subscription unique ID <b class='grmsg'> ". $sanitized_sub_id ." </b> is Invalid.</p>";
				
		}
	echo $string;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_subscription_validate', 'revglue_ddeals_subscription_validate' );
function revglue_ddeals_data_import()
{
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$categories_table = $wpdb->prefix.'rg_categories';
	$stores_table = $wpdb->prefix.'rg_stores';
	$date = date('Y-m-d H:i:s');
	$string = '';
	$import_type = sanitize_text_field( @$_POST['import_type'] );
	$sql = "SELECT *FROM $project_table WHERE project LIKE 'Daily Deals UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		$subscriptionid = $project_detail[0]->subcription_id;
		$useremail = $project_detail[0]->email;
		$userpassword = $project_detail[0]->password;
		$projectid = $project_detail[0]->partner_iframe_id;

		if( $import_type == 'rg_stores_import'  )
		{
			revglue_dd_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			$template_type = revglue_dd_check_subscriptions();
			if($template_type=="Free"){
				$apiURL ="https://www.revglue.com/partner/dailydeals_stores/$projectid/json/wp/$subscriptionid";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiURL , array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
				// die($apiURL);

			}
				else{
					$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGDDEALS__API_URL . "api/group_deal_stores/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 

				}
				 // pre($resp_from_server);
			 	 // die;
			$result = $resp_from_server['response']['stores'];
			// pre($result);
			// die;
	  		if($resp_from_server['response']['success'] == 1 )
			{
				foreach($result as $row)
				{
					$sqlinstore = "Select rg_store_id FROM $stores_table Where rg_store_id = '".$row['rg_store_id']."'";
					$rg_store_exists = $wpdb->get_var( $sqlinstore );
					if( empty( $rg_store_exists ) )
					{
						$wpdb->insert( 
							$stores_table, 
							array( 
								'rg_store_id' 				=> $row['rg_store_id'], 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=>  $row['affiliate_network_link'],
								'store_base_currency' 		=> $row['store_base_currency'], 
								'store_base_country' 		=> $row['store_base_country'], 
								'category_ids' 				=> $row['category_ids'],
								'date' 						=> $date
							) 
						);
					} else 
					{
						$wpdb->update( 
							$stores_table, 
							array( 
								'mid' 						=> $row['affiliate_network_mid'], 
								'title' 					=> $row['store_title'], 
								'url_key' 					=> $row['url_key'], 
								'description' 				=> $row['store_description'], 
								'image_url' 				=> $row['image_url'], 
								'affiliate_network' 		=> $row['affiliate_network'], 
								'affiliate_network_link'	=> $row['affiliate_network_link'], 
								'store_base_currency' 		=> $row['store_base_currency'], 
								'store_base_country' 		=> $row['store_base_country'], 
								'category_ids' 				=> $row['category_ids'],
								'date' 						=> $date
							),
							array( 'rg_store_id' => $rg_store_exists )
						);
					}					
				}
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} else
		 if( $import_type == 'rg_categories_import'  )
		{
			revglue_dd_update_subscription_expiry_date($subscriptionid, $userpassword, $useremail, $projectid);
			$template_type = revglue_dd_check_subscriptions();
			if($template_type=="Free"){
				$apiURL ="https://www.revglue.com/partner/dailydeals_categories//json/wp/$subscriptionid";
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiURL , array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
				// die($apiURL);

			}
				else{

			$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGDDEALS__API_URL . "api/group_deal_categories/json/".$project_detail[0]->subcription_id, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
				}
				// pre($resp_from_server);
				// die;
			$resultCategories = $resp_from_server['response']['categories'];
			if($resp_from_server['response']['success'] == 1 )
			{
				foreach($resultCategories as $row)
				{	
					$sqlincat = "Select rg_category_id FROM $categories_table Where rg_category_id = '".$row['dailydeals_category_id']."'";
					$rg_category_exists = $wpdb->get_var( $sqlincat );
					if( empty( $rg_category_exists ) )
					{					
						$title 		= $row['dailydeals_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->insert( 
							$categories_table, 
							array( 
								'rg_category_id' 		=> $row['dailydeals_category_id'], 
								'title' 				=> $row['dailydeals_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'parent' 				=> $row['parent_category_id'], 
								'date' 					=> $date
							) 
						);
					} else 
					{
						$title 		= $row['dailydeals_cateogry_title'];
						$url_key 	= preg_replace('/[^\w\d_ -]/si', '', $title); 	// remove any special character
						$url_key 	= preg_replace('/\s\s+/', ' ', $url_key);		// replacing multiple spaces to signle
						$url_key 	= strtolower(str_replace(" ","-",$url_key));
						$wpdb->update( 
							$categories_table, 
							array( 
								'title' 				=> $row['dailydeals_cateogry_title'], 
								'url_key' 				=> $url_key, 
								'date' 					=> $date,
								'parent' 				=> $row['parent_category_id']
							),
							array( 'rg_category_id' => $rg_category_exists )
						);
					}
				}
				$wpdb->query( "DELETE FROM $categories_table WHERE `date` != '$date' " );
			   $sqlParentCat = "SELECT * FROM $categories_table ";
				$CateIDs = $wpdb->get_results( $sqlParentCat ); 
				foreach ($CateIDs as $key => $cID) {
								$update_array = array();
								if($cID->parent == '0'){
									$update_array['header_category_tag'] = 'yes';
									$catid = $cID->rg_category_id;
								}else{
									$catid = $cID->parent;
								}
					$catnames = array( "Automotive", "Books and Magazines", "Finance", "Computers and Internet" );
								$update_array['icon_url'] = $catid;
								$update_array['image_url'] = $catid;
								if ( in_array( $cID->title, $catnames) ){
									$update_array['popular_category_tag'] = 'yes';
									}
								$wpdb->update( 
										$categories_table, 
										$update_array,
										array( 'rg_category_id' => $cID->rg_category_id )
									); 
								}
			} else 
			{
				$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
			}
		} 
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql_1 = "SELECT MAX(date) FROM $categories_table";
	$last_updated_category = $wpdb->get_var($sql_1);
	$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l , d-M-Y h:i:s A', strtotime( $last_updated_category ) ) : '-' );
	$sql = "SELECT MAX(date) FROM $stores_table";
	$last_updated_store = $wpdb->get_var($sql);
	$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l , d-M-Y h:i:s A', strtotime( $last_updated_store ) ) : '-' );
	$sql_2 = "SELECT count(*) as categories FROM $categories_table";
	$count_category = $wpdb->get_results($sql_2);
	$response_array['count_category'] = $count_category[0]->categories;
	$sql_3 = "SELECT count(*) as stores FROM $stores_table";
	$count_store = $wpdb->get_results($sql_3);
	$response_array['count_store'] = $count_store[0]->stores;
		echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_data_import', 'revglue_ddeals_data_import' );
function revglue_banner_data_import()
{
			//echo "One";
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$banner_table = $wpdb->prefix.'rg_banner';
	$string = '';
	$date = date('Y-m-d H:i:s');
	$import_type = sanitize_text_field( $_POST['import_type'] );
	$sql = "SELECT *FROM $project_table WHERE project LIKE 'Banners UK'";
	$project_detail = $wpdb->get_results($sql);
	$rows = $wpdb->num_rows;
	if( !empty ( $rows ) )
	{
		if( $import_type == 'rg_banners_import'   )
		{
			$i = 0;
			$page = 1;
			do {
				$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGDDEALS__API_URL . "api/banners/json/".$project_detail[0]->subcription_id."/".$page, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
				update_option("rg_banners_status", $page);
				$total = ceil( $resp_from_server['response']['banners_total'] / 1000 ) ;
				$result = $resp_from_server['response']['banners'];
				if($resp_from_server['response']['success'] == true )
				{
					foreach($result as $row)
					{
						$sqlinstore = "SELECT rg_store_banner_id FROM $banner_table WHERE rg_store_banner_id = '".$row['rg_banner_id']."' AND `banner_type` = 'imported'";
						$rg_banner_exists = $wpdb->get_var( $sqlinstore );
						if( empty( $rg_banner_exists ) )
						{
							$wpdb->insert( 
								$banner_table, 
								array( 
										'rg_store_banner_id' 	=> $row['rg_banner_id'], 
										'rg_store_id' 			=> $row['rg_store_id'], 
										'title' 				=> $row['banner_alt_text'],   
										'image_url' 			=> $row['banner_image_url'], 
										'url' 					=> $row['deep_link'], 
										'rg_size' 			    => $row['width_pixels'].'x'.$row['height_pixels'], 
										'date' 			    	=> $date, 
										'placement' 			=> 'unassigned', 
										'banner_type' 			=> 'imported'
								) 
							);
							/*echo $wpdb->last_query;
							die();*/
						} else 
						{
							$wpdb->update( 
								$banner_table, 
								array( 
									'rg_store_id' 			=> $row['rg_store_id'], 
									'title' 				=> $row['banner_alt_text'], 
									'url' 				    => $row['deep_link'], 
									'date' 			    	=> $date, 
									'image_url' 			=> $row['banner_image_url'],	
									'date' 			    	=> $date
								),
								array( 'rg_store_banner_id' => $rg_banner_exists )
							);
						}										
					}
					$wpdb->query( "DELETE FROM $banner_table WHERE `date` != '$date' " );
				} else 
				{
					$string .= '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
				}
				$i++;
				$page++;
			} while ( $i < $total );
		}
	} else 
	{
		$string .= "<p style='color:red'>Please subscribe for your RevGlue project first, then you have the facility to import the data";
	}
	$response_array = array();
	$response_array['error_msgs'] = $string;
	$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
	$count_banner = $wpdb->get_results($sql1);
	$response_array['count_banner'] = $count_banner[0]->banner;
	echo json_encode($response_array);	
	wp_die();
}
add_action( 'wp_ajax_revglue_banner_data_import', 'revglue_banner_data_import' );
function revglue_ddeals_data_delete()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$categories_table = $wpdb->prefix.'rg_categories';
	$banner_table = $wpdb->prefix.'rg_banner';
	$data_type = sanitize_text_field( $_POST['data_type'] );
	$response_array = array();
	if( $data_type == 'rg_stores_delete' )
	{
		$response_array['data_type'] = 'rg_stores';
		$wpdb->query( "DELETE FROM $stores_table" );	
		$sql = "SELECT MAX(date) FROM $stores_table";
		$last_updated_store = $wpdb->get_var($sql);
		$response_array['last_updated_store'] = ( $last_updated_store ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_store ) ) : '-' );
		$sql2 = "SELECT count(*) as stores FROM $stores_table";
		$count_store = $wpdb->get_results($sql2);
		$response_array['count_store'] = $count_store[0]->stores;
	} else if( $data_type == 'rg_categories_delete' )
	{
		$response_array['data_type'] = 'rg_categories';
		$wpdb->query( "DELETE FROM $categories_table" );	
		$sql = "SELECT MAX(date) FROM $categories_table";
		$last_updated_category = $wpdb->get_var($sql);
		$response_array['last_updated_category'] = ( $last_updated_category ? date( 'l jS \of F Y h:i:s A', strtotime( $last_updated_category ) ) : '-' );
		$sql2 = "SELECT count(*) as categories FROM $categories_table";
		$count_category = $wpdb->get_results($sql2);
		$response_array['count_category'] = $count_category[0]->categories;
	} else if( $data_type == 'rg_banners_delete' )
	{
		$response_array['data_type'] = 'rg_banners';
		$wpdb->query( "DELETE FROM $banner_table where banner_type='imported'" );	
		$sql1 = "SELECT count(*) as banner FROM $banner_table where banner_type= 'imported'";
		$count_banner = $wpdb->get_results($sql1);
		$response_array['count_banner'] = $count_banner[0]->banner;
	}
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_data_delete', 'revglue_ddeals_data_delete' );
function revglue_ddeals_update_header_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'header_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_update_header_category', 'revglue_ddeals_update_header_category' );
function revglue_ddeals_update_popular_category()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$cat_state 	= sanitize_text_field( $_POST['state'] );
	$wpdb->update( 
		$categories_table, 
		array( 'popular_category_tag' => $cat_state ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_update_popular_category', 'revglue_ddeals_update_popular_category' );
function revglue_ddeals_update_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$icon_url 	= esc_url_raw( $_POST['icon_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => $icon_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_update_category_icon', 'revglue_ddeals_update_category_icon' );
function revglue_ddeals_update_category_logo_image_home()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id	= absint( $_POST['cat_id'] );
	$icon_url = esc_url_raw( $_POST['logo_image_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'logo_image_url' => $icon_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	// echo $wpdb->last_query;
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_update_category_logo_image_home', 'revglue_ddeals_update_category_logo_image_home' );
function revglue_ddeals_delete_category_logo_home()
{
	global $wpdb; 
	// pre($wpdb);
	// die();
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'logo_image_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_delete_category_logo_home', 'revglue_ddeals_delete_category_logo_home');
function revglue_ddeals_delete_category_icon()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'icon_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_delete_category_icon', 'revglue_ddeals_delete_category_icon' );
function revglue_ddeals_update_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$image_url 	= esc_url_raw( $_POST['image_url'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => $image_url ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_update_category_image', 'revglue_ddeals_update_category_image' );
function revglue_ddeals_delete_category_image()
{
	global $wpdb; 
	$categories_table = $wpdb->prefix.'rg_categories';
	$cat_id		= absint( $_POST['cat_id'] );
	$wpdb->update( 
		$categories_table, 
		array( 'image_url' => '' ), 
		array( 'rg_category_id' => $cat_id )
	);
	echo $cat_id;
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_delete_category_image', 'revglue_ddeals_delete_category_image' );
function revglue_ddeals_fetch($subcriptionid,$projectid,$rg_store_id,$page=1,$ddeals_table,$wpdb,$date){
						
$template_type = revglue_dd_check_subscriptions();
if( $template_type=="Free"){
	// echo "i,m here free";
	//die;
	$apiurl = "https://www.revglue.com/partner/dailydeals/$page/$rg_store_id/$projectid/json/$subcriptionid/";
	// die($apiurl);
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
}else{
	// echo "i,m here paid";
	// die;
$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( RGDDEALS__API_URL . "api/group_deals/json/".$subcriptionid."/".$rg_store_id."/".$page, array( 'timeout' => 120, 'sslverify'   => false ) ) ), true);
}
 // pre($resp_from_server);
 // die;
						update_option("rg_banners_status", $page);
						$total_pages = (int) $resp_from_server['response']['total_pages'];
						// echo $total;
						// pre($total_pages);
						// die;
						// die;
						$result = $resp_from_server['response']['group_deals'];
						// pre($result);
						// die;
						if($resp_from_server['response']['success'] == true )
						{
							foreach($result as $key => $row)
							{ 
								set_time_limit(0);
								// pre("come here 1");
								if($key == 999 && $total_pages>$page){
									$page++;
									// echo $page;
									// die;
									revglue_ddeals_fetch($subcriptionid,$projectid,$rg_store_id,$page,$ddeals_table,$wpdb,$date);

								}
								// pre($page);
								revglue_ddels_insert_cities($row['city']);
								$exist = $wpdb->get_var("SELECT count(*) FROM $ddeals_table WHERE rg_deal_id='$row[dailydeal_id]'");
								// pre("come here 2");
								if($exist == 0){
									$wpdb->insert( 
										$ddeals_table, 
										array( 
										'rg_deal_id'			=> $row['dailydeal_id'],
										'rg_store_id'			=> $row['rg_store_id'],
										'title'					=> trim($row['dailydeal_title'],"''"),
										'description'			=> trim($row['dailydeal_description'],"''"),
										'image_url'				=> $row['image_url'],
										'deeplink'				=> $row['deep_link'],
										'price'					=> $row['price'],
										'rrp'					=> $row['rrp'],
										'category_ids'			=> $row['dailydeals_category_id'],
										'brand'					=> $row['brand'],
										'city'			    	=> $row['city'],
										'issue_date'			=> $row['issue_date'],
										'expiry_date'			=> $row['expiry_date'],
										'date'					=> $date
										) 
									);
								}else{
									// pre("come here 3");
									// die();
									$wpdb->update( 
										$ddeals_table, 
										array( 
										'title'					=> trim($row['dailydeal_title'],"''"),
										'description'			=> trim($row['dailydeal_description'],"''"),
										'image_url'				=> $row['image_url'],
										'deeplink'				=> $row['deep_link'],
										'price'					=> $row['price'],
										'rrp'					=> $row['rrp'],
										'category_ids'			=> $row['dailydeals_category_id'],
										'brand'					=> $row['brand'],
										'city'			    	=> $row['city'],
										'issue_date'			    => $row['issue_date'],
										'expiry_date'			=> $row['expiry_date'],
										'date'					=> $date
										),
										array( 'rg_deal_id' => $row['dailydeal_id'] )
									);
								}										
							}

						} else 
						{
							$string = '<p style="color:red">'.$resp_from_server['response']['message'].'</p>';
						}
				}
function revglue_ddeals_get_daily_deals()
{
	global $wpdb; 
	$project_table = $wpdb->prefix.'rg_projects';
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
	$rg_store_id = absint( $_POST['rg_store_id'] );
	$date = date('Y-m-d');
	$sql = "SELECT *FROM $project_table WHERE project LIKE 'Daily Deals UK'";
	$project_detail = $wpdb->get_row($sql, ARRAY_A);
	$subcriptionid =	$project_detail['subcription_id'];
	$projectid ='';





	$rows = $wpdb->num_rows; 
	if( !empty ( $rows ) )
	{
				$projectid =	$project_detail['partner_iframe_id'];
				$page = 1;
				revglue_ddeals_fetch($subcriptionid, $projectid, $rg_store_id,$page,$ddeals_table,$wpdb,$date); 
	}
	$response_array = array();
	$response_array['rg_store_id'] = $rg_store_id;
	$sql1  = "SELECT COUNT( rg_store_id ) as total_deals, MAX( date ) as last_updated FROM $ddeals_table WHERE rg_store_id = $rg_store_id";
	$project_detail = $wpdb->get_results($sql1);
	$sqld = "SELECT MAX(date) FROM $ddeals_table WHERE rg_store_id = $rg_store_id";
	$last_updated_deal = $wpdb->get_var($sqld);
	$response_array['last_updated_deal'] = ( $last_updated_deal!="" ? date("d-M-Y", strtotime( $last_updated_deal)) : '-' );
	$sqlc = "SELECT COUNT(*) FROM $ddeals_table WHERE rg_store_id = $rg_store_id";
	$count_deal = $wpdb->get_var($sqlc);
	$response_array['count_deal'] = $count_deal;
	echo json_encode($response_array);
	wp_die();
}
add_action( 'wp_ajax_revglue_ddeals_get_daily_deals', 'revglue_ddeals_get_daily_deals' );
function revglue_ddels_insert_cities($cities){
	global $wpdb; 
	$cities_table = $wpdb->prefix.'rg_cities';
	$citi_name = explode(", ", $cities);
	foreach($citi_name as $city_name) {
		// $sql = "INSERT INTO `$cities_table` (`city_name`) VALUES ( '$city_name')";
		$sql = "INSERT INTO $cities_table (city_name)
		SELECT * FROM (SELECT '$city_name') AS tmp
		WHERE NOT EXISTS (
			SELECT city_name FROM $cities_table WHERE city_name='$city_name'
		)";
		$wpdb->query($sql);
	}
}
function revglue_ddeals_load_banners()
{
	global $wpdb; 
	$stores_table = $wpdb->prefix.'rg_stores';
	$sTable = $wpdb->prefix.'rg_banner';
	$upload = wp_upload_dir();
	$base_url = $upload['baseurl'];
	$uploadurl = $base_url.'/revglue/daily-deals/banners/';
	$placements = array(
		'home-top'				=> 'Home:: Top Header',
		'home-slider'			=> 'Home:: Main Banners',
		'home-mid'				=> 'Home:: After Categories',
		'home-bottom'			=> 'Home:: Before Footer',
		'cat-top'				=> 'Category:: Top Header',
		'cat-side-top'			=> 'Category:: Top Sidebar',
		'cat-side-bottom'		=> 'Category:: Bottom Sidebar 1',
		'cat-side-bottom-two'	=> 'Category:: Bottom Sidebar 2',
		'cat-bottom'			=> 'Category:: Before Footer',
		'store-top'				=> 'Store:: Top Header',
		'store-side-top'		=> 'Store:: Top Sidebar',
		'store-side-bottom'		=> 'Store:: Bottom Sidebar 1',
		'store-side-bottom-two'	=> 'Store:: Bottom Sidebar 2',
		'store-main-bottom'		=> 'Store:: After Review',
		'store-bottom'			=> 'Store:: Before Footer',
		'unassigned' 			=> 'Unassigned Banners'
	);
	$aColumns = array( 'banner_type', 'placement', 'status', 'title', 'url', 'image_url', 'rg_store_id', 'rg_id', 'rg_store_banner_id', 'rg_size'  ); 
	$sIndexColumn = "rg_store_id"; 
	$sLimit = "LIMIT 1, 50";

	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}

	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
	/*	for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			//$columnIdx = array_search( $requestColumn['data'], $dtColumns );
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = "SELECT SQL_CALC_FOUND_ROWS `".str_replace(" , ", " ", implode("`, `", $aColumns))."` FROM   $sTable $where $sOrder $sLimit";
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	$sQuery = "SELECT FOUND_ROWS()";
	$rResultFilterTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$sQuery = "SELECT COUNT(`".$sIndexColumn."`) FROM   $sTable";
	$rResultTotal = $wpdb->get_results($sQuery, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	foreach($rResult as $aRow)
	{
		/*pre($aRow);
		die();*/
		$row = array();
		for ( $i=0 ; $i<count($aColumns) ; $i++ )
		{
			if( $i == 0 )
			{
				if( $aRow[ $aColumns[5] ] == '' )
				{
					$uploadedbanner = $uploadurl . $aRow[ $aColumns[3] ];
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGDDEALS__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $uploadedbanner ) .'"/></div>';
				} else
				{
					$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGDDEALS__PLUGIN_URL .'/admin/images/loading.gif" data-src="'. esc_url( $aRow[ $aColumns[5] ] ) .'" /></div>';
				}
			}else if( $i == 1 )
			{
				$row[] = $aRow[ $aColumns[8] ];
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[3] ];
			} else if( $i == 3 )
			{
				$row[] = ( $aRow[ $aColumns[0] ] == 'local' ? 'Local' : 'RevGlue Banner' );
			} else if( $i == 4 )
			{
				$row[] = $placements[$aRow[ $aColumns[1]]];
			} else if( $i == 5 )
			{
				$row[] = $aRow[ $aColumns[9]];
			} else if( $i == 6 )
			{
				if( ! empty( $aRow[ $aColumns[4]] ) )
				{
					$url_to_show = esc_url( $aRow[ $aColumns[4]] ); 
				} else if( ! empty( $aRow[ $aColumns[6]] ) )
				{
					$sql_1 = "SELECT affiliate_network_link FROM $stores_table where rg_store_id = ".$aRow[ $aColumns[6]];
					$deep_link = $wpdb->get_results($sql_1);
					$url_to_show = ( !empty( $deep_link[0]->affiliate_network_link ) ? esc_url( $deep_link[0]->affiliate_network_link ) : 'No Link'  );
				} else
				{
					$url_to_show = 'No Link';
				}
				$row[] = '<a class="rg_store_link_pop_up" id="'. $aRow[ $aColumns[7]] .'" title="'. str_replace("subid-value", "",$url_to_show).'" href="'. str_replace("subid-value", "",$url_to_show) .'" target="_blank"><img src="'. RGDDEALS__PLUGIN_URL .'/admin/images/linkicon.png" style="width:50px;"/></a>';
			} else if( $i == 7 )
			{
				$row[] = $aRow[ $aColumns[2]];
			} else if( $i == 8 )
			{
				$row[] = '<a href="'. admin_url( 'admin.php?page=revglue-banners&action=edit&banner_id='.$aRow[ $aColumns[7]] ) .'">Edit</a>';
			} else if ( $aColumns[$i] != ' ' )
			{    
				$row[] = $aRow[ $aColumns[$i] ];
			}
		}
		$output['data'][] = $row;
	}
	echo json_encode( $output );
	die(); 
}
add_action( 'wp_ajax_revglue_ddeals_load_banners', 'revglue_ddeals_load_banners' );
function rev_ddeals_admin_screen_listing_query()
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
	$categories_table = $wpdb->prefix.'rg_categories';
//11
	$aColumns = array( 
		'rg_id',
		'title', 
		'image_url',
		'deeplink',
		'brand',
		'city',
		'price',
		'rrp',
		'category_ids' );
/*print_r($aColumns);
die();*/
	$sLimit = "LIMIT 1, 50";
	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}

	/*if ( isset( $_REQUEST['start'] ) && $_REQUEST['length'] != '-1' )
	{
		$sLimit = "LIMIT ".intval( $_REQUEST['start'] ).", ".intval( $_REQUEST['length'] );
	}*/
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);
		 	$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=count($_REQUEST['columns']) ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i]; 
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = " SELECT *FROM $ddeals_table $where $sOrder $sLimit ";
	/*echo $sQuery;
	die();*/
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	/*print_r($rResult);
	die(); */ 
	$sQuery1 = " SELECT count(*) FROM $ddeals_table $where";
	//echo $sQuery1;
	 //die();
//	  die($sQuery1);
	$rResultFilterTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$rResultTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw'])  ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	//echo  $cc =count($aColumns);
	//die();
	foreach($rResult as $aRow)
	{
		/*echo $i++."<br/>";
		die();*/
		$row = array();
		for ( $i=0; $i<10; $i++ )
		{
			if( $i == 0 )
			{
				   $row[] = $aRow[ $aColumns[0] ];
				//$row[] = "Title";
			} else if( $i == 1 )
			{
				   $row[] = $aRow[ $aColumns[1] ];
			} else if( $i == 2 )
			{
				 //$row[] = "Deeplink";
				//$row[] = $aRow[ $aColumns[2] ];
				$row[]='<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGDDEALS__PLUGIN_URL. '/admin/images/loading.gif" data-src="'.  $aRow[ $aColumns[2] ] .' "/></div>';
			} else if( $i == 3 )
			{
				//$row[] = "Brand";
				//$row[] = $aRow[ $aColumns[2] ];
				 $row[]='<a class="rg_store_link_pop_up" id="'. $aRow[ $aColumns[0] ] .'"  href="'. str_replace("{subid-value}", "",$aRow[ $aColumns[3] ]).'" title="'. str_replace("{subid-value}", "",$aRow[ $aColumns[3] ]).'" target="_blank"> <img src="'. RGDDEALS__PLUGIN_URL.'/admin/images/linkicon.png" style="width:50px;"/>';
			//	$row[] = "Hello";
			} else if( $i == 4 )
			{
				//$row[] = "city";
				$row[] = $aRow[ $aColumns[4] ];
			}  else if( $i == 5 )
			{
				//$row[] = "Price";
				$row[] = $aRow[ $aColumns[5] ];
			}  else if( $i == 6 )
			{
				 $row[] = $aRow[ $aColumns[6] ];
				//$row[] = "RRP";
			} else if( $i == 7 )
			{
				 $row[] = $aRow[ $aColumns[7] ];
			}   else if( $i == 8 )
			{
				   $row[] = $aRow[ $aColumns[8] ];
				  //$row[] ="Last";
			} 	  
	}
	$output['data'][] = $row; 
}
    // print_r($output['data']);
	echo json_encode( $output );
	 die(); 
} 
add_action( 'wp_ajax_rev_ddeals_admin_screen_listing_query', 'rev_ddeals_admin_screen_listing_query' );
function revglue_ddeals_data_query(){
	global $wpdb; 
	$rg_ddeals = $wpdb->prefix.'rg_ddeals';
	$rg_id		= absint( $_POST['rg_id'] );
	$status 	= sanitize_text_field( $_POST['status'] );
	$str="";
	if ($rg_id){
	$updated=	 $wpdb->update( 
				$rg_ddeals, 
				array( 
					'status' => $status,	 
					'rg_id' => $rg_id  
				), 
				array( 'rg_id' => $rg_id ), 
				array( 
					'%s',	 
					'%d'	 
				), 
				array( '%d' ) 
		);
		//if $updated you can log the details
		if ( $updated ) {
			 $str= "Daily Deal is UPDATED: RG ID: ". $rg_id . ", Status: " .$status ;
		} else {
			 $str= "Daily Deal is Not UPDATED";
		}
}
	echo $str; 
	wp_die();  
}
add_action( 'wp_ajax_revglue_ddeals_data_query', 'revglue_ddeals_data_query' );
function revglue_dd_update_subscription_expiry_date($purchasekey, $userpassword, $useremail, $projectid){
	global $wpdb; 
	$projects_table = $wpdb->prefix.'rg_projects';
	$apiurl = RGDDEALS__API_URL . "api/validate_subscription_key/$useremail/$userpassword/$purchasekey";
	$resp_from_server = json_decode( wp_remote_retrieve_body( wp_remote_get( $apiurl , array( 'timeout' => 120, 'sslverify'   => false ) ) ), true); 
	$expiry_date = $resp_from_server['response']['result']['expiry_date'];
	if ( empty($projectid)){
		$sql ="UPDATE $projects_table SET `expiry_date` = '$expiry_date' WHERE `subcription_id` ='$purchasekey'";
		$wpdb->query($sql);
	} 
}

function revglue_dd_check_subscriptions(){
	global $wpdb;
	$project_table = $wpdb->prefix.'rg_projects';
	$sql ="SELECT `expiry_date` FROM $project_table WHERE `expiry_date`='Free' ";
	$project = $wpdb->get_var($sql);
	return $project;
}


 function revglue_ddeals_load_deals_listing() 
{
	global $wpdb;
	$stores_table = $wpdb->prefix.'rg_stores';
	$ddeals_table = $wpdb->prefix.'rg_ddeals';
//11
	$aColumns = array( 
		'rg_store_id',
		'image_url',
		'title' );
/*print_r($aColumns);
die();*/
	$sLimit = "LIMIT 1, 50"; 
	/*if ( isset( $_REQUEST['start'] ) && $_REQUEST['length'] != '-1' )
	{
		$sLimit = "LIMIT ".intval( $_REQUEST['start'] ).", ".intval( $_REQUEST['length'] );
	}*/

	if ( isset( $_REQUEST['start'] ) && sanitize_text_field($_REQUEST['length']) != '-1' )
	
	{
		$sLimit = "LIMIT ".intval(sanitize_text_field($_REQUEST['start'])).", ".intval(sanitize_text_field($_REQUEST['length']));
	}
	$sOrder = "";
	// make order functionality
	$where = "";
	$globalSearch = array();
	$columnSearch = array();
	$dtColumns = $aColumns;
	if ( isset($_REQUEST['search']) && sanitize_text_field($_REQUEST['search']['value']) != '' ) {
		$str = sanitize_text_field($_REQUEST['search']['value']);

			$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}

		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}
		/*for ( $i=0, $ien=3 ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i];
			$column = $dtColumns[ $requestColumn['data'] ];
			if ( $requestColumn['searchable'] == 'true' ) {
				$globalSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Individual column filtering
	if ( isset( $_REQUEST['columns'] ) ) {

		$request_columns = [];
		foreach ($_REQUEST['columns'] as $key => $val ) {
			if(is_array($val)){$request_columns[$key] = $val;}
			else{$request_columns[$key] = sanitize_text_field($val);}
		}
		for ( $i=0, $ien=count($request_columns) ; $i<$ien ; $i++ ) {
			$requestColumn = sanitize_text_field($request_columns[$i]) ;
			$column = sanitize_text_field($dtColumns[ $requestColumn['data'] ]) ;
			$str = sanitize_text_field($requestColumn['search']['value']) ;
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}

		/*for ( $i=0, $ien=3 ; $i<$ien ; $i++ ) {
			$requestColumn = $_REQUEST['columns'][$i]; 
			$column = $dtColumns[ $requestColumn['data'] ];
			$str = $requestColumn['search']['value'];
			if ( $requestColumn['searchable'] == 'true' &&
			 $str != '' ) {
				$columnSearch[] = "`".$column."` LIKE '%".$str."%'";
			}
		}*/
	}
	// Combine the filters into a single string
	$where = '';
	if ( count( $globalSearch ) ) {
		$where = '('.implode(' OR ', $globalSearch).')';
	}
	if ( count( $columnSearch ) ) {
		$where = $where === '' ?
			implode(' AND ', $columnSearch) :
			$where .' AND '. implode(' AND ', $columnSearch);
	}
	if ( $where !== '' ) {
		$where = 'WHERE '.$where;
	}
	$sQuery = " SELECT *FROM $stores_table $where $sOrder $sLimit ";
	 /*echo $sQuery;
	die(); */
	$rResult = $wpdb->get_results($sQuery, ARRAY_A);
	 /*print_r($rResult);
	die(); */ 
	$sQuery1 = " SELECT count(*) FROM $stores_table $where"; 
  // echo $sQuery1;
	$sqld = "SELECT rg_store_id, MAX( `date` ) as last_updated, count(rg_store_id) as total 
		FROM $ddeals_table group by rg_store_id";
		$ddData = $wpdb->get_results($sqld);
		$ddarray = array();
		foreach($ddData as $row){
				$ddarray[$row->rg_store_id]['last_updated'] = $row->last_updated;
				$ddarray[$row->rg_store_id]['total'] = $row->total;
		}
		/*print_r($mdData );
		die();*/
	$rResultFilterTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iFilteredTotal = $rResultFilterTotal [0];
	$rResultTotal = $wpdb->get_results($sQuery1, ARRAY_N); 
	$iTotal = $rResultTotal [0];
	$output = array(
		"draw"            => isset ( $_REQUEST['draw'] ) ? intval( sanitize_text_field($_REQUEST['draw']) ) : 0,
		"recordsTotal"    => $iTotal,
		"recordsFiltered" => $iFilteredTotal,
		"data"            => array()
	);
	//echo  $cc =count($aColumns);
	//die();
	foreach($rResult as $aRow)
	{
		/*echo $i++."<br/>";
		die();*/
		$row = array();
		for ( $i=0; $i<6; $i++ )
		{
			if( $i == 0 )
			{
				    $row[] = $aRow[ $aColumns[$i] ];
			} else if( $i == 1 )
			{
				$row[] = '<div class="revglue-banner-thumb"><img class="revglue-unveil" src="'. RGDDEALS__PLUGIN_URL. '/admin/images/loading.gif" data-src="'. $aRow[ $aColumns[$i] ].'" /></div>';
			} else if( $i == 2 )
			{
				$row[] = $aRow[ $aColumns[$i] ];
			}  
			 else if( $i == 3 )
			{
				$uDAte =   @date("d-M-Y", strtotime($ddarray[$aRow[ $aColumns[0] ]]['last_updated'] ));
				 	$uDAte = ($uDAte != "01-Jan-1970") ? $uDAte  : "-"	 .'</div>';  
				  $row[]='<div id="ddeal_updated_'.$aRow[ $aColumns[0] ].'"> '.$uDAte; 
			} 
			 else if( $i == 4 )
			{
					$countofProducts = $ddarray[$aRow[ $aColumns[0] ]]['total'] ? $ddarray[$aRow[ $aColumns[0] ]]['total']  : 0 ;  
					$countofProducts = ($countofProducts != 0) ? $countofProducts  : "0"	 .'</div>';  
					$row[]='<div id="ddeal_fcount_'.$aRow[ $aColumns[0] ].'"> '.$countofProducts; 
			}  else if( $i == 5 )
			{
				$row[]='<div id="ddeal_antiloader_'.$aRow[ $aColumns[0] ].'" class="ddeal_antiloader">
						<a href="javascript:" class=" rg_import_ddeal btn txtwhite btn-primary" data-rg_store_id="'.$aRow[ $aColumns[0] ].'" >Import</a>
					</div>
							<div id="ddeal_loader_'.$aRow[ $aColumns[0] ].'" style="display:none"><img src="'. RGDDEALS__PLUGIN_URL.'/admin/images/loading.gif" >
							<p>Importing products. please wait...</p>
							</div>';
			} 
	}
	$output['data'][] = $row; 
}
    // print_r($output['data']);
	echo json_encode( $output );
	 die(); 
} 
add_action( 'wp_ajax_revglue_ddeals_load_deals_listing', 'revglue_ddeals_load_deals_listing' ); 
?>