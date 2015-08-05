<?php
global $wpdb;
global $current_user;

// include casting class
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

wp_deregister_script('jquery'); 
wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
wp_enqueue_script('jquery_latest');

// rb agency settings
	$rb_agency_options = get_option('rb_agency_options');
	$rb_agency_option_allowsendemail = isset($rb_agency_options["rb_agency_option_allowsendemail"])?$rb_agency_options["rb_agency_option_allowsendemail"]:""; 
	$rb_agency_option_agencyemail = $rb_agency_options["rb_agency_option_agencyemail"];


echo $rb_header = RBAgency_Common::rb_header();?>

<script type="text/javascript">
jQuery(document).ready(function(){
	jQuery(".send_invite").click(function(){
		jQuery(this).html("Sending...");
		jQuery(this).html("Invited.");
	});

	jQuery("#sel_all").click(function(){
		if(jQuery(this).is(':checked')){
			jQuery(".select_app").attr("checked",true);
		} else {
			jQuery(".select_app").attr("checked",false);
		}
	});

	jQuery("#action_submit").click(function(){

		if(jQuery("#action_dropdown").val() == ''){
			alert("You need to select an action first to proceed.");
		} else {
			if(jQuery("#action_dropdown").val() == '2'){

					var data = "";
					jQuery(".select_app:checked").each(function(){
						data = data + ";" + jQuery(this).val();
					});

					if(data == ""){
						alert("You must select at least one from the applicants list before proceeding!");
					} else {
						var loader = "<?php echo plugins_url('rb-agency-casting/view/loader.gif'); ?>";

						jQuery(this).nextAll("#re_bottom").html("<img src='"+loader+"'>");

						jQuery.ajax({
								type: "POST",
								url: "<?php echo admin_url('admin-ajax.php') ?>",
								dataType: 'json',
								data: {
									action: "client_add_casting",
									'talent_id' : data,
									'job_id': "none"
								},
								success: function (results) {
									if(results.data == "success"){
										window.location.href = window.location.pathname;
									}
								}
						});
					}
			} else {

				if(jQuery("#action_dropdown").val() == '1'){
					if(jQuery(".select_app").length > 0){
						var $href = "All";
					} else {
						var $href = "";
					}
				} else if(jQuery("#action_dropdown").val() == '0'){
					var $href = "";
					jQuery(".select_app:checked").each(function(){
						$href = $href + ";" + jQuery(this).val();
					});
				}
				if($href == ""){
					alert("You must select a recipient from applicant lists before proceeding!");
				} else {
					window.location.href = "<?php echo get_bloginfo('wpurl') ?>/email-applicant/" + $href;
				}

			}
		}
	});

	jQuery(".star").mouseover(function(){

		jQuery(this).css('background-position','0px 0px');
		jQuery(this).prevAll(".star").css('background-position','0px 0px');
		jQuery(this).nextAll(".star").css('background-position','0px -15px');
		var count = jQuery(this).prevAll(".star").length + 1;
		jQuery(this).parent().nextAll('.clients_rating').eq(0).val(count);

	});

	jQuery(".rate").click(function(){
		// TODO PATH INVALID
		var loader = "<?php echo plugins_url('rb-agency-casting/view/loader.gif'); ?>";
		var check = "<?php echo plugins_url('rb-agency-casting/view/check.png'); ?>";

		jQuery(this).nextAll(".loading").html("<img src='"+loader+"'>");

		var app_id = jQuery(this).prevAll(".application_id").eq(0).val();

		var rating = jQuery(this).prevAll(".clients_rating").eq(0).val();

		var loading = jQuery(this).nextAll(".loading");

		jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				data: {
					action: "rate_applicant",
					'application_id': app_id,
					'clients_rating': rating
				},
				success: function (results) {
					loading.html("<img src='"+check+"'>");
				}
		});

	});

	jQuery("body").on('click','.add_casting', function(){

		var loader = "<?php echo plugins_url('rb-agency-casting/view/loader.gif'); ?>";

		jQuery(this).html("<img src='"+loader+"'>");

		var $this = jQuery(this);

		var profile_id = jQuery(this).prevAll(".profile_id").eq(0).val();

		var job_id = jQuery(this).prevAll(".job_id").eq(0).val();

		jQuery.ajax({
				type: "POST",
				url: "<?php echo admin_url('admin-ajax.php') ?>",
				dataType: 'json',
				data: {
					action: "client_add_casting",
					'job_id' : job_id,
					'talent_id': profile_id
				},
				success: function (results) {
					console.log(results);
						if(results.data == ""){
							$this.html("Failed. Retry.");
						} else if(results.data == "inserted"){
							$this.html("Remove from Casting");
						} else if(results.data == "deleted"){
							$this.html("Add to CastingCart");
						}
  				},error: function(err){
  					console.log(err);
  				}
		});

	});

});
</script>

<?php
if (is_user_logged_in()) {

	echo "<div id=\"rbcontent\">";

	// casting agents and admin only
	if(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID) || current_user_can( 'edit_posts' )){

		if ( current_user_can( 'edit_posts' ) ) {
			echo "<p><h3>All Applicants to All Job Postings from Casting Agents</h3></p><br>";
		} else {
			echo "<p><h3>Applicants to your Job Postings</h3></p><br>";
		}

		//setup filtering sessions
		if(isset($_GET['filter'])){

			$_SESSION['filter'] = "";
			$_SESSION['job_title'] = "";
			$_SESSION['applicant'] = "";
			$_SESSION['percentage'] = "";
			$_SESSION['perpage'] = "";
			$_SESSION['rating'] = "";

			// job title
			if(isset($_GET['filter_jobtitle']) && $_GET['filter_jobtitle'] != ""){
				$_SESSION['job_title'] = $_GET['filter_jobtitle'];
				$_SESSION['filter'] = "jobs.Job_ID = " . $_SESSION['job_title'];
			}

			// applicant
			if(isset($_GET['filter_applicant']) && $_GET['filter_applicant'] != ""){
				$_SESSION['applicant'] = $_GET['filter_applicant'];
				$AND = ($_SESSION['filter'] != "") ? " AND " : ""; 
				$_SESSION['filter'] .= $AND . "applicants.Job_UserLinked = " . $_SESSION['applicant'];
			}

			// percentage
			if(isset($_GET['filter_jobpercentage']) && $_GET['filter_jobpercentage'] != ""){
				$_SESSION['percentage'] = $_GET['filter_jobpercentage'];
				$percent_arr = explode("-",$_GET['filter_jobpercentage']);
				$AND = ($_SESSION['filter'] != "") ? " AND " : ""; 
				$_SESSION['filter'] .= $AND . "Job_Criteria_Percentage >= " . $percent_arr[0] . " AND Job_Criteria_Percentage <= " . $percent_arr[1];
			}

			// perpage
			if(isset($_GET['filter_rating']) && $_GET['filter_rating'] != ""){
				$_SESSION['rating'] = $_GET['filter_rating'];
				$AND = ($_SESSION['filter'] != "") ? " AND " : ""; 
				if($_SESSION['rating'] == 'not_rated'){
					$_SESSION['filter'] .= $AND . "(Job_Client_Rating = '' OR Job_Client_Rating IS NULL)";
				} else {
					$_SESSION['filter'] .= $AND . "Job_Client_Rating = " . $_SESSION['rating'];
				}
			}

			// perpage
			if(isset($_GET['filter_perpage']) && $_GET['filter_perpage'] != ""){
				$_SESSION['job_perpage'] = $_GET['filter_perpage'];
			}

		}

		// set for display
		$applicant = (isset($_SESSION['applicant']) && $_SESSION['applicant'] != "") ? $_SESSION['applicant'] : "";
		$percentage = (isset($_SESSION['percentage']) && $_SESSION['percentage'] != "") ? $_SESSION['percentage'] : "";
		$jobtitle = (isset($_SESSION['job_title']) && $_SESSION['job_title'] != "") ? $_SESSION['job_title'] : "";
		$rating = (isset($_SESSION['rating']) && $_SESSION['rating'] != "") ? $_SESSION['rating'] : "";
		$perpage = (isset($_SESSION['job_perpage']) && $_SESSION['job_perpage'] != "") ? $_SESSION['job_perpage'] : 2;

		//pagination setup
		$filter = "";
		$start = get_query_var('target');
		$record_per_page = $perpage;
		$link = get_bloginfo('wpurl') . "/view-applicants/";
		$table_name = table_agency_casting_job_application;

		//for admin view
		if ( current_user_can( 'edit_posts' ) ) {
			if(isset($_SESSION['filter']) && $_SESSION['filter'] != ""){
				$filter = " WHERE " . $_SESSION['filter']; 
			}
			$where = " applicants LEFT JOIN " . table_agency_casting_job . 
					" jobs ON jobs.Job_ID = applicants.Job_ID" . $filter;
			$where_wo_filter = " applicants LEFT JOIN " . table_agency_casting_job . 
								" jobs ON jobs.Job_ID = applicants.Job_ID";
		} else {
			if(isset($_SESSION['filter']) && $_SESSION['filter'] != ""){
				$filter = " AND " . $_SESSION['filter']; 
			}
			$where = " applicants LEFT JOIN " . table_agency_casting_job . 
					" jobs ON jobs.Job_ID = applicants.Job_ID 
						WHERE jobs.Job_UserLinked = " . $current_user->ID . $filter;
			$where_wo_filter = " applicants LEFT JOIN " . table_agency_casting_job . 
								" jobs ON jobs.Job_ID = applicants.Job_ID 
									WHERE jobs.Job_UserLinked = " . $current_user->ID;

		}

		$selected_page = get_query_var('target');

		if($start != ""){
			$limit1 = ($start * $record_per_page) - $record_per_page;
		} else {
			$limit1 = 0;
		}

		// this query is going to used by email all visible
		$_SESSION['Current_User_Query'] = "SELECT applicants.Job_UserLinked FROM " 
											. table_agency_casting_job_application . $where
											. " GROUP By applicants.Job_ID ORDER By applicants.Job_Criteria_Passed DESC 
											LIMIT " . $limit1 . "," . $record_per_page ;

		// setup filter display
		echo "<form id=\"job-applicants-filter\" method='GET' action='".get_bloginfo('wpurl')."/view-applicants/'>";
		echo "<table style='margin-bottom:20px'>\n";
		echo "<tbody>";
		echo "    <tr class=\"thead\">\n";
		echo "        <td>Job Title<br>
						<select name='filter_jobtitle' style='width: 100%;'>
							<option value=''>-- Select Job Title --</option>";

		$job_applicant = array();

		if(current_user_can("edit_posts")){
			$load_job_filter = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." ORDER BY Job_Title");
			if(count($load_job_filter) > 0){
				foreach($load_job_filter as $j){
					echo "<option value='".$j->Job_ID."' ".selected($jobtitle,$j->Job_ID,false).">".$j->Job_Title."</option>";
				}
			}
		} else {
			//load jobs by current user
			$load_job_filter = $wpdb->get_results("SELECT *, applicants.Job_UserLinked as app_id  FROM " . table_agency_casting_job_application .
												$where_wo_filter
												. " GROUP By applicants.Job_ID ORDER By applicants.Job_Criteria_Passed DESC");

			// store applicants


			if(count($load_job_filter) > 0){
				foreach($load_job_filter as $j){
					if(!array_key_exists($j->app_id,$job_applicant)){
						$job_applicant[$j->app_id] = RBAgency_Casting::rb_casting_ismodel($j->app_id, "ProfileContactDisplay"); 
					}
					echo "<option value='".$j->Job_ID."' ".selected($jobtitle,$j->Job_ID,false).">".$j->Job_Title."</option>";
				}
			}
		}
		echo "			</select>
						</td>\n";
		echo "        <td>Applicant<br>
						<select name='filter_applicant'>
							<option value=''>-- Select Applicant --</option>";
		foreach($job_applicant as $key => $val){
			echo "<option value='".$key."' ".selected($key, $applicant,false).">".$val."</option>";
 
		}

		echo "			</select>
						</td>\n";
		echo "        <td>Criteria Matched<br>
						<select name='filter_jobpercentage'>
							<option value=''>-- Select Matched % --</option>
							<option value='75-100' ".selected($percentage,'75-100',false).">75% - 100% Matched</option>
							<option value='50-75' ".selected($percentage,'50-75',false).">50% - 75% Matched</option>
							<option value='25-50' ".selected($percentage,'25-50',false).">25% - 50% Matched</option>
							<option value='0-25' ".selected($percentage,'0-25',false).">0% - 25% Matched</option>
						</select>
						</td>\n";

		echo "        <td>Your Rating<br>
						<select name='filter_rating'>
							<option value=''> - </option>";
							echo "<option value='not_rated' ".selected('not_rated', $rating,false).">No Rating</option>";
							$page = 1;
							for($page = 1; $page <= 5; $page ++){
								echo "<option value='$page' ".selected($page, $rating,false).">$page Star</option>";
							}

		echo "			</select>
						</td>\n";

		echo "        <td>Records Per Page<br>
						<select name='filter_perpage'>
							<option value=''>- # of Rec -</option>";
							echo "<option value='2' ".selected(2, $perpage,false).">2</option>";

		$page = 0;
		for($page = 5; $page <= 50; $page += 5){
			echo "<option value='$page' ".selected($page, $perpage,false).">$page</option>";
		}

		echo "			</select>
						</td>\n";

		echo "        <td><input type='submit' name='filter' class='button-primary' value='filter'></td>\n";
		echo "    </tr>\n";
		echo "</tbody>";
		echo "</table>";
		echo "</form>";

		echo "<table cellspacing=\"0\" id=\"job-applicants\">\n";
		echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\">Select<br><input type='checkbox' id='sel_all'></th>\n";
		echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:100px;\">Job Title / ID</th>\n";
		echo "        <th class=\"column-JobApplicant\" id=\"JobApplicant\" scope=\"col\">Applicant</th>\n";
		echo "        <th class=\"column-JobCriteriaPassed\" id=\"CriteriaPassed\" scope=\"col\">Criteria Passed</th>\n";
		echo "        <th class=\"column-JobApplicationLetter\" id=\"JobApplicationLetter\" scope=\"col\">Application Letter</th>\n";
		echo "        <th class=\"column-JobAction\" id=\"JobAction\" scope=\"col\">Action</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";

		// load all job postings
		//for admin view
		$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_application .
											$where
											. " GROUP By applicants.Job_UserLinked ORDER By applicants.Job_Criteria_Passed DESC 
											LIMIT " . $limit1 . "," . $record_per_page );

		

		if(count($load_data) > 0){
			foreach($load_data as $load){
				
				$details = RBAgency_Casting::rb_casting_get_model_details($load->Job_UserProfileID);
				if($details->ProfileGallery != ""){
					$display = '<a href="'.get_bloginfo('wpurl').'/profile/'.$details->ProfileGallery.'">'.$details->ProfileContactDisplay.'</a>';
				} else {
					$display = $details->ProfileContactNameFirst;
				}
				echo "    <tr>\n";
				echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\"><input type='checkbox' name='select' class='select_app' value='".$load->Job_ID.":".$load->Job_UserLinked."'></td>\n";
				echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."<br><span class=\"id\">Job ID# : ".$load->Job_ID."</span></td>\n";
				echo "        <td class=\"column-JobDate\" scope=\"col\">";
				
				// applicant image
				
				$image = RBAgency_Casting::rb_get_model_image($load->Job_UserProfileID);
				if($image!= ""){
					echo "<div class=\"photo\">";
					echo "<span style = 'height: 120px; line-height:120px; width: 120px; display: table-cell; vertical-align: middle; text-align: center; soverflow: hidden;'>";
					echo "<a href=\"".get_bloginfo('wpurl')."/profile/".$details->ProfileGallery."\">";
					echo "<img src='".$image."'>";
					echo "</a>";
					echo "</span>";
					echo "</div>";
				} else {
					echo "<div class=\"no-image photo\">";
					echo "No Image";
					echo "</div>";
				}

				echo "<br><span style ='margin-left:5px; float:left; clear:both'>" . $display."</span></td>\n";

				if(RBAgency_Casting::rb_get_job_visibility($load->Job_ID) == 1){
					echo "        <td class=\"column-JobLocation\" scope=\"col\">100% Matched <br> <hr style='margin:5px'> Open to All<br>";
				} else {
					echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Criteria_Passed . RBAgency_Casting::rb_casting_get_percentage_passed($load->Job_ID, $load->Job_Criteria_Passed) . "<br>";
				}

				$load_detials = unserialize($load->Job_Criteria_Details);

				if(!empty($load_detials)){
					echo "<hr style='margin:5px'>";
					foreach($load_detials as $key => $val){
						$get_title = "SELECT ProfileCustomTitle FROM " . table_agency_customfields . " WHERE ProfileCustomID = " . $key;
						$get_row = $wpdb->get_row($get_title);
						if(count($get_row) > 0){
							echo "<span style='font-size:11px; font-weight:bold'>" . $get_row->ProfileCustomTitle . ": </span><br>";
							echo "<span style='font-size:11px'>" . $val . "</span><br>";
						} else {
							echo "<span style='font-size:11px'>" . $val . "</span><br>";
						}
					}
				}

				echo "</td>\n";

				echo "        <td class=\"column-JobApplicationLetter\" scope=\"col\">".$load->Job_Pitch ."</td>";

				echo "        <td class=\"column-JobAction\" scope=\"col\">";
				if(current_user_can("edit_posts")){
					echo "<a href='".admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".$load->Job_ID)."' style=\"font-size:12px;\">Edit Job Details</a><br>";
				} else {
					echo "<a href='".get_bloginfo('wpurl')."/casting-editjob/".$load->Job_ID."' style=\"font-size:12px;\">Edit Job Details</a><br>";
				}
				echo "        <input type='hidden' class='job_id' value='".$load->Job_ID."'>";
				echo "        <input type='hidden' class='profile_id' value='".$load->app_id."'>";
				if($rb_agency_option_allowsendemail == 1){
					echo "        <a href='".get_bloginfo('wpurl')."/email-applicant/".$load->Job_ID."/".$load->app_id."' style=\"font-size:12px;\">Send Email</a><br>";
				}
				if(RBAgency_Casting::rb_check_in_cart($load->app_id,$load->Job_ID)){
					echo "        <a class = 'add_casting' href='javascript:;' style=\"font-size:12px;\">Remove from Casting</a><br>";
				} else {
					echo "        <a class = 'add_casting' href='javascript:;' style=\"font-size:12px;\">Add to CastingCart</a><br>";
				}
				echo "<a href=\"".get_bloginfo("url")."/profile-casting/\" style=\"font-size:12px;\">View Casting Cart</a>";
			      echo "        <p  style='clear:both; margin-top:12px'>Rate Applicant</p>";

				$link_bg = plugins_url('rb-agency-casting/view/sprite.png');

				echo "        <div style='clear:both; margin-top:5px'>";
				echo "					<div class='star' style='float:left; width:15px; height:15px; background:url(\"$link_bg\") ".(isset($load->Job_Client_Rating) && $load->Job_Client_Rating >= 1 ? "0px 0px;" : '0px -15px;' ) ."'></div>";
				echo "					<div class='star' style='float:left; width:15px; height:15px; background:url(\"$link_bg\") ".(isset($load->Job_Client_Rating) && $load->Job_Client_Rating >= 2 ? "0px 0px;" : '0px -15px;' ) ."'></div>";
				echo "					<div class='star' style='float:left; width:15px; height:15px; background:url(\"$link_bg\") ".(isset($load->Job_Client_Rating) && $load->Job_Client_Rating >= 3 ? "0px 0px;" : '0px -15px;' ) ."'></div>";
				echo "					<div class='star' style='float:left; width:15px; height:15px; background:url(\"$link_bg\") ".(isset($load->Job_Client_Rating) && $load->Job_Client_Rating >= 4 ? "0px 0px;" : '0px -15px;' ) ."'></div>";
				echo "					<div class='star' style='float:left; width:15px; height:15px; background:url(\"$link_bg\") ".(isset($load->Job_Client_Rating) && $load->Job_Client_Rating == 5 ? "0px 0px;" : '0px -15px;' ) ."'></div>";
				echo "        </div>
								<input type='hidden' class='application_id' value='".$load->Job_Application_ID."'>
								<input type='hidden' class='clients_rating' value='".(isset($load->Job_Client_Rating) ?$load->Job_Client_Rating:"")."'>
								<input type='button' class='rate' value='Rate' style='clear:both; float:left'> <div class='loading' style='float:right; margin-right:15px; margin-top:5px; width:20px; height:20px'></div>
							</td>\n";
				echo "    </tr>\n";
			}
			echo "</table>";

		} else {

			echo "</table>";
			echo "<p style=\"width:100%;\">You have no Applicants.<br>if you don't have any job postings, create a new job posting <a href='".get_bloginfo('wpurl')."/casting-postjob'>Here.</a></p>\n";

		}

		// actual pagination
		RBAgency_Casting::rb_casting_paginate($link, $table_name, $where, $record_per_page, $selected_page);

		echo "<br><div id=\"result-action\">
				<select id='action_dropdown' style='float:left'>
					<option value=''>-- Select Action --</option>
					<option value='2'>Add/Remove to Casting Cart</option>";
				if($rb_agency_option_agencyemail == 1){
						echo "<option value='0'>Send Email to Selected</option>";
						echo "<option value='1'>Send Email to All Visible</option>";
				}

		echo "
				</select>
				<input type='button' id='action_submit' style='margin-left:12px; float:left' class='button-primary' value='Submit'>
				<div id='re_bottom' style='margin-left:12px; float:left; width:20px; height:20px'></div>
				</div>\n";

				echo "<div style=\"clear:both\">";

		if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'],'browse-jobs') > -1){
			echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/browse-jobs'>Go Back to Job Postings.</a></p>\n";
		}

		echo "<br><p><a href='".get_bloginfo('wpurl')."/profile-casting'>View Your Casting Cart</a><a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go Back to Casting Dashboard</a></p>\n";

	} else {
		echo "<p class=\"rbalert info\">Only Casting Agents are permitted on this page.<br>You need to be registered <a href='".get_bloginfo('wpurl')."/casting-register'>here.</a></p><br>";
	}

	echo "</div> <!-- #rbcontent -->";
} else {
	include ("include-login.php");
}

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 

?>