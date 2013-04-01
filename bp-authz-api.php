<?php
/*********************************************************************************************************
 * BuddyPress Privacy Component API
 *
 * @package BP-Privacy
 * @version 1.0
 * @since 1.0-RC1
 *
 * This is an internal Privacy API that provides a few mechanisms for developers of 3rd-party BuddyPress
 * plugins to extend privacy filtering to objects created by their plugin.
 *
 * At this stage it is a simple file with a few key functions. As such, it is not much of an API. It's
 * more a set of helper functions that can allow a developer to utilize the ACL tables, some of the
 * privacy filtering functions, and more easily create a privacy setting screen for their plugin.
 *
 * BP Privacy uses many of the functions found within this limited API as well. Of course, it also uses
 * key BuddyPress-core specific privacy filtering functions found within bp-authz-core.php. Not all of
 * the functions in this API will be applicable to 3rd-party developers' use at this time.
 *
 * Some of the functions in this simple API are best described as privacy template tags. They are designed
 * to help 3rd-party plugin developers have an easier time creating privacy settings screens and privacy
 * filtering routines.
 *
 * In a future version of BuddyPress, there may be a new core BP API. See these links for more details:
 *
 * http://trac.buddypress.org/wiki/NextGenApi
 * http://api.buddypress.org/home/
 *
 * If and when the BP API becomes a reality, the Privacy API will be refactored, possibly extending various
 * classes of the BP API. It is hoped that this will thus offer developers a more robust, full-featured set
 * of privacy filtering services.
 *********************************************************************************************************/


/**
 * bp_authz_tiered_form_section_visibility_toggle()
 *
 * Auto triggers the jQuery event, after form is done loading, which will expand
 * all groups or single privacy containers on a tiered privacy settings form.
 * This function is only called if data exists in the $expand_container array.
 *
 * What causes this function to be triggered? Two possibilities: for group privacy
 * containers, they are set to visible if there is no current global ACL record but
 * other ACL records do exist for the given user and component; for single privacy
 * containers, they are set to visible using similar logic.
 *
 * This jQuery function that orchestrates this action is the same one that controls
 * the expand and collapse action when a user manually clicks on a "More options..."
 * or "Fewer options..." link.
 *
 * @package BP-Privacy API
 * @param $acl_rec The unique numerical identifier of the "expand-button-" id selector
 * @see privacy.js
 *
 * @version 1.0
 * @since 1.0-RC1
 */
function bp_authz_tiered_form_section_visibility_toggle( $acl_rec ) {

	?>
	<script type='text/javascript'>

		jQuery(document).ready(function() {

			// Pass the unique numerical "expand-button-" id into a javascript variable
			var clicked_acl_container = '<?php echo $acl_rec; ?>';

			// Trigger the jQuery click event associated with the "expand-button-" id selector
			jQuery("th[id^='expand-button-']").triggerHandler("click", [clicked_acl_container]);

		});

	</script>
	<?php
}


//*** The below three functions should be tied into a cron job

/**
 * bp_authz_get_bp_site_users()
 *
 * **** Possibly load at BPAZ int with a check to see if it already exists.
 * If it exists, do not load again; maybe timer function. last update < x,
 * don't load if exists.
 *
 * Pulls the entire list of current users from the wp_users table. Assumes
 * that all users are members of the BuddyPress install. These data are used
 * by the bp_authz_create_privacy_settings_listbox() to populate the list
 * box so that a member can select a list of users to grant viewing rights
 * to a given privacy item.
 *
 * @package BP-Privacy API
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $wpdb WordPress DB access object
 * @see bp_authz_create_privacy_settings_listbox()
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 * @return array $bp_site_users A globally-available array of site's users sorted by display name
 */
function bp_authz_get_bp_site_users() {
	global $wpdb, $bp, $bp_site_users;

	$bp_site_users = "SELECT DISTINCT u.ID as id, u.user_login, u.display_name as name FROM {$wpdb->users} AS u ORDER BY u.display_name ASC";

	if ( $bp_site_users = $wpdb->get_results($bp_site_users, ARRAY_A) ) {
		return $bp_site_users;
	} else {
		return false;
	}
}


/**
 * bp_authz_get_bp_site_groups()
 *
 * **** Possibly load at BPAZ int with a check to see if it already exists.
 * If it exists, do not load again; maybe timer function. last update < x,
 * don't load if exists.
 *
 * Pulls the entire list of current BuddyPress Groups wp_BP_groups table.
 * These data are used by bp_authz_the create_privacy_settings_listbox() to
 * populate the list box so that a member can select a list of groups whose
 * members they grant viewing rights to a given privacy item.
 *
 * @package BP-Privacy API
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $wpdb WordPress DB access object
 * @see bp_authz_create_privacy_settings_listbox()
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 * @return array $bp_site_groups A globally-available array of site's groups sorted by display name
 */
function bp_authz_get_bp_site_groups() {
	global $wpdb, $bp, $bp_site_groups;

	//check to see if group component is activated
	if( !isset( $bp->active_components['groups'] ) ) {
		return false;
	}

	$bp_site_groups = "SELECT DISTINCT g.id, g.name, g.status FROM {$bp->groups->table_name} AS g ORDER BY g.name ASC";

	if ( $bp_site_groups = $wpdb->get_results($bp_site_groups, ARRAY_A) ) {
		return $bp_site_groups;
	} else {
		return false;
	}
}


/**
 * bp_authz_get_users_groups()
 *
 * **** Possibly load at BPAZ int with a check to see if it already exists.
 * If it exists, do not load again; maybe timer function. last update < x,
 * don't load if exists.
 *
 * Pulls the entire list of current BuddyPress Groups to which the user
 * belongs. This is used by bp_authz_create_privacy_settings_listbox() in the
 * foreach loop to filter out any hidden groups that a user is not a member.
 * This prevents hidden groups that a user should not see from being outputted
 * in the groups listbox.
 *
 * @package BP-Privacy API
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $wpdb WordPress DB access object
 * @param $user_id The user ID for which group membership should be searched
 * @see bp_authz_create_privacy_settings_listbox()
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 * @return array $bp_users_groups A globally-available array list all the groups to which user belongs
 */
function bp_authz_get_users_groups( $user_id ) {
	global $wpdb, $bp, $bp_users_groups;

	//check to see if group component is activated
	if( !isset( $bp->active_components['groups'] ) ) {
		return false;
	}

	$bp_users_groups = $wpdb->prepare( "SELECT group_id FROM {$bp->groups->table_name_members} WHERE user_id = %d AND is_confirmed = 1 AND is_banned = 0 ORDER BY group_id ASC", $user_id );

	if ( $bp_users_groups = $wpdb->get_results($bp_users_groups, ARRAY_A) ) {

		/* The get_results method of the $wpdb class creates a
		 * 2-level deep multidimensional array when more than
		 * one row is retrieved. So, need to loop through
		 * results and build a new single-level array.
		 */
		foreach ( $bp_users_groups as $key => $value) {
			if ( is_array ( $value ) ) {
				foreach ( $value as $key2 => $value2) {
					$bp_users_group_membership[] = $value2;
				}
			} else {
				$bp_users_group_membership[] = $value;
			}
		}

		//echo '<br />Test user group array: <br />';
		//print_r($bp_users_group_membership);
		//echo '<br />_______________________<br />';
		return $bp_users_group_membership;
	} else {
		return false;
	}
}


/**
 * bp_authz_create_privacy_settings_listbox()
 *
 * When a user chooses the "Members of These Groups" or "These Users Only"
 * ACL privacy settings option, this function outputs all the html for a
 * multiselect dropdown listbox of either BP users or BP groups.
 *
 * If user or group data already exists in the ACL Lists table, then those data
 * will be used to cross check against the $bp_site_users or $bp_site_groups
 * global arrays. If a match is found, then that/those occurance(s) will be
 * selected in the displayed listbox. If no matches are found, then the listbox
 * will have zero selected rows.
 *
 * Whether a listbox is displayed when a privacy settings form is first rendered
 * depends a number of factors. The sequence of events and underlying code
 * execution is fully explained in the Developer's Guide section of the
 * BuddyPress Privacy Manual in the subsection entitled, "Using AJAX to display
 * Group and User Listboxes".
 *
 * NOTE: You may be thinking that this function violates the separation of business logic
 * from presentation logic--as do many of the BuddyPress settings screens and a number of
 * core WP and BP functions. For the below function at least, this is not a valid argument.
 *
 * Remember, not all CSS is used for theme design. jQuery functions can track events that
 * occur at specificed IDs and Classes. That is exactly the purpose of the selector markup
 * outputted below. It is not intended for use by theme desingers. Touch it, change it, mess
 * with it in anyway, and you could break certain jQuery actions. You have been warned. This
 * is, of course, another sound reason why it can be wise not to include some of the selectors
 * within a form file--to try and isolate themers from messing with crucial selectors that
 * basically have little to nothing to do with design presentation.
 *
 * However, in case you're thinking that the privacy settings forms will be difficult to
 * style, there are plenty of CSS selectors included within the settings form files that
 * theme designers can use to style presentation output. Most of those have a selector
 * markup in the privacy_settings.css file. But in all cases, it is never wise to change
 * the name of or remove any selector. They are all their for a purpose--be that output
 * display or jQuery functionality.
 *
 *
 * @package BP-Privacy API
 * @param array $bp_current_user_group_list The passed in array of currently
 * selected, if any, BP users or groups; grabbed from xx_bp_authz_acl_lists table
 * @param string $list_type Does the listbox show a 'grouplist' or 'userlist'
 * @param integer $bpaz The ACL level associated with privacy item when form loads
 * @param integer $acl_rec The unique CSS ID counter
 * @param integer $single_rec The unique single record counter (if single data)
 * @param boolean $tiered Is privacy settings data from a multi-tiered form (global, group, single) or single
 * @param string $form_level Form level from which data are being sent (global, group, or single)
 * @param integer $group_rec The unique group record counter (if group data)
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $bp_site_users The array holding the list of current BuddyPress users
 * @global $bp_site_groups The array holding the list of current BuddyPress Groups
 * @global $current_user A variable holding the UserID of the user setting privacy selections
 * @global $bp_users_groups The array holding the list of groups user belongs to
 *
 * @version 1.0
 * @since 1.0-RC1
 */
function bp_authz_create_privacy_settings_listbox( $bp_current_user_group_list, $list_type, $bpaz = 0, $acl_rec = 0, $single_rec = 0, $tiered = false, $form_level, $group_rec = 0 ) {
	global $bp, $current_user;

	//*** Activate the below global variables once cron jobs are in place
	//global $bp_site_users, $bp_site_groups, $bp_users_groups;

	// Is listbox function being called via an AJAX request.
	if ( ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' ) {

		/* Passed-in function parameters are coming from an AJAX request.
		 * Convert lists array from JSON format back into object format.
		 * Before doing that, we need to remove any slashes that jQuery
		 * added to data as it was formatting JSON string to pass.
		 */
		$bp_current_user_group_list = stripslashes($bp_current_user_group_list);

		$bp_current_user_group_list = json_decode( $bp_current_user_group_list, true );
	}

	/* Parse the passed in multidimensional $bp_current_user_group_list array, extracting just
	 * the values from each user_group_id key of either the 'grouplist' or the 'userlist' array
	 * element. The $list_type variable determines which of these first-level array elements to use.
	 */
	foreach ( (array)$bp_current_user_group_list[$list_type] as $key => $value ) {
		$selected_user_group_list[] = $value;
	}

	if ( empty( $selected_user_group_list ) ) {
		$selected_user_group_list = array();
	}

	/*
	foreach ( (array)$bp_current_user_group_list[$list_type] as $key => $value ) {
		$selected_user_group_list[] = $value['user_group_id'];
	}
	*/

	//*** Below will need to be refactored, except for check to see if Group component is active, after cron jobs are in place
	if ( $list_type == 'grouplist' ) {

		/* Check to see if group component is activated. If not, then a group
		 * listbox should not be outputted.
		 */
		if( !isset( $bp->active_components['groups'] ) ) {
			return false;
		}

		$bp_site_user_group_list = bp_authz_get_bp_site_groups();

		//*** To be used once bp_authz_get_bp_site_groups() function is controlled by cron job
		//$bp_site_user_group_list = $bp_site_groups;

		// If there are no groups, then throw message and end execution.
		if ( empty( $bp_site_user_group_list ) ) {
			echo "<div id='acl_warning'><p>" . __( 'Our network does not have any groups yet. Be the first to create one!', BP_AUTHZ_PLUGIN_NAME ) . "</p></div>";
			return false;
		} else {
			//*** Below should be deleted once global $bp_users_groups variable is in use via bp_authz_get_users_groups() function being controlled by cron job
			$bp_users_groups = bp_authz_get_users_groups( $current_user->ID );
		}

	} elseif ( $list_type == 'userlist' ) {

		$bp_site_user_group_list = bp_authz_get_bp_site_users();

		//*** To be used once bp_authz_get_bp_site_users() function is controlled by cron job
		//$bp_site_user_group_list = $bp_site_users;
	} else {
		return false;
	}
	//*** Above will need to be refactored once cron jobs are set up and globals $bp_site_groups, $bp_users_groups, and $bp_site_users are in place


	// Request coming from a simple privacy settings form with single fields.
	if ( $tiered != true) {

		$div_id = "single-acl-{$acl_rec}-" . $list_type;
		$bpaz_post_name_segment = "bp-authz[singles][single-{$single_rec}]";

	// Request coming from a tiered privacy settings form.
	} else {

		switch ($form_level) {
			case 'global':
				$div_id = 'global-acl-' . $list_type;
				$bpaz_post_name_segment = 'bp-authz[global]';
				break;
			case 'group':
				$div_id = "group-acl-{$acl_rec}-" . $list_type;
				$bpaz_post_name_segment = "bp-authz[groups][group-{$group_rec}]";
				break;
			case 'single':
				$div_id = "single-acl-{$acl_rec}-" . $list_type;
				$bpaz_post_name_segment = "bp-authz[groups][group-{$group_rec}][singles][single-{$single_rec}]";
				break;
		}
	}

	// Now begin building the group or user listbox html string for outputting
	//$listbox_html = "<div id='{$div_id}' class='{$usergroup_class}'>";
	$listbox_html = "<div id='{$div_id}'>";

	/* Along with the 'multiple' attribute of the select tag, the [] in the name attribute
	 * is essential for allowing multiple selections to be captured into an array.
	 */
	$listbox_html .= "<select name='{$bpaz_post_name_segment}[{$list_type}][]' size='7' multiple='multiple'>";

	//***
	/* Will need to loop through the global variable $bp_site_users
	 * and $bp_site_groups instead of $bp_site_user_group_list once cron
	 * jobs are in place
	 */

	// Output the listbox with any previously-selected items highlighted
	foreach ( $bp_site_user_group_list as $privacykey => $value ) {

		$user_group_list_id = $value['id'];
		$user_group_list_name = $value['name'];

		/* Filter out the current user from the list as it does not
		 * make sense to have their own name in the userlist from
		 * which they will be picking users.
		 */

		if ( $user_group_list_id == $current_user->ID ) {
			// Skip to next array element
			continue;
		}

		if ( $list_type == 'grouplist' ) {
			$user_group_list_status = $value['status'];
		}

		/* Should the current user see the group? If it is hidden and they
		 * are not a member, no. Otherwise, member or not, any logged in
		 * user can see the existence of a public or private group.
		 */

		//***
		/* Place the "is user a member of group" check into separate function
		 * with cron job; grab all user's group memberships and place results
		 * into an array.
		 */
		//*** Use $bp_users_groups instead of check member method call
		if ( $list_type == 'grouplist' ) {
			if ( $user_group_list_status == 'hidden' ) {

				/* User is not a member of the hidden group. Do not output this
				 * array element. Continue (skip) to next key/value pair.
				 */
				if ( !in_array( $user_group_list_id, $bp_users_groups ) ) {
					continue;
				}
			}
		}

		/* Determine which list items should be highlighted, indicating the Groups
		 * or Users that have been previously selected (chosen) by the user.
		 */
		if ( in_array( $user_group_list_id, (array)$selected_user_group_list ) ) {

			//***
			//echo 'Item is IN array.<br />';

			$selected = 'selected="selected"';
		} else {

			//***
			//echo 'ID number ' . $user_group_list_id . ' NOT in array.<br />';

			$selected = '';
		};

		$listbox_html .= "<option {$selected} value='{$user_group_list_id}'>{$user_group_list_name}</option>";
	}
	unset($bp_site_user_group_list);

	$listbox_html .= '</select>';

	/**
	 * Future Version Consideration:
	 *
	 * In keeping with WordPress' goal of minimizing user decisions, BP Privacy currently saves all existing
	 * group and user list records no matter the BPAz level. The exception is BPAz = 0 (All Users). When a user
	 * sets their BPAz level back to zero, then any existing group and/or user lists are purged from the lists table.
	 *
	 * These lines of code were put in for possible future functionality. The idea is to allow users to indicate
	 * whether lists should be saved no matter what the currently-set BPAz level.
	 *
	 * This new functionality would allow users to indicate which lists are kept no matter what the BPAz setting.
	 * To enable this alternate way of handling list data, the logic in bp_authz_process_privacy_settings_array_element()
	 * would have to be altered to allow for the processing of this additional data.
	 */
	/*
	$keep_list = __( 'Keep List?', BP_AUTHZ_PLUGIN_NAME );
	$listbox_html .= "<p>{$keep_list}";
	$listbox_html .= "<input type='checkbox' name='{$bpaz_post_name_segment}[keep_{$list_type}]' checked='yes' value='yes' />";
	$listbox_html .= '</p>';
	*/

	$listbox_html .= '</div>';

	//echo $listbox_html;

	return $listbox_html;
}


/**
 * bp_authz_process_privacy_settings_array_element()
 *
 * Save the processed privacy settings form data into the ACL tables.
 *
 * Data is passed from bp_authz_process_privacy_settings().
 * The array element family tree--the nested parental elements of the
 * current associative array--depends upon the type of privacy settings form
 * being processed.
 *
 * If array data are sent from a tiered privacy settings form, then the array
 * element family tree will be one of the following:
 *
 * ['global']
 * ['groups']["group-{$i}"]
 * ['groups']["group-{$i}"]['singles']["single-{$j}"]
 *
 * If array data are sent from a single privacy settings form, then the array
 * element family tree will be:
 *
 * ['singles']["single-{$j}"]
 *
 * So, using the ID field as an example in all three instances, the passed in
 * array will in essence be set to prepopulate the ACL table fields as if the
 * following notation was used:
 *
 * For Global: $id = $privacy_post_array['global']['id'];
 * For Group: $id = $privacy_post_array['groups']["group-{$i}"]['id'];
 * For Single: $id = $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['id'];
 *
 * But all that is necessary to prepopulate the fields for saving to the ACL tables
 * is the following (assuming data are for a global-level item):
 *
 * $id = $privacy_post_array_element['id'];
 * $filtered_component = $privacy_post_array_element['filtered_component'];
 * $filtered_item = $privacy_post_array_element['filtered_item'];
 * $item_id = $privacy_post_array_element['item_id'];
 * $group_user_list_old = $privacy_post_array_element['group_user_list_old'];
 * $bpaz_level = $privacy_post_array_element['acl'];
 * $acl_group_list = $privacy_post_array_element['grouplist'];
 * $acl_user_list = $privacy_post_array_element['userlist'];
 *
 * In essence, using this construct allows for an easier assignment of the current
 * array element's key => value pairs into field variables as the multi-level element
 * tree is not actually required to be known.
 *
 * @package BP-Privacy API
 * @see bp_authz_process_privacy_settings()
 * @param array $privacy_post_array_element An associative array containing current element key => value pairs
 * @uses bp_authz_save_user_acl_array_main()
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 * @return boolean True if ACL record created/updated.
 */
function bp_authz_process_privacy_settings_array_element( $privacy_post_array_element ) {
	global $bp, $current_user;

	$id = $privacy_post_array_element['id'];
	$filtered_component = $privacy_post_array_element['filtered_component'];
	$filtered_item = $privacy_post_array_element['filtered_item'];
	$item_id = $privacy_post_array_element['item_id'];
	$old_lists_array = $privacy_post_array_element['group_user_list_old'];
	$bpaz_level = $privacy_post_array_element['acl'];
	$acl_group_list = ! empty( $privacy_post_array_element['grouplist'] ) ? $privacy_post_array_element['grouplist'] : array();
	$acl_user_list = ! empty( $privacy_post_array_element['userlist'] ) ? $privacy_post_array_element['userlist'] : array();

	//***
	/*
	echo '<br />*******Pre-Save Processing: Current Post Array Element:<br />';
	print_r($privacy_post_array_element);
	echo '<br />';
	*/

	//***
	/*
	$bp_current_user_group_list = json_decode( $old_lists_array, true );
	echo '<br />===> Old Lists Array Element:<br />';
	print_r($bp_current_user_group_list);
	echo '<br />';
	*/

	/* Create a nested array which will hold the passed-in group
	 * and user list arrays--if any
	 */
	$group_user_list_id_array = array();

	if ( !empty ( $acl_group_list ) ) {
		$group_user_list_id_array['group_list'] = $acl_group_list;
	}

	if ( !empty ( $acl_user_list ) ) {
		$group_user_list_id_array['user_list'] = $acl_user_list;
	}

	/* Save data to the ACL table(s); the saving of any list data will be
	 * triggered within the save() method of the BP_Authz_ACL_Main class.
	 */
	$acl_main_record = bp_authz_save_user_acl_array_main( $id, $current_user->ID, $filtered_component, $filtered_item, $item_id, $old_lists_array, $bpaz_level, $group_user_list_id_array );

	if ( $acl_main_record != true ) {
		return false;
	}

	return true;

}

/**
 * bp_authz_process_privacy_settings()
 *
 * Processes the privacy settings screen form data gathered by the $_POST
 * function, preparing it for ACL table CRUD operations (not retrieval).
 *
 * Data from each privacy settings screen form is stored in the bp_authz
 * $_POST array. This array is a multidimensional array whose stored data
 * format depends on the type of privacy settings form being used: tiered
 * or single.
 *
 * Tiered privacy forms provide a multi-level privacy settings view that
 * allows users to set a given ACL setting globally, by group, or by
 * individual item (single). Single privacy forms contain the data for
 * a simple list of individual privacy items.
 *
 * See the following in the Developer's Guide section of the BuddyPress
 * Privacy Manual for more details:
 *
 * 	- Tiered Privacy Form Data Array Structure and Array Element Levels
 *
 * @package BP-Privacy API
 * @see ???
 * @param array $privacy_post_array A nested associative array containing the gathered privacy settings data
 * @param boolean $tiered Is privacy settings data from a multi-tiered form (global, group, single) or single
 * @uses bp_authz_process_privacy_settings_array_element()
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 * @return boolean $bp_privacy_updated Default is false. True if ACL record updated (created/resaved/deleted).

 */
function bp_authz_process_privacy_settings( $privacy_post_array, $tiered = false ) {
	global $bp, $bp_privacy_updated;

	// Initialize variable
	$bp_privacy_updated = true;

	// Request coming from a simple privacy settings form with single fields
	if ( $tiered != true ) { // Begin if/else Primary; start "A" section

		$count_singles = count( $privacy_post_array['singles'] );

		//***
		/*
		echo '<br />Processing Simple Form data...<br />';
		echo "Array has $count_singles elements.<br />";
		*/
		//***

		for( $k = 1; $k <= $count_singles; $k++ ) { // Begin for loop 1A

			// ACL > 0, create or update record
			if ( $privacy_post_array['singles']["single-{$k}"]['acl'] != 0 ) { // Begin if/else 1A

				//***
				//echo "<br />Saving single record {$k}.<br />";

				/* Pass current array element key => value pairs to prepopulate
				 * field variables used to save or update ACL record
				 */
				$privacy_post_array_element = $privacy_post_array['singles']["single-{$k}"];

				$bp_privacy_updated = bp_authz_process_privacy_settings_array_element( $privacy_post_array_element );

			// ACL = 0, delete record in main table if one exists and any related records in lists table
			} else { // if/else 1A con't

				$single_id = $privacy_post_array['singles']["single-{$k}"]['item_id'];

				//***
				//echo "<br />Single record {$k} ( field ID = $single_id ) has ACL set to 0. Do not save.<br />";

				// Delete Individual ACL Record if one exists
				if ( !empty( $privacy_post_array['singles']["single-{$k}"]['id'] ) ) {

					//***
					//echo "<br />Single record {$k} ( field ID = $single_id ) exists and ACL set to 0. Record deleted.<br />";

					$acl_record_to_delete = $privacy_post_array['singles']["single-{$k}"]['id'];

					$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
				}

			} // End if/else 1A

			if ( $bp_privacy_updated == false ) {
				//***
				//echo "<br />NOTICE: Error Processing Simple Form Data => Single record {$k} ( field ID = $single_id )<br />";

				// There was an error. End $_Post array processing.
				return false;
			}

		} // End for loop 1A

		return $bp_privacy_updated;

	// request coming from a tiered privacy settings form
	} else { // if/else Primary con't; start "B" section

		//***
		//echo '<br />Processing Tiered Form data...<br />';

		/* Initialize flags used to help determine if global or
		 * group acl should be applied to single privacy fields
		 * and global and group post array variables.
		 */
		$use_global_acl = false;
		$use_group_acl = false;
		$global_privacy_post_array_element = '';
		$group_privacy_post_array_element = '';

		// Begin foreach loop 1
		foreach ( $privacy_post_array as $privacykey => $firstvalue ) { // Begin foreach 1B

			// The first element array stores any global ACL settings
			if ( $privacykey == 'global' ) { // Begin if/else 1B

				//***
				//echo "Processing Global Array...<br />";

				/* Check to see if global privacy settings checkbox is marked
				 * for saving. If not, skip to the group array elements. But
				 * first check to see if a ACL global record currently exists
				 * for component. If so, delete.
				 */
				if ( ! isset( $privacy_post_array['global']['save_global'] ) ) { // Begin if/else 2B

					//***
					//echo 'Do not save global record. Checking if need to delete existing global record...<br />';

					/* A global privacy ACL record exists; must delete
					 * it before continuing on to group array elements.
					 */
					if ( !empty( $privacy_post_array['global']['id'] ) ) {

						//***
						//echo 'Global record to be deleted.<br />';

						$acl_record_to_delete = $privacy_post_array['global']['id'];

						$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
					}

				// Global ACL to be saved and also applied to all single privacy fields
				} else { // if/else 2B con't

					$use_global_acl = true;

					/* Pass current array element key => value pairs to prepopulate
					 * field variables that will be saved to an ACL record
					 */
					$privacy_post_array_element = $privacy_post_array['global'];

					// Set current global array element to be used in all single fields
					$global_privacy_post_array_element = $privacy_post_array_element;

					/* If ACL = 0, this is a special case where user is indicating that
					 * they want to "zero out" their ACL settings across the board for
					 * the given multi-tiered privacy settings form. Any existing ACL
					 * records for this component will be reset to default by being
					 * deleted from the table. This, in essence, resets all ACL values
					 * to zero. See "Resetting ACL Levels on Tiered Privacy Forms" in
					 * the User's Guide section of the BuddyPress Privacy Manual.
					 */
					if ( $privacy_post_array['global']['acl'] == 0 ) {

						/* A global privacy ACL record exists; must delete
						 * it before continuing on to group array elements.
						 */
						if ( !empty( $privacy_post_array['global']['id'] ) ) {

							//***
							//echo 'Zeroing out: Global record to be deleted.<br />';

							// Global record exists. Delete it.
							$acl_record_to_delete = $privacy_post_array['global']['id'];

							$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
						}

						//***
						//echo 'Zeroing out all ACL values.<br />';

					// Global ACL > 0, there's something to save
					} else {

						//***
						//echo 'Global record to be saved.<br />';

						$bp_privacy_updated = bp_authz_process_privacy_settings_array_element( $privacy_post_array_element );

					}

				} // End if/else 2B

				if ( $bp_privacy_updated == false ) {
					//***
					//echo '<br />NOTICE: Error Processing Tiered Form => Global Tiered Form data!<br />';

					// There was an error. End $_Post array processing.
					return false;
				}

			/* The second array element stores any group ACL settings.
			 * It is a nested associative array called ['groups'] that contains
			 * arrays for each field group ['group-##']. In turn, each of these
			 * field group arrays is a nested associative array that contains a
			 * child array called ['singles']. This child array contains all the
			 * individual privacy fields that are associated with a given privacy
			 * field group. The ACL settings for individual privacy items are
			 * stored in each ['singles-##'] array.
			 */
			} elseif ( $privacykey == 'groups' ) { // if/else 1B con't

				/* Loop through the ['groups'] array. If Global save is in effect, then
				 * loop through group data only to access each group's ['singles'] element
				 * data. If Global save is not in effect, then group data might need saving.
				 */

				// Loop through the ['groups'] associative array
				$count_groups = count( $privacy_post_array['groups'] );

				for( $i = 1; $i <= $count_groups; $i++ ) { // Begin for loop 1B

			 		// Reinitialize for next pass
			 		$use_group_acl = false;
			 		$group_privacy_post_array_element = "";

					//***
					/*
					echo '<br />________________________<br />';
					echo "Processing group record {$i}.<br />";
					*/

					/* Check to see if group privacy settings checkbox is marked for
					 * saving to all single fields within this group or if global ACL
					 * record should be applied. If neither of these conditions apply,
					 * then skip to the single array elements associated with this
					 * group array. However, first check to see if a ACL group record
					 * currently exists for this group for this component. If so, delete it.
					 */
					if ( ! isset( $privacy_post_array['groups']["group-{$i}"]['save_group'] ) ) { // Begin if/else 3B

						//***
						//echo 'Do not save group record. Checking if need to delete existing group record...<br />';

						/* A group privacy ACL record exists; must delete
						 * it before continuing on to single array elements.
						 */
						if ( !empty( $privacy_post_array['groups']["group-{$i}"]['id'] ) ) {

							//***
							//echo "Group record {$i} to be deleted.<br />";

							$acl_record_to_delete = $privacy_post_array['groups']["group-{$i}"]['id'];

							$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
						}

					/* Group ACL settings to be saved and also applied to all single privacy
					 * fields within this group.
					 */
					} else { // if/else 3B con't

						$use_group_acl = true;

						/* Pass current array element key => value pairs to prepopulate
						 * field variables that will be saved to an ACL record
						 */
						$privacy_post_array_element = $privacy_post_array['groups']["group-{$i}"];

						//***
						/*
						echo '<br />Group Array output:<br />';
						print_r($privacy_post_array_element);
						echo '<br />';
						*/

						// Set current group array element to be used below in the group's single fields
						$group_privacy_post_array_element = $privacy_post_array_element;

						/* If ACL = 0, this is a special case where user is indicating that
						 * they want to "zero out" their ACL settings across the group for
						 * the given multi-tiered privacy settings form. Any existing ACL
						 * records for this component within this group will be reset to
						 * default by being deleted from the table. This, in essence, resets
						 * all ACL values within the group to zero. See "Resetting ACL Levels
						 * on Tiered Privacy Forms" in the User's Guide section of the BuddyPress
						 * Privacy Manual.
						 */
						if ( $privacy_post_array['groups']["group-{$i}"]['acl'] == 0 ) {

							/* A global privacy ACL record exists; must delete
							 * it before continuing on to group array elements.
							 */
							if ( !empty( $privacy_post_array['groups']["group-{$i}"]['id'] ) ) {

								//***
								//echo "Zeroing: Group record {$i} to be deleted.<br />";

								// Group record exists. Delete it.
								$acl_record_to_delete = $privacy_post_array['groups']["group-{$i}"]['id'];

								$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
							}

							//***
							//echo 'Zeroing out Group ACL values.<br />';

						// Group ACL > 0, there's something to save
						} else {

							//echo "Group record {$i} to be saved!<br />";

							$bp_privacy_updated = bp_authz_process_privacy_settings_array_element( $privacy_post_array_element );
						}

					} // End if/else 3B

					if ( $bp_privacy_updated == false ) {
						//***
						$group_id = $privacy_post_array['groups']["group-{$i}"]['item_id'];
						//echo "<br />NOTICE: Error Processing Tiered Form => Group {$i}, Single record {$j} (Group ID = $group_id)<br />";

						// There was an error. End $_Post array processing.
						return false;
					}

					/* Loop through the ['singles'] associative array for current group
					 *
					 * Whether Global or Group save is in effect, we must always process ['singles'] element data.
					 * See the subsection entitled "Tiered Privacy Form Data Array Structure and Array Element Levels"
					 * in the Developer's Guide section of the BuddyPress Privacy Manual for more details.
					 */

					//***
					//echo "<br />Processing singles data for Group record {$i}...<br />";

					$count_singles = count( $privacy_post_array['groups']["group-{$i}"]['singles'] );

					//***
					//echo "Group {$i} has $count_singles elements.<br />";

					for( $j = 1; $j <= $count_singles; $j++ ) { // Begin for loop 2B

						// Store select Global ACL settings in current single's fields
						if ( $use_global_acl == true ) {

							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['acl'] = $global_privacy_post_array_element['acl'];
							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['grouplist'] = ! empty( $global_privacy_post_array_element['grouplist'] ) ? $global_privacy_post_array_element['grouplist'] : array();
							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['userlist'] = ! empty( $global_privacy_post_array_element['userlist'] ) ? $global_privacy_post_array_element['userlist'] : array();

						// Store select Group ACL settings in current single's fields
						} elseif ( $use_group_acl == true ) {

							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['acl'] = $group_privacy_post_array_element['acl'];
							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['grouplist'] = $group_privacy_post_array_element['grouplist'];
							$privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['userlist'] = $group_privacy_post_array_element['userlist'];
						}

						//***
						//echo '<br />Single ACL value = ' . $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['acl'] . '<br />';

						// ACL > 0, create or update record
						if ( $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['acl'] != 0 ) { // Begin if/else 4B

							//***
							//echo "<br />Saving single record {$j}.<br />";

							/* Pass current array element key => value pairs to prepopulate
							 * field variables that will be saved to an ACL record. Some of
							 * the data in the single array element might have been changed
							 * above if Global or Group save is in effect.
							 */
							$privacy_post_array_element = $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"];

							$bp_privacy_updated = bp_authz_process_privacy_settings_array_element( $privacy_post_array_element );

						// ACL = 0, delete record if one exists
						} else { // if/else 4B con't

							$single_id = $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['item_id'];

							//***
							//echo "<br />Single record {$j} ( field ID = $single_id ) has ACL set to 0. Do not save.<br />";

							// Delete Individual ACL Record if exists
							if ( !empty( $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['id'] ) ) {

								//***
								//echo "<br />NOTE: Single record {$j} ( field ID = $single_id ) is being deleted...<br />";

								$acl_record_to_delete = $privacy_post_array['groups']["group-{$i}"]['singles']["single-{$j}"]['id'];

								//***
								//echo "<br />Record ID to delete: {$acl_record_to_delete}.<br />";

								$bp_privacy_updated = bp_authz_delete_user_acl_record( $acl_record_to_delete );
							}

						} // End if/else 4B

						if ( $bp_privacy_updated == false ) {
							//***
							//echo "<br />NOTICE: Error Processing Tiered Form => Group {$i}, Single record {$j} (field ID = $single_id)<br />";

							// There was an error. End $_Post array processing.
							return false;
						}

					} // End for loop 2B; the singles array loop

					//***
					//echo "<br /><-----------END OF LOOP FOR GROUP {$i}-----------><br />";

				} // End for loop 1B; the groups array loop
			} // End if/else 1B; the tiered $_POST array processing section
		} // End foreach loop 1B

		//***
		//echo "<br /><-----------END OF POST ARRAY PROCESSING-----------><br />";

		return $bp_privacy_updated;

	} // End if/else Primary

}


/**
 * bp_authz_output_bpaz_select()
 *
 * Outputs the ACL-settings list dropdown box for users to select which ACL
 * setting they want to apply to a given privacy item. Called by each
 * bp_authz_screen_x_privacy_content() function call in the various
 * bp-authz-x-settings.php files.
 *
 * @version 1.0
 * @since 0.4
 *
 * @return boolean $acl_inactive Default is false. True if selected ACL is inactive.
 */
function bp_authz_output_bpaz_select( $acl_selected ) {
	global $bp;

	$acl_inactive = false;

	$privacy_levels = $bp->authz->bpaz_acl_levels;

	// NOTE: Select tags wrapping this dropdown box are unique and are found within each settings form

	foreach( $privacy_levels as $key => $value ) {

		// array( 'level' => 0, 'enabled' => 0 )
		foreach( $value as $key2 => $value2 ) {

			if( $key2 == 'level' ) {
				$acl_level = $value2;

				if ( $acl_level == $acl_selected ) {
					$selected = 'selected="selected"';
				} else {
					$selected = '';
				};

			} else {
				if( $value2 == 0 ) /* 'disabled' element */ {
					echo "<option {$selected} disabled='disabled' value='$acl_level'>{$key}</option>";
				} else {
					echo "<option {$selected} value='$acl_level'>{$key}</option>";
				};

				/* Put up error message as user has selected an
				 * ACL level that is no longer valid. Site Admin
				 * has deactivated it.
				 */
				if( !empty( $selected ) && $value2 == 0 ) {
					$acl_inactive = true;
				}
			}
		}
	}

	return $acl_inactive;
}


/**
 * bp_privacy_site_lockdown()
 *
 * @version 1.0
 * @since 0.4
 */
function bp_privacy_site_lockdown () {
	global $bp, $bp_authz_lockdown, $bp_authz_redirect_page;

	/* If user navigated to registration page, member activation page, or the privacy-policy page, then let them through */
	if( bp_is_register_page() || bp_is_activation_page() || BP_AUTHZ_PRIVACY_POLICY_SLUG == $bp->current_component )
		return;

	// Possible slugs: BP_AUTHZ_MAINTENANCE_SLUG || BP_AUTHZ_CUSTOM_HOME_SLUG || BP_REGISTER_SLUG
	/* Otherwise, restrict access based on Admin settings and type of user */
	if( ( $bp_authz_lockdown == 'logged_in' && !is_user_logged_in() ) or ( $bp_authz_lockdown == 'maintenance' && !is_super_admin() ) ) {

		// Redirect to desired page
		if( $bp_authz_redirect_page != $bp->current_component ) {
			bp_core_redirect( $bp->root_domain . '/' . $bp_authz_redirect_page . '/' );
		}
	}

	// Site is either open, user is logged in, or user is Site Admin
	return;
}
add_action( 'wp', 'bp_privacy_site_lockdown', 2 );


/**
 * bp_privacy_accept()
 *
 * Displays the "Accept Privacy Settings" checkbox if
 * Site Admin has activated the privacy acceptance TOS.
 *
 * @version 1.0
 * @since 0.4
 */
function bp_privacy_accept() {
	global $bp_authz_settings;

	/* Check to see if Site Admin has activated the privacy acceptance TOS; if
	 * so, modify registration screen by including privacy acceptance checkbox
	 */
	if ( $bp_authz_settings[ 'privacy_tos' ] ) {

		do_action( 'bp_privacy_before_accept_field' );

		$privacy_acceptance_message = sprintf( __( ' To register on this site, you must check this box to indicate your acceptance of our <a href="%1s" title="Accept Privacy Policy">privacy policy</a>.', BP_AUTHZ_PLUGIN_NAME ), site_url( BP_AUTHZ_PRIVACY_POLICY_SLUG . '/' ) );

		// You can customize the privacy acceptance text by adding a filter function
		$privacy_acceptance_message = apply_filters( 'bp_authz_privacy_acceptance_message', $privacy_acceptance_message ); ?>

			<div class="register-section" id="basic-details-section">

				<h4><?php _e( 'Accept Privacy Settings', BP_AUTHZ_PLUGIN_NAME ) ?></h4>
				<?php do_action( 'bp_signup_accept_privacy_errors' ); ?>

				<p><input type="checkbox" name="signup_accept_privacy" id="accept_privacy" value="0" /><?php echo $privacy_acceptance_message; ?></p>

			</div>

		<?php

		do_action( 'bp_privacy_after_accept_field' );

	} // endif
}
add_action( 'bp_after_signup_profile_fields', 'bp_privacy_accept' );


/**
 * bp_privacy_validate_tos()
 *
 * Checks to make sure that the "Accept Privacy Settings"
 * checkbox has been checked. If not, displays a message.
 *
 * @version 1.0
 * @since 0.4
 */
function bp_privacy_validate_tos() {
	global $bp, $bp_authz_settings;

	if ( BP_AUTHZ_DISABLED == 0 && $bp_authz_settings[ 'privacy_tos' ] ) {

		if ( empty( $_POST[ 'signup_accept_privacy' ] ) )
			$bp->signup->errors[ 'signup_accept_privacy' ] = __( 'To complete registration, you must accept our privacy policy.', BP_AUTHZ_PLUGIN_NAME );
	}

}
add_action( 'bp_signup_validate', 'bp_privacy_validate_tos' );


/**
 * bp_privacy_special_screens()
 *
 * Intercepts a URL that references the privacy-policy slug,
 * the custom homepage slug, or the maintenance slug. It then
 * calls bp_core_load_template to load the proper template
 * file for display. See the BuddyPress Privacy Manual for
 * more details.
 *
 * @see bp_core_load_template()
 *
 * @version 1.1
 * @since 0.4
 */

function bp_privacy_special_screens() {
	global $bp;

	if( $bp->current_component == BP_AUTHZ_PRIVACY_POLICY_SLUG ) {
		bp_core_load_template( 'privacy/privacy-policy' );
	} elseif ( $bp->current_component == BP_AUTHZ_CUSTOM_HOME_SLUG ) {
		bp_core_load_template( 'privacy/welcome' );
	} elseif ( $bp->current_component == BP_AUTHZ_MAINTENANCE_SLUG ) {
		bp_core_load_template( 'privacy/maintenance' );
	} else {
		return false;
	}

}
add_action( 'wp', 'bp_privacy_special_screens', 3 );


/* Add a function to only show BP contents to logged in users, all other users
 * get redirected to the main screen or registration page. See these BP forum
 * threads for details:
 * http://buddypress.org/forums/topic/security-all-site-data-visible-to-members-and-non-members-alike
 * http://buddypress.org/forums/topic/how-to-make-a-private-community
 *
 * Also include a radio button group that offers these options:
 *
 * 1. Open to all (anyone can see the network)
 * 2. Only logged in users can access network, all others get redirected to register screen ot splash screen
 * 3. Maintenance mode: only Site Admins can access network, all others get redirected to maintenance screen
 */



/**
 * bp_privacy_filtering_active()
 *
 * Runs a check to see if the passed-in privacy filtering group is active.
 *
 * @param string $privacy_group
 * @return boolean
 *
 * @version 1.0
 * @since 0.4
 */
function bp_privacy_filtering_active( $privacy_group ) {
	global $bp_authz_settings;

	if ( $bp_authz_settings[ 'privacy_filtering' ][ $privacy_group ] == 1 ) {
		return true;
	}

	return false;
}


/**********************************************************************
 * These functions are not currently used. Implement in future version.
 **********************************************************************/

/**
 * bp_privacy_register_component()
 *
 * Allows other plugins to be recognized by the Privacy Component.
 * This is the first step in extending a plugin to offer privacy
 * filtering.
 *
 * Registering your plugin with the Privacy Component will provide
 * your plugin with a submenu grouping under a user's main Privacy
 * settings menu. It will also add your plugin to the list of plugins
 * that offer privacy filtering so that the Site Admin can enable or
 * disable privacy filtering of your plugin via the backend admin
 * privacy settings menu.
 *
 * @param string $component_id
 * @param string $key
 * @param string $value
 * @return boolean
 *
 * @version 1.0
 * @since 0.4
 */
function bp_privacy_register_component( $component_id, $key, $value ) {
	global $bp, $bp_authz_registered_components;

	if ( empty( $component_id ) || empty( $key ) || empty( $value ) )
		return false;

	$bp->authz->registered_components->{$component_id}->{$key} = array(
		'key' => $key,
		'value' => $value
	);

	$bp_authz_registered_components = array( $bp->authz->registered_components );
}


/**
 * bp_privacy_get_action()
 *
 * @param string $component_id
 * @param string $key
 *
 * @version x.x
 * @since x.x
 */
function bp_privacy_get_action( $component_id, $key ) {
	global $bp;

	if ( empty( $component_id ) || empty( $key ) )
		return false;

	return apply_filters( 'bp_privacy_get_action', $bp->authz->registered_components->{$component_id}->{$key}, $component_id, $key );
}

/**
 * bp_privacy_block_feeds()
 *
 * @param boolean $bp_authz_remove_feeds
 *
 * @version x.x
 * @since x.x
 */
function bp_privacy_block_feeds() {

	/* Temporary hardcoding of boolean to stop function; this should be
	 * passed in as a parameter. Site Admin should have an option to
	 * set whether RSS feeds are private or not. This could later be
	 * extended to offer individuals the right to set whether or not
	 * their content is exposed via RSS feeds.
	 */
	$bp_authz_remove_feeds = false;

	if( $bp_authz_remove_feeds == true ) {
		// remove all feeds
		remove_action( 'wp', 'bp_activity_action_sitewide_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_personal_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_friends_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_my_groups_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_mentions_feed', 3 );
		remove_action( 'wp', 'bp_activity_action_favorites_feed', 3 );
		remove_action( 'wp', 'groups_action_group_feed', 3 );
	}
}
//add_action( 'bp_authz_init', 'bp_privacy_block_feeds' );

?>