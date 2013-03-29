<?php /* This template is only used in conjunction with the Privacy Component 
		it must be copied over to Buddypress' /bp-default or your child-theme directory
		*/ ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

<head>

	<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />

	<title><?php bp_page_title() ?></title>

	<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />

	<?php //wp_head(); ?>

</head>

<body>

	<div id="header">
		<h1 id="logo"><a href="<?php echo site_url() ?>" title="<?php _e( 'Home', 'buddypress' ) ?>"><?php bp_site_name() ?></a></h1>
	</div>

	<div id="container">

		<div id="content">
			
			<div class="padder">
				
				<h3><?php _e( 'Please Pardon Our Improvements!', BP_AUTHZ_PLUGIN_NAME ); ?></h3>
				
				<?php 
				
					do_action( 'bp_privacy_before_maintenance_message' );
				
					$custom_maintenance_message = __( 'Our site is currently undergoing maintenance and will be back up shortly. Please return in a few hours.', BP_AUTHZ_PLUGIN_NAME );
					
					$custom_maintenance_message = apply_filters( 'bp_authz_maintenance_message_text', $custom_maintenance_message );
				
				 ?>
				
				<p><?php echo $custom_maintenance_message; ?></p>
	
				<?php do_action( 'bp_privacy_after_maintenance_message' ); ?>
	
			</div><!-- .padder -->
							
		</div><!-- #content -->
	
	</div><!-- #container -->

</body>