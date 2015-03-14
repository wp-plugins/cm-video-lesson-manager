<?php

namespace com\cminds\videolessons\model;

class Settings extends SettingsAbstract {
	
	const OPTION_PERMALINK_PREFIX = 'cmvl_permalink_prefix';
	
	const OPTION_PLAYLIST_VIEW = 'cmvl_playlist_view';
	
	const OPTION_VIMEO_CLIENT_ID = 'cmvl_vimeo_client_id';
	const OPTION_VIMEO_CLIENT_SECRET = 'cmvl_vimeo_client_secret';
	const OPTION_VIMEO_ACCESS_TOKEN = 'cmvl_vimeo_access_token';
	const OPTION_VIMEO_CACHE_SEC = 'cmvl_vimeo_cache_sec';
	
	const OPTION_ACCESS_VIEW = 'cmvl_access_view';
	
	const OPTION_SHORTCODE_PLAYLIST = 'cmvl_shortcode_playlist';
	const OPTION_SHORTCODE_STATISTICS = 'cmvl_shortcode_stats';
	
	const OPTION_MICROPAYMENTS_INTERVALS = 'cmvl_mp_intervals';
	
	const ACCESS_EVERYONE = 'everyone';
	const ACCESS_LOGGED_IN_USERS = 'users';
	
	const PAGE_CREATE_KEY = '--create--';
	const PAGE_DEFINITION = 'newPage';
	
	
	public static $categories = array(
		'general' => 'General',
		'labels' => 'Labels',
	);
	
	public static $subcategories = array(
		'general' => array(
			'navigation' => 'Navigation',
			'appearance' => 'Appearance',
			'vimeo' => 'Vimeo',
			'access' => 'Access',
			'shortcodes' => 'Shortcodes',
			'eeee' => 'EEEEEE',
		),
	);
	
	
	public static function getOptionsConfig() {
		
		return apply_filters('cmvl_options_config', array(
				
			// Main
			self::OPTION_PERMALINK_PREFIX => array(
				'type' => self::TYPE_STRING,
				'default' => 'video-lessons',
				'category' => 'general',
				'subcategory' => 'navigation',
				'title' => 'Permalink prefix',
				'desc' => 'Enter the prefix of the channels and categories permalinks, eg. <kbd>video-lessons</kbd> '
							. 'will give permalinks such as: <kbd>/<strong>video-lessons</strong>/category/channel</kbd>.',
			),
			
			// Appearance
			
				
			// Vimeo
			self::OPTION_VIMEO_CLIENT_ID => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'vimeo',
				'title' => 'App Client Identifier',
				'desc' => 'Enter the client identifier of the Vimeo App.',
			),
			self::OPTION_VIMEO_CLIENT_SECRET => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'vimeo',
				'title' => 'App Client Secret',
				'desc' => 'Enter the client secret of the Vimeo App.',
			),
			self::OPTION_VIMEO_ACCESS_TOKEN => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'vimeo',
				'title' => 'Access token',
				'desc' => 'Enter the access token with the public, private, edit and interact priviliges.',
			),
			self::OPTION_VIMEO_CACHE_SEC => array(
				'type' => self::TYPE_INT,
				'default' => 600,
				'category' => 'general',
				'subcategory' => 'vimeo',
				'title' => 'Cache lifetime for Vimeo API',
				'desc' => 'Enter the number of seconds to cache the results of the Vimeo API requests. Caching will increase the load times. Set 0 to disable.',
			),
				
				
			// Access
			
			
			// Shortcodes
			self::OPTION_SHORTCODE_PLAYLIST => array(
				'type' => self::TYPE_CUSTOM,
				'category' => 'general',
				'subcategory' => 'shortcodes',
				'title' => 'Playlist',
				'content' => '<code>[cmvl-playlist view="playlist|tiles" category="id|slug" channel="id|slug" navbar=1 ajax=1]</code>',
				'desc' => 'Display playlist view.',
			),
			
			
		));
		
	}
	
	
	public static function processPostRequest($data) {
		
		// Create new pages
		$options = static::getOptionsConfig();
		foreach ($data as $key => &$value) {
			if ($value == self::PAGE_CREATE_KEY AND !empty($options[$key][self::PAGE_DEFINITION])) {
				$post = array_merge(array(
					'post_author' => get_current_user_id(),
					'post_status' => 'publish',
					'post_type' => 'page',
					'comment_status' => 'closed',
					'ping_status' => 'closed',
				), $options[$key][self::PAGE_DEFINITION]);
				$result = wp_insert_post($post);
				if (is_numeric($result)) {
					$value = $result;
				}
			}
		}
		
		parent::processPostRequest($data);
		
	}
	
	
}
