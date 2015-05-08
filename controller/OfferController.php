<?php

namespace com\cminds\videolesson\controller;

use com\cminds\videolesson\App;

class OfferController extends Controller {
	
	const PAGE_YEARLY_OFFER = 'https://www.cminds.com/store/cm-wordpress-plugins-yearly-membership/';
	
	protected static $actions = array(array('name' => 'admin_menu', 'method' => 'offerMenuItem', 'priority' => 50));
	

	static function offerMenuItem() {
		global $submenu;
		if (current_user_can('manage_options')) {
			$submenu[App::MENU_SLUG][997] = array('Yearly membership offer', 'manage_options', self::PAGE_YEARLY_OFFER);
			add_action('admin_head', array(__CLASS__, 'offerEmbedCSS'));
		}
	}
	
	
	static function offerEmbedCSS() {
		echo '<style type="text/css">
        		#toplevel_page_cmvl a[href*="cm-wordpress-plugins-yearly-membership"] {color: white;}
    			a[href*="cm-wordpress-plugins-yearly-membership"]:before {font-size: 16px; vertical-align: middle; padding-right: 5px; color: #d54e21;
    				content: "\f487";
				    display: inline-block;
					-webkit-font-smoothing: antialiased;
					font: normal 16px/1 \'dashicons\';
    			}
    			#toplevel_page_cmvl a[href*="cm-wordpress-plugins-yearly-membership"]:before {vertical-align: bottom;}
  
        	</style>';
	}
	
	
}
