<?php
// *************************************************************************************************** //
// Admin Head Section 

/*	add_action('admin_head', 'rb_agency_casting_admin_head');
		function rb_agency_casting_admin_head(){
		  if( is_admin() ) {
			echo "<link rel=\"stylesheet\" href=\"". rb_agency_casting_BASEDIR ."style/admin.css\" type=\"text/css\" media=\"screen\" />\n";
		  }
		}*/
	
	// Remember to flush_rules() when adding rules
	add_filter('init','rbcasting_flushrules');
		function rbcasting_flushRules() {
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
	}

	// Adding a new rule
	add_filter('rewrite_rules_array','rb_agency_casting_rewriteRules');
		function rb_agency_casting_rewriteRules($rules) {
			$newrules = array();
			$newrules['casting-manage'] = 'index.php?type=castingmanage';
			$newrules['casting-dashboard'] = 'index.php?type=castingoverview';
			$newrules['casting-register'] = 'index.php?type=castingregister';
			$newrules['casting-login'] = 'index.php?type=castinglogin';
			$newrules['profile-casting/(.*)$'] = 'index.php?type=casting&target=$matches[1]';
			$newrules['profile-casting'] = 'index.php?type=casting&target=casting';
			$newrules['client-view/(.*)$'] = 'index.php?type=profilecastingcart&target=$matches[1]';
			$newrules['profile-favorite'] = 'index.php?type=favorite';
			return $newrules + $rules;
		}
	
	// Set Custom Template
	add_filter('template_include', 'rb_agency_casting_template_include', 1, 1); 
		function rb_agency_casting_template_include( $template ) {
			if ( get_query_var( 'type' ) ) {
			  if (get_query_var( 'type' ) == "castingoverview") {
				return dirname(__FILE__) . '/view/casting-overview.php'; 
			  } elseif (get_query_var( 'type' ) == "castingmanage") {
				return dirname(__FILE__) . '/view/casting-manage.php'; 
			  } elseif (get_query_var( 'type' ) == "castinglogin") {
				return dirname(__FILE__) . '/view/casting-login.php'; 
			  } elseif (get_query_var( 'type' ) == "castingregister") {
				return dirname(__FILE__) . '/view/casting-register.php'; 
			  }	elseif (get_query_var( 'type' ) == "favorite") {
				return dirname(__FILE__) . '/view/profile-favorite.php';
			  } elseif (get_query_var( 'type' ) == "casting") {
				return dirname(__FILE__) . '/view/profile-viewcasting.php';
			  }	elseif (get_query_var( 'type' ) == "profilecastingcart") {
				return rb_agency_BASEREL . 'view/profile-castingcart.php';
			  } 
			}
			return $template;
		}
	
	function get_state_json(){
		global $wpdb;
		$states=array();
		$country=$_POST['countryid'];
		$query_get ="SELECT * FROM ".table_agency_data_state." WHERE CountryID='".$country."'";
		$result_query_get = $wpdb->get_results($query_get);
		echo json_encode($result_query_get);
		die();	
	}
    add_action('wp_ajax_get_state_json', 'get_state_json');
    add_action('wp_ajax_nopriv_get_state_json', 'get_state_json');	

	/* 
	 * Profile Favorite Front End
	 */
	function rb_agency_save_favorite() {
		global $wpdb;
		if(is_user_logged_in()){	
			if(isset($_POST["talentID"])){
				 $query_favorite = mysql_query("SELECT * FROM ".table_agency_savedfavorite." WHERE SavedFavoriteTalentID='".$_POST["talentID"]."'  AND SavedFavoriteProfileID = '".rb_agency_get_current_userid()."'" ) or die("error");
				 $count_favorite = mysql_num_rows($query_favorite);
				 $datas_favorite = mysql_fetch_assoc($query_favorite);
				 
				 if($count_favorite<=0){ //if not exist insert favorite!
					 
					   mysql_query("INSERT INTO ".table_agency_savedfavorite."(SavedFavoriteID,SavedFavoriteProfileID,SavedFavoriteTalentID) VALUES('','".rb_agency_get_current_userid()."','".$_POST["talentID"]."')") or die("error");
					   echo "inserted";
					   
				 }else{ // favorite model exist, now delete!
					 
					   mysql_query("DELETE FROM  ".table_agency_savedfavorite." WHERE SavedFavoriteTalentID='".$_POST["talentID"]."'  AND SavedFavoriteProfileID = '".rb_agency_get_current_userid()."'") or die("error");
					   echo "deleted";
				
				 }				
			}			
		}
		else {
			echo "not_logged";
		}
		die();
	}

	function rb_agency_save_favorite_javascript() {

			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_option_layoutprofile = (int)$rb_agency_options_arr['rb_agency_option_layoutprofile'];
			$rb_agency_option_layoutprofile = sprintf("%02s", $rb_agency_option_layoutprofile);

	?>

		<!--RB Agency Favorite -->
		<script type="text/javascript" >
		var layout_favorite = "<?php echo $rb_agency_option_layoutprofile; ?>";
		jQuery(document).ready(function () {
			jQuery(".favorite a:first, .favorite a").click(function () {
				var Obj = jQuery(this);
				jQuery.ajax({
					type: 'POST',
					url: '<?php echo admin_url('admin-ajax.php'); ?>',
					data: {
						action: 'rb_agency_save_favorite',
						'talentID': jQuery(this).attr("id")
					},
					success: function (results) {
						if (results == 'error') {
							Obj.fadeOut().empty().html("Error in query. Try again").fadeIn();
						} else if (results == -1) {
							Obj.fadeOut().empty().html("<span style=\"color:red;font-size:11px;\">You're not signed in.</span><a href=\"<?php echo get_bloginfo('wpurl'); ?>/profile-member/\">Sign In</a>.").fadeIn();
							setTimeout(function () {
								if (Obj.attr("class") == "save_favorite") {
									Obj.fadeOut().empty().html("").fadeIn();
									Obj.attr('title', 'Save to Favorites');
								} else {
									Obj.fadeOut().empty().html("Favorited").fadeIn();
									Obj.attr('title', 'Remove from Favorites');
								}
							}, 2000);
						} else {
							<?php
							if (get_query_var('type') == "favorite"){?>
									Obj.parents(".rbprofile-list").hide("slow",function(){Obj.parents(".rbprofile-list").remove();});
							<?php } else { ?>
								if(layout_favorite == "00"){
									if (Obj.hasClass("save_favorite") || (Obj.hasClass("favorited") && jQuery.trim(results)=="inserted") ) {
										Obj.removeClass("save_favorite");
										Obj.addClass("favorited");
										Obj.attr('title', 'Remove from Favorites');
										Obj.html('Remove from Favorites');
									} else {
										Obj.removeClass("favorited");
										Obj.addClass("save_favorite");
										Obj.attr('title', 'Add to Favorites');
										Obj.html('Add to Favorites');
									}
								} else {
									if (Obj.attr("class") == "save_favorite") {
										Obj.empty().fadeOut().empty().html("").fadeIn();
										Obj.attr("class", "favorited");
										Obj.attr('title', 'Remove from Favorites')
									} else {
										Obj.empty().fadeOut().empty().html("").fadeIn();
										Obj.attr('title', 'Save to Favorites');
										jQuery(this).find("a[class=view_all_favorite]").remove();
										Obj.attr("class", "save_favorite");
									}
								}
						    <?php } ?>
						}
					}
				})
			});
		});
		</script>
		<!--END RB Agency Favorite -->

		<!-- [class=profile-list-layout<?php echo (int)$rb_agency_option_layoutprofilelist; ?>]-->
		<?php
	}

	add_action('wp_footer', 'rb_agency_save_favorite_javascript');
	add_action('wp_ajax_rb_agency_save_favorite', 'rb_agency_save_favorite');

	/* 
	 * Profile Casting Front End
	 */	
	function rb_agency_save_castingcart() {
				global $wpdb;
			
				if(is_user_logged_in()){ 
					if(isset($_POST["talentID"])){ 
						$query_castingcart = mysql_query("SELECT * FROM ". table_agency_castingcart."  WHERE CastingCartTalentID='".$_POST["talentID"]."'  AND CastingCartProfileID = '".rb_agency_get_current_userid()."'" ) or die("error");
						$count_castingcart = mysql_num_rows($query_castingcart);
						$datas_castingcart = mysql_fetch_assoc($query_castingcart);
						 
						if($count_castingcart<=0){ //if not exist insert favorite!
							$wpdb->insert(table_agency_castingcart, array('CastingCartProfileID'=>rb_agency_get_current_userid(), 'CastingCartTalentID'=>$_POST["talentID"]));
							echo "inserted";
						} else { // favorite model exist, now delete!
							mysql_query("DELETE FROM  ". table_agency_castingcart."  WHERE CastingCartTalentID='".$_POST["talentID"]."'  AND CastingCartProfileID = '".rb_agency_get_current_userid()."'") or die("error");
							echo "deleted";
						}
					}
				}
				else {
					echo "not_logged";
				}
				die();
			}
		
		function rb_agency_save_castingcart_javascript() {
			
			$rb_agency_options_arr = get_option('rb_agency_options');
			$rb_agency_option_layoutprofile = (int)$rb_agency_options_arr['rb_agency_option_layoutprofile'];
			$rb_agency_option_layoutprofile = sprintf("%02s", $rb_agency_option_layoutprofile);

		?>
				<!--RB Agency CastingCart -->
				<script type="text/javascript" >
					var layout_casting = "<?php echo $rb_agency_option_layoutprofile; ?>";
					jQuery(document).ready(function ($) {
						$(".castingcart a").click(function () {
							var Obj = $(this);
							jQuery.ajax({
								type: 'POST',
								url: '<?php echo admin_url('admin-ajax.php'); ?>',
								data: {
									action: 'rb_agency_save_castingcart',
									'talentID': $(this).attr("id")
								},
								success: function (results) {
									if (results == 'error') {
										Obj.fadeOut().empty().html("Error in query. Try again").fadeIn();
									} else if (results == -1) {
										Obj.fadeOut().empty().html("<span style=\"color:red;font-size:11px;\">You're not signed in.</span><a href=\"<?php echo get_bloginfo('wpurl'); ?>/profile-member/\">Sign In</a>.").fadeIn();
										setTimeout(function () {
											if (Obj.attr("class") == "save_castingcart") {
												Obj.fadeOut().empty().html("").fadeIn();
											} else {
												Obj.fadeOut().empty().html("").fadeIn();
											}
										}, 2000);
									} else {
										<?php 
										if (get_query_var('type') == "casting"){?>
											Obj.parents(".rbprofile-list").hide("slow",function(){Obj.parents(".rbprofile-list").remove();});
										<?php
										} else { 
										?>
											if(layout_casting == "00"){
												if (Obj.hasClass("save_castingcart") || (Obj.hasClass("saved_castingcart") && jQuery.trim(results)=="inserted")) {
													Obj.removeClass("save_castingcart");
													Obj.addClass("saved_castingcart");
													Obj.attr('title', 'Remove from Casting Cart');
													Obj.html('Remove from Casting Cart');
												} else {
													Obj.removeClass("saved_castingcart");
													Obj.addClass("save_castingcart");
													Obj.attr('title', 'Add to Casting Cart');
													Obj.html('Add to Casting Cart');
												}
											} else {
												if (Obj.attr("class") == "save_castingcart") {
													Obj.empty().fadeOut().html("").fadeIn();
													Obj.attr("class", "saved_castingcart");
													Obj.attr('title', 'Remove from Casting Cart');
												} else {
													Obj.empty().fadeOut().html("").fadeIn();
													Obj.attr("class", "save_castingcart");
													Obj.attr('title', 'Add to Casting Cart');
													$(this).find("a[class=view_all_castingcart]").remove();
												}
											}
										<?php } ?>
									}
								}
							})
						});
				});	
			 </script>
		<?php
		}

		add_action('wp_ajax_rb_agency_save_castingcart', 'rb_agency_save_castingcart');
		add_action('wp_footer', 'rb_agency_save_castingcart_javascript');



	/*/
	 *  Fix form post url for multi language.
	/*/
/*
	function rb_agency_casting_postURILanguage($request_URI){
	     if(!in_array(substr($_SERVER['REQUEST_URI'],1,2), array("en","nl"))){
			if (function_exists('trans_getLanguage')) {
				 if(qtrans_getLanguage()=='nl') {
					return "/".qtrans_getLanguage();
				
				} elseif(qtrans_getLanguage()=='en') {
					return "/".qtrans_getLanguage();
				}
			 }
	    }
	}
	*/

// *************************************************************************************************** //
// Handle Emails

	// Make Directory for new profile
/*     function rb_agency_casting_checkdir($ProfileGallery){
	      	
			if (!is_dir(rb_agency_UPLOADPATH . $ProfileGallery)) {
				mkdir(rb_agency_UPLOADPATH . $ProfileGallery, 0755);
				chmod(rb_agency_UPLOADPATH . $ProfileGallery, 0777);
			}
			return $ProfileGallery;
     }*/



// *************************************************************************************************** //
// Functions

	// Move Login Page	
/*	add_filter("login_init", "rb_agency_casting_login_movepage", 10, 2);
		function rb_agency_casting_login_movepage( $url ) {
			global $action;
		
			if (empty($action) || 'login' == $action) {
				wp_safe_redirect(get_bloginfo("wpurl"). "/profile-login/");
				die;
			}
		}

	// Redirect after Login
	add_filter('login_redirect', 'rb_agency_casting_login_redirect', 10, 3);	
		function rb_agency_casting_login_redirect() {
			global $user_ID, $current_user, $wp_roles;
			if( $user_ID ) {
				$user_info = get_userdata( $user_ID ); 

				if( current_user_can( 'manage_options' )) {
					header("Location: ". get_bloginfo("wpurl"). "/wp-admin/");
				} elseif ( strtotime( $user_info->user_registered ) > ( time() - 172800 ) ) {
					// If user_registered date/time is less than 48hrs from now
					// Message will show for 48hrs after registration
					header("Location: ". get_bloginfo("wpurl"). "/profile-member/account/");
				} else {
					header("Location: ". get_bloginfo("wpurl"). "/profile-member/");
				}
			}
		}*/




?>