<?php
/**
* eDemo SSO auth plugin - widgets
* 
* - login widget
*
* @since 0.1
*
*/

### login widget

class eDemoSSO_login extends WP_Widget {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( false, 'eDemoSSO_login' );
	}

	function widget( $args, $instance ) { 
		// Widget output
		
		global $eDemoSSO;

		$title = apply_filters( 'widget_title', $instance['title'] );
		$current_user = wp_get_current_user(); 
?>
		<?= str_replace('class="widget widget_edemosso_login', 'class="widget widget_links', $args['before_widget']) ?>
<?php

		// display title
		if ( ! empty( $title ) ) echo $args['before_title'] . $title . $args['after_title']; ?>
		<ul>
<?php

		// welcome message 
		if ( is_user_logged_in() ) { 
?>
			<p><?= __('Welcome ','eDemo-SSO').$current_user->display_name.'!' ?></p>
<?php
		}
		
		// container for notices
		$eDemoSSO->notice();

		// section for logged in users
		if ( is_user_logged_in() ) { 
			if ($eDemoSSO->is_bind_allowed() and !$eDemoSSO->has_user_SSO($current_user->ID)) { 
?>
			<li><a href="<?= $eDemoSSO->get_SSO_action_link('binding') ?>"><?= __('Bind SSO account','eDemo-SSO')?></a></li>
<?php
			}
?>
			<li><a href="<?= $eDemoSSO->get_SSO_action_link('refresh') ?>"><?= __('Refresh SSO data','eDemo-SSO')?></a></li>	
			<li><a href="/wp-admin/profile.php"><?=__('Show user profile', 'eDemo-SSO')?></a></li>
			<li><a href="<?=wp_logout_url( urldecode($_SERVER['REQUEST_URI']) )?>"><?= __('Logout', 'eDemo-SSO')?></a></li>
<?php	
		}
		
		// section for visitors
		elseif ( $eDemoSSO->is_login_allowed() ) { 
?>
			<li><a href="<?= $eDemoSSO->get_SSO_action_link('login')    ?>"><?= __('Login with SSO', 'eDemo-SSO')    ?></a></li>
<?php
			if ( $eDemoSSO->is_register_allowed() ) { 
?>
			<li><a href="<?= $eDemoSSO->get_SSO_action_link('register') ?>"><?= __('Register with SSO', 'eDemo-SSO') ?></a></li>
<?php
			}
		} 
		else {
?>
			<p><?= __('Sorry! Login with SSO service isn\'t allowed temporarily.', 'eDemo-SSO') ?></p>
<?php
		}
		
		//section for all 
?>
			<li><a href="<?= $eDemoSSO->get_SSO_site_url() ?>"><?= __('SSO services', 'eDemo-SSO')?></a></li>
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
		else $title = __( 'New title', 'eDemo-SSO' );
		
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