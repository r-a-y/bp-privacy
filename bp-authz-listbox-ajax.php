<?php
/**
 * bp_authz_ajax_listbox_generation()
 *
 * This script does not have direct access. Instead, it is called via
 * a jQuery ajax request in privacy.js. Data is returned in JSON format 
 * and therefore requires PHP 5.2 or greater to function.
 *
 * The purpose of this function is to use the passed-in variables to
 * set the parameters of the bp_authz_create_privacy_settings_listbox()
 * function. The returned string contains the html that will output the
 * group or user listbox into the proper element of the given Privacy
 * Settings form.
 *
 * To learn more about how this AJAXified function works within BP Privacy,
 * see the "Using AJAX to display Group and User Listboxes" subsection in 
 * the Developer's Guide section of the BuddyPress Privacy Manual.
 *
 * @package BP-Privacy
 * @version 1.0
 * @since 1.0-RC1
 *
 * Parameters: Required variables passed into function via an AJAX request 
 * Return: A JSON-encoded array indicating success status and the html-formatted 
 * string that will be outputted to create the listbox
 */
function bp_authz_ajax_listbox_generation( ) {
	
	check_ajax_referer( $_POST[ 'nonce_name' ] );

	// Pass parameters into listbox function to trigger the creation of the appropriate listbox
	$privacy_listbox = bp_authz_create_privacy_settings_listbox( $_POST[ 'list_array' ], $_POST[ 'list_type' ], $_POST[ 'bpaz_level' ], $_POST[ 'acl_rec' ], $_POST[ 'single_rec' ], $_POST[ 'tiered' ], $_POST[ 'form_level' ], $_POST[ 'group_rec' ] );
	
	ob_start();
	header("Content-Type: application/json");
	
	if ( !empty( $privacy_listbox ) ) {
		$bpaz_message = __( 'none', BP_AUTHZ_PLUGIN_NAME );

		echo json_encode( array( "status" => "success", "message" => $bpaz_message, "listbox_html" => $privacy_listbox ) );
		
	} else {
		$bpaz_message = __( 'Error outputting listbox.', BP_AUTHZ_PLUGIN_NAME );

		echo json_encode( array( "status" => "error", "message" => $bpaz_message, "listbox_html" => $privacy_listbox ) );
	}
	
	ob_end_flush();
	exit;

}
add_action( 'wp_ajax_bp_authz_ajax_listbox', 'bp_authz_ajax_listbox_generation' );
?>