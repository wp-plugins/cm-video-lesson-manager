<?php

namespace com\cminds\videolessons\controller;

use com\cminds\videolessons\model\Labels;

use com\cminds\videolessons\model\Settings;

use com\cminds\videolessons\shortcode\PlaylistShortcode;

use com\cminds\videolessons\App;

use com\cminds\videolessons\model\Category;

use com\cminds\videolessons\model\Video;

use com\cminds\videolessons\model\Vimeo;

use com\cminds\videolessons\model\Channel;

class ChannelController extends Controller {
	
	const DEFAULT_VIEW = 'tiles';

	protected static $actions = array('add_meta_boxes', array('name' => 'save_post', 'args' => 1));
	protected static $filters = array('the_content');
	protected static $suspendActions = 0;
	

	static function init() {
		parent::init();
		add_rewrite_tag('%video%', '(\d+)');
		add_rewrite_tag('%'. Category::TAXONOMY .'%', '([^/]+)');
	}
	
	
	static function add_meta_boxes() {
		add_meta_box( App::prefix('-choose-channel'), 'Choose Vimeo Channel', array(get_called_class(), 'choose_channel_meta_box'),
			Channel::POST_TYPE, 'normal', 'high' );
	}


	static function choose_channel_meta_box($post) {
		$vimeo = Vimeo::getInstance();
		$vimeo->disableCacheOnce();
		$channels = $vimeo->request('/me/channels', array('per_page' => 50, 'filter' => 'moderated'));
		if ($channel = Channel::getInstance($post)) {
			$currentChannelId = $channel->getVimeoId();
		} else {
			$currentChannelId = 0;
		}
		wp_enqueue_script('cmvl-backend');
		self::loadBackendView('choose-channel-meta-box', compact('channels', 'currentChannelId'));
	}
	
	

	static function save_post($post_id) {
		if (!static::$suspendActions AND $channel = Channel::getInstance($post_id)) {
			static::$suspendActions++;
			
			self::save_post_channel($channel);
			
			if (!$channel->getCategories()) {
				$channel->addDefaultCategory();
			}
			
			static::$suspendActions--;
		}
	}
	
	
	static protected function save_post_channel(Channel $channel) {
		if (!empty($_POST['cmvl_vimeo_channel_id_direct'])) {
			$channel->setVimeoId($_POST['cmvl_vimeo_channel_id_direct']);
		}
		else if (!empty($_POST['cmvl_vimeo_channel_id'])) {
			$channel->setVimeoId($_POST['cmvl_vimeo_channel_id']);
		}
	}
	

	static function the_content($content) {
		if (is_main_query() AND is_single() AND get_post_type() == Channel::POST_TYPE) {
			
			global $post;
			$channel = Channel::getInstance($post);
			
			$playlist = PlaylistShortcode::shortcode(array(
				'channel' => $post->ID,
				'category' => get_query_var(Category::TAXONOMY),
				'ajax' => 0,
			));
			
			self::loadAssets();
			return self::loadFrontendView('single_content', compact('content', 'playlist'), $return = true);
			
		} else {
			return $content;
		}
	}
	
	
	static function loadAssets() {
		wp_enqueue_style('dashicons');
		wp_enqueue_style('cmvl-frontend');
		wp_enqueue_script('cmvl-utils');
		wp_enqueue_script('jquery');
		wp_enqueue_script('cmvl-playlist');
		do_action('cmvl_load_assets_frontend');
	}
	
	
	static function loadAccessDeniedView(Channel $channel = null) {
		self::loadAssets();
		return self::loadFrontendView('access_denied', compact('channel'), $return = true);
	}
	
	
	static function loadNotFoundView() {
		self::loadAssets();
		return self::loadFrontendView('not_found', array(), $return = true);
	}
	
	
	static function playlist($post, $view = null) {
		if ($channel = Channel::getInstance($post)) {
			if ($channel->canView()) {
				$videos = $channel->getVideos();
				return self::renderPlaylist($videos, $view);
			} else {
				return self::loadAccessDeniedView($channel);
			}
		} else {
			return self::loadNotFoundView();
		}
	}
	
	
	static function renderPlaylist($videos, $view = null) {
		
		self::loadAssets();
		
		$currentVideo = reset($videos);
		if ($currentVideoId = get_query_var('video')) {
			foreach ($videos as $v) {
				if ($v->getId() == $currentVideoId) {
					$currentVideo = $v;
					break;
				}
			}
		}
		
		$playerOptions = array('autoplay' => false /*self::isAjax()*/ );
		$view = self::checkView($view);
		return self::loadView('frontend/playlist/' . $view,
			compact('videos', 'currentVideo', 'playerOptions'), $return = true);
		
	}
	
	
	protected static function checkView($view) {
		if ($availableViews = Settings::getOptionConfig(Settings::OPTION_PLAYLIST_VIEW)) {
			$availableViews = array_keys($availableViews['options']);
			if (!in_array($view, $availableViews)) {
				$view = Settings::getOption(Settings::OPTION_PLAYLIST_VIEW);
				if (empty($view)) {
					$view = self::DEFAULT_VIEW;
				}
			}
			return $view;
		} else {
			return self::DEFAULT_VIEW;
		}
	}
	
	
}
