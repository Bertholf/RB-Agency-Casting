<?php
global $wpdb;
global $current_user;

      get_currentuserinfo();


//bulk delete
if(isset($_POST['delete_bulk'])){
	if(is_array($_POST['job_checkbox'])){
		foreach($_POST['job_checkbox'] as $val){
			casting_deletejob(array('jobID' => $val));
		}
		
		$_SESSION['job_delete_bulk'] = count($_POST['job_checkbox']);
		//redirect.. part of security so our codes cant affect by refresh re-send post.
		wp_redirect(get_bloginfo('wpurl')."/browse-jobs/?delete");
		exit;
	}
}

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

wp_deregister_script('jquery'); 
wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
wp_enqueue_script('jquery_latest');
wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js');






echo $rb_header = RBAgency_Common::rb_header();
if (is_user_logged_in()) {

	if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID')){
		$is_active = rb_check_profile_status($current_user->ID);
	}else{
		$is_active = rb_check_casting_status($current_user->ID);
	}
	
	if($is_active == false and !current_user_can("edit_posts")){
		echo '		
			<div id="rbcontent" role="main">
			
				<header class="entry-header">
				<h1 class="entry-title">'.__("You are not permitted to access this page.",RBAGENCY_casting_TEXTDOMAIN).'</h1>
				</header>
				<div class="entry-content">
				<p class="rbalert error">
					<strong></strong>
				</p>
			</div>';
		echo $rb_footer = RBAgency_Common::rb_footer(); 
		exit;
	}

	echo "<div id=\"rbcontent\">";

		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID)){
				echo "<p><h3>".__("Job Postings",RBAGENCY_casting_TEXTDOMAIN)."</h3></p><br>";
		} else {
			if ( current_user_can( 'edit_posts' ) ) {
				echo "<p><h3>".__("All Job Postings from Casting Agents",RBAGENCY_casting_TEXTDOMAIN)."</h3></p><br>";
			} elseif ( RBAgency_Casting::rb_casting_is_castingagent($current_user->ID)) {
				echo "<p><h3>".__("Your Job Postings",RBAGENCY_casting_TEXTDOMAIN)."</h3></p><br>";
			}
		}
		
		
		if(isset($_GET['delete']) and !empty($_SESSION['job_delete_bulk'])){
			$_pluralJobs = (int)$_SESSION['job_delete_bulk'] > 1 ? 'Jobs' : 'job';
			echo "<p><b>".$_SESSION['job_delete_bulk']." $_pluralJobs deleted.</b></p><br>";
			unset($_SESSION['job_delete_bulk']);
		}

		//setup filtering sessions
		if(isset($_POST['filter'])){

			$_SESSION['perpage_browse'] = "";
			$_SESSION['range'] = "";
			$_SESSION['startdate'] = "";
			$_SESSION['location'] = "";

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

				//validate date
				list($y,$m,$d)= explode('-',$_SESSION['startdate']);
				if(checkdate($m,$d,$y)!==true){
					$_SESSION['startdate'] = "";
				}
			}

			if(isset($_POST['filter_location']) && $_POST['filter_location'] != ""){
				$_SESSION['location'] = $_POST['filter_location'];
			}


		}

		// set for display
		$perpage = (isset($_SESSION['perpage_browse']) && $_SESSION['perpage_browse'] != "") ? $_SESSION['perpage_browse'] : 2;
		$startdate = (isset($_SESSION['startdate']) && $_SESSION['startdate'] != "") ? $_SESSION['startdate'] : "";
		$range = (isset($_SESSION['range']) && $_SESSION['range'] != "") ? $_SESSION['range'] : "";
		$location = (isset($_SESSION['location']) && $_SESSION['location'] != "") ? $_SESSION['location'] : "";

		echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
		echo '<script type="text/javascript">
				jQuery(document).ready(function($){
					jQuery( ".datepicker" ).datepicker({
						dateFormat: "yy-mm-dd"
					});
					
					
					var date_start="'.$startdate.'";
					jQuery("#filter_startdate").val(date_start);
					
					';
					
		if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'edit_posts' )){
					echo '
					jQuery(".delete_jobcast").on( "click", function() {
						if (confirm("Are you sure you want to delete this Job") == true){
							var Job_ID = $(this).attr("job_id");
	                        $.post( "'.admin_url('admin-ajax.php').'", { jobID: Job_ID, action: "casting_deletejob" })
						        .done(function( data ) {
						            $(".job_"+Job_ID+" td").fadeOut();
							        console.log( data );
						    });
                        }
                        return false;
				    });
				    
				    jQuery(".job_checkbox_all").on( "click", function() {
						if($(this).prop("checked")){
							$(".job_checkbox").prop("checked" , true);
						}else{
							$(".job_checkbox").prop("checked" , false);
						}
				    });
				    
				    jQuery(".delete_bulk").on( "click", function() {
				        if($(".job_checkbox:checked").length <= 0 ){
				            alert("There\'s no any selected job to delete.");
				            return false;
				        }else{
				            if (confirm("Are you sure you want to delete all selected Jobs") == true){
				                return true;
				            }
				            return false;
				        }
				    });
				    ';
		}
		echo '    
			});
		</script>
		<style>
			#filter_startdate{
				background-image: url('.RBAGENCY_PLUGIN_URL.'assets/img/calendar_icon.png);
				background-repeat:no-repeat;
				background-position: right center;
				background-position: right;
				background-position-y: 4px;
			}
		</style>
		
		';

		// setup filter display
		echo "<form id=\"jobposting-filter\" method='POST' action='".get_bloginfo('wpurl')."/browse-jobs/'>";
		echo "<table class='table-filter'>\n";
		echo "<tbody>";
		echo "<tr>";
		echo "        <td>".__("Start Date",RBAGENCY_casting_TEXTDOMAIN)."<br>
						<div class='tdbox'>
							<select name='filter_range'>
							<option value='0' ".selected(0, $range,false).">".__("Before",RBAGENCY_casting_TEXTDOMAIN)."</option>
							<option value='1' ".selected(1, $range,false).">".__("Later than",RBAGENCY_casting_TEXTDOMAIN)."</option>
							<option value='2' ".selected(2, $range,false).">".__("Exact",RBAGENCY_casting_TEXTDOMAIN)."</option>";
		echo "				</select>
						</div>
						<div class='tdbox'>
							<input type='text' name='filter_startdate' id='filter_startdate' class='datepicker'>
						</div>
						</td>\n";

		echo "        <td>".__("Location",RBAGENCY_casting_TEXTDOMAIN)."<br>
						<select name='filter_location'>
							<option value=''>".__("-- Select Location --",RBAGENCY_casting_TEXTDOMAIN)."</option>";

		if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) ) {
			$get_all_loc = "SELECT DISTINCT LOWER(Job_Location) as Location FROM " . table_agency_casting_job . " WHERE Job_UserLinked = " . $current_user->ID;
		} else {
			$get_all_loc = "SELECT DISTINCT LOWER(Job_Location) as Location FROM " . table_agency_casting_job;
		}
		$result_loc = $wpdb->get_results($get_all_loc,ARRAY_A);
		$count = $wpdb->num_rows;
		if($count > 0){
			foreach($result_loc as $loc){
				echo "<option value='".$loc['Location']."' ".selected(strtolower($loc['Location']),strtolower($location),false).">" .$loc['Location'] . "</option>";
			}
		}

		echo "				</select> 
					</td>\n";

		echo "        <td>".__("Records Per Page",RBAGENCY_casting_TEXTDOMAIN)."<br>
						<select name='filter_perpage'>
							<option value=''>".__("- # of Rec -",RBAGENCY_casting_TEXTDOMAIN)."</option>";
							echo "<option value='2' ".selected(2, $perpage,false).">2</option>";

		$page = 0;
		for($page = 5; $page <= 50; $page += 5){
			echo "<option value='$page' ".selected($page, $perpage,false).">$page</option>";
		}

		echo "			</select>
						</td>\n";


		echo "        <td><input type='submit' name='filter' class='button-primary' value='".__("filter",RBAGENCY_casting_TEXTDOMAIN)."'></td>\n";
		echo "    </tr>\n";
		echo "</tbody>";
		echo "</table>";
		echo "</form>";

		echo "<form method=\"post\" id=\"job-postings\">\n";
		echo "<table cellspacing=\"0\" class=\"widefat fixed rbtable\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"column-checkbox\"  scope=\"col\" style=\"width:30px;\"><input type='checkbox' value='0' class='job_checkbox_all' name='job_checkbox_all'></th>\n";
		echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\">".__("ID",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\">".__("Job Title",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobDate\" id=\"JobDate\" scope=\"col\">".__("Start Date",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobLocation\" id=\"JobLocation\" scope=\"col\">".__("Location",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobRegion\" id=\"JobRegion\" scope=\"col\">".__("Region",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobDatePosted\" id=\"JobDatePosted\" scope=\"col\">".__("Date Posted",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "        <th class=\"column-JobActions\" id=\"JobActions\" scope=\"col\">".__("Actions",RBAGENCY_casting_TEXTDOMAIN)."</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";

		//pagination setup
		$start = get_query_var('target');
		$record_per_page = $perpage;
		$link = get_bloginfo('wpurl') . "/browse-jobs/";
		$table_name = table_agency_casting_job;

		// setup range date for start date
		$filter='';
		if($startdate!=''){
			if($range == 0){
				$filter = "Job_Date_Start < '". $startdate ."'"; 
			} elseif($range == 1){
				$filter = "Job_Date_Start > '". $startdate ."'"; 
			} elseif($range == 2){
				$filter = "Job_Date_Start = '". $startdate ."'"; 
			}
		}

		// setup location filter
		if($location != ''){
			if($filter != ''){
				$filter .= " AND ";
			}

			$filter .= "LOWER(Job_Location) = '" . strtolower($location) . "'";

		}

		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) || current_user_can( 'edit_posts' )){
			if($filter!=''){
				$where = "WHERE $filter"; 
			} else {
				$where = ""; 
			}
		} elseif(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) ) {
			$AND = ($filter != '') ? " AND $filter" : "";
			$where = "WHERE Job_UserLinked = " . $current_user->ID . $AND; 
		}
		$selected_page = get_query_var('target');
		if($start != ""){
			$limit1 = ($start * $record_per_page) - $record_per_page;
		} else {
			$limit1 = 0;
		}
		// end pagination setup

		$profileUserID = "";
		$userIDS = $wpdb->get_results("SELECT ProfileID,ProfileUserLinked FROM wp_agency_profile WHERE ProfileUserLinked = $current_user->ID");

		foreach($userIDS as $user){
			$profileUserID = $user->ProfileID;
		}
		
		// load postings for models , talents and admin view
		//$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job . " " . $where . " LIMIT " . $limit1 . "," . $record_per_page );
		$load_data = $wpdb->get_results("SELECT jobs.*, agency.* FROM wp_agency_casting_job jobs, wp_agency_casting as agency WHERE jobs.Job_ID > 0 AND agency.CastingUserLinked = jobs.Job_UserLinked");
		
		if(count($load_data) > 0){
			foreach($load_data as $load){

				if($load->Job_Visibility == 0){
					@$find = strpos($load->Job_Talents,$profileUserID);
					if($find !== false ){
									echo "    <tr class=\"job_".$load->Job_ID."\">\n";
									echo "        <td class=\"column-checkbox\" scope=\"col\" style=\"width:30px;\"><input type='checkbox' class='job_checkbox' name='job_checkbox[]' value='".$load->Job_ID."'/></td>\n";
									echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
									echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
									echo "        <td class=\"column-JobDate\" scope=\"col\">".$load->Job_Date_Start."</td>\n";
									echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Location."</td>\n";
									echo "        <td class=\"column-JobRegion\" scope=\"col\">".$load->Job_Region."</td>\n";
									echo "        <td class=\"column-JobDateCreated\" scope=\"col\">".date("M d, Y - h:iA",strtotime($load->Job_Date_Created))."</td>\n";

									// if model is viewing
									if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID')){
										echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>".__("View Details",RBAGENCY_casting_TEXTDOMAIN)."</a></td>\n";
									} else {

										//if admin, can only edit his own job postings.
										if(current_user_can( 'edit_posts' ) || ($current_user->ID == RBAgency_Casting::rb_casting_job_ownerid($load->Job_ID)) ){
											if($current_user->ID == RBAgency_Casting::rb_casting_job_ownerid($load->Job_ID)){
												echo "        <td class=\"column-JobActions\" scope=\"col\">
																<a href='".get_bloginfo('wpurl')."/casting-editjob/".$load->Job_ID."'>".__("Edit Job Details",RBAGENCY_casting_TEXTDOMAIN)."</a><br>
																<a href='".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$load->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_perpage=5&filter=filter'>".__("View Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a>
																<br>
																<a href='#' job_id='".$load->Job_ID."' class='delete_jobcast'>".__("Delete Job",RBAGENCY_casting_TEXTDOMAIN)."</a><br/>
																</td>\n";
											} else {
												echo "        <td class=\"column-JobActions\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>".__("View Details",RBAGENCY_casting_TEXTDOMAIN)."</a><br>
																<a href='".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$load->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_perpage=5&filter=filter'>".__("View Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a>
																</td>\n";
											}

										//if agent
										} else {
											echo "        <td class=\"column-JobActions\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/casting-postjob/".$load->Job_ID."'>".__("View Details",RBAGENCY_casting_TEXTDOMAIN)."</a></td>\n";
										}

									}
									echo "    </tr>\n";
					} //  end strpos
				}// end visibility 0
				elseif($load->Job_Visibility == 1){
					echo "    <tr class=\"job_".$load->Job_ID."\">\n";
									echo "        <td class=\"column-checkbox\" scope=\"col\" style=\"width:30px;\"><input type='checkbox' class='job_checkbox' name='job_checkbox[]' value='".$load->Job_ID."'/></td>\n";
									echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
									echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
									echo "        <td class=\"column-JobDate\" scope=\"col\">".$load->Job_Date_Start."</td>\n";
									echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Location."</td>\n";
									echo "        <td class=\"column-JobRegion\" scope=\"col\">".$load->Job_Region."</td>\n";
									echo "        <td class=\"column-JobDateCreated\" scope=\"col\">".date("M d, Y - h:iA",strtotime($load->Job_Date_Created))."</td>\n";

									// if model is viewing
									if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID')){
										echo "        <td class=\"column-JobType\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>".__("View Details",RBAGENCY_casting_TEXTDOMAIN)."</a></td>\n";
									} else {

										//if admin, can only edit his own job postings.
										if(current_user_can( 'edit_posts' ) || ($current_user->ID == RBAgency_Casting::rb_casting_job_ownerid($load->Job_ID)) ){
											if($current_user->ID == RBAgency_Casting::rb_casting_job_ownerid($load->Job_ID)){
												echo "        <td class=\"column-JobActions\" scope=\"col\">
																<a href='".get_bloginfo('wpurl')."/casting-editjob/".$load->Job_ID."'>Edit Job Details</a><br>
																<a href='".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$load->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_perpage=5&filter=filter'>".__("View Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a>
																<br>
																<a href='#' job_id='".$load->Job_ID."' class='delete_jobcast'>Delete Job</a><br/>
																</td>\n";
											} else {
												echo "        <td class=\"column-JobActions\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/job-detail/".$load->Job_ID."'>View Details</a><br>
																<a href='".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$load->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_perpage=5&filter=filter'>".__("View Applicants",RBAGENCY_casting_TEXTDOMAIN)."</a>
																</td>\n";
											}

										//if agent
										} else {
											echo "        <td class=\"column-JobActions\" scope=\"col\"><a href='".get_bloginfo('wpurl')."/casting-postjob/".$load->Job_ID."'>".__("View Details",RBAGENCY_casting_TEXTDOMAIN)."</a></td>\n";
										}

									}
									echo "    </tr>\n";
				}

				

			}

			echo "</table>";

			echo "<footer>";
			// actual pagination
			RBAgency_Casting::rb_casting_paginate($link, $table_name, $where, $record_per_page, $selected_page);

		} else {


			echo "<tr><td colspan='8'>";
			// only admin and casting should post jobs
			if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'edit_posts' )){
				echo "<p>".__("You have no Job Postings.<br>Start New Job Posting",RBAGENCY_casting_TEXTDOMAIN)." <a href='".get_bloginfo('wpurl')."/casting-postjob'>".__("Here.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";
			} else {
				echo "<p>".__("There are no available job postings.",RBAGENCY_casting_TEXTDOMAIN)."</p>\n";
			}
			echo "</td></tr>";

			echo "</table>";			

			echo "<footer>";

		}		
		
		// only admin and casting should have access to casting dashboard
		if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'edit_posts' )){
			echo "<div class='jobposting-actions'>";
			echo "<input type='submit' name='delete_bulk' class='delete_bulk' value='".__("Delete",RBAGENCY_casting_TEXTDOMAIN)."'>";
			echo "</div>";
			echo "<div class='footer-links'><a href='".get_bloginfo('wpurl')."/casting-dashboard' class=\"pure-button\">".__("Go Back to Casting Dashboard",RBAGENCY_casting_TEXTDOMAIN)."</a></div>\n";
		}

		// for models
		if(RBAgency_Casting::rb_casting_ismodel($current_user->ID)){
			echo "<div class='footer-links'><a href='".get_bloginfo('wpurl')."/profile-member' class=\"pure-button\">".__("Go Back to Profile Dashboard",RBAGENCY_casting_TEXTDOMAIN)."</a></div>\n";
		}

		echo "</footer>";
		
		echo "</form>";
		
	echo "</div> <!-- #rbcontent -->";

} else {
	include ("include-login.php");
}

//get_footer(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 

?>
