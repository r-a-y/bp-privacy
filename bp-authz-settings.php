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
 * @uses bp_core_new_nav_item to create the main privacy navigation menu
 *
 * @version 1.0
 * @since 1.0-RC1
 */

function bp_authz_load_settings_files_and_add_settings_nav() {
	global $bp, $bp_authz_settings;

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

		// setup default screen and subnav for main privacy nav item
		$default_screen = false;
		$default_subnav = false;

		/*
		// COMMENT OUT PROFILE PRIVACY FOR NOW
		//
		// - will probably conflict with BP Core's profile privacy
		// - needs investigation
		//
		// xprofile
		if ( bp_is_active( 'xprofile' ) ) {
			if ( bp_privacy_filtering_active( 'profile' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_profile_privacy';
					$default_subnav = 'profile-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-profile-settings.php' );
			}
		}
		*/

		// activity
		if( bp_is_active( 'activity' ) ) {
			if ( bp_privacy_filtering_active( 'activity' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_activity_privacy';
					$default_subnav = 'activity-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-activity-settings.php' );
			}
		}

		// friends
		if( bp_is_active( 'friends' ) ) {
			if ( bp_privacy_filtering_active( 'friends' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_friends_privacy';
					$default_subnav = 'friends-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-friends-settings.php' );
			}
		}

		// messages
		if( bp_is_active( 'messages' ) ) {
			if ( bp_privacy_filtering_active( 'messages' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_messaging_privacy';
					$default_subnav = 'messaging-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-messages-settings.php' );
			}
		}

		// blogs
		if( bp_is_active( 'blogs' ) ) {
			if ( bp_privacy_filtering_active( 'blogs' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_blogs_privacy';
					$default_subnav = 'blogs-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-blogs-settings.php' );
			}
		}

		// groups
		if( bp_is_active( 'groups' ) ) {
			if ( bp_privacy_filtering_active( 'groups' ) ) {
				if ( ! $default_screen ) {
					$default_screen = 'bp_authz_screen_groups_privacy';
					$default_subnav = 'groups-privacy';
				}

				include_once( BP_AUTHZ_SETTINGS_DIR . '/bp-authz-groups-settings.php' );
			}
		}

	}

	/* Just in case Site Admin has disabled all privacy objects but for some reason
	 * did not disable overall privacy filtering we need to set a catch variable so
	 * that the privacy menu does not get displayed
	 */
	if( ! $default_screen ) {
		define( 'BP_AUTHZ_PSEUDO_DISABLED', 1 );
		//echo "Default is null; disabled = " . BP_AUTHZ_PSEUDO_DISABLED;
	} else {
		define( 'BP_AUTHZ_PSEUDO_DISABLED', 0 );
	};

	/* Add the privacy settings navigation item if privacy is enabled */
	if( BP_AUTHZ_DISABLED == 0 && BP_AUTHZ_PSEUDO_DISABLED == 0 ) {
		bp_core_new_nav_item( array(
			'name'     => __( 'Privacy', BP_AUTHZ_PLUGIN_NAME ),
			'slug'     => $bp->authz->slug,
			'position' => 90,
			'show_for_displayed_user' => false,
			'screen_function'         => $default_screen,
			'default_subnav_slug'     => $default_subnav
		) );
	};

	do_action( 'bp_authz_add_settings_nav' );

}
add_action( 'bp_setup_nav', 'bp_authz_load_settings_files_and_add_settings_nav', 1 );

/**
 * Setup the WP Toolbar.
 *
 * @since 1.0-RC2
 */
function bp_authz_setup_admin_bar() {
	// Bail if this is an ajax request
	if ( defined( 'DOING_AJAX' ) )
		return;

	// Do not proceed if BP_USE_WP_ADMIN_BAR constant is not set or is false
	if ( !bp_use_wp_admin_bar() )
		return;

	// Prevent debug notices
	$wp_admin_nav = array();

	// Menus for logged in user
	if ( is_user_logged_in() ) {
		// Add the privacy settings nav item if privacy is enabled
		if( BP_AUTHZ_DISABLED == 0 && BP_AUTHZ_PSEUDO_DISABLED == 0 ) {
			global $bp;

			$privacy_link = trailingslashit( bp_loggedin_user_domain() . $bp->authz->slug );

			// "Privacy" parent nav menu
			$wp_admin_nav[] = array(
				'parent' => $bp->my_account_menu_id,
				'id'     => 'my-account-' . $bp->authz->id,
				'title'  => __( 'Privacy', BP_AUTHZ_PLUGIN_NAME ),
				'href'   => $privacy_link
			);

			/*
			// COMMENT OUT PROFILE PRIVACY FOR NOW
			//
			// - will probably conflict with BP Core's profile privacy
			// - needs investigation
			//
			// "Profile" subnav item
			if ( bp_is_active( 'xprofile' ) && bp_privacy_filtering_active( 'profile' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-xprofile',
					'title'  => __( 'Profile Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'profile-privacy' )
				);
			}
			*/

			// "Activity" subnav item
			if ( bp_is_active( 'activity' ) && bp_privacy_filtering_active( 'activity' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-activity',
					'title'  => __( 'Activity Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'activity-privacy' )
				);
			}

			// "Friends" subnav item
			if ( bp_is_active( 'friends' ) && bp_privacy_filtering_active( 'friends' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-friends',
					'title'  => __( 'Friends Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'friends-privacy' )
				);
			}

			// "Messages" subnav item
			if ( bp_is_active( 'messages' ) && bp_privacy_filtering_active( 'messages' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-messages',
					'title'  => __( 'Messaging Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'messaging-privacy' )
				);
			}

			// "Blogs" subnav item
			if ( bp_is_active( 'blogs' ) && bp_privacy_filtering_active( 'blogs' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-blogs',
					'title'  => __( 'Blogs Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'blogs-privacy' )
				);
			}

			// "Groups" subnav item
			if ( bp_is_active( 'groups' ) && bp_privacy_filtering_active( 'groups' ) ) {
				$wp_admin_nav[] = array(
					'parent' => 'my-account-' . $bp->authz->id,
					'id'     => 'my-account-' . $bp->authz->id . '-groups',
					'title'  => __( 'Groups Privacy', BP_AUTHZ_PLUGIN_NAME ),
					'href'   => trailingslashit( $privacy_link . 'groups-privacy' )
				);
			}
		}

	}

	// Filter the nav before adding
	$wp_admin_nav = apply_filters( 'bp_authz_toolbar', $wp_admin_nav );

	// Do we have Toolbar menus to add?
	if ( ! empty( $wp_admin_nav ) ) {
		global $wp_admin_bar;

		// Add each admin menu
		foreach( $wp_admin_nav as $admin_menu ) {
			$wp_admin_bar->add_menu( $admin_menu );
		}
	}

}
add_action( 'bp_setup_admin_bar', 'bp_authz_setup_admin_bar', 20 );
