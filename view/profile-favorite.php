<?php
// *************************************************************************************************** //
// This is the Profile-favorite page 
include(rb_agency_BASEREL ."app/profile.class.php");
global $wpdb;
// Get Profile
if (isset($ProfileType) && !empty($ProfileType)){
	$DataTypeID = 0;
	$DataTypeTitle = "";
	$query = "SELECT DataTypeID, DataTypeTitle FROM ". table_agency_data_type ." WHERE DataTypeTag = '". $ProfileType ."'";

	$results = $wpdb->get_results($query,ARRAY_A);
	foreach($results as $data) {
		$DataTypeID = $data['DataTypeID'];
		$DataTypeTitle = $data['DataTypeTitle'];
		$filter .= " AND profile.ProfileType=". $DataTypeID ."";
	}
}

echo $rb_header = RBAgency_Common::rb_header(); 

echo "	<div id=\"primary\" class=\"".fullwidth_class()." column\">\n";
echo "  	<div id=\"content\" role=\"main\" class=\"transparent\">\n";
echo '			<header class="entry-header">';
echo '				<h1 class="entry-title">Favorites</h1>';
echo '			</header>';
echo '			<div class="entry-content">';
echo "				<div id=\"profile-favorites\">\n";

			// Return favorites
			$search_array = array();
			$search_sql_query = RBAgency_Profile::search_generate_sqlwhere($search_array);
			
			//query type favorite
			$query_type = 1;
			
			// Process Form Submission
			echo $search_results = RBAgency_Profile::search_results($search_sql_query, $query_type);

echo "				</div>\n";
echo "				<div class=\"cb\"></div>\n";
echo "			</div><!-- .entry-content -->\n"; // .entry-content
echo "			<input type=\"hidden\" name=\"favorite\" value=\"1\"/>";
echo "  	</div><!-- #content -->\n"; // #content
echo "	</div><!-- #primary -->\n"; // #primary

//	get_sidebar();        

echo $rb_footer = RBAgency_Common::rb_footer(); ?>