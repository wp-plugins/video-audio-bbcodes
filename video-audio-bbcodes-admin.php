<?php

	function video_audio_bbcode_plugin_menu() {
		add_options_page('Video Audio BBCode', 'Video Audio BBCode', 'manage_options', 'video-audio-bbcodes', 'video_audio_bbcodes_plugin_options');
	}

	add_action('admin_menu', 'video_audio_bbcode_plugin_menu');

	function video_audio_bbcodes_plugin_options() {
		global $bbp_sc_whitelist;

		if (!current_user_can('manage_options'))  {
			echo '<div class="wrap"><p>No options currently available.  Sorry!</p></div>';
		} else {
			$bbcodes_active = true;
			$whitelist_enabled = is_plugin_active('bbpress2-shortcode-whitelist/bbpress2-shortcode-whitelist.php');

			if($whitelist_enabled) {
				$enabled_plugins = get_option('bbpscwl_enabled_plugins');  
				if($enabled_plugins == '') $enabled_plugins = array();
				else $enabled_plugins = unserialize($enabled_plugins);

				$bbcodes_active = false;
				foreach($enabled_plugins as $plugin_tag) {
					if($plugin_tag == 'video-audio-bbcode') $bbcodes_active = true;
				}
			}

			include(VIDEO_AUDIO_BBCODE_PATH.'/options-form-template.php');
		}
	}
?>
