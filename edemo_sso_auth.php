<?php
/*
Plugin Name: eDemo SSO authentication
Plugin URI: https://github.com/edemo/wp_oauth_plugin/wiki
Description: Allows you connect to the Edemo SSO server, and autenticate the users, who acting on your site
Version: 0.1
Author: Claymanus
Author URI: https://github.com/Claymanus
License: GPL2

{Plugin Name} is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
{Plugin Name} is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.

License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: eDemo-SSO
Domain Path: /languages
*/

### Version
define( 'EDEMO_AUTH_VERSION', 0.1 );
global $eDemoSSO;
require_once( dirname(__file__).'/includes/edemo-auth_main.php' );
require_once( dirname(__file__).'/includes/edemo-auth_communication.php' );

if ( !is_admin() ) {
	require_once( dirname(__file__).'/includes/edemo-auth_widgets.php' );
	if (!isset($eDemoSSO)) { $eDemoSSO = new edemo_auth(); } 
}
else {
	require_once( dirname(__file__).'/includes/edemo-auth_admin.php' );
	if (!isset($eDemoSSO)) { $eDemoSSO = new edemo_auth_admin(); } 
}






?>
