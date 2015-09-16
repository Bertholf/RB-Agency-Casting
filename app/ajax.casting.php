<?php


add_action('wp_ajax_casting_deletejob','casting_deletejob');
//add_action('wp_ajax_nopriv_casting_deletejob','casting_deletejob'); // 
function casting_deletejob(){
	global $wpdb;
	
	$jobID = esc_attr($_POST['jobID']);
	$delete = $wpdb->query("DELETE FROM " . table_agency_casting_job . " WHERE Job_ID='$jobID'");
	echo 'deleted';

	//print_r($_POST);
    exit;
}
