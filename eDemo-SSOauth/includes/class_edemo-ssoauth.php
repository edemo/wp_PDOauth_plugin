<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/edemo/wp_oauth_plugin/wiki
 * @since      0.0.1
 *
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 */
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      0.0.1
 * @package    eDemo-SSOauth
 * @subpackage eDemo-SSOauth/includes
 * @author     Claymanus
 */
class eDemo_SSOauth {
	const TEXTDOMAIN 			= 'eDemo-SSOauth';
	const QUERY_VAR				= 'sso_callback';
	const USER_ROLE				= 'eDemo_SSO_role';
	const USERMETA_ID			= 'eDemoSSO_ID'; 
	const USERMETA_TOKEN		= 'eDemoSSO_refresh_token';
	const USERMETA_ASSURANCES	= 'eDemoSSO_assurances';
	const WP_REDIR_VAR			= 'wp_redirect';
	const SSO_LOGIN_URL			= 'sso.edemokraciagep.org/login.html';
	const SSO_UIDVAR			= 'eDemoSSO_uid';
	/*
	 * constants for SSO comminication interface
	 */
	const SSO_DOMAIN	= 'sso.edemokraciagep.org';
	const SSO_TOKEN_URI	= 'sso.edemokraciagep.org/ada/v1/oauth2/token';
	const SSO_AUTH_URI	= 'sso.edemokraciagep.org/ada/v1/oauth2/auth';
	const SSO_USER_URI	= 'sso.edemokraciagep.org/ada/v1/users/me';
	const SSO_USERS_URI	= 'sso.edemokraciagep.org/ada/v1/users';
	const SSO_SITE_URL	= 'https://sso.edemokraciagep.org/login.html';
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      eDemo_SSOauth_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;
	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;
	/**
	 * The current version of the plugin.
	 *
	 * @since    0.0.1
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;
	protected $options = array(
					'eDemoSSO_serviceURI',
					'eDemoSSO_appkey',
					'eDemoSSO_secret',
					'eDemoSSO_appname',
					'eDemoSSO_sslverify',
					'eDemoSSO_allowBind',
					'eDemoSSO_allowRegister',
					'eDemoSSO_allowLogin',
					'eDemoSSO_default_role',
					'eDemoSSO_hide_adminbar',
					'eDemoSSO_needed_assurances',
					'eDemoSSO_callback_uri'
					);

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Initialise session if not initialised yet
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site according the requested content
	 *
	 * @since    0.0.1
	 */
	public function __construct() {
		$this->plugin_name = 'eDemo-SSOauth';
		$this->version = '0.0.1';
		if (!session_id()) session_start();
		$this->add_options();
		$this->load_dependencies();
		$this->set_locale();
		if ( is_admin() ) {
			$this->define_admin_hooks();
		}
		else {
			$this->define_public_hooks();
		}
	}
	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - eDemo-SSOauth_Loader. Orchestrates the hooks of the plugin.
	 * - eDemo-SSOauth_i18n. Defines internationalization functionality.
	 * - eDemo-SSOauthe_Admin. Defines all hooks for the admin area.
	 * - eDemo-SSOauth_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class_edemo-ssoauth_loader.php';
		/**
		 * The class contains commonly used functions
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class_edemo-ssoauth_base.php';
		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class_edemo-ssoauth_i18n.php';
		/**
		* The class responsible for defining all actions that occur in the widgets
		*/
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class_edemo-ssoauth_widgets.php';
		if ( is_admin() ) {
			/**
			* The class responsible for defining all actions that occur in the admin area.
			*/
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class_edemo-ssoauth_admin.php';
		}
		else {
			/**
			* The class responsible for defining all actions that occur in the public-facing
			* side of the site.
			*/
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class_edemo-ssoauth_public.php';
		}
		/**
		 * The class responsible for defining communication with the SSO server
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . '/includes/class_edemo-ssoauth_communication.php';
		$this->loader = new eDemo_SSOauth_Loader();
	}
	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new eDemo_SSOauth_i18n(self::TEXTDOMAIN);
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}
	/**
	 * Register all of the hooks related to both admin and public area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */	
	private function define_general_hooks() {
		$plugin_general = new eDemo_SSOauth_General( $this->get_plugin_name(), $this->get_version() );

	}
	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new eDemo_SSOauth_Admin( $this->get_plugin_name(), $this->get_version() );
//		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
//		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'widgets_init', $plugin_admin, 'register_widgets' );
		
		#display messages in the notice container on the admin pages
		$this->loader->add_action( 'admin_notices', $plugin_admin, 'notice');

		# show user profile
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'show_SSO_user_profile' );

		# edit user profile
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'show_SSO_user_profile' );

		# update user profile
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'update_user_profile' );
		
		# Hook for hiding admin notices if the current user isn't an admin
		$this->loader->add_action( 'admin_head', $plugin_admin,'hide_update_notice_to_all_but_admin_users', 1 );
		
		# Add admin page
		$this->loader->add_action('admin_menu', $plugin_admin, 'addAdminPage' );
	}
	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    0.0.1
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new eDemo_SSOauth_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		# Adding SSO login area to the bottom of login screen
		$this->loader->add_action( 'login_footer', $plugin_public, 'login_page_extension' );
		# Adding sso callback function to rewrite rules
		$this->loader->add_action( 'generate_rewrite_rules', $plugin_public, 'add_rewrite_rules' );	
		
		# for refreshing the SSO metadata
		$this->loader->add_action( 'wp_login', $plugin_public, 'get_SSO_assurances', 10, 1);
		
		# for disable account functionality
		$this->loader->add_filter( 'wp_authenticate_user', $plugin_public, 'authenticate_user', 1 );
		$this->loader->add_action( 'widgets_init', $plugin_public, 'register_widgets' );
		$this->loader->add_filter( 'do_parse_request', $plugin_public, 'do_parse_request', 10, 3 );
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    0.0.1
	 */
	public function run() {
		$this->loader->run();
	}
	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     0.0.1
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}
	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     0.0.1
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}
	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
	/**
	 * Add plugin options to Wp
	 *
	 * @since     0.0.1
	 * @return    string    The version number of the plugin.
	 */
	private function add_options() {
		foreach ( $this->options as $option){
			error_log($option." - ".(add_option( $option, '', '', 'yes')?"true":"false")." - ".get_option($option,"nincs"));
		}
	}

}