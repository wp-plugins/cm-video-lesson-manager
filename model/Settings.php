<?php

namespace com\cminds\videolesson\model;

class Settings extends SettingsAbstract {
	
	const OPTION_PERMALINK_PREFIX = 'cmvl_permalink_prefix';
	
	const OPTION_PLAYLIST_VIEW = 'cmvl_playlist_view';
// 	const OPTION_VIDEO_SORT_METHOD = 'cmvl_video_sort_method';
// 	const OPTION_VIDEO_SORT_DIRECTION = 'cmvl_video_sort_direction';
	const OPTION_PAGINATION_LIMIT = 'cmvl_pagination_limit';
	
	const OPTION_VIMEO_CLIENT_ID = 'cmvl_vimeo_client_id';
	const OPTION_VIMEO_CLIENT_SECRET = 'cmvl_vimeo_client_secret';
	const OPTION_VIMEO_ACCESS_TOKEN = 'cmvl_vimeo_access_token';
	const OPTION_VIMEO_CACHE_SEC = 'cmvl_vimeo_cache_sec';
	
	const OPTION_NEW_SUB_ADMIN_NOTIF_ENABLE = 'cmvl_new_sub_admin_nofif_enable';
	const OPTION_NEW_SUB_ADMIN_NOTIF_EMAILS = 'cmvl_new_sub_admin_nofif_emails';
	const OPTION_NEW_SUB_ADMIN_NOTIF_SUBJECT = 'cmvl_new_sub_admin_nofif_subject';
	const OPTION_NEW_SUB_ADMIN_NOTIF_TEMPLATE = 'cmvl_new_sub_admin_nofif_template';
	
	const OPTION_ACCESS_VIEW = 'cmvl_access_view';
	
	const OPTION_SHORTCODE_PLAYLIST = 'cmvl_shortcode_playlist';
	const OPTION_SHORTCODE_STATISTICS = 'cmvl_shortcode_stats';
	
	const ACCESS_EVERYONE = 'everyone';
	const ACCESS_LOGGED_IN_USERS = 'users';
	
	const PAGE_CREATE_KEY = '--create--';
	const PAGE_DEFINITION = 'newPage';
	
	
	public static $categories = array(
		'general' => 'General',
		'notifications' => 'Notifications',
		'labels' => 'Labels',
	);
	
	public static $subcategories = array(
		'general' => array(
			'navigation' => 'Navigation',
			'appearance' => 'Appearance',
			'vimeo' => 'Vimeo',
			'access' => 'Access',
			'shortcodes' => 'Shortcodes',
		),
		'notifications' => array(
			'sub' => 'New subscription',
		),
	);
	
	
	public static function getOptionsConfig() {
		
		return apply_filters('cmvl_options_config', array(
				
			// Main
			self::OPTION_PERMALINK_PREFIX => array(
				'type' => self::TYPE_STRING,
				'default' => 'video-lesson',
				'category' => 'general',
				'subcategory' => 'navigation',
				'title' => 'Permalink prefix',
				'desc' => 'Enter the prefix of the channels and categories permalinks, eg. <kbd>video-lesson</kbd> '
							. 'will give permalinks such as: <kbd>/<strong>video-lesson</strong>/category/channel</kbd>.',
			),
			
			// Appearance
// 			self::OPTION_VIDEO_SORT_METHOD => array(
// 				'type' => self::TYPE_RADIO,
// 				'options' => array(
// 					Video::SORT_MANUAL => 'manual',
// 					Video::SORT_DATE => 'date',
// 					Video::SORT_ALPHABETICAL => 'alphabetical',
// 					Video::SORT_PLAYS => 'plays number',
// 					Video::SORT_LIKES => 'likes number',
// 					Video::SORT_COMMENTS => 'comments number',
// 					Video::SORT_DURATION => 'duration',
// 					Video::SORT_MODIFIED_TIME => 'modification time',
// 				),
// 				'default' => Video::SORT_MANUAL,
// 				'category' => 'general',
// 				'subcategory' => 'appearance',
// 				'title' => 'Videos sorting method',
// 				'desc' => 'Choose the videos sorting method.',
// 			),
// 			self::OPTION_VIDEO_SORT_DIRECTION => array(
// 				'type' => self::TYPE_RADIO,
// 				'options' => array(
// 					Video::DIR_ASC => 'ascending',
// 					Video::DIR_DESC => 'descending',
// 				),
// 				'default' => Video::DIR_ASC,
// 				'category' => 'general',
// 				'subcategory' => 'appearance',
// 				'title' => 'Videos sorting direction',
// 				'desc' => 'Choose the videos sorting direction.',
// 			),
			self::OPTION_PAGINATION_LIMIT => array(
				'type' => self::TYPE_INT,
				'default' => 10,
				'category' => 'general',
				'subcategory' => 'appearance',
				'title' => 'Videos per page',
				'desc' => 'Limit the videos per page number in the tiles view. Max is 50.',
			),
				
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
				'content' => '<code>[cmvl-playlist view="playlist|tiles" category="id|slug" channel="id|slug" navbar=1 searchbar=1 linksbar=1 ajax=1 urlsearch=0]</code>',
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
