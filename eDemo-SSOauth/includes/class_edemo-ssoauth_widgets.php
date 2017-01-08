<?php
/**
* eDemo SSO auth plugin - widgets
* 
* - login widget
*
* @since 0.0.1
*
*/

### login widget

class eDemo_SSOauth_login_widget extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		$this->widget_vars = array ( 
			'common'		=> new eDemo_SSOauth_Base(),
			'allowBind'		=> get_option('eDemoSSO_allowBind'),
			'allowRegister' => get_option('eDemoSSO_allowRegister'),
			'allowLogin'	=> get_option('eDemoSSO_allowLogin')
								);
		parent::__construct( false, 'eDemo_SSOauth_login_widget' );
	}

	function widget( $args, $instance ) { 
		// Widget output
		
		extract( $args );
		extract( $this->widget_vars );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$current_user = wp_get_current_user(); 
?>
		<?= str_replace('class="widget widget_edemosso_login', 'class="widget widget_links', $before_widget) ?>
<?php

		// display title
		if ( ! empty( $title ) ) echo $before_title . $title . $after_title; ?>
		<ul>
<?php

		// welcome message 
		if ( is_user_logged_in() ) { 
?>
			<p><?= __('Welcome ',eDemo_SSOauth::TEXTDOMAIN).$current_user->display_name.'!' ?></p>
<?php
		}
		
		// container for notices
		$common->notice();

		// section for logged in users
		if ( is_user_logged_in() ) { 
			if ($allowBind and !$common->has_user_SSO($current_user->ID)) { 
?>
			<li><a href="<?= $common->get_button_action('binding') ?>"><?= __('Bind SSO account',eDemo_SSOauth::TEXTDOMAIN)?></a></li>
<?php
			}
?>
			<li><a href="<?= $common->get_button_action('refresh') ?>"><?= __('Refresh SSO data',eDemo_SSOauth::TEXTDOMAIN)?></a></li>	
			<li><a href="/wp-admin/profile.php"><?=__('Show user profile', eDemo_SSOauth::TEXTDOMAIN)?></a></li>
			<li><a href="<?=wp_logout_url( get_permalink())?>"><?= __('Logout', eDemo_SSOauth::TEXTDOMAIN)?></a></li>
<?php	
		}
		
		// section for visitors
		elseif ( $allowLogin ) { 
?>
			<li><a href="<?= $common->get_button_action('login') ?>"><?= __('Login with SSO', eDemo_SSOauth::TEXTDOMAIN)    ?></a></li>
<?php
			if ( $allowRegister ) { 
?>
			<li><a href="<?= $common->get_button_action('register') ?>"><?= __('Register with SSO', eDemo_SSOauth::TEXTDOMAIN) ?></a></li>
<?php
			}
		} 
		else {
?>
			<p><?= __('Sorry! Login with SSO service isn\'t allowed temporarily.', eDemo_SSOauth::TEXTDOMAIN) ?></p>
<?php
		}
		
		//section for all 
?>
			<li><a href="https://<?= (get_option('eDemoSSO_serviceURI').eDemo_SSOauth::SSO_SITE_URL) ?>"><?= __('SSO services', eDemo_SSOauth::TEXTDOMAIN)?></a></li>
		</ul>
		<?= $args['after_widget'] ?>
<?php
	}

	function update( $new_instance, $old_instance ) {
		// Save widget options
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

	function form( $instance ) {
		// Output admin widget options form
		
		// widget title
		if ( isset( $instance[ 'title' ] ) ) $title = $instance[ 'title' ];
		else $title = __( 'New title', eDemo_SSOauth::TEXTDOMAIN );
		
		// Widget admin form
?>
		<p>
			<label for="<?= $this->get_field_id( 'title' ); ?>"><?= __( 'Title:' ); ?></label>
			<input class="widefat" id="<?= $this->get_field_id( 'title' ); ?>" name="<?= $this->get_field_name( 'title' ); ?>" type="text" value="<?= esc_attr( $title ); ?>" />
		</p>
<?php
	}
}
?>