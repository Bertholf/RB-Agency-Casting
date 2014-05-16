<?php

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");
	
// *************************************************************************************************** //
// Respond to Login Request
if ( $_SERVER['REQUEST_METHOD'] == "POST" && !empty( $_POST['action'] ) && $_POST['action'] == 'log-in' ) {

	global $error;
	$login = wp_login( $_POST['user-name'], $_POST['password'] );
	$login = wp_signon( array( 'user_login' => $_POST['user-name'], 'user_password' => $_POST['password'], 'remember' => $_POST['remember-me'] ), false );
	
    get_currentuserinfo();
    
	if($login->ID) {
    	wp_set_current_user($login->ID);  // populate
	   	get_user_login_info();
	}
}

function get_user_login_info(){

    global $user_ID;
	$redirect = isset($_POST["lastviewed"])?$_POST["lastviewed"]:"";
	get_currentuserinfo();
	$user_info = get_userdata( $user_ID );

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
					header("Location: ". get_bloginfo("wpurl"). "/profile-member/");
				} else {
					header("Location: ". get_bloginfo("wpurl"). "/casting-dashboard/");
				}
			}
	  	}
	} elseif(empty($_POST['user-name']) || empty($_POST['password']) ){
		header("Location: ". get_bloginfo("wpurl"));

	} else {
		// Reload
		if(RBAgency_Casting::rb_casting_ismodel($user_ID)){
			header("Location: ". get_bloginfo("wpurl"). "/profile-member/");
		} else {
			header("Location: ". get_bloginfo("wpurl"). "/casting-dashboard/");
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

	} // Done

?>