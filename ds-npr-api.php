<?php
/**
 * Plugin Name: NPR Story API
 * Description: A collection of tools for reusing content from NPR.org supplied by Digital Services.
 * Version: 1.5.2
 * Author: NPR Digital Services
 * License: GPLv2
*/
/*
    Copyright 2012 NPR Digital Services

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'NPR_STORY_ID_META_KEY', 'npr_story_id' );
define( 'NPR_API_LINK_META_KEY', 'npr_api_link' );
define( 'NPR_HTML_LINK_META_KEY', 'npr_html_link' );
define( 'NPR_SHORT_LINK_META_KEY', 'npr_short_link' );
define( 'NPR_STORY_CONTENT_META_KEY', 'npr_story_content' );
define( 'NPR_BYLINE_META_KEY', 'npr_byline' );
define( 'NPR_BYLINE_LINK_META_KEY', 'npr_byline_link' );
define( 'NPR_MULTI_BYLINE_META_KEY', 'npr_multi_byline' );
define( 'NPR_IMAGE_GALLERY_META_KEY', 'npr_image_gallery');
define( 'NPR_AUDIO_META_KEY', 'npr_audio');
define( 'NPR_AUDIO_M3U_META_KEY', 'npr_audio_m3u');
define( 'NPR_PUB_DATE_META_KEY', 'npr_pub_date');
define( 'NPR_STORY_DATE_MEATA_KEY', 'npr_story_date');
define( 'NPR_LAST_MODIFIED_DATE_KEY', 'npr_last_modified_date');
define( 'NPR_RETRIEVED_STORY_META_KEY', 'npr_retrieved_story');

define( 'NPR_IMAGE_CREDIT_META_KEY', 'npr_image_credit');
define( 'NPR_IMAGE_AGENCY_META_KEY', 'npr_image_agency');
define( 'NPR_IMAGE_CAPTION_META_KEY', 'npr_image_caption');

define( 'NPR_PUSH_STORY_ERROR', 'npr_push_story_error');

define( 'NPR_MAX_QUERIES', 10 );

define( 'DS_NPR_PLUGIN_DIR', plugin_dir_path(__FILE__) );

define( 'NPR_POST_TYPE', 'npr_story_post' );
require_once( DS_NPR_PLUGIN_DIR . '/settings.php' );
require_once( DS_NPR_PLUGIN_DIR . '/classes/NPRAPIWordpress.php');

require_once( DS_NPR_PLUGIN_DIR . '/get_stories.php');
require_once( DS_NPR_PLUGIN_DIR . '/meta-boxes.php');

//add the cron to get stories
register_activation_hook( DS_NPR_PLUGIN_DIR . '/ds-npr-api.php', 'nprstory_activation' );
add_action( 'npr_ds_hourly_cron', array ( 'DS_NPR_API','nprstory_cron_pull' ) );
register_deactivation_hook( DS_NPR_PLUGIN_DIR . '/ds-npr-api.php', 'nprstory_deactivation' );


function nprstory_activation() {
	global $wpdb;
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	  // check if it is a network activation - if so, run the activation function for each blog id
        $old_blog = $wpdb->blogid;
		// Get all blog ids
		$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
		foreach ( $blogids as $blog_id ) {
            switch_to_blog( $blog_id );
            nprstory_activate();
		}
		switch_to_blog( $old_blog );
	} else {
        nprstory_activate();
	}
}

function nprstory_activate() {
	update_option( 'dp_npr_query_multi_cron_interval', 60 );
	if ( ! wp_next_scheduled( 'npr_ds_hourly_cron' ) ) {
		error_log( 'turning on cron event' );
		wp_schedule_event( time(), 'hourly', 'npr_ds_hourly_cron' );
	}

	$num =  get_option( 'ds_npr_num' );
	if ( empty($num) ) {
		update_option( 'ds_npr_num', 5 );
	}
	
	$def_url = 'http://api.npr.org';
	$pull_url = get_option( 'ds_npr_api_pull_url' );
	if ( empty( $pull_url ) ) {
		update_option( 'ds_npr_api_pull_url', $def_url );
	}
	
}

function nprstory_deactivation() {
	global $wpdb;
	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	  // check if it is a network activation - if so, run the activation function for each blog id
		$old_blog = $wpdb->blogid;
		// Get all blog ids
		$blogids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs" ) );
		foreach ( $blogids as $blog_id ) {
			switch_to_blog( $blog_id );
			nprstory_deactivate();
		}
		switch_to_blog( $old_blog );
	} else {
		nprstory_deactivate();
	}
}

function nprstory_deactivate() {
	wp_clear_scheduled_hook( 'npr_ds_hourly_cron' );
	$num =  get_option( 'ds_npr_num' );
	if ( ! empty($num) ) {
		delete_option( 'ds_npr_num' );
	}
	
	$push_url = get_option( 'ds_npr_api_push_url' );
	if ( ! empty( $push_url ) ) {
		delete_option( 'ds_npr_api_push_url' );
	}
}


function nprstory_show_message( $message, $errormsg = false ) {
	if ( $errormsg ) {
		echo '<div id="message" class="error">';
	} else {
		echo '<div id="message" class="updated fade">';
	}
	echo "<p><strong>$message</strong></p></div>";
}

require_once( 'push_story.php' );


add_action( 'init', 'nprstory_create_post_type' );

function nprstory_create_post_type() {
	register_post_type( NPR_POST_TYPE,
		array(
			'labels' => array(
				'name' => __( 'NPR Stories' ),
				'singular_name' => __( 'NPR Story' ),
        ),
		'public' => true,
		'has_archive' => true,
		'menu_position' => 5,
		'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		)
	);
}

function nprstory_add_meta_boxes() {
	$screen = get_current_screen();
	$push_url = get_option( 'ds_npr_api_push_url' );
	if ( $screen->id == 'post' && ! empty( $push_url ) ) {
		global $post;
		add_meta_box(
			'ds_npr_document_meta',
			'NPR Story API',
			'ds_npr_publish_meta_box',
			'post', 'side'
		);
	}
}
add_action('add_meta_boxes', 'nprstory_add_meta_boxes');
