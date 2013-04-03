<?php

/**
 * bp_authz_admin_settings_init()
 *
 * Admin settings options for BP Privacy. This settings form uses the WordPress Settings API: http://codex.wordpress.org/Settings_API
 *
 * @since 0.4
 */
function bp_authz_admin_settings_init() {
	register_setting( 'bp_authz_admin_settings_options', 'bp_authz_admin_settings_options', 'bp_authz_admin_settings_options_validate' );

	/* Add section heading details to variables for better translation results.
	 * This allows the CSS markup to be extracted from the translation string.
	 */
	$bp_authz_admin_section_heading = '<div id="bpaz_settings">' . __( 'Site-wide Privacy Control', BP_AUTHZ_PLUGIN_NAME ) . '</div>';
	$bp_authz_admin_section_heading2 = '<div id="bpaz_settings">' . __( 'Individual Component Privacy Filtering Control', BP_AUTHZ_PLUGIN_NAME ) . '</div>';
	$bp_authz_admin_section_heading3 = '<div id="bpaz_settings">' . __( 'Customize ACL Settings', BP_AUTHZ_PLUGIN_NAME ) . '</div>';
	$bp_authz_admin_section_heading4 = '<div id="bpaz_settings">' . __( 'Privacy Acceptance Checkbox', BP_AUTHZ_PLUGIN_NAME ) . '</div>';
	$bp_authz_admin_section_heading5 = '<div id="bpaz_settings">' . __( 'Site Lockdown Control', BP_AUTHZ_PLUGIN_NAME ) . '</div>';

	// Site-wide Privacy Control Settings Section
	add_settings_section( 'bp_authz_admin_enable_sitewide', $bp_authz_admin_section_heading, 'bp_authz_admin_enable_sitewide_section', 'bp_authz_admin_settings' );

	add_settings_field( 'bp_authz_admin_enable_sitewide_radio', __( 'Enable or Disable Site-wide Privacy:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_sitewide_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_sitewide' );


	// Individual Privacy Object Control Settings Section
	add_settings_section('bp_authz_admin_enable_individual_filtering', $bp_authz_admin_section_heading2, 'bp_authz_admin_enable_individual_filtering_section', 'bp_authz_admin_settings');

	// COMMENT OUT PROFILE PRIVACY FOR NOW
	//add_settings_field( 'bp_authz_admin_enable_profile_filtering_radio', __( 'Profile Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_profile_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );

	add_settings_field( 'bp_authz_admin_enable_activity_filtering_radio', __( 'Activity Stream Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_activity_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );

	add_settings_field( 'bp_authz_admin_enable_friends_filtering_radio', __( 'Friends Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_friends_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );

	add_settings_field( 'bp_authz_admin_enable_message_filtering_radio', __( 'Messaging Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_message_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );

	add_settings_field( 'bp_authz_admin_enable_blog_filtering_radio', __( 'Blogs Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_blog_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );

	add_settings_field( 'bp_authz_admin_enable_group_filtering_radio', __( 'Groups Privacy Filtering:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_enable_group_filtering_settings', 'bp_authz_admin_settings', 'bp_authz_admin_enable_individual_filtering' );


	// Customize ACL Settings Section
	add_settings_section( 'bp_authz_admin_set_acl', $bp_authz_admin_section_heading3, 'bp_authz_admin_set_acl_section', 'bp_authz_admin_settings' );

	add_settings_field( 'bp_authz_admin_set_acl_check', __( 'All Users:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check2', __( 'Logged in Users:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings2', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check3', __( 'Friends:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings3', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check4', __( 'Members of These Groups:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings4', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check5', __( 'These Users Only:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings5', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check6', __( 'Only Me:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings6', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );

	add_settings_field( 'bp_authz_admin_set_acl_check7', __( 'Current ACL Settings:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_set_acl_settings7', 'bp_authz_admin_settings', 'bp_authz_admin_set_acl' );


	// Privacy Acceptance Checkbox Settings Section
	add_settings_section('bp_authz_admin_privacy_accept', $bp_authz_admin_section_heading4, 'bp_authz_admin_privacy_accept_section', 'bp_authz_admin_settings');

	add_settings_field( 'bp_authz_admin_privacy_accept_check', __( 'Require Privacy Acceptance:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_privacy_accept_settings', 'bp_authz_admin_settings', 'bp_authz_admin_privacy_accept' );


	// Site Lockdown Control Settings Section
	add_settings_section('bp_authz_admin_site_lockdown', $bp_authz_admin_section_heading5, 'bp_authz_admin_site_lockdown_section', 'bp_authz_admin_settings');

	add_settings_field( 'bp_authz_admin_site_lockdown_radio', __( 'Access to Site:', BP_AUTHZ_PLUGIN_NAME ), 'bp_authz_admin_site_lockdown_settings', 'bp_authz_admin_settings', 'bp_authz_admin_site_lockdown' );

	do_action( 'bp_authz_admin_settings_action' );
}
add_action( 'admin_init', 'bp_authz_admin_settings_init' );


/**
 * bp_authz_admin_settings_options()
 *
 * Displays the admin privacy control settings screen when an admin
 * user clicks on the 'Privacy Settings' submenu under the
 * BuddyPress menu group in the backend
 *
 * @since 0.4
 */
function bp_authz_admin_settings_options() {
	global $bp_authz_settings;

	echo '<style type="text/css">

		#bpaz_wrap #bpaz_settings {
			margin: 10px 0px 0px 10px;
		}

		#bpaz_settings .sidebar {
			width: 275px;
			float: left;
			padding: 15px 15px 40px 10px;
			margin: 0px 20px 0px 0px;
			border-right: 2px solid #cccccc;
		}

		#bpaz_settings .settings {
			float: left;
			width: 625px;
			margin: 0px 15px 0px 0px;
		}

		.settings #api-nag {
			font-weight: bold;
			text-align: center;
			background-color: #fffeeb;
			color: #FF8000;
			margin: 10px;
			padding: 10px;
		}

		.settings input {
			font-weight: bold;
		}

		#bpaz_settings h3 {
			color: black;
			background-color: #cccccc;
			padding: 1px 0px 8px 5px;
		}

		#bpaz_settings .enable {
			/*padding: 0px 10px 0px 10px;*/
		}

		.enable .radio_group {
			width: 125px;
			text-align: right;
		}

		.sidebar .section {
			font-size: 1.1em; /* 1.1px; */
			font-weight: bold;
			color: black;
			background-color: #cccccc;
			margin: 0px 0px 10px 0px;
			padding: 10px 0px 10px 10px;
		}

		.wordpress_donate {
			text-align: center;
			margin: 10px 0px 20px 0px;
			padding: 5px 10px 5px 10px;
			border: 1px solid gray;
		}

		.paypal_button table {
			margin: 10px 0px 15px 30px;
		}

		.sidebar .copy {
			text-align: center;
			font-size: .85em;
			margin: 10px 0px 0px 0px;
		}

	</style>';
?>
	<div id="bpaz_wrap">

		<div id="bpaz_settings">

			<div class="sidebar">

				<div class="section">
					<?php _e( 'Plugin Information', BP_AUTHZ_PLUGIN_NAME ) ?>
				</div>

				<?php
				echo "<div class='copy'><p>" . __( 'Version', BP_AUTHZ_PLUGIN_NAME ) . ": " .  BP_AUTHZ_VERSION . "<br />" . __( 'Requires', BP_AUTHZ_PLUGIN_NAME ) . ": " .  BP_AUTHZ_REQUIREMENTS . "<br />" . __( 'Suitability', BP_AUTHZ_PLUGIN_NAME ) . ": " .  BP_AUTHZ_SUITABLE . "<br />" . __( 'Licensed Under', BP_AUTHZ_PLUGIN_NAME ) . ": <a href='http://www.gnu.org/licenses/gpl-2.0.html'> GPL 2.0 or later version</a><br />&copy; Copyright 2009-2011 <a href='http://jeffsayre.com'>Jeff Sayre</a></p></div>";
				?>

				<div class="section">
					<?php _e( 'Support the WordPress Foundation', BP_AUTHZ_PLUGIN_NAME ) ?>
				</div>

				<div class="wordpress_donate">
					<p>Click the WordPress logo to donate!</p>
					<a href="http://wordpressfoundation.org/donate/"><img src="http://s.wordpress.org/about/images/logo-blue/blue-xl.png" alt="WordPress Logo" /></a>
					<p><em><?php _e( 'If you are a corporate user, consultant, plugin developer, or theme designer and profit from using this plugin, WordPress, and BuddyPress, then please consider donating to the WordPress Foundation. Thank you!', BP_AUTHZ_PLUGIN_NAME ) ?></em></p>
				</div>

				<div class="section">
					<?php _e( 'BuddyPress Privacy Resources', BP_AUTHZ_PLUGIN_NAME ) ?>
				</div>

				<a href="http://code.google.com/p/bp-privacy/">BuddyPress Privacy Repo on Google Code</a>
				<br /><br />
				<a href="http://jeffsayre.com/2011/01/19/bp-privacy-history-and-lessons-learned-from-developing-a-major-buddypress-component/">BP Privacy: History and Lessons Learned</a>
				<br /><br />
				<a href="http://jeffsayre.com/2009/12/21/oauth-buddypress-and-privacy/">OAuth, BuddyPress, and Privacy</a>
				<br /><br />
				<a href="http://jeffsayre.com/2009/12/05/buddypress-authentication-versus-authorization/">Authentication versus authorization</a>
				<br /><br />

			</div>

			<div class="settings">

				<h2><?php _e( 'BuddyPress Privacy Settings', BP_AUTHZ_PLUGIN_NAME ) ?></h2>

				<p><?php _e( 'The BuddyPress Privacy Component and all its filters are enabled by default. Below are settings to give you fine-grained control over the Privacy Component.', BP_AUTHZ_PLUGIN_NAME ) ?></p>

				<form action="options.php" method="post">

					<?php

					settings_fields( 'bp_authz_admin_settings_options' );

					do_settings_sections( 'bp_authz_admin_settings' );

					?>

					<input name="bp-authz-admin-options-submit" type="submit" value="<?php esc_attr_e( 'Save Settings', BP_AUTHZ_PLUGIN_NAME ); ?>" />
					<?php echo "<br /><br />"; ?>

				</form>
			</div>
		</div>
	</div>

<?php
}


/**
 * bp_authz_admin_settings_options_validate()
 *
 * Validates all set options before saving to DB
 *
 * @since 0.4
 */
function bp_authz_admin_settings_options_validate() {

	/* If the form has been submitted and the admin referrer checks out, save the settings */
	if( isset( $_POST[ 'bp-authz-admin-options-submit' ] ) && isset( $_POST[ 'bp_authz_admin_settings_options' ] ) ) {

		if( !check_admin_referer( 'bp_authz_admin_settings_options-options' ) )
			return false;

		// for additional security
		$_POST[ 'bp_authz_admin_settings_options' ] = array_map( 'stripslashes_deep', $_POST[ 'bp_authz_admin_settings_options' ] );

		$bp_authz_admin_options_new = $_POST[ 'bp_authz_admin_settings_options' ];

		do_action( 'bp_authz_admin_settings_options_validate', $bp_authz_admin_options_new );

		return $bp_authz_admin_options_new;
	}
}


/*******************************************************
 * Site-wide Privacy Control Settings Section Functions
 ******************************************************/

/**
 * bp_authz_admin_enable_sitewide_section()
 *
 * Outputs the text seen under the 'Site-wide Privacy Control' section heading
 *
 * @since 0.4
 */
function bp_authz_admin_enable_sitewide_section() {
	echo "<p>" . __( 'If you need to temporarily disable site-wide privacy, you can do so below. Disabling site-wide privacy will remove the privacy settings navigation menu from each user&#39;s settings menu. It also disables all the privacy filtering functions. However, it does not deactivate the privacy plugin.', BP_AUTHZ_PLUGIN_NAME ) . "</p>";
}


/**
 * bp_authz_admin_enable_sitewide_settings()
 *
 * Outputs the "Enable" and "Disable" radio button field group
 *
 * @since 0.4
 */
function bp_authz_admin_enable_sitewide_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[site_wide]' value='1'" . ( $bp_authz_settings[ 'site_wide' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[site_wide]' value='0'" . ( $bp_authz_settings[ 'site_wide' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/***************************************************************
 * Individual Privacy Object Control Settings Section Functions
 **************************************************************/

/**
 * bp_authz_admin_enable_individual_filtering_section()
 *
 * Outputs the text seen under the 'Individual Component Privacy Filtering Control' section heading
 *
 * @since 0.4
 */
function bp_authz_admin_enable_individual_filtering_section() {
	echo "<p>" . __( 'In addition to enabling or disabling site-wide privacy, you have control over individual BuddyPress component privacy filtering. You can choose which privacy features you want to offer to your users. NOTE: As the Site Administrator, you will always be able to see each user&#39;s complete content with the exception of private groups where you are not a member.', BP_AUTHZ_PLUGIN_NAME ) . "</p>";
}


/**
 * bp_authz_admin_enable_profile_filtering_settings()
 *
 * Outputs the radio group to enable/disable Profile Privacy Filtering.
 * Allows a user to manage individual privacy settings for xprofile
 * groups and fields.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_profile_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][profile]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'profile' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][profile]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'profile' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/**
 * bp_authz_admin_enable_activity_filtering_settings()
 *
 * Outputs the radio group to enable/disable Activity Stream Privacy Filtering.
 * Allows a user to manage individual privacy settings for each unique
 * component action in their activity stream.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_activity_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][activity]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'activity' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][activity]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'activity' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/**
 * bp_authz_admin_enable_friends_filtering_settings()
 *
 * Outputs the radio group to enable/disable Friends Privacy Filtering.
 * Allows a user to manage who sees their friends list and who can
 * request friendship.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_friends_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][friends]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'friends' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][friends]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'friends' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/**
 * bp_authz_admin_enable_message_filtering_settings()
 *
 * Outputs the radio group to enable/disable Messaging Privacy Filtering.
 * Allows a user to manage who has the rights to send them a private message.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_message_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][messages]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'messages' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][messages]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'messages' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/**
 * bp_authz_admin_enable_blog_filtering_settings()
 *
 * Outputs the radio group to enable/disable Blogs Privacy Filtering.
 * Allows a user to manage who has the rights to see their blogs.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_blog_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][blogs]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'blogs' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][blogs]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'blogs' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/**
 * bp_authz_admin_enable_group_filtering_settings()
 *
 * Outputs the radio group to enable/disable Groups Privacy Filtering.
 * The groups privacy filter allows a user to manage who sees which
 * groups they belong to. Members within the group still see the user.
 *
 * @since 0.4
 */
function bp_authz_admin_enable_group_filtering_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><p><h4>";
	echo "<label>" . __( 'Enabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][groups]' value='1'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'groups' ] == 1 ? ' checked' : '' ) . " /></label> &nbsp;&nbsp;";
	echo "<label>" . __( 'Disabled', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[privacy_filtering][groups]' value='0'" . ( $bp_authz_settings[ 'privacy_filtering' ][ 'groups' ] == 0 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div>";
}


/*******************************************
 * Customize ACL Settings Section Functions
 ******************************************/

/**
 * bp_authz_admin_set_acl_section()
 *
 * Outputs the text seen under the 'Customize ACL Settings' section heading
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_section() {
	echo "<p>" . __( 'The BuddyPress Privacy Component offers a rigorous, multilevel access control list (ACL) settings array that provides each member with the ability to decide how much or how little access they grant to each piece of datum they generate. As a Site Administrator, you may wish to limit the number of options your users have in setting access rights.', BP_AUTHZ_PLUGIN_NAME ) . "</p>";

	echo "<p>" . __( 'By default, all ACL-settings levels are selected. <strong>Please Be Advised:</strong> if you start off by offering all the ACL-settings levels and then later limit the number of levels, users who have selected a level for a piece of datum that no longer applies will have to reset access rights for that object. See the BuddyPress Privacy Manual for more details.', BP_AUTHZ_PLUGIN_NAME ) . "</p>";
}


/**
 * bp_authz_admin_set_acl_settings()
 *
 * Outputs the check box that allows Site Admin to include
 * the "All Users" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][All Users]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][All Users]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'All Users' ] == true ? ' checked' : '' ) . " />";
	}
}


/**
 * bp_authz_admin_set_acl_settings2()
 *
 * Outputs the check box that allows Site Admin to include
 * the "Logged in Users" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings2() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Logged in Users]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Logged in Users]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'Logged in Users' ] == true ? ' checked' : '' ) . " />";
	}
}


/**
 * bp_authz_admin_set_acl_settings3()
 *
 * Outputs the check box that allows Site Admin to include
 * the "Friends" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings3() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Friends]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Friends]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'Friends' ] == true ? ' checked' : '' ) . " />";
	}
}


/**
 * bp_authz_admin_set_acl_settings4()
 *
 * Outputs the check box that allows Site Admin to include
 * the "Members of These Groups" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings4() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Members of These Groups]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Members of These Groups]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'Members of These Groups' ] == true ? ' checked' : '' ) . " />";
	}
}


/**
 * bp_authz_admin_set_acl_settings5()
 *
 * Outputs the check box that allows Site Admin to include
 * the "These Users Only" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings5() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][These Users Only]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][These Users Only]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'These Users Only' ] == true ? ' checked' : '' ) . " />";
	}
}


/**
 * bp_authz_admin_set_acl_settings6()
 *
 * Outputs the check box that allows Site Admin to include
 * the "Only Me" ACL level option
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings6() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'acl_levels' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Only Me]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[acl_levels][Only Me]' value='yes'" . ( $bp_authz_settings[ 'acl_levels' ][ 'Only Me' ] == true ? ' checked' : '' ) . " />";
	}

	// Cheap trick: Place a line between the ACL-settings levels and example settings dropdown box
	echo "<br />__________________________________";

}


/**
 * bp_authz_admin_set_acl_settings7()
 *
 * Outputs the example ACL-settings list dropdown box
 * to show Site Admin what users will see on the settings
 * screens.
 *
 * @since 0.4
 */
function bp_authz_admin_set_acl_settings7() {
	global $bp;

	_e( 'Save settings to see your changes reflected below.' . '<br />', BP_AUTHZ_PLUGIN_NAME ) ;

	$privacy_levels = $bp->authz->bpaz_acl_levels;

	echo "<select name='acl_level'>";

	foreach( $privacy_levels as $key => $value ) {

		foreach( $value as $key2 => $value2 ) {

			if( $key2 == 'level' ) {
				$acl_level = $value2;
			} else {
				if( $value2 == 0 ) {
					echo "<option disabled='disabled' value='$acl_level'>$key</option>";
				} else {
					echo "<option value='$acl_level'>$key</option>";
				};
			}
		}
	}

	echo "</select>";

}

/***********************************************
 * Privacy Acceptance Checkbox Settings Section
 **********************************************/

/**
 * bp_authz_admin_privacy_accept_section()
 *
 * Outputs the text seen under the 'Privacy Acceptance Checkbox' section heading
 *
 * @since 0.4
 */
function bp_authz_admin_privacy_accept_section() {
	echo "<p>" . __( 'If you have enabled privacy, it is a good idea to let newly-registering members know about your privacy policy and require that they accept it as part of the signup process. You can automatically place a checkbox on the registration form by checking the box below. Members must click the box, indicating their acceptance of your site&#039;s privacy policy. They will not be able to complete the signup process without checking the box. See the BuddyPress Privacy Manual for more details on setting your own custom Privacy Policy page.', BP_AUTHZ_PLUGIN_NAME ) . "</p>";
}


/**
 * bp_authz_admin_privacy_accept_settings()
 *
 * Outputs the checkbox that allows Site Admin to choose
 * whether or not to display an "Accept Privacy Policy"
 * checkbox on the registration form
 *
 * @since 0.4
 */
function bp_authz_admin_privacy_accept_settings() {
	global $bp_authz_settings;

	if( empty( $bp_authz_settings[ 'privacy_tos' ] ) ) {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[privacy_tos]' value='yes' />";
	} else {
		echo "<input type='checkbox' name='bp_authz_admin_settings_options[privacy_tos]' value='yes'" . ( $bp_authz_settings[ 'privacy_tos' ] == true ? ' checked' : '' ) . " />";
	}
}


/***************************************************
 * Site Lockdown Control Settings Section Functions
 **************************************************/

 /**
 * bp_authz_admin_site_lockdown_section()
 *
 * Outputs the text seen under the 'Site Lockdown Control' section heading
 *
 * @since 0.4
 */
function bp_authz_admin_site_lockdown_section() {
	echo "<p>" . __( 'As a Site Administrator, you may prefer not to let non-logged-in members access your network. Instead, you might want to send them to a custom homepage or directly to the registration page. At other times, you may need to do some heavy behind-the-scenes site maintenance and do not want anyone expect Site Admins to have access to the network. The default option is to present an open network to all. Choose which option you prefer below. See the BuddyPress Privacy Manual and installation directions for more details.', BP_AUTHZ_PLUGIN_NAME );
}


/**
 * bp_authz_admin_site_lockdown_settings()
 *
 * The _site_lockdown_settings is a checkbox group
 * with three choices. This function cre
 *
 * @since 0.4
 */
function bp_authz_admin_site_lockdown_settings() {
	global $bp_authz_settings;

	echo "<div id='bpaz_settings'><div class='enable'><div class='radio_group'><p><h4>";
	echo "<label>" . __( 'Open to All', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[lockdown]' value='0'" . ( $bp_authz_settings[ 'lockdown' ] == 0 ? ' checked' : '' ) . " /></label><br /><br />"; //&nbsp;&nbsp;
	echo "<label>" . __( 'Must be Logged in', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[lockdown]' value='1'" . ( $bp_authz_settings[ 'lockdown' ] == 1 ? ' checked' : '' ) . " /></label><br /><br />";

	echo "<label>" . __( 'Maintenance Mode', BP_AUTHZ_PLUGIN_NAME ) . "&nbsp;<input type='radio' name='bp_authz_admin_settings_options[lockdown]' value='2'" . ( $bp_authz_settings[ 'lockdown' ] == 2 ? ' checked' : '' ) . " /></label>";
	echo "</h4></p></div></div></div>";

}


/**
 * Setup BP-Privacy pages.
 *
 * We have to do some funny business to get BuddyPress to accept custom pages
 * on the "Settings > BuddyPress > Pages" screen.
 *
 * BuddyPress makes it easy to register a new directory page for components,
 * but in our case, we want multiple pages and our pages are not directories.
 *
 * So what we're doing here is emulating new components for each of our
 * privacy pages in the admin area only.
 *
 * We only add new pages if the BP-Privacy option in question is toggled.
 *
 * See {@link bp_authz_admin_slugs_options()} to view how the pages are displayed
 * on the "Settings > BuddyPress > Pages" screen.
 *
 * The alternative to this would have been to create our own validation and
 * DB option solely for privacy pages, but I didn't want to do that. :)
 *
 * @since 1.0-RC2
 */
function bp_authz_setup_pages() {
	global $bp, $bp_authz_settings;

	$pages = array();

	// privacy TOS check
	if ( ! empty( $bp_authz_settings['privacy_tos'] ) ) {
		$pages[ constant( 'BP_AUTHZ_PRIVACY_POLICY_SLUG' ) ] = __( 'Privacy Policy', BP_AUTHZ_PLUGIN_NAME );
	}

	switch( $bp_authz_settings['lockdown'] ) {

		// logged-in
		case 1 :
			$pages[ constant( 'BP_AUTHZ_CUSTOM_HOME_SLUG' ) ] = __( 'Landing Page for Non-logged-in Users', BP_AUTHZ_PLUGIN_NAME );

			break;

		// maintenance mode
		case 2 :
			$pages[ constant( 'BP_AUTHZ_MAINTENANCE_SLUG' ) ] = __( 'Maintenance Mode', BP_AUTHZ_PLUGIN_NAME );

			break;
	}

	// store temporary reference variable so we can access it later on the BP
	// "Pages" screen
	$bp_authz_settings['pages'] = $pages;

	// hack! in order for BuddyPress to recognize other pages that are not
	// directories, we have to declare each page as its own component
	//
	// not pretty...
	foreach ( $pages as $slug => $name ) {
		$bp->$slug = new stdClass;
		$bp->$slug->id                = $slug;
		$bp->$slug->slug              = $slug;
		$bp->$slug->name              = $name;
		$bp->$slug->has_directory     = true;
		$bp->loaded_components[$slug] = $slug;
	}
}
add_action( 'bp_admin_init', 'bp_authz_setup_pages', 20 );

/**
 * Output our custom pages on the "Settings > BuddyPress > Pages" screen.
 *
 * @since 1.0-RC2
 *
 * @see bp_authz_setup_pages()
 */
function bp_authz_admin_slugs_options() {
	global $bp_authz_settings;

	// see bp_authz_setup_pages() where this variable is initially setup
	$pages = $bp_authz_settings['pages'];

	if ( empty( $pages ) )
		return;

	// Get the existing WP pages
	$existing_pages = bp_core_get_directory_page_ids();

	// have to do this unfortunately due to the way the 'bp_active_external_pages'
	// hook is positioned
	echo '</tbody></table>';

	// the following markup is almost identical to bp_core_admin_slugs_options()
?>

		<h3><?php _e( 'Privacy', BP_AUTHZ_PLUGIN_NAME ); ?></h3>

		<p><?php _e( 'Associate WordPress Pages with the following BuddyPress Privacy pages.', BP_AUTHZ_PLUGIN_NAME ); ?></p>

		<table class="form-table">
			<tbody>

				<?php foreach ( $pages as $name => $label ) : ?>

					<tr valign="top">
						<th scope="row">
							<label for="bp_pages[<?php echo esc_attr( $name ) ?>]"><?php echo esc_html( $label ) ?></label>
						</th>

						<td>

							<?php if ( ! bp_is_root_blog() ) switch_to_blog( bp_get_root_blog_id() ); ?>

							<?php echo wp_dropdown_pages( array(
								'name'             => 'bp_pages[' . esc_attr( $name ) . ']',
								'echo'             => false,
								'show_option_none' => __( '- None -', 'buddypress' ),
								'selected'         => !empty( $existing_pages[$name] ) ? $existing_pages[$name] : false
							) ) ?>

							<a href="<?php echo admin_url( add_query_arg( array( 'post_type' => 'page' ), 'post-new.php' ) ); ?>" class="button-secondary"><?php _e( 'New Page', 'buddypress' ); ?></a>
							<input class="button-primary" type="submit" name="bp-admin-pages-single" value="<?php _e( 'Save', 'buddypress' ) ?>" />

							<?php if ( !empty( $existing_pages[$name] ) ) : ?>

								<a href="<?php echo get_permalink( $existing_pages[$name] ); ?>" class="button-secondary" target="_bp"><?php _e( 'View', 'buddypress' ); ?></a>

							<?php endif; ?>

							<?php if ( ! bp_is_root_blog() ) restore_current_blog(); ?>

						</td>
					</tr>

				<?php endforeach ?>

<?php
		// notice that the trailing '</tbody></table>' is omitted?
		// have to do this unfortunately due to the way the 'bp_active_external_pages'
		// hook is positioned

}
add_action( 'bp_active_external_pages', 'bp_authz_admin_slugs_options' );

?>