<?php /*

**************************************************************************

Plugin Name:  Video and Audio BBCodes
Plugin URI:   http://wp.antonchanning.com/video-audio-bbcodes/
Description:  Adds support for video and audio bbcodes to wordpress, buddypress and bbpress
Version:      1.1
Author:       Anton Channing
Author URI:   http://wp.antonchanning.com

**************************************************************************

Copyright (C) 2013 Anton Channing

***** GPL3 *****
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

define('VIDEO_AUDIO_BBCODE_PATH', WP_CONTENT_DIR.'/plugins/'.plugin_basename(dirname(__FILE__)) );

//Admin options
include(VIDEO_AUDIO_BBCODE_PATH.'/video-audio-bbcodes-admin.php');

//Classes
include(VIDEO_AUDIO_BBCODE_PATH.'/class_video-audio-bbcodes.php');

// Start this plugin once all other plugins are fully loaded
add_action( 'plugins_loaded', create_function( '', 'global $VideoAudioBBCode; $VideoAudioBBCode = new VideoAudioBBCode();' ) );
?>
