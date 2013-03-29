<?php /* This template is only used in conjunction with the Privacy Component 
		it must be copied over to Buddypress' /bp-default/registration or your 
		/registration directory of your child-theme
		*/ ?>

<?php get_header() ?>

	<div id="content">
		
		<div class="padder">
			
			<h3><?php _e( 'Privacy Policy', BP_AUTHZ_PLUGIN_NAME ); ?></h3>
			
			<?php 
			
				do_action( 'bp_privacy_before_privacy_policy' );
			
				$privacy_policy = __( 'This site uses a robust privacy filtering plugin that provides site members with fine, granular control over who has access to which pieces of their personal data. Site Administrators will always be able to see your content no matter your selected privacy settings. Group Administrators will always be able to see your group content no matter your selected privacy settings. Privacy settings and control may be changed by site owner from time to time without any notice.', BP_AUTHZ_PLUGIN_NAME );
				
				$privacy_policy = apply_filters( 'bp_authz_privacy_policy_text', $privacy_policy );
			
			 ?>
			
			<p><?php echo $privacy_policy; ?></p>

			<?php do_action( 'bp_privacy_after_privacy_policy' ); ?>

		</div><!-- .padder -->
						
	</div><!-- #content -->
	
	<?php locate_template( array( 'sidebar.php' ), true ) ?>

<?php get_footer() ?>