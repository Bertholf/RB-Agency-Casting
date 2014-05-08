<?php 
global $wpdb;
define("LabelPlural", "Pending Clients");
define("LabelSingular", "Pending Client");
$rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_unittype  			= $rb_agency_options_arr['rb_agency_option_unittype'];
	$rb_agency_option_showsocial 			= $rb_agency_options_arr['rb_agency_option_showsocial'];
	$rb_agency_option_agencyimagemaxheight 	= $rb_agency_options_arr['rb_agency_option_agencyimagemaxheight'];
		if (empty($rb_agency_option_agencyimagemaxheight) || $rb_agency_option_agencyimagemaxheight < 500) { $rb_agency_option_agencyimagemaxheight = 800; }
	$rb_agency_option_profilenaming 		= (int)$rb_agency_options_arr['rb_agency_option_profilenaming'];
	$rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];
// *************************************************************************************************** //
// Handle Post Actions
if (isset($_POST['action'])) {
	// Get Post State
	$action = $_POST['action'];
	switch($action) {
	// *************************************************************************************************** //
	// Delete bulk
	case 'deleteRecord':
		foreach($_POST as $CastingID) {
			// Verify Record
			$queryDelete = "SELECT * FROM ". table_agency_casting ." WHERE CastingID =  ". $CastingID;
			$resultsDelete = mysql_query($queryDelete);
			while ($dataDelete = mysql_fetch_array($resultsDelete)) {
				$CastingGallery = $dataDelete['CastingGallery'];
		
				// Remove Profile
				$delete = "DELETE FROM " . table_agency_casting . " WHERE CastingID = ". $CastingID;
				$results = $wpdb->query($delete);
					
				if (isset($CastingGallery)) {
					// Remove Folder
					$dir = rb_agency_UPLOADPATH . $CastingGallery ."/";
					$mydir = opendir($dir);
					while(false !== ($file = readdir($mydir))) {
						if($file != "." && $file != "..") {
							unlink($dir.$file) or DIE("couldn't delete $dir$file<br />");
						}
					}
					// remove dir
					if(is_dir($dir)) {
						rmdir($dir) or DIE("couldn't delete $dir$file<br />");
					}
					closedir($mydir);
					
				} else {
					echo __("No valid record found.", rb_agency_casting_TEXTDOMAIN);
				}
					
			echo ('<div id="message" class="updated"><p>'. __("Client deleted successfully!", rb_agency_casting_TEXTDOMAIN) .'</p></div>');
			} // is there record?
			
		}
		rb_display_list();
		exit;
	break;
	
	}
}
else {
// *************************************************************************************************** //
// Show List
	rb_display_list();
}

// *************************************************************************************************** //
// Manage Record
function rb_display_list() {
  global $wpdb;
  $rb_agency_options_arr = get_option('rb_agency_options');
	$rb_agency_option_locationtimezone 		= (int)$rb_agency_options_arr['rb_agency_option_locationtimezone'];
  echo "<div class=\"wrap\">\n";
  echo "  <div id=\"rb-overview-icon\" class=\"icon32\"></div>\n";
  echo "  <h2>". __("List", rb_agency_casting_TEXTDOMAIN) ." ". LabelPlural ."</h2>\n";
	
  echo "  <h3 class=\"title\">". __("All Records", rb_agency_casting_TEXTDOMAIN) ."</h3>\n";
		
		// Sort By
        $sort = "";
        if (isset($_GET['sort']) && !empty($_GET['sort'])){
            $sort = $_GET['sort'];
        }
        else {
            $sort = "client.CastingContactNameFirst";
        }
		
		// Sort Order
        $dir = "";
        if (isset($_GET['dir']) && !empty($_GET['dir'])){
            $dir = $_GET['dir'];
            if ($dir == "desc" || !isset($dir) || empty($dir)){
               $sortDirection = "asc";
               } else {
               $sortDirection = "desc";
            } 
		} else {
			   $sortDirection = "desc";
			   $dir = "asc";
		}
  	
		// Filter
		$filter = "WHERE client.CastingIsActive = 3 ";
        if ((isset($_GET['CastingContactNameFirst']) && !empty($_GET['CastingContactNameFirst'])) || isset($_GET['CastingContactNameLast']) && !empty($_GET['CastingContactNameLast'])){
        	if (isset($_GET['CastingContactNameFirst']) && !empty($_GET['CastingContactNameFirst'])){
			$selectedNameFirst = $_GET['CastingContactNameFirst'];
			$query .= "&CastingContactNameFirst=". $selectedNameFirst ."";
			$filter .= " AND client.CastingContactNameFirst LIKE '". $selectedNameFirst ."%'";
	        }
        	if (isset($_GET['CastingContactNameLast']) && !empty($_GET['CastingContactNameLast'])){
			$selectedNameLast = $_GET['CastingContactNameLast'];
			$query .= "&CastingContactNameLast=". $selectedNameLast ."";
			$filter .= " AND client.CastingContactNameLast LIKE '". $selectedNameLast ."%'";
	        }
		}
		if (isset($_GET['CastingLocationCity']) && !empty($_GET['CastingLocationCity'])){
			$selectedCity = $_GET['CastingLocationCity'];
			$query .= "&CastingLocationCity=". $selectedCity ."";
			$filter .= " AND client.CastingLocationCity='". $selectedCity ."'";
		}
		if (isset($_GET['CastingContactEmail']) && !empty($_GET['CastingContactEmail'])){
			$selectedContactEmail = $_GET['CastingContactEmail'];
			$query .= "&CastingContactEmail=". $selectedContactEmail ."";
			$filter .= " AND client.CastingContactEmail LIKE '". $selectedContactEmail ."%'";
	     
		}
		
		// Bulk Action
		
		if(isset($_POST['BulkAction_ProfileApproval']) || isset($_POST['BulkAction_ProfileApproval2'])){
			
			//**** BULK DELETE	
			if($_POST['BulkAction_ProfileApproval']=="Delete" || $_POST['BulkAction_ProfileApproval2']=="Delete"){
			 
			   if(isset($_POST['castingID'])){
					foreach($_POST['castingID'] as $key){
					 
									$CastingID = $key;
									// Verify Record
									$queryDelete = "SELECT * FROM ". table_agency_casting ." WHERE CastingID =  ". $CastingID;
									$resultsDelete = mysql_query($queryDelete);
									while ($dataDelete = mysql_fetch_array($resultsDelete)) {
										$CastingGallery = $dataDelete['CastingGallery'];
								
										// Remove Profile
										$delete = "DELETE FROM " . table_agency_casting . " WHERE CastingID = ". $CastingID;
										$results = $wpdb->query($delete);
											
										if (isset($CastingGallery)) {
											// Remove Folder
											$dir = rb_agency_UPLOADPATH . $CastingGallery ."/";
											$mydir = opendir($dir);
											while(false !== ($file = readdir($mydir))) {
												if($file != "." && $file != "..") {
													$isUnlinked = @unlink($dir.$file);
													if($isUnlinked){
														
													}else{
													   echo "Couldn't delete $dir$file<br />";	
													}
												}
											}
											// remove dir
											if(is_dir($dir)) {
												$isRemoved = @rmdir($dir);
												if($isRemoved){
														
												}else{
													   echo "Couldn't delete $dir$file<br />";	
												}
											}
											closedir($mydir);
											
										} else {
											echo __("No valid record found.", rb_agency_casting_TEXTDOMAIN);
										}
											
									echo ('<div id="message" class="updated"><p>'. __("Client deleted successfully!", rb_agency_casting_TEXTDOMAIN) .'</p></div>');
									} // is there record?
									
						
					}
					
			   }
				
			}
			// Bulk Approve
			else if($_POST['BulkAction_ProfileApproval']=="Approve" || $_POST['BulkAction_ProfileApproval2']=="Approve"){
					
					if(isset($_POST['castingID'])){
						$countProfile = 0;
						foreach($_POST['castingID'] as $key){
							
							$countProfile++;
							$CastingID = $key;
							// Verify Record
							$queryApprove = "UPDATE ". table_agency_casting ." SET CastingIsActive = 1 WHERE CastingID =  ". $CastingID;
							$resultsApprove = mysql_query($queryApprove);
						
							
						}
						
						$profileLabel = '';
						$countProfile > 1 ? $profileLabel = "$countProfile Clients" : $profileLabel = "Profile" ;
					echo ('<div id="message" class="updated"><p>'. __("$profileLabel Approved successfully!", rb_agency_casting_TEXTDOMAIN) .'</p></div>');
						
							
					}
				
			}
		}
		
		if(isset($_GET["action"])=="approveRecord"){
			$CastingID = $_GET["CastingID"];
			$queryApprove = "UPDATE ". table_agency_casting ." SET CastingIsActive = 1 WHERE CastingID =  %d";
			$resultsApprove = $wpdb->query($wpdb->prepare($queryApprove,$CastingID));
			if($resultsApprove){ 
				echo ('<div id="message" class="updated"><p>'. __("$profileLabel Approved successfully!", rb_agency_casting_TEXTDOMAIN) .'</p></div>');
			}
		}
		
		//Paginate
		$items = mysql_num_rows(mysql_query("SELECT * FROM ". table_agency_casting ." profile LEFT JOIN ". table_agency_data_type ." castingtype ON client.CastingType = castingtype.DataTypeID ". $filter  ."")); // number of total rows in the database
		if($items > 0) {
			$p = new rb_agency_pagination;
			$p->items($items);
			$p->limit(50); // Limit entries per page
			$p->target("admin.php?page=". $_GET['page'] .$query);
			$p->currentPage($_GET[$p->paging]); // Gets and validates the current page
			$p->calculate(); // Calculates what to show
			$p->parameterName('paging');
			$p->adjacents(1); //No. of page away from the current page
	 
			if(!isset($_GET['paging'])) {
				$p->page = 1;
			} else {
				$p->page = $_GET['paging'];
			}
	 
			//Query for limit paging
			$limit = "LIMIT " . ($p->page - 1) * $p->limit  . ", " . $p->limit;
		} else {
			$limit = "";
		}
		
		if($items >= 50) {
        echo "<div class=\"tablenav\">\n";
 	    echo "  <div class=\"tablenav-pages\">\n";
		echo $p->show();  // Echo out the list of paging. 
		echo "  </div>\n";
        echo "</div>\n";
    	}
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
		echo "  <thead>\n";
		echo "    <tr>\n";
		echo "        <td style=\"width: 90%;\" nowrap=\"nowrap\">    \n";  
       
	
	
		echo "        	<form method=\"GET\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\">\n";
		echo "        		<input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"". $_GET['page_index'] ."\" />  \n";
		echo "        		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"". $_GET['page'] ."\" />\n";
		echo "        		<input type=\"hidden\" name=\"type\" value=\"name\" />\n";
		echo "        		". __("Search By", rb_agency_casting_TEXTDOMAIN) .": \n";
		echo "        		". __("First Name", rb_agency_casting_TEXTDOMAIN) .": <input type=\"text\" name=\"CastingContactNameFirst\" value=\"". $selectedNameFirst ."\" style=\"width: 100px;\" />\n";
		echo "        		". __("Last Name", rb_agency_casting_TEXTDOMAIN) .": <input type=\"text\" name=\"CastingContactNameLast\" value=\"". $selectedNameLast ."\" style=\"width: 100px;\" />\n";
		echo "        		". __("Location", rb_agency_casting_TEXTDOMAIN) .": \n";
		echo "        		<select name=\"CastingLocationCity\">\n";
		echo "				  <option value=\"\">". __("Any Location", rb_agency_casting_TEXTDOMAIN) ."</option>";
								$query = "SELECT DISTINCT CastingLocationCity, CastingLocationState FROM ". table_agency_casting ." ORDER BY CastingLocationState, CastingLocationCity ASC";
								$results = mysql_query($query);
								$count = mysql_num_rows($results);
								while ($data = mysql_fetch_array($results)) {
									if (isset($data['CastingLocationCity']) && !empty($data['CastingLocationCity'])) {
									echo "<option value=\"". $data['CastingLocationCity'] ."\" ". selected($selectedCity, $data["CastingLocationCity"]) ."\">". $data['CastingLocationCity'] .", ". strtoupper($dataLocation["CastingLocationState"]) ."</option>\n";
									}
								} 
		echo "        		</select>\n";
		echo "        		<input type=\"submit\" value=\"". __("Filter", rb_agency_casting_TEXTDOMAIN) ."\" class=\"button-primary\" />\n";
		echo "          </form>\n";
		echo "        </td>\n";
		echo "        <td style=\"width: 10%;\" nowrap=\"nowrap\">\n";
		echo "        	<form method=\"GET\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\">\n";
		echo "        		<input type=\"hidden\" name=\"page_index\" id=\"page_index\" value=\"". $_GET['page_index'] ."\" />  \n";
		echo "        		<input type=\"hidden\" name=\"page\" id=\"page\" value=\"". $_GET['page'] ."\" />\n";
		echo "        		<input type=\"submit\" value=\"". __("Clear Filters", rb_agency_casting_TEXTDOMAIN) ."\" class=\"button-secondary\" />\n";
		echo "        	</form>\n";
		echo "        </td>\n";
		echo "        <td>&nbsp;</td>\n";
		
		echo "    </tr>\n";
		echo "  </thead>\n";
		echo "</table>\n";
     
		echo "<form method=\"post\" action=\"". admin_url("admin.php?page=". $_GET['page']) ."\" id=\"formMainBulk\">\n";	
	    echo "        		<select name=\"BulkAction_ProfileApproval\">\n";
		echo "              <option value=\"\"> ". __("Bulk Action", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Approve\"> ". __("Approve", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Delete\"> ". __("Delete", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              </select>"; 
		echo "    <input type=\"submit\" value=\"". __("Apply", rb_agency_casting_TEXTDOMAIN) ."\" name=\"ProfileBulkAction\" class=\"button-secondary\"  />\n";
		echo "<table cellspacing=\"0\" class=\"widefat fixed\">\n";
	    echo " <thead>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
		echo "        <th class=\"column-ProfileID\" id=\"ProfileID\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=ProfileID&dir=". $sortDirection) ."\">ID</a></th>\n";
		echo "        <th class=\"column-CastingContactNameFirst\" id=\"CastingContactNameFirst\" scope=\"col\" style=\"width:130px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactNameFirst&dir=". $sortDirection) ."\">First Name</a></th>\n";
		echo "        <th class=\"column-CastingContactNameLast\" id=\"CastingContactNameLast\" scope=\"col\" style=\"width:130px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactNameLast&dir=". $sortDirection) ."\">Last Name</a></th>\n";
		echo "        <th class=\"column-CastingContactEmail\" id=\"CastingContactEmail\" scope=\"col\" style=\"width:165px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingContactEmail&dir=". $sortDirection) ."\">Email Address</a></th>\n";
		echo "        <th class=\"column-ProfilesProfileDate\" id=\"ProfilesProfileDate\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=ProfileDateBirth&dir=". $sortDirection) ."\">Age</a></th>\n";
		echo "        <th class=\"column-CastingLocationCity\" id=\"CastingLocationCity\" scope=\"col\" style=\"width:100px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingLocationCity&dir=". $sortDirection) ."\">City</a></th>\n";
		echo "        <th class=\"column-CastingLocationState\" id=\"CastingLocationState\" scope=\"col\" style=\"width:50px;\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&sort=CastingLocationState&dir=". $sortDirection) ."\">State</a></th>\n";
		echo "        <th class=\"column-ProfileDateViewLast\" id=\"ProfileDateViewLast\" scope=\"col\">Date Created</th>\n";
		echo "    </tr>\n";
		echo " </thead>\n";
		echo " <tfoot>\n";
		echo "    <tr class=\"thead\">\n";
		echo "        <th class=\"manage-column column-cb check-column\" id=\"cb\" scope=\"col\"><input type=\"checkbox\"/></th>\n";
		echo "        <th class=\"column\" scope=\"col\">ID</th>\n";
		echo "        <th class=\"column\" scope=\"col\">First Name</th>\n";
		echo "        <th class=\"column\" scope=\"col\">Last Name</th>\n";
		echo "        <th class=\"column\" scope=\"col\">Email Address</th>\n";
		echo "        <th class=\"column\" scope=\"col\">Age</th>\n";
		echo "        <th class=\"column\" scope=\"col\">City</th>\n";
		echo "        <th class=\"column\" scope=\"col\">State</th>\n";
		echo "        <th class=\"column\" scope=\"col\">Date Created</th>\n";
		echo "    </tr>\n";
		echo " </tfoot>\n";
		echo " <tbody>\n";
        $query = "SELECT * FROM ". table_agency_casting ." client LEFT JOIN ". table_agency_data_type ." castingtype ON client.CastingType = castingtype.DataTypeID ". $filter  ." ORDER BY $sort $limit";
        $results2 = @mysql_query($query);
        $count = @mysql_num_rows($results2);
        while ($data = @mysql_fetch_array($results2)) {
            
            $CastingID = $data['CastingID'];
            $CastingGallery = stripslashes($data['CastingGallery']);
            $CastingContactNameFirst = stripslashes($data['CastingContactNameFirst']);
            $CastingContactNameLast = stripslashes($data['CastingContactNameLast']);
            $CastingLocationCity = RBAgency_Common::format_propercase(stripslashes($data['CastingLocationCity']));
            $CastingLocationState = stripslashes($data['CastingLocationState']);
            $CastingContactEmail = stripslashes($data['CastingContactEmail']);
            $CastingDateBirth = stripslashes($data['CastingDateBirth']);
            $CastingStatHits = stripslashes($data['CastingStatHits']);
            $CastingDateCreated = stripslashes($data['CastingDateCreated']);
            
			 $DataTypeTitle = stripslashes($data['CastingType']);
			
			if(strpos($data['CastingType'], ",") > 0){
            $title = explode(",",$data['CastingType']);
            $new_title = "";
            foreach($title as $t){
                $id = (int)$t;
                $get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .  
                             " WHERE DataTypeID = " . $id;   
                $resource = mysql_query($get_title);             
                $get = mysql_fetch_assoc($resource);
                if (mysql_num_rows($resource) > 0 ){
                    $new_title .= "," . $get['DataTypeTitle']; 
                }
            }
            $new_title = substr($new_title,1);
        } else {
                $new_title = "";
                $id = (int)$data['CastingType'];
                $get_title = "SELECT DataTypeTitle FROM " . table_agency_data_type .  
                             " WHERE DataTypeID = " . $id;   
                $resource = mysql_query($get_title);             
                $get = mysql_fetch_assoc($resource);
                if (mysql_num_rows($resource) > 0 ){
                    $new_title = $get['DataTypeTitle']; 
                }
        }
         
        
        $DataTypeTitle = stripslashes($new_title);
			
		echo "    <tr". $rowColor .">\n";
		echo "        <th class=\"check-column\" scope=\"row\">\n";
		echo "          <input type=\"checkbox\" value=\"". $CastingID ."\" class=\"administrator\" id=\"". $CastingID ."\" name=\"castingID[". $CastingID ."]\"/>\n";
		echo "        </th>\n";
		echo "        <td class=\"ProfileID column-ProfileID\">". $CastingID ."</td>\n";
		echo "        <td class=\"CastingContactNameFirst column-CastingContactNameFirst\">\n";
		echo "          ". $CastingContactNameFirst ."\n";
		echo "          <div class=\"row-actions\">\n";
		echo "            <span class=\"allow\"><a href=\"". admin_url("admin.php?page=". $_GET['page'] ."&amp;action=approveRecord&amp;CastingID=". $CastingID) ."\" title=\"". __("Approve this Record", rb_agency_casting_TEXTDOMAIN) . "\">". __("Approve", rb_agency_casting_TEXTDOMAIN) . "</a> | </span>\n";
		//echo "            <span class=\"edit\"><a href=\"". admin_url("admin.php?page=rb_agency_menu_profiles&amp;action=editRecord&amp;CastingID=". $CastingID) ."\" title=\"". __("Edit this Record", rb_agency_casting_TEXTDOMAIN) . "\">". __("Edit", rb_agency_casting_TEXTDOMAIN) . "</a> | </span>\n";
		echo "            <span class=\"view\"><a href=\"/profile-casting/".  $CastingGallery ."/\" title=\"". __("View", rb_agency_casting_TEXTDOMAIN) . "\" target=\"_blank\">". __("View", rb_agency_casting_TEXTDOMAIN) . "</a> | </span>\n";
		//echo "            <span class=\"delete\"><a class=\"submitdelete\" href=\"". admin_url("admin.php?page=". $_GET['page']) ."&amp;action=deleteRecord&amp;ProfileID=". $CastingID ."\"  onclick=\"if ( confirm('". __("You are about to delete the profile for ", rb_agency_casting_TEXTDOMAIN) ." ". $CastingContactNameFirst ." ". $CastingContactNameLast ."'". __("Cancel", rb_agency_casting_TEXTDOMAIN) . "\' ". __("to stop", rb_agency_casting_TEXTDOMAIN) . ", \'". __("OK", rb_agency_casting_TEXTDOMAIN) . "\' ". __("to delete", rb_agency_casting_TEXTDOMAIN) . ".') ) { return true;}return false;\" title=\"". __("Delete this Record", rb_agency_casting_TEXTDOMAIN) . "\">". __("Delete", rb_agency_casting_TEXTDOMAIN) . "</a> </span>\n";
		echo "          </div>\n";
		echo "        </td>\n";
		echo "        <td class=\"CastingContactNameLast column-CastingContactNameLast\">". $CastingContactNameLast ."</td>\n";
		echo "        <td class=\"CastingContactEmail column-CastingContactEmail\">". $CastingContactEmail ."</td>\n";
		echo "        <td class=\"ProfilesProfileDate column-ProfilesProfileDate\">". rb_agency_get_age($ProfileDateBirth) ."</td>\n";
		echo "        <td class=\"CastingLocationCity column-CastingLocationCity\">". $CastingLocationCity ."</td>\n";
		echo "        <td class=\"CastingLocationCity column-CastingLocationState\">". $CastingLocationState ."</td>\n";
		echo "        <td class=\"ProfileDateViewLast column-ProfileDateViewLast\">\n";
		echo "           ". rb_agency_makeago(rb_agency_convertdatetime($CastingDateCreated), $rb_agency_option_locationtimezone);
		echo "        </td>\n";
		echo "    </tr>\n";
		
		
		
		
        }
            @mysql_free_result($results2);
            if ($count < 1) {
				if (isset($filter)) { 
		echo "    <tr>\n";
		echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
		echo "        <td class=\"name column-name\" colspan=\"5\">\n";
		echo "           <p>No profiles found with this criteria.</p>\n";
		echo "        </td>\n";
		echo "    </tr>\n";
				} else {
		echo "    <tr>\n";
		echo "        <th class=\"check-column\" scope=\"row\"></th>\n";
		echo "        <td class=\"name column-name\" colspan=\"5\">\n";
		echo "            <p>There aren't any profiles loaded yet!</p>\n";
		echo "        </td>\n";
		echo "    </tr>\n";
				}
        } 
		echo " </tbody>\n";
		echo "</table>\n";
		
		echo "        		<select name=\"BulkAction_ProfileApproval2\">\n";
		echo "              <option value=\"\"> ". __("Bulk Action", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Approve\"> ". __("Approve", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              <option value=\"Delete\"> ". __("Delete", rb_agency_casting_TEXTDOMAIN) ."<option\>\n";
		echo "              </select>"; 
		echo "    <input type=\"submit\" value=\"". __("Apply", rb_agency_casting_TEXTDOMAIN) ."\" name=\"ProfileBulkAction\" class=\"button-secondary\"  />\n";
		
		echo "<div class=\"tablenav\">\n";
		echo "  <div class='tablenav-pages'>\n";
			if($items > 0) {
				echo $p->show();  // Echo out the list of paging. 
			}
		echo "  </div>\n";
		echo "</div>\n";
    
		echo "<p class=\"submit\">\n";
		//echo "  <input type=\"hidden\" value=\"deleteRecord\" name=\"action\" />\n";
		//echo "  <input type=\"submit\" value=\"". __('Delete') ."\" class=\"button-primary\" name=\"submit\" />	\n";	
		echo "</p>\n";
		
		
		echo "</form>\n";
}
?>