<noscript>You need to enable JavaScript to run this app.</noscript>
<div id="root"></div>
<script>
    <?php
    $slug = get_option('mscpt_makestories_settings');
    $baseUrl = get_site_url();
    if (!empty($slug) && isset($slug['post_slug'])) {
        $baseUrl = trailingslashit($baseUrl) . trailingslashit($slug['post_slug']);
    }
    $user = wp_get_current_user();
    ?>
    const msWPConfig = {
        wpBaseUrl: '<?php echo get_site_url(""); ?>',
        currentPage: "<?php echo $subpage; ?>",
        wpAdminBaseURL: '<?php echo MS_WP_ADMIN_BASE_URL; ?>',
        adminAjaxUrl: '<?php echo admin_url('admin-ajax.php') ?>',
        cpt: "<?php echo MS_POST_TYPE; ?>",
        wpStoriesBaseURL: '<?php echo $baseUrl; ?>',
        wpNonce: '<?php echo wp_create_nonce(MS_NONCE_REFERRER) ?>',
        wpUser: '<?php echo $user->ID; ?>',
        wpEmail: '<?php echo $user->user_email; ?>',
        wpUsername: '<?php echo $user->first_name." ".$user->last_name; ?>',
        isCategoriesEnabled: <?php echo ms_is_categories_enabled() ? "true" : "false"; ?>,
        adminPublishPost: '<?php echo admin_url( 'edit.php?post_type=' . MS_POST_TYPE ); ?>',
    };
    window.msWPConfig = msWPConfig;
</script>
