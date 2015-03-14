<?php

namespace com\cminds\videolessons\controller;

use com\cminds\videolessons\model\Vimeo;

use com\cminds\videolessons\model\Labels;

use com\cminds\videolessons\App;

use com\cminds\videolessons\model\Settings;

class SettingsController extends Controller {
	
	const ACTION_CLEAR_CACHE = 'clear-cache';
	
	const ABOUT_IFRAME_URL = 'https://plugins.cminds.com/product-catalog/?showfilter=No&cat=Plugin&nitems=2';
	const USER_GUIDE_URL = 'https://plugins.cminds.com/cm-video-lessons-manager-plugin-for-wordpress/';
	
	
	
	protected static $actions = array(array('name' => 'admin_menu', 'priority' => 15), 'admin_notices');
	protected static $filters = array(array('name' => 'cmvl-settings-category', 'args' => 2, 'method' => 'settingsLabels'));
	
	
	static function admin_menu() {
		add_submenu_page(App::MENU_SLUG, 'Video Lessons Settings', 'Settings', 'manage_options', self::getMenuSlug(), array(get_called_class(), 'render'));
		add_submenu_page(App::MENU_SLUG, 'About Video Lessons', 'About', 'manage_options', self::getMenuSlug('about'), array(get_called_class(), 'about'));
		add_submenu_page(App::MENU_SLUG, 'Video Lessons User Guide', 'User Guide', 'manage_options', self::getMenuSlug('user-guide'),
			array(get_called_class(), 'userGuide'));
		if (!App::isPro()) {
			add_submenu_page(App::MENU_SLUG, 'Upgrade to Video Lessons Pro', 'Upgrade to Pro', 'manage_options', self::getMenuSlug('upgrade'),
				array(get_called_class(), 'upgradeToPro'));
		}
	}
	
	
	static function getMenuSlug($slug = 'settings') {
		return App::MENU_SLUG . '-' . $slug;
	}
	
	
	
	static function admin_notices() {
		if (!get_option('permalink_structure')) {
			printf('<div class="error"><p><strong>CM Video Lessons:</strong> to make the plugin works properly
				please enable the <a href="%s">Wordpress permalinks</a>.</p></div>', admin_url('options-permalink.php'));
		}
	}
	
	
	static function render() {
		wp_enqueue_style('cmvl-backend');
		wp_enqueue_style('cmvl-settings');
		wp_enqueue_script('cmvl-backend');
		self::loadView('backend/template', array(
			'title' => 'Video Lessons Settings',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('settings', array(
				'clearCacheUrl' => self::createBackendUrl(self::getMenuSlug(), array('action' => self::ACTION_CLEAR_CACHE), self::ACTION_CLEAR_CACHE),
			), $return = true),
		));
	}
	
	
	static function about() {
		self::loadView('backend/template', array(
			'title' => 'About Video Lessons',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => self::ABOUT_IFRAME_URL,
			), $return = true) . self::loadBackendView('experts', array(), $return = true),
		));
	}
	
	
	static function userGuide() {
		self::loadView('backend/template', array(
			'title' => 'User Guide',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('about', array(
				'iframeURL' => self::USER_GUIDE_URL,
			), $return = true) . self::loadBackendView('experts', array(), $return = true),
		));
	}
	
	
	static function upgradeToPro() {
		wp_enqueue_style('cmvl-backend');
		self::loadView('backend/template', array(
			'title' => 'Upgrade to Pro',
			'nav' => self::getBackendNav(),
			'content' => self::loadBackendView('upgrade', array(), $return = true) . self::loadBackendView('experts', array(), $return = true),
		));
	}
	
	
	static function settingsLabels($result, $category) {
		if ($category == 'labels') {
			$result = self::loadBackendView('labels', array(), $return = true);
		}
		return $result;
	}
	
	
	static function processRequest() {
		if (!empty($_GET['page']) AND $_GET['page'] == self::getMenuSlug()) {
			
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
	
	
	
	
}
