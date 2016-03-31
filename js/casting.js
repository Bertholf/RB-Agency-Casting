// JavaScript Document

//Populate state options for selected  country
/*function populateStates(){
	
	}



 jQuery(function(){


    
	jQuery("#CastingCountry").change(function(){

			var url=jQuery("#url").val();
			console.log("URL:"+url);
			if(jQuery("#CastingCountry").val()!=""){
					jQuery("#CastingState").show();
					jQuery("#CastingState").find("option:gt(0)").remove();
					jQuery("#CastingState").find("option:first").text("Loading...");
					jQuery.ajax({
							type: 'POST',
							dataType: 'json',
							url: url,
							data: { 
								action: 'get_state_ajax', 
								country: jQuery("#CastingCountry").val()
							},
							success: function(data){
								console.log(data);
								jQuery("<option/>").attr("value", "").text("Select State").appendTo(jQuery("#CastingState"));	
											for (var i = 0; i < data.length; i++) {
									jQuery("<option/>").attr("value", data[i].StateID).text(data[i].StateTitle).appendTo(jQuery("#CastingState"));
								}
								jQuery("#CastingState").find("option:eq(0)").remove();
							},
							error: function(e){
								console.log(e);
							}
						});
				}else{
					jQuery("#CastingState").find("option:gt(0)").remove();
				}

	});

});*/

//Populate state options for selected  country
function populateStatesCastingRegister(countryId,stateId){
	var url=jQuery("#url").val();
	var ajax_url = (typeof(rb_ajaxurl)!=="undefined")? rb_ajaxurl: ajaxurl;
	console.log(ajax_url);
	console.log(countryId);
	console.log(stateId);
	if(jQuery("#"+countryId).val()!=""){
			jQuery("#"+stateId).show();
			jQuery("#"+stateId).find("option:gt(0)").remove();
			jQuery("#"+stateId).find("option:first").text("Loading...");
		jQuery.ajax({
			type:'POST',
			dataType : "json",
	        data:{action:"get_state_ajax",country:jQuery("#"+countryId).val()},
			url: ajax_url,
			success:function(data) {		
				jQuery("<option/>").attr("value", "").text(objectL10n.select_state).appendTo(jQuery("#"+stateId));	
	                        for (var i = 0; i < data.length; i++) {
								jQuery("<option/>").attr("value", data[i].StateID).text(data[i].StateTitle).appendTo(jQuery("#"+stateId));
							}
				jQuery("#"+stateId).find("option:eq(0)").remove();
				console.log(data);
			},
			error: function(e){
				console.log(e);
			}
		});
		
	}else{
		jQuery("#"+stateId).find("option:gt(0)").remove();
			
	}
 }	