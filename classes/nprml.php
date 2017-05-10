<?php

date_default_timezone_set('America/New_York');


/**
 * nprstory_to_nprml(): Translates a post to NPRML.  Returns an XML string.
 */
function nprstory_to_nprml( $post ) {
    $story = nprstory_post_to_nprml_story( $post );
    $doc = array();
    $doc[] = array(
        'tag' => 'list',
        'children' => array( array( 'tag' => 'story', 'children' => $story ), ),
    );
    $ret_xml = nprstory_nprml_array_to_xml( 'nprml', array( 'version' => '0.93' ), $doc );
    return $ret_xml;
}

/**
 * 
 * Do the mapping from WP post to the array that we're going to build the NPRML from.  
 * This is also where we will do custom mapping if need be.
 * If a mapped custom field does not exist in a certain post, just send the default field.
 * @param  $post
 */
function nprstory_post_to_nprml_story( $post ) {
    $topics = array();

    $topics['Afghanistan'] = 1149;
    $topics['Africa'] = 1126;
    $topics['Analysis'] = 1059;
    $topics['Animals'] = 1132;
    $topics['Architecture'] = 1142;
    $topics['Around the Nation '] = 1091;
    $topics['Art &amp; Life'] = 1008;
    $topics['Art &amp; Design'] = 1047;
    $topics['Asia'] = 1125;
    $topics['Author Interviews'] = 1033;
    $topics['Book News &amp; Features'] = 1161;
    $topics['Book Reviews'] = 1034;
    $topics['Books'] = 1032;
    $topics['Brain Candy'] = 1130;
    $topics['Business'] = 1006;
    $topics['Business Story of the Day'] = 1095;
    $topics['Children\'s Health'] = 1030;
    $topics['Columns'] = 1058;
    $topics['Commentary'] = 1060;
    $topics['Concerts'] = 1190;
    $topics['Dance'] = 1145;
    $topics['Digital Life'] = 1049;
    $topics['Discover Songs'] = 1108;
    $topics['Diversions'] = 1051;
    $topics['Economy'] = 1017;
    $topics['Education'] = 1013;
    $topics['Elections'] = 139482413;
    $topics['Energy'] = 1131;
    $topics['Environment'] = 1025;
    $topics['Europe'] = 1124;
    $topics['Fine Art'] = 1141;
    $topics['Fitness &amp; Nutrition'] = 1134;
    $topics['Food'] = 1053;
    $topics['From Our Listeners'] = 1061;
    $topics['Games &amp; Humor'] = 1052;
    $topics['Gardening'] = 1054;
    $topics['Global Health'] = 1031;
    $topics['Governing'] = 1123;
    $topics['Health'] = 1128;
    $topics['Health Care'] = 1027;
    $topics['History'] = 1136;
    $topics['Holiday Story of the Day'] = 1096;
    $topics['Home Page Top Stories'] = 1002;
    $topics['House &amp; Senate Races'] = 139545299;
    $topics['Humans'] = 1129;
    $topics['Impact'] = 1162;
    $topics['In Performance'] = 1040;
    $topics['Interviews'] = 1022;
    $topics['Iraq'] = 1010;
    $topics['Israeli-Palestinian Coverage'] = 1101;
    $topics['Katrina &amp; Beyond'] = 1093;
    $topics['Latin America'] = 1127;
    $topics['Law'] = 1070;
    $topics['Lost &amp; Found Sound'] = 1074;
    $topics['Low-Wage America'] = 1076;
    $topics['Media'] = 1020;
    $topics['Medical Treatments'] = 1135;
    $topics['Mental Health'] = 1029;
    $topics['Middle East'] = 1009;
    $topics['Movie Interviews'] = 1137;
    $topics['Movie Reviews'] = 4467349;
    $topics['Movies'] = 1045;
    $topics['Multimedia'] = 1147;
    $topics['Music'] = 1039;
    $topics['Music Interviews'] = 1105;
    $topics['Music Lists'] = 1107;
    $topics['Music News'] = 1106;
    $topics['Music Quizzes'] = 1151;
    $topics['Music Reviews'] = 1104;
    $topics['Music Videos'] = 1110;
    $topics['National Security'] = 1122;
    $topics['News'] = 1001;
    $topics['On Aging'] = 1028;
    $topics['On Disabilities'] = 1133;
    $topics['Opinion'] = 1057;
    $topics['Performing Arts'] = 1046;
    $topics['Photography'] = 1143;
    $topics['Political Data &amp; Technology'] = 455776378;
    $topics['Political Demographics'] = 455778960;
    $topics['Politics'] = 1014;
    $topics['Politics &amp; Pop Culture'] = 455779148;
    $topics['Politics: Fact Check'] = 455779263;
    $topics['Politics: Issues'] = 455779709;
    $topics['Politics: Member Stations'] = 455779583;
    $topics['Pop Culture'] = 1048;
    $topics['Presidential Race'] = 139544303;
    $topics['Race'] = 1015;
    $topics['Recipes'] = 1139;
    $topics['Religion'] = 1016;
    $topics['Remembrances'] = 1062;
    $topics['Research News'] = 1024;
    $topics['Science'] = 1007;
    $topics['Social Security Debate'] = 1083;
    $topics['Space'] = 1026;
    $topics['Sports'] = 1055;
    $topics['Statewide Races'] = 139545485;
    $topics['Story of the Day'] = 1090;
    $topics['Strange News'] = 1146;
    $topics['Summer'] = 1088;
    $topics['Technology'] = 1019;
    $topics['Television'] = 1138;
    $topics['The Impact of War'] = 1078;
    $topics['Theater'] = 1144;
    $topics['U.S.'] = 1003;
    $topics['World'] = 1004;
    $topics['World Story of the Day'] = 1056;
    $topics['Your Health'] = 1066;
    $topics['Your Money'] = 1018;

    $story = array();
    $story[] = array( 
        'tag' => 'link',
        'attr' => array( 'type' => 'html' ),
        'text' => get_permalink( $post ),
    );
    $use_custom = get_option( 'dp_npr_push_use_custom_map' );

    $posttags = get_the_tags();
    if ($posttags) {
      foreach($posttags as $tag) {
        if ( array_key_exists( $tag->name, $topics ) ) {
            $story[] = array(
                'tag' => 'parent',
                'attr' => array( 'type' => 'topic', 'id' => $topics[$tag->name] ),   
            );         
        }
      }
    }
    
    $starting_time = strtotime(get_post_meta( $post->ID, 'starting_time', true ));

    /**
     * Setup program related tags 
     *    <show>
     *      <program id="65" code="1A" name="1A" label="1A">1A</program>
     *      <showDate>Mon, 20 Feb 2017 05:01:02 +0000</showDate>
     *      <segNum>2</segNum>
     *    </show>
     *
     * <parent type="program" id="65">
     *   <title>1A</title>
     *   <link>http://the1a.org</link>
     * </parent>
     *
     */

    $program_id = get_option('ds_npr_api_program_id');

    
    if ( ! empty($program_id) ){
        $name = get_bloginfo();

        $the_post_id = $post->ID;
        
        // Fetch all segments for show date and get order of each segment
        $today = date("Ymd", $starting_time);
        $args = array(
            'post_type' => 'segment',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'numberposts' => 10,
            'meta_query' => array( array(
                            'key' => 'starting_time',
                            'value' => $today,
                            'compare' => '=',
                            'type' => 'DATE',             
                ))
        );        
        $segments = get_posts($args);
        usort($segments, "wamu_segment_sort_asc");
        
        $show_date = strtotime(get_post_meta( $segments[0]->ID, 'starting_time', true ));

        $segment_number = array_filter(
            $segments,
            function ($e) use ($the_post_id) {
                return $e->ID == $the_post_id;
            }
        );

        $story[] = array(
            'tag' => 'parent',
            'attr' => array( 'type' => 'program', 'id' => $program_id),
            'children' => array(
                array(
                    'tag' => 'title',
                    'text' => $name
                ),
                array(
                    'tag' => 'link',
                    'text' => get_option('siteurl')
                )
            )
        );

        $story[] = array(
            'tag' => 'show',
            'children' => array(
                array(
                    'tag' => 'program',
                    'attr' => array('id' => $program_id, 'code' => $name,  'name' => $name, 'label' => $name),
                    'text' => $name
                ),
                array(
                    'tag' => 'showDate',
                    'text' => gmdate('D, d M Y H:i:s O', $show_date)
                ),
                array(
                    'tag' => 'segNum',
                    'text' => array_keys($segment_number)[0] + 1
                )
            )
        );
    }



    //get the list of metas available for this post
    $post_metas = get_post_custom_keys( $post->ID );
    
    $teaser_text = '';
    if ( ! empty( $post->post_excerpt ) ){
        $teaser_text = $post->post_excerpt;
    }
    
/*
    $custom_content_meta = get_option( 'ds_npr_api_mapping_body' );
    if ( $use_custom && ! empty( $custom_content_meta ) && $custom_content_meta != '#NONE#' && in_array( $custom_content_meta,$post_metas ) ){
        $content = get_post_meta( $post->ID, $custom_content_meta, true);
        $post_for_teaser = $post;
        $post_for_teaser->post_content = $content;
        if ( empty( $teaser_text ) ){
            $teaser_text = nprstory_nai_get_excerpt( $post_for_teaser );
        }
    } else {
*/
        $content = '';

        if ( function_exists('wamu_get_guest_host') ) {

            $guest_host = wamu_get_guest_host($post->ID);
            
            if ( count($guest_host) > 0 ) {
                $content .= '<p><em>With guest host ' . $guest_host[0]['name'] .'</em>.</p>';
            }           

        }
        
        if( function_exists('wamu_markdown_transform')) {
            $content .= wamu_markdown_transform($post->post_content);
        }

        if ( function_exists('wamu_get_guests') ) {
            
            $guests = wamu_get_guests($post->ID);
            
            if ( count($guests) > 0 ) {
                $content .= '<p><h5>Guests</h5></p>';
            }

            foreach ( $guests as $guest ) {
                $content .= '<p><strong>' . $guest['name'] . '</strong>, ' . $guest['credentials'] . '</p>';
                
            }
        }

        // Add attribution
        $content .= '<p>&copy; ' . substr(get_post_meta($post->ID, 'starting_time', true), 0, 4 ) . ' WAMU 88.5 - American University Radio. ';
        $content .= '<p>For more, see <a href="' . get_permalink( $post ) . '">' . get_permalink( $post ) . '</a>';
        $content .= '</p>';


        if ( empty( $teaser_text ) ) {
            $teaser_text = nprstory_nai_get_excerpt( $post );
        }
    //}
    //lets see if there are any plugins that need to fix their shortcodes before we run do_shortcode
    if ( has_filter( 'npr_ds_shortcode_filter' ) ) {
        $content = apply_filters( 'npr_ds_shortcode_filter', $content );
    }
      //let any plugin that has short codes try and replace those with HTML
    $content = do_shortcode( $content );
    //for any remaining short codes, nuke 'em
    $content = strip_shortcodes( $content );
    $content = apply_filters( 'the_content', $content );

    $story[] = array(
        'tag' => 'teaser',
        'text' => $teaser_text,
    );
    $custom_title_meta = get_option( 'ds_npr_api_mapping_title' );
    if ( $use_custom && !empty( $custom_title_meta ) && $custom_title_meta != '#NONE#' && in_array( $custom_content_meta,$post_metas ) ){
        $custom_title = get_post_meta( $post->ID, $custom_title_meta, true );
        $story[] = array(
            'tag' => 'title',
            'text' => $custom_title,
        );
    } else {
        $story[] = array(
            'tag' => 'title',
            'text' => $post->post_title,
        );
    }

    // NPR One
    // If the box is checked, the value here is '1'
    if ( ! empty( $_POST['send_to_nprone'] ) ) {
        $story[] = array(
            'tag' => 'parent',
            'attr' => array( 'id' => '319418027', 'type' => 'collection' ),
        );
    }

    #'miniTeaser' => array( 'text' => '' ),
    #'slug' => array( 'text' => '' ),

    $story[] = array(
        'tag' => 'storyDate',
        'text' => gmdate('D, d M Y H:i:s O', $starting_time),
    );
    $story[] = array(
        'tag' => 'pubDate',
        'text' => mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt ),
    );
    $story[] = array(
        'tag' => 'lastModifiedDate',
        'text' => mysql2date( 'D, d M Y H:i:s +0000', $post->post_modified_gmt ), 
    );
    $story[] = array(
        'tag' => 'partnerId',
        'text' => $post->guid,
    );
    //TODO:  When the API accepts sending both text and textWithHTML, send a totally bare text.  Don't do do_shortcode(). 
    //for now (using the npr story api) we can either send text or textWithHTML, not both.
    //it would be nice to send text after we strip all html and shortcodes, but we need the html
    //and sending both will duplicate the data in the API
    $story[] = array(
        'tag' => 'textWithHtml',
        'children' => nprstory_nprml_split_paragraphs( $content ),
    );

    $perms_group = get_option( 'ds_npr_story_default_permission' );
    if (!empty( $perms_group ) ) {
        $story[] = array(
            'tag' => 'permissions',
            'children' => array (
                array( 
                    'tag' => 'permGroup',
                    'attr' => array( 'id' => $perms_group ),
                )
            ),
        );
    }
    
    $custom_media_credit = get_option( 'ds_npr_api_mapping_media_credit' );
    $custom_media_agency = get_option( 'ds_npr_api_mapping_media_agency' );

    /* remove this for now until we decide if we're going to actually do this...km
    $dist_media_option = get_option('ds_npr_api_mapping_distribute_media');
    $dist_media_polarity = get_option('ds_npr_api_mapping_distribute_media_polarity');
    */
    $args = array(
        'order'=> 'DESC',
        'post_mime_type' => 'image',
        'post_parent' => $post->ID,
        'post_status' => null,
        'post_type' => 'attachment'
    );

    //$images = get_children( $args );
    $images = array(get_post(get_post_thumbnail_id( $post->ID, 'full' )));
            
    foreach ( $images as $image ) {
        $custom_credit = '';
        $custom_agency = '';
        //$image_metas = get_post_custom_keys( $image->ID );
        $custom_credit = get_post_meta($image->ID, 'credit', true);
        $image_type = 'primary';

        $story[] = array( 
            'tag' => 'image',
            'attr' => array( 'src' => wamu_cdn_dirty_rewrite($image->guid) . $in_body, 'type' => $image_type ), 
            'children' => array(
                array(
                    'tag' => 'title',
                    'text' => $image->post_title,
                ),
                array(
                    'tag' => 'caption',
                    'text' => $image->post_excerpt,
                ),
                array(
                    'tag' => 'producer',  
                    'text' => $custom_credit
                ),
                array(
                    'tag' => 'provider',  
                    'text' => $custom_agency
                )
            ),
        );
    }

    $audio_file = get_post_meta( $post->ID, 'audio_file', true );
    $audio_duration = get_post_meta( $post->ID, '_audio_file_duration', true );

    if (! empty( $audio_file )) {

        $audio = wamuSite::globals()->audio_archive_mp3_path_ssl . $audio_file . '.mp3';

        $story[] = array(
            'tag' => 'audio',
            'children' => array(
                array(
                    'tag' => 'format',
                    'children' => array (
                        array(
                        'tag' => 'mp3',
                            'text' => $audio,
                        )
                    ),
                ),
                array(
                    'tag' => 'duration',
                    'text' => strlen($audio_duration) > 0 ? $audio_duration : '2880',
                ),
            ),
        );   

    }

    return $story;
}

// Convert "HH:MM:SS" duration (not time) into seconds
function nprstory_convert_duration_to_seconds( $duration ) {
  $pieces = explode( ':', $duration );
  $duration_in_seconds = ( $pieces[0] * 60 * 60 + $pieces[1] * 60 + $pieces[2] );
  return $duration_in_seconds;
}

function nprstory_nprml_split_paragraphs( $html ) {
    $parts = array_filter( 
        array_map( 'trim', preg_split( "/<\/?p>/", $html ) ) 
    );
    $graphs = array();
    $num = 1;
    foreach ( $parts as $part ) {
        $graphs[] = array( 
            'tag' => 'paragraph',
            'attr' => array( 'num' => $num ),
            'cdata' => $part,
        );
        $num++;
    }
    return $graphs;
}


/**
 * convert a PHP array to XML
 */
function nprstory_nprml_array_to_xml( $tag, $attrs, $data ) {
    $xml = new DOMDocument();
    $xml->formatOutput = true;
    $root = $xml->createElement( $tag );
    foreach ( $attrs as $k => $v ) {
        $root->setAttribute( $k, $v );
    }
    foreach ( $data as $item ) {
        $elemxml = nprstory_nprml_item_to_xml( $item, $xml );
        $root->appendChild( $elemxml );
    }
    $xml->appendChild( $root );
    return $xml->saveXML();
}

/**
 * convert a loosely-defined item to XML
 *
 * @todo figure out way for this to safely fail
 *
 * @param Array $item Must have a key 'tag'
 * @param DOMDocument $xml
 */
function nprstory_nprml_item_to_xml( $item, $xml ) {
    if ( ! array_key_exists( 'tag', $item ) ) {
        error_log( "Unable to convert NPRML item to XML: no tag for: " . print_r( $item, true ) ); // debug use
        // this should actually be a serious error
    }
    $elem = $xml->createElement( $item[ 'tag' ] );
    if ( array_key_exists( 'children', $item ) ) {
        foreach ( $item[ 'children' ] as $child ) {
            $childxml = nprstory_nprml_item_to_xml( $child, $xml );
            $elem->appendChild( $childxml );
        }
    }
    if ( array_key_exists( 'text', $item ) ) { 
        $elem->appendChild(
            $xml->createTextNode( $item[ 'text' ] )
        );
    }
    if ( array_key_exists( 'cdata', $item ) ) { 
        $elem->appendChild(
            $xml->createCDATASection( $item[ 'cdata' ] )
        );
    }
    if ( array_key_exists( 'attr', $item ) ) { 
        foreach ( $item[ 'attr' ] as $attr => $val ) {
            $elem->setAttribute( $attr, $val );
        }
    }
    return $elem;
}


/**
 * Retrieves the excerpt of any post.
 *
 * HACK: This is ripped from wp_trim_excerpt() in
 * wp-includes/formatting.php because there's seemingly no way to
 * use it outside of The Loop
 * Filed as ticket #16372 in WP Trac
 *
 * @todo replace this with wp_trim_words, see https://github.com/nprds/nprapi-wordpress/issues/20
 *
 * @param   object  $post       Post object
 * @param   int     $word_count Number of words (default 30)
 * @return  String
 */
function nprstory_nai_get_excerpt( $post, $word_count = 30 ) {
    $text = $post->post_content;

    $text = strip_shortcodes( $text );

    $text = apply_filters( 'the_content', $text );
    $text = str_replace( ']]>', ']]&gt;', $text );
    $text = strip_tags( $text );
    $excerpt_length = apply_filters( 'excerpt_length', $word_count );
    //$excerpt_more = apply_filters( 'excerpt_more', ' ' . '[...]' );
    $words = preg_split( "/[\n\r\t ]+/", $text, $excerpt_length + 1, 
                         PREG_SPLIT_NO_EMPTY );
    if ( count( $words ) > $excerpt_length ) {
        array_pop( $words );
        $text = implode( ' ', $words );
        //$text = $text . $excerpt_more;
    } else {
        $text = implode( ' ', $words );
    }
    return $text;
}
