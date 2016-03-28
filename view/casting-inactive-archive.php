<?php
/*
Template Name: 	Member Details
 * @name		Member Details
 * @type		PHP page
 * @desc		Member Details
*/

if (!headers_sent()) {
header("Cache-control: private"); //IE 6 Fix
}
global $wpdb;
global $current_user;
get_currentuserinfo();

// Change Title
add_filter('wp_title', 'rb_agencyinteractive_override_title', 10, 2);
	function rb_agencyinteractive_override_title(){
		return "Profile Pending";
	}
$profile_gallery = $wpdb->get_row($wpdb->prepare("SELECT ProfileGallery FROM ".table_agency_profile." WHERE ProfileUserLinked = %d",$current_user->ID));
		
/* Display Page ******************************************/ 


// Call Header
echo $rb_header = RBAgency_Common::rb_header();
	
	echo "	<div id=\"primary\" class=\"rb-agency-interact rb-agency-interact-overview\">\n";
	echo "  	<div id=\"rbcontent\">\n";
         if(is_user_logged_in()){
			echo "	<div id=\"profile-manage\" class=\"profile-overview\">\n";

					echo " <div class=\"manage-overview manage-content\">\n";
					echo sprintf(__("Thanks for joining %s!",RBAGENCy_casting_TEXTDOMAIN), get_option('blogname'));
					echo "<br/>";
					echo __("Your account is currently set as Inactive or Archived. You will receive an email once your registration is approved or activated by the admin.",RBAGENCy_casting_TEXTDOMAIN);
					echo " </div>\n";

			echo " </div>\n"; // .welcome
			//be sure to logout the profile
			wp_logout();
		
		} else {

			// Show Login Form
			include("include-login.php");
		}
		
	echo "  </div><!-- #rbcontent -->\n";
	echo "</div><!-- #primary -->\n";

// Call Footer
echo $rb_footer = RBAgency_Common::rb_footer();
?>			