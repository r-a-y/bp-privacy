<?php
/*
Plugin Name: BuddyPress Privacy Component (BP Privacy, BP_Authz, or BPAz)
Plugin URI: http://buddypress.org/community/groups/bp-privacy/
Description: BuddyPress Privacy is a privacy control component for BuddyPress' Core Components. It provides a site's users a mechanism with which to control who has access to which pieces of their BuddyPress Core-generated personal data. This plugin is a release candidate version to be used only in a development sandbox and not in a production environment. Use at your own risk.
Version: 1.0-RC1
Revision Date: February 16, 2011
Requires at least: PHP 5.2.x, WordPress 3.0.5, BuddyPress 1.2.7
Tested up to: PHP 5.2.x, WordPress 3.0.5, BuddyPress 1.2.7
License: GNU General Public License 2.0 (GPL) or any later version
Author: Jeff Sayre
Author URI: http://jeffsayre.com/
Network: true

Copyright 2009 - 2011 Jeff Sayre and SayreMedia, Inc.

This plugin is a release candidate version to be used only in a development
sandbox and not in a production environment. Use at your own risk. This 
plugin is also not being developed or supported anymore by the author.
It is released to the BuddyPress community for it to be adopted and further
developed.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2.0 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

ADDITIONAL DISCLAIMERS, TERMS AND CONDITIONS, and NOTICES:

See disclaimer.txt which is distributed with this plugin

*/

/*************************************************************************
 * This file performs barebones initialization of the Privacy Component
 * DO NOT MODIFY OR UNIVERSE WILL END. YOU WOULDN'T WANT THAT, WOULD YOU?
 ************************************************************************/

/**
 * bpaz_init()
 *
 * Initialize basic constants and make sure BuddyPress
 * is installed and activated. If true, then allow for 
 * Privacy Component to finish loading.
 *
 * @since 0.4
 */
function bpaz_init() {
		
	/* Define the component's parent folder name */
	define( 'BP_AUTHZ_PLUGIN_NAME', 'bp-privacy' );
	
	/* Define component's directory and URL Paths */
	define( 'BP_AUTHZ_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . BP_AUTHZ_PLUGIN_NAME );
	define( 'BP_AUTHZ_PLUGIN_URL', WP_PLUGIN_URL . '/' . BP_AUTHZ_PLUGIN_NAME );
	
	/* BuddyPress is installed and activated, finish initialization and go! */
	require_once( BP_AUTHZ_PLUGIN_DIR . '/bp-authz-core.php' );
	
	/**
	 * Privacy Action Hook
	 *
	 * This hook allows those plugins that are dependent on 
	 * the BuddyPress Privacy Component to hook into it in a safe
	 * manner -- only when it is installed and activated. If your
	 * plugin extends privacy filtering to its services, then make
	 * sure the first action to which you tie your privacy functions
	 * is this one. If privacy is active, then your function(s) will fire.
	 *
	 * Alternatively, you can check for the existence of bpaz_init() and
	 * only include your privacy services if it exists.
	 */
	
	do_action( 'bp_authz_init' );
	
}
add_action( 'bp_include', 'bpaz_init', 1 );


/**
 * bp_authz_plugin_activated()
 *
 * Register plugin activation with WordPress and
 * set the activation hook.
 *
 * @since 0.4
 */
function bp_authz_plugin_activated() {
	do_action( 'bp_authz_loader_activate' );
}
register_activation_hook( 'bp-privacy/bp-authz-loader.php', 'bp_authz_plugin_activated' );


/**
 * bp_authz_plugin_deactivated()
 *
 * Register plugin deactivation with WordPress and
 * set the deactivation hook.
 *
 * @since 0.4
 */
function bp_authz_plugin_deactivated() {
	if ( !function_exists( 'delete_site_option') )
		return false;
	
	/* See the following in the Developer's Guide section of the BuddyPress 
	 * Privacy Manual for more details:
	 *
	 * 	- Resetting BP Privacy's Metadata Settings
	 */

	/* Future Version: offer a setting in Admin menu to indicate
	 * whether Admin wishes meta data to be automatically purged
	 * from the WordPress’ metadata tables upon plugin deactivation.
	 * This is a different issue than what should happen upon
	 * plugin uninstallation.
	 */

	/* If you enable the code below, then you will have to reset
	 * any previously disabled Admin privacy objects. In other words,
	 * when you reactivate the privacy component, it will run with
	 * all privacy objects fully active. By keeping this code
	 * disabled, you can easily deactivate privacy filtering and
	 * then reactivate it without losing any previous settings.
	 */
	/*
	delete_site_option( 'bp-authz-db-version' );
	delete_site_option( 'bp-privacy-components-deactivated' );
	delete_site_option( 'bp-privacy-acceptance' );
	delete_option( 'bp_authz_admin_settings_options' );
	*/
	
	do_action( 'bp_authz_loader_deactivate' );
}
register_deactivation_hook( 'bp-privacy/bp-authz-loader.php', 'bp_authz_plugin_deactivated' );

?>