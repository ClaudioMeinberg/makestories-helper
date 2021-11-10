<?php
/**
 * Hook to register MakeStories story post type
 */
add_action("init","mscpt_register_amp_stories_post_type");

/**
 * Hook function that registers custom post type
 */
function mscpt_register_amp_stories_post_type(){
    $toRewrite = false;
	if(isset($_POST['mscpt_makestories_post_slug']) && !empty($_POST['mscpt_makestories_post_slug']) && current_user_can('administrator')){
        check_admin_referer('mscpt_register_amp_stories_post_type');
        $slug = sanitize_title($_POST['mscpt_makestories_post_slug'],'story');
        update_option('mscpt_makestories_settings',['post_slug'=>$slug]);
        $toRewrite = true;
        //Setting rewrite flag to true to know that this is the first time we are registering the plugin and so need to rewrite the permalinks for it to work
    }
    $slug = ms_get_options();
	if($slug['to_rewrite']){
	    $toRewrite = true;
	    $slug['to_rewrite'] = false;
        update_option('mscpt_makestories_settings',$slug);
    }
	if(!empty($slug) && isset($slug['post_slug']))
	{
		register_post_type(
		    MS_POST_TYPE,
            array(
                'labels'=> array(
                    'name'=>__("MakeStories Web Stories",'post type general name'),
                    'singular_name'=>__("MakeStories Story",'post type general name'),
                ),
                'show_in_rest'=>true,
                'public'=>true,
                'show_ui'=>true,
                'show_in_menu'=>false,
                'has_archive'=>true,
                'rest_base' => 'makestories',
                'publicly_queryable' => true,
                'show_in_admin_bar'=>true,
                'taxonomies'  => array( MS_TAXONOMY ),
                'rewrite' => array(
                    'slug' => ms_get_slug(),
                    'with_front' => false,
                )
            )
        );

        $labels = array(
            'name' => _x( 'MS Story Category', 'taxonomy general name' ),
            'singular_name' => _x( 'MS Story Category', 'taxonomy singular name' ),
            'search_items' =>  __( 'Search MS Categories' ),
            'all_items' => __( 'All MS Categories' ),
            'parent_item' => __( 'Parent MS Category' ),
            'parent_item_colon' => __( 'Parent MS Category:' ),
            'edit_item' => __( 'Edit MS Category' ), 
            'update_item' => __( 'Update MS Category' ),
            'add_new_item' => __( 'Add New MS Category' ),
            'new_item_name' => __( 'New MS Category Name' ),
            'menu_name' => __( 'MS Category' ),
          );    
         
        // Now register the taxonomy
          register_taxonomy(MS_TAXONOMY,MS_POST_TYPE, array(
            'hierarchical' => false,
            'labels' => $labels,
            'show_ui' => true,
            'show_in_rest' => true,
            'show_admin_column' => true,
            'query_var' => true,
              'rewrite' => array(
                  'slug' => ms_get_slug().'-category'
              )
          ));

	}else{
	    //No option found for the options, show the modal to user to help him set options
        add_action('admin_head','mscpt_makeStoriesHeaderScript');
        add_action('admin_footer','mscpt_makeStoriesSlugModal');
	}
	if($toRewrite){
        flush_rewrite_rules();
    }
}

/**
 * Function that adds jQuery UI to header for showing Slug popup.
 */
function mscpt_makeStoriesHeaderScript(){ wp_enqueue_script( 'jquery-ui-dialog' ); wp_enqueue_style( 'wp-jquery-ui-dialog' ); }


/**
 * Function that adds slug modal template
 */
function mscpt_makeStoriesSlugModal(){ require_once(MS_PLUGIN_BASE_PATH.'/slug-modal.php'); }

add_action( 'wp', 'mscpt_amp_story_load_frontend' );

function mscpt_amp_story_load_frontend()
{
    global $post;
    if ($post && $post->post_type == MS_POST_TYPE && is_singular(MS_POST_TYPE)) {
        //If it is a story, then show the story html
        print_r(apply_filters("ms_story_html", $post->post_content));
        die();
    }
}

/**
 * Add hooks to set default values while activation and de-activation of plugin.
 */
register_activation_hook( __FILE__, 'mscpt_makeStoriesRegisterStoryOptions' );
function mscpt_makeStoriesRegisterStoryOptions(){ add_option('mscpt_makestories_settings',false); }

register_deactivation_hook( __FILE__, 'mscpt_makeStoriesDeleteStoryOptions' );
function mscpt_makeStoriesDeleteStoryOptions(){ delete_option('mscpt_makestories_settings'); }


/**
 * Function that enque styling to published stories via shortcode
 */
function mscpt_makeStoriesHeaderStyle() {
    wp_register_style( 'style-main', MS_PLUGIN_BASE_URL.'assets/css/ms-style.css');
    wp_register_script( 'script-main', MS_PLUGIN_BASE_URL.'assets/js/ms-script.js',array('jquery','slick-min-js'), false, true);
    wp_register_style( 'slick-theme-css', MS_PLUGIN_BASE_URL.'vendor/slick/slick-theme.css');
    wp_register_style( 'slick-css', MS_PLUGIN_BASE_URL.'vendor/slick/slick.css');
    wp_localize_script( "ajax-script", "ajaxurl", admin_url("admin-ajax.php"));
    wp_register_script( 'slick-min-js', MS_PLUGIN_BASE_URL.'vendor/slick/slick.min.js',array('jquery'), false, true);
}
add_action( 'wp_enqueue_scripts', 'mscpt_makeStoriesHeaderStyle' );


/**
 * Add styling to wp-admin area
 */
function mscpt_makeStoriesHeaderStyle_toAdmin() {
    wp_enqueue_style( 'style', MS_PLUGIN_BASE_URL.'assets/css/ms-custom.css');
    wp_enqueue_script( 'script', MS_PLUGIN_BASE_URL.'assets/js/ms-custom.js',array('jquery'));

    // For TinyMce 
    wp_localize_script( 'jquery', 'MS_SC_API_CONFIG', [
        'categories' => ms_get_categories_raw(),
        'stories' => ms_get_story_raw(),
    ]);
    wp_register_script('tiny-mce-js', MS_PLUGIN_BASE_URL.'assets/js/tiny-mce/tiny-mce.js');
}
add_action( 'admin_enqueue_scripts', 'mscpt_makeStoriesHeaderStyle_toAdmin' );


add_filter('post_type_link', 'mswp_permalink_structure', 10, 2);

function mswp_permalink_structure($post_link, $post) {
    $category = "category";
    $taxonomy = MS_TAXONOMY;
    if (false !== strpos($post_link, '%'.$category.'%')) {
        $projectscategory_type_term = get_the_terms($post->ID, $taxonomy);
        if (!empty($projectscategory_type_term)){
            $post_link = str_replace('%'.$category.'%', $projectscategory_type_term[0]->slug, $post_link);
        }
        else{
            $post_link = str_replace('%'.$category.'%/', "", $post_link);
        }
    }
    return $post_link;
}

function ms_add_action_links($links){
    $mylinks = array( '<a href="' . admin_url("admin.php?page=ms_settings") . '">Settings</a>' );
    return array_merge( $mylinks, $links );
}


/**
 * Action for register tinymce button [for generate shortcode] in editor
 */

function mscpt_tiny_mce_add_buttons( $plugins ) {
    $plugins['mytinymceplugin'] = MS_PLUGIN_BASE_URL.'assets/js/tiny-mce/tiny-mce.js';
    return $plugins;
  }
  
function mscpt_tiny_mce_register_buttons( $buttons ) {
    $newBtns = array(
        'myblockquotebtn'
    );
    $buttons = array_merge( $buttons, $newBtns );
    return $buttons;
}

add_action( 'init', 'mscpt_tiny_mce_new_buttons' );

function mscpt_tiny_mce_new_buttons() {
  add_filter( 'mce_external_plugins', 'mscpt_tiny_mce_add_buttons' );
  add_filter( 'mce_buttons', 'mscpt_tiny_mce_register_buttons' );
}

function mscpt_getTemplatePath($file){
    $theme_files = "/ms-templates". '/' . $file;
    if ( file_exists(get_template_directory() . $theme_files) ) {
        return get_template_directory() . $theme_files;
    } else {
        return MS_PLUGIN_BASE_PATH . '/templates' . '/' . $file;
    }  
}

add_filter('template_include', 'mscpt_template');
function mscpt_template( $template ) {
    if ( is_post_type_archive(MS_POST_TYPE) ) {
        // Post Archive
        return mscpt_getTemplatePath("archive-stories.php");
    }
    else if( is_tax(MS_TAXONOMY) ) {
        $theme_files = '/taxonomy-ms_story_category.php';
        if ( file_exists(get_template_directory() . $theme_files) ) {
            return get_template_directory() . $theme_files;
        } else {
            return MS_PLUGIN_BASE_PATH . $theme_files;
        }
    }

    return $template;
}

// Load more stories using ajax
function mscpt_more_post_ajax(){
    $offset = $_POST["offset"];
    $ppp = $_POST["ppp"];

    header("Content-Type: text/html");

    $args = [
        'suppress_filters' => true,
        'post_type' => MS_POST_TYPE,
        'posts_per_page' => $ppp,
        'offset' => $offset,
    ];

    $loop = new WP_Query($args);
    $out = '';

    while ($loop->have_posts()) { $loop->the_post();
        include mscpt_getTemplatePath("prepare-story-vars.php");
        include mscpt_getTemplatePath("single-story.php");
    }
    
    wp_reset_postdata(); 
    die($out);
}

add_action('wp_ajax_nopriv_more_post_ajax', 'mscpt_more_post_ajax'); 
add_action('wp_ajax_more_post_ajax', 'mscpt_more_post_ajax');

add_filter( 'admin_url', 'mswp_add_new_story_link', 10, 2 );
function mswp_add_new_story_link( $url, $path ){
    if( $path === 'post-new.php?post_type='.MS_POST_TYPE ) {
        $url = MS_WP_ADMIN_BASE_URL
            .(strpos(MS_WP_ADMIN_BASE_URL, "?") == false ? "?" : "")
            .build_query([
                "page" => MS_ROUTING['EDITOR']['slug'],
                "mspage" => "new-story",
            ]);
    }
    return $url;
}
