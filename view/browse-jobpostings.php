<?php

global $wpdb;
global $current_user;

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

echo $rb_header = RBAgency_Common::rb_header();

if (is_user_logged_in()) { 

	if(RBAgency_Casting::rb_casting_ismodel($current_user->ID)){
		
		echo "	<style>
					table td{border:1px solid #CCC;padding:12px;}
					table th{border:1px solid #CCC;padding:12px;}
				</style>";
		
		echo "<p><h3>Browse Job Postings</h3></p><br>";
		
		echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\">ID</th>\n";
		echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\">Job Title</th>\n";
		echo "        <th class=\"column-JobDate\" id=\"JobDate\" scope=\"col\">Start Date</th>\n";
		echo "        <th class=\"column-JobLocation\" id=\"ProfilesProfileDate\" scope=\"col\">Location</th>\n";
		echo "        <th class=\"column-JobRegion\" id=\"ProfileLocationCity\" scope=\"col\">Region</th>\n";
		echo "        <th class=\"column-JobRegion\" id=\"ProfileLocationCity\" scope=\"col\">Job Details</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";
		
		// load all job postings
		$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job);
		if(count($load_data) > 0){
			foreach($load_data as $load){
				echo "    <tr>\n";
				echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
				echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
				echo "        <td class=\"column-JobDate\" scope=\"col\">".$load->Job_Date_Start."</td>\n";
				echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Location."</td>\n";
				echo "        <td class=\"column-JobRegion\" scope=\"col\">".$load->Job_Region."</td>\n";
				echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>View Details</a></td>\n";
				echo "    </tr>\n";
			}
		}
		
		echo "</table>";
	
	} else {

			echo "<p><h3>Only models and talents  has the permission to browse Jobs.</h3></p><br>";	
	
	}

} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 



?>