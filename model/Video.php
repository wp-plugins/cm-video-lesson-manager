<?php

namespace com\cminds\videolesson\model;

class Video extends Model {
	
	const META_POST_VIDEO_DURATION = 'cmvl_video_duration';
	
	protected $video;
	protected $channel;
	protected $searchScore;
	
	
	function __construct($video, Channel $channel) {
		
		$this->video = $video;
		$this->channel = $channel;
		
		$this->cacheDuration();
		
	}
		
		
	protected function cacheDuration() {
		$channelId = $this->getChannel()->getId();
		$metaKey = self::META_POST_VIDEO_DURATION .'_'. $this->getId();
		if (!get_post_meta($channelId, $metaKey, $single = true)) {
			update_post_meta($channelId, $metaKey, $this->getDuration());
		}
		return $this;
	}
	
	
	function getTitle() {
		return $this->video['name'];
	}
	
	
	function getDescription() {
		return $this->video['description'];
	}
	
	
	function getDuration() {
		return $this->video['duration'];
	}
	
	
	function getDurationFormatted() {
		$duration = $this->getDuration();
		if ($duration > 3600) return Date('H:i:s', $duration);
		else return Date('i:s', $duration);
	}
	
	
	function getThumbUri($minWidth = 100) {
		if (!empty($this->video['pictures']['sizes'])) {
			foreach ($this->video['pictures']['sizes'] as $picture) {
				if ($picture['width'] >= $minWidth) {
					return $picture['link'];
				}
			}
		}
	}
	
	
	function getScreenshot() {
		if (!empty($this->video['pictures']['sizes'])) {
			$picture = end($this->video['pictures']['sizes']);
			return $picture['link'];
		}
	}
	
	
	function getChannel() {
		return $this->channel;
	}
	
	
	function getPermalink() {
		return add_query_arg('video', urlencode($this->getId()), $this->getChannel()->getPermalink());
	}
	
	
	function getPlayerUrl(array $options = array()) {
		$options = shortcode_atts(array(
			'api' => 1,
			'autoplay' => 0,
			'badge' => 0,
			'byline' => 0,
			'portrait' => 0,
			'title' => 0,
			'player_id' => null,
		), $options);
		return add_query_arg(urlencode_deep($options), '//player.vimeo.com/video/' . urlencode($this->getId()));
	}
	
	
	function getPlayer(array $options = array()) {
		$this->unlock();
		if (empty($options['player_id'])) $options['player_id'] = 'cmvl-player-' . rand(0, 99999);
		return '<iframe id="'. esc_attr($options['player_id']) .'" src="'. esc_attr($this->getPlayerUrl($options))
			. '" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
	}
	
	
	function getId() {
		if (isset($this->video['uri'])) {
			return preg_replace('/[^0-9]/', '', $this->video['uri']);
		}
	}
	
	
	function getVimeoUri() {
		return '/videos/'. $this->getId();
	}
	
	
	function getPrivacyView() {
		return $this->video['privacy']['view'];
	}
	
	
	function setPrivacyView($value) {
		Vimeo::getInstance()->request($this->getVimeoUri(), array('privacy' => array('view' => $value)), 'PATCH');
		return $this;
	}
	
	
	function getPrivacyEmbed() {
		return $this->video['privacy']['embed'];
	}
	
	
	function setPrivacyEmbed($value) {
		Vimeo::getInstance()->request($this->getVimeoUri(), array('privacy' => array('embed' => $value)), 'PATCH');
		$this->clearCache();
		return $this;
	}
	
	
	function getPrivacyDomains() {
		$results = array();
		$vimeo = Vimeo::getInstance();
		$vimeo->disableCacheOnce();
		$domains = $vimeo->request($this->getVimeoUri() . '/privacy/domains');
		if (!empty($domains['body']['data'])) foreach ($domains['body']['data'] as $domain) {
			$results[] = $domain['domain'];
		}
		return $results;
	}
	
	
	function addPrivacyDomain($domain = null) {
		if (is_null($domain)) {
			$domain = preg_replace('/^www./', '', $_SERVER['HTTP_HOST']);
		}
		$result = Vimeo::getInstance()->request($this->getVimeoUri() . '/privacy/domains/'. urlencode($domain), array(), 'PUT');
		return $this;
	}
	
	
	function unlock() {
		$vimeo = Vimeo::getInstance();
		// This is no longer needed:
// 		if ($this->getPrivacyView() != 'anybody') {
// 			$this->setPrivacyView('anybody');
// 		}
		if ($this->getPrivacyEmbed() == 'private') {
			$this->setPrivacyEmbed('whitelist');
		}
		$domain = preg_replace('/^www./', '', $_SERVER['HTTP_HOST']);
		if (!in_array($domain, $this->getPrivacyDomains())) {
			$this->addPrivacyDomain($domain);
		}
	}
	
	
	function clearCache() {
		$vimeo = Vimeo::getInstance();
		$vimeo->disableCacheOnce();
		$result = $vimeo->request($this->getVimeoUri());
		if (!empty($result['body']['data'])) {
			$this->video = $result['body']['data'];
		}
		return $this;
	}
	
	
	static function getAll() {
		$channels = Channel::getAll();
		$results = array();
		foreach ($channels as $channel) {
			$videos = $channel->getVideos();
			foreach ($videos as $video) {
				$results[$video->getId()] = $video;
			}
		}
		return $results;
	}
	
	
	function setSearchScore($score) {
		$this->searchScore = $score;
		return $this;
	}
	
	function getSearchScore() {
		return $this->searchScore;
	}
	
	
	
	static function search($str, $context) {
		
		foreach ($context as $video) {
			$score = 0;
			
		}
		
		
		$resposne = Vimeo::getInstance()->request('/me/videos', array('query' => $str, 'per_page' => 50));
// 		var_dump($resposne);
		$videosResults = array();
		if (!empty($resposne['body']['data'])) {
			foreach ($resposne['body']['data'] as $video) {
				$videoId = Channel::parseId($video['uri']);
				$videosResults[$videoId] = $videoId;
			}
		}
		
		// Filter videos which are associated with context
		foreach ($videosResults as $videoId => &$video) {
			if (isset($context[$videoId])) {
				$video = $context[$videoId];
			} else {
				$video = null;
			}
		}
		
		return array_filter($videosResults);
		
	}
	
	
	function getChannelId() {
		return $this->getChannel()->getId();
	}
	
	
	
}
