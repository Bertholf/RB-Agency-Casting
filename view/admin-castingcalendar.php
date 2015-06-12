<?php 

	global $wpdb; 
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'rbcastingcalendarmoment', plugins_url('js/moment.min.js', dirname(__FILE__)), array());
	wp_enqueue_script( 'rbcastingcalendar', plugins_url('js/fullcalendar.min.js', dirname(__FILE__)), array( ));
	wp_register_style( 'rbcastingcalendar', plugins_url('css/fullcalendar.css', dirname(__FILE__)) );
	wp_register_style( 'rbcastingcalendarprint', plugins_url('css/fullcalendar.print.css', dirname(__FILE__)) );
	wp_enqueue_style( 'rbcastingcalendar' );
	wp_enqueue_style( 'rbcastingcalendarprint' );
	wp_enqueue_script( 'jqueryui',  'http://code.jquery.com/ui/1.10.4/jquery-ui.js',false,1,true); 
?>
<h2>Casting Calendar</h2>

<style type="text/css">
	/* DatePicker Container */
.ui-datepicker {
    width: 216px;
    height: auto;
    margin: 5px auto 0;
    font: 9pt Arial, sans-serif;
    -webkit-box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, .5);
    -moz-box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, .5);
    box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, .5);
    background-color: #FFF;
   padding: .2em .2em 0; 
}
table.ui-datepicker-calendar td {
padding: 7px;
}
div.ui-datepicker-header.ui-widget-header.ui-helper-clearfix.ui-corner-all {
text-align: center !important;
}
a.ui-datepicker-prev.ui-corner-all{
	float:left;
}
a.ui-datepicker-next.ui-corner-all{
	float:right;
}
a.ui-state-default.ui-state-active {
background: #E0E0E0;
padding: 4px;
}
</style>
<?php 

  $years = $wpdb->get_results("SELECT YEAR(Job_Date_Start) as ds, YEAR(Job_Date_End) as de, YEAR(Job_Audition_Date_Start) as ads,YEAR(Job_Audition_Date_End) as ade FROM ".table_agency_casting_job."");
  $arr_years = array();
  foreach ($years as $key) {
 		array_push($arr_years, $key->ds);
 		array_push($arr_years, $key->de);
 		array_push($arr_years, $key->ads);
 		array_push($arr_years, $key->ade);
     }
    $arr_years = array_unique(array_filter($arr_years)); 
  ?>
<form action="<?php echo admin_url("admin.php?page=rb_agency_castingcalendar"); ?>" method="get">
	<input type="hidden" value="rb_agency_castingcalendar" name="page" />

	Month: 
	<select name="month">
		<?php for($a=1; $a<=12; $a++):?>
			<option value="<?php echo date("m",strtotime("".($a-1)." month"));?>" <?php  echo (!isset($_GET["month"]) && date("m")==date("m",strtotime("".($a-1)." month")))?"selected='selected'":(isset($_GET["month"]) && $_GET["month"]==date("m",strtotime("".($a-1)." month"))?"selected='selected'":"") ?>><?php echo date("F",strtotime(" ".($a-1)." month"));?></option>
		<?php endfor; ?>
	</select>
	Year: 
	<select name="year">
	<?php foreach ($arr_years as $key) {
			echo "<option value=\"".$key."\" ". (!isset($_GET["year"]) && date("Y")==$key?"selected='selected'":($_GET["year"]==$key)?"selected='selected'":"").">".$key."</option>";
	}?>
	</select>
	<select name="filter">
		<option value="jobs" <?php echo isset($_GET["filter"]) && $_GET["filter"] == "jobs"?"selected='selected'":""; ?>>Jobs</option>
		<option value="auditions" <?php echo isset($_GET["filter"]) && $_GET["filter"] == "auditions"?"selected='selected'":""; ?>>Auditions</option>
	</select>
	<input type="submit"  value="Search" class="button">
</form>
<br/>
<div id='calendar'></div>
<script>

	jQuery(document).ready(function() {
         <?php 
     		$defaultDate =  isset($_GET["year"]) && isset($_GET["month"])?$_GET["year"]."-".$_GET["month"]:date("Y-m");

     		if(!isset($_GET["filter"]) || (isset($_GET["filter"]) && $_GET["filter"] == "jobs")){ // jobs
			   	$casting_jobs = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job."  ");
			   } else {
 				$casting_jobs = $wpdb->get_results("SELECT * FROM ".table_agency_casting_job."  ");
			  }
         ?>
		jQuery('#calendar').fullCalendar({
			header: {
				right: 'month' //,basicWeek,basicDay'
			},
			defaultDate: '<?php echo $defaultDate;?>',
			editable: true,
			eventSources: [
			<?php foreach($casting_jobs as $job): ?>

			{
				events: [
				{
					title: '<?php echo $job->Job_Title;?>',
					<?php if(!isset($_GET["filter"]) || (isset($_GET["filter"]) && $_GET["filter"] == "jobs")): // jobs ?>
			  			start: '<?php echo date("Y-m-d",strtotime($job->Job_Date_Start)); ?>',
						end: '<?php echo date("Y-m-d",strtotime($job->Job_Date_End)); ?>',
					<?php else: ?>
			  			start: '<?php echo date("Y-m-d",strtotime($job->Job_Audition_Date_Start)); ?>',
						end: '<?php echo date("Y-m-d",strtotime($job->Job_Audition_Date_End)); ?>',
					<?php endif; ?>
					url: '<?php echo admin_url("admin.php?page=rb_agency_castingjobs&action=informTalent&Job_ID=".$job->Job_ID); ?>',

				}],
				//color: 'black !important',   
                //textColor: 'yellow !important' 
      		},
			<?php endforeach; ?>
			]

		});

	});

</script>
<style>

	body {
		margin: 0;
		padding: 0;
		font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
		font-size: 14px;
	}



</style>