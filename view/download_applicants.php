<?php

ob_start();
error_reporting(0);
// Tap into WordPress Database
@include_once('../../../../wp-config.php');
@include_once('../../../../wp-load.php');
@include_once('../../../../wp-includes/wp-db.php');
global $wpdb;


		unset($_POST["mass_delete"]);
		unset($_POST['batch_download']);

		$ids = $_GET['profileids'];
		$q = "SELECT cs_job.*, avail.* FROM  ".table_agency_casting_job." AS cs_job INNER JOIN ".table_agency_castingcart_availability."
					AS avail ON cs_job.Job_ID = avail.CastingJobID WHERE avail.CastingAvailabilityProfileID IN (".$ids.") AND cs_job.Job_ID = ".$_GET['Job_ID'];
		$results = $wpdb->get_results($q,ARRAY_A);
		
		$output_arr =array();
		$output_arr[] = array('Name','Job Title','Date Confirmed','MP3 Audition Files','Availability');
		//$output_arr[] = array('Name','Job Title','Date Confirmed','MP3 Audition File','Availability');
		foreach($results as $res){

			$queryData1 = "SELECT * FROM " . table_agency_profile . " WHERE ProfileID = ".$res['CastingAvailabilityProfileID'];
				$qd = $wpdb->get_results($queryData1,ARRAY_A);
				//print_r($qd);
				foreach($qd as $profile){
					$fullname = $profile['ProfileContactNameFirst'].' '.$profile['ProfileContactNameLast'];
				}

			
			$jobtitle = $res['Job_Title'];
			$dateconfirmed = $res['CastingAvailabilityDateCreated'];

			//mp3 file
			$dir = RBAGENCY_UPLOADPATH ."_casting-jobs/";
			$files = scandir($dir, 0);
											
			$medialink_option = $rb_agency_options_arr['rb_agency_option_profilemedia_links'];
			$mp3_file = '';
			for($i = 0; $i < count($files); $i++){
				$parsedFile = explode('-',$files[$i]);

					if($parsedFile[0] == $res['Job_ID'] && $res['CastingAvailabilityProfileID'] == $parsedFile[1]){
						$mp3_file .= str_replace(array($parsedFile[0].'-',$parsedFile[1].'-'),'',$files[$i])." ";
					}
			}

			$availability = $res['CastingAvailabilityStatus'];

			$output_arr[] = array($fullname,$jobtitle,$dateconfirmed,$mp3_file,$availability);
			
		}

	

	$extension = "";
				$type = "";
			   if($_GET["export_type"] == "csv"){
			   	$type = "CSV";
			   	$extension = "csv";
			   } elseif($_GET["export_type"] == "xls"){
					$type = "Excel5";
			   	$extension = "xls";
			  }

	require_once("PHPExcel.php");
	require_once("PHPExcel/IOFactory.php");
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->setActiveSheetIndex(0);
	$rowNumber = 1;

	/*Getting headers*/
	$headings = array();
	

	$objPHPExcel->getActiveSheet()->fromArray(array($headings),NULL,'A'.$rowNumber);
	$objPHPExcel->getActiveSheet()->fromArray($output_arr,NULL,'A'.$rowNumber);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,  $type);
	$objWriter->save(str_replace('.php', '.'.$extension, __FILE__));

	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
	header("Content-Type: application/force-download");
	header("Content-Type: application/octet-stream");
	header("Content-Type: application/download");
	header("Content-Disposition: attachment;filename=auditions.".$extension); 
	header("Content-Transfer-Encoding: binary ");
	ob_clean();
	flush();
	readfile(str_replace('.php', '.'.$extension, __FILE__));
	unlink(str_replace('.php', '.'.$extension, __FILE__));



	?>