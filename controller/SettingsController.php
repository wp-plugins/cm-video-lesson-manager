<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Vimeo;

use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\App;

use com\cminds\videolesson\model\Settings;

class SettingsController extends Controller {
	
	const ACTION_CLEAR_CACHE = 'clear-cache';
	
	const PAGE_ABOUT_URL = 'https://plugins.cminds.com/product-catalog/?showfilter=No&cat=Plugin&nitems=2';
	const PAGE_USER_GUIDE_URL = 'https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/';
	
	protected static $actions = array(array('name' => 'admin_menu', 'priority' => 15), 'admin_notices');
	protected static $filters = array(array('name' => 'cmvl-settings-category', 'args' => 2, 'method' => 'settingsLabels'));
	
	
	static function admin_menu() {
		add_submenu_page(App::MENU_SLUG, App::getPluginName() . ' Settings', 'Settings', 'manage_options', self::getMenuSlug(), array(get_called_class(), 'render'));
	}
	
	
	static function getMenuSlug() {
		return App::MENU_SLUG . '-settings';
	}
	
	
	static function admin_notices() {
		if (!get_option('permalink_structure')) {
			printf('<div class="error"><p><strong>%s:</strong> to make the plugin works properly
				please enable the <a href="%s">Wordpress permalinks</a>.</p></div>', App::getPluginName(), admin_url('options-permalink.php'));
		}
	}
	
	
	static function render() {
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_style('cmvl-settings');
		wp_enqueue_script('cmvl-backend');
		echo self::loadView('backend/template', array(
			'title' => App::getPluginName() . ' Settings',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('settings', array(
				'clearCacheUrl' => self::createBackendUrl(self::getMenuSlug(), array('action' => self::ACTION_CLEAR_CACHE), self::ACTION_CLEAR_CACHE),
			)),
		));
	}
	
	
	static function settingsLabels($result, $category) {
		if ($category == 'labels') {
			$result = self::loadBackendView('labels');
		}
		return $result;
	}
	
	
	static function processRequest() {
		$fileName = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
		if (is_admin() AND $fileName == 'admin.php' AND !empty($_GET['page']) AND $_GET['page'] == self::getMenuSlug()) {
			
			if (!empty($_POST)) {
				
				// CSRF protection
		        if ((empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], self::getMenuSlug()))) {
		        	$response = array('status' => 'error', 'msg' => 'Invalid nonce.');
		        } else {
			        Settings::processPostRequest($_POST);
			        Labels::processPostRequest();
			        $response = array('status' => 'ok', 'msg' => 'Settings have been updated.');
		        }
		        
		        wp_redirect(self::createBackendUrl(self::getMenuSlug(), $response));
	            exit;
	            
			}
			else if (!empty($_GET['action']) AND !empty($_GET['nonce']) AND wp_verify_nonce($_GET['nonce'], $_GET['action'])) switch ($_GET['action']) {
				case self::ACTION_CLEAR_CACHE:
					Vimeo::clearCache();
					wp_redirect(self::createBackendUrl(self::getMenuSlug(), array('status' => 'ok', 'msg' => 'Cache has been removed.')));
					exit;
					break;
			}
	        
		}
	}
	
	
	static function getSectionExperts() {
		return self::loadBackendView('experts');
	}
	
	
}
