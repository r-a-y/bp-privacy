<?php

/**
 * Blogs Privacy Settings Screen
 */

function bp_authz_add_blogs_nav() {
	global $bp;

 	// Add all the enabled privacy sub navigation items
	$privacy_link = $bp->loggedin_user->domain . $bp->authz->slug . '/';

	if( bp_privacy_filtering_active( 'blogs' ) ) {
		bp_core_new_subnav_item( array( 'name' => __( 'Blogs Privacy', BP_AUTHZ_PLUGIN_NAME ), 'slug' => 'blogs-privacy', 'parent_url' => $privacy_link, 'parent_slug' => $bp->authz->slug, 'screen_function' => 'bp_authz_screen_blogs_privacy', 'position' => 50, 'user_has_access' => bp_is_my_profile() ) );
	};
}
add_action( 'bp_authz_add_settings_nav', 'bp_authz_add_blogs_nav' );


function bp_authz_screen_blogs_privacy() {
	global $current_user, $bp_privacy_updated, $privacy_form_error;

	add_action( 'bp_template_content', 'bp_authz_screen_blogs_privacy_content' );

	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

function bp_authz_screen_blogs_privacy_content() {
	global $bp, $current_user, $bp_privacy_updated; ?>

	<h3><?php _e( 'Set the rights to who can see your blogs', BP_AUTHZ_PLUGIN_NAME ) ?></h3>
	<p><?php _e( 'This feature will be available in Version 1.0-RC2.', BP_AUTHZ_PLUGIN_NAME ) ?></p>

<?php
}
?>