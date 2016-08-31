<?php

	// include casting class
	include(dirname(dirname(__FILE__)) ."/app/casting.class.php");


	global $current_user, $wp_roles;

	get_currentuserinfo();

	$job_id = get_query_var('value');

	// add scripts
	wp_deregister_script('jquery'); 
	wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
	wp_enqueue_script('jquery_latest');

	echo $rb_header = RBAgency_Common::rb_header();

	echo "<div id=\"rbcontent\" class=\"job-".$job_id."\">";
   
	//if (is_user_logged_in()) {

		echo "<script type='text/javascript'>
				jQuery(document).ready(function(){
					jQuery('#apply_job').click(function(){";
						if(RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID')){
							if(strpos($_SERVER['HTTP_REFERER'], "view-applicants") > -1){
								echo "window.location = '".get_bloginfo('wpurl')."/view-applicants'; ";
							} elseif(strpos($_SERVER['HTTP_REFERER'], "browse-jobs") > -1){
								echo "window.location = '".get_bloginfo('wpurl')."/browse-jobs'; ";
							} else {
								echo "window.location = '".get_bloginfo('wpurl')."/browse-jobs'; ";
							}
						} else {
							echo "window.location = '".get_bloginfo('wpurl')."/job-application/".$job_id."'; ";
						}
					echo "});
				});
			</script>";

		echo "<h2>".__("Job Details",RBAGENCY_TEXTDOMAIN)."</h2><br>";

		global $wpdb;

		//fetch data from database
		$data_r = $wpdb->get_results("SELECT * FROM ". table_agency_casting_job . " WHERE Job_ID = " . $job_id);
		if(count($data_r) > 0){
			foreach($data_r as $r){

				echo "<h3>".__($r->Job_Title, RBAGENCY_TEXTDOMAIN)."</h3>";
				echo "<p>".__($r->Job_Text, RBAGENCY_TEXTDOMAIN)."</p>";
				// echo "<p>".$r->Job_Criteria."</p>";

				$job_criteria = explode("|", $r->Job_Criteria);
				$criteria_age = $job_criteria[0];
				$criteria_age_val = explode("/", $criteria_age);

				echo "<div id=\"job-details\">";
					echo "<div id=\"details\">";
						echo "<table>
							<tr id=\"shoot-date-start\">
								<td><b>".__("Shoot Date Start:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".date('F j, Y', strtotime($r->Job_Date_Start))."</td>
							</tr>
							<tr id=\"shoot-date-end\">
								<td><b>".__("Shoot Date End:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".date('F j, Y', strtotime($r->Job_Date_End))."</td>
							</tr>";
							
							if(!empty($r->Job_Time_Start)){
							echo "<tr id=\"job-time-start\">
									<td><b>".__("Job Time Start:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Time_Start."</td>
								</tr>";
							}
							if(!empty($r->Job_Time_End)){
							echo "<tr id=\"job-time-end\">
									<td><b>".__("Job Time End:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Time_End."</td>
								</tr>";
							}
							
						
						if(!empty($r->Job_Location)){
						echo "
							<tr>
								<td><b>".__("Location:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".$r->Job_Location."</td>
							</tr>";
						}
							
						if(!empty($r->Job_Region)){
						echo "
							<tr>
								<td><b>".__("Region:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".$r->Job_Region."</td>
							</tr>";
						}
						
						if(!empty($r->Job_Offering)){
						echo "
							<tr>
								<td><b>".__("Payment :", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".$r->Job_Offering."</td>
							</tr>";
						}
						
						if(!empty($r->Job_Type)){
						echo "
							<tr>
								<td><b>".__("Job Type:", RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".RBAgency_Casting::rb_get_job_type_name($r->Job_Type)."</td>
							</tr>
						";
						}
							
						if(!empty($r->CastingContactCompany)){
						echo "
							<tr id=\"agency-producer\">
								<td><b>".__("Agency/Producer",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
								<td class='jobdesc'>".$r->CastingContactCompany."</td>
							</tr>";
						}
						
						echo "</table>";												
					echo "</div>";
					echo "<div id=\"how-to-apply\">";
					echo "<table>";
							
							if(!empty($criteria_age)){
							echo "<tr>
									<td><b>".__("Criteria",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>";
									foreach ($job_criteria as $criteria) {

										$criteria_item = explode("/", $criteria);
										$criteria_item_label = $criteria_item[0];
										$criteria_item_value = $criteria_item[1];

										// $job_types = $wpdb->get_row("SELECT Job_Type_Title FROM ".table_agency_casting_job_type. " WHERE Job_Type_ID = ".$job_type_id);

										if($criteria_item_value != "null" && $criteria_item_value != "undefined"){
											if($criteria_item_label == "gender"){
												$criteria_gender = $wpdb->get_row($wpdb->prepare("SELECT GenderID, GenderTitle FROM ".table_agency_data_gender." WHERE GenderID='".$criteria_item_value."' "),ARRAY_A,0);
												$count = $wpdb->num_rows;
												if($count > 0){
													$criteria_item_value = $criteria_gender["GenderTitle"];
												}
											}
											echo __(ucfirst($criteria_item_label), RBAGENCY_TEXTDOMAIN).": ".__($criteria_item_value, RBAGENCY_TEXTDOMAIN)."<br>";
										}										
									}
							echo "	</td>
								</tr>";
							}
							if(!empty($r->Job_Audition_Date_Start)){
							echo "<tr>
									<td><b>".__("Audition Date Start:",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Audition_Date_Start."</td>
								</tr>";
							}
							if(!empty($r->Job_Audition_Date_End)){
							echo "
								<tr>
									<td><b>".__("Audition Date End:",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Audition_Date_End."</td>
								</tr>";
							}
							if(!empty($r->Job_Audition_Time)){
							echo "<tr>
									<td><b>".__("Audition Time Start:",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Audition_Time."</td>
								</tr>";
							}
							if(!empty($r->Job_Audition_Time_End)){
							echo "<tr>
									<td><b>".__("Audition Time End:",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Audition_Time_End."</td>
								</tr>";
							}
							
							if(!empty($r->Job_Audition_Venue)){
							echo "<tr>
									<td><b>".__("Audition Venue:",RBAGENCY_casting_TEXTDOMAIN)."</b></td>
									<td class='jobdesc'>".$r->Job_Audition_Venue."</td>
								</tr>";
							}
							//Custom fields
							rb_agency_detail_castingjob();
							//End custom fields							
					echo "</table>";
						if( (RBAgency_Casting::rb_casting_ismodel($current_user->ID,'ProfileID') && !current_user_can( 'edit_posts' )) || !is_user_logged_in() ){							
							if(is_user_logged_in()){
								echo "<input id='apply_job_btn' type='button' class='button-primary' value='Apply to this Job' onClick='window.location.href=\"".get_bloginfo("wpurl")."/job-application/".$r->Job_ID."\"'>";
								echo "&nbsp;&nbsp;<input id='browse_jobs' type='button' class='button-primary' onClick='window.location.href= \"".get_bloginfo('wpurl')."/browse-jobs\"' style='margin-left:12px;' value='".__("Browse More Jobs",RBAGENCY_casting_TEXTDOMAIN)."'>";
							}else{
								echo "<input id='apply_job_btn' type='button' class='button-primary' value='Apply to this Job' onClick='window.location.href=\"".get_bloginfo("wpurl")."/profile-login?h=/job-application/".get_query_var('value')."\"'>";
								echo "&nbsp;&nbsp;&nbsp;&nbsp;<input id='go_back' type='button' class='button-primary' onClick='window.history.back();' style='margin-left:12px;' value='Go Back'>";
							}
						} elseif(RBAgency_Casting::rb_casting_is_castingagent($current_user->ID,'ProfileID') || current_user_can( 'edit_posts' )){
							if(isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], "view-applicants") > -1){
								echo "<input id='apply_job' type='button' class='button-primary' value='Back to Applicants'>";
							} else {
								echo "&nbsp;&nbsp;<input id='browse_jobs' type='button' class='button-primary' onClick='window.location.href= \"".get_bloginfo('wpurl')."/browse-jobs\"' style='margin-left:12px;' value='".__("Browse More Jobs",RBAGENCY_casting_TEXTDOMAIN)."'>";
							}
						}
						if(current_user_can("edit_posts")){
							echo "<td class='jobdesc'>";
							echo "&nbsp;&nbsp;<input id=\"view_applicants\" type='button' class='button-primary'  onClick='window.location.href=\"".get_bloginfo('wpurl')."/view-applicants/?filter_jobtitle=".$r->Job_ID."&filter_applicant=&filter_jobpercentage=&filter_rating=&filter_perpage=10&filter=filter\"' value=\"".__("View Applicants",RBAGENCY_casting_TEXTDOMAIN)."\"/>";
							echo "</td>";
						}					
					echo "</div><!-- #how-to-apply -->";
				echo "</div>";
				
			}

			// for models
			if(RBAgency_Casting::rb_casting_ismodel($current_user->ID) && !current_user_can( 'edit_posts' )){
				echo "<br><p style=\"width:100%;\"><a href='".get_bloginfo('wpurl')."/profile-member'>".__("Go Back to Profile Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>\n";
			}

		} else {
			echo "<p>".__("Job doesn't exist",RBAGENCY_casting_TEXTDOMAIN)."</p>";
		}

	//} else {
		//include ("include-login.php");
	//}

	echo "</div><!-- #rbcontent -->";

	//get_sidebar(); 
	echo $rb_footer = RBAgency_Common::rb_footer(); 
?>
