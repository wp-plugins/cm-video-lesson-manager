<?php

namespace com\cminds\videolessons\shortcode;

use com\cminds\videolessons\App;

use com\cminds\videolessons\controller\BookmarkController;

use com\cminds\videolessons\model\Settings;

use com\cminds\videolessons\model\Category;
use com\cminds\videolessons\model\Channel;
use com\cminds\videolessons\controller\ChannelController;


class PlaylistShortcode extends Shortcode {
	
	const SHORTCODE_NAME = 'cmvl-playlist';
	
	
	static function shortcode($atts) {
		
		$atts = shortcode_atts(array(
			'navbar' => 1,
			'ajax' => 1,
			'view' => '',
			'category' => null,
			'channel' => null,
		), $atts);
		
		$navbar = $atts['navbar'];
		
		$categoriesTree = Category::getTree(array('hide_empty' => 1));
		$currentCategory = null;
		if (!empty($atts['category']) AND $currentCategory = Category::getInstance($atts['category'])) {
			// ok
		} else {
			$atts['category'] = key($categoriesTree);
			$currentCategory = Category::getInstance($atts['category']);
		}
		
		$channels = $currentCategory->getChannels();
		$currentChannel = null;
		if ($atts['channel'] == 'bookmarks') {
			// do nothing
		}
		else if (!empty($atts['channel']) AND $currentChannel = Channel::getInstance($atts['channel'])) {
			// ok
		}
		else if ($channels) {
			$atts['channel'] = $channels[0]->getId();
			$currentChannel = Channel::getInstance($atts['channel']);
		}
		else $atts['channel'] = null;
		
		$result = '';
		$displayOptions = array();
		
		// Navbar
		if (!empty($atts['navbar']) AND $atts['channel'] != 'bookmarks' AND Channel::checkViewAccess()) {
			$currentChannelId = ($currentChannel ? $currentChannel->getId() : null);
			$currentCategoryId = ($currentCategory ? $currentCategory->getId() : null);
			$result .= ChannelController::loadView('frontend/playlist/navbar',
				compact('currentChannel', 'currentChannelId', 'currentCategory', 'currentCategoryId', 'categoriesTree', 'channels'), true);
		}
		
		// Playlist
		if (!empty($atts['channel'])) {
			if ($atts['channel'] == 'bookmarks' AND class_exists(App::namespaced('controller\\BookmarkController'))) {
				$result .= BookmarkController::render($atts['view']);
			} else {
				$result .= ChannelController::playlist($atts['channel'], $atts['view']);
			}
		}
		
		$extra = '';
		if ($atts['ajax']) {
			$extra .= ' data-use-ajax="1"';
		}
		
		return self::wrap($result, $extra);
	}

	
}
