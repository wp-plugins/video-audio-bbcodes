<?php /*

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

class VideoAudioBBCode {
	public $use_whitelist = false;
	public $swfobjects = array();

	// Plugin initialization - modded by Anton Channing
	function __construct() {
		// This version only supports WP 2.5+ (learn to upgrade please!)
		if ( !function_exists('add_shortcode') ) return;

		// Audio shortcodes
		add_shortcode( 'freesound' , array(&$this, 'shortcode_freesound') );
		add_shortcode( 'FREESOUND' , array(&$this, 'shortcode_freesound') );

		// Video shortcodes
		add_shortcode( 'youtube' , array(&$this, 'shortcode_youtube') );		
		add_shortcode( 'YOUTUBE' , array(&$this, 'shortcode_youtube') );
		add_shortcode( 'vimeo' , array(&$this, 'shortcode_vimeo') );		
		add_shortcode( 'VIMEO' , array(&$this, 'shortcode_vimeo') );
		add_shortcode( 'googlevideo' , array(&$this, 'shortcode_gvideo') );
		add_shortcode( 'GOOGLEVIDEO' , array(&$this, 'shortcode_gvideo') );
		add_shortcode( 'gvideo' , array(&$this, 'shortcode_gvideo') );
		add_shortcode( 'GVIDEO' , array(&$this, 'shortcode_gvideo') );
		add_shortcode( 'video' , array(&$this, 'shortcode_video') );
		add_shortcode( 'VIDEO' , array(&$this, 'shortcode_video') );

		$this->use_whitelist = true;
	}

	function do_shortcode($content) {
		if(function_exists('bbp_whitelist_do_shortcode')) {
			return bbp_whitelist_do_shortcode($content);
		} else {
			return do_shortcode($content);
		}
	}

	// No-name attribute fixing - modded by Anton Channing
	function attributefix( $atts = array() ) {
		if ( empty($atts[0]) ) return $atts;

		if ( 0 !== preg_match( '#=("|\')(.*?)("|\')#', $atts[0], $match ) )
			$atts[0] = $match[2];
		return $atts;
	}

	// freesound shortcode - by Anton Channing
	function shortcode_freesound( $atts = array(), $content = NULL ) {
		if ( "" === $content ) return __('No Freesound Audio ID Set');

		if(empty($atts)) {
			// [freesound]164929[/freesound]
			$size = 'medium';
		} else {
			// [freesound=large]164929[/freesound]
			// [freesound=small]164929[/freesound]
			$size = $this->attributefix( $atts );
		    	$size = trim(array_shift($size),'="'); //Remove quotes and equals.
		}
		$id = $text = $content;
		switch($size) {
			case 'large':
			case 'l':
				return '<iframe frameborder="0" scrolling="no" src="http://www.freesound.org/embed/sound/iframe/' .$id . '/simple/large/" width="920" height="245"></iframe>';
				break;
			case 'small':
			case 's':
				return '<iframe frameborder="0" scrolling="no" src="http://www.freesound.org/embed/sound/iframe/' .$id . '/simple/small/" width="375" height="30"></iframe>';
				break;
			case 'medium':
			case 'm':
			default:
				return '<iframe frameborder="0" scrolling="no" src="http://www.freesound.org/embed/sound/iframe/' .$id . '/simple/medium/" width="481" height="86"></iframe>';
				break;
		}
	}

	// Generate a placeholder ID?  Probably not needed
	function videoid( $type ) {
		global $post;

		if ( empty($post) || empty($post->ID) ) {
			$objectid = uniqid("vvq-$type-");
		} else {
			$count = 1;
			$objectid = 'vvq-' . $post->ID . '-' . $type . '-' . $count;

			while ( !empty($this->usedids[$objectid]) ) {
				$count++;
				$objectid = 'vvq-' . $post->ID . '-' . $type . '-' . $count;
			}

			$this->usedids[$objectid] = true;
		}

		return $objectid;
	}	

	// Is a string a URL? Not as perfect as esc_url() validation but it'll do
	function is_url( $string ) {
		return preg_match( '#^https?://#i', $string );
	}	

	// Reverse the parts we care about (and probably some we don't) of wptexturize() which gets applied before shortcodes
	function wpuntexturize( $text ) {
		$find = array( '&#8211;', '&#8212;', '&#215;', '&#8230;', '&#8220;', '&#8217;s', '&#8221;', '&#038;' );
		$replace = array( '--', '---', 'x', '...', '``', '\'s', '\'\'', '&' );
		return str_replace( $find, $replace, $text );
	}
	
	// Handle YouTube shortcodes - forked from Vipers Video Shortcodes
	function shortcode_youtube( $atts, $content = '' ) {
		$origatts = $atts;
		$content = $this->wpuntexturize( $content );

		// Handle WordPress.com shortcode format
		if ( isset($atts[0]) ) {
			$atts = $this->attributefix( $atts );
			$content = $atts[0];
			unset($atts[0]);
		}

		if ( empty($content) )
			return $this->error( sprintf( __('No URL or video ID was passed to the %s BBCode', 'vipers-video-quicktags'), __('YouTube') ) );

		if ( is_feed() )
			return $this->postlink();

		// Set any missing $atts items to the defaults
		$atts = shortcode_atts(array(
			'width'      => 480,
			'height'     => 360,
			'color1'     => '#660000',
			'color2'     => '#990000',
			'border'     => 0,
			'rel'        => 0,
			'fs'         => 1,
			'autoplay'   => 0,
			'loop'       => 0,
			'showsearch' => 0,
			'showinfo'   => 0,
			'hd'         => 0,
		), $atts);

		// Allow other plugins to modify these values (for example based on conditionals)
		$atts = apply_filters( 'video_audio_bbcodeatts', $atts, 'youtube', $origatts );

		// If a URL was passed
		if ( $this->is_url( $content ) ) {

			// Playlist URL ( http://www.youtube.com/playlist?list=PLXXXXX )
			if ( false !== stristr( $content, 'playlist' ) ) {
				preg_match( '#https?://(www.youtube|youtube|[A-Za-z]{2}.youtube)\.com/playlist\?list=([\w-]+)(.*?)#i', $content, $matches );
				if ( empty( $matches ) || empty( $matches[2] ) )
					return $this->error( sprintf( __( 'Unable to parse URL, check for correct %s format', 'vipers-video-quicktags' ), __( 'YouTube' ) ) );

				// Hack until this plugin properly supports iframe-based embeds
				$iframe = 'http://www.youtube.com/embed/videoseries?list=' . $matches[2];
			}
			// Legacy playlists ( http://www.youtube.com/view_play_list?p=XXX )
			elseif ( FALSE !== stristr( $content, 'view_play_list' ) ) {
				preg_match( '#https?://(www.youtube|youtube|[A-Za-z]{2}.youtube)\.com/view_play_list\?p=([\w-]+)(.*?)#i', $content, $matches );
				if ( empty($matches) || empty($matches[2]) ) return $this->error( sprintf( __('Unable to parse URL, check for correct %s format', 'vipers-video-quicktags'), __('YouTube') ) );

				$youtube_id = $matches[2];
				$embedpath = 'p/' . $youtube_id;
				$fallbacklink = $fallbackcontent = 'http://www.youtube.com/view_play_list?p=' . $matches[2];
			}
			// Short youtu.be URL
			elseif ( FALSE !== stristr( $content, 'youtu.be' ) ) {
				preg_match( '#https?://youtu\.be/([\w-]+)#i', $content, $matches );
				if ( empty($matches) || empty($matches[1]) )
					return $this->error( sprintf( __('Unable to parse URL, check for correct %s format', 'vipers-video-quicktags'), __('YouTube') ) );

				$youtube_id = $matches[1];
				$embedpath = 'v/' . $youtube_id;
				$fallbacklink = 'http://www.youtube.com/watch?v=' . $youtube_id;
				$fallbackcontent = '<img src="' . esc_url( 'http://img.youtube.com/vi/' . $matches[1] . '/0.jpg' ) . '" alt="' . esc_attr__('YouTube Preview Image', 'vipers-video-quicktags') . '" />';
			}
			// Normal video URL
			else {
				preg_match( '#https?://(www.youtube|youtube|[A-Za-z]{2}.youtube)\.com/(watch\?v=|w/\?v=|\?v=)([\w-]+)(.*?)#i', $content, $matches );
				if ( empty($matches) || empty($matches[3]) ) return $this->error( sprintf( __('Unable to parse URL, check for correct %s format', 'vipers-video-quicktags'), __('YouTube') ) );

				$youtube_id = $matches[3];
				$embedpath = 'v/' . $youtube_id;
				$fallbacklink = 'http://www.youtube.com/watch?v=' . $youtube_id;
				$fallbackcontent = '<img src="' . esc_url( 'http://img.youtube.com/vi/' . $matches[3] . '/0.jpg' ) . '" alt="' . esc_attr__('YouTube Preview Image', 'vipers-video-quicktags') . '" />';
			}
		}
		// If a URL wasn't passed, assume a video ID was passed instead
		else {
			$youtube_id = $content;
			$embedpath = 'v/' . $youtube_id;
			$fallbacklink = 'http://www.youtube.com/watch?v=' . $youtube_id;
			$fallbackcontent = '<img src="' . esc_url( 'http://img.youtube.com/vi/' . $youtube_id . '/0.jpg' ) . '" alt="' . esc_attr__('YouTube Preview Image', 'vipers-video-quicktags') . '" />';
		}

		// Setup the parameters
		$color1 = $color2 = $border = $autoplay = $loop = $showsearch = $showinfo = $hd = '';

		if ( '' != $atts['color1'] && $this->defaultsettings['youtube']['color1'] != $atts['color1'] )
			$color1 = '&color1=0x' . str_replace( '#', '', $atts['color1'] );

		if ( '' != $atts['color2'] && $this->defaultsettings['youtube']['color2'] != $atts['color2'] )
			$color2 = '&color2=0x' . str_replace( '#', '', $atts['color2'] );

		if ( $atts['border'] )
			$border = '&border=1';

		if ( $atts['autoplay'] )
			$autoplay = '&autoplay=1';

		if ( $atts['loop'] )
			$loop = '&loop=1';

		if ( $atts['hd'] )
			$hd = '&hd=1';

		$rel        = ( 1 == $atts['rel'] ) ? '1' : '0';
		$fs         = ( 1 == $atts['fs'] ) ? '1' : '0';
		$showsearch = ( 1 == $atts['showsearch'] ) ? '1' : '0';
		$showinfo   = ( 1 == $atts['showinfo'] ) ? '1' : '0';

		$atts['width']  = absint( $atts['width'] );
		$atts['height'] = absint( $atts['height'] );


		//$objectid = $this->videoid('youtube');

		// Hack until this plugin properly supports iframe-based embeds
		if ( ! empty( $iframe ) ) {
			return '<iframe class="vabbox vabyoutube" width="' . esc_attr( $atts['width'] ) . '" height="' . esc_attr( $atts['height'] ) . '" src="'. esc_url( $iframe . '&rel=' . $rel . '&fs=' . $fs . '&showsearch=' . $showsearch . '&showinfo=' . $showinfo . $autoplay . $loop . $hd ) . '" frameborder="0" allowfullscreen></iframe>';
		}
		
		return '<iframe width="'.$atts['width'].'" height="'.$atts['height'].'" src="http://www.youtube.com/embed/'.$youtube_id.'" frameborder="0" allowfullscreen></iframe>';
		//return '<span class="vabbox vabyoutube" style="' . esc_attr( 'width:' . $atts['width'] . 'px;height:' . $atts['height'] . 'px;' ) . '"><span id="' . esc_attr( $objectid ) . '"><a href="' . esc_url( $fallbacklink ) . '">' . $fallbackcontent . '</a></span></span>';
	}

	// Handle Vimeo shortcodes
	function shortcode_vimeo( $atts, $content = '' ) {
		$origatts = $atts;
		$content = $this->wpuntexturize( $content );

		// Handle malformed WordPress.com shortcode format
		if ( isset($atts[0]) ) {
			$atts = $this->attributefix( $atts );
			$content = $atts[0];
			unset($atts[0]);
		}

		if ( empty($content) )
			return $this->error( sprintf( __('No URL or video ID was passed to the %s BBCode', 'vipers-video-quicktags'), __('Vimeo', 'vipers-video-quicktags') ) );

		if ( is_feed() )
			return $this->postlink();

		// Set any missing $atts items to the defaults
		$atts = shortcode_atts(array(
			'width'      => 480,
			'height'     => 360,
			'color'      => '#660000',
			'portrait'   => 0,
			'title'      => 1,
			'byline'     => 1,
			'fullscreen' => 1,
		), $atts);

		// Allow other plugins to modify these values (for example based on conditionals)
		$atts = apply_filters( 'video_audio_bbcodeatts', $atts, 'vimeo', $origatts );

		// If a URL was passed
		if ( $this->is_url( $content ) ) {
			preg_match( '#https?://(www.vimeo|vimeo)\.com(/|/clip:)(\d+)(.*?)#i', $content, $matches );
			if ( empty($matches) || empty($matches[3]) ) return $this->error( sprintf( __('Unable to parse URL, check for correct %s format', 'vipers-video-quicktags'), __('Vimeo', 'vipers-video-quicktags') ) );

			$videoid = $matches[3];
		}
		// If a URL wasn't passed, assume a video ID was passed instead
		else {
			$videoid = $content;
		}

		// Setup the parameters
		$portrait   = ( 1 == $atts['portrait'] )   ? '1' : '0';
		$title      = ( 1 == $atts['title'] )      ? '1' : '0';
		$byline     = ( 1 == $atts['byline'] )     ? '1' : '0';
		$fullscreen = ( 1 == $atts['fullscreen'] ) ? '1' : '0';

		$iframeurl = 'http://player.vimeo.com/video/' . $videoid;
		foreach ( array( 'title', 'byline', 'portrait', 'fullscreen' ) as $attribute ) {
			$iframeurl = add_query_arg( $attribute, $$attribute, $iframeurl );
		}

		if ( '' != $atts['color'] && $this->defaultsettings['vimeo']['color'] != $atts['color'] )
			$iframeurl = add_query_arg( 'color', str_replace( '#', '', $atts['color'] ), $iframeurl );

		$atts['width']  = absint( $atts['width'] );
		$atts['height'] = absint( $atts['height'] );


		//$objectid = $this->videoid('vimeo');

		return '<iframe src="http://player.vimeo.com/video/'.$videoid.'" width="'.$atts['width'].'" height="'.$atts['height'].'" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>';

		//return '<span class="vvqbox vvqvimeo" style="' . esc_attr( 'width:' . $atts['width'] . 'px;height:' . $atts['height'] . 'px;' ) . '"><iframe id="' . esc_attr( $objectid ) . '" src="' . esc_url( $iframeurl ) . '" width="' . esc_attr( $atts['width'] ) . '" height="' . esc_attr( $atts['height'] ) . '" frameborder="0"><a href="' . esc_url( 'http://www.vimeo.com/' . $videoid ) . '">' . esc_url( 'http://www.vimeo.com/' . $videoid ) . '</a></iframe></span>';
	}
	
	// bOingball - google video shortcode - modded by Anton Channing
	function shortcode_gvideo( $atts = array(), $content = NULL ) {
		if ( "" === $content ) return 'No Google Video ID Set';
		$id = $text = $content;
		return '<embed style="width:400px; height:325px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=' . $id . '&hl=en"></embed>';
	}	

	// Handle Google Video shortcodes
	function shortcode_googlevideo( $atts, $content = '' ) {
		$origatts = $atts;
		$content = $this->wpuntexturize( $content );

		// Handle WordPress.com shortcode format
		if ( isset($atts[0]) ) {
			$atts = $this->attributefix( $atts );
			$content = $atts[0];
			unset($atts[0]);
		}

		if ( empty($content) )
			return $this->error( sprintf( __('No URL or video ID was passed to the %s BBCode', 'vipers-video-quicktags'), __('Google Video', 'vipers-video-quicktags') ) );

		if ( is_feed() )
			return $this->postlink();

		// Set any missing $atts items to the defaults
		$atts = shortcode_atts(array(
			'width'    => 480,
			'height'   => 360,
			'autoplay' => 0,
			'fs'       => 1,
		), $atts);

		// Allow other plugins to modify these values (for example based on conditionals)
		$atts = apply_filters( 'vvq_shortcodeatts', $atts, 'googlevideo', $origatts );

		// If a URL was passed
		if ( $this->is_url( $content ) ) {
			preg_match( '#https?://video\.google\.([A-Za-z.]{2,5})/videoplay\?docid=([\d-]+)(.*?)#i', $content, $matches );
			if ( empty($matches) || empty($matches[2]) ) return $this->error( sprintf( __('Unable to parse URL, check for correct %s format', 'vipers-video-quicktags'), __('Google Video', 'vipers-video-quicktags') ) );

			$videoid = $matches[2];
		}
		// If a URL wasn't passed, assume a video ID was passed instead
		else {
			$videoid = $content;
		}

		// Setup the parameters
		$flashvars = array();
		if ( 1 == $atts['autoplay'] ) $flashvars['autoplay'] = '1';
		if ( 1 == $atts['fs'] )       $flashvars['fs']       = 'true';

		$atts['width']  = absint( $atts['width'] );
		$atts['height'] = absint( $atts['height'] );

		//$objectid = $this->videoid('googlevideo');

		//$this->swfobjects[$objectid] = array( 'width' => $atts['width'], 'height' => $atts['height'], 'url' => 'http://video.google.com/googleplayer.swf?docid=' . $videoid, 'flashvars' => $flashvars );
		return '<embed style="width:480px; height:360px;" id="VideoPlayback" type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId='.$videoid.'&hl=en"></embed>';
	}

	// video shortcode - by Anton Channing
	function shortcode_video( $atts = array(), $content = NULL ) {
		global $VipersVideoQuicktags; 
		if("" === $content) return __('No video');

		// If content is a url, work out which shortcode to emulate
		if(false !== strpos($content,'youtube.com')) return $this->shortcode_youtube($atts, $content);
		if(false !== strpos($content,'video.google')) return $this->shortcode_googlevideo($atts, $content);
		//if(false !== strpos($content,'dailymotion.com')) return $VipersVideoQuicktags->shortcode_dailymotion($atts, $content);
		if(false !== strpos($content,'vimeo.com')) return $this->shortcode_vimeo($atts, $content);
		//if(false !== strpos($content,'veoh.com')) return $VipersVideoQuicktags->shortcode_veoh($atts, $content);
		//if(false !== strpos($content,'metacafe.com')) return $VipersVideoQuicktags->shortcode_metacafe($atts, $content);
		//if(false !== strpos($content,'flickr.com')) return $VipersVideoQuicktags->shortcode_flickrvideo($atts, $content);
		//if(false !== strpos($content,'ifilm.com')) return $VipersVideoQuicktags->shortcode_ifilm($atts, $content);
		//if(false !== strpos($content,'spike.com')) return $VipersVideoQuicktags->shortcode_ifilm($atts, $content);
		//if(false !== strpos($content,'myspace.com')) return $VipersVideoQuicktags->shortcode_myspace($atts, $content);
		//if(false !== strpos($content,'myspacetv.com')) return $VipersVideoQuicktags->shortcode_myspace($atts, $content);

		//Otherwise we have no choice but to assume its a plain old video file.
		//return $this->shortcode_videofile($atts, $content);
		return __('Video type not currently supported.');
	}	
}
?>
