<?php

session_start();

global $wpdb;
global $current_user;

// Profile Class
include(rb_agency_BASEREL ."app/profile.class.php");

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

wp_deregister_script('jquery'); 
wp_register_script('jquery', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
wp_enqueue_script('jquery');
wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js');

echo $rb_header = RBAgency_Common::rb_header();

if (is_user_logged_in()) { 

		echo "	<style>
					table td{border:1px solid #CCC;padding:12px;}
					table th{border:1px solid #CCC;padding:12px;}
				</style>";

		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID)){
				echo "<p><h3>Job Postings</h3></p><br>";
		} else {
			if ( current_user_can( 'manage_options' ) ) {
				echo "<p><h3>All Job Postings from Casting Agents</h3></p><br>";
			} elseif ( RBAgency_Casting::rb_casting_is_castingagent($current_user->ID)) {
				echo "<p><h3>Your Job Postings</h3></p><br>";
			}
		}		

		//setup filtering sessions
		if(isset($_POST['filter'])){

			$_SESSION['perpage_browse'] = "";

			// perpage
			if(isset($_POST['filter_perpage']) && $_POST['filter_perpage'] != ""){
				$_SESSION['perpage_browse'] = $_POST['filter_perpage'];
			}	
			// range
			if(isset($_POST['filter_range']) && $_POST['filter_range'] != ""){
				$_SESSION['range'] = $_POST['filter_range'];
			}					

			if(isset($_POST['filter_startdate']) && $_POST['filter_startdate'] != ""){
				$_SESSION['startdate'] = $_POST['filter_startdate'];
			}
		}

		// set for display
		$perpage = (isset($_SESSION['perpage_browse']) && $_SESSION['perpage_browse'] != "") ? $_SESSION['perpage_browse'] : 2;
		$startdate = (isset($_SESSION['startdate']) && $_SESSION['startdate'] != "") ? $_SESSION['startdate'] : "";
		$range = (isset($_SESSION['range']) && $_SESSION['range'] != "") ? $_SESSION['range'] : "";

		echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
		echo '<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery( ".datepicker" ).datepicker();
					jQuery( ".datepicker" ).datepicker("option", "dateFormat", "yy-mm-dd");
					var date_start="'.$startdate.'";
    				jQuery("#filter_startdate").val(date_start);
	      })
		</script>';
		
		// setup filter display
		echo "<form method='POST' action='".get_bloginfo('wpurl')."/browse-jobs/'>";		
		echo "<table style='margin-bottom:20px'>\n";
		echo "<tbody>";
		echo "<tr>";
		echo "        <td>Start Date<br>
						 <select name='filter_range'>
						 	<option value='0' ".selected(0, $range,false).">Before</option>
						 	<option value='1' ".selected(1, $range,false).">Later than</option>
						 	<option value='2' ".selected(2, $range,false).">Exact</option>";
		echo "			 </select>			
						<input type='text' name='filter_startdate' id='filter_startdate' class='datepicker'>
					  </td>\n";

		echo "        <td>Records Per Page<br>
						 <select name='filter_perpage'>
						 	<option value=''>- # of Rec -</option>";
							echo "<option value='2' ".selected(2, $perpage,false).">2</option>";		
							
		$page = 0;
		for($page = 5; $page <= 50; $page += 5){
			echo "<option value='$page' ".selected($page, $perpage,false).">$page</option>";
		}
		
		echo "			 </select>		
					  </td>\n";

					  
		echo "        <td><input type='submit' name='filter' class='button-primary' value='filter'></td>\n";
		echo "    </tr>\n";
		echo "</tbody>";
		echo "</table>";		
		echo "</form>";
		
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
		
		//pagination setup
		$start = get_query_var('target');
		$record_per_page = $perpage;
		$link = get_bloginfo('wpurl') . "/browse-jobs/";
		$table_name = table_agency_casting_job;
		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) || current_user_can( 'manage_options' )){
			$where = ""; 
		} elseif(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) ) {
			$where = "WHERE Job_UserLinked = " . $current_user->ID; 
		}
		$selected_page = get_query_var('target');
		if($start != ""){
			$limit1 = ($start * $record_per_page) - $record_per_page;
		} else {
			$limit1 = 0;
		}
		// end pagination setup

		// load postings for models , talents and admin view
		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) || current_user_can( 'manage_options' )){
			$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job . " LIMIT " . $limit1 . "," . $record_per_page );
		
		// load postings for casting agents view
		} elseif(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) ) {
			$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job . " WHERE Job_UserLinked = " . $current_user->ID . " LIMIT " . $limit1 . "," . $record_per_page );
		}
		
		if(count($load_data) > 0){
			foreach($load_data as $load){
				echo "    <tr>\n";
				echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
				echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
				echo "        <td class=\"column-JobDate\" scope=\"col\">".$load->Job_Date_Start."</td>\n";
				echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Location."</td>\n";
				echo "        <td class=\"column-JobRegion\" scope=\"col\">".$load->Job_Region."</td>\n";
				
				// if model is viewing
				if(RBAgency_Casting::rb_casting_ismodel($current_user->ID)){
					echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>View Details</a></td>\n";
				} else {
					
					//if admin, can only edit his own job postings.
					if(current_user_can( 'manage_options' )){
						if($current_user->ID == RBAgency_Casting::rb_casting_job_ownerid($load->Job_ID)){
							echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/casting-editjob/".$load->Job_ID."'>Edit Job Details</a></td>\n";
						} else {
							echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>View Details</a></td>\n";
						}
						
					//if agent
					} else {
						echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/casting-editjob/".$load->Job_ID."'>Edit Job Details</a></td>\n";
					}
					
				}
				echo "    </tr>\n";
			}
		
			echo "</table>";
			
			// actual pagination
			RBAgency_Casting::rb_casting_paginate($link, $table_name, $where, $record_per_page, $selected_page);
			
		} else {
			
			echo "</table>";
			
			// only admin and casting should post jobs
			if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'manage_options' )){
				echo "<p style=\"width:100%;\">You have no Job Postings.<br>Start New Job Posting <a href='".get_bloginfo('wpurl')."/casting-postjob'>Here.</a></p>\n";
			}
			
		}

		// only admin and casting should have access to casting dashboard
		if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'manage_options' )){
			echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go Back to Casting Dashboard.</a></p>\n";
		}
		
} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 



?>