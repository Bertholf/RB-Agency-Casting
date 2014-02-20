<?php

	global $wpdb;

	echo '<div class="wrap" style="min-width: 1020px;">
 			<div id="rb-overview-icon" class="icon32"></div>
 			<h2>Job Postings</h2>
		  </div>'; 	

	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if ($items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";

	echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
	echo " <thead>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileID&dir=" . $sortDirection) . "\">ID</a></th>\n";
	echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=JobTitle&dir=" . $sortDirection) . "\">Job Title</a></th>\n";
	echo "        <th class=\"column-JobText\" id=\"JobText\" scope=\"col\">Job Description</th>\n";
	echo "        <th class=\"column-JobDate\" id=\"JobDate\" scope=\"col\">Duration</th>\n";
	echo "        <th class=\"column-JobLocation\" id=\"ProfilesProfileDate\" scope=\"col\">Location</th>\n";
	echo "        <th class=\"column-JobRegion\" id=\"ProfileLocationCity\" scope=\"col\">Region</th>\n";
	echo "        <th class=\"column-JobOffer\" id=\"ProfileLocationState\" scope=\"col\">Offering</th>\n";
	echo "        <th class=\"column-JobVisibility\" id=\"ProfileDetails\" scope=\"col\">Visibility</th>\n";
	echo "        <th class=\"column-JobCriteria\" id=\"ProfileDetails\" scope=\"col\">Criteria</th>\n";
	echo "        <th class=\"column-JobType\" id=\"ProfileStatHits\" scope=\"col\">Type</th>\n";
	echo "    </tr>\n";
	echo " </thead>\n";
	
	// load data
	$load_data = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job);
	if(count($load_data) > 0){
		foreach($load_data as $load){
			echo "    <tr>\n";
			echo "        <td class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></td>\n";
			echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$load->Job_ID."</td>\n";
			echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$load->Job_Title."</td>\n";
			echo "        <td class=\"column-JobText\" scope=\"col\">".$load->Job_Text."</td>\n";
			echo "        <td class=\"column-JobDate\" scope=\"col\">".$load->Job_Date_Start." - ".$load->Job_Date_Start."</td>\n";
			echo "        <td class=\"column-JobLocation\" scope=\"col\">".$load->Job_Location."</td>\n";
			echo "        <td class=\"column-JobRegion\" scope=\"col\">".$load->Job_Region."</td>\n";
			echo "        <td class=\"column-JobOffer\" scope=\"col\">".$load->Job_Offering."</td>\n";
			echo "        <td class=\"column-JobVisibility\" scope=\"col\">".$load->Job_Visibility."</td>\n";
			echo "        <td class=\"column-JobCriteria\" scope=\"col\">".$load->Job_Criteria."</td>\n";
			echo "        <td class=\"column-JobType\" scope=\"col\">".$load->Job_Type."</td>\n";
			echo "    </tr>\n";
		}
		
	}

	echo " <tfoot>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">ID</th>\n";
	echo "        <th class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">Job Title</th>\n";
	echo "        <th class=\"column-JobText\" scope=\"col\">Job Description</th>\n";
	echo "        <th class=\"column-JobDate\" scope=\"col\">Duration</th>\n";
	echo "        <th class=\"column-JobLocation\" scope=\"col\">Location</th>\n";
	echo "        <th class=\"column-JobRegion\" scope=\"col\">Region</th>\n";
	echo "        <th class=\"column-JobOffer\" scope=\"col\">Offering</th>\n";
	echo "        <th class=\"column-JobVisibility\" scope=\"col\">Visibility</th>\n";
	echo "        <th class=\"column-JobCriteria\" scope=\"col\">Criteria</th>\n";
	echo "        <th class=\"column-JobType\" scope=\"col\">Type</th>\n";
	echo "    </tr>\n";
	echo " </tfoot>\n";
	echo " <tbody>\n";
	echo "</table>\n";

	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if ($items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";
	echo "</form>\n";

	echo "</div>\n";

?>