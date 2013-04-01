<?php
/**
 * Privacy Filtering Functions
 *
 * @package BP-Privacy
 * @version 1.3
 * @since 0.01
 */

 /*********************************************************************************
 * Privacy Filtering Functions
 *
 * The functions below grant or deny access to a user's core BuddyPress component
 * items by determing whether or not the viewing user has sufficient rights to
 * access a given item. The privacy filtering functions use the user's
 * BPAz ACL recordset, if any.
 *
 ********************************************************************************/

/**
 * bp_authz_filter_activity_by_acl()
 *
 * Filters a user's activity stream based on their privacy settings
 */
function bp_authz_filter_activity_by_acl( $has_activities, $activities_template ) {
	global $bp;

	$fields_removed = array();

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */

	if ( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_ACTIVITY_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		$filtered_activities_template = $activities_template;

	} else {

		// Future Version

			/* Offer privacy filtering by activity subnavigation menuing. In essence, a user could select to have certain submenu activity
			 * items filtered from viewing. This will require adding to the activity privacy settings screen to create the
			 * special activity subnavigation menu selections.
			 *
			 * This requires more thought...Perhaps it is overkill, not needed.
			 *
			 * Need to determine whether user has requested an entire Activity Cateogry to be filtered (i.e. not visible to certain users)
			 * or if select, individual activity groups (items) are to be filtered
			 *
			 * Setting the variable $has_activities = false will in effect hide the entire activity stream
			 *
			 * $has_activities = false;
			 * return $has_activities;
			 *
			 * Test for Scope using $bp->current_action to determine current activity search
			 * If there is no "Scope" listed, then that is the sitewide-activity stream
			 * Scope = just-me
			 * Scope = friends
			 * Scope = groups
			 * Scope = favorites
			 * Scope = mentions
			 *
			 * So, if user has set a privacy filtering on their Friends activity stream listing, when this function is triggered, it
			 * will check to see if the Scope = friends. If so, then it will check to see if the viewing user has the rights to see
			 * the content. If not, then $has_activities will be set to false and returned, thereby preventing the listing of that
			 * given user's friends' activity listing. NOTE: Perhaps this should automatically be done for any user who has set friends
			 * privacy filtering.
			 */

		// End Future Version

		// Set an array object variable that is used to rebuild the overall BP_Activity_Template Object
		$filtered_activities_template = $activities_template;

		$component = "activity";

		$filtered_item = "activity_field";

		foreach ( $activities_template as $key => $value ) { // Begin foreach 1A

			if ( $key == 'activities' ) { // Begin if 1A

				// Set an array object variable that is used to rebuild the activities object array
				$filtered_activities_array = $value;

				$count = count( $value );

				for( $i = 0; $i < $count; $i++ ) { // Begin for 1A

					$filter_user_content = false;
					$rekey_array = false;

					foreach ( $value[$i] as $nextkey => $nextvalue ) { // Begin foreach 2A

						if ( $nextkey == 'user_id' ) { // Begin ifelse 1A

							$user_to_filter = $nextvalue;

						// the component in which action occurred
						} elseif ( $nextkey == 'component' ) { // Continue ifelse 1A

							$component_name = $nextvalue;

						// the component action that occurred; object array element name used to be 'component_action'
						} elseif ( $nextkey == 'type' ) { // Continue ifelse 1A

							$component_action = $nextvalue;

							// Activity directory check
							if ( ! bp_displayed_user_id() && bp_is_activity_component() && ! bp_current_action() ) {
								$user_to_filter = $user_to_filter;

							// Everything else is assumed to be on a profile page
							// Filter by displayed user ID
							} else {
								$user_to_filter = bp_displayed_user_id();
							}

							// Next, need to recreate the unique activity action item ID

							/* Think the next several lines of code look odd? They are and it's
							 * too bad they're required. Refer to the following subsection in
							 * the Developer's Guide of the BuddyPress Privacy Manual:
							 *
							 * - Creating a Unique Item ID When BuddyPress Does Not Offer One
							 */

							// Grab first alpha character
							$code_first = substr( $component_action, 0, 1);

							// Find the first and last (if any) underscore character
							$first_dash = stripos( $component_action, '_' );
							$last_dash = strripos( $component_action, '_' );

							// Grab next alpha character; the one just after underscore
							$code_next = substr( $component_action, $first_dash + 1, 1 );

							// Grab last alpha character, if needed.
							if ( $first_dash != $last_dash) {
								$code_last = substr( $component_action, $last_dash + 1, 1 );
								$code_final = $code_first . $code_next . $code_last;
							} else {
								$code_final = $code_first . $code_next;
							}

							// Generate ASCII-based artificial integer
							$item_id = NULL;

						    for ($j = 0; $j < strlen($code_final); $j++) {
						    	$item_id .= ord( $code_final[$j] );
						    }

							$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( $user_to_filter, $component, $filtered_item, $item_id);

							// filter profile field if record not empty; if empty, skip to next key
							if ( !empty( $acl_row) ) {

								$acl_level = $acl_row->bpaz_level;

								$check_if_friend = bp_displayed_user_id();

								$user_type = bp_authz_determine_user_type( $check_if_friend );

								$group_user_list = array();

								/* If the ACL equals 3 or 4 and there is list data, then the
								 * list needs to be parsed.
								 */
								if ( !empty( $acl_row->lists) ) {
									if ( $acl_level == 3 || $acl_level == 4 ) {
										$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
									}
								}

								$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
							}

						} // End ifelse 1A

						// Rebuild array by removing marked array element
						if ( $filter_user_content == true ) {

							// Remove array element
							unset( $filtered_activities_array[$i] );

							// Set a flag to indicate that array needs to be re-keyed
							$rekey_array = true;
						}

						if ( $rekey_array == true ) {
							$fields_removed[] = $component_action;
						}

					}; // End foreach 2A

				} // End for 1A

				if ( !empty( $fields_removed ) ) {
					// Re-key the activities object array so the keys are sequential and numeric (starting at 0)
					$filtered_activities_array = array_values( $filtered_activities_array );
				}

			} // End if 1A
		} // End foreach 1A

		// Finally, rebuild overall $filtered_activities_template object by resetting activities count and activities object array

		$filtered_activities_template->activity_count = count( $filtered_activities_array );

		/* Note: This is not possible to do at this stage; does it matter?
		 * $filtered_activities_template->total_activity_count = ???;
		 */

		$filtered_activities_template->activities = $filtered_activities_array;
	}

	return $filtered_activities_template;
}
add_filter( 'bp_has_activities', 'bp_authz_filter_activity_by_acl', 5, 2 );


/**
 * bp_authz_filter_profile_by_acl()
 *
 * Filters a user's profile fields based on their privacy settings
 */
function bp_authz_filter_profile_by_acl( $fields ) {
	global $bp;

	$fields_removed = array();

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if ( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_XPROFILE_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		$filtered_fields = $fields;

	} else {

		$filtered_fields = $fields;

		$component = "profile";

		$filtered_item = "profile_field";

		$count = count( $fields );

		for( $i = 0; $i < $count; $i++ ) {

			$filter_user_content = false;
			$rekey_array = false;

			foreach ( $fields[$i] as $key => $value ) {

				// when key = id, this is the field id
				if ( $key == 'id' ) {

					$item_id = $value;

					$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( bp_displayed_user_id(), $component, $filtered_item, $item_id);

					// filter profile field if record not empty; if empty, skip to next key
					if ( !empty( $acl_row) ) {

						$acl_level = $acl_row->bpaz_level;

						$check_if_friend = bp_displayed_user_id();

						$user_type = bp_authz_determine_user_type( $check_if_friend );

						$group_user_list = array();

						/* If the ACL equals 3 or 4 and there is list data, then the
						 * list needs to be parsed.
						 */
						if ( !empty( $acl_row->lists) ) {
							if ( $acl_level == 3 || $acl_level == 4 ) {
								$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
							}
						}

						$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
					}
				}
			};

			// rebuild array by removing marked array element
			if ( $filter_user_content == true ) {

				// remove array element
				unset( $filtered_fields[$i] );

				// Set a flag to indicate that array needs to be re-keyed
				$rekey_array = true;

			}

			if ( $rekey_array == true ) {
				$fields_removed[] = $object_id;
			}
		}

		if ( !empty( $fields_removed ) ) {
			// Re-key the array so the keys are sequential and numeric (starting at 0)
			$filtered_fields = array_values( $filtered_fields );
		}

	}
	return $filtered_fields;
}
add_filter( 'xprofile_group_fields', 'bp_authz_filter_profile_by_acl', 5, 2 );


/**
 * bp_authz_filter_friends_list_by_acl()
 *
 * Filters a user's friends list based on their privacy settings
 */
function bp_authz_filter_friends_list_by_acl() {
	global $bp, $members_template;

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_FRIENDS_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		//Do nothing
	} else {

		/* If privacy settings do not allow the current viewer to see the displayed user's
		 * friends list, then all that is needed is to set $members_template->member_count = 0
		 * in the $members_template object array.
		 */

		$filter_user_content = false;

		$component = "friends";

		$filtered_item = "friends_list";

		$item_id = 0;

		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( bp_displayed_user_id(), $component, $filtered_item, $item_id);

		// filter profile field if record not empty; if empty, skip to next key
		if ( !empty( $acl_row) ) {

			$acl_level = $acl_row->bpaz_level;

			$check_if_friend = bp_displayed_user_id();

			$user_type = bp_authz_determine_user_type( $check_if_friend );

			$group_user_list = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( !empty( $acl_row->lists) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
				}
			}

			$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
		}

		if ( $filter_user_content == true ) {
			$members_template->member_count = 0;
			$members_template->total_member_count = 0;
		}
	}

}
add_action( 'bp_before_directory_members_list', 'bp_authz_filter_friends_list_by_acl', 5 );


/**
 * bp_authz_filter_friends_count_tab()
 *
 * If a given user's friend list is to be filtered from display then the friend count on the
 * Friends tab is set to zero.
 */
function bp_authz_filter_friends_count_tab( $count ) {
	global $bp;

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_FRIENDS_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		//Do nothing
	} else {

		/* If privacy settings do not allow the current viewer to see the displayed user's
		 * friends list, then all that is needed is to set $members_template->member_count = 0
		 * in the $members_template object array.
		 */

		$filter_user_content = false;

		$component = "friends";

		$filtered_item = "friends_list";

		$item_id = 0;

		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( bp_displayed_user_id(), $component, $filtered_item, $item_id);

		// filter profile field if record not empty; if empty, skip to next key
		if ( !empty( $acl_row) ) {

			$acl_level = $acl_row->bpaz_level;

			$check_if_friend = bp_displayed_user_id();

			$user_type = bp_authz_determine_user_type( $check_if_friend );

			$group_user_list = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( !empty( $acl_row->lists) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
				}
			}

			$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
		}

		if( $filter_user_content == true ) {
			$count = 0;
		}
	}

	return $count;
}
add_filter( 'friends_get_total_friend_count', 'bp_authz_filter_friends_count_tab', 5, 1 );


/**
 * bp_authz_filter_add_friends_button_by_acl()
 *
 * Determines who has access to a user's "Add Friend" button
 */
function bp_authz_filter_add_friends_button_by_acl( $friend_button ) {
	global $bp;

	// Assign passed array elements to variables
	$button = $friend_button;
	$friend_status = $friend_button[ 'id'];

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_FRIENDS_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		//Do nothing

	} else {

		/* If privacy settings do not allow the current viewer to see the "Add Friend" Button,
		 * then set $button = false.
		 */

		if( $friend_status == 'pending' || $friend_status == 'is_friend' ) {
			// Do nothing
		} else {
			// Check to see if the "Add Friend" button should be removed. If so, do it.

			$filter_user_content = false;

			$component = "friends";

			$filtered_item = "add_friend_button";

			$item_id = 0;

			$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( bp_displayed_user_id(), $component, $filtered_item, $item_id);

			// filter profile field if record not empty; if empty, skip to next key
			if ( !empty( $acl_row) ) {

				$acl_level = $acl_row->bpaz_level;

				$check_if_friend = bp_displayed_user_id();

				$user_type = bp_authz_determine_user_type( $check_if_friend );

				$group_user_list = array();

				/* If the ACL equals 3 or 4 and there is list data, then the
				 * list needs to be parsed.
				 */
				if ( !empty( $acl_row->lists) ) {
					if ( $acl_level == 3 || $acl_level == 4 ) {
						$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
					}
				}

				$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
			}

			if( $filter_user_content == true ) {
				$button = false;
			}
		}
	}

	return $button;

}
add_filter( 'bp_get_add_friend_button', 'bp_authz_filter_add_friends_button_by_acl', 1, 1 );


/**
 * bp_authz_filter_send_message_button_by_acl()
 *
 * Determines who has access to a user's "Send Message" button
 */
function bp_authz_filter_send_message_button_by_acl( $message_button_string ) {
	global $bp;

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if ( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_MESSAGES_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		$disallow_message = false;
	} else {

		/* If privacy settings do not allow the current viewer to see the "Send Private Message" Button,
		 * then change the message button string to prevent display.
		 */

		$filter_user_content = false;

		$disallow_message = false;

		$component = "messages";

		$filtered_item = "allow_messages_from";

		$item_id = 0;

		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( bp_displayed_user_id(), $component, $filtered_item, $item_id);

		// filter profile field if record not empty; if empty, skip to next key
		if ( !empty( $acl_row) ) {

			$acl_level = $acl_row->bpaz_level;

			$check_if_friend = bp_displayed_user_id();

			$user_type = bp_authz_determine_user_type( $check_if_friend );

			$group_user_list = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( !empty( $acl_row->lists) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
				}
			}

			$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
		}

		if ( $filter_user_content == true ) {
			$message_button_string = false;
		}
	}

	return $message_button_string;

}
add_filter( 'bp_get_send_message_button', 'bp_authz_filter_send_message_button_by_acl', 5, 1 );


// NOTE: THIS IS FOR TESTING ONLY AND IS CURRENTLY DISABLED
/**
 * bp_authz_test_filter_compose_messages_by_acl()
 *
 * Filters the recipient list in a composed message to make sure that any
 * recipient who does not want to receive a message from the user is removed
 */
function bp_authz_test_filter_compose_messages_by_acl( $recipients ) {
	global $bp;

	$filtered_recipients = $recipients;

	// need to convert any display names into userids

	/* Loop the recipients and convert all usernames to user_ids where needed
		foreach( (array) $recipients as $recipient ) {
			if ( is_numeric( trim( $recipient ) ) )
				$recipient_ids[] = (int)trim( $recipient );

			if ( $recipient_id = bp_core_get_userid( trim( $recipient ) ) )
				$recipient_ids[] = (int)$recipient_id;
		}
	*/

	$filtered_recipients = (array)"tester1";

	return $filtered_recipients;
}
//add_filter( 'bp_messages_recipients', 'bp_authz_test_filter_compose_messages_by_acl', 5, 1 );


// NOTE: THIS STILL NEEDS TO BE COMPLETED; IT IS CURRENTLY DISABLED
/**
 * bp_authz_filter_compose_messages_by_acl()
 *
 * Filters the recipient list in a composed message to make sure that any
 * recipient who does not want to receive a message from the user is removed
 */
function bp_authz_filter_compose_messages_by_acl( $recipients ) {
	global $bp;

	// See filter hook bp_friends_autocomplete_list in bp_dtheme_ajax_messages_autocomplete_results()

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if ( is_super_admin() || bp_is_my_profile() || BP_AUTHZ_MESSAGES_DISABLED == 1 || BP_AUTHZ_DISABLED == 1 ) {
		$filtered_recipients = $recipients;
	} else {

		$filter_user_content = false;

		$component = "messages";

		$filtered_item = "allow_messages_from";

		$item_id = 0;

		/* Loop through recipients, checking each one to determine if the viewing user has the
		 * rights to send them a message; if not, remove recipient from list
		 */

		$filtered_recipients = $recipients;

		foreach ( $filtered_recipients as $key => $value ) {

			$filter_user_content = false;
			$rekey_array = false;

			// set a variable that is eventually sent to check_is_friend() and passed into $possible_friend_userid
			$check_if_friend = $value;

			$user_type = bp_authz_determine_user_type( $check_if_friend );

			/** Cannot use bp_displayed_user_id() in the below function call; instead, need to use the
			 * current recipient in the array which is represented by the variable $value. But to do that,
			 * we first need convert username into userID
			 */

			//*** As mentioned above, set this variable to the UserID; find BP function to use.
			//$recipient_id = function_that_converts_parameter_to_userID( $value );

			$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( $recipient_id, $component, $filtered_item, $item_id);

			// filter profile field if record not empty; if empty, skip to next key
			if ( !empty( $acl_row) ) {

				$acl_level = $acl_row->bpaz_level;

				$check_if_friend = bp_displayed_user_id();

				$user_type = bp_authz_determine_user_type( $check_if_friend );

				$group_user_list = array();

				/* If the ACL equals 3 or 4 and there is list data, then the
				 * list needs to be parsed.
				 */
				if ( !empty( $acl_row->lists) ) {
					if ( $acl_level == 3 || $acl_level == 4 ) {
						$group_user_list = bp_authz_parse_list( $acl_row->lists, $acl_level );
					}
				}

				$filter_user_content = bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content, $group_user_list );
			}

			//***
			// For testing
			//echo "Recipient = " . $value . "Remove: " . $filter_user_content . "<br />";
			// End testing

			// rebuild array by removing marked array element
			if ( $filter_user_content == true ) {

				// remove array element
				unset( $filtered_recipients[$key] );

				// Set a flag to indicate that array needs to be re-keyed
				$rekey_array = true;

			}

			if ( $rekey_array == true ) {
				$fields_removed[] = $object_id;
			}

		}

		if ( !empty( $fields_removed ) ) {
			// Re-key the array so the keys are sequential and numeric (starting at 0)
			$filtered_recipients = array_values( $filtered_recipients );
		}

	}
	return $filtered_recipients;

}
//add_filter( 'bp_messages_recipients', 'bp_authz_filter_compose_messages_by_acl', 5, 1 );


/********************************************************************************
 * Shared Privacy Filtering Helper Functions
 *
 * These functions are privacy filtering helper functions tht perform the same,
 * need task for all of the above functions.
 ********************************************************************************/

/**
 * bp_authz_parse_list()
 *
 * Takes the multidimensional lsits array and extracts just the groupID or userID,
 * creating a new single-dimensional array that can be used in bp_authz_acl_filter_level()
 * below.
 */
function bp_authz_parse_list( $bp_current_user_group_list, $acl_level ) {

	if ( $acl_level == 3 ) {
		$list_type = 'grouplist';
	} else {
		$list_type = 'userlist';
	}

	// If list_type array element is empty, do not process
	if ( empty( $bp_current_user_group_list[$list_type] ) ) {
		$selected_user_group_list = null;

	// Process list_type array element
	} else {

		foreach ( (array)$bp_current_user_group_list[$list_type] as $key => $value ) {
			$selected_user_group_list[] = $value;
		}
	}

	return $selected_user_group_list;
}


/**
 * bp_authz_extract_users_in_group
 *
 * Given a groupID, extract the members (by userID) of the group, placing the IDs
 * into a simple, one dimensional array for further processing.
 */
function bp_authz_extract_users_in_group( $group_id) {
	global $bp;

	$groups_members = BP_Groups_Member::get_group_member_ids($group_id);

	if ( empty( $groups_members) ) {
		$groups_members = null;
	}

	return $groups_members;
}


/**
 * bp_authz_determine_user_type()
 *
 * Determines $user_type which is then used to determine which viewing privileges
 * a given user has to the currently displayed user's data
 */
function bp_authz_determine_user_type( $check_if_friend ) {
	global $bp;

	// Determine relationship between logged in user and displayed user
	if ( !is_user_logged_in() ) {
		$user_type = "logged out"; //equates to bpaz_level = 0
	} else {
		// Is viewing user a friend?
		$isfriend = BP_Friends_Friendship::check_is_friend( $bp->loggedin_user->id, $check_if_friend );

		if ( $isfriend == 'is_friend') {
			$user_type = "friend"; //equates to bpaz_level = 2
		} else {
			$user_type = "logged in user"; //equates to bpaz_level = 1
		};
	}

	$user_type = apply_filters( 'bp_authz_determine_user_type', $user_type );

	return $user_type;
}


/**
 * bp_authz_acl_filter_level()
 *
 * Uses $user_type and the retreived acl level set by the displayed user to determine which
 * pieces of data the viewer may see
 */
function bp_authz_acl_filter_level( $acl_level, $user_type, $filter_user_content = false, $group_user_list ) {
	global $bp;

	switch ( $acl_level ) {
		case 1: // Logged in users only

	    	if ( $user_type == "logged out" ) {
	        	/* Logged out users cannot view; remove array element */
	        	$filter_user_content = true;
	        } else {
	        	$filter_user_content = false;
	        }

	        break;

	    case 2: // Friends only

	       	if ( $user_type == "friend" ) {
	        	$filter_user_content = false;
	        } else {
	        	/* Viewing user cannot view; they may not be logged in and/or
	        	 are not a friend of displayed user; remove array element */
	        	$filter_user_content = true;
	        }

	        break;

	    case 3: // Members of These Groups
	    	/* logged in user must be a member of at least one of the groups in array */

			/* Loop through each group, extracting its members (by userID) into an array,
			 * adding the results to the previous array. This builds one big array of userIDs
			 * that have viewing rights to the piece of datum in question.
			 */
			foreach( $group_user_list as $group_key => $group_value ) {
				$groups_members = bp_authz_extract_users_in_group( $group_value );

				/* Now loop through the returned array, extracting just the
				 * userIDs. Create a new array with all userIDs for all groups
				 * passed into this function.
				 */
				if( !empty( $groups_members ) ) {
					foreach( $groups_members as $user_key => $user_value ) {
						$user_list_for_groups[] = $user_value;
					}
					unset( $user_key);
				}
			}
			unset( $group_key);

			// Search for viewing user's UserID in grouplist
	       	if ( in_array( $bp->loggedin_user->id, $user_list_for_groups ) ) {
	        	$filter_user_content = false;
	       	} else {
	       		// Logged in user is not on username list; remove array element
	        	$filter_user_content = true;
	       	}

	        break;

	    case 4: // By username only; logged in user must be in array of allowed users

	       	/* Search for viewing user's name in userlist */
	       	if ( in_array( $bp->loggedin_user->id, $group_user_list ) ) {
	        	$filter_user_content = false;
	       	} else {
	       		/* Logged in user is not on username list; remove array element */
	        	$filter_user_content = true;
	       	}

	        break;

	    case 5: // Totally private; for displayed user's and site admin's eyes only

	        /* Remove array element */
	        $filter_user_content = true;

	        break;

	    default: // All users can view
	    	$filter_user_content = false;

	}

	$filter_user_content = apply_filters( 'bp_authz_acl_filter_level', $filter_user_content );

	return $filter_user_content;
}


 /*********************************************************************************
 * Test Privacy Filtering Functions
 *
 * The functions are placed here for future use. Most are just skeleton functions
 * at this time.
 *
 ********************************************************************************/
// TEST

function test_filter_activity_subnav( $subnav_item_link, $subnav_item ) {
	global $bp;

	/*
	echo "<br /><-------------------------->\n";
	//echo "<br />subnav link\n";
	//print_r( $subnav_item_link );
	echo "<br />subnav item:\n";
	print_r( $subnav_item );
	echo "<br /><-------------------------->\n";
	*/

	//return $subnav_item_link;
	//return false;
}
//add_filter( 'bp_get_options_nav_just-me', 'test_filter_activity_subnav', 5, 2 );
//add_filter( 'bp_get_options_nav_activity-friends', 'test_filter_activity_subnav', 5, 2 );
//add_filter( 'bp_get_options_nav_activity-groups', 'test_filter_activity_subnav', 5, 2 );
//add_filter( 'bp_get_options_nav_activity-favs', 'test_filter_activity_subnav', 5, 2 );
//add_filter( 'bp_get_options_nav_activity-mentions', 'test_filter_activity_subnav', 5, 2 );
//add_filter( 'bp_get_options_nav_activity-filter', 'test_filter_activity_subnav', 5, 2 );


function test_filter_latest_update( $latest_update ) {
	global $bp;

	return $latest_update;
	//return false;
}
//add_filter( 'bp_get_activity_latest_update', 'test_filter_latest_update', 5, 1  );


function test_filter_username( $username ) {
	global $bp;

	return $username;
	//return bp_displayed_user_username();
}
//add_filter( 'bp_displayed_user_fullname', 'test_filter_username', 5, 1 );


// Blogs Privacy Filtering --> Similar to Activities filter using bp-has-activities
function test_filter_blogs( $has_blogs, $blogs_template ) {
	global $bp;


}
//add_filter( 'bp_has_blogs', 'test_filter_blogs', 5, 2 );


// Groups Privacy Filtering --> Similar to Activities filter using bp-has-activities
function test_filter_groups( $has_groups, $groups_template ) {
	global $bp;


}
//add_filter( 'bp_has_groups', 'test_filter_groups', 5, 2 );


// END TEST

?>