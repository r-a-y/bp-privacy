<?php

/**
 * Activity Privacy Settings Screen
 *
 * This is a multi-tiered privacy settings screen with options for
 * applying ACL settings globally, to a given field grouping, or
 * to a single field.
 *
 * @since 0.01
 * @version 3.0
 */

function bp_authz_add_activity_nav() {
	global $bp;

 	// Add all the enabled privacy sub navigation items
	$privacy_link = $bp->loggedin_user->domain . $bp->authz->slug . '/';

	if( bp_privacy_filtering_active( 'activity' ) ) {
		bp_core_new_subnav_item( array( 'name' => __( 'Activity Privacy', BP_AUTHZ_PLUGIN_NAME ), 'slug' => 'activity-privacy', 'parent_url' => $privacy_link, 'parent_slug' => $bp->authz->slug, 'screen_function' => 'bp_authz_screen_activity_privacy', 'position' => 20, 'user_has_access' => bp_is_my_profile() ) );
	};

}
add_action( 'bp_authz_add_settings_nav', 'bp_authz_add_activity_nav' );

function bp_authz_screen_activity_privacy() {
	global $bp_privacy_updated, $privacy_form_error;

	$bp_privacy_updated = false;
	$privacy_form_error = false;

	if ( isset( $_POST[ 'bp-authz-activity-submit' ] ) && isset( $_POST[ 'bp-authz' ] ) ) {
		if ( !check_admin_referer( 'bp-authz-privacy-activity', '_wpnonce_privacy-activity' ) )
			return false;

		// for additional security
		$privacy_post_array = array_map( 'stripslashes_deep', $_POST[ 'bp-authz' ] );

		//***
		/*
		echo '<br />_________________<br />bp-authz ARRAY:<br />';
			foreach ( $privacy_post_array as $key => $value ) {
				echo "<pre>";
				echo "<strong>" . $key . ": </strong><br />";
				print_r( $value );
				echo "</pre>";
			}
		echo '<br />END ==> bp-authz ARRAY:<br />';
		*/

		/* This function initiates processing of the passed-in form array data and then triggers
		 * the saving or deleting of Main and Lists ACL records
		 */
		$bp_privacy_updated = bp_authz_process_privacy_settings( $privacy_post_array, $tiered = true );

		if ( $bp_privacy_updated == false ) {
			$privacy_form_error = true;
		}

	}

	do_action( 'bp_authz_activity_privacy' );

	add_action( 'bp_template_content', 'bp_authz_screen_activity_privacy_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_authz_screen_activity_privacy_content() {
	global $bp, $current_user, $bp_privacy_updated, $privacy_form_error;

	if ( $bp_privacy_updated && !$privacy_form_error ) { ?>
		<div id="message" class="updated fade">
			<p><?php _e( 'Changes Saved.', BP_AUTHZ_PLUGIN_NAME ) ?></p>
		</div>
	<?php } elseif ( !$bp_privacy_updated && $privacy_form_error ) { ?>
		<div id="message" class="error fade">
			<p><?php _e( 'Error Saving Your Settings.', BP_AUTHZ_PLUGIN_NAME ) ?></p>
		</div>
	<?php } ?>

	<form action="<?php echo $bp->loggedin_user->domain . 'privacy/activity-privacy' ?>" method="post" id="privacy-settings-form">
		<h3><?php _e( 'Control who can view your activity stream', BP_AUTHZ_PLUGIN_NAME ) ?></h3>
		<p><?php _e( 'You can apply a single privacy-level setting to an activity group.  Or you can expand the selection, offering even greater control over each activity type.', BP_AUTHZ_PLUGIN_NAME ); ?>
		<p><?php _e( 'You can choose to accept the default settings or set a custom value. You can select multiple users or groups in each listbox (PC: Ctrl click; Mac: Cmd click).', BP_AUTHZ_PLUGIN_NAME ) ?></p>

		<?php wp_nonce_field( 'bp-authz-privacy-activity', '_wpnonce_privacy-activity' );

		// Used to create unique CSS ID names which JQuery can reference
		$acl_rec = 0;

		// Indicate for which component privacy settings are being set
		$component = "activity";

		// Form type: tiered (true) or single (false)
		$tiered = true;

		/* Initialize the expanding container variables and auto_trigger array. See very
		 * end of function for a few more details or refer to the following section in the
		 * Developer's Guide of the BuddyPress Privacy Manual:
		 *
		 * 	- Creating a Tiered Privacy Settings Screen: Auto triggering tiered containers
		 *
		 */
		$expand_global_container = false;
		$containers_to_trigger = array();
		$containers_with_content = array('global' => 0, 'groups' => 0, 'singles' => 0);

		/*************************************************************************
		 * The next several lines are exclusively for allowing the translatable
		 * "More options..." and "Fewer options..." text strings to be accessible
		 * and useable with the jQuery function that controls the expanding and
		 * collapsing sections.
		 ************************************************************************/

		/* Set translatable Expand/Collapse text and the path to the Ajax-loading spinner gif
		 * into variables to be used below and within privacy.js
		 */
		$bpaz_more_options = esc_js( __( 'More options...', BP_AUTHZ_PLUGIN_NAME ) );
		$bpaz_fewer_options = esc_js( __( 'Fewer options...', BP_AUTHZ_PLUGIN_NAME ) );
		$bpaz_ajax_spinner = esc_js( $bp->authz->image_base . "/ajax-loader.gif" );

		/* Pass the translatable "More", "Fewer", and path to the Ajax-loading spinner gif variables
		 * to javascript variables so that jQuery can utilize.
		 */
		?>
		<script type='text/javascript'>
			var bpaz_more = '<?php echo $bpaz_more_options; ?>';
			var bpaz_fewer = '<?php echo $bpaz_fewer_options; ?>';
			var bpaz_ajax_spinner = '<?php echo $bpaz_ajax_spinner; ?>';
		</script>

		<?php
		// Begin Output of Privacy Settings Form

		// First we output the Global privacy settings container

		// Set the container form-level variable and item variable
		$form_level = 'global';
		$filtered_item = 'activity_global';

		// Eventually replace below query with a single call to bp_authz_retrieve_user_acl_recordset()

		// Retrieve the ACL "global" record, if any; All ACL global records have a $field_id = 0
		$acl_row_global = bp_authz_retrieve_user_acl_record_id_not_known( $current_user->ID, $component, $filtered_item, 0);

		//***
		/*
		echo "________________<br />Current Global ACL Record:<br />";
		print_r( $acl_row_global );
		echo "<br />________________<br />";
		*/

		/* Populate $containers_to_trigger array:
		 *
		 * A global ACL record does not exist for this user for this component. This
		 * means that all group levels, and possibly certain single levels as well,
		 * might need to be made visible upon form load. If all privacy items are
		 * set to "All Users", which is ACL= 0, then the global container will not be
		 * expanded on form load.
		 *
		 * See "Auto triggering tiered containers: The Global Groups Singles Grid Array"
		 * in the Developer's Guide section of the BuddyPress Privacy Manual.
		 */
		if ( empty( $acl_row_global ) ) {
			$expand_global_container = true;
		} else {
			$containers_with_content['global'] = 1;
		}

		?>

		<div class="privacy_slide_main">
			<table class="privacy-settings" id="global-activity-privacy-settings-<?php echo $acl_rec; ?>">
				<thead>
					<tr>
						<th class="group-title"><h5><?php _e( 'Global Activity', BP_AUTHZ_PLUGIN_NAME ) ?></h5></th>
						<th class="group-second"></th>
						<th class="group-third"></th>
						<th class="button-expand" id="expand-button-<?php echo $acl_rec; ?>">
							<p><a href="javascript:;"><?php echo $bpaz_more_options; ?></a></p>

							<?php

							/* Populate hidden $_POST array elements with required acl table fields. Variable $acl_id is used
							 * to determine whether inserting new record or updating table.
							 */
							if ( empty( $acl_row_global ) ) {
								$acl_id = null;
								$acl_bpaz = 0;
								$acl_group_user_list = null;
							} else {
								$acl_id = $acl_row_global->id;
								$acl_bpaz = $acl_row_global->bpaz_level;
								$acl_group_user_list = ! empty( $acl_row_global->lists ) ? $acl_row_global->lists : array();
							}

							/* NOTE: The value string in the hidden "group_user_list_old" $_POST array
							 * element needs to be single quoted as the JSON-encoded string uses double
							 * quotes. If the value string used double quotes, then only the very first
							 * character in the encoded string would be passed.
							 */
							?>

							<input type ="hidden" name="bp-authz[global][id]" value="<?php echo $acl_id; ?>" />
							<input type ="hidden" name="bp-authz[global][filtered_component]" value="<?php echo $component; ?>" />
							<input type ="hidden" name="bp-authz[global][filtered_item]" value="<?php echo $filtered_item; ?>" />
							<input type ="hidden" name="bp-authz[global][item_id]" value="0" />
							<input type ="hidden" name="bp-authz[global][group_user_list_old]" value='<?php echo json_encode( $acl_group_user_list ); ?>' />
						</th>
					</tr>
				</thead>

				<tbody>
					<tr>
						<td class="group-selection">
							<?php _e( 'Who Can View', BP_AUTHZ_PLUGIN_NAME ) ?>
							<select name="bp-authz[global][acl]" id="global-acl">
								<?php $acl_inactive = bp_authz_output_bpaz_select( $acl_bpaz ); ?>
							</select>
							<?php if( $acl_inactive ) {
									echo "<div id='acl_warning'><p>" . __( 'Please select a new option.', BP_AUTHZ_PLUGIN_NAME ) . "</p></div>";
									$privacy_form_error = true;
								}
							?>
						</td>
						<td class="group-userlist">

							<?php
							/* To learn more about what is going on within this class selector, see the
							 * "Using AJAX to display Group and User Listboxes" subsection in the
							 * Developer's Guide section of the BuddyPress Privacy Manual.
							 */

							// Initialize for passing into jQuery if needed
							$single_rec = 0;
							$group_rec = 0;

							/* As javascript does not support associative arrays, the lists object array needs
							 * to be converted into JSON format before it can be used in the AJAX call. jQuery
							 * can then grab it as needed.
							 *
							 * NOTE: The value string needs to be single quoted as the JSON-encoded string
							 * uses double quotes. If the value string used double quotes, then only the very
							 * first character in the encoded string would be passed.
							 */
							?>
							<div id="global-acl-json-lists-<?php echo $acl_rec; ?>">
								<input type="hidden" name="json_lists" value='<?php echo json_encode( $acl_group_user_list ); ?>' />
							</div>

							<?php
							/* Set necessary listbox function parameters so that jQuery can grab the values
							 * if necessary. This is accomplished via jQuery's serializeArray Ajax helper
							 * function. Variables are typecast for extra security.
							 */
							 ?>

							<div id="global-acl-listbox">

								<input type="hidden" name="component" value="<?php echo esc_js( (string)$component ); ?>" />
								<input type="hidden" name="acl_rec" value="<?php echo (int)$acl_rec; ?>" />
								<input type="hidden" name="single_rec" value="<?php echo (int)$single_rec; ?>" />
								<input type="hidden" name="tiered" value="<?php echo (bool)$tiered; ?>" />
								<input type="hidden" name="form_level" value="<?php echo esc_js( (string)$form_level ); ?>" />
								<input type="hidden" name="group_rec" value="<?php echo (int)$group_rec; ?>" />

								<div class="listbox_output">

									<img class="ajax_spinner" src="<?php echo $bpaz_ajax_spinner ?>" alt="Loading..." />

									<?php

									//***
									/*
									echo "Lists Array:<br />";
									print_r( $acl_group_user_list );
									echo "<br />";
									*/
									//*** END TEST

									/* Output the group or user listboxes
									 *
									 * This is auto populated on form load if BPAz equals 3 or 4. If BPAz equals another
									 * value, then setting of the "Who Can View" selector to "Members of These Groups" or
									 * "These Users Only" will trigger a jQuery function that will create the proper listbox
									 * to output.
									 */
									if ( $acl_bpaz == 3 || $acl_bpaz == 4 ) {

										if ( $acl_bpaz == 3 ) {
											$acl_list_type = 'grouplist';
										} else {
											$acl_list_type = 'userlist';
										}

										// Populate the Groups or Users listbox; any previously-selected items will be highlighted
										$listbox_html = bp_authz_create_privacy_settings_listbox( $acl_group_user_list, $acl_list_type, $acl_bpaz, $acl_rec, 0, $tiered, $form_level );

										echo $listbox_html;
									}

									?>
								</div>
							</div>
						</td>
						<td class="group-third"></td>
						<td class="group-save">
							<p>
								<?php _e( 'Apply Globally on Save?', BP_AUTHZ_PLUGIN_NAME ) ?>
								<input type="checkbox" name="bp-authz[global][save_global]" value="yes" />
							</p>
						</td>

					</tr>
				</tbody>
			</table>
		</div>

		<?php

		// Now we output the group activity privacy settings container(s)

		// Group array counter
		$group_rec = 1;

		// Initialize
		$keep_global_container_element = false;

		// Step through the activity object array; the first level holds those components that capture activity stream data
		foreach ( $bp->activity->actions as $actionkey => $firstvalue ) { // Begin foreach 1A

			// Set the container form-level variable and item variable. Resest each pass through loop.
			$form_level = 'group';
			$filtered_item = 'activity_group';

			// Increment unique CSS ID name counter
			$acl_rec ++;

			// Reset single array counter for next pass
			$single_rec = 0;

			// (Re)Initialize for next pass
			$containers_with_content['groups'] = 0;

			/* Think the next several lines of code look odd? They are and it's
			 * too bad they're required. Refer to the following subsection in
			 * the Developer's Guide of the BuddyPress Privacy Manual:
			 *
			 * - Creating a Unique Item ID When BuddyPress Does Not Offer One
			 */

			// grab the 1, 2 and 4 characters to assemble initial code.
			$code_initial = substr( $actionkey, 0, 4 );
			$code_first = substr( $code_initial, 0, 2 );
			$code_last = substr( $code_initial, -1, 1 );
			$action_code = $code_first . $code_last;

			// Generate ASCII-based artificial integer
		    $item_id = NULL;

		    for ($i = 0; $i < strlen($action_code); $i++) {
		    	$item_id .= ord( $action_code[$i] );
		    }

		   	//echo 'Group Item ID: ' . $item_id . '<br />';

			//***
			// Eventually replace below query with a single call for this component to bp_authz_retrieve_user_acl_recordset()

			// Retrieve any Group ACL record
			$acl_row_group = bp_authz_retrieve_user_acl_record_id_not_known( $current_user->ID, $component, $filtered_item, $item_id);

			//***
			/*
			echo "________________<br />Current Group ACL Record:<br />";
			print_r( $acl_row_group );
			echo "<br />________________<br />";
			*/

			/* Populating and managing the $containers_to_trigger array:
			 *
			 * Any unique numerical identifiers stored in this array will trigger the
			 * appropriate group containers to expand, revealing the privacy items at
			 * the single level. The only exception is if there are zero ACL records
			 * for all tiered levels for the given user for this component. That
			 * happens when all privacy items are set to "All Users", which is ACL= 0.
			 *
			 * The $containers_to_trigger array is built be using data stored in the
			 * temporary $containers_with_content array. See end of Singles for loop.
			 */
			if ( $expand_global_container == true ) {

				if ( empty( $acl_row_group ) ) {

					/* A group ACL record does not exist for this user for this component.
					 * Add group to list of group containers that may need to be expanded
					 * on form load. Whether it is expanded or not depends on if at least
					 * one of the group's associated single privacy items has an ACL value
					 * greater than zero.
					 */
					$group_container_id = $acl_rec;

				} else {
					$containers_with_content['groups'] = 1;
				}
			}
		?>

			<div class="privacy_slide_group" id="group-<?php echo $acl_rec; ?>">

				<table class="privacy-settings" id="group-activity-privacy-settings-<?php echo $acl_rec; ?>">
					<thead>
						<tr>
							<th class="group-title">
								<h5>
								<?php
									switch ( $actionkey ) {
										case 'activity' :
											echo __( 'Status Updates', BP_AUTHZ_PLUGIN_NAME );
											break;

										case 'bbpress' :
											echo __( 'Forums', BP_AUTHZ_PLUGIN_NAME );
											break;

										default :
											if ( $actionkey == 'xprofile' )
												$actionkey = 'profile';

											$label = ! empty( $bp->{$actionkey}->name ) ? $bp->{$actionkey}->name : ucfirst( $actionkey );

											echo $label;
											break;
									}
								?>
								</h5>
							</th>
							<th class="group-second"></th>
							<th class="group-third"></th>
							<th class="button-expand" id="expand-button-<?php echo $acl_rec; ?>">
								<p><a href="javascript:;"><?php echo $bpaz_more_options; ?></a></p>

								<?php

								/* Populate hidden $_POST array elements with required acl table fields. Variable $acl_id is used
								 * to determine whether inserting new record or updating table. Userlist is also populated to be used elsewhere.
								 */
								if ( empty( $acl_row_group ) ) {
									$acl_id = null;
									$acl_bpaz = 0;
									$acl_group_user_list = null;
								} else {
									$acl_id = $acl_row_group->id;
									$acl_bpaz = $acl_row_group->bpaz_level;
									$acl_group_user_list = $acl_row_group->lists;
								}

								/* NOTE: The value string in the hidden "group_user_list_old" $_POST array
								 * element needs to be single quoted as the JSON-encoded string uses double
								 * quotes. If the value string used double quotes, then only the very first
								 * character in the encoded string would be passed.
								 */
								?>

								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][id]" value="<?php echo $acl_id; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][filtered_component]" value="<?php echo $component; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][filtered_item]" value="<?php echo $filtered_item; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][item_id]" value="<?php echo $item_id; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][group_user_list_old]" value='<?php echo json_encode( $acl_group_user_list ); ?>' />

							</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="group-selection">
								<?php _e( 'Who Can View', BP_AUTHZ_PLUGIN_NAME ) ?>
								<select name="bp-authz[groups][group-<?php echo $group_rec ?>][acl]" id="group-acl-<?php echo $acl_rec; ?>">
									<?php $acl_inactive = bp_authz_output_bpaz_select( $acl_bpaz ); ?>
								</select>
								<?php if( $acl_inactive ) {
										echo "<div id='acl_warning'><p>" . __( 'Please select a new option.', BP_AUTHZ_PLUGIN_NAME ) . "</p></div>";
										$privacy_form_error = true;
									}
								?>
							</td>
							<td class="group-userlist">

								<?php // See comment in the global container for this class selector name ?>

								<div id="group-acl-<?php echo $acl_rec; ?>-json-lists-<?php echo $acl_rec; ?>">
									<input type="hidden" name="json_lists" value='<?php echo json_encode( $acl_group_user_list ); ?>' />
								</div>

								<div id="group-acl-<?php echo $acl_rec; ?>-listbox">

									<input type="hidden" name="component" value="<?php echo esc_js( (string)$component ); ?>" />
									<input type="hidden" name="acl_rec" value="<?php echo (int)$acl_rec; ?>" />
									<input type="hidden" name="single_rec" value="<?php echo (int)$single_rec; ?>" />
									<input type="hidden" name="tiered" value="<?php echo (bool)$tiered; ?>" />
									<input type="hidden" name="form_level" value="<?php echo esc_js( (string)$form_level ); ?>" />
									<input type="hidden" name="group_rec" value="<?php echo (int)$group_rec; ?>" />

									<div class="listbox_output">

										<img class="ajax_spinner" src="<?php echo $bpaz_ajax_spinner ?>" alt="Loading..." />

										<?php

										/* Output the group or user listboxes
										 *
										 * This is auto populated on form load if BPAz equals 3 or 4. If BPAz equals another
										 * value, then setting of the "Who Can View" selector to "Members of These Groups" or
										 * "These Users Only" will trigger a jQuery function that will create the proper listbox
										 * to output. For this jQuery function to work, we need to pass it a some data. See below.
										 */
										if ( $acl_bpaz == 3 || $acl_bpaz == 4 ) {

											if ( $acl_bpaz == 3 ) {
												$acl_list_type = 'grouplist';
											} else {
												$acl_list_type = 'userlist';
											}

											// Populate the Groups or Users listbox; any previously-selected items will be highlighted
											$listbox_html = bp_authz_create_privacy_settings_listbox( $acl_group_user_list, $acl_list_type, $acl_bpaz, $acl_rec, 0, $tiered, $form_level, $group_rec );

											echo $listbox_html;
										}

										?>
									</div>
								</div>
							</td>
							<td class="group-third"></td>
							<td class="group-save">
								<p>
									<?php _e( 'Apply to Group on Save?', BP_AUTHZ_PLUGIN_NAME ) ?>
									<input type="checkbox" name="bp-authz[groups][group-<?php echo $group_rec ?>][save_group]" value="yes" />
								</p>
							</td>
						</tr>
					</tbody>
				</table>

				<?php

				/* Finally, we output the singles privacy settings container(s)
				 * associated with the given group privacy container
				 */

				// Set the container form-level variable and item variable. Resest each pass through loop.
				$form_level = 'single';
				$filtered_item = 'activity_field';

				?>
				<div class="privacy_slide_single">
					<table class="privacy-settings" id="single-activity-privacy-settings-<?php echo $acl_rec; ?>">

						<thead>
							<tr>
								<th class="title"><?php _e( 'Individual Activities', BP_AUTHZ_PLUGIN_NAME ) ?></th>
								<th class="selection"><?php _e( 'Who Can View', BP_AUTHZ_PLUGIN_NAME ) ?></th>
								<th class="userlist"><?php _e( 'User/Group List', BP_AUTHZ_PLUGIN_NAME ) ?></th>
							</tr>
						</thead>

				<?php
				// Next, step through the activity object subarray to grab the action => value pairs that indicate individual actions
				foreach ( $firstvalue as $action_key => $action_value ) { // Begin foreach 2A

					/* The action name to store in the ACL main table
					$action_value['key']

					// The action name to display on the settings form
					$action_value['value']
					*/

					// (Re)Initialize for next pass
					$containers_with_content['singles'] = 0;

					// Increment unique CSS ID counter and single record counter
					$acl_rec++;
					$single_rec++;

					/* Think the next several lines of code look odd? They are and it's
					 * too bad they're required. Refer to the following subsection in
					 * the Developer's Guide of the BuddyPress Privacy Manual:
					 *
					 * - Creating a Unique Item ID When BuddyPress Does Not Offer One
					 */

					// Grab first alpha character
					$code_first = substr( $action_key, 0, 1);

					// Find the first and last (if any) underscore character
					$first_dash = stripos( $action_key, '_' );
					$last_dash = strripos( $action_key, '_' );

					// Grab next alpha character; the one just after underscore
					$code_next = substr( $action_key, $first_dash + 1, 1 );

					// Grab last alpha character, if needed.
					if ( $first_dash != $last_dash) {
						$code_last = substr( $action_key, $last_dash + 1, 1 );
						$code_final = $code_first . $code_next . $code_last;
					} else {
						$code_final = $code_first . $code_next;
					}

					// Generate ASCII-based artificial integer
					$item_id = NULL;

				    for ($i = 0; $i < strlen($code_final); $i++) {
				    	$item_id .= ord( $code_final[$i] );
				    }

					// Eventually replace below query with a single call to bp_authz_retrieve_user_acl_recordset()

					// Retrieve any Single ACL record for the field
					$acl_row_single = bp_authz_retrieve_user_acl_record_id_not_known( $current_user->ID, $component, $filtered_item, $item_id );

					//***
					/*
					echo "________________<br />Current Field ACL Record:<br />";
					print_r( $acl_row_single );
					echo "<br />________________<br />";
					*/

					// Used below to populate the $containers_to_trigger array
					if ( $expand_global_container == true && !empty( $acl_row_single ) )  {
						$containers_with_content['singles'] = $containers_with_content['singles'] + 1;
					}

				?>
					<tbody>
						<tr>
							<td class="title">
								<?php

								echo $action_value['value'] . '<br />';

								/* Populate hidden $_POST array elements with required acl table fields. Variable $acl_id is used
								 * to determine whether inserting new record or updating table. Userlist is also populated to be used elsewhere.
								 */
								if ( empty( $acl_row_single ) ) {
									$acl_id = null;
									$acl_bpaz = 0;
									$acl_group_user_list = null;
								} else {
									$acl_id = $acl_row_single->id;
									$acl_bpaz = $acl_row_single->bpaz_level;
									$acl_group_user_list = ! empty( $acl_row_single->lists ) ? $acl_row_single->lists : array();
								}

								/* NOTE: The value string in the hidden "group_user_list_old" $_POST array
								 * element needs to be single quoted as the JSON-encoded string uses double
								 * quotes. If the value string used double quotes, then only the very first
								 * character in the encoded string would be passed.
								 */
								?>

								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][id]" value="<?php echo $acl_id; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][filtered_component]" value="<?php echo $component; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][filtered_item]" value="<?php echo $filtered_item; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][item_id]" value="<?php echo $item_id; ?>" />
								<input type ="hidden" name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][group_user_list_old]" value='<?php echo json_encode( $acl_group_user_list ); ?>' />


							</td>
							<td class="selectbox">
								<?php
								// Output the dropdown box ?>
								<select name="bp-authz[groups][group-<?php echo $group_rec ?>][singles][single-<?php echo $single_rec ?>][acl]" id="single-acl-<?php echo $acl_rec; ?>">
									<?php $acl_inactive = bp_authz_output_bpaz_select( $acl_bpaz ); ?>
								</select>
								<?php if( $acl_inactive ) {
										echo "<div id='acl_warning'><p>" . __( 'Please select a new option.', BP_AUTHZ_PLUGIN_NAME ) . "</p></div>";
										$privacy_form_error = true;
									}
								?>
							</td>
							<td class="userlist">

								<?php // See comment in the global container for this class selector name ?>

								<div id="single-acl-<?php echo $acl_rec; ?>-json-lists-<?php echo $acl_rec; ?>">
									<input type="hidden" name="json_lists" value='<?php echo json_encode( $acl_group_user_list ); ?>' />
								</div>

								<div id="single-acl-<?php echo $acl_rec; ?>-listbox">

									<input type="hidden" name="component" value="<?php echo esc_js( (string)$component ); ?>" />
									<input type="hidden" name="acl_rec" value="<?php echo (int)$acl_rec; ?>" />
									<input type="hidden" name="single_rec" value="<?php echo (int)$single_rec; ?>" />
									<input type="hidden" name="tiered" value="<?php echo (bool)$tiered; ?>" />
									<input type="hidden" name="form_level" value="<?php echo esc_js( (string)$form_level ); ?>" />
									<input type="hidden" name="group_rec" value="<?php echo (int)$group_rec; ?>" />

									<div class="listbox_output">

										<img class="ajax_spinner" src="<?php echo $bpaz_ajax_spinner ?>" alt="Loading..." />

										<?php

										/* Output the group or user listboxes
										 *
										 * This is auto populated on form load if BPAz equals 3 or 4. If BPAz equals another
										 * value, then setting of the "Who Can View" selector to "Members of These Groups" or
										 * "These Users Only" will trigger a jQuery function that will create the proper listbox
										 * to output. For this jQuery function to work, we need to pass it a some data. See below.
										 */
										if ( $acl_bpaz == 3 || $acl_bpaz == 4 ) {

											if ( $acl_bpaz == 3 ) {
												$acl_list_type = 'grouplist';
											} else {
												$acl_list_type = 'userlist';
											}

											// Populate the Groups or Users listbox; any previously-selected items will be highlighted
											$listbox_html = bp_authz_create_privacy_settings_listbox( $acl_group_user_list, $acl_list_type, $acl_bpaz, $acl_rec, $single_rec, $tiered, $form_level, $group_rec );

											echo $listbox_html;
										}

										?>
									</div>
								</div>
							</td>
						</tr>
					</tbody>

				<?php

				}; // End foreach 2A; end single container loop

				unset( $action_value );

				/* Populate $containers_to_trigger array:
				 *
				 * Populate the array with the unique "expand-button-" numerical
				 * identifiers. These ids will be used to trigger the visiblity
				 * of the desired containers on form load. See end of function.
				 */
				if ( $containers_with_content['global'] == 0 ) {

					if ( $containers_with_content['groups'] != 0 ) {

						/* If global container is not yet in array,
						 * add its unique numeric identifer
						 */
						if ( !in_array( 0, $containers_to_trigger ) ) {
							$containers_to_trigger[] = 0;
						}

					} elseif ( $containers_with_content['groups'] == 0 && $containers_with_content['singles'] != 0 ) {

						/* If global container is not yet in array,
						 * add its unique numeric identifer
						 */
						if ( !in_array( 0, $containers_to_trigger ) ) {
							$containers_to_trigger[] = 0;
						}

						$containers_to_trigger[] = $group_container_id;
					}
				}

				//***
				/*
				echo '<br />**** Test Expand Container Array ****<br />';
				print_r($test_containers_to_trigger);
				echo '<br />';
				*/

			?>
				</table> <?php // end single table ?>
			</div> <?php // end single container div ?>
		</div> <?php // end group container div ?>

		<?php

		$group_rec++;

		}; // End foreach 1A; end group container loop

		unset( $firstvalue );

		?>

		<?php do_action( 'bp_authz_activity_privacy_content' ) ?>
		<div>
			<p class="submit"><input type="submit" name="bp-authz-activity-submit" value="<?php esc_attr_e( 'Save Changes', BP_AUTHZ_PLUGIN_NAME ) ?>" id="submit" class="auto"/></p>

			<h4><?php _e( 'Note: Site Administrators can always see your data', BP_AUTHZ_PLUGIN_NAME ) ?></h4>
		</div>

	</form>

<?php

	/* Looping Through the $containers_to_trigger Array:
	 *
	 * Now that form has loaded, we need to expand any privacy containers (group
	 * and single) that should be visible. The data stored in the array's value
	 * elements are the unique numerical identifiers appended to the end of each
	 * "expand-button-" id selector. This unique number, iterated via the variable
	 * $acl_rec, is auto-generated as the form drills down through each successive
	 * hierarchy in the tiered form.
	 *
	 * Any privacy container that should be visible on form load will have its
	 * unique "expand-button-" numerical identifier represented in the array.
	 * This value is passed to a function in the BuddyPress Privacy Component API
	 * that triggers a jQuery event which will expand the proper privacy container.
	 * This is the same jQuery function that controls the expand and collapse action
	 * when a user manually clicks on a "More options..." or "Fewer options..." link.
	 */
	if ( !empty( $containers_to_trigger ) ) {
		foreach ( $containers_to_trigger as $key => $value ) {
			bp_authz_tiered_form_section_visibility_toggle( $value );
		}
	}

}
?>