<?php
/**
 * Version of plugin in use
 */
define("MS_PLUGIN_VERSION", "2.2");

/**
 * URL From where the main editor compiled files and other assets would be served
 */
define("MS_CDN_URL", "https://api.makestories.io/wp/".MS_PLUGIN_VERSION."/");

/**
 * Base server url
 */
define("MS_BASE_SERVER_URL", "https://server.makestories.io/");

/**
 * Url for viewing live preview of story being edited.
 */
define("MS_PREVIEW_URL", MS_BASE_SERVER_URL."preview/");

/**
 * Placeholder in html to change in order to set a correct page link as canonical while publishing
 */
define("MS_WORDPRESS_CANONICAL_SUBSTITUTION_PLACEHOLDER", "MS_WORDPRESS_CANONICAL_SUBSTITUTION_PLACEHOLDER");

/**
 * Starting point of execution in the editor scripts
 */
define("MS_MANIFEST_SCRIPT_URL", MS_CDN_URL."manifest.js");
define("MS_MAIN_SCRIPT_URL", MS_CDN_URL."chunks/index.js");
define("MS_VENDOR_SCRIPT_URL", MS_CDN_URL."chunks/vendor.js");

/**
 * WP Translation text domain - not used for now. Will setup translations later on.
 */
define("MS_TEXT_DOMAIN", "MAKESTORIES");

/**
 * Icon to be shown in Wordpress Leftbar Menu
 */
define("MS_MENU_ICON_URL", "MAKESTORIES");

/**
 * Post type to publish in while working on stories.
 */
define("MS_POST_TYPE", "makestories_story");
define("MS_TAXONOMY", "ms_story_category");

/**
 * Commn action for checking genuineness of request.
 */
define("MS_NONCE_REFERRER", "ms_wp_plugin_referrer");

/**
 * Router setup for backend
 */
define("MS_ROUTING", [
    "EDITOR" => [
        "slug" => "makestories-editor",
        "icon" => "dashicons-schedule"
    ],
    "Published" =>[
    	"slug"=>"published_stories_slug"
    ]
]);

define("MS_DEFAULT_OPTIONS", [
    "post_slug" => "web-stories",
    "categories_enabled" => false,
    "to_rewrite" => false,
    "forceUploadMedia" => false,
    "default_category" => "Uncategorized",
    "roles" => ["editor", "author", "administrator"],
]);

define("MS_CDN_LINK", "cdn.storyasset.link");

define("MS_DOMAINS", [
    "storage.googleapis.com/makestories",
    "storage.googleapis.com/cdn-storyasset-link",
    "makestories.io",
    "images.unsplash.com",
    "storyasset.link",
]);