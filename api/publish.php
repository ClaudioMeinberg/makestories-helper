<?php
add_action("wp_ajax_ms_publish_post", "ms_publish_post");

/**
 * Action for publishing the post. Takes the story ID and gets the HTML for that
 */

function ms_publish_post(){
    ms_protect_ajax_route();
    header("Content-Type: application/json");
    if((isset($_REQUEST["slug"]) || isset($_REQUEST["post_id"])) && isset($_REQUEST["story"])){
        $storyId = $_REQUEST["story"];
        $r = ms_get_story_HTML($_GET['story']);
        $parsed = json_decode($r, true);
        $html = $parsed['html'];
        $title = $parsed['publisherDetails']['title'];

        if(isset($_REQUEST['post_id'])) {
            $post = get_post((int)$_REQUEST['post_id']);
            if ($post && $post->post_status != 'trash') {
                $post = $post->ID;
                $toCreate = false;
            } else {
                die(json_encode(["success" => false, "error" => "Post already deleted!"]));
            }
        }else{
            $slug = $_REQUEST["slug"];
            $post = wp_insert_post([
                "post_content" => $html,
                "post_name" => $slug,
                "post_title" => $title,
                "post_status" => "publish",
                "post_type" => MS_POST_TYPE,
            ]);
        }

        include_once( ABSPATH . 'wp-admin/includes/image.php' );
        if( ! ( function_exists( 'wp_get_attachment_by_post_name' ) ) ) {
            function wp_get_attachment_by_post_name( $post_name ) {
                $id = post_exists($post_name);
                $args           = array(
                    'posts_per_page' => 1,
                    'post_type'      => 'attachment',
                    'p'           => $id,
                );

                $get_attachment = new WP_Query( $args );

                if ( ! $get_attachment || ! isset( $get_attachment->posts, $get_attachment->posts[0] ) ) {
                    return false;
                }

                return $get_attachment->posts[0];
            }
        }
        $mediaLinksToDownload = [];
        // Uploading media to media library of wordpress
        $forceUploadMedia = ms_get_options()['forceUploadMedia'];
        foreach ($parsed['media'] as $media) {
            $imageurl = $media['url'];
            $name = $imageurl;
            $nameExploded = explode("?",$imageurl);
            if(count($nameExploded)){
                $nameExploded = $nameExploded[0];
            }
            $nameExploded = explode("/",$nameExploded);
            if(count($nameExploded)){
                $name = $nameExploded[count($nameExploded) - 1];
            }
            $filename = date('dmY').''.(int) microtime(true).basename($name);
            $atta_title = basename( $media['url'] );

            $attach_id = false;
            if( post_exists($atta_title) && !$forceUploadMedia ) {
                if(wp_validate_boolean(apply_filters("ms_check_for_duplicate_media", true))){
                    $getAttachment = wp_get_attachment_by_post_name( $atta_title );
                }

                if($getAttachment) {
                    $attach_id = $getAttachment->ID;
                }else{
                    $mediaLinksToDownload[] = [
                        "imageurl" => $imageurl,
                        "filename" => $filename,
                        "atta_title" => $atta_title,
                        "into_else" => true,
                        "exists" => post_exists($atta_title),
                    ];
                }

            } else {
                $mediaLinksToDownload[] = [
                    "imageurl" => $imageurl,
                    "filename" => $filename,
                    "atta_title" => $atta_title,
                ];
            }
            if($attach_id){
                // Get permalink of media library
                $permalink = wp_get_attachment_url($attach_id);

                // Replace permalink of image with html
                $html = str_ireplace($media['url'], $permalink, $html);
            }
        }

        if(ms_is_categories_enabled() && !isset($_REQUEST['is_republish'])){
            $category = ms_get_default_category();
            if(isset($_REQUEST['category']) && !empty($_REQUEST['category'])){
                $category = $_REQUEST['category'];
            }
            wp_set_post_terms($post, $category, MS_TAXONOMY);
        }
        $link = get_post_permalink($post);
        $slug = $_REQUEST["slug"];
        wp_update_post([
            "post_content" => str_ireplace(MS_WORDPRESS_CANONICAL_SUBSTITUTION_PLACEHOLDER, $link, $html),
            "ID" => $post,
            "post_name" => $slug,
            "post_title" => $title,
        ]);
        update_post_meta( $post, "story_id", $storyId);
        update_post_meta( $post, "publisher_details", json_encode($parsed['publisherDetails']));
        print_r(
            json_encode(
                getMSPostDataToSend(
                    get_post($post),
                    [
                        "media" => $mediaLinksToDownload
                    ]
                )
            )
        );
        wp_die();
    }
    wp_die(json_encode(["success" => false, "error" => "Invalid details provided!"]));
}

add_action("wp_ajax_ms_upload_image_to_media_library", "ms_upload_image_to_media_library");
add_action("wp_ajax_nopriv_ms_upload_image_to_media_library", "ms_upload_image_to_media_library");

function ms_upload_image_to_media_library(){
    ms_protect_ajax_route();
    header("Content-Type: application/json");
    $replaced = false;
    if(
        isset($_REQUEST['imageurl']) &&
        isset($_REQUEST['post_id'])
    ){
        $imageurl = $_REQUEST['imageurl'];
        $atta_title = basename( $imageurl );

        if(isset($_REQUEST['atta_title'])){
            $atta_title = $_REQUEST['atta_title'];
        }

        if(isset($_REQUEST['filename'])){
            $filename = $_REQUEST['filename'];
        }else{
            $name = $imageurl;
            $nameExploded = explode("?",$imageurl);
            if(count($nameExploded)){
                $nameExploded = $nameExploded[0];
            }
            $nameExploded = explode("/",$nameExploded);
            if(count($nameExploded)){
                $name = $nameExploded[count($nameExploded) - 1];
            }
            $filename = date('dmY').''.(int) microtime(true).basename($name);
        }

        $postId = $_REQUEST['post_id'];
        include_once( ABSPATH . 'wp-admin/includes/image.php' );
        if( ! ( function_exists( 'wp_get_attachment_by_post_name' ) ) ) {
            function wp_get_attachment_by_post_name( $post_name ) {
                $id = post_exists($post_name);
                $args           = array(
                    'posts_per_page' => 1,
                    'post_type'      => 'attachment',
                    'p'           => $id,
                );

                $get_attachment = new WP_Query( $args );

                if ( ! $get_attachment || ! isset( $get_attachment->posts, $get_attachment->posts[0] ) ) {
                    return false;
                }

                return $get_attachment->posts[0];
            }
        }

        $attach_id = false;
        $forceUploadMedia = ms_get_options()['forceUploadMedia'];
        if(wp_validate_boolean(apply_filters("ms_check_for_duplicate_media", true))){
            $getAttachment = wp_get_attachment_by_post_name( $atta_title );
        }
        if(isset($_REQUEST['is_republish']) && wp_validate_boolean($_REQUEST['is_republish'])){
            $forceUploadMedia = true;
        }
        if( post_exists($atta_title) && !$forceUploadMedia && $getAttachment) {
            $attach_id = $getAttachment->ID;
        }else{

            $wp_filetype = wp_check_filetype(basename($filename), null );
            if(empty($wp_filetype['type'])){
                $wp_filetype = ms_find_filetype($imageurl);
            }

            if(isset($wp_filetype['ext']) && !empty($wp_filetype['ext']) && !msEndsWith($filename, $wp_filetype['ext'])){
                $filename = $filename.".".$wp_filetype['ext'];
            }

            $uploaddir = wp_upload_dir();
            $uploadfile = $uploaddir['path'] . '/' . $filename;
            $contents= file_get_contents($imageurl);
            $savefile = fopen($uploadfile, 'w');
            fwrite($savefile, $contents);
            fclose($savefile);

            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => $atta_title,
                'post_content' => '',
                'post_status' => 'inherit',
            );
            $attach_id = wp_insert_attachment( $attachment, $uploadfile );
            if($attach_id){
                $attach_data = wp_generate_attachment_metadata($attach_id, $uploadfile);
                wp_update_attachment_metadata($attach_id, $attach_data);
            }
        }

        if($attach_id){
            // Get permalink of media library
            $permalink = wp_get_attachment_url($attach_id);
            $post = get_post($postId);
            $html = $post->post_content;
            // Replace permalink of image with html
            $html = str_ireplace($imageurl, $permalink, $html);
            $replaced = true;
            $updated = wp_update_post([
                "post_content" => $html,
                "ID" => $postId,
            ]);

            //CHeck in post meta data
            $meta = get_post_meta($postId, "publisher_details", true);
            try{
                $meta = json_decode($meta, true);
                $propertiesToAdd = [
                    "publisher-logo-src",
                    "poster-portrait-src",
                    "poster-landscape-src",
                    "poster-square-src",
                ];
                foreach ($propertiesToAdd as $prop){
                    if(isset($meta[$prop]) && $meta[$prop] == $imageurl){
                        $meta[$prop] = $permalink;
                    }
                }
                update_post_meta( $postId, "publisher_details", json_encode($meta));
            }catch(Exception $e){
                //Do Nothing
            }

        }
    }
    echo json_encode([
        "replaced" => $replaced,
    ]);
    wp_die();

}

add_action("wp_ajax_ms_get_published_posts", "ms_get_published_posts");


function ms_get_published_posts(){
    ms_protect_ajax_route();
    header("Content-Type: application/json");
    $args = [
        "post_type" => MS_POST_TYPE,
        "numberposts" => -1,
    ];
    if(!current_user_can("manage_options")){
        $args["author"] = get_current_user_id();
    }
    $posts = get_posts($args);
    $toSend = [
        "posts" => [],
    ];
    foreach ($posts as $post){
        $storyId = get_post_meta($post->ID, "story_id", true);
        $category = [];
        $terms = wp_get_post_terms($post->ID, MS_TAXONOMY);
        foreach ($terms as $term){
            $category[] = $term->name;
        }
        $title = $post->post_name;
        $meta = get_post_meta($post->ID, "publisher_details", true);
        $poster = "";
        if($meta && is_string($meta) && strlen($meta)){
            try{
                $parsed = json_decode($meta, true);
                if($parsed && is_array($parsed)){
                    if(isset($parsed['title'])){
                        $title = $parsed['title'];
                    }
                    if(isset($parsed['poster-portrait-src'])){
                        $poster = $parsed['poster-portrait-src'];
                    }
                }
            }catch (Exception $e){
                //Do nothing - Just for safety
            }
        }
        $toSend['posts'][$storyId] = [
            "link" => get_post_permalink($post->ID),
            "title" => $title,
            "poster" => $poster,
            "updatedAt" => strtotime($post->post_modified) * 1000,
            "post_id" => $post->ID,
            "category" => $category,
        ];
    }
    die(json_encode($toSend));
}


add_action("wp_ajax_ms_get_published_posts_all", "ms_get_published_posts_all");


function ms_get_published_posts_all(){
    ms_protect_ajax_route();
    header("Content-Type: application/json");
    $args = [
        "post_type" => MS_POST_TYPE,
        "numberposts" => -1,
    ];
    $posts = get_posts($args);
    $toSend = [
        "posts" => [],
    ];
    foreach ($posts as $post){
        $storyId = get_post_meta($post->ID, "story_id", true);
        $category = [];
        $terms = wp_get_post_terms($post->ID, MS_TAXONOMY);
        foreach ($terms as $term){
            $category[] = $term->name;
        }
        $title = $post->post_name;
        $meta = get_post_meta($post->ID, "publisher_details", true);
        $poster = "";
        if($meta && is_string($meta) && strlen($meta)){
            try{
                $parsed = json_decode($meta, true);
                if($parsed && is_array($parsed)){
                    if(isset($parsed['title'])){
                        $title = $parsed['title'];
                    }
                    if(isset($parsed['poster-portrait-src'])){
                        $poster = $parsed['poster-portrait-src'];
                    }
                }
            }catch (Exception $e){
                //Do nothing - Just for safety
            }
        }
        $toSend['posts'][$post->ID] = [
            "link" => get_post_permalink($post->ID),
            "title" => $title,
            "poster" => $poster,
            "updatedAt" => strtotime($post->post_modified) * 1000,
            "post_id" => $post->ID,
            "story_id" => $storyId,
            "category" => $category,
        ];
    }
    die(json_encode($toSend));
}

add_action("wp_ajax_ms_get_published_post", "ms_get_published_post"); 

function ms_get_published_post(){
    ms_protect_ajax_route();
    $toSend = [
        "isPublished" => false,
    ];
    if(isset($_REQUEST['story'])){
        $storyId = $_REQUEST['story'];
        $args = [
            "post_type" => MS_POST_TYPE,
            "numberposts" => 1,
            "meta_query" => [
                [
                    "key" => "story_id",
                    "value" => $storyId,
                    "compare" => "="
                ]
            ]
        ];
        $posts = get_posts($args);
        if(count($posts)){
            $toSend = getMSPostDataToSend($posts[0]);
            $toSend["isPublished"] = true;
        }
    }
    header("Content-Type: application/json");
    print_r(json_encode($toSend));
    die();
}

add_action("wp_ajax_ms_delete_post", "ms_delete_post");

function ms_delete_post(){
    ms_protect_ajax_route();
    $toSend = [
        "deleted" => false,
    ];
    if(isset($_REQUEST['story']) && isset($_REQUEST['post_id'])){
        $storyId = $_REQUEST['story'];
        $postId = $_REQUEST['post_id'];
        $args = [
            "post_type" => MS_POST_TYPE,
            "numberposts" => 1,
            "meta_query" => [
                [
                    "key" => "story_id",
                    "value" => $storyId,
                    "compare" => "="
                ]
            ]
        ];
        //Verify if post is already there before sending delete command
        $posts = get_posts($args);
        if(count($posts) && $postId == $posts[0]->ID){
            //Verified, now deleting
            $toSend["deleted"] = (bool) wp_trash_post($postId);
        }
    }
    header("Content-Type: application/json");
    print_r(json_encode($toSend));
    die();
}

add_action("wp_ajax_ms_change_story_slug", "ms_change_story_slug");

function ms_verify_media_in_story(){
//    ms_protect_ajax_route();
    $media = [];
    if(isset($_REQUEST['post_id'])){
        $postId = $_REQUEST['post_id'];
        $post = get_post($postId);
        $content = $post->post_content;
        $doc = new DOMDocument();
        $doc->loadHTML($content);
        $mediaInStory = [];

        //Gather all Image element sources
        $images = $doc->getElementsByTagName("amp-img");
        foreach ($images as $image){
            $mediaInStory[] = $image->getAttribute("src");
        }

        //Gather all Video element sources
        $videos = $doc->getElementsByTagName("amp-video");
        foreach ($videos as $video){
            $mediaInStory[] = $video->getAttribute("poster");
            $sources = $video->getElementsByTagName("source");
            foreach ($sources as $source){
                $mediaInStory[] = $source->getAttribute("src");
            }
        }

        //Gather all link elements
        $links = $doc->getElementsByTagName("link");
        foreach ($links as $link){
            $rel = $link->getAttribute("rel");
            if(strpos($rel, "icon") !== false){
                $mediaInStory[] = $link->getAttribute("href");
            }
        }

        //Gather all meta elements
        $metas = $doc->getElementsByTagName("meta");
        foreach ($metas as $meta){
            $name = $meta->getAttribute("property");
            if(empty($name)){
                $name = $meta->getAttribute("name");
            }
            if($name && strpos($name, ":image")){
                $mediaInStory[] = $meta->getAttribute("content");
            }
        }

        //Add images from JsonLd
        $scripts = $doc->getElementsByTagName("script");
        if(count($scripts)){
            foreach ($scripts as $script){
                $type = $script->getAttribute("type");
                if($type === "application/ld+json"){
                    try{
                        $json = json_decode($script->nodeValue, true);
                        if($json && is_array($json)){
                            if(isset($json['image']) && is_array($json['image'])){
                                foreach ($json['image'] as $url){
                                    $mediaInStory[] = $url;
                                }
                            }
                            if(
                                isset($json['publisher']) &&
                                is_array($json['publisher']) &&
                                isset($json['publisher']['logo']) &&
                                is_array($json['publisher']['logo']) &&
                                isset($json['publisher']['logo']["@type"]) &&
                                isset($json['publisher']['logo']["url"]) &&
                                $json['publisher']['logo']["@type"] === "ImageObject"
                            ){
                                $mediaInStory[] = $json['publisher']['logo']["url"];
                            }
                        }
                    }catch(Exception $e){
                        //Do Nothing
                    }
                }
            }
        }

        //Posters and other details
        $ampStory = $doc->getElementsByTagName("amp-story");
        if(count($ampStory)){
            $ampStory = $ampStory->item(0);
            $propertiesToAdd = [
                "publisher-logo-src",
                "poster-portrait-src",
                "poster-landscape-src",
                "poster-square-src",
            ];
            foreach ($propertiesToAdd as $prop){
                $mediaInStory[] = $ampStory->getAttribute($prop);
            }
        }

        //CHeck in post meta data
        $meta = get_post_meta($postId, "publisher_details", true);
        try{
            $meta = json_decode($meta, true);
            $propertiesToAdd = [
                "publisher-logo-src",
                "poster-portrait-src",
                "poster-landscape-src",
                "poster-square-src",
            ];
            foreach ($propertiesToAdd as $prop){
                if(isset($meta[$prop])){
                    $mediaInStory[] = $meta[$prop];
                }
            }
        }catch(Exception $e){
            //Do Nothing
        }



        //Sanitize all the media urls to take only the MS hosted ones
        foreach ($mediaInStory as $imageUrl){
            foreach (MS_DOMAINS as $domain){
                if(strpos($imageUrl, $domain) !== false){
                    $media[] = ms_replace_domains($imageUrl);
                    break;
                }
            }
        }
    }
    header("Content-Type: application/json");
    print_r(json_encode(array_unique($media)));
    die();
}

function ms_replace_domains($url){
    $url = str_replace("storage.googleapis.com/makestories-202705.appspot.com",MS_CDN_LINK, $url);
    $url = str_replace("storage.googleapis.com/cdn-storyasset-link",MS_CDN_LINK, $url);
    return $url;
}

add_action("wp_ajax_ms_verify_media_in_story", "ms_verify_media_in_story");

function ms_change_story_slug(){
    ms_protect_ajax_route();
    if($_REQUEST['post'] && $_REQUEST['slug']){
        header("Content-Type: application/json");
        $postId = $_REQUEST['post'];
        $newTitle = $_REQUEST['slug'];
        $post = get_post($postId);
        if($post){
            wp_update_post([
                "post_name" => $newTitle,
                "ID" => $postId,
            ]);
            print_r(json_encode(getMSPostDataToSend($post)));
        }else{
            print_r(json_encode([
                "message"=> "Post not found!",
                "error" => true,
            ]));
        }
    }else{
        print_r(json_encode([
            "message"=> "Invalid arguments provided!",
            "error" => true,
        ]));
    }
    die();
}

function getMSPostDataToSend($post, $toReturn = []){
    if(!is_array($toReturn)){
        $toReturn = [];
    }
    $category = [];
    $terms = wp_get_post_terms($post->ID, MS_TAXONOMY);
    foreach ($terms as $term){
        $category[] = $term->name;
    }
	return array_merge([
        "id" => $post->ID,
        "lastUpdated" => strtotime($post->post_modified),
        "permalink" => get_post_permalink($post->ID),
        "name" => $post->post_name,
        "category" => $category,
    ], $toReturn);
}