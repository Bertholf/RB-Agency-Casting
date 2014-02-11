<?php

session_start();
header("Cache-control: private"); //IE 6 Fix
include(rb_agency_BASEREL ."app/profile.class.php");

wp_deregister_script('jquery'); 
wp_register_script('jquery', 'http://code.jquery.com/jquery-1.11.0.min.js'); 
wp_enqueue_script('jquery');
wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js');

echo $rb_header = RBAgency_Common::rb_header(); 

//===============================
// if sumitted process here	
//===============================

if(isset($_GET['save_job'])){
	
	foreach($_GET as $key => $val){
		if(is_array($val)){
			$n = "";
			foreach($val as $x){
				$n .= $n . "-" . $x; 
			}
			$val = trim($n,"-");
		}
		//will add data here
	}

	echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
	echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
	echo '			<div class="entry-content">';	
	echo "			<div class=\"cb\"></div>\n";
	echo '			<header class="entry-header">';
	echo '				<h3 class="entry-title">You have successfully added your new Job Posting!</h3>';
	echo '			</header>';
	echo "			<div class=\"cb\"></div>\n";
	echo "			</div><!-- .entry-content -->\n"; // .entry-content
	echo "			<input type=\"hidden\" name=\"favorite\" value=\"1\"/>";
	echo "  	</div><!-- #content -->\n"; // #content
	echo "	</div><!-- #primary -->\n"; // #primary
	
} else {
	
	echo '<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">';
	echo '<script type="text/javascript">
				jQuery(document).ready(function(){
					jQuery( ".datepicker" ).datepicker();
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
									}
							});
						} else {
							jQuery("#criteria").html("");
						}
					});
				});
		  </script>';
	
	if(rb_is_user_casting()){
	
		echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
		echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
		echo '			<header class="entry-header">';
		echo '				<h1 class="entry-title">New Job Posting</h1>';
		echo '			</header>';
		echo '			<div class="entry-content">';
		
		//===============================
		//	table form
		//===============================
		echo " <form method='get' actipn='".$_SERVER['PHP_SELF']."'>
					<table>
						<tr>
							<td><h3>Job Description</h3></td>
							<td></td>
						</tr>
						<tr>
							<td>Title:</td>
							<td><input type='text' name='Job_Title'></td>
						</tr>
						<tr>
							<td>Description:</td>
							<td><input type='text' name='Job_Text'></td>
						</tr>	
						<tr>
							<td><h3>Job Duration</h3></td><td></td>
						</tr>
						<tr>
							<td>Date Start:</td>
							<td>
									<input type='text' name='Job_Date_Start' class='datepicker'>
							</td>
						</tr>	
						<tr>
							<td>Date End:</td>
							<td>
									<input type='text' name='Job_Date_End' class='datepicker'>
							</td>
						</tr>												
						<tr>
							<td><h3>Job Location</h3></td><td></td>
						</tr>												
						<tr>
							<td>Location:</td>
							<td><input type='text' name='Job_Location'></td>
						</tr>												
						<tr>
							<td>Region:</td>
							<td><input type='text' name='Job_Region'></td>
						</tr>	
						<tr>
							<td><h3>Job Criteria</h3></td><td></td>
						</tr>				
						<tr>
							<td>Type:</td>
							<td>
								<select id='Job_Type' name='Job_Type'>
									<option value=''>-- Select Type --</option>
									<option value='0'>Invite Only</option>
									<option value='1'>Open to All</option>
									<option value='2'>Matching Criteria</option>
								</select>
							</td>
						</tr>																			
						<tr>
							<td>Visibility:</td>
							<td>
								<select id='Job_Visibility' name='Job_Visibility'>
									<option value=''>-- Select Type --</option>
									<option value='0'>Invite Only</option>
									<option value='1'>Open to All</option>
									<option value='2'>Matching Criteria</option>
								</select>
							</td>
						</tr>												
						<tr>
							<td></td>
							<td id='criteria'></td>
						</tr>	
						<tr>
							<td></td>
							<td><input type='submit' name='save_job' value='Submit Job'></td>
						</tr>		
					</table>
				</form>";
		
		echo "			<div class=\"cb\"></div>\n";
		echo "			</div><!-- .entry-content -->\n"; // .entry-content
		echo "			<input type=\"hidden\" name=\"favorite\" value=\"1\"/>";
		echo "  	</div><!-- #content -->\n"; // #content
		echo "	</div><!-- #primary -->\n"; // #primary
	
	} else {
	
		echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
		echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
		echo '			<header class="entry-header">';
		echo '				<h1 class="entry-title">You are not permitted to access this page.</h1>';
		echo '			</header>';
		if(!is_user_logged_in()){
			require_once("include-login.php");
		}
		echo "  	</div><!-- #content -->\n"; // #content
		echo "	</div><!-- #primary -->\n"; // #primary
	
	}
}
echo $rb_footer = RBAgency_Common::rb_footer(); 

?>
