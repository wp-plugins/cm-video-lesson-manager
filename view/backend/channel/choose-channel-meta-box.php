<?php

use com\cminds\videolessons\model\Channel;

if (!empty($channels['body']['total'])) {
	echo '<section class="cmvl-tiles cmvl-channels" id="cmvl-choose-channel">';
	foreach ($channels['body']['data'] as $channel) {
		$channelId = Channel::parseId($channel['uri']);
		printf('<figure data-name="%s" data-description="%s">
				<label>
					%s
					<figcaption><input type="radio" name="cmvl_vimeo_channel_id" value="%d"%s />%s</figcaption>
				</label>
			</figure>',
			esc_attr($channel['name']),
			esc_attr(json_encode($channel['description'])),
// 			(empty($channel['pictures']['sizes'][0]['link']) ? '' : sprintf('<img src="%s" alt="Thumb" />', esc_attr($channel['pictures']['sizes'][0]['link']))),
			'',
			$channelId,
			($currentChannelId == $channelId ? ' checked="checked"' : ''),
			esc_html($channel['name'])
		);
	}
	echo '</section>';
} else {
	echo '<p>No channels found. Please check if there are any channels on your Vimeo account
		and you have provided the <a href="'. esc_attr('admin.php?page=cmvl-settings') .'">Vimeo API Settings</a>.</p>';
}
