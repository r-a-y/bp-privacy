<?php

/**
 * Messages Privacy Settings Screen
 *
 * This is a single (non-tiered) privacy settings screen with options
 * targeted to single privacy items.
 *
 * @since 0.01
 * @version 3.0
 */
function bp_authz_add_messages_nav() {
	global $bp;

 	// Add all the enabled privacy sub navigation items
	$privacy_link = $bp->loggedin_user->domain . $bp->authz->slug . '/';

	if( bp_privacy_filtering_active( 'messages' ) ) {
		bp_core_new_subnav_item( array( 'name' => __( 'Messaging Privacy', BP_AUTHZ_PLUGIN_NAME ), 'slug' => 'messaging-privacy', 'parent_url' => $privacy_link, 'parent_slug' => $bp->authz->slug, 'screen_function' => 'bp_authz_screen_messaging_privacy', 'position' => 40, 'user_has_access' => bp_is_my_profile() ) );
	};
}
add_action( 'bp_authz_add_settings_nav', 'bp_authz_add_messages_nav' );

function bp_authz_screen_messaging_privacy() {
	global $bp_privacy_updated, $privacy_form_error;

	$bp_privacy_updated = false;
	$privacy_form_error = false;

	if ( isset( $_POST[ 'bp-authz-messaging-submit' ] ) && isset( $_POST[ 'bp-authz' ] ) ) {
		if ( !check_admin_referer( 'bp-authz-privacy-messages', '_wpnonce_privacy-messages' ) )
			return false;

		// for additional security
		$privacy_post_array = array_map( 'stripslashes_deep', $_POST[ 'bp-authz' ] );

		/* This function initiates processing of the passed-in form array data and then triggers
		 * the saving or deleting of Main and Lists ACL records
		 */
		$bp_privacy_updated = bp_authz_process_privacy_settings( $privacy_post_array, $tiered = false );

		if ( $bp_privacy_updated == false ) {
			$privacy_form_error = true;
		}

	}

	do_action( 'bp_authz_messaging_privacy' );

	add_action( 'bp_template_content', 'bp_authz_screen_messaging_privacy_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_authz_screen_messaging_privacy_content() {
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

	<form action="<?php echo $bp->loggedin_user->domain . 'privacy/messaging-privacy' ?>" method="post" id="privacy-settings-form">
		<h3><?php _e( 'Set the rights to who can send you a private message', BP_AUTHZ_PLUGIN_NAME ) ?></h3>
		<p><?php _e( 'You can choose to accept the default settings or set a custom value. You can select multiple users or groups in each listbox (PC: Ctrl click; Mac: Cmd click).', BP_AUTHZ_PLUGIN_NAME ) ?></p>

		<?php wp_nonce_field( 'bp-authz-privacy-messages', '_wpnonce_privacy-messages' );

		// Indicate for which component privacy settings are being set
		$component = "messages";

		// Form type: tiered (true) or single (false)
		$tiered = false;

		// Initialize ACL and single array counters
		$acl_rec = 0;
		$single_rec = 0;

		// Set the path to the Ajax-loading spinner gif into variables to be used below and within privacy.js
		$bpaz_ajax_spinner = esc_js( $bp->authz->image_base . "/ajax-loader.gif" );

		/* Pass the path to the Ajax-loading spinner gif variable
		 * to a javascript variable so that jQuery can utilize.
		 */
		?>
		<script type='text/javascript'>
			var bpaz_ajax_spinner = '<?php echo $bpaz_ajax_spinner; ?>';
		</script>

		<?php // Begin Output of Privacy Settings Form ?>

		<table class="privacy-settings" id="messaging-privacy-settings">

			<thead>
				<tr>
					<th class="title"><?php _e( 'Messaging Settings', BP_AUTHZ_PLUGIN_NAME ) ?></th>
					<th class="selection"><?php _e( 'Who Can Contact you', BP_AUTHZ_PLUGIN_NAME ) ?></th>
					<th class="userlist">Group or User Listbox</th>
				</tr>
			</thead>

			<tbody>
				<tr>
					<td class="title"><?php _e( 'Who can send you a private message', BP_AUTHZ_PLUGIN_NAME ) ?></td>

						<?php

						// Set the container form-level variable
						$form_level = 'single';

						// Indicate for which item privacy settings are being set
						$filtered_item = "allow_messages_from";

						// Increment ACL and single array counters
						$acl_rec++;
						$single_rec++;

						// Retrieve the ACL record, if any; for tis query the $field_id = 0
						$acl_row = bp_authz_retrieve_user_acl_record_id_not_known( $current_user->id, $component, $filtered_item, 0);

						//***
						/*
						echo "________________<br />Current ACL Record:<br />";
						print_r( $acl_row );
						echo "<br />________________<br />";
						*/
						//***

						/* Populate hidden $_POST array elements with required acl table fields. Variable $acl_id is used
						 * to determine whether inserting new record or updating table.
						 */
						if ( empty( $acl_row ) ) {
							$acl_id = null;
							$acl_bpaz = 0;
							$acl_group_user_list = null;
						} else {
							$acl_id = $acl_row->id;
							$acl_bpaz = $acl_row->bpaz_level;
							$acl_group_user_list = $acl_row->lists;
						}

						/* NOTE: The value string in the hidden "group_user_list_old" $_POST array
						 * element needs to be single quoted as the JSON-encoded string uses double
						 * quotes. If the value string used double quotes, then only the very first
						 * character in the encoded string would be passed.
						 */
						?>

						<input type ="hidden" name="bp-authz[singles][single-<?php echo $single_rec ?>][id]" value="<?php echo $acl_id; ?>" />
						<input type ="hidden" name="bp-authz[singles][single-<?php echo $single_rec ?>][filtered_component]" value="<?php echo $component; ?>" />
						<input type ="hidden" name="bp-authz[singles][single-<?php echo $single_rec ?>][filtered_item]" value="<?php echo $filtered_item; ?>" />
						<input type ="hidden" name="bp-authz[singles][single-<?php echo $single_rec ?>][item_id]" value="0" />
						<input type ="hidden" name="bp-authz[singles][single-<?php echo $single_rec ?>][group_user_list_old]" value='<?php echo json_encode( $acl_group_user_list ); ?>' />

						<?php

						/*
						When filtering a user's messaging option, the $bp->authz->bpaz_acl_levels array needs
						to be altered, changing a few elements.

						Array before alteration:
							0 => 'All Users' *** remove element: non-logged users cannot send PMs
							1 => 'Logged in Users'
							2 => 'Friends'
							3 => 'Members of these Groups'
							4 => 'These Users Only',
							5 => 'Only Me' *** change this to read "No One"

						The result is that five out of the orignal six elements are available in the selection box. Of
						course, some of these elements may be disabled if Site Admin has set them as such.

						Array after cloning and filtering:
							1 => 'Logged in Users'  (is the default option)
							2 => 'Friends'
							3 => 'Members of these Groups'
							4 => 'These Users Only'
							5 => 'No One'

						*/

						// offer a reduced set of options for this acl object array. See above comment block for details.
						unset( $bp->authz->bpaz_acl_levels[ 'All Users' ] );
						unset( $bp->authz->bpaz_acl_levels[ 'Only Me' ] );
						$alt_ACL_key = __( 'No One', BP_AUTHZ_PLUGIN_NAME );
						$bp->authz->bpaz_acl_levels[ $alt_ACL_key ] = array( 'level' => 5, 'enabled' => 1 );

						?>

					<td class="selectbox">
						<select name="bp-authz[singles][single-<?php echo $single_rec ?>][acl]" id="messages-acl-<?php echo $acl_rec; ?>">
							<?php $acl_inactive = bp_authz_output_bpaz_select( $acl_row->bpaz_level ); ?>
						</select>
						<?php if( $acl_inactive ) {
								echo "<div id='acl_warning'><p>" . __( 'Please select a new option.', BP_AUTHZ_PLUGIN_NAME ) . "</p></div>";
								$privacy_form_error = true;
							}
						?>
					</td>
					<td class="userlist">
						<?php
						/* To learn more about what is going on within this class selector, see the
						 * "Using AJAX to display Group and User Listboxes" subsection in the
						 * Developer's Guide section of the BuddyPress Privacy Manual.
						 */

						// Initialize for passing into jQuery if needed
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
						<div id="messages-acl-<?php echo $acl_rec; ?>-json-lists-<?php echo $acl_rec; ?>">
							<input type="hidden" name="json_lists" value='<?php echo json_encode( $acl_group_user_list ); ?>' />
						</div>

						<?php
						/* Set necessary listbox function parameters so that jQuery can grab the values
						 * if necessary. This is accomplished via jQuery's serializeArray Ajax helper
						 * function. Variables are typecast for extra security.
						 */
						 ?>

						<div id="messages-acl-<?php echo $acl_rec; ?>-listbox">

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
									$listbox_html = bp_authz_create_privacy_settings_listbox( $acl_group_user_list, $acl_list_type, $acl_bpaz, $acl_rec, $single_rec, $tiered, $form_level );

									echo $listbox_html;
								}

								?>
							</div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>

		<?php do_action( 'bp_authz_messaging_privacy_content' ) ?>

		<p class="submit"><input type="submit" name="bp-authz-messaging-submit" value="<?php esc_attr_e( 'Save Changes', BP_AUTHZ_PLUGIN_NAME ) ?>" id="submit" class="auto"/></p>

		<h4><?php _e( 'Note: Site Administrators can always send you messages', BP_AUTHZ_PLUGIN_NAME ) ?></h4>

	</form>
<?php
}
?>