<?php

namespace com\cminds\videolesson\model;
use com\cminds\videolesson\App;

require_once App::path('lib/Vimeo/Vimeo.php');

class Vimeo extends \Vimeo\Vimeo {
	
	const TRANSIENT_PREFIX = 'cmvlvimeo';
	
	const CACHE_ENABLED = 1;
	const CACHE_DISABLED = 2;
	const CACHE_DISABLED_ONCE = 3;
	
	static protected $instance;
	
	protected $cache = self::CACHE_ENABLED;
	
	
	/**
	 * Get instance.
	 * 
	 * @return \Vimeo\Vimeo
	 */
	static function getInstance() {
		if (empty(self::$instance)) {
			self::$instance = new self(
				Settings::getOption(Settings::OPTION_VIMEO_CLIENT_ID),
				Settings::getOption(Settings::OPTION_VIMEO_CLIENT_SECRET),
				Settings::getOption(Settings::OPTION_VIMEO_ACCESS_TOKEN)
			);
		}
		return self::$instance;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \Vimeo\Vimeo::request()
	 */
	public function request($url, $params = array(), $method = 'GET', $json_body = true) {
		$cache = $this->isCacheEnabled();
		if ($cache) {
			$transient = self::TRANSIENT_PREFIX . '_' . md5(implode('___', array($url, serialize($params), $method, $json_body)));
			$result = get_transient($transient);
		}
		if (empty($result) OR empty($result['body'])) {
			$result = parent::request($url, $params, $method, $json_body);
			if ($cache) {
				set_transient($transient, $result, $expiration = Settings::getOption(Settings::OPTION_VIMEO_CACHE_SEC));
			}
		}
		return $result;
	}
	
	
	static function clearCache() {
		global $wpdb;
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->options WHERE option_name LIKE %s", '_transient_'. self::TRANSIENT_PREFIX .'_%'));
	}
	
	
	function isCacheEnabled() {
		if ($this->cache == self::CACHE_DISABLED_ONCE) {
			$this->cache = self::CACHE_ENABLED;
			return false;
		} else {
			return ($this->cache == self::CACHE_ENABLED);
		}
	}
	
	function disableCacheOnce() {
		$this->cache = self::CACHE_DISABLED_ONCE;
		return $this;
	}
	
	function disableCache() {
		$this->cache = self::CACHE_DISABLED;
		return $this;
	}
	
	
	function enableCache() {
		$this->cache = self::CACHE_ENABLED;
		return $this;
	}
	

}
