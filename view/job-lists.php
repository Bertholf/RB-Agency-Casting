<?php
echo $rb_header = RBAgency_Common::rb_header();

$job_type_id = get_query_var('target');



global $wpdb;


$job_types = $wpdb->get_row("SELECT Job_Type_Title FROM ".table_agency_casting_job_type. " WHERE Job_Type_ID = ".$job_type_id);

echo "<h2>Lists of Jobs under ".$job_types->Job_Type_Title."</h2>";
$results = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." WHERE Job_Type = $job_type_id AND (Job_Visibility = 1 OR Job_Visibility = 2)");

$output = "";
$output .= "<table>";
$output .= "<tr><td>Job Title</td><td>Job Description</td>";
foreach($results as $job){
	$output .= "<tr>";
	$output .= "<td><a href=\"".site_url()."/job-detail/".$job->Job_ID."\">".$job->Job_Title."</a></td>";
	$output .= "<td>".$job->Job_Text."</td>";
	$output .= "</tr>";
}
$output .= "</table>";

echo $output;

echo $rb_footer = RBAgency_Common::rb_footer(); 

?>