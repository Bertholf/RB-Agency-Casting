<?php

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

	global $wpdb;

	global $current_user, $wp_roles;

	get_currentuserinfo();

	$job_id = get_query_var('target');
	$user_linked_id = get_query_var('value');

	// single data only
	$name = $current_user->user_nicename;
	$email = $current_user->user_email;
	$subject = '[Your Subject Here]';
	$contact_display = "Talent";
	$message = "Dear $contact_display,\n\n[Your message here]\n\nRespectfully yours,\n".$current_user->user_nicename;

	// check if this is single email only
	$single_email = false;
	if($user_linked_id != ""){
		$single_email = true;
		$contact_display = RBAgency_Casting::rb_casting_ismodel($user_linked_id, "ProfileContactDisplay",true);
		$message = "Dear $contact_display,\n\n[Your message here]\n\nRespectfully yours,\n".$current_user->user_nicename;
	}

	/*
	 * SEND EMAIL
	 */
	$remarks = ''; 
	if(isset($_POST['send_email'])){

		// validate details
		if($_POST['sender_name'] == ''){
			$remarks = __("Sender name must not be equal to blanks.<br>", RBAGENCY_casting_TEXTDOMAIN);
		}

		if($_POST['sender_email'] == ''){
			$remarks .= __("Sender email must not be equal to blanks.<br>", RBAGENCY_casting_TEXTDOMAIN);
		} else {
			if ( !is_email($_POST['sender_email'])) {
				$remarks .= __("You must enter a valid sender email address.<br />", RBAGENCY_casting_TEXTDOMAIN);
			}
		}

		if($_POST['subject'] == ''){
			$remarks .= __("Subject must not be equal to blanks.<br>", RBAGENCY_casting_TEXTDOMAIN);
		}

		if($_POST['sender_message'] == ''){
				$remarks .= __("You must have a valid message.<br />", RBAGENCY_casting_TEXTDOMAIN);
		}

		if($remarks == ''){

			/*
			 * Actual Single Email Submission
			 */
			if($single_email){

					$recipient = RBAgency_Casting::rb_casting_ismodel($user_linked_id, "ProfileContactEmail");
					if($recipient != ""){
						$headers = 'From: '. get_option('blogname') .' <'. $_POST['sender_email'] .'>' . "\r\n";
						wp_mail($recipient, htmlspecialchars($_POST['subject']) , htmlspecialchars($_POST['sender_message']), $headers); 
						$remarks = __("Message was successfully sent!<br />", RBAGENCY_casting_TEXTDOMAIN);
					} else {
						$remarks = __("Recipients Email is not available.<br />", RBAGENCY_casting_TEXTDOMAIN);
					}

			}

			/*
			 * Actual Multiple Email Submission
			 */
			else {

				$recipients = array();

				//get all emails
				if($job_id == "All"){

					$main_query = $_SESSION['Current_User_Query'];

					$load_emails = $wpdb->get_results($main_query); 

					$recipients = array();

					foreach($load_emails as $user){
						$recipient_email = RBAgency_Casting::rb_casting_ismodel($user->Job_UserLinked, "ProfileContactEmail");
						if(!in_array($recipient_email, $recipients) && $recipient_email != false){
							$recipients[] = $recipient_email;
						}
					}

					if(!empty($recipients)){
						$headers = 'From: '. get_option('blogname') .' <'. $_POST['sender_email'] .'>' . "\r\n";
						wp_mail($recipients, htmlspecialchars($_POST['subject']) , htmlspecialchars($_POST['sender_message']), $headers); 
						$remarks = __("Message was successfully sent!<br />", RBAGENCY_casting_TEXTDOMAIN);
					} else {
						$remarks = __("Recipients Email is not available.<br />", RBAGENCY_casting_TEXTDOMAIN);
					}

				}

				//only selected emails
				else {

					$rec = explode(";",$job_id);

					//get recipients
					$recipient_array = array();
					foreach($rec as $details){
						$r = explode(":",$details);
						$recipient_array[] = $r[1];
					}

					$recipients = array();

					foreach($recipient_array as $userlinked){
						$recipient_email = RBAgency_Casting::rb_casting_ismodel($userlinked, "ProfileContactEmail");
						if(!in_array($recipient_email, $recipients) && $recipient_email != false){
							$recipients[] = $recipient_email;
						}
					}

					if(!empty($recipients)){
						$headers = 'From: '. get_option('blogname') .' <'. $_POST['sender_email'] .'>' . "\r\n";
						wp_mail($recipients, htmlspecialchars($_POST['subject']) , htmlspecialchars($_POST['sender_message']), $headers); 
						$remarks = __("Message was successfully sent!<br />", RBAGENCY_casting_TEXTDOMAIN);
					} else {
						$remarks = __("Recipients Email is not available.<br />", RBAGENCY_casting_TEXTDOMAIN);
					}

				}

			}

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

	if (is_user_logged_in() /*&& RBAgency_Casting::rb_casting_is_castingagent($current_user->ID)*/) {

		echo "<p style='color:red; margin-bottom:20px'>$remarks</p>";

		echo "<h1>".__("Email Applicant",RBAGENCY_casting_TEXTDOMAIN)."</h1>";

		echo "<form method='POST' action='".get_bloginfo('wpurl')."/email-applicant/$job_id/$user_linked_id'>";

		echo "<table>";

		echo "<tr><td>".__("Your Name:",RBAGENCY_casting_TEXTDOMAIN)."  </td></tr><td><input type='text' name='sender_name' value='".$name."'></td></tr>";

		echo "<tr><td>".__("Your Email:",RBAGENCY_casting_TEXTDOMAIN)."  </td></tr><td><input type='text' name='sender_email' value='".$email."'></td></tr>";

		echo "<tr><td>".__("Subject:",RBAGENCY_casting_TEXTDOMAIN)."  </td></tr><td><input type='text' name='subject' style='width:400px' value='".$subject."'></td></tr>";

		echo "<tr><td>".__("Your Messsage:",RBAGENCY_casting_TEXTDOMAIN)."  </td></tr><td><textarea style='width:400px; height:300px' name='sender_message'>".$message."</textarea></td></tr>";

		echo "<tr><td></td></tr><td><input type='submit' name='send_email' value='Send Email' class='button-primary'></td></tr>";

		echo "</table>";

		echo "</form>";

		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/view-applicants'>".__("Go Back to Applicants.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";

		echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/casting-dashboard'>".__("Go Back to Casting Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";


	} else {
		include ("include-login.php");
	}

	//get_sidebar(); 
	echo $rb_footer = RBAgency_Common::rb_footer(); 

?>