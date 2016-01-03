<?php
/**
* Uninstall eDemo SSO auth plugin
* 
* Deletes all plugin specific data like
* - user meta data
* - plugin options
* - users registered with SSO
*
* @since 0.1
*
*/

// If uninstall is not called from WordPress, exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit();
}

### delete plugin options

global $wpdb;
$eDemoSSO_options = $wpdb->get_results( 'SELECT option_name FROM wp_options WHERE option_name LIKE "eDemoSSO_%";', OBJECT );


foreach ($eDemoSSO_options as $option_name) delete_option( $option_name );
 
// For site options in Multisite
foreach ($eDemoSSO_options as $option_name) delete_site_option( $option_name ); 

### delete user meta data and plugin specific users

	// dummy user for reassign
$reassign_user_id = wp_insert_user( array( 'user_login' => 'SSO-reassign-user', 'user_pass' => null ));
	// gets all plugin specific user mata keys
$eDemoSSO_usermetakeys = $wpdb->get_results( 'SELECT DISTINCT meta_key FROM wp_usermeta WHERE meta_key LIKE "%eDemoSSO_%";', OBJECT );
	// gets all user having SSO ID
$users=get_users( array('meta_key' => eDemoSSO::USERMETA_ID) );

foreach($users as $user) {
	// deleting users registered with SSO
	if ($user->ID == get_user_meta($user->ID,self::USERMETA_ID, true) {
		wp_delete_user( $user->ID, $reassign_user_id );
	}
	// deleting plugin specific meta data of other users
	else {
		foreach($eDemoSSO_usermetakeys as $metakey) delete_user_meta( $user->ID, $metakey);
	}
}

 ?>