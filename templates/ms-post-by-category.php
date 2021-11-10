<section class="default-stories">
    <h3><?php echo $term->name; ?></h3>
    <div class="stories-group">
        <?php
        while ($loop->have_posts()) : $loop->the_post();
            include mscpt_getTemplatePath("prepare-story-vars.php");
            include mscpt_getTemplatePath("single-story.php");
        endwhile;
        wp_reset_postdata();
        ?>
    </div>
</section>