<?php

namespace com\cminds\videolesson\model;
use com\cminds\videolesson\model\Category;
use com\cminds\videolesson\App;

class Channel extends PostType {
	
	const POST_TYPE = 'cmvl_channel';
	const META_VIMEO_URI = 'vimeo_uri';
	const META_DURATION = 'cmvl_channel_duration';
	const MAX_PER_PAGE = 50;
	
	
	static protected $postTypeOptions = array(
		'label' => 'Lesson Channel',
		'public' => true,
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_admin_bar' => true,
		'show_in_menu' => App::MENU_SLUG,
		'hierarchical' => false,
		'supports' => array('title', 'editor'),
		'has_archive' => true,
		'taxonomies' => array(Category::TAXONOMY),
	);
	
	
	protected $totalVideos = null;
	
	
	static protected function getPostTypeLabels() {
		$singular = ucfirst(Labels::getLocalized('channel'));
		$plural = ucfirst(Labels::getLocalized('channels'));
		return array(
			'name' => $plural,
            'singular_name' => $singular,
            'add_new' => sprintf(__('Add %s', 'cm-answers'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'cm-answers'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'cm-answers'), $singular),
            'new_item' => sprintf(__('New %s', 'cm-answers'), $singular),
            'all_items' => $plural,
            'view_item' => sprintf(__('View %s', 'cm-answers'), $singular),
            'search_items' => sprintf(__('Search %s', 'cm-answers'), $plural),
            'not_found' => sprintf(__('No %s found', 'cm-answers'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'cm-answers'), $plural),
            'menu_name' => App::getPluginName()
		);
	}
	
	
	static function init() {
		static::$postTypeOptions['rewrite'] = array('slug' => Settings::getOption(Settings::OPTION_PERMALINK_PREFIX) . '/%'. Category::TAXONOMY .'%');
		parent::init();
		add_filter('post_type_link', array(get_called_class(), 'permalinkStructure'), 10, 4);
	}
	
	
	static function permalinkStructure($post_link, $post, $leavename, $sample) {
		if ( false !== strpos( $post_link, '%'. Category::TAXONOMY .'%' ) ) {
			$event_type_term = get_the_terms( $post->ID, Category::TAXONOMY );
			if (!empty($event_type_term) AND is_array($event_type_term)) {
				$post_link = str_replace( '%'. Category::TAXONOMY .'%', reset( $event_type_term )->slug, $post_link );
			}
		}
		if (!empty($post->cmvl_video)) {
			$post_link = add_query_arg('video', urlencode($post->cmvl_video), $post_link);
		}
		return $post_link;
	}
	
	
	/**
	 * Get instance
	 * 
	 * @param WP_Post|int $post Post object or ID
	 * @return com\cminds\videolesson\model\Channel
	 */
	static function getInstance($post) {
		return parent::getInstance($post);
	}
	
	
	static function parseId($uri) {
		if (preg_match('#(?<=/)[0-9]+$#', $uri, $match)) {
			return $match[0];
		}
	}
	
	
	static function normalizeUri($uri) {
		if (strpos($uri, 'channel') !== false) {
			return '/channels/' . self::parseId($uri);
		}
		else if (strpos($uri, 'album') !== false) {
			return '/albums/' . self::parseId($uri);
		} else {
			return $uri;
		}
	}
	
	
	function getDescription() {
		return parent::getContent();
	}
	
	
	function setDescription($desc) {
		return parent::setContent($desc);
	}
	
	
	function getVimeoUri() {
		return $this->getPostMeta(self::META_VIMEO_URI);
	}
	
	
	function setVimeoUri($uri) {
		return $this->setPostMeta(self::META_VIMEO_URI, $uri);
	}
	
	
	function getVideo($videoId) {
		$video = Vimeo::getInstance()->request($this->getVimeoUri() .'/videos/'. $videoId);
		if (!empty($video['body'])) {
			if (empty($video['body']['error']) AND !empty($video['body']['uri'])) {
				return new Video($video['body'], $this);
			}
		}
	}
	
	
	static function getAll() {
		$posts = get_posts(array('post_type' => static::POST_TYPE));
		$result = array();
		foreach ($posts as $post) {
			$result[$post->ID] = static::getInstance($post);
		}
		return $result;
	}
	
	
	/**
	 * Get channels visible for given user.
	 * 
	 * @param int $userId
	 * @return array
	 */
	static function getVisible($userId) {
		return self::getAll();
	}
	
	
	function getVideos($pagination = array()) {
		
		$pagination = shortcode_atts(array(
			'page' => 1,
			'per_page' => Settings::getOption(Settings::OPTION_PAGINATION_LIMIT),
		), $pagination);
		
		$results = array();
		
		$response = Vimeo::getInstance()->request($this->getVimeoUri() .'/videos', array(
			'page' => $pagination['page'],
			'per_page' => $pagination['per_page'],
			'sort' => Settings::getOption(Settings::OPTION_VIDEO_SORT_METHOD),
			'direction' => Settings::getOption(Settings::OPTION_VIDEO_SORT_DIRECTION),
		));
		if (!empty($response['body']['data'])) {
			foreach ($response['body']['data'] as $video) {
				if (empty($video['error']) AND !empty($video['uri'])) {
					$results[] = new Video($video, $this);
				}
			}
		}
		if (isset($response['body']['total'])) {
			$this->totalVideos = $response['body']['total'];
		}
		
		return $results;
		
	}
	
	
	function getAllVideos($cacheDuration = false) {
		
		$results = array();
		$page = 1;
		
		do {
			$videos = $this->getVideos(array(
				'page' => $page,
				'per_page' => self::MAX_PER_PAGE,
			));
			$results = array_merge($results, $videos);
			$page++;
		} while (!empty($videos));
		
		if ($cacheDuration) $this->cacheDuration($results);
		
		return $results;
		
	}
	
	
	function getTotalVideos() {
		return $this->totalVideos;
	}
	
	
	function getPermalinkForCategory(Category $category) {
		return trailingslashit($category->getPermalink() . $this->getSlug());
	}
	
	
	function canView($userId = null) {
		if (is_null($userId)) $userId = get_current_user_id();
		return apply_filters('cmvl_channel_can_view', static::checkViewAccess(), $this, $userId);
	}
	
	
	static function checkViewAccess() {
		switch (Settings::getOption(Settings::OPTION_ACCESS_VIEW)) {
			case Settings::ACCESS_LOGGED_IN_USERS:
				return is_user_logged_in();
			default:
			case Settings::ACCESS_EVERYONE:
				return true;
		}
	}
	
	
	protected function cacheDuration($videos) {
		$duration = $this->sumDuration($videos);
		if ($duration != $this->getDuration()) {
			update_post_meta($this->getId(), self::META_DURATION, $duration);
		}
		return $this;
	}
	
	
	protected function sumDuration($videos) {
		$duration = 0;
		foreach ($videos as $video) {
			$duration += $video->getDuration();
		}
		return $duration;
	}
	
	
	function getDuration() {
		$duration = get_post_meta($this->getId(), self::META_DURATION, $single = true);
		if (!$duration) {
			$duration = $this->sumDuration($this->getAllVideos($cacheDuration = false));
			update_post_meta($this->getId(), self::META_DURATION, $duration);
		}
		return $duration;
	}
	
	
	static function getChannelsSummaryDuration() {
		global $wpdb;
		return $wpdb->get_var($wpdb->prepare("SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = %s", self::META_DURATION));
	}
	
	
	function getEditUrl() {
		return admin_url(sprintf('post.php?action=edit&post=%d',
			$this->getId()
		));
	}
	

	function getCategories() {
		$terms = wp_get_post_terms($this->getId(), Category::TAXONOMY);
		foreach ($terms as &$term) {
			$term = Category::getInstance($term);
		}
		return $terms;
	}
	
	
	function addDefaultCategory() {
		$term = get_term('General', Category::TAXONOMY);
		if (empty($term)) {
			$terms = get_terms(array(Category::TAXONOMY), array('hide_empty' => false));
			if (!empty($terms)) {
				$term = reset($terms);
			}
		}
		if (!empty($term)) {
			wp_set_post_terms($this->getId(), $term->term_id, Category::TAXONOMY);
		}
	}
	
	
	
}
