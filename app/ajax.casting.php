<?php


add_action('wp_ajax_casting_deletejob','casting_deletejob');
//add_action('wp_ajax_nopriv_casting_deletejob','casting_deletejob'); // 
function casting_deletejob($_datapost =''){
	global $wpdb;
	
	$_data = $_POST;
	//optional function 
	//so we can use it as ajax or function
	if(is_array($_datapost)){
		$_data = $_datapost;
	}	
	$jobID = esc_attr($_data['jobID']);
	$delete = $wpdb->query("DELETE FROM " . table_agency_casting_job . " WHERE Job_ID='$jobID'");
	return $delete;
	//echo 'deleted';
	//print_r($_POST);
   
}
