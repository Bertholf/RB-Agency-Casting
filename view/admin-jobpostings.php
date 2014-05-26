<?php

	global $wpdb;

	echo '<div class="wrap" style="min-width: 1020px;">
 			<div id="rb-overview-icon" class="icon32"></div>
 			<h2>Manage Job Postings</h2>
		  </div>'; 	

	//menu
	if( isset( $_GET['page'] ) ) {  
		$active_page = isset( $_GET['page'] ) ? $_GET['page'] : 'display_options';
	}  
	echo '<h2 class="nav-tab-wrapper">
			<a href="?page=rb_agency_casting_jobpostings" class="nav-tab ' . (($active_page == "rb_agency_casting_jobpostings" && !isset($_GET['action']) ) ? "nav-tab-active" : "") . '" > Manage Jobs</a>  
			<a href="?page=rb_agency_casting_jobpostings&action=manage_types" class="nav-tab ' . ((isset($_GET['action']) && $_GET['action'] == 'manage_types' ) ? "nav-tab-active" : "") . '" > Manage Job Types</a>  
		 </h2>';


/*---------------------------------------------------------------------
 * DISPLAY SELECTIONS
 *---------------------------------------------------------------------
 */

if ($active_page == "rb_agency_casting_jobpostings" && !isset($_GET['action'])) {
	
	table_posting();

} elseif(isset($_GET['action']) && $_GET['action'] == 'manage_types'){
	
	job_type_settings();
	
}


/*---------------------------------------------------------------------
 * MANAGE JOBS
 *---------------------------------------------------------------------*/
function table_posting(){
	
	global $wpdb;
	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if (isset($items) && $items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";
	
	echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
	echo " <thead>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=ProfileID&dir=" . (isset($sortDirection)?$sortDirection:"")) . "\">ID</a></th>\n";
	echo "        <th class=\"column-JobTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=JobTitle&dir=" . (isset($sortDirection)?$sortDirection:"") ) . "\">Job Title</a></th>\n";
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
	echo "</table>\n";

	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if (isset($items) && $items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";

	echo "</form>\n";
	
}


/*---------------------------------------------------------------------
 * MANAGE JOB TYPES
 *---------------------------------------------------------------------*/
function job_type_settings(){
	
	global $wpdb;
	
	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if (isset($items) && $items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";
	
	/*---------------------------------------------------------------------
	 * Process: page actions ADD DISPLAY
	 *---------------------------------------------------------------------*/	
	if(isset($_GET['proc']) && $_GET['proc'] == 'addnew_jobtype' ){

		echo "<h2>Add New Job Type</h2>";
		echo "<form method=\"GET\" action=\"" . admin_url("admin.php") . "\">\n";
		echo "<table>
				<tr><td>Job Type Title:</td><td><input type='text' name='Job_Type_Title'></td></tr>
				<tr><td>Job Type Description:</td><td><input type='text' name='Job_Type_Text'></td></tr>
			  </table>"; 	
		echo "<input type='hidden' name='page' value='".$_GET['page']."'>";
		echo "<input type='hidden' name='action' value='manage_types'>";
		echo "<input type='hidden' name='rec_process' value='add'>";
		echo "<input type='hidden' name='proc' value='save_new_jobtype'>";
		echo "<p><input type='submit' class='button-primary' value='Save Job Type'></p>";
		echo "<form>";		

	}

	/*---------------------------------------------------------------------
	 * Process: page actions EDIT DISPLAY
	 *---------------------------------------------------------------------*/	
	elseif(isset($_GET['proc']) && $_GET['proc'] == 'edit_jobtype' ){
		
		// get details
		$type = $_GET['type_id'];
		$get_details = "SELECT * FROM " . table_agency_casting_job_type . " WHERE Job_Type_ID = " . $type;
		$results = $wpdb->get_row($get_details) or die(mysql_error());
		
		echo "<h2>Edit Job Type</h2>";
		echo "<form method=\"GET\" action=\"" . admin_url("admin.php") . "\">\n";
		echo "<table>
				<tr><td>Job Type Title:</td><td><input type='text' name='Job_Type_Title' value='".$results->Job_Type_Title."'></td></tr>
				<tr><td>Job Type Description:</td><td><input type='text' name='Job_Type_Text' value='".$results->Job_Type_Text."'></td></tr>
			  </table>"; 	
		echo "<input type='hidden' name='page' value='".$_GET['page']."'>";
		echo "<input type='hidden' name='action' value='manage_types'>";
		echo "<input type='hidden' name='rec_process' value='edit'>";
		echo "<input type='hidden' name='typeid' value='".$type."'>";
		echo "<input type='hidden' name='proc' value='save_new_jobtype'>";
		echo "<p><input type='submit' class='button-primary' value='Save Job Type'></p>";
		echo "<form>";		

	}	

	/*---------------------------------------------------------------------
	 * Process: page actions EDIT DISPLAY
	 *---------------------------------------------------------------------*/	
	elseif(isset($_GET['proc']) && $_GET['proc'] == 'delete_jobtype' ){
		
		// get details
		$type = $_GET['type_id'];
		$delete_details = "DELETE FROM " . table_agency_casting_job_type . " WHERE Job_Type_ID = " . $type;
		$results = $wpdb->query($delete_details) or die(mysql_error());
		
		$msg = __("Successfully Deleted Record.<br />", rb_agency_casting_TEXTDOMAIN);
		echo $msg;		

		echo "<p><a class='button-primary' href='".admin_url("admin.php?page=" . $_GET['page'] . "&action=manage_types&proc=addnew_jobtype")."'>Add New Job Type</a></p>";
		
	}	
		
	/*---------------------------------------------------------------------
	 * Process: page actions ADD DISPLAY
	 *---------------------------------------------------------------------*/	
	elseif(isset($_GET['proc']) && $_GET['proc'] == 'save_new_jobtype' ){
		
		// get id
		$type = $_GET['typeid'];
		//check errors
		$error = '';
		$have_error = false;
		if(empty($_GET['Job_Type_Title'])){
			$error .= __("Job Type Title is required.<br />", rb_agency_casting_TEXTDOMAIN);
			$have_error = true;
		} 
		if(empty($_GET['Job_Type_Text'])){
			$error .= __("Job Type Description is required.<br />", rb_agency_casting_TEXTDOMAIN);
			$have_error = true;
		} 
		
		if(!$have_error){
			if($_GET['rec_process'] == 'add'){
				$sql_insert = "INSERT INTO " . table_agency_casting_job_type . " ( Job_Type_Title, Job_Type_text ) VALUES ( '".$_GET['Job_Type_Title']."','".$_GET['Job_Type_Text']."' )";
				$wpdb->query($sql_insert) or die(mysql_error());
				$msg = __("Successfully Added Record.<br />", rb_agency_casting_TEXTDOMAIN);
				echo $msg;
			} elseif($_GET['rec_process'] == 'edit'){
				$sql_update = "UPDATE " . table_agency_casting_job_type . " SET Job_Type_Title = '".$_GET['Job_Type_Title']."', Job_Type_text = '".$_GET['Job_Type_Text']."' WHERE Job_Type_ID = " . $type ;
				mysql_query($sql_update) or die(mysql_error());
				$msg = __("Successfully Updated Record.<br />", rb_agency_casting_TEXTDOMAIN);
				echo $msg;
			}
		}

		echo "<p><a class='button-primary' href='".admin_url("admin.php?page=" . $_GET['page'] . "&action=manage_types&proc=addnew_jobtype")."'>Add New Job Type</a></p>";

	} else {

		echo "<p><a class='button-primary' href='".admin_url("admin.php?page=" . $_GET['page'] . "&action=manage_types&proc=addnew_jobtype")."'>Add New Job Type</a></p>";
	
	}



	/*---------------------------------------------------------------------
	 * Process: page actions ADD DISPLAY
	 *---------------------------------------------------------------------*/		
	echo "<form method=\"post\" action=\"" . admin_url("admin.php?page=" . $_GET['page']) . "\">\n";
	echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
	echo " <thead>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-JobID\" id=\"JobID\" scope=\"col\" style=\"width:50px;\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page'] . "&sort=JobTypeID") . "\">ID</a></th>\n";
	echo "        <th class=\"column-JobTypeTitle\" id=\"JobTitle\" scope=\"col\" style=\"width:150px;\">Job Type Title</th>\n";
	echo "        <th class=\"column-JobTypeText\" id=\"JobText\" scope=\"col\">Job Type Description</th>\n";
	echo "    </tr>\n";
	echo " </thead>\n";
	
	// load job type settings	
	$load_jobtypes = $wpdb->get_results("SELECT * FROM " . table_agency_casting_job_type) or die(mysql_error());
	if(count($load_jobtypes) > 0 ){
		foreach($load_jobtypes as $jtypes){
			echo "    <tr>\n";
			echo "        <td class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></td>\n";
			echo "        <td class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">".$jtypes->Job_Type_ID."</td>\n";
			echo "        <td class=\"column-JobTitle\" scope=\"col\" style=\"width:150px;\">".$jtypes->Job_Type_Title;
			echo "          <div class=\"row-actions\">\n";
			echo "            <span class=\"edit\"><a href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&action=manage_types&proc=edit_jobtype&type_id=" . $jtypes->Job_Type_ID . "\" title=\"" . __("Edit this Record", rb_agency_TEXTDOMAIN) . "\">" . __("Edit", rb_agency_TEXTDOMAIN) . "</a> | </span>\n";
			echo "            <span class=\"delete\"><a class=\"submitdelete\" href=\"" . admin_url("admin.php?page=" . $_GET['page']) . "&action=manage_types&proc=delete_jobtype&type_id=" . $jtypes->Job_Type_ID . "\"  onclick=\"if ( confirm('" . __("You are about to delete the Job Type with ID ", rb_agency_TEXTDOMAIN) . " " . $jtypes->Job_Type_ID . " \'" . __("Cancel", rb_agency_TEXTDOMAIN) . "\' " . __("to stop", rb_agency_TEXTDOMAIN) . ", \'" . __("OK", rb_agency_TEXTDOMAIN) . "\' " . __("to delete", rb_agency_TEXTDOMAIN) . ".') ) { return true;}return false;\" title=\"" . __("Delete this Record", rb_agency_TEXTDOMAIN) . "\">" . __("Delete", rb_agency_TEXTDOMAIN) . "</a> </span>\n";
			echo "          </div>\n";			
			echo "		  </td>\n";
			echo "        <td class=\"column-JobText\" scope=\"col\">".$jtypes->Job_Type_Text."</td>\n";
			echo "    </tr>\n";		
		}
	} 
	
	echo " <tfoot>\n";
	echo "    <tr class=\"thead\">\n";
	echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
	echo "        <th class=\"column-JobID\" scope=\"col\" style=\"width:50px;\">ID</th>\n";
	echo "        <th class=\"column-JobTypeTitle\" scope=\"col\" style=\"width:150px;\">Job Title</th>\n";
	echo "        <th class=\"column-JobTypeText\" scope=\"col\">Job Description</th>\n";
	echo "    </tr>\n";
	echo " </tfoot>\n";
	echo " <tbody>\n";
	echo "</table>\n";

	// Show Pagination
	echo "<div class=\"tablenav\">\n";
	echo "  <div class='tablenav-pages'>\n";
	if (isset($items) && $items > 0) {
		echo $p->show();  // Echo out the list of paging. 
	}
	echo "  </div>\n";
	echo "</div>\n";
	echo "</form>\n";
	
}

?>