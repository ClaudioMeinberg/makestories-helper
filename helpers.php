<?php
/**
 * Validates if the incoming ajax request is from the referrer that we have set.
 * If not then aborts further execution by dying.
 */
function ms_protect_ajax_route(){
    $isValid = check_ajax_referer(MS_NONCE_REFERRER, false, false);
    if(!$isValid){
        die("Not Allowed");
    }
}

/**
 * Function to get built HTML content from MakeStories preview engine given the story ID.
 * @param $storyId string
 * @return bool|string
 */
function ms_get_story_HTML($storyId){
    $c = curl_init(MS_PREVIEW_URL.$storyId.'?forWordpress&v=2');
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    $html = curl_exec($c);

    if (curl_error($c)){
        die(json_encode(["success" => false, "error" => "Error occurred while fetching story data."]));
    }
    // Get the status code
    curl_close($c);
    return $html;
}

/**
 * Function to get MIMETYPE of URL
 * @param $imageurl string
 * @return bool|string
 */
function ms_find_filetype($imageurl){
    $c = curl_init(MS_BASE_SERVER_URL.'get-image-meta?url='.$imageurl);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

    $details = curl_exec($c);

    if (curl_error($c)){
        die(json_encode(["success" => false, "error" => "Error occurred while fetching image type."]));
    }
    // Get the status code
    curl_close($c);
    return json_decode($details, true);
}

function msEndsWith( $haystack, $needle ) {
    $length = strlen( $needle );
    if( !$length ) {
        return true;
    }
    return substr( $haystack, -$length ) === $needle;
}
function ms_get_options(){
    $options = get_option('mscpt_makestories_settings');
    $defaults = MS_DEFAULT_OPTIONS;
    if($options && is_array($options)){
        foreach ($defaults as $key => $value){
            if(isset($options[$key])){
                $defaults[$key] = $options[$key];
            }
        }
    }
    $options['forceUploadMedia'] = false;
    return $defaults;
}

function ms_set_options(){
    $options = ms_get_options();
    $options['categories_enabled'] = isset($_POST['categories_enabled']);
    if(isset($_POST['post_slug'])){
        $options['post_slug'] = $_POST['post_slug'];
    }
    if(isset($_POST['default_category'])){
        $options['default_category'] = $_POST['default_category'];
    }
    if(isset($_POST['roles']) && is_array($_POST['roles'])){
        $options['roles'] = $_POST['roles'];
    }
    $options['to_rewrite'] = true;
    if(isset($_POST['forceUploadMedia'])){
        $options['forceUploadMedia'] = true;
    }else{
        $options['forceUploadMedia'] = false;
    }
    update_option('mscpt_makestories_settings',$options);
    return $options;
}

function ms_is_categories_enabled(){
    $config = ms_get_options();
    return $config['categories_enabled'];
}
function ms_get_default_category(){
    $config = ms_get_options();
    return $config['default_category'];
}

function ms_get_slug(){
    $config = ms_get_options();
    return $config['post_slug'];
}

function ms_get_allowed_roles(){
    $config = ms_get_options();
    return $config['roles'];
}

function ms_get_categories_list() {
    $categories = get_terms([
        'taxonomy' => MS_TAXONOMY,
        'hide_empty' => false,
        'posts_per_page' => -1,
    ]);
    $cat = [];
    foreach($categories as $category) {
        array_push($cat,$category->name);
    }

    return $cat;
}