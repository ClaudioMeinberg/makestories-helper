<?php get_header(); 
$default_posts_per_page = get_option( 'posts_per_page' );
$getAjaxUrl =  admin_url('admin-ajax.php'); ?>
<section class="default-stories">
    <div id="ajax-posts" class="stories-group" data-posts="<?php echo $default_posts_per_page; ?>" data-ajax="<?php echo $getAjaxUrl; ?>" class="row">
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

<?php get_footer(); ?>