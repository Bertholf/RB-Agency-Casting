<?php
global $wpdb;

	add_action('init', 'rb_agency_casting_init_sessions');
		function rb_agency_casting_init_sessions() {
			if (!session_id()) {
				session_start();
			}
		}
echo $rb_header = RBAgency_Common::rb_header();
// Profile Class
include(RBAGENCY_PLUGIN_DIR ."app/profile.class.php");
	$rb_agency_options_arr = get_option('rb_agency_options');
		$rb_agency_option_profilenaming = $rb_agency_options_arr['rb_agency_option_profilenaming'];

	echo "<div class=\"one-column\">\n";
	echo "    <div id=\"rbcontent\" role=\"main\" class=\"transparent\">\n";

		echo " <div id=\"profile-private\">\n";

		// Get Profile
		$SearchMuxHash = get_query_var('target');

		if (isset($SearchMuxHash)) {

			// Get Identifier
			$_SESSION['SearchMuxHash'] = $SearchMuxHash;

			// Get Casting Cart by Identifier
			$query = "SELECT search.SearchTitle, search.SearchProfileID, search.SearchOptions, searchsent.SearchMuxHash FROM ". table_agency_searchsaved ." search LEFT JOIN ". table_agency_searchsaved_mux ." searchsent ON search.SearchID = searchsent.SearchID WHERE searchsent.SearchMuxHash = \"". $SearchMuxHash ."\"";
			$results = $wpdb->get_results($query,ARRAY_A);
			$count =  $wpdb->num_rows;
			// Get Casting Cart ID
			foreach($results as $data) {
				$castingcart_id = $data['SearchProfileID'];
			}

			// Return Search

			$search_array = array("perpage" => 9999, "include" => $castingcart_id);
			$search_sql_query = RBAgency_Profile::search_generate_sqlwhere($search_array);

			// Process Form Submission
			echo $search_results = RBAgency_Profile::search_results($search_sql_query, 0);

			// echo  $formatted = RBAgency_Profile::search_formatted($search_array);




		}
		if (empty($SearchMuxHash) || ($count == 0)) {
			echo "<strong>". __("No search results found.  Please check link again.", RBAGENCY_TEXTDOMAIN) ."</strong>";
		}

		echo "  <div style=\"clear: both;\"></div>";
		echo " </div>\n";
		echo "  </div>\n";
		echo "</div>\n";

//get_sidebar(); 
echo $rb_footer = RBAgency_Common::rb_footer(); 
?>