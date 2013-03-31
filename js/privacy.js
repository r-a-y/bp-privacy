jQuery(document).ready(function() {
	
	jQuery("select[id*='-acl']").change(
		function() {
			var idname = jQuery(this).attr("id");
			var selection = jQuery("select#" + idname).val();
			//alert(idname + " = " + selection);
			
			var group_list = (idname + "-grouplist");
			var user_list = (idname + "-userlist");
			//alert("Group: " + group_list + "; User: " + user_list);
			
			// Initialize
			var list_type = "";

			if(selection == '3') {
				//alert("ACL = 3");
				
				list_type = "grouplist";
								
				jQuery("div#" + user_list).hide();
				
				jQuery("div#" + group_list + " select").attr("disabled", false);
				jQuery("div#" + group_list + " select").focus();
				
			} else if(selection == '4') {
				//alert("ACL = 4");
				
				list_type = "userlist";
								
				jQuery("div#" + group_list).hide();
				
				jQuery("div#" + user_list + " select").attr("disabled", false);
				jQuery("div#" + user_list + " select").focus();
				
			} else {
				//alert("Hiding group or user listbox");
				jQuery("div#" + group_list).hide();
				jQuery("div#" + user_list).hide();

			}
			
			if( selection == '3' || selection == '4' ) {
				
				// Show the Ajax-loading spinner gif
				jQuery("div#" + idname + "-listbox div.listbox_output img").show();
			
				// Grab the data only from the hidden input elements of the selected division
				var listbox_params = jQuery("div#" + idname + "-listbox :input" ).serializeArray();

				// Initialize variables so they can get set within and then passed out of the .each function
				var privacy_component = "";
				var acl_rec = 0;
				var single_rec = 0;
				var tiered = false;
				var form_level = "";
				var group_rec = 0;
			
				// Loop through the serialized array and assign values
				jQuery.each(listbox_params, function(i, field) {
					
					if(field.name == 'component') {
						privacy_component = field.value;
					}
					
					if(field.name == 'acl_rec') {
						acl_rec = field.value;
					}
					
					if(field.name == 'single_rec') {
						single_rec = field.value;
					}
					
					if(field.name == 'tiered') {
						tiered = field.value;
					}
					
					if(field.name == 'form_level') {
						form_level = field.value;
					}
					
					if(field.name == 'group_rec') {
						group_rec = field.value;
					}
				});
				
				// Grab the data only from the hidden input elements of the selected division
				var lists = jQuery("div#" + idname + "-json-lists-" + acl_rec + "  :input").val();
				
				//***
				//alert("Passed in JSON List from " + idname + "-json-lists-" + acl_rec + " = " + lists);
				
				// Make sure browsers (specifically IE) do not cache AJAX
				jQuery.ajaxSetup({
					 cache: false,
				});
				
				// If an AJAX error occurs, catch it and report
				jQuery(document).ajaxError(function(e, xhr, settings, exception) {
					alert('error in: ' + settings.url + '; '+'error: ' + exception);
				});
				
				var nonce_name = ("bp-authz-privacy-" + privacy_component);
				var wp_nonce_name = jQuery("input#_wpnonce_privacy-" + privacy_component).val();
				
				/* To learn more about how this AJAX function works within BP Privacy, 
				 * see the "Using AJAX to display Group and User Listboxes" subsection
				 * in the Developer's Guide section of the BuddyPress Privacy Manual.
				 * 
				 * To learn more about AJAX in BuddyPress plugins, see Note 2 in the section
				 * entitled: "Ajax on the Viewer-Facing Side" of this WP Codex article:
				 * http://codex.wordpress.org/AJAX_in_Plugins 
				 */
				jQuery.post(PrivacyAjax, {
	    			action: "bp_authz_ajax_listbox",
	    			'_wpnonce': ""+wp_nonce_name+"",
	    			'nonce_name': ""+nonce_name+"",
					'list_array': ""+lists+"",
					'list_type': ""+list_type+"",
					'bpaz_level': ""+selection+"",
					'acl_rec': ""+acl_rec+"",
					'single_rec': ""+single_rec+"",
					'tiered': ""+tiered+"",
					'form_level': ""+form_level+"",
					'group_rec': ""+group_rec+""
	    		}, 
	    		function(data) {
	        		if(data.status == "success") 
	            	{   
	            		//alert("AJAX Complete!");
						
						// Output the new group or user listbox
						jQuery("div#" + idname + "-listbox div.listbox_output").html(data.listbox_html);
												
						// Place the hidden image element containing the Ajax-loading spinner gif back into the division
						jQuery("div#" + idname + "-listbox div.listbox_output").append("<img class='ajax_spinner' src='" + bpaz_ajax_spinner + "' alt='Loading...' />");
						
						if(data.message != "none")
							alert(data.message);
	
	                } else {
	                	alert("AJAX Failure!");
	                	alert(data.message);
	            	}
	        	},
	        	"json");

			}
		}
	);
	
	/* Expand or collapse privacy table sections on tiered privacy settings forms
	 * when the "More options..." and "Fewer options..." links are clicked.
	 */
	jQuery("th[id^='expand-button-']").click( function(event, param1) {
			
			// Initialize parameter if undefined
			if(typeof param1 == 'undefined') {
        		//alert("Parameter undefined");
        		var param1 = '';
    		}
			
			//alert("Passed parameter into jQuery = " + param1);
			
			if(param1.length == 0) {
				//alert("Parameter empty");
				var headerid = jQuery(this).attr("id");
			} else {
				//alert("Parameter = " + param1);
				var headerid = "expand-button-" + param1;
				//alert("Table Header Triggered = " + headerid);
			}
			
			//var test_headerid = jQuery(this);
			//alert("Test Header ID = " + test_headerid);
			
			//var headerid = jQuery(this).attr("id");
			var link_text = jQuery("th#" + headerid + " p").text();
			
			//alert("Table Header Class = " + headerid);
			//alert("Link Text = " + link_text);
			
			//alert("More Options Variable = " + bpaz_more);
			//alert("Fewer Options Variable = " + bpaz_fewer);
			
			// Determine the slider section being manipulated
			var stringpos2 = headerid.lastIndexOf( "-" );
			var group_number = headerid.substr( stringpos2 + 1 );
			
			// Determine the table being manipulated
			var tableid = jQuery("table[id*='-privacy-settings-" + group_number + "']").attr("id");
			//alert("Table ID = " + tableid);
			
			var stringpos = tableid.indexOf( "-" );
			var slider_group = tableid.substr( 0, stringpos );
			var stringpos2 = tableid.lastIndexOf( "-" );
			var group_number = tableid.substr( stringpos2 + 1 );
			
			//alert("Slider Section = " + slider_group);
			//alert("Slider Number = " + group_number);
			
			/* The variables bpaz_more and bpaz_fewer are sent to jQuery
			 * from the tiered privacy settings forms and contain the
			 * translatable strings.
			 */ 
			
			if((slider_group == "global") || (slider_group == "group")) {
				if(link_text == bpaz_more) {
					jQuery("th#" + headerid + " p").html("<a href='javascript:;'>" + bpaz_fewer + "</a>");		
					jQuery("table#" + tableid + " select").attr("disabled", true);
					jQuery("table#" + tableid + " td.group-save input").attr("checked", false);
					jQuery("table#" + tableid + " td.group-save input").attr("disabled", true);
					
				} else if(link_text == bpaz_fewer) {
					jQuery("th#" + headerid + " p").html("<a href='javascript:;'>" + bpaz_more + "</a>");
					jQuery("table#" + tableid + " select").attr("disabled", false);
					jQuery("table#" + tableid + " input").attr("disabled", false);
					
					if(slider_group == "global") {
						// Make sure no group save checkboxes are checked
						jQuery("div.privacy_slide_group td.group-save input").attr("checked", false);
					}
				}
			}
			
			if(slider_group == "global") {
				jQuery("div.privacy_slide_group").slideToggle("slow", function() {
    				// Animation complete
	  			});
				
			} else if(slider_group == "group") {
				jQuery("div#group-" + group_number + " div.privacy_slide_single").slideToggle("slow", function() {
    				
    				// Reposition browser window to make sure clicked on container is visible
    				jQuery(window).scrollTop(jQuery("div#group-" + group_number).offset().top - 50);
    				
    				// Animation complete
    			});
				
	  		}
		}
	);
	
});