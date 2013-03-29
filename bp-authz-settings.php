<?php

/**
 * bp_authz_load_settings_files_and_add_settings_nav()
 *
 * Load all active privacy settings forms for active BuddyPress core components. Then
 * add the privacy settings main navigation item and sub items to a user's profile.
 * To do this, we check to see if a given BP Core component and its corresponding
 * privacy item are enabled.
 *
 * @var string $default_function is the default value to the 'screen_function' key passed into bp_core_new_nav_item()
 * @var string $default_subnav is the default value to the 'default_subnav_slug' key passed into bp_core_new_nav_item()
 * @global $bp The global BuddyPress settings variable created in bp_core_setup_globals()
 * @global $bp_authz_settings The global privacy settings array variable
 * @global $bp_authz_lockdown The current Site Lockdown Control set by Site Admin
 * @global $bp_authz_redirect_page The special theme slug registered as a root component that will be used in redirect
 * @uses bp_core_new_nav_item to create the main privacy navigation menu
 *
 * @version 1.0
 * @since 1.0-RC1
 */

function bp_authz_load_settings_files_and_add_settings_nav() {
	global $bp, $bp_authz_settings, $bp_authz_lockdown, $bp_authz_redirect_page;

	/* Load any settings files with active privacy groups. But first
	 * check to see if site-wide privacy is enabled or disabled.
	 */
	if ( $bp_authz_settings[ 'site_wide' ] == 0 ) {
		define( 'BP_AUTHZ_DISABLED', 1 );

	/* BPAuthz is enabled; now check to see which privacy groups
	 * are enabled and load their setting file.
	 */
	} else {
		define( 'BP_AUTHZ_DISABLED', 0 );

		// Check for deactivated BP Core component before including each privacy settings form

		/* See below comment for why we're using function_exist() instead of isset()
		 * for just the first check below.
		 */
		if ( function_exists( 'xprofile_install' ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'profile' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-profile-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-profile-settings.php' );
			}
		}

		if( isset( $bp->active_components['activity'] ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'activity' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-activity-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-activity-settings.php' );
			}
		}

		if( isset( $bp->active_components['friends'] ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'friends' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-friends-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-friends-settings.php' );
			}
		}

		if( isset( $bp->active_components['messages'] ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'messages' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-messages-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-messages-settings.php' );
			}
		}

		if( isset( $bp->active_components['blogs'] ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'blogs' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-blogs-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-blogs-settings.php' );
			}
		}

		if( isset( $bp->active_components['groups'] ) ) {
			if ( $bp_authz_settings[ 'privacy_filtering' ][ 'groups' ] == 1 && file_exists( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-groups-settings.php') ) {
				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-groups-settings.php' );
			}
		}

	}

	// Populate Site Lockdown Control variables

	// Site is open to all
	if ( $bp_authz_settings[ 'lockdown' ] == 0 ) {

		// This lockdown status is not currently used
		$bp_authz_lockdown = 'open_to_all';

	// Users must be logged in to access site
	} elseif ( $bp_authz_settings[ 'lockdown' ] == 1 ) {

		$bp_authz_lockdown = 'logged_in';
		$bp_authz_redirect_page = BP_AUTHZ_CUSTOM_HOME_SLUG;

	// Site is in maintenance mode; only Site Admins can access
	} elseif ( $bp_authz_settings[ 'lockdown' ] == 2 ) {

		$bp_authz_lockdown = 'maintenance';
		$bp_authz_redirect_page = BP_AUTHZ_MAINTENANCE_SLUG;
	}

	/* For any BP Privacy filtering groupings that are disabled,
	 * set a constant for use in privacy filtering functions.
	 */

	if ( bp_privacy_filtering_active( 'profile' ) == false ) {
		define( 'BP_AUTHZ_XPROFILE_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_XPROFILE_DISABLED', 0 );
	}

	if ( bp_privacy_filtering_active( 'activity' ) == false  ) {
		define( 'BP_AUTHZ_ACTIVITY_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_ACTIVITY_DISABLED', 0 );
	}

	if ( bp_privacy_filtering_active( 'friends' ) == false  ) {
		define( 'BP_AUTHZ_FRIENDS_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_FRIENDS_DISABLED', 0 );
	}

	if ( bp_privacy_filtering_active( 'messages' ) == false  ) {
		define( 'BP_AUTHZ_MESSAGES_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_MESSAGES_DISABLED', 0 );
	}

	if ( bp_privacy_filtering_active( 'blogs' ) == false  ) {
		define( 'BP_AUTHZ_BLOGSS_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_BLOGSS_DISABLED', 0 );
	}

	if ( bp_privacy_filtering_active( 'groups' ) == false  ) {
		define( 'BP_AUTHZ_GROUPS_DISABLED', 1 );
	} else {
		define( 'BP_AUTHZ_GROUPS_DISABLED', 0 );
	}

	//*** This filter may be enabled in a future version
	//apply_filters( 'bp_authz_add_settings_nav', $default_function, $default_subnav );

	$default_subnav = null;

	/* Here we have to check for the xprofile_install function since
	 * BuddyPress uses the 'profile' key name for registering either
	 * the xprofile or the WordPress profile in the active components
	 * array. Therefore, checking for !isset( $bp->active_components['profile'] )
	 * will always fail as it will be set whether or not the xprofile
	 * component is activated.
	 *
	 * This should be reported as a bug in BuddyPress Trac. So, if you
	 * are reading this, then figure out the details and report it. I'm
	 * too tiered to do so. Besides, if you've discovered this, then
	 * you're starting to figure out how BP Privacy works. Congratulations!
	 */

	//check to see if xprofile component and profile privacy filtering are both activated
	if ( function_exists( 'xprofile_install' ) && bp_privacy_filtering_active( 'profile' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_profile_privacy';
			$default_subnav = 'profile-privacy';
		};
	}

	/* For the remaining checks, we can count on using the isset() function to
	 * give us an accurate accounting of whether or not a given BP core
	 * component is active.
	 */

	//check to see if activity component and activity privacy filtering are both activated
	if( isset( $bp->active_components['activity'] ) && bp_privacy_filtering_active( 'activity' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_activity_privacy';
			$default_subnav = 'activity-privacy';
		};
	}

	//check to see if friends component and friends privacy filtering are both activated
	if( isset( $bp->active_components['friends'] ) && bp_privacy_filtering_active( 'friends' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_friends_privacy';
			$default_subnav = 'friends-privacy';
		};
	}

	//check to see if messages component and messages privacy filtering are both activated
	if( isset( $bp->active_components['messages'] ) && bp_privacy_filtering_active( 'messages' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_messaging_privacy';
			$default_subnav = 'messaging-privacy';
		};
	}

	//check to see if blog component and blogs privacy filtering are both activated
	if( isset( $bp->active_components['blogs'] ) && bp_privacy_filtering_active( 'blogs' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_blogs_privacy';
			$default_subnav = 'blogs-privacy';
		};
	}

	//check to see if group component and groups privacy filtering are both activated
	if( isset( $bp->active_components['groups'] ) && bp_privacy_filtering_active( 'groups' ) ) {
		if( is_null( $default_subnav ) ) {
			$default_function = 'bp_authz_screen_groups_privacy';
			$default_subnav = 'groups-privacy';
		};
	}

	/* Just in case Site Admin has disabled all privacy objects but for some reason
	 * did not disable overall privacy filtering we need to set a catch variable so
	 * that the privacy menu does not get displayed
	 */
	if( is_null( $default_subnav ) ) {
		define( 'BP_AUTHZ_PSEUDO_DISABLED', 1 );
		//echo "Default is null; disabled = " . BP_AUTHZ_PSEUDO_DISABLED;
	} else {
		define( 'BP_AUTHZ_PSEUDO_DISABLED', 0 );
	};

	/* Add the privacy settings navigation item if privacy is enabled */
	if( BP_AUTHZ_DISABLED == 0 && BP_AUTHZ_PSEUDO_DISABLED == 0 ) {
		bp_core_new_nav_item( array( 'name' => __( 'Privacy', BP_AUTHZ_PLUGIN_NAME ), 'slug' => $bp->authz->slug, 'position' => 90, 'show_for_displayed_user' => false, 'screen_function' => $default_function, 'default_subnav_slug' => $default_subnav ) );
	};

	do_action( 'bp_authz_add_settings_nav', $default_function, $default_subnav );

}
add_action( 'bp_setup_globals', 'bp_authz_load_settings_files_and_add_settings_nav', 11 );

//*** This add_action() used to work in an older version of BuddyPress
//add_action( 'bp_setup_nav', 'bp_authz_load_settings_files_and_add_settings_nav' );

?>