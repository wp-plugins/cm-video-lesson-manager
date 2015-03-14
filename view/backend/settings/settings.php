<?php

use com\cminds\videolessons\controller\SettingsController;
use com\cminds\videolessons\view\SettingsView;
use com\cminds\videolessons\App;
use com\cminds\videolessons\model\Settings;


if (!empty($_GET['status']) AND !empty($_GET['msg'])) {
	printf('<div id="message" class="%s"><p>%s</p></div>', ($_GET['status'] == 'ok' ? 'updated' : 'error'), esc_html($_GET['msg']));
}


?><form method="post" id="settings">


<ul class="cmvl-settings-tabs"><?php

$tabs = apply_filters('cmvl_settings_pages', Settings::$categories);
foreach ($tabs as $tabId => $tabLabel) {
	printf('<li><a href="#tab-%s">%s</a></li>', $tabId, $tabLabel);
}

?></ul>

<div class="inner"><?php

$settingsView = new SettingsView();
echo $settingsView->render();

?></div>

<p class="form-finalize">
	<a href="<?php echo esc_attr($clearCacheUrl); ?>" class="right button">Clear cache</a>
	<input type="hidden" name="nonce" value="<?php echo wp_create_nonce(SettingsController::getMenuSlug()); ?>" />
	<input type="submit" value="Save" class="button button-primary" />
</p>

</form>

<h3>Vimeo API instructions</h3>
<ol>
	<li>To display more than one channel you need to have the Vimeo Plus or Pro account.
	When using the basic free account you can provide only one channel.</li>
	<li>Please go to <a href="https://developer.vimeo.com/apps">developer.vimeo.com/apps</a>
	and click the <strong>Create a new app</strong> button (you must have a Vimeo account).</li>
	<li>Enter as the App URL: <kbd><?php echo get_home_url(); ?></kbd></li>
	<li>When the new Application has been created, go to the <strong>Authentication</strong> tab.
	Copy the <strong>Client Identifier</strong> and <strong>Client Secret</strong> values and them put into the Video Lessons Settings.</li>
	<li>On the same <em>Authentication</em> tab, scroll down to the <strong>Generate an Access Token</strong> section.
	Check the <strong>Edit</strong> and <strong>Interact</strong> permission scopes and then press the <em>Generate Token</em> button.
	Copy <strong>Your new Access token</strong> value and put into the Video Lessons Settings.</li>
</ol>