<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/admin
 */
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/admin
 * @author     Claymanus
 */
class eDemo_SSOauth_Admin extends eDemo_SSOauth_Base {
	/**
	 * The ID of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;
	/**
	 * The version of this plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    0.0.1
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		parent::__construct( );
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
//		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/plugin-name-admin.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    0.0.1
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
//		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/plugin-name-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * to hiding admin notices if the current user isn't an admin
	 *
	 * @since    0.0.1
	 */
	function hide_update_notice_to_all_but_admin_users() {
		if (!current_user_can('update_core')) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}	
	}

	#
	# Options/admin panel
	#

	// Add page to options menu.
	function addAdminPage() 
	{
	  // Add a new submenu under Options:
		add_options_page('eDemo SSO Options', 'eDemo SSO', 'manage_options', 'edemosso', array( $this, 'displayAdminPage'));
	}

	// Display the admin page.
	function displayAdminPage() {
		
		if (isset($_POST['edemosso_update'])) {

			// Update options 
			$this->serviceURI 		= $_POST[ 'EdemoSSO_serviceURI' ];
			$this->sslverify		= isset($_POST['EdemoSSO_sslverify']);
			$this->appkey			= $_POST['EdemoSSO_appkey'];
			$this->secret			= $_POST['EdemoSSO_secret'];
			$this->appname			= $_POST['EdemoSSO_appname'];
			$this->allowBind		= isset($_POST['EdemoSSO_allowBind']);
			$this->allowRegister	= isset($_POST['EdemoSSO_allowRegister']);
			$this->allowLogin		= isset($_POST['EdemoSSO_allowLogin']);
			$this->default_role		= $_POST['EdemoSSO_default_role'];
			$this->hide_adminbar	= isset($_POST['EdemoSSO_hide_adminbar']);
			$this->needed_assurances= $_POST['EdemoSSO_needed_assurances'];
			$this->callback_uri		= $_POST['EdemoSSO_callback_uri'];

			
			update_option( 'eDemoSSO_serviceURI'   		, $this->serviceURI );
			update_option( 'eDemoSSO_secret'   			, $this->secret );
			update_option( 'eDemoSSO_appname'  			, $this->appname  );
			update_option( 'eDemoSSO_sslverify'			, $this->sslverify );
			update_option( 'eDemoSSO_allowBind'			, $this->allowBind );
			update_option( 'eDemoSSO_allowRegister'		, $this->allowRegister );
			update_option( 'eDemoSSO_allowLogin'		, $this->allowLogin );
			update_option( 'eDemoSSO_callback_uri'		, $this->callback_uri );
			update_option( 'eDemoSSO_hide_adminbar'		, $this->hide_adminbar );
			update_option( 'eDemoSSO_default_role'  	, $this->default_role );
			update_option( 'eDemoSSO_needed_assurances' , str_replace(' ', '', $this->needed_assurances) );

			// echo message updated		
			?>
			<div class='updated fade'><p><?= __('Options updated.',eDemo_SSOauth::TEXTDOMAIN) ?></p></div>
		<?php
		}		
		?>
		<div class="wrap">

			<h2><?= __( 'eDemo SSO Authentication Options', eDemo_SSOauth::TEXTDOMAIN ) ?></h2>
			<form method="post">
				<fieldset class='options'>
					<table class="form-table">
						<tr>
							<th>
								<label for="EdemoSSO_serviceURI"><?= __( 'SSO service URL:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='40' name='EdemoSSO_serviceURI' id='EdemoSSO_serviceURI' value='<?= get_option('eDemoSSO_serviceURI'); ?>' />
								<p class="description"><?= __( 'The base URL of the SSO service', eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_appname"><?= __( 'Application name:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_appname' id='EdemoSSO_appname' value='<?= get_option('eDemoSSO_appname'); ?>' />
								<p class="description"><?= __( 'Used for registering the application', eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_appkey"><?= __( 'Application key:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_appkey' id='EdemoSSO_appkey' value='<?= get_option('eDemoSSO_appkey'); ?>' />
								<p class="description"><?= __( 'Application key.', eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_secret"><?= __( 'Application secret:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_secret' id='EdemoSSO_secret' value='<?= get_option('eDemoSSO_secret'); ?>' />
								<p class="description"><?= __( 'Application secret.', eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_sslverify"><?= __( 'Allow verify ssl certificates:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_sslverify' id='EdemoSSO_sslverify' <?= (get_option('eDemoSSO_sslverify')?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the ssl certificates will be verified during the communication with sso server. Uncheck is recommended if your site has no cert, or the issuer isn't validated.", eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_callback_uri"><?= __( 'eDemo_SSO callback URL:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_callback_uri' id='EdemoSSO_callback_uri' value='<?= get_option('eDemoSSO_callback_uri') ?>' />
								<p class="description"><?=__('Callback uri for communication with the SSO_server', eDemo_SSOauth::TEXTDOMAIN)?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowBind"><?= __( 'SSO account binding:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowBind' id='EdemoSSO_allowBind' <?= (get_option('eDemoSSO_allowBind')?'checked':''); ?> />
								<p class="description"><?= __( "If this set, a SSO account can be binded with the given Wordpress account. User gets a 'bind' button on his datasheet and in the SSO login widget.", eDemo_SSOauth::TEXTDOMAIN) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowRegister"><?= __( 'Allow registering:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowRegister' id='EdemoSSO_allowRegister' <?= (get_option('eDemoSSO_allowRegister')?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the user registrating with SSO service.", eDemo_SSOauth::TEXTDOMAIN) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowLogin"><?= __( 'Allow sign in:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowLogin' id='EdemoSSO_allowLogin' <?= (get_option('eDemoSSO_allowLogin')?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the users logging in. In emergency case unset this option to forbid the users logging in", eDemo_SSOauth::TEXTDOMAIN) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_hide_adminbar"><?= __( 'Hide adminbar:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_hide_adminbar' id='EdemoSSO_hide_adminbar' <?= (get_option('eDemoSSO_hide_adminbar')?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the hide admin bar option will be set on the users profile during registration process. That means, the admin bar willn't be shown as default if the user logged in. Anyway hide and show admin bar can be set on the user's profile page", eDemo_SSOauth::TEXTDOMAIN) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_default_role"><?= __( 'Default WP role for SSO usrs:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<select name="EdemoSSO_default_role" id="EdemoSSO_default_role">
									<?= wp_dropdown_roles( get_option('eDemoSSO_default_role') ); ?>
								</select>
								<p class="description"><?= __( "The default WP role, which will be added during the SSO registration", eDemo_SSOauth::TEXTDOMAIN) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_needed_assurances"><?= __( 'Needed assurances:', eDemo_SSOauth::TEXTDOMAIN ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_needed_assurances' id='EdemoSSO_needed_assurances' value='<?= get_option('EdemoSSO_needed_assurances'); ?>' />
								<p class="description"><?= __( 'Comma separated list of assurances needed for allowing registering the user. Keep empty, if no assurance needed for registration.', eDemo_SSOauth::TEXTDOMAIN ) ?></p>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<p class="submit"><input class="button button-primary" type='submit' id="EdemoSSO_update" name='edemosso_update' value='<?= __( 'Update Options', eDemo_SSOauth::TEXTDOMAIN ) ?>' /></p>
							</td>
						</tr>
					</table>
				</fieldset>
			</form>
		</div>
		<?php
	}
	
function show_SSO_user_profile( $user ) { 
    ?>
 	<hr>
	<h3><?= __( 'SSO user data', eDemo_SSOauth::TEXTDOMAIN )?></h3>
    <table class="form-table">
		<div id="eDemoSSO-message-container"></div>
		<?php if (isset($_SESSION['eDemoSSO_auth_message']) and  $_SESSION['eDemoSSO_auth_message']!='') {?> 
			<div class="notice notice-success inline">
				<p><?=$_SESSION['eDemoSSO_auth_message']?></p>
			</div>
		<?php }			
		if ($this->has_user_SSO($user->ID)) {?> 
        <tr>
            <th>SSO id</th>
            <td><?= $this->get_user_SSO_id($user->ID) ?></td>
		</tr>
		<tr>
            <th>SSO token</th>
            <td><?= $this->get_refresh_token($user->ID) ?></td>
		</tr>
		<tr>			
			<th>SSO assurances</th>
            <td><?= get_user_meta($user->ID,eDemo_SSOauth::USERMETA_ASSURANCES, true) ?></td>
        </tr>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=$this->get_action_link('refresh',$user->ID)?>">
						<?= __( 'Refresh', eDemo_SSOauth::TEXTDOMAIN )?>
					</a>
				</p>
				<p class="description"><?= __('Downloads the assurences from the SSO service', eDemo_SSOauth::TEXTDOMAIN )?></p>
			</td>
		</tr>
		<?php if ($user->data->user_login!=$this->get_user_SSO_id($user->ID)) {;?>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=$this->get_action_link('unbind',$user->ID)?>">
						<?= __( 'Unbind', eDemo_SSOauth::TEXTDOMAIN )?>
					</a>
				</p>
				<p class="description"><?= __('Unbinding SSO account from the user', eDemo_SSOauth::TEXTDOMAIN )?></p>
			</td>
		</tr>
		<?php }
		}
		else if ($user->ID==get_current_user_id() and $this->allowBind){?>
		<tr>
			<th></th>
			<td>
				<p><?__( "For this account hasn't SSO account binded", eDemo_SSOauth::TEXTDOMAIN )?><p>
				<a class="button" href="<?=$this->get_SSO_action_link('binding',$user->ID)?>">
					<?= __( 'Bind SSO account', eDemo_SSOauth::TEXTDOMAIN )?>
				</a>
				<p class="description"><?= __('This will bind your account with an SSO account. If you are logged in to your SSO account, this goes automaticly. Otherwise you will be redirected to the SSO login page served by SSO Service. ', eDemo_SSOauth::TEXTDOMAIN )?></p>
				<p class="description"><?= __('If you have here registered with your SSO account, that will be merged in this account with all activity data stored before. User data still remain.', eDemo_SSOauth::TEXTDOMAIN )?></p>
			</td>
		</tr>
		<?php }
		?>
    </table>
		<?php if ( current_user_can( 'edit_users' ) ) { ?>
    <h3><?= __('Ban user', eDemo_SSOauth::TEXTDOMAIN) ?></h3>
    <table class="form-table">
    <tr>
        <th >
			<label for="EdemoSSO_disable_account"><?= __('Disable account:', eDemo_SSOauth::TEXTDOMAIN)?></label>
		</th>
        <td>
			<input name="EdemoSSO_disable_account" type="checkbox" id="EdemoSSO_disable_account" 
				<?= (($this->is_account_disabled($user->ID))?'checked ':'') ?>
				<?= (($user->ID==get_current_user_id())?'disabled readonly':'') ?>
				/>
			<p class="description"><?= __('Set this to ban the user. User will can\'t login.', eDemo_SSOauth::TEXTDOMAIN) ?></p>
		</td>
    </tr>
    </table>	
	<?php }
	}	
	function update_user_profile() {
		if ( !current_user_can( 'edit_users' ) ) return;
        global $user_id;

    // User cannot disable itself
		if ( get_current_user_id() == $user_id ) return;

	// Lock
		if( isset( $_POST['EdemoSSO_disable_account'] ) && $_POST['EdemoSSO_disable_account'] ) {
			$this->disable_user_account( $user_id );
		} 
	// Unlock
		else { 
			$this->enable_user_account( $user_id );
		}
    }
	function disable_user_account( $user_id ){
		update_user_option( $user_id, 'eDemoSSO_account_disabled', true, false );
	}
	function enable_user_account( $user_id ) {
		update_user_option( $user_id, 'eDemoSSO_account_disabled', false, false );
	}
}
