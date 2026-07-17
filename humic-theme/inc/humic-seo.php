<?php
/**
 * HUMIC Engineering SEO Configurations
 */

if (!defined('ABSPATH')) {
    exit;
}

// Basic SEO Meta Tags
add_action('wp_head', 'humic_seo_meta_tags', 1);
function humic_seo_meta_tags() {
    $site_name = get_bloginfo('name');
    $title = wp_get_document_title();
    
    global $wp;
    $url = home_url(add_query_arg(array(), $wp->request));
    $desc = get_bloginfo('description');
    $image = '';

    if (is_singular()) {
        global $post;
        if (has_excerpt($post->ID)) {
            $desc = wp_strip_all_tags(get_the_excerpt($post->ID));
        } else {
            $desc = wp_trim_words(wp_strip_all_tags($post->post_content), 30);
        }
        if (has_post_thumbnail($post->ID)) {
            $image = get_the_post_thumbnail_url($post->ID, 'large');
        }
    }
    
    // Default image fallback if none is found
    if (empty($image)) {
        // We can use a custom logo if available, or just omit the image
        $custom_logo_id = get_theme_mod('custom_logo');
        if ($custom_logo_id) {
            $image = wp_get_attachment_image_url($custom_logo_id, 'full');
        }
    }
    
    // Escape for output
    $desc = esc_attr($desc);
    $url = esc_url($url);
    $title = esc_attr($title);
    $image = esc_url($image);
    
    echo "\n<!-- HUMIC SEO Tags -->\n";
    if (!empty($desc)) {
        echo "<meta name=\"description\" content=\"{$desc}\" />\n";
        echo "<meta property=\"og:description\" content=\"{$desc}\" />\n";
    }
    echo "<link rel=\"canonical\" href=\"{$url}\" />\n";
    echo "<meta property=\"og:title\" content=\"{$title}\" />\n";
    echo "<meta property=\"og:type\" content=\"" . (is_singular() ? 'article' : 'website') . "\" />\n";
    echo "<meta property=\"og:url\" content=\"{$url}\" />\n";
    echo "<meta property=\"og:site_name\" content=\"{$site_name}\" />\n";
    
    if (!empty($image)) {
        echo "<meta property=\"og:image\" content=\"{$image}\" />\n";
        echo "<meta name=\"twitter:card\" content=\"summary_large_image\" />\n";
    } else {
        echo "<meta name=\"twitter:card\" content=\"summary\" />\n";
    }
    echo "<!-- End HUMIC SEO Tags -->\n\n";
}
