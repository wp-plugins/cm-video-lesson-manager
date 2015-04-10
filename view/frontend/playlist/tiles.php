<?php

use com\cminds\videolesson\model\Labels;

?>

<section class="cmvl-playlist">
	<?php if (empty($videos)): ?>
		<p class="cmvl-no-videos"><?php echo Labels::getLocalized('msg_no_videos'); ?></p>
	<?php else: ?>
		<div class="cmvl-tiles"><?php foreach ($videos as $video):
			printf('<figure class="cmvl-video" data-video-id="%s" data-channel-id="%s">', $video->getId(), $video->getChannel()->getId()); ?>
				<header>
					<ul class="cmvl-controls"><?php echo apply_filters('cmvl_video_controls', '', $video); ?></ul>
					<h2><?php echo esc_html($video->getTitle()); ?></h2>
				</header>
				<div class="cmvl-player-outer"><?php echo $video->getPlayer(); ?></div>
				<figcaption><div class="cmvl-description-inner"><?php echo $video->getDescription(); ?></div></figcaption>
				<?php do_action('cmvl_video_bottom', $video); ?>
			<?php echo '</figure>'; ?>
		<?php endforeach; ?></div>
	<?php endif; ?>
</section>