<?php
global $wpdb;

header("Cache-control: private"); //IE 6 Fix
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");

/*wp_deregister_script('jquery'); 
wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js',false,1,true); 
wp_enqueue_script('jquery_latest');*/
wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js',false,1,true); 
	wp_register_script('jquery-timepicker',  plugins_url('../js/jquery-timepicker.js', __FILE__),false,1,true); 
	wp_enqueue_script('jquery-timepicker');
	wp_register_style( 'timepicker-style', plugins_url('../css/timepicker-addon.css', __FILE__) );
	wp_enqueue_style( 'timepicker-style' );

// set job id
$JobID = get_query_var('target');

//fetch data from DB
$get_data = "SELECT * FROM " . table_agency_casting_job . " WHERE Job_ID = " . $JobID;
$get_results = $wpdb->get_row($get_data,ARRAY_A);
$data = array();
$count = $wpdb->num_rows;
if($count > 0){
	$d = $get_results;
	foreach($d as $key => $val){
		$data[$key] = $val;
	}

}

//store criteria from db
$Job_criteria_old = $data['Job_Criteria'];

//populate from post if there are new values
foreach($_GET as $key => $val) {
	if(array_key_exists($key, $data)){
		$data[$key] = $_GET[$key];
	}
}

echo $rb_header = RBAgency_Common::rb_header(); 

//===============================
// if sumitted process here
//===============================

if(isset($_GET['save_job'])){

		// Error checking
		$error = "";
		$have_error = false;
		$date_confirm = 0;

		if ( empty($_GET['Job_Title'])) {
			$error .= __("Job Title is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_GET['Job_Text'])) {
			$error .= __("Job Description is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_GET['Job_Offering'])) {
			$error .= __("Job Offer is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_GET['Job_Date_Start'])) {
			$error .= __("Start Date is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
			$date_confirm++;
		} else {
			list($y,$m,$d)= explode('-',$_GET['Job_Date_Start']);
			if(checkdate($m,$d,$y)!==true){
				$error .= __("Start Date is invalid date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
				$date_confirm++;
			}
		}

		if ( empty($_GET['Job_Date_End'])) {
			$error .= __("End Date is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
			$date_confirm++;
		} else {
			list($y,$m,$d)= explode('-',$_GET['Job_Date_End']);
			if(checkdate($m,$d,$y)!==true){
				$error .= __("End Date is invalid date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
				$date_confirm++;
			}
		}

		if($date_confirm == 0){
			$date_start = strtotime($_GET['Job_Date_Start']);
			$date_end = strtotime($_GET['Job_Date_End']);
			if($date_start > $date_end){
				$error .= __("Start Date cannot be greate than the End Date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
			}
		}

		if ( empty($_GET['Job_Location'])) {
			$error .= __("Job Location is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_GET['Job_Region'])) {
			$error .= __("Job Region is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_GET['Job_Type'])) {
			$error .= __("Job type is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_GET['Job_Visibility'])) {
			$error .= __("Visibility is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if(!$have_error){

			// update data to db
			//
			$into = array();
			$calues = array();
			$criteria = array();

			//get string values
			foreach($_GET as $key => $val){
				if($key != "save_job"){
					
					if (strpos($key, "ProfileCustomID") > -1){
						if($val != "" && !empty($val)){
							if(is_array($val)){
								$n = "";
								foreach($val as $x){
									$n .= "-" . $x; 
								}
								$n = trim($n,"-");
							} else {
								$n = trim($val);
							}

							if($n != ""){
								$criteria[] = substr($key,15) . "/" . $n ;
							}
						}
					} else {
						$parseKey = explode("_",$key);
						if($parseKey[0] != 'UpdateJob'){
							//Normal String
							$into[] = $key;
							$values[] = "'". trim($val) . "'";
						}
						
					}
				}
			}

			//construct update statement
			$sql_update = "UPDATE " . table_agency_casting_job . " SET ";
			$ctr = 0;
			$sql_update_arr = array();
			foreach($into as $field){
				$sql_update_arr[] = $field . " = " . $values[$ctr] ; 
				$ctr++;
			}

			$sql_update .=  implode(",",$sql_update_arr) . ", Job_Criteria = '".implode("|",$criteria)."' WHERE Job_ID = " . $JobID;

			$wpdb->query($sql_update);

			/**UPDATE CUSTOM FIELDS**/

							foreach($_GET as $k=>$v){

								$parseCustom = explode("_",$k);
								
								if($parseCustom[0] == 'UpdateJob'){

									$profilecustom_ids[] = $parseCustom[1];
									$profilecustom_types[] = $parseCustom[2];
									$query_get = "SELECT * FROM ".$wpdb->prefix."agency_casting_job_customfields WHERE Customfield_ID = ". $parseCustom[1];
									$wpdb->get_results($query_get,ARRAY_A);
									if($wpdb->num_rows > 0){
										//Update
										foreach($profilecustom_ids as $k=>$v){
											foreach($_GET["UpdateJob_".$v."_".$profilecustom_types[$k]] as $key=>$value){
												if($profilecustom_types[$k] == 9 || $profilecustom_types[$k] == 5){
													$data = implode("|",$_GET["UpdateJob_".$v."_".$profilecustom_types[$k]]);
												}else{
													$data = $_GET["UpdateJob_".$v."_".$profilecustom_types[$k]][$key];
												}
												if(empty($data) || $data == '--Select--'){
													$data = NULL;
												}
												
												$update_to_casting_custom[] = "UPDATE ".$wpdb->prefix."agency_casting_job_customfields
																				SET Customfield_value = '".esc_attr($data)."' WHERE Job_ID = ".esc_attr($JobID)." AND Customfield_ID = ".esc_attr($v)."
																				";
											}
																	
										}

										$temp_arr = array();
										foreach($update_to_casting_custom as $k=>$v){
											if(!in_array($v,$temp_arr)){

												$wpdb->query($v);
												$temp_arr[$k] = $v; 
											}						
										}
									}else{
										//Add
										foreach($profilecustom_ids as $k=>$v){
											echo $v;
											foreach($_GET["UpdateJob_".$v."_".$profilecustom_types[$k]] as $key=>$value){
												if($profilecustom_types[$k] == 9 || $profilecustom_types[$k] == 5){
													$data = implode("|",$_GET["UpdateJob_".$v."_".$profilecustom_types[$k]]);
												}else{
													$data = $_GET["UpdateJob_".$v."_".$profilecustom_types[$k]][$key];
												}
												if(empty($data) || $data == '--Select--'){
													$data = NULL;
												}

												$insert_to_casting_custom[] = "INSERT INTO ".$wpdb->prefix."agency_casting_job_customfields(Job_ID,Customfield_ID,Customfield_value,Customfield_type) values('".esc_attr($JobID)."','".esc_attr($v)."','".esc_attr($data)."','".esc_attr($profilecustom_types[$k])."')";							
											}
																	
										}
										$temp_arr = array();
										foreach($insert_to_casting_custom as $k=>$v){
											if(!in_array($v,$temp_arr)){
												$wpdb->query($v);
												$temp_arr[$k] = $v; 
											}						
										}
									}
								}
							}
							

							/**END UPDATE CUSTOM FIELDS**/

			//check data integrity for applicants for new criterias only
			if(trim(implode("|",$criteria)) != $Job_criteria_old){
				RBAgency_Casting::rb_update_applicant_data(implode("|",$criteria), $JobID);
			}

			echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
			echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
			echo '			<div class="entry-content">';
			echo "			<div class=\"cb\"></div>\n";
			echo '			<header class="entry-header">';
			echo '				<p>'.__('You have successfully updated your new Job Posting!',RBAGENCY_casting_TEXTDOMAIN).' <a href="'.get_bloginfo('wpurl').'/browse-jobs">'.__('View Your Job Postings?',RBAGENCY_casting_TEXTDOMAIN).'</a></p>';
			echo '				<p><a href="'.get_bloginfo('wpurl').'/casting-dashboard">'.__("Go Back to Casting Dashboard.",RBAGENCY_casting_TEXTDOMAIN).'</a></p>';
			echo '			</header>';
			echo "			<div class=\"cb\"></div>\n";
			echo "			</div><!-- .entry-content -->\n"; // .entry-content
			echo "			<input type=\"hidden\" name=\"favorite\" value=\"1\"/>";
			echo "  	</div><!-- #content -->\n"; // #content
			echo "	</div><!-- #primary -->\n"; // #primary

		} else {

			load_job_display($error, $data);

		}

} else {

	load_job_display("",$data);

}
echo $rb_footer = RBAgency_Common::rb_footer(); 

function load_job_display($error = NULL, $data){

	global $wpdb;
	global $current_user;

	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';


	if (is_user_logged_in()) {
	//if(RBAgency_Casting::rb_is_user_casting()){

		echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
		echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
		echo '			<header class="entry-header">';
		echo '				<h1 class="entry-title">'.__("Edit Job Posting",RBAGENCY_casting_TEXTDOMAIN).'</h1>';
		echo '			</header>';

		if(isset($error) && $error != ""){
			echo '			<p>'.$error.'</p>';
		}

		echo '			<div class="entry-content">';

		//===============================
		//	table form
		//===============================
		echo " <form method='get' actipn='".$_SERVER['PHP_SELF']."' class=\"rbform\">
					<table>
						<tr>
							<td><h3>".__("Job Description",RBAGENCY_casting_TEXTDOMAIN)."</h3></td>
							<td></td>
						</tr>
						<tr>
							<td>".__("Title:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td><input type='text' name='Job_Title' value='".$data['Job_Title']."'></td>
						</tr>
						<tr>
							<td>".__("Description:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td><input type='text' name='Job_Text' value='".$data['Job_Text']."'></td>
						</tr>
						<tr>
							<td>".__("offer:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td><input type='text' name='Job_Offering' value='".$data['Job_Offering']."'></td>
						</tr>
						<tr>
							<td><h3>".__("Job Duration",RBAGENCY_casting_TEXTDOMAIN)."</h3></td><td></td>
						</tr>
						<tr>
						<td>".__("Date Start:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Date_Start' id='Job_Date_Start'  value='".$data['Job_Date_Start']."' class='datepicker'>
							</td>
						</tr>
						<tr>
							<td>".__("Date End:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Date_End' id='Job_Date_End'  value='".$data['Job_Date_End']."' class='datepicker'>
							</td>
						</tr>
						<tr>
							<td>".__("Time Start:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Time_Start' id='Job_Time_Start' class='timepicker' value='".$data['Job_Time_Start']."'>
							</td>
						</tr>
						<tr>
							<td>".__("Time End:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Time_End' id='Job_Time_End' class='timepicker' value='".$data['Job_Time_End']."'>
							</td>
						</tr>
						
						
						<tr>
							<td><h3>".__("Job Location",RBAGENCY_casting_TEXTDOMAIN)."</h3></td><td></td>
						</tr>
						<tr>
							<td>".__("Location:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td><input type='text' name='Job_Location' value='".$data['Job_Location']."'></td>
						</tr>
						<tr>
							<td>".__("Region:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td><input type='text' name='Job_Region' value='".$data['Job_Region']."'></td>
						</tr>
						<tr>
							<td><h3>".__("Job Audition",RBAGENCY_casting_TEXTDOMAIN)."</h3></td><td></td>
						</tr>
						<tr>
							<td>".__("Date Start:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Audition_Date_Start' id='Job_Audition_Date_Start' class='datepicker' value='".$data['Job_Audition_Date_Start']."'>
							</td>
						</tr>
						<tr>
							<td>".__("Date End:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Audition_Date_End' id='Job_Audition_Date_End' class='datepicker' value='".$data['Job_Audition_Date_End']."'>
							</td>
						</tr>
						<tr>
							<td>".__("Time Start:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Audition_Time' id='Job_Audition_Time' class='timepicker' value='".$data['Job_Audition_Time']."'>
							</td>
						</tr>
						<tr>
							<td>".__("Time End:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<input type='text' name='Job_Audition_Time_End' id='Job_Audition_Time_End' class='timepicker' value='".$data['Job_Audition_Time_End']."'>
							</td>
						</tr>
						<tr>
						<td>".__("Venue",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<textarea name='Job_Audition_Venue'>".$data['Job_Audition_Venue']."</textarea>
							</td>
						</tr>
						<tr>
							<td><h3>".__("Job Criteria",RBAGENCY_casting_TEXTDOMAIN)."</h3></td><td></td>
						</tr>
						<tr>
							<td>".__("Type:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<select id='Job_Type' name='Job_Type'>
									<option value=''>".__("-- Select Type --",RBAGENCY_casting_TEXTDOMAIN)."</option>";

									$get_job_type = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_type);
									if(count($get_job_type)){
										foreach($get_job_type as $jtype){
											echo "<option value='".$jtype->Job_Type_ID."' ".selected($jtype->Job_Type_ID,$data['Job_Type'],false).">".$jtype->Job_Type_Title."</option>";
										}
									}

						echo "	</select>
							</td>
						</tr>
						<tr>
							<td>".__("Visibility:",RBAGENCY_casting_TEXTDOMAIN)."</td>
							<td>
								<select id='Job_Visibility' name='Job_Visibility'>
									<option value=''>".__("-- Select Type --",RBAGENCY_casting_TEXTDOMAIN)."</option>
									<option value='0' ".selected($data['Job_Visibility'],"0",false).">".__("Invite Only",RBAGENCY_casting_TEXTDOMAIN)."</option>
									<option value='1' ".selected($data['Job_Visibility'],"1",false).">".__("Open to All",RBAGENCY_casting_TEXTDOMAIN)."</option>
									<option value='2' ".selected($data['Job_Visibility'],"2",false).">".__("Matching Criteria",RBAGENCY_casting_TEXTDOMAIN)."</option>
								</select>
							</td>
						</tr>";

						rb_agency_update_castingjob();
		echo "<tr>
							<td></td>
							<td id='criteria'></td>
						</tr>
						<tr>
							<td colspan=\"2\"><input type='submit' name='save_job' value='".__("Submit Job",RBAGENCY_casting_TEXTDOMAIN)."'></td>
						</tr>
					</table>
					<input type=\"hidden\" name=\"Job_UserLinked\" value=\"".$current_user->ID."\"/>
				</form>";
		echo "			<div class=\"cb\"></div>\n";
		echo "			<br><p><a href='".get_bloginfo('wpurl')."/casting-dashboard'>".__("Go Back to Casting Dashboard.",RBAGENCY_casting_TEXTDOMAIN)."</a></p>";
		echo "			</div><!-- .entry-content -->\n"; // .entry-content
		echo "  	</div><!-- #content -->\n"; // #content
		echo "	</div><!-- #primary -->\n"; // #primary


		echo '<script type="text/javascript">
				jQuery(document).ready(function(){

				jQuery( ".datepicker" ).datepicker();
				jQuery( ".datepicker" ).datepicker("option", "dateFormat", "yy-mm-dd");
					var date_start="'.$data['Job_Date_Start'].'";
					var date_end="'.$data['Job_Date_End'].'";
					var date_audition_start = "'.$data["Job_Audition_Date_Start"].'";
					var date_audition_end = "'.$data["Job_Audition_Date_End"].'";

					jQuery("#Job_Date_Start").val(date_start);
					jQuery("#Job_Date_End").val(date_end);
					jQuery("#Job_Audition_Date_Start").val(date_audition_start);
					jQuery("#Job_Audition_Date_End").val(date_audition_end);

					jQuery("#Job_Visibility").change(function(){
						if(jQuery(this).val() == 2){
							jQuery("#criteria").html("Loading Criteria List");
							jQuery.ajax({
									type: "POST",
									url: "'. admin_url('admin-ajax.php') .'",
									data: {
										action: "load_criteria_fields"
									},
									success: function (results) {
										jQuery("#criteria").html(results);
									}
							});
						} else {
							jQuery("#criteria").html("");
						}
					});';

					if($data['Job_Visibility'] == 2){
							echo 'jQuery.ajax({
									type: "POST",
									url: "'. admin_url('admin-ajax.php') .'",
									data: {
										action: "load_criteria_fields",
										value: "'.$data['Job_Criteria'].'"
									},
									success: function (results) {
										jQuery("#criteria").html(results);
									}
								});';
					}
					echo '	jQuery(".timepicker").timepicker({
									hourGrid: 4,
									minuteGrid: 10,
									timeFormat: \'g:ia\' ,
									noneOption: [{
										label: "--",
										value: "--"
									}]
								});';

	echo '});
			</script>';

	} else {

		echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
		echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
		echo '			<header class="entry-header">';
		echo '				<h1 class="entry-title">'.__("You are not permitted to access this page.",RBAGENCY_casting_TEXTDOMAIN).'</h1>';
		echo '			</header>';
		if(!is_user_logged_in()){
			require_once("include-login.php");
		}
		echo "  	</div><!-- #content -->\n"; // #content
		echo "	</div><!-- #primary -->\n"; // #primary

	}
}

?>
