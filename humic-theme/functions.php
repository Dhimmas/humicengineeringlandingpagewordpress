<?php
/**
 * HUMIC Engineering Theme Functions
 */

if (!defined('ABSPATH')) {
    exit;
}

// Enable theme support
add_action('after_setup_theme', function() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
});

// Add humic-custom-layout body class unconditionally
add_filter('body_class', function($classes) {
    if (!in_array('humic-custom-layout', $classes)) {
        $classes[] = 'humic-custom-layout';
    }
    return $classes;
});

// Load the custom architecture engine
require_once get_template_directory() . '/inc/humic-news.php';
require_once get_template_directory() . '/inc/humic-pages.php';
require_once get_template_directory() . '/inc/humic-extras.php';
