<?php /* This template is only used in conjunction with the Privacy Component 
		it must be copied over to Buddypress' /bp-default or your child-theme directory
		*/ ?>

<?php get_header() ?>

	<div id="content">
		
		<div class="padder">
			
			<h3><?php _e( 'Custom Home Theme Example', BP_AUTHZ_PLUGIN_NAME ); ?></h3>
			
			<?php 
			
				do_action( 'bp_privacy_before_custom_home' );
			
				$custom_home_message = __( 'This is an example of a custom BuddyPress welcome homepage that non-logged in users will see if you have set the Site Lockdown Control in BP Privacy Admin to Must be Logged in. Style away and redesign as you see fit.', BP_AUTHZ_PLUGIN_NAME );
				
				$custom_home_message = apply_filters( 'bp_authz_custom_home_text', $custom_home_message );
			
			 ?>
			
			<p><?php echo $custom_home_message; ?></p>

			<?php do_action( 'bp_privacy_after_custom_home' ); ?>

		</div><!-- .padder -->
						
	</div><!-- #content -->
	
	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>