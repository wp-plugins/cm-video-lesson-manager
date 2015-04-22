<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\model\Micropayments;

use com\cminds\videolesson\App;

class UpdateController extends Controller {
	
	const OPTION_NAME = 'cmvl_update_methods';

	static function bootstrap() {
		global $wpdb;
		
		$updates = get_option(self::OPTION_NAME);
		if (empty($updates)) $updates = array();
		$count = count($updates);
		
		$methods = get_class_methods(__CLASS__);
		foreach ($methods as $method) {
			if (preg_match('/^update((_[0-9]+)+)$/', $method, $match)) {
				if (!in_array($method, $updates)) {
					call_user_func(array(__CLASS__, $method));
					$updates[] = $method;
				}
			}
		}
		
		if ($count != count($updates)) {
			update_option(self::OPTION_NAME, $updates);
		}
		
	}
	
	
	static function update_1_0_3() {
		global $wpdb;
		
		if (!App::isPro()) return;
		
		// Get subscription records in old format:
		$records = $wpdb->get_results($wpdb->prepare("SELECT * FROM $wpdb->postmeta WHERE meta_key LIKE %s", 'cmvl_mp_subscription_%'), ARRAY_A);
		foreach ($records as $row) {
			if (preg_match('/^cmvl_mp_subscription_[0-9]+$/', $row['meta_key'])) {
				$value = @unserialize($row['meta_value']);
				if (!empty($value) AND is_array($value)) {
					
					// Create subscription records in new format
					$postId = $row['post_id'];
					$metaId = add_post_meta($postId, Micropayments::META_MP_SUBSCRIPTION, $value['userId'], $unique = false);
					add_post_meta($postId, Micropayments::META_MP_SUBSCRIPTION_START .'_'. $metaId, $value['start'], $unique = true);
					add_post_meta($postId, Micropayments::META_MP_SUBSCRIPTION_END .'_'. $metaId, $value['stop'], $unique = true);
					add_post_meta($postId, Micropayments::META_MP_SUBSCRIPTION_DURATION .'_'. $metaId, $value['period'], $unique = true);
					add_post_meta($postId, Micropayments::META_MP_SUBSCRIPTION_POINTS .'_'. $metaId, 0, $unique = true);
					
					// Delete old record
					$wpdb->delete($wpdb->postmeta, array('meta_id' => $row['meta_id']));
					
				}
			}
		}
		
	}
	
}
