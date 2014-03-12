<?php

	session_start();

	header("Cache-control: private"); //IE 6 Fix

	// Profile Class
	include(rb_agency_BASEREL ."app/profile.class.php");
	
	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

	global $wpdb;
	
	global $current_user, $wp_roles;
	
	get_currentuserinfo();
	
	$job_id = get_query_var('target');
	
	$profile_id = get_query_var('value');
	
	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery_latest');

	echo $rb_header = RBAgency_Common::rb_header();

	if (is_user_logged_in() && RBAgency_Casting::rb_casting_is_castingagent($current_user->ID)) { 	
		
		echo "<h1>Email Applicant</h1>";
		
		echo "<form method='POST' action='".get_bloginfo('wpurl')."/email-applicant'>";
		
		echo "<table>";
		
		echo "<tr><td> Your Name: </td></tr><td><input type='text' name='sender_name' value=''></td></tr>";

		echo "<tr><td> Your Email: </td></tr><td><input type='text' name='sender_email' value=''></td></tr>";
		
		echo "<tr><td> Subject: </td></tr><td><input type='text' name='subject' value=''></td></tr>";

		echo "<tr><td> Your Messsage: </td></tr><td><textarea style='width:400px; height:300px' name='sender_message'></textarea></td></tr>";

		echo "<tr><td></td></tr><td><input type='submit' name='send_email' value='Send Email' class='button-primary'></td></tr>";
		
		echo "</table>";
				
		echo "</form>";

		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/view-applicants'>Go Back to Applicants.</a></p>\n";

		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go Back to Casting Dashboard.</a></p>\n";
			

	} else {
		include ("include-login.php");
	}
	
	//get_sidebar(); 
	echo $rb_footer = RBAgency_Common::rb_footer(); 

?>