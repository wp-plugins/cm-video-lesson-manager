<?php
/*
  Plugin Name: CM Video Lesson Manager
  Plugin URI: http://answers.cminds.com/
  Description: Manage video lessons from Vimeo private channels. 
  Author: CreativeMindsSolutions
  Version: 1.0.0
 */

if (version_compare('5.3', PHP_VERSION, '>')) {
	die(sprintf('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: %s)'
		. ' - please upgrade or contact your system administrator.', PHP_VERSION));
}

define('CMVL_PLUGIN_FILE', __FILE__);

require_once dirname(__FILE__) . '/App.php';
com\cminds\videolessons\App::bootstrap();
