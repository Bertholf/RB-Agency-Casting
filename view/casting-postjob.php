<?php
include(dirname(dirname(__FILE__)) ."/app/casting.class.php");
/*wp_deregister_script('jquery'); 
wp_register_script('jquery_latest', 'http://code.jquery.com/jquery-1.11.0.min.js',false,1,true); 
wp_enqueue_script('jquery_latest');*/
wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js',false,1,true); 
	wp_register_script('jquery-timepicker',  plugins_url('../js/jquery-timepicker.js', __FILE__),false,1,true); 
	wp_enqueue_script('jquery-timepicker');
	wp_register_style( 'timepicker-style', plugins_url('../css/timepicker-addon.css', __FILE__) );
	wp_enqueue_style( 'timepicker-style' );

echo $rb_header = RBAgency_Common::rb_header(); 

//===============================
// if sumitted process here
//===============================



if(isset($_POST['save_job'])){

		// Error checking
		$error = "";
		$have_error = false;
		$date_confirm = 0;

		if ( empty($_POST['Job_Title'])) {
			$error .= __("Job Title is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['Job_Text'])) {
			$error .= __("Job Description is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['Job_Offering'])) {
			$error .= __("Job Offer is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if ( empty($_POST['Job_Date_Start'])) {
			$error .= __("Start Date is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
			$date_confirm++;
		} else {
			list($y,$m,$d)= explode('-',$_POST['Job_Date_Start']);
			if(checkdate($m,$d,$y)!==true){
				$error .= __("Start Date is invalid date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
				$date_confirm++;
			}
		}

		if ( empty($_POST['Job_Date_End'])) {
			$error .= __("End Date is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
			$date_confirm++;
		} else {
			list($y,$m,$d)= explode('-',$_POST['Job_Date_End']);
			if(checkdate($m,$d,$y)!==true){
				$error .= __("End Date is invalid date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
				$date_confirm++;
			}
		}

		if($date_confirm == 0){
			$date_start = strtotime($_POST['Job_Date_Start']);
			$date_end = strtotime($_POST['Job_Date_End']);
			if($date_start > $date_end){
				$error .= __("Start Date cannot be greater than the End Date.<br />", RBAGENCY_casting_TEXTDOMAIN);
				$have_error = true;
			}
		}

		if ( empty($_POST['Job_Location'])) {
			$error .= __("Job Location is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['Job_Region'])) {
			$error .= __("Job Region is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( empty($_POST['Job_Type'])) {
			$error .= __("Job type is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}
		if ( $_POST['Job_Visibility'] == "") {
			$error .= __("Visibility is required.<br />", RBAGENCY_casting_TEXTDOMAIN);
			$have_error = true;
		}

		if(!$have_error){

			$sql_Insert = "INSERT INTO " . table_agency_casting_job ;

			$into = array();
			$calues = array();
			$criteria = array();

			//get string values
			foreach($_POST as $key => $val){
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
						//Normal String
						if(strpos($key,"ProfileCustom2") === false){							
							$into[] = $key;
							$values[] = "'". trim($val) . "'";
						}
					}
				}
			}
			$job_talents_hash = RBAgency_Common::generate_random_string(10,"abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ");
			$sql_Insert .=  " ( " . implode(",",$into) . ", Job_Criteria, Job_Talents_Hash, Job_Date_Created) VALUES ( " . implode(",",$values) . ",'".implode("|",$criteria)."' ,'".$job_talents_hash."',Now())";

			$wpdb->query($sql_Insert);
			$Job_ID = $wpdb->insert_id;

					$insert_to_casting_custom = array();
					
					$profilecustom_ids = array();
					$profilecustom_types = array();
					foreach($_POST as $k=>$v){
						$parsek = explode("_",$k);
						if($parsek[0] == 'ProfileCustom2'){
							$profilecustom_ids[] = $parsek[1];
							$profilecustom_types[] = $parsek[2];
						}
					}
					
					foreach($profilecustom_ids as $k=>$v){
						foreach($_POST["ProfileCustom2_".$v."_".$profilecustom_types[$k]] as $key=>$value){
							if($profilecustom_types[$k] == 9 || $profilecustom_types[$k] == 5){
								$data = implode("|",$_POST["ProfileCustom2_".$v."_".$profilecustom_types[$k]]);
							}else{
								$data = $_POST["ProfileCustom2_".$v."_".$profilecustom_types[$k]][$key];
							}
							if(empty($data) || $data == '--Select--'){
								$data = NULL;
							}

							$insert_to_casting_custom[] = "INSERT INTO ".$wpdb->prefix."agency_casting_job_customfields(Job_ID,Customfield_ID,Customfield_value,Customfield_type) values('".esc_attr($Job_ID)."','".esc_attr($v)."','".esc_attr($data)."','".esc_attr($profilecustom_types[$k])."')";							
						}
												
					}
					$temp_arr = array();
					foreach($insert_to_casting_custom as $k=>$v){
						if(!in_array($v,$temp_arr)){
							$wpdb->query($v);
							$temp_arr[$k] = $v; 
						}						
					}

			echo "  	<div id=\"rbcontent\">\n";
			echo '			<div class="entry-content">';
			echo "			<div class=\"cb\"></div>\n";
			echo '			<p class="rbalert success">';
			echo '				<strong>You have successfully added your new Job Posting!</strong><br/> <a href=\''.get_bloginfo('wpurl').'/casting-postjob\'">Add new Job Posting?</a>';
			echo '				<a href=\''.get_bloginfo('wpurl').'/casting-dashboard\'">Go Back to Casting Dashboard</a>';
			echo '			</p>';
			echo "			<div class=\"cb\"></div>\n";
			echo "			</div><!-- .entry-content -->\n"; // .entry-content
			echo "			<input type=\"hidden\" name=\"favorite\" value=\"1\"/>";
			echo "  	</div><!-- #rbcontent -->\n"; // #rbcontent

		} else {

			load_job_display($error);

		}

} else {

	load_job_display();

}
echo $rb_footer = RBAgency_Common::rb_footer(); 

function load_job_display($error = NULL){

	global $wpdb;
	global $current_user;
	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
		echo '<script type="text/javascript">
				jQuery(document).ready(function(){

					jQuery( ".datepicker" ).datepicker();
					jQuery(".datepicker").each(function() {
						jQuery(this).datepicker("setDate", jQuery(this).val());
						console.log(jQuery(this).val());
					});
					jQuery( ".datepicker" ).datepicker("option", "dateFormat", "yy-mm-dd");
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
									},
									error: function (err){
										console.log(err);
									}
							});
						} else {
							jQuery("#criteria").html("");
						}
					});
					jQuery(".timepicker").timepicker({
									hourGrid: 4,
									minuteGrid: 10,
									timeFormat: \'g:ia\' 
					});
				});
				function ValidateForm(){
					jQuery(\'span.error_msg\').html(\'\');
					var success = true;
					jQuery("input,textarea,select").each(function() {
						jQuery(this).next().hide();
						if(jQuery(this).val()=="") {
							jQuery(this).next().html("Please fill out this field.");
							jQuery(this).next().show();
							// success = false; // TODO: Restore
						}
					});
					return success;
				}
			</script>';
			echo '<style type="text/css">
					span.error_msg{display:none; }
					span.error_msg{
						position: relative;
						z-index: 1;
						color: #CF4040;
						margin-left: 0px;
						margin-top: 2px;
						display: block;
						border: 1px solid rgb(215, 215, 215);
						width: 100%;
						padding: 2px 30px 1px;
						background: #FFFAFA;
						margin-bottom: 30px;
					}
			</style>';

	if (is_user_logged_in()) {
	
	
		$is_active = rb_check_casting_status();
		if($is_active == false){
			echo '		
				<div id="rbcontent" role="main">
				
					<header class="entry-header">
					<h1 class="entry-title">You are not permitted to access this page.</h1>
					</header>
					<div class="entry-content">
					<p class="rbalert error">
						<strong></strong>
					</p>
				</div>';
			echo $rb_footer = RBAgency_Common::rb_footer(); 
			exit;
		}
	
	
	
	//if(RBAgency_Casting::rb_is_user_casting()){

		echo "<div id=\"rbcontent\" ";?><?php echo post_class(); ?><?php echo ">\n";
		echo '			<header class="entry-header">';
		echo '				<h1 class="entry-title">New Job Posting</h1>';
		echo '			</header>';

		if(isset($error) && $error != ""){
			echo '			<p>'.$error.'</p>';
		}

		echo '			<div class="entry-content">';

		//===============================
		//	table form
		//===============================
		echo " <form method='post' action='' onsubmit=\"return ValidateForm();\" class=\"rbform\">
					<div class='rbfield rbtext rbsingle'>
						<h3>Job Description</h3>
						<label>Title:</label>
						<div><input type='text' name='Job_Title' value='".(isset($_POST['Job_Title'])?$_POST['Job_Title']:"")."'><span style=\"display:none;\" class=\"error_msg tooltip\"></span></div>
					</div>
					<div class='rbfield rbtextarea rbsingle'>
						<label>Description:</label>
						<div><textarea name='Job_Text'>".(isset($_POST['Job_Text'])?$_POST['Job_Text']:"")."</textarea><span style=\"display:none;\" class=\"error_msg tooltip\"></span></div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<label>Offer:</label>
						<div><input type='text' name='Job_Offering' value='".(isset($_POST['Job_Offering'])?$_POST['Job_Offering']:"")."'><span style=\"display:none;\" class=\"error_msg tooltip\"></span></div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<h3>Job Duration</h3>
						<label>Date Start:</label>
						<div>
							<input type='text' name='Job_Date_Start' class='datepicker' value='".(isset($_POST['Job_Date_Start'])?$_POST['Job_Date_Start']:"")."'>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>

					<div class='rbfield rbtext rbsingle'>
						<label>Date End:</label>
						<div>
							<input type='text' name='Job_Date_End' class='datepicker' value='".(isset($_POST['Job_Date_End'])?$_POST['Job_Date_End']:"")."'>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<h3>Job Location</h3>
						<label>Location:</label>
						<div><input type='text' name='Job_Location' value='".(isset($_POST['Job_Location'])?$_POST['Job_Location']:"")."'><span style=\"display:none;\" class=\"error_msg tooltip\"></span></div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<label>Region:</label>
						<div><input type='text' name='Job_Region' value='".(isset($_POST['Job_Region'])?$_POST['Job_Region']:"")."'><span style=\"display:none;\" class=\"error_msg tooltip\"></span></div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<h3>Job Audition</h3>
						<label>Date Start:</label>
						<div>
							<input type='text' name='Job_Audition_Date_Start' class='datepicker' value='".(isset($_POST['Job_Audition_Date_Start'])?$_POST['Job_Audition_Date_Start']:"")."'>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<label>Date End:</label>
						<div>
							<input type='text' name='Job_Audition_Date_End' class='datepicker' value='".(isset($_POST['Job_Audition_Date_End'])?$_POST['Job_Audition_Date_End']:"")."'>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<label>Time:</label>
						<div>
							<input type='text' name='Job_Audition_Time' class='timepicker' value='".(isset($_POST['Job_Audition_Time'])?$_POST['Job_Audition_Time']:"")."'>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbtextarea rbsingle'>
					<label>Venue:</label>
						<div>
							<textarea name='Job_Audition_Venue'>".(isset($_POST['Job_Audition_Venue'])?$_POST['Job_Audition_Venue']:"")."</textarea>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbselect rbsingle'>
						<h3>Job Criteria</h3>
						<label>Type:</label>
						<div>
							<select id='Job_Type' name='Job_Type'>
								<option value=''>-- Select Type --</option>";
								$get_job_type = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_type); 
								if(count($get_job_type)){
									foreach($get_job_type as $jtype){
										echo "<option value='".$jtype->Job_Type_ID."' ".selected($jtype->Job_Type_ID,isset($_POST['Job_Type'])?$_POST['Job_Type']:"",false).">".$jtype->Job_Type_Title."</option>";
									}
								}
						echo "	</select>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>
					<div class='rbfield rbtext rbsingle'>
						<label>Visibility:</label>
						<div>
							<select id='Job_Visibility' name='Job_Visibility'>
								<option value=''>-- Select Type --</option>
								<option value='0' ".selected(isset($_POST['Job_Visibility'])?$_POST['Job_Visibility']:"","0",false).">Invite Only</option>
								<option value='1' ".selected(isset($_POST['Job_Visibility'])?$_POST['Job_Visibility']:"","1",false).">Open to All</option>
								<option value='2' ".selected(isset($_POST['Job_Visibility'])?$_POST['Job_Visibility']:"","2",false).">Matching Criteria</option>
							</select>
							<span style=\"display:none;\" class=\"error_msg tooltip\"></span>
						</div>
					</div>";
					
					rb_get_customfields_castingpostjobs();
					echo "<div id='criteria'></div>
					

					<div class='rbfield rbsubmit'>
						<label></label>

						<input type='submit' name='save_job' value='Submit Job'>
					</div>
					<input type=\"hidden\" name=\"Job_UserLinked\" value=\"".$current_user->ID."\"/>
				</form>";
		echo "			<div class=\"cb\"></div>\n";
		echo "			<br><p><a href='".get_bloginfo('wpurl')."/casting-dashboard'>Go Back to Casting Dashboard.</a></p>\n";
		echo "			</div><!-- .entry-content -->\n"; // .entry-content
		echo "		</div><!-- #rbcontent -->\n"; // #content

	} else {

		echo "		<div id=\"rbcontent\" role=\"main\" class=\"transparent\">\n";
		echo '			<p class="rbalert error">';
		echo '				<strong>You are not permitted to access this page.</strong>';
		echo '			</p>';
		if(!is_user_logged_in()){
			require_once("include-login.php");
		}
		echo "		</div><!-- #rbcontent -->\n"; // #rbcontent

	}
}

?>
