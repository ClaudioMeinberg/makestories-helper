<?php
/**
 * Adding shortcode for displaying all published stories on any page via shortcode 
 */
add_shortcode( 'ms_get_published_post', 'ms_get_published_post_via_shortcode' );

function ms_get_published_post_via_shortcode() {
    $default_posts_per_page = get_option( 'posts_per_page' );
    $getAjaxUrl =  admin_url('admin-ajax.php'); 
    ob_start();
    ?>
    <section class="default-stories">
        <h3>All Stories</h3>
        <div id="ajax-posts" class="stories-group"  data-posts="<?php echo $default_posts_per_page; ?>" data-ajax="<?php echo $getAjaxUrl; ?>" class="row">
            <?php 
            $postsPerPage = $default_posts_per_page;
            $args = [
                'post_type' => MS_POST_TYPE,
                'posts_per_page' => $postsPerPage,
            ];

            $loop = new WP_Query($args);
                while ($loop->have_posts()) : $loop->the_post();
                    include mscpt_getTemplatePath("prepare-story-vars.php");
                    include mscpt_getTemplatePath("single-story.php");
                endwhile;
            wp_reset_postdata(); ?>
        </div>
        <div class="load-more-wrap">
            <span id="loading-spinner"></span>
            <a id="more_posts">Load More</a>
        </div>
    </section>
<?php
return ob_get_clean();
}

/**
 * Adding shortcode for displaying list of published stories of perticular CATEGORY
 */
add_shortcode( 'ms_get_post_by_category', 'ms_get_post_by_category' );

function ms_get_post_by_category($attr) {
    $default_posts_per_page = get_option( 'posts_per_page' );
    $getAjaxUrl =  admin_url('admin-ajax.php');
    $cat_id = $attr['category_id'];
    $int_cat = (int)$cat_id;
    $term = get_term($int_cat,MS_TAXONOMY);
    $args = [
        'post_type' => MS_POST_TYPE,
        'posts_per_page' => -1,
        'offset'=> 0,
        "tax_query" => array(
            array(
                "taxonomy" => MS_TAXONOMY,
                "field" => "term_id",
                "terms" =>  $int_cat
            ))
    ];
    $loop = new WP_Query($args);
    ob_start();
    require(mscpt_getTemplatePath('ms-post-by-category.php'));
    return ob_get_clean();
}
/**
 * Adding shortcode for displaying single published story on any page via shortcode 
 */
add_shortcode( 'ms_get_single_post', 'ms_get_single_post_via_shortcode' );

function ms_get_single_post_via_shortcode($attr) {
    $args = [
        "post_type" => MS_POST_TYPE,
        "numberposts" => -1
    ];
    $posts = get_posts($args);
    ob_start();
    foreach ($posts as $post){
        $postId = $post->ID;
        if($postId == $attr['post_id']) { ?>
            <?php require(mscpt_getTemplatePath('ms-single-post.php'));
        }
    }
    return ob_get_clean();
}