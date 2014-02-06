<?php
// *************************************************************************************************** //
// Admin Head Section 

/*	add_action('admin_head', 'rb_agency_casting_admin_head');
		function rb_agency_casting_admin_head(){
		  if( is_admin() ) {
			echo "<link rel=\"stylesheet\" href=\"". rb_agency_casting_BASEDIR ."style/admin.css\" type=\"text/css\" media=\"screen\" />\n";
		  }
		}*/
	
	// Remember to flush_rules() when adding rules
	add_filter('init','rbcasting_flushrules');
		function rbcasting_flushRules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
	}

	// Adding a new rule
	add_filter('rewrite_rules_array','rb_agency_casting_rewriteRules');
		function rb_agency_casting_rewriteRules($rules) {
			$newrules = array();
			$newrules['casting-manage'] = 'index.php?type=castingmanage';
			$newrules['casting-dashboard'] = 'index.php?type=castingoverview';
			$newrules['casting-register'] = 'index.php?type=castingregister';
			$newrules['casting-login'] = 'index.php?type=castinglogin';
			$newrules['profile-casting/(.*)$'] = 'index.php?type=casting&target=$matches[1]';
			$newrules['profile-casting'] = 'index.php?type=casting&target=casting';
			$newrules['client-view/(.*)$'] = 'index.php?type=profilecastingcart&target=$matches[1]';
			$newrules['profile-favorite'] = 'index.php?type=favorite';
			return $newrules + $rules;
		}
	
	// Set Custom Template
	add_filter('template_include', 'rb_agency_casting_template_include', 1, 1); 
		function rb_agency_casting_template_include( $template ) {
			if ( get_query_var( 'type' ) ) {
			  if (get_query_var( 'type' ) == "castingoverview") {
				return dirname(__FILE__) . '/view/casting-overview.php'; 
			  } elseif (get_query_var( 'type' ) == "castingmanage") {
				return dirname(__FILE__) . '/view/casting-manage.php'; 
			  } elseif (get_query_var( 'type' ) == "castinglogin") {
				return dirname(__FILE__) . '/view/casting-login.php'; 
			  } elseif (get_query_var( 'type' ) == "castingregister") {
				return dirname(__FILE__) . '/view/casting-register.php'; 
			  }	elseif (get_query_var( 'type' ) == "favorite") {
				return dirname(__FILE__) . '/view/profile-favorite.php';
			  } elseif (get_query_var( 'type' ) == "casting") {
				return dirname(__FILE__) . '/view/profile-viewcasting.php';
			  }	elseif (get_query_var( 'type' ) == "profilecastingcart") {
				return rb_agency_BASEREL . 'view/profile-castingcart.php';
			  } 
			}
			return $template;
		}
	
	function get_state_json(){
		global $wpdb;
		$states=array();
		$country=$_POST['countryid'];
		$query_get ="SELECT * FROM ".table_agency_data_state." WHERE CountryID='".$country."'";
		$result_query_get = $wpdb->get_results($query_get);
		echo json_encode($result_query_get);
		die();	
	}
    add_action('wp_ajax_get_state_json', 'get_state_json');
    add_action('wp_ajax_nopriv_get_state_json', 'get_state_json');	

	/*/
	 *  Fix form post url for multi language.
	/*/
/*
	function rb_agency_casting_postURILanguage($request_URI){
	     if(!in_array(substr($_SERVER['REQUEST_URI'],1,2), array("en","nl"))){
			if (function_exists('trans_getLanguage')) {
				 if(qtrans_getLanguage()=='nl') {
					return "/".qtrans_getLanguage();
				
				} elseif(qtrans_getLanguage()=='en') {
					return "/".qtrans_getLanguage();
				}
			 }
	    }
	}
	*/

// *************************************************************************************************** //
// Handle Emails

	// Make Directory for new profile
/*     function rb_agency_casting_checkdir($ProfileGallery){
	      	
			if (!is_dir(rb_agency_UPLOADPATH . $ProfileGallery)) {
				mkdir(rb_agency_UPLOADPATH . $ProfileGallery, 0755);
				chmod(rb_agency_UPLOADPATH . $ProfileGallery, 0777);
			}
			return $ProfileGallery;
     }*/



// *************************************************************************************************** //
// Functions

	// Move Login Page	
/*	add_filter("login_init", "rb_agency_casting_login_movepage", 10, 2);
		function rb_agency_casting_login_movepage( $url ) {
			global $action;
		
			if (empty($action) || 'login' == $action) {
				wp_safe_redirect(get_bloginfo("wpurl"). "/profile-login/");
				die;
			}
		}

	// Redirect after Login
	add_filter('login_redirect', 'rb_agency_casting_login_redirect', 10, 3);	
		function rb_agency_casting_login_redirect() {
			global $user_ID, $current_user, $wp_roles;
			if( $user_ID ) {
				$user_info = get_userdata( $user_ID ); 

				if( current_user_can( 'manage_options' )) {
					header("Location: ". get_bloginfo("wpurl"). "/wp-admin/");
				} elseif ( strtotime( $user_info->user_registered ) > ( time() - 172800 ) ) {
					// If user_registered date/time is less than 48hrs from now
					// Message will show for 48hrs after registration
					header("Location: ". get_bloginfo("wpurl"). "/profile-member/account/");
				} else {
					header("Location: ". get_bloginfo("wpurl"). "/profile-member/");
				}
			}
		}*/




?>