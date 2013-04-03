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

	$fields_removed = array();

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */

	if ( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'activity' ) || BP_AUTHZ_DISABLED == 1 ) {
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

					$remove = false;
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
							if ( ! bp_displayed_user_id() && bp_is_activity_component() ) {
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

							for ( $j = 0; $j < strlen( $code_final ); $j++ ) {
								$item_id .= ord( $code_final[$j] );
							}

							// get activity user's privacy settings
							$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( $user_to_filter, $component, $filtered_item, $item_id );

							// check permissions
							if ( ! empty( $acl_row ) ) {
								$permissions_args = array();

								/* If the ACL equals 3 or 4 and there is list data, then the
								 * list needs to be parsed.
								 */
								if ( ! empty( $acl_row->lists ) ) {
									if ( $acl_level == 3 || $acl_level == 4 ) {
										$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
									}
								}

								$permissions_args['acl_level'] = $acl_row->bpaz_level;

								// should we remove the activity item?
								$remove = bp_authz_acl_filter_level( $permissions_args );
							}

						} // End ifelse 1A

						// we are removing the activity item
						if ( $remove == true ) {

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

	$fields_removed = array();

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if ( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'profile' ) || BP_AUTHZ_DISABLED == 1 ) {
		$filtered_fields = $fields;

	} else {

		$filtered_fields = $fields;

		$count = count( $fields );

		for( $i = 0; $i < $count; $i++ ) {

			$remove = false;
			$rekey_array = false;

			foreach ( $fields[$i] as $key => $value ) {

				// when key = id, this is the field id
				if ( $key == 'id' ) {

					$item_id = $value;

					// check displayed user's profile settings
					$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
						bp_displayed_user_id(),
						'profile',
						'profile_field',
						$item_id
					);

					// check permissions
					if ( ! empty( $acl_row ) ) {
						$permissions_args = array();

						/* If the ACL equals 3 or 4 and there is list data, then the
						 * list needs to be parsed.
						 */
						if ( ! empty( $acl_row->lists ) ) {
							if ( $acl_level == 3 || $acl_level == 4 ) {
								$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
							}
						}

						$permissions_args['acl_level'] = $acl_row->bpaz_level;

						// should we remove the profile field?
						$remove = bp_authz_acl_filter_level( $permissions_args );
					}

				}
			};

			// we are removing the profile field
			if ( $remove == true ) {

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
	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'friends' ) || BP_AUTHZ_DISABLED == 1 ) {
		return;

	/* If privacy settings do not allow the current viewer to see the displayed user's
	 * friends list, then all that is needed is to set $members_template->member_count = 0
	 * in the $members_template object array.
	 */
	} else {

		// get displayed user's friends list settings
		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
			bp_displayed_user_id(),
			'friends',
			'friends_list'
		);

		$remove = false;

		// check permissions
		if ( ! empty( $acl_row ) ) {
			$permissions_args = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( ! empty( $acl_row->lists ) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
				}
			}

			$permissions_args['acl_level'] = $acl_row->bpaz_level;

			// should we remove the friends list?
			$remove = bp_authz_acl_filter_level( $permissions_args );
		}

		// we are removing the friends list
		if ( $remove == true ) {
			global $members_template;

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

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'friends' ) || BP_AUTHZ_DISABLED == 1 ) {
		return $count;

	/* If privacy settings do not allow the current viewer to see the displayed user's
	 * friends list, then all that is needed is to set $members_template->member_count = 0
	 * in the $members_template object array.
	 */
	} else {

		// get displayed user's friend list settings
		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
			bp_displayed_user_id(),
			'friends',
			'friends_list'
		);

		$remove = false;

		// check permissions
		if ( ! empty( $acl_row ) ) {
			$permissions_args = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( ! empty( $acl_row->lists ) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
				}
			}

			$permissions_args['acl_level'] = $acl_row->bpaz_level;

			// should we remove the friends list?
			$remove = bp_authz_acl_filter_level( $permissions_args );
		}

		// we are removing the friends list
		if( $remove == true ) {
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
function bp_authz_filter_add_friends_button_by_acl( $button ) {

	// Assign passed array elements to variables
	$friend_status = $button['id'];

	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'friends' ) || BP_AUTHZ_DISABLED == 1 ) {
		return $button;

	} else {

		// If displayed user is pending or already is friends, show the button!
		if( $friend_status == 'pending' || $friend_status == 'is_friend' ) {
			return $button;

		// Check if we should remove the "Add Friend" button.
		} else {

			// get displayed user's friend settings
			$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
				bp_displayed_user_id(),
				'friends',
				'add_friend_button'
			);

			$remove = false;

			// check permissions
			if ( ! empty( $acl_row ) ) {
				$permissions_args = array();

				/* If the ACL equals 3 or 4 and there is list data, then the
				 * list needs to be parsed.
				 */
				if ( ! empty( $acl_row->lists ) ) {
					if ( $acl_level == 3 || $acl_level == 4 ) {
						$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
					}
				}

				$permissions_args['acl_level'] = $acl_row->bpaz_level;

				// see if we should remove the friend button
				$remove = bp_authz_acl_filter_level( $permissions_args );
			}

			// we are removing the friend button
			if( $remove == true ) {
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
function bp_authz_filter_send_message_button_by_acl( $button ) {
	/**
	 * Allow site admin to see all user data.
	 * Even though there is a "Only Me" privacy option, users do not
	 * have any options to hide content from site administrators
	 */
	if ( is_super_admin() || bp_is_my_profile() || ! bp_privacy_filtering_active( 'messages' ) || BP_AUTHZ_DISABLED == 1 ) {
		return $button;

	/* If privacy settings do not allow the current viewer to see the "Send Private Message" Button,
	 * then change the message button string to prevent display.
	 */
	} else {

		// get displayed user's PM settings
		$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
			bp_displayed_user_id(),
			'messages',
			'allow_messages_from'
		);

		$remove = false;

		// check permissions
		if ( ! empty( $acl_row ) ) {
			$permissions_args = array();

			/* If the ACL equals 3 or 4 and there is list data, then the
			 * list needs to be parsed.
			 */
			if ( ! empty( $acl_row->lists ) ) {
				if ( $acl_level == 3 || $acl_level == 4 ) {
					$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
				}
			}

			$permissions_args['acl_level'] = $acl_row->bpaz_level;

			// see if we should remove the PM button
			$remove = bp_authz_acl_filter_level( $permissions_args );
		}

		// we are removing the PM button
		if ( $remove == true ) {
			$button = false;
		}
	}

	return $button;

}
add_filter( 'bp_get_send_message_button', 'bp_authz_filter_send_message_button_by_acl', 5, 1 );


/**
 * bp_authz_filter_compose_messages_by_acl()
 *
 * Filters the recipient list in a composed message to make sure that any
 * recipient who does not want to receive a message from the user is removed
 */
function bp_authz_filter_compose_messages_by_acl( $message_info ) {

	// If message privacy is disabled, stop now!
	if ( is_super_admin() || ! bp_privacy_filtering_active( 'messages' ) || BP_AUTHZ_DISABLED == 1 ) {
		return;

	// Check recipient's PM privacy settings
	} else {

		$recipients = $message_info->recipients;

		// # of recipients in the message that are not friends
		$u = 0;

		foreach ( $recipients as $key => $recipient ) {
			// make sure sender is not trying to send to themselves
			if ( $recipient->user_id == $message_info->sender_id ) {
				unset( $message_info->recipients[$key] );
				continue;
			}

			// get recipient's PM privacy settings
			$acl_row = bp_authz_retrieve_user_acl_record_id_not_known(
				$recipient->user_id,
				'messages',
				'allow_messages_from'
			);

			$remove = false;

			// check permissions
			if ( ! empty( $acl_row ) ) {
				$permissions_args = array();

				switch ( $acl_row->bpaz_level ) {
					// Friends
					case 2 :
						$permissions_args['receiver_user_id'] = $recipient->user_id;

						break;

					// Lists
					case 3 :
					case 4 :
						if ( ! empty( $acl_row->lists ) ) {
							$permissions_args['group_user_list'] = bp_authz_parse_list( $acl_row->lists, $acl_row->bpaz_level );
						}

						break;

				}

				// set privacy level
				$permissions_args['acl_level']         = $acl_row->bpaz_level;

				// set initiator user ID
				$permissions_args['initiator_user_id'] = $message_info->sender_id;

				// are we removing the recipient from the PM list?
				$remove = bp_authz_acl_filter_level( $permissions_args );
			}

			// we are removing the recipient
			if ( $remove == true ) {
				unset( $message_info->recipients[$key] );
				$u++;
			}

		}

		// if there are multiple recipients and if one of the recipients has
		// restricted their PM settings, remove everyone from the recipient's list
		//
		// this is designed to prevent the message from being sent to anyone and is
		// another spam prevention measure
		if ( count( $recipients ) > 1 && $u > 0 ) {
			unset( $message_info->recipients );
		}

	}

}
add_action( 'messages_message_before_save', 'bp_authz_filter_compose_messages_by_acl' );


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

	$groups_members = BP_Groups_Member::get_group_member_ids($group_id);

	if ( empty( $groups_members) ) {
		$groups_members = null;
	}

	return $groups_members;
}


/**
 * bp_authz_acl_filter_level()
 *
 * Uses the retrieved acl level set by the displayed user to determine which
 * pieces of data the viewer may see
 *
 * @return bool True if we should block the item; false if we shouldn't block the item.
 */
function bp_authz_acl_filter_level( $args = '' ) {
	$defaults = array(
		'acl_level'           => 0,
		'group_user_list'     => array(),
		'initiator_user_id'   => bp_loggedin_user_id(),
		'receiver_user_id'    => bp_displayed_user_id()
	);

	$r = wp_parse_args( $args, $defaults );

	// Default is to allow access
	// 'false' means we're not doing any blocking
	$retval = false;

	switch ( $r['acl_level'] ) {
		// Logged in users only
		case 1:
			// WP doesn't have a function to check if a specific user is logged in or not
			// So this will fail if a plugin dev is programatically trying to create a PM
			//
			// @todo Write a function that will log the current, logged in users with a
			//       transient
			if ( ! is_user_logged_in() ) {
				$retval = true;
			}

			break;

		// Friends only
		case 2:
			// sanity check
			if ( ! bp_is_active( 'friends' ) ) {
				continue;
			}

			if ( ! friends_check_friendship( $r['initiator_user_id'], $r['receiver_user_id'] ) ) {
				$retval = true;
			}

		break;

		// Members of These Groups
		case 3:
			$user_list_for_groups = array();

			// Loop through each group, extracting its members (by userID) into an array,
			// adding the results to the previous array. This builds one big array of userIDs
			// that have viewing rights to the piece of datum in question.
			foreach( $group_user_list as $group_key => $group_value ) {
				$groups_members = bp_authz_extract_users_in_group( $group_value );

				// Now loop through the returned array, extracting just the
				// userIDs. Create a new array with all userIDs for all groups
				// passed into this function.
				if( !empty( $groups_members ) ) {
					foreach( $groups_members as $user_key => $user_value ) {
						$user_list_for_groups[] = $user_value;
					}
					unset( $user_key);
				}
			}
			unset( $group_key);

			// Search for viewing user's UserID in grouplist
			if ( ! in_array( $r['initiator_user_id'], $user_list_for_groups ) ) {
				$retval = true;
			}

			break;

		// By username only; logged in user must be in array of allowed users
		case 4:
		       	if ( ! in_array( $r['initiator_user_id'], $r['group_user_list'] ) ) {
		        	$retval = true;
		       	}

		        break;

		// Totally private; for displayed user's and site admin's eyes only
		case 5:
			$retval = true;

		        break;

	}

	return apply_filters( 'bp_authz_acl_filter_level', $retval, $r );
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

	return $latest_update;
	//return false;
}
//add_filter( 'bp_get_activity_latest_update', 'test_filter_latest_update', 5, 1  );


function test_filter_username( $username ) {

	return $username;
	//return bp_displayed_user_username();
}
//add_filter( 'bp_displayed_user_fullname', 'test_filter_username', 5, 1 );


// Blogs Privacy Filtering --> Similar to Activities filter using bp-has-activities
function test_filter_blogs( $has_blogs, $blogs_template ) {


}
//add_filter( 'bp_has_blogs', 'test_filter_blogs', 5, 2 );


// Groups Privacy Filtering --> Similar to Activities filter using bp-has-activities
function test_filter_groups( $has_groups, $groups_template ) {


}
//add_filter( 'bp_has_groups', 'test_filter_groups', 5, 2 );


// END TEST

?>