<?php

/*
* Hooks:
* edemo_auth_do_update_options
*/

class edemo_auth_admin extends edemo_auth {

	function __construct() {
		// Instantiate the parent object
		parent::__construct( );
		// Admin page 
		// User profile
		add_action( 'show_user_profile', array ( $this, 'show_SSO_user_profile' ) );
		add_action( 'edit_user_profile', array ( $this, 'show_SSO_user_profile' ) );
		add_action( 'edit_user_profile_update', array ( $this, 'update_user_profile') );
		add_action( 'admin_enqueue_scripts', array ( $this, 'add_js') );
		
		# Hook for hiding admin notices if the current user isn't an admin
		add_action( 'admin_head', array( $this,'hide_update_notice_to_all_but_admin_users'), 1 );
		
		# Adding admin page
		add_action('admin_menu', array( $this, 'addAdminPage' ) );
	}
	

	
	
	
	# to hiding admin notices if the current user isn't an admin
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
//			check_admin_referer();    // EZT MAJD MEG KELLENE NÉZNI !!!!!

			do_action( 'edemo_auth_do_update_options' );

			// Update options 
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

			update_option( 'eDemoSSO_appkey'   			, $this->appkey   );
			update_option( 'eDemoSSO_secret'   			, $this->secret   );
			update_option( 'eDemoSSO_appname'  			, $this->appname  );
			update_option( 'eDemoSSO_sslverify'			, $this->sslverify );
			update_option( 'eDemoSSO_allowBind'			, $this->allowBind );
			update_option( 'eDemoSSO_allowRegister'		, $this->allowRegister );
			update_option( 'eDemoSSO_allowLogin'		, $this->allowLogin );
			update_option( 'eDemoSSO_hide_adminbar'		, $this->hide_adminbar );
			update_option( 'eDemoSSO_default_role'  	, $this->default_role );
			update_option( 'eDemoSSO_needed_assurances' , str_replace(' ', '', $this->needed_assurances) );

			// echo message updated
			echo "<div class='updated fade'><p>Options updated.</p></div>";
		}		
		?>
		<div class="wrap">

			<h2><?= __( 'eDemo SSO Authentication Options', 'eDemo-SSO' ) ?></h2>
			<form method="post">
				<fieldset class='options'>
					<table class="form-table">
						<tr>
							<th>
								<label for="EdemoSSO_appname"><?= __( 'Application name:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_appname' id='EdemoSSO_appname' value='<?= get_option('eDemoSSO_appname'); ?>' />
								<p class="description"><?= __( 'Used for registering the application', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_appkey"><?= __( 'Application key:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_appkey' id='EdemoSSO_appkey' value='<?= $this->appkey; ?>' />
								<p class="description"><?= __( 'Application key.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_secret"><?= __( 'Application secret:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='40' maxlength='40' name='EdemoSSO_secret' id='EdemoSSO_secret' value='<?= $this->secret; ?>' />
								<p class="description"><?= __( 'Application secret.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_sslverify"><?= __( 'Allow verify ssl certificates:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_sslverify' id='EdemoSSO_sslverify' <?= (($this->sslverify)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the ssl certificates will be verified during the communication with sso server. Uncheck is recommended if your site has no cert, or the issuer isn't validated.", 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="eDemoSSO_callbackURI"><?= __( 'eDemo_SSO callback URL:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<?= $this->callbackURL ?>
								<p class="description"><?=__('Callback url for communication with the SSO_server', 'eDemo-SSO')?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowBind"><?= __( 'SSO account binding:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowBind' id='EdemoSSO_allowBind' <?= (($this->allowBind)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, a SSO account can be binded with the given Wordpress account. User gets a 'bind' button on his datasheet and in the SSO login widget.", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowRegister"><?= __( 'Allow registering:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowRegister' id='EdemoSSO_allowRegister' <?= (($this->allowRegister)?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the user registrating with SSO service.", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_allowLogin"><?= __( 'Allow sign in:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_allowLogin' id='EdemoSSO_allowLogin' <?= (($this->allowLogin)?'checked':''); ?> />
								<p class="description"><?= __( "This setting allows the users logging in. In emergency case unset this option to forbid the users logging in", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_hide_adminbar"><?= __( 'Hide adminbar:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='checkbox' name='EdemoSSO_hide_adminbar' id='EdemoSSO_hide_adminbar' <?= (($this->hide_adminbar)?'checked':''); ?> />
								<p class="description"><?= __( "If this set, the hide admin bar option will be set on the users profile during registration process. That means, the admin bar willn't be shown as default if the user logged in. Anyway hide and show admin bar can be set on the user's profile page", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_default_role"><?= __( 'Default WP role for SSO usrs:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<select name="EdemoSSO_default_role" id="EdemoSSO_default_role">
									<?= wp_dropdown_roles( $this->default_role ); ?>
								</select>
								<p class="description"><?= __( "The default WP role, which will be added during the SSO registration", 'eDemo-SSO') ?></p>
							</td>
						</tr>
						<tr>
							<th>
								<label for="EdemoSSO_needed_assurances"><?= __( 'Needed assurances:', 'eDemo-SSO' ) ?></label>
							</th>
							<td>
								<input type='text' size='16' maxlength='30' name='EdemoSSO_needed_assurances' id='EdemoSSO_needed_assurances' value='<?= get_option('EdemoSSO_needed_assurances'); ?>' />
								<p class="description"><?= __( 'Comma separated list of assurances needed for allowing registering the user. Keep empty, if no assurance needed for registration.', 'eDemo-SSO' ) ?></p>
							</td>
						</tr>
						<tr>
							<td colspan="2">
							<p class="submit"><input class="button button-primary" type='submit' name='edemosso_update' value='<?= __( 'Update Options', 'eDemo-SSO' ) ?>' /></p>
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
	<h3><?= __( 'SSO user data', 'eDemo-SSO' )?></h3>
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
            <td><?= get_user_meta($user->ID,parent::USERMETA_ID, true) ?></td>
		</tr>
		<tr>
            <th>SSO token</th>
            <td><?= get_user_meta($user->ID,parent::USERMETA_TOKEN, true) ?></td>
		</tr>
		<tr>			
			<th>SSO assurances</th>
            <td><?= get_user_meta($user->ID,parent::USERMETA_ASSURANCES, true) ?></td>
        </tr>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=$this->get_action_link('refresh',$user->ID)?>">
						<?= __( 'Refresh', 'eDemo-SSO' )?>
					</a>
				</p>
				<p class="description"><?= __('Downloads the assurences from the SSO service', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<?php if ($user->data->user_login!=get_user_meta($user->ID, parent::USERMETA_ID, true)) {;?>
		<tr>
			<th></th>
			<td>
				<p>
					<a class="button" href="<?=$this->get_action_link('unbind',$user->ID)?>">
						<?= __( 'Unbind', 'eDemo-SSO' )?>
					</a>
				</p>
				<p class="description"><?= __('Unbinding SSO account from the user', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<?php }
		}
		else if ($user->ID==get_current_user_id() and $this->allowBind){?>
		<tr>
			<th></th>
			<td>
				<p><?__( "For this account hasn't SSO account binded", 'eDemo-SSO' )?><p>
				<a class="button" href="<?=$this->get_SSO_action_link('binding',$user->ID)?>">
					<?= __( 'Bind SSO account', 'eDemo-SSO' )?>
				</a>
				<p class="description"><?= __('This will bind your account with an SSO account. If you are logged in to your SSO account, this goes automaticly. Otherwise you will be redirected to the SSO login page served by SSO Service. ', 'eDemo-SSO' )?></p>
				<p class="description"><?= __('If you have here registered with your SSO account, that will be merged in this account with all activity data stored before. User data still remain.', 'eDemo-SSO' )?></p>
			</td>
		</tr>
		<?php }
		?>
    </table>
		<?php if ( current_user_can( 'edit_users' ) ) { ?>
    <h3>Ban user</h3>
    <table class="form-table">
    <tr>
        <th >
			<label for="EdemoSSO_disable_account"><?= __('Disable account:', 'eDemo-SSO')?></label>
		</th>
        <td>
			<input name="EdemoSSO_disable_account" type="checkbox" id="EdemoSSO_disable_account" 
				<?= (($this->is_account_disabled($user->ID))?'checked ':'') ?>
				<?= (($user->ID==get_current_user_id())?'disabled readonly':'') ?>
				/>
			<p class="description"><?= __('Set to ban the user. User will can\'t login.', 'eDemo-SSO') ?></p>
		</td>
    </tr>
    </table>	
	<?php }
	}
}

?>