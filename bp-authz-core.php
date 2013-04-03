<?php

/**************************************************************
 * Finish defining constants: DO NOT CHANGE OR GALAXY WILL END.
 * That's not as bad as the universe ending, but hey, it's a
 * galaxy and it's the one we live in, so not cool.
 *************************************************************/

define( 'BP_AUTHZ_VERSION', '1.0-RC1' );
define( 'BP_AUTHZ_DB_VERSION', '17' );
define( 'BP_AUTHZ_IS_INSTALLED', 1 );
define( 'BP_AUTHZ_REQUIREMENTS', 'PHP 5.2.x, WordPress 3.0.5, BuddyPress 1.2.7' );
define( 'BP_AUTHZ_SUITABLE', 'Development Sandbox Only' );

/* New constant using the BP-Privacy prefix */
define( 'BP_PRIVACY_IS_INSTALLED', 1 );

//*** does this constant need to have a translatable string?
if ( !defined( 'BP_AUTHZ_SLUG' ) )
	define( 'BP_AUTHZ_SLUG', 'privacy' );

/* Define the directory where user settings' functions reside */
define( 'BP_AUTHZ_SETTINGS_DIR', BP_AUTHZ_PLUGIN_DIR . '/' . 'settings' );

/* Define the directory and subdirectories where special privacy themes reside */
// These constants are currently not used
//define( 'BP_AUTHZ_THEMES_DIR', BP_AUTHZ_PLUGIN_DIR . '/' . 'themes' );
//define( 'BP_AUTHZ_THEMES_URL', BP_AUTHZ_PLUGIN_URL . '/' . 'themes' );

// Do the next three constants need to have translatable strings?
/* Define the slug for the privacy-policy page */
define( 'BP_AUTHZ_PRIVACY_POLICY_SLUG', 'privacy-policy' );

/* Define the slug for the maintenance mode page */
define( 'BP_AUTHZ_MAINTENANCE_SLUG', 'maintenance' );

/* Define the slug for the custom home page for non-logged in users */
define( 'BP_AUTHZ_CUSTOM_HOME_SLUG', 'welcome' );


/**************************************************************
 * End constant definitions. Feel free to change anything
 * below -- that is if you want the Solar System to end. If you
 * have the slightest semblance of a moral thread, then it's
 * a good idea to not mess with the below stuff. Instead
 * of hacking the code, modify BP-Privacy's behavior with action
 * and filter functions. In other words, use one or more of the
 * over 65 provided hooks!
 *************************************************************/

	/****************************************************
	 * DWARF PLANET EXCEPTION: The Pluto Constant
	 *
	 * Okay, here's one additional constant that I'll
	 * give to you so that you can freely change it --
	 * that is if you know what you are doing.
	 *
	 * Although dwarf planets might appear unimportant
	 * and measly, they're still mighty influential
	 * things. So be careful with the power offered
	 * below. Make sure you fully understand the
	 * gravity of the situation.
	 *
	 * You are entirely on your own with this one. If
	 * you break something, you're out of luck. You
	 * have been warned. But at least the Solar System
	 * will be safe for the rest of us.
	 *
	 * See the "Using MySQL's InnoDB Storage Engine for
	 * the ACL Tables" in the Site Administrator's Guide
	 * of the BuddyPress Privacy Manual for details.
	 ****************************************************/

	/* Define a constant that indicates whether ACL tables
	 * should be installed using the InnoDB storage engine
	 * and/or if already typed as InnoDB, if the ACL class
	 * models should take advantage of cascading deletes.
	 *
	 * The default is false (0) which means that BP-Privacy
	 * will assume that the ACL tables are typed as MyISAM.
	 *
	 * To change this constant, define it in your wp-config.php
	 * file. See the BuddyPress Privacy Manual for more details.
	 */
	if ( !defined( 'BP_AUTHZ_USE_INNODB' ) )
		define( 'BP_AUTHZ_USE_INNODB', false );


/**
 * bp_authz_load_settings_and_files()
 *
 * Load the Privacy Component settings array and various supporting
 * privacy files.
 *
 * The global privacy settings array variable holds information on
 * which privacy settings the Site Admin has picked. A non-existent
 * array element provides as much useful data as an existing one. The
 * privacy settings are stored in the wp_options table or in the
 * wp_x_options table if multisite is in use--where x is the blogID
 * under which BuddyPress is installed.
 *
 * Various states of the privacy component can be ascertained including
 * whether or not the Site Admin has enabled the Privacy Component, which
 * individual Privacy Component Groups have been enabled, which ACL-settings
 * levels are active, whether the Privacy TOS should be displayed on the
 * registration form, and the site's overall lockdown status.
 *
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $bp_authz_settings The global privacy settings array variable
 *
 * @version 1.2
 * @since 0.3
 */
function bp_authz_load_settings_and_files() {
	global $bp_authz_settings;

	$bp_authz_settings = apply_filters( 'bp_authz_admin_options_set', get_option( 'bp_authz_admin_settings_options' ) );

	/** Finally, load these multi-purpose files containing the privacy API,
	 * the ACL classes & methods, various filters, user privacy settings forms
	 * menu options, and the listbox AJAX function.
	 */
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-settings.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-classes.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-cssjs.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-sanitize-filters.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-listbox-ajax.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-api.php' );
	require( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-privacy-filters.php' );
}
add_action( 'bp_authz_init', 'bp_authz_load_settings_and_files', 1 );


/**
 * bp_authz_setup_globals()
 *
 * Sets up the global BPAz variables
 */
function bp_authz_setup_globals() {
	global $bp;

	// For internal identification
	$bp->authz       = new stdClass;
	$bp->authz->id   = constant( 'BP_AUTHZ_SLUG' );
	$bp->authz->slug = constant( 'BP_AUTHZ_SLUG' );
	$bp->authz->name = __( 'Privacy', BP_AUTHZ_PLUGIN_NAME );

	// Custom variables
	$bp->authz->table_name_acl_main  = $bp->table_prefix . 'bp_authz_acl_main';
	$bp->authz->table_name_acl_lists = $bp->table_prefix . 'bp_authz_acl_lists';
	$bp->authz->image_base           = constant( 'BP_AUTHZ_PLUGIN_URL' ) . '/images';

	// Register this in the active components array
	$bp->active_components[$bp->authz->slug] = 1;

	// Register this component in the loaded components array
	$bp->loaded_components[$bp->authz->slug] = $bp->authz->id;
}
add_action( 'bp_setup_globals', 'bp_authz_setup_globals' );


/**
 * bp_authz_register_root_component()
 *
 * Register 'privacy' as a root component and a few
 * special theme slugs
 */
function bp_authz_register_root_component() {
	bp_core_add_root_component( BP_AUTHZ_SLUG );
	bp_core_add_root_component( BP_AUTHZ_PRIVACY_POLICY_SLUG );
	bp_core_add_root_component( BP_AUTHZ_MAINTENANCE_SLUG );
	bp_core_add_root_component( BP_AUTHZ_CUSTOM_HOME_SLUG );
}
add_action( 'bp_setup_root_components', 'bp_authz_register_root_component' );


/**
 * bp_authz_setup_acl_levels()
 *
 * Set up the default BPAz access control privacy level array
 * and then modify, if necessary, based on Site Admin settings
 */
function bp_authz_setup_acl_levels() {
	global $bp, $bp_authz_settings;

	/* Initialize ACL-level array. ACL elements are initialized
	 * as disabled ( 'enabled' => 0 ).
	 */
	$bp->authz->bpaz_acl_levels = array(
		__( 'All Users', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 0, 'enabled' => 0 ),
		__( 'Logged in Users', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 1, 'enabled' => 0 ),
		__( 'Friends', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 2, 'enabled' => 0 ),
		__( 'Members of These Groups', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 3, 'enabled' => 0 ),
		__( 'These Users Only', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 4, 'enabled' => 0 ),
		__( 'Only Me', BP_AUTHZ_PLUGIN_NAME ) => array( 'level' => 5, 'enabled' => 0 )
	 );

	/* Next, enable all ACL levels in the ACL array that the Site Admin has selected.
	 * This is a simple process as the existence of any element in the below array
	 * indicates that a given ACL level has been enabled.
	 */
	foreach ( (array)$bp_authz_settings[ 'acl_levels' ] as $key => $value ) {
		$bp->authz->bpaz_acl_levels[ $key ][ 'enabled' ] = 1;
	}

}
add_action( 'bp_setup_globals', 'bp_authz_setup_acl_levels', 20 );


/****************************************************************
 * Privacy Table Install & Upgrade Functions
 *
 * These three functions deal with installing and upgrading the
 * Privacy Table in the MySQL database, and with initializing the
 * Admin settings options. If the stored value for
 * bp-authz-db-version in the wp_usermeta table is lower than
 * the value held in the constant BP_AUTHZ_DB_VERSION, then
 * bp_authz_install_upgrade() will be fired.
 *
 ***************************************************************/

/**
 * bp_authz_check_installed()
 *
 * Check to see if privacy tables are installed or need upgrading.
 * If BP_AUTHZ_DB_VERSION is greater than (newer than) the version
 * stored in the WP options table, then sript will be triggered.
 */
function bp_authz_check_installed() {
	global $wpdb, $bp;

	if ( !is_super_admin() )
		return false;

	if ( get_site_option( 'bp-authz-db-version' ) < BP_AUTHZ_DB_VERSION )
		bp_authz_install_upgrade();
}
add_action( 'admin_menu', 'bp_authz_check_installed' );


/**
 * bp_authz_install_upgrade()
 *
 * Installs or upgrades privacy database tables. There is an option
 * to create table with the InnoDB storage engine.
 */
function bp_authz_install_upgrade() {
	global $wpdb, $bp, $bp_authz_settings;

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	// Create tables using the InnoDB storage engine
	if ( BP_AUTHZ_USE_INNODB == true ) {

		$engine_collation_main = "{$charset_collate} ENGINE=INNODB";
		$engine_collation_child = ", FOREIGN KEY (id_main) REFERENCES {$bp->authz->table_name_acl_main}(id) ON UPDATE CASCADE ON DELETE CASCADE) {$charset_collate} ENGINE=INNODB";

	// Create tables using the default MyISAM storage engine
	} else {

		$engine_collation_main = $charset_collate;
		$engine_collation_child = ") {$charset_collate}";
	}

	$sql[] = "CREATE TABLE {$bp->authz->table_name_acl_main} (
		  		id int unsigned NOT NULL auto_increment,
		  		user_id bigint unsigned NOT NULL,
		  		filtered_component varchar(15) NOT NULL,
		  		filtered_item varchar(60) NOT NULL,
		  		item_id int unsigned NOT NULL default '0',
		  		bpaz_level tinyint(1) unsigned NOT NULL default '0',
		  		last_updated datetime NOT NULL,
		  		PRIMARY KEY (id),
		  		INDEX user_id (user_id),
		  		INDEX filtered_component (filtered_component(6)),
		  		INDEX filtered_item (filtered_item(30)),
		  		INDEX item_id (item_id)
		 	   ) {$engine_collation_main};";

	$sql[] = "CREATE TABLE {$bp->authz->table_name_acl_lists} (
		  		id bigint unsigned NOT NULL auto_increment,
		  		id_main int unsigned NOT NULL,
		  		list_type varchar(9) NOT NULL,
		  		user_group_id bigint unsigned NOT NULL,
		  		PRIMARY KEY (id),
		  		INDEX id_main (id_main),
			    INDEX list_type (list_type(4)),
			    INDEX user_group_id (user_group_id)
		 	   {$engine_collation_child};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );

	dbDelta($sql);

	update_site_option( 'bp-authz-db-version', BP_AUTHZ_DB_VERSION );

	// Initialize the privacy settings' options
	if( empty( $bp_authz_settings ) )
		bp_authz_initialize_settings();
}


/**
 * bp_authz_initialize_settings()
 *
 * Initializes the BuddyPress Privacy Component admin settings
 * option. This ensures that the component and all of its filters
 * are enabled at install.
 */

function bp_authz_initialize_settings() {
	global $bp;

	$name = "bp_authz_admin_settings_options";

	//*** Note: blogs and groups should be enable once those features are available in v1.0-RC2

	// Create array with desired, initial BuddyPress Privacy Component Settings
	$bp->authz->initialize_settings = array(
		'site_wide' => '1',
		'privacy_filtering' => array( 'profile' => '1', 'activity' => '1', 'friends' => '1', 'messages' => '1' , 'blogs' => '0' , 'groups' => '0' ),
		'acl_levels' => array( 'All Users' => 'yes', 'Logged in Users' => 'yes', 'Friends' => 'yes', 'Members of These Groups' => 'yes', 'These Users Only' => 'yes', 'Only Me' => 'yes' ),
		'lockdown' => 0
	);

	add_option( $name, $bp->authz->initialize_settings );

}
add_action( 'admin_menu', 'bp_authz_initialize_settings' );


/**
 * bp_authz_update_message()
 *
 * Add an extra update message to BP-Privacy's
 * update-plugin notifications in the admin
 * dashboard.
 *
 * @package BP-Privacy
 */
function bp_authz_update_message() {
	echo '<p style="color: red; margin: 3px 0 0 0; border-top: 1px solid #ddd; padding-top: 3px">' . __( 'IMPORTANT: <a href="http://#/">Please see the readme.txt file or ...before updating the Privacy Component.</a>', BP_AUTHZ_PLUGIN_NAME ) . '</p>';
}
//add_action( 'in_plugin_update_message-bp-privacy/bp-authz-loader.php', 'bp_authz_update_message' );


/****************************************************************
 * Internationalization Function
 *
 * All translations of BPAz should be placed in the language
 * directory. If a translation of the Privacy Component does not
 * exist in your language, please contribute to the overall
 * BuddyPress project by creating one!
 *
 ***************************************************************/

/**
 * bp_authz_load_textdomain()
 *
 * Load the BPAz translation file for current language
 */
function bp_authz_load_textdomain() {

	/* First get locale file if it exists */
	$locale = apply_filters( 'bp_authz_locale', get_locale() );

	/* If locale file exists, create path to .mo file and try to load */
	if ( !empty( $locale ) ) {

		/* path to .mo file */
		$mofile_path = sprintf( '%s/languages/%s-%s.mo', BP_AUTHZ_PLUGIN_DIR, BP_AUTHZ_PLUGIN_NAME, $locale );

		/* Allow filtering of file path */
		$mofile = apply_filters( 'bp_authz_mofile', $mofile_path );

		if ( file_exists( $mofile ) ) {
			load_textdomain( BP_AUTHZ_PLUGIN_NAME, $mofile );
		}
	}
}
add_action ( 'bp_authz_init', 'bp_authz_load_textdomain', 2 );


/****************************************************************
 * Privacy Administration Functions
 *
 * The BuddyPress Privacy Component allows for some customization
 * of its functionality via an admin menu in WP's backend. From
 * turning off the filtering functions of the entire component
 * (without deactivating it), to turning off filtering of single,
 * selected privacy groups, to choosing which ACL levels to make
 * active.
 *
 ***************************************************************/

/**
 * bp_authz_add_privacy_admin_menu()
 *
 * Registers the BPAz admin menu and places it under the BuddyPress admin menu.
 */
function bp_authz_add_privacy_admin_menu() {
	global $wpdb, $bp, $menu;

	/* Load admin functionality only when site admin is in wp-admin area */
	if ( !is_super_admin() )
		return false;

	require ( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-admin.php' );

	/* Add the Privacy Settings menu under the "BuddyPress" menu for site administrators */
	add_submenu_page( 'bp-general-settings', __( 'Privacy Settings', BP_AUTHZ_PLUGIN_NAME ), __( 'Privacy Settings', BP_AUTHZ_PLUGIN_NAME ), 'manage_options', 'bp_authz_admin_settings', 'bp_authz_admin_settings_options' );

}
add_action( 'admin_menu', 'bp_authz_add_privacy_admin_menu', 10 );


/******************************************************************************
 * Activity Functions
 *
 * These functions handle the registering, recording, and deleting of activity
 * actions for the user and for this specific component.
 *
 ******************************************************************************/

/**
 * bp_authz_register_privacy_activity_actions()
 *
 * Register the activity stream actions for the Privacy Component
 */
function bp_authz_register_privacy_activity_actions() {
	global $bp;

	if ( !function_exists( 'bp_activity_set_action' ) )
		return false;

	bp_activity_set_action( $bp->authz->id, 'updated_privacy_settings', __( 'Privacy settings updated', BP_AUTHZ_PLUGIN_NAME ) );

	do_action( 'privacy_register_activity_actions' );
}
//add_action( 'bp_register_activity_actions', 'bp_authz_register_privacy_activity_actions' );


/**********************************************************************************
 * BPAz Data Functions
 *
 * These functions handle the initiating of retrieving, recording, and deleting of
 * privacy data in the xx_bp_authz_acl_xx tables. Users can set the access rights
 * they grant to each of their BuddyPress' core component datasets. The set of all
 * privacy records for a given user is called their BPAz Access Control List
 * (BPAz ACL).
 *
 **********************************************************************************/

/**
 * bp_authz_save_user_acl_array_main()
 *
 * Saves the main ACL record for a given user
 *
 * @version 1.0
 * @since 1.0-RC1
 */
function bp_authz_save_user_acl_array_main( $id, $user_id, $filtered_component, $filtered_item, $item_id, $old_lists_array, $bpaz_level, $group_user_list_id_array ) {
	global $bp;

	//***
	/*
	echo '<br />Saving Main ACL Data<br />';
	echo '<br />ID = ' . $id . ', UserID = ' . $user_id . ', Component = ' . $filtered_component . ', Item = ' . $filtered_item . ', ObjID = ' . $item_id . ', BPAz = ' . $bpaz_level . '<br />Lists Data:<br />';
	print_r($group_user_list_id_array);
	echo '<br />Old Lists Data:<br />';
	print_r($old_lists_array);
	echo '<br />--------<br />';
	*/

	$acl_main_record = new BP_Authz_ACL_Main();

	$acl_main_record->id = $id;
	$acl_main_record->user_id = $user_id;
	$acl_main_record->filtered_component = wp_filter_kses( $filtered_component );
	$acl_main_record->filtered_item = wp_filter_kses( $filtered_item );
	$acl_main_record->item_id = $item_id;
	$acl_main_record->old_lists_array = $old_lists_array;
	$acl_main_record->bpaz_level = $bpaz_level;
	$acl_main_record->last_updated = time();
	$acl_main_record->group_user_list_id_array = $group_user_list_id_array;

	return $acl_main_record->save();
}


/**
 * bp_authz_retrieve_user_acl_dataset()
 *
 * Retrieves the entire access control list (ACL) dataset across all core components for a given user
 */
function bp_authz_retrieve_user_acl_dataset( $user_id ) {
	global $bp;

	if ( !$user_id )
		return false;

	return BP_Authz_ACL_Main::get_user_acl_dataset( $user_id );
}


/**
 * bp_authz_retrieve_user_acl_recordset()
 *
 * Retrieves the ACL recordset for a given component for a given user
 */
function bp_authz_retrieve_user_acl_recordset( $user_id, $filtered_component ) {
	global $bp;

	if ( !$user_id )
		return false;

	return BP_Authz_ACL_Main::get_user_acl_recordset_by_component( $user_id, $filtered_component );
}


/**
 * bp_authz_retrieve_user_acl_record_using_id()
 *
 * Retrieves a given ACL record for a given user if the record id is known
 */
function bp_authz_retrieve_user_acl_record_using_id( $id ) {
	global $bp;

	if ( !$id )
		return false;

	return BP_Authz_ACL_Main::get_user_acl_privacy_item_by_id( $id );
}


/**
 * bp_authz_retrieve_user_acl_record_id_not_known()
 *
 * Retrieves a given ACL record for a given user using parameters other than record id
 */
function bp_authz_retrieve_user_acl_record_id_not_known( $user_id, $filtered_component, $filtered_item, $item_id = 0 ) {
	global $bp;

	if ( !$user_id )
		return false;

	return BP_Authz_ACL_Main::get_user_acl_privacy_item_no_id( $user_id, $filtered_component, $filtered_item, $item_id );
}


/**
 * bp_authz_delete_user_acl_record()
 *
 * Deletes a given BPAz access control record for a given user
 *
 * See delete method comments in class file to learn how BP Privacy
 * deals with deleting records with the InnoDB versus MyISAM storage
 * engines.
 */
function bp_authz_delete_user_acl_record( $acl_record_to_delete ) {
	global $bp;

	//***
	//echo "<br />PreProcessing --> Record ID to delete: {$acl_record_to_delete}.<br />";

	return BP_Authz_ACL_Main::delete( $acl_record_to_delete );

}


/******************************************************************************
 * Special Functions
 *
 * These functions handle special use cases where users or groups are deleted,
 * or a user leaves a hidden group and the impact that those events can have
 * on the data integrity of the ACL Main and ACL Lists tables.
 *
 * The below three functions are not yet used nor tested.
 *
 ******************************************************************************/

// BELOW REQUIRES FURTHER DEVELOPMENT AND TESTING

/**
 * bp_authz_remove_data_on_user_deletion()
 *
 * When a user is deleted from the WordPress users table, or marked as a spam user, then
 * all of their ACL recordsets need to be removed from all xx_bp_authz_acl_xx tables as well.
 *
 * @package BP-Privacy Core
 * @param $user_id The ID of the deleted user
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 */
function bp_authz_remove_data_on_user_deletion( $user_id ) {

	// This removes all of the deleted user's ACL recordsets
	BP_Authz_ACL_Main::delete_select_user_acl_records( $user_id );

	/* Delete all occurrences of deleted user in the user_group_id field
	 * of xx_bp_authz_acl_lists table. In other words, it removes any
	 * records containing that $user_id from other users' ACL recordsets.
	 */
	return BP_Authz_ACL_Lists::delete_select_user_group_listings( $user_id );

}
//add_action( 'wpmu_delete_user', 'bp_authz_remove_data_on_user_deletion', 10, 1 );
//add_action( 'delete_user', 'bp_authz_remove_data_on_user_deletion', 10, 1 );
//add_action( 'make_spam_user', 'bp_authz_remove_data_on_user_deletion', 10, 1 );


/**
 * bp_authz_remove_data_on_group_deletion()
 *
 * When a group is deleted from BuddyPress, then all occurrences of the group in the
 * user_group_id field of xx_bp_authz_acl_lists table must be removed as well.
 *
 * @package BP-Privacy Core
 * @param $group_id The ID of the deleted group
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 */
function bp_authz_remove_data_on_group_deletion( $group_id ) {
	return BP_Authz_ACL_Lists::delete_select_user_group_listings( $group_id );
}
//add_action( 'groups_delete_group', 'bp_authz_remove_data_on_group_deletion', 10, 1 );


/**
 * bp_authz_remove_data_on_user_leaving_hidden_group()
 *
 * When a member of a hidden group leaves that group, then all references to that hidden
 * group need to deleted for that user in any of their ACL List recordsets. If not, then
 * the hidden group will remain in the user's ACL table but not show up in the group
 * listbox as the user is no longer a member.
 *
 * @package BP-Privacy Core
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $bp_site_groups A globally-available array of site's groups sorted by display name
 * @param $group_id The ID of the group user left
 * @param $user_id The ID of the user who left the group
 *
 * @version 1.0
 * @since 1.0-RC1
 *
 */
function bp_authz_remove_data_on_user_leaving_hidden_group( $group_id, $user_id ) {
	global $bp, $bp_site_groups;

	// Is this group a hidden group? If so, delete all group references from ACL Lists table
	foreach ( $bp_site_groups as $privacykey => $firstvalue ) {

		$group_list_id = $firstvalue['id'];
		$group_list_name = $firstvalue['name'];
		$group_list_status = $firstvalue['status'];

		if ( $group_list_id == $group_id ) {
			if ( $group_list_status == 'hidden' ) {
				return BP_Authz_ACL_Main::delete_hidden_group_from_user_listings( $group_id, $user_id );
			}
		}
	}
}
//add_action( 'groups_leave_group', 'bp_authz_remove_data_on_user_leaving_hidden_group', 10, 2 );

//END TESTING


/*********************************************************************************
 * Cron Settings And Timed Functions
 *
 * See future.txt for functions that should have cron jobs
 *
 ********************************************************************************/

?>