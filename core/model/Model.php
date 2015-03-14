<?php

namespace com\cminds\videolessons\model;

abstract class Model {
	
	
	static function bootstrap() {
		add_action('init', array(get_called_class(), 'init'), 2);
	}
	
	static function init() {
// 		var_dump(get_called_class());
	}
	
	
}