<?php

/**
 * bp_authz_add_js()
 *
 * This function enqueues the javascript file and creates a unique JS namespace
 * for the privacy object.
 *
 * To learn more about how BP Privacy uses AJAX, see the following in the
 * Developer's Guide section of the BuddyPress Privacy Manual for more
 * details: Using AJAX to display Group and User Listboxes.
 *
 * @version 1.1
 * @since 0.01
 */
function bp_authz_add_js() {
	global $bp;

	if ( $bp->current_component == $bp->authz->slug ) {
		wp_enqueue_script( 'bp-authz-js', BP_AUTHZ_PLUGIN_URL . '/js/privacy.js', array( 'jquery' ) );

		// declare a JavaScript namespace object for the file that handles the AJAX request (wp-admin/admin-ajax.php)
		//wp_localize_script( 'bp-authz-js', 'PrivacyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
}
add_action( 'template_redirect', 'bp_authz_add_js', 1 );

/**
 * bp_authz_create_js_namespace()
 *
 * Declare a JavaScript namespace object for the file that handles
 * the AJAX request (wp-admin/admin-ajax.php)
 *
 * @package BP Privacy
 * @version 1.0
 * @since 1.0-RC1
 */
function bp_authz_create_js_namespace() {
	global $bp;

	if ( $bp->current_component == $bp->authz->slug ) {
		echo '<script type="text/javascript">var PrivacyAjax = "' . admin_url( 'admin-ajax.php' ) . '";</script>';
	}
}
add_action( 'wp_head', 'bp_authz_create_js_namespace' );

/**
 * bp_authz_add_structure_css()
 *
 * This function enqueues the structural CSS to help retain interface
 * structure regardless of the theme currently in use.
 *
 * @version 1.0
 * @since 0.01
 */
function bp_authz_add_structure_css() {
	/* Enqueue the privacy settings CSS file to give positional formatting for the privacy component reglardless of the theme. */
	wp_enqueue_style( 'bp-authz-structure', BP_AUTHZ_PLUGIN_URL . '/css/privacy_settings.css' );
}
add_action( 'init', 'bp_authz_add_structure_css', 2 );

?>