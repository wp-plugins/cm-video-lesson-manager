<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Labels;

use com\cminds\videolesson\model\Settings;

use com\cminds\videolesson\shortcode\PlaylistShortcode;

use com\cminds\videolesson\App;

use com\cminds\videolesson\model\Category;

use com\cminds\videolesson\model\Video;

use com\cminds\videolesson\model\Vimeo;

use com\cminds\videolesson\model\Channel;

class ChannelController extends Controller {
	
	const DEFAULT_VIEW = 'tiles';
	const PARAM_PAGE = 'cmvl_page';

	protected static $actions = array('add_meta_boxes', array('name' => 'save_post', 'args' => 1));
	protected static $filters = array(
		'the_content',
		array('name' => 'cmvl_playlist_shortcode_content', 'args' => 2, 'priority' => 30),
	);
	protected static $suspendActions = 0;
	

	static function init() {
		parent::init();
		add_rewrite_tag('%video%', '(\d+)');
		add_rewrite_tag('%'. Category::TAXONOMY .'%', '([^/]+)');
	}
	
	
	static function add_meta_boxes() {
		add_meta_box( App::prefix('-choose-channel'), 'Choose Vimeo Album', array(get_called_class(), 'choose_channel_meta_box'),
			Channel::POST_TYPE, 'normal', 'high' );
	}


	static function choose_channel_meta_box($post) {
		$vimeo = Vimeo::getInstance();
		$vimeo->disableCacheOnce();
		$channels = $vimeo->request('/me/channels', array('per_page' => 50, 'filter' => 'moderated'));
		$albums = $vimeo->request('/me/albums', array('per_page' => 50, 'filter' => 'moderated'));
		if ($channel = Channel::getInstance($post)) {
			$currentChannelUri = $channel->getVimeoUri();
		} else {
			$currentChannelUri = 0;
		}
		wp_enqueue_script('cmvl-backend');
		echo self::loadBackendView('choose-channel-meta-box', compact('channels', 'albums', 'currentChannelUri'));
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
		if (!empty($_POST['cmvl_channel_uri'])) {
			$channel->setVimeoUri($_POST['cmvl_channel_uri']);
		}
	}
	

	static function the_content($content) {
		if (is_main_query() AND is_single() AND get_post_type() == Channel::POST_TYPE) {
			
			global $post;
			$channel = Channel::getInstance($post);
			$category = get_query_var(Category::TAXONOMY);
			if (empty($category)) {
				if ($categories = $channel->getCategories()) {
					$cat = reset($categories);
					$category = $cat->getId();
				}
			}
			
			$playlist = PlaylistShortcode::shortcode(array(
				'channel' => $post->ID,
				'category' => $category,
				'ajax' => 0,
			));
			
			self::loadAssets();
			return self::loadFrontendView('single_content', compact('content', 'playlist'));
			
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
		return self::loadFrontendView('access_denied', compact('channel'));
	}
	
	
	static function loadNotFoundView() {
		self::loadAssets();
		return self::loadFrontendView('not_found');
	}
	
	
	
	static function playlist($post, $pagination = array(), $view = null) {
		
		$pagination = shortcode_atts(array(
			'page' => ((isset($_GET[self::PARAM_PAGE]) AND is_numeric($_GET[self::PARAM_PAGE])) ? $_GET[self::PARAM_PAGE] : 1),
			'per_page' => Settings::getOption(Settings::OPTION_PAGINATION_LIMIT),
		), $pagination);
		
		if ($channel = Channel::getInstance($post)) {
			if ($channel->canView()) {
				$videos = $channel->getVideos($pagination);
				$pagination['total'] = $channel->getTotalVideos();
				$categories = $channel->getCategories();
				$pagination['base_url'] = $channel->getPermalinkForCategory($categories[0]);
				return self::renderPlaylist($videos, $pagination, $view);
			} else {
				return self::loadAccessDeniedView($channel);
			}
		} else {
			return self::loadNotFoundView();
		}
	}
	
	
	static function renderPlaylist($videos, $pagination = array(), $view = null) {
		
		self::loadAssets();
		
		$pagination = shortcode_atts(array(
			'page' => 1,
			'per_page' => Settings::getOption(Settings::OPTION_PAGINATION_LIMIT),
			'total' => 0,
			'base_url' => null,
		), $pagination);
		
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
		
		if ($pagination['per_page'] > 0) {
			$pagination['total_pages'] = ceil($pagination['total'] / $pagination['per_page']);
		}
		if (empty($pagination['total_pages'])) {
			$pagination['total_pages'] = 1;
		}
		if (!empty($pagination['base_url']) AND $pagination['total_pages'] > 1) {
			$paginationView = self::loadView('frontend/playlist/pagination', $pagination);
		} else $paginationView = '';
		
		return self::loadView('frontend/playlist/' . $view, compact('videos', 'currentVideo', 'playerOptions', 'paginationView'));
		
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
	
	
	static function cmvl_playlist_shortcode_content($content, $atts) {
		if (!empty($atts['linksbar']) AND $linksbar = apply_filters('cmvl_playlist_links_bar', '')) {
			$content = sprintf('<ul class="cmvl-inline-nav">%s</ul>', $linksbar) . $content;
		}
		return $content;
	}
	
	
}
