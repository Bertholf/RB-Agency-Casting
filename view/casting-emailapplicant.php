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
	$user_linked_id = get_query_var('value');
	
	$contact_display = RBAgency_Casting::rb_casting_ismodel($user_linked_id, "ProfileContactDisplay");
	
	//setup initial details
	$name = $current_user->user_nicename;
	$email = $current_user->user_email;
	$subject = '[Your Subject Here]';
	$message = "Dear $contact_display,\n\n[Your message here]\n\nRespectfully yours,\n".$current_user->user_nicename;
	
	/*
	 * SEND EMAIL
	 */	
	$remarks = ''; 
	if(isset($_POST['send_email'])){
		
		// validate details
		if($_POST['sender_name'] == ''){
			$remarks = __("Sender name must not be equal to blanks.<br>", rb_agency_casting_TEXTDOMAIN);
		}

		if($_POST['sender_email'] == ''){
			$remarks .= __("Sender email must not be equal to blanks.<br>", rb_agency_casting_TEXTDOMAIN);
		} else {
			if ( !is_email($_POST['sender_email'], true)) {
				$remarks .= __("You must enter a valid sender email address.<br />", rb_agency_casting_TEXTDOMAIN);
			}		
		}
		
		if($_POST['subject'] == ''){
			$remarks .= __("Subject must not be equal to blanks.<br>", rb_agency_casting_TEXTDOMAIN);
		}
				
		if($_POST['sender_message'] == ''){
				$remarks .= __("You must have a valid message.<br />", rb_agency_casting_TEXTDOMAIN);
		}
		
		if($remarks == ''){
			$remarks = "Message was successfully sent!<br>";
		}
		
		//reset for post details
		$name = $_POST['sender_name'];
		$email = $_POST['sender_email'];
		$subject = $_POST['subject'];
		$message = $_POST['sender_message'];
		
	} 
   
	
	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery_latest');

	echo $rb_header = RBAgency_Common::rb_header();

	if (is_user_logged_in() && RBAgency_Casting::rb_casting_is_castingagent($current_user->ID)) { 	
		
		echo "<p style='color:red; margin-bottom:20px'>$remarks</p>";
		
		echo "<h1>Email Applicant</h1>";
		
		echo "<form method='POST' action='".get_bloginfo('wpurl')."/email-applicant/$job_id/$user_linked_id'>";
		
		echo "<table>";
		
		echo "<tr><td> Your Name: </td></tr><td><input type='text' name='sender_name' value='".$name."'></td></tr>";

		echo "<tr><td> Your Email: </td></tr><td><input type='text' name='sender_email' value='".$email."'></td></tr>";
		
		echo "<tr><td> Subject: </td></tr><td><input type='text' name='subject' style='width:400px' value='".$subject."'></td></tr>";

		echo "<tr><td> Your Messsage: </td></tr><td><textarea style='width:400px; height:300px' name='sender_message'>".$message."</textarea></td></tr>";

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