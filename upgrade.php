<?php
global $wpdb;


// Safe Add column
	function rb_agency_addColumn($tbl = "",$column = "", $atts = ""){
		global $wpdb;
		$debug = debug_backtrace();
		if($wpdb->get_var("SHOW COLUMNS FROM ".trim($tbl)." LIKE '%".trim($column)."%' ") != trim($column)){
			$result = $wpdb->query(" ALTER TABLE ".trim($tbl)." ADD ".trim($column)." ".$atts.";");// or die("rb_agency_addColumn()  - Adding column ".trim($column)." in line ".$debug["line"]." <br/> ".mysql_error());
			return $result;
		}
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.1") {
		// Add Table
		if ($wpdb->get_var("show tables like '".table_agency_castingcart_jobs."'") !=table_agency_castingcart_jobs) { 
			$results = $wpdb->query("CREATE TABLE IF NOT EXISTS ".table_agency_castingcart_jobs ." (
			CastingJobID INT(20) NOT NULL AUTO_INCREMENT,
			CastingJobAudition VARCHAR(100) ,
			CastingJobRole VARCHAR(100),
			CastingJobAuditionDate VARCHAR(50),
			CastingJobAuditionVenue VARCHAR(500),
			CastingJobAuditionTime VARCHAR(100),
			CastingJobClothing VARCHAR(600),
			CastingJobRCallBackWardrobe VARCHAR(600),
			CastingJobScript VARCHAR(600),
			CastingJobShootDate VARCHAR(100),
			CastingJobRoleFee VARCHAR(600),
			CastingJobComments VARCHAR(1000),
			CastingJobSelectedFor VARCHAR(100),
			CastingJobDateCreated TIMESTAMP,
			PRIMARY KEY (CastingJobID)
			);") or mysql_error();
		}

		// Add Table
		if ($wpdb->get_var("show tables like '".table_agency_castingcart_availability."'") !=table_agency_castingcart_availability) { 
			$results = $wpdb->query("CREATE TABLE IF NOT EXISTS ".table_agency_castingcart_availability ." (
			CastingAvailabilityID INT(20) NOT NULL AUTO_INCREMENT,
			CastingAvailabilityProfileID INT(20) NOT NULL,
			CastingAvailabilityStatus VARCHAR(255),
			CastingAvailabilityDateCreated TIMESTAMP,
			PRIMARY KEY (CastingAvailabilityID)
			);") or mysql_error();
		}

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.2");
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.2") {

		rb_agency_addColumn( table_agency_castingcart_availability,"CastingJobID","INT(10) NOT NULL DEFAULT '0'");

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.3");
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.3") {

		rb_agency_addColumn( table_agency_castingcart_jobs,"CastingJobTalents","VARCHAR(500)");

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.4");
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 5) == "0.0.4") {

		rb_agency_addColumn( table_agency_castingcart_jobs,"CastingJobTalentsHash","VARCHAR(10)");

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.5");
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.5") {

		rb_agency_addColumn( table_agency_castingcart_jobs,"CastingJobWardrobe","VARCHAR(600)");
		$wpdb->query("ALTER TABLE ".table_agency_castingcart_jobs." CHANGE CastingJobRCallBackWardrobe CastingJobRCallBack VARCHAR(600)");
	

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.6");
	}
	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.6") {

		rb_agency_addColumn( table_agency_castingcart_jobs,"CastingJobShootLocation","VARCHAR(600)");
	    rb_agency_addColumn( table_agency_castingcart_jobs,"CastingJobShootLocationMap","VARCHAR(600)");
				

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.7");
	}

	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.7") {

		if ($wpdb->get_var("show tables like '".table_agency_castingcart_profile_hash."'") !=table_agency_castingcart_profile_hash) { 
		// Casting Jobs > Invite Profile ID hash
				$sql = "CREATE TABLE IF NOT EXISTS ". table_agency_castingcart_profile_hash." (
					CastingProfileHashID BIGINT(20) NOT NULL AUTO_INCREMENT,
					CastingProfileHashJobID VARCHAR(255),
					CastingProfileHashProfileID VARCHAR(255),
					CastingProfileHash VARCHAR(255),
					PRIMARY KEY (CastingProfileHashID)
					);";
				$wpdb->query($sql) or mysql_error();
		}
		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.8");
	}



  	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.8") {

			$wpdb->query("DROP TABLE {$wpdb->prefix}agency_casting_job");

			$wpdb->query("RENAME TABLE {$wpdb->prefix}agency_castingcart_profile_hash TO {$wpdb->prefix}agency_casting_job_hash");
			$wpdb->query("RENAME TABLE {$wpdb->prefix}agency_castingcart_jobs TO {$wpdb->prefix}agency_casting_job");
			
			rb_agency_addColumn( table_agency_castingcart_jobs,"Visibility","INT(10)");

		// Updating version number!
		update_option('rb_agency_casting_version', "0.0.9");
   }

   	if (substr(get_option('rb_agency_casting_version'), 0, 7) == "0.0.9") {

	   // Add Table
		if ($wpdb->get_var("show tables like '".table_agency_castingcart_jobs."'") !=table_agency_castingcart_jobs) { 
				$results = $wpdb->query("CREATE TABLE IF NOT EXISTS ".table_agency_castingcart_jobs ." (
				CastingJobID INT(20) NOT NULL AUTO_INCREMENT,
				CastingJobAudition VARCHAR(100) ,
				CastingJobRole VARCHAR(100),
				CastingJobAuditionDate VARCHAR(50),
				CastingJobAuditionVenue VARCHAR(500),
				CastingJobAuditionTime VARCHAR(100),
				CastingJobClothing VARCHAR(600),
				CastingJobRCallBackWardrobe VARCHAR(600),
				CastingJobScript VARCHAR(600),
				CastingJobShootDate VARCHAR(100),
				CastingJobRoleFee VARCHAR(600),
				CastingJobComments VARCHAR(1000),
				CastingJobSelectedFor VARCHAR(100),
				CastingJobDateCreated TIMESTAMP,
				PRIMARY KEY (CastingJobID)
				);") or mysql_error();
		}

		// Updating version number!
		update_option('rb_agency_casting_version', "0.1.0");
	}
