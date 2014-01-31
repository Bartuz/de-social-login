<?php
/*
Plugin Name: DE Social Login
Plugin URI: http://Tiddu.com
Description: A Simple wordpress plugin which enable the user to login in wordress site with Google/Twitter/OpenId/LinkedIn/Facebook accounts with one click.
Version: 0.1.1
Author: Surinder Singh and Sunil Kumar
Author URI: http://developerextensions.com
License:GPL2
*/
//error_reporting(E_ALL);
define( 'loginByOpenID_PATH', plugin_dir_path(__FILE__) );
if (is_admin()){// admin actions
	include(loginByOpenID_PATH.'admin.php');
}else{
	include(loginByOpenID_PATH.'front.php');
}
?>