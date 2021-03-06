<?php

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

// *************************************************************************************************** //
// Respond to Login Request
if ( $_SERVER['REQUEST_METHOD'] == "POST" && !empty( $_POST['action'] ) && $_POST['action'] == 'log-in' ) {

	global $error;
	//$login = wp_login( $_POST['user-name'], $_POST['password'] ); TODO: remove deprecated
	$login = wp_signon( array( 'user_login' => $_POST['user-name'], 'user_password' => $_POST['password'], 'remember' => isset($_POST['remember-me'])?$_POST['remember-me']:false ), false );

    get_currentuserinfo();
    
	if(isset($login->ID)) {
		wp_set_current_user($login->ID);// populate
			get_user_login_info();
	}
}

function get_user_login_info(){

    global $user_ID;
	$redirect = isset($_REQUEST["lastviewed"])?$_REQUEST["lastviewed"]:"";
	get_currentuserinfo();
	$user_info = get_userdata( $user_ID );

	
	$rb_agencyinteract_options_arr = get_option('rb_agencyinteract_options');
	$url = $rb_agencyinteract_options_arr['rb_agencyinteract_option_redirect_afterlogin_agent'];
	if( !empty($url)){
		$customUrl = '/casting-dashboard/';
	}else{
		$customUrl = $rb_agencyinteract_options_arr['rb_agencyinteract_option_redirect_afterlogin_agent_url'];
	}
	
	
	
	if($user_ID){

		// If user_registered date/time is less than 48hrs from now
	
		if(!empty($redirect)){
			header("Location: ". get_bloginfo("wpurl"). "/profile/".$redirect);
		} else {

			// If Admin, redirect to plugin
			if( $user_info->user_level > 7) {
				header("Location: ". admin_url("admin.php?page=rb_agency_menu"));
			} else {
				if(RBAgency_Casting::rb_casting_ismodel($user_ID)){
					
					wp_logout();
					
				} else {
					if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID,'CastingIsActive') == 3){
						header("Location:".get_bloginfo("wpurl").'/casting-pending?status=pending');
					}else{
						header("Location: ". get_bloginfo("wpurl").  $customUrl);
					}
				}
			}
			}
	} elseif(empty($_POST['user-name']) || empty($_POST['password']) ){
		header("Location: ". get_bloginfo("wpurl"));

	} else {
		// Reload
		if(RBAgency_Casting::rb_casting_ismodel($user_ID)){
			
			wp_logout();
			
		} else {
			if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID,'CastingIsActive') == 3){
				header("Location:".get_bloginfo("wpurl").'/casting-pending?status=pending');
			}else{
				header("Location: ". get_bloginfo("wpurl").  $customUrl);
			}
			
		}
	}
}

// ****************************************************************************************** //
// Already logged in 
	if (is_user_logged_in()) {

		global $user_ID; 
		$login = get_userdata( $user_ID );
		get_user_login_info();


// ****************************************************************************************** //
// Not logged in
	} else {

		// *************************************************************************************************** //
		// Prepare Page
		echo $rb_header = RBAgency_Common::rb_header();

		echo "<div id=\"rbcontent\" class=\"rb-interact rb-interact-login\">\n";

			// Show Login Form
			$hideregister = true;

			require_once("include-login.php");

			// Get Footer
			echo $rb_footer = RBAgency_Common::rb_footer();

	}// Done

?>