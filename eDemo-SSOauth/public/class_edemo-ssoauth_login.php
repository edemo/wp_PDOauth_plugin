<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
 
/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
class eDemo_SSOauth_Login extends eDemo_SSOauth_Base {
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	public $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	public $version;
	/**
	 * The communications object
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      object    $com    An instance of the communicaation object.
	 */
	private $com;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		parent::__construct( );
		$this->com = new eDemo_SSOauth_com( $plugin_name, $version );
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/edemo-ssoauth_public.js', array( ), $this->version, false );
	}
	
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/public.css', array(), $this->version, 'all' );
	}
	
	/**
	 * Add SSO login area to the bottom of login screen
	 *
	 * Add login button and register button.
	 * The ability is controlled trough the 'allowLogin' and 'allowRegister' options.
	 *
	 * @since    0.0.1
	 */	
	function login_page_extension() { ?>
	<div style="width: 320px; margin: 20px auto; display: table; background: #FFF none repeat scroll 0% 0%; box-shadow: 0px 1px 3px rgba(0, 0, 0, 0.13);">
		<div style="margin: 26px 24px 26px;">
		<h3 align="center" style="margin-bottom: 15px;"><?= __('SSO login',eDemo_SSOauth::TEXTDOMAIN) ?></h3>
		<div class="button <?= ($this->allowLogin)?'':'disabled'?>">
	<?php if ($this->allowLogin) {?>
			<a href="<?= $this->get_button_action('login')    ?>"><?=__( 'SSO login', eDemo_SSOauth::TEXTDOMAIN );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO login', eDemo_SSOauth::TEXTDOMAIN );?>
	<?php }?>
		</div>
		<div class="button <?= ($this->allowLogin and $this->allowRegister)?'':'disabled'?>" width="50%">
	<?php if ($this->allowRegister and $this->allowLogin) {?>
			<a href="<?= $this->get_button_action('register') ?>"><?=__( 'SSO register', eDemo_SSOauth::TEXTDOMAIN );?></a>
	<?php }
	else { ?>
			<?=__( 'SSO register', eDemo_SSOauth::TEXTDOMAIN );?>
	<?php }?>
		</div>
		<p style="margin-top: 15px;"><?= ($this->allowLogin)?'':__('Sorry! Login with SSO service isn\'t allowed temporarily.', eDemo_SSOauth::TEXTDOMAIN)?></p>
	</div></div>
	<div id="<?=eDemo_SSOauth::MESSAGE_FRAME_ID?>" class="message-frame"></div>
	<?php 
	}
}
?>