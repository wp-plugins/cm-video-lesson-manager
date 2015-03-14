<?php

namespace com\cminds\videolessons\controller;
use com\cminds\videolessons\App;

abstract class Controller {
	
	static protected $instance;
	protected static $actions = array();
	protected static $filters = array();
	protected static $ajax = array();
	
	static function bootstrap() {
		
		foreach (static::$actions as $action) {
			if (!is_array($action)) {
				$action = array('name' => $action, 'priority' => 10, 'args' => 0);
			}
			if (empty($action['priority'])) $action['priority'] = 10;
			if (empty($action['args'])) $action['args'] = 0;
			if (empty($action['method'])) $action['method'] = strtr($action['name'], '-', '_');
			add_action($action['name'], array(get_called_class(), $action['method']), $action['priority'], $action['args']);
		}
		
		foreach (static::$filters as $filter) {
			if (!is_array($filter)) {
				$filter = array('name' => $filter, 'priority' => 10, 'args' => 1);
			}
			if (empty($filter['priority'])) $filter['priority'] = 10;
			if (empty($filter['args'])) $filter['args'] = 1;
			if (empty($filter['method'])) $filter['method'] = strtr($filter['name'], '-', '_');
			add_filter($filter['name'], array(get_called_class(), $filter['method']), $filter['priority'], $filter['args']);
		}
		
		foreach (static::$ajax as $ajax) {
			add_action('wp_ajax_'. $ajax, array(get_called_class(), $ajax));
			add_action('wp_ajax_nopriv_'. $ajax, array(get_called_class(), $ajax));
		}
		
		add_action('init', array(get_called_class(), 'init'), 3);
		add_action('init', array(get_called_class(), 'processRequest'), PHP_INT_MAX);
		
	}
	
	
	static function init() {
		
	}
	
	
	static function processRequest() {
		
	}
	
	static function getInstance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	
	static function loadView($_viewName, $_params = array(), $_return = false) {
		$_viewPath = App::path('view/'. $_viewName .'.php');
		if (file_exists($_viewPath)) {
			extract($_params);
			if ($_return) ob_start();
			include $_viewPath;
			if ($_return) return ob_get_clean();
		} else {
			trigger_error('['. App::PREFIX .'] View not found: '. $_viewName, E_USER_WARNING);
		}
	}
	
	
	static function shortClassName() {
		return App::shortClassName(get_called_class(), 'Controller');
	}
	
	
	static function loadFrontendView($_viewName, $_params = array(), $_return = false) {
		$_viewName = 'frontend' . DIRECTORY_SEPARATOR . strtolower(static::shortClassName()) . DIRECTORY_SEPARATOR . $_viewName;
		return static::loadView($_viewName, $_params, $_return);
	}
	
	
	static function loadBackendView($_viewName, $_params = array(), $_return = false) {
		$_viewName = 'backend' . DIRECTORY_SEPARATOR . strtolower(static::shortClassName()) . DIRECTORY_SEPARATOR . $_viewName;
		return static::loadView($_viewName, $_params, $_return);
	}
	
	
	static function isAjax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}
	
	
	static function getBackendNav() {
		return '';
	}
	
	
	static function createBackendUrl($page, $params = array(), $nonce = false) {
		$params['page'] = $page;
		if ($nonce !== false) {
			$params['nonce'] = wp_create_nonce($nonce);
		}
		return admin_url('admin.php') . ($params ? '?' . http_build_query($params) : '');
	}
	
	
}
