<?php
echo $rb_header = RBAgency_Common::rb_header();

$job_type_id = get_query_var('target');

global $wpdb;
$job_type = $wpdb->get_row("SELECT Job_Type_Title FROM ".table_agency_casting_job_type. " WHERE Job_Type_ID = ".$job_type_id);

$output = "";
$output .= "<div id=\"rbcontent\">";

$output .= "<div id=\"job-types\">";
$output .= "<ul>";
	$job_types = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_type);
	if(count($job_types) > 0 ){
		foreach($job_types as $jobtype){
			$output .= "    <li>\n";
			$output .= "        <a href=\"".site_url()."/job-type/".$jobtype->Job_Type_ID."\">".$jobtype->Job_Type_Title."</a>";
			$output .= "    </li>\n";
		}
	}
$output .= "</ul>";
$output .= "</div>";

$output .= "<br /><br /><h2>Lists of Jobs under ".$job_type->Job_Type_Title."</h2><br />";

$results = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job." WHERE Job_Type = $job_type_id AND (Job_Visibility = 1 OR Job_Visibility = 2)");

$output .= "<div id=\"job-auditions\">";
foreach($results as $job){
$output .= "	<div class=\"job-audition\">";
$output .= "		<div class=\"ja-thumbnail\">";
$output .= "			<a href=\"".site_url()."/job-detail/".$job->Job_ID."\" title=\"View this Job\"><img src=\"".RBAGENCY_PLUGIN_URL."/assets/img/rbplugin-logo-o25.png\"></a>";
$output .= "		</div><!-- .ja-thumbnail -->";
$output .= "		<div class=\"ja-content\">";
$output .= "			<h3><a href=\"".site_url()."/job-detail/".$job->Job_ID."\" title=\"View this Job\">".$job->Job_Title."</a></h3>";						
$output .= "			<p>".$job->Job_Text."</p>";						
$output .= "			<p class=\"ja-date\">Apply Before 28/02/2016</p><!-- .ja-content -->";
$output .= "		</div><!-- .ja-content -->";
$output .= "		<div class=\"ja-footer\">";
$output .= "			<a href=\"".site_url()."/job-detail/".$job->Job_ID."\" title=\"View this Job\">View this Job</a>";			
$output .= "		</div><!-- .ja-footer -->";
$output .= "	</div><!-- .job-audition -->";
}
$output .= "</div><!-- #rbcontent -->";

echo $output;

echo $rb_footer = RBAgency_Common::rb_footer(); 

?>