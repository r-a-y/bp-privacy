<?php
/**
 * Sanitization filters for user input and output
 *
 * @package BP-Privacy
 * @version 1.2
 * @since 0.01
 */

	/**
 	* Added filters to key BP-Privacy functions and methods
 	*/

	// Before save added filters for BP_Authz_ACL_Main
	add_filter( 'authz_acl_user_id_before_save', 'wp_filter_kses', 1 );
	add_filter( 'authz_acl_filtered_component_before_save', 'wp_filter_kses', 1 );
	add_filter( 'authz_acl_filtered_item_before_save', 'wp_filter_kses', 1 );
	add_filter( 'authz_acl_filtered_item_before_save', 'stripslashes', 1 );
	add_filter( 'authz_acl_filtered_item_id_before_save', 'wp_filter_kses', 1 );

	// Before save added filters for BP_Authz_ACL_Lists
	add_filter( 'authz_acl_list_type_before_save', 'wp_filter_kses', 1 );
	add_filter( 'authz_acl_list_type_before_save', 'stripslashes', 1 );
	add_filter( 'authz_acl_user_group_id_before_save', 'wp_filter_kses', 1 );

?>