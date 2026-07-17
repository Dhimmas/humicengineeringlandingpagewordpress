    <?php
    /**
     * HUMIC Pages — Vision & Mission, Members, Partners archive, IPR, Media
     * WPCode PHP snippet (Run Everywhere). Requires humic-news.php.
     */

    if (!defined('ABSPATH')) {
        exit;
    }

    define('HUMIC_MEMBER_CPT', 'humic_member');
    define('HUMIC_IPR_CPT', 'humic_ipr');
    define('HUMIC_MEDIA_CPT', 'humic_media');
    define('HUMIC_CATALOG_URL', 'https://dev-katakatalog.pantheonsite.io/');
    define('HUMIC_IPR_DOC_URL', 'https://docs.google.com/document/d/1AcV0M0ti-Ng3o6jA1nbDrA9--mfUekph/edit');

    add_action('init', 'humic_pages_register');
    function humic_pages_register() {
        register_post_type(HUMIC_MEMBER_CPT, array(
            'labels' => array(
                'name'          => 'HUMIC Members',
                'singular_name' => 'Member',
                'add_new_item'  => 'Add Member',
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array('slug' => 'members'),
            'show_ui'      => true,
            'show_in_menu' => defined('HUMIC_ADMIN_MENU') ? HUMIC_ADMIN_MENU : 'edit.php?post_type=humic_news',
            'supports'     => array('title', 'editor', 'thumbnail', 'page-attributes'),
        ));

        register_post_type(HUMIC_IPR_CPT, array(
            'labels' => array(
                'name'          => 'HUMIC IPR',
                'singular_name' => 'IPR Item',
                'add_new_item'  => 'Add IPR Item',
            ),
            'public'       => true,
            'has_archive'  => false,
            'show_ui'      => true,
            'show_in_menu' => defined('HUMIC_ADMIN_MENU') ? HUMIC_ADMIN_MENU : 'edit.php?post_type=humic_news',
            'supports'     => array('title', 'editor', 'page-attributes'),
        ));

        register_post_type(HUMIC_MEDIA_CPT, array(
            'labels' => array(
                'name'          => 'HUMIC in Media',
                'singular_name' => 'Media Item',
                'add_new_item'  => 'Add Media Item',
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array('slug' => 'media'),
            'show_ui'      => true,
            'show_in_menu' => defined('HUMIC_ADMIN_MENU') ? HUMIC_ADMIN_MENU : 'edit.php?post_type=humic_news',
            'supports'     => array('title', 'editor', 'page-attributes'),
        ));

        add_rewrite_rule('^vision-mission/?$', 'index.php?humic_virtual_page=vision-mission', 'top');
        add_rewrite_rule('^ipr/?$', 'index.php?humic_virtual_page=ipr', 'top');
        add_rewrite_rule('^research/?$', 'index.php?humic_virtual_page=research', 'top');
    }

    add_filter('query_vars', 'humic_pages_query_vars');
    function humic_pages_query_vars($vars) {
        $vars[] = 'humic_virtual_page';
        return $vars;
    }

    add_action('init', function () {
        if (get_option('humic_pages_rewrite_v2') !== '1') {
            flush_rewrite_rules(false);
            update_option('humic_pages_rewrite_v2', '1');
        }
    }, 99);

    /* ── Meta boxes ── */

    add_action('add_meta_boxes', 'humic_member_meta_boxes');
    function humic_member_meta_boxes() {
        add_meta_box('humic_member_details', 'Member Details', 'humic_member_meta_render', HUMIC_MEMBER_CPT, 'side');
    }

    function humic_member_meta_render($post) {
        wp_nonce_field('humic_member_save', 'humic_member_nonce');
        $role     = get_post_meta($post->ID, '_humic_member_role', true);
        $position = get_post_meta($post->ID, '_humic_member_position', true);
        ?>
        <p>
            <label for="humic_member_role"><strong>Group</strong></label><br>
            <select id="humic_member_role" name="humic_member_role" class="widefat">
                <option value="head" <?php selected($role, 'head'); ?>>Head of Research Center</option>
                <option value="officer" <?php selected($role, 'officer'); ?>>Officer</option>
                <option value="researcher" <?php selected($role, 'researcher'); ?>>Researcher</option>
            </select>
        </p>
        <p>
            <label for="humic_member_position"><strong>Position label</strong></label><br>
            <input type="text" id="humic_member_position" name="humic_member_position"
                value="<?php echo esc_attr($position); ?>" class="widefat"
                placeholder="e.g. Administrative, Finance">
        </p>
        <hr>
        <p><strong>Social links</strong></p>
        <p class="description">Shown on the members page for all member types.</p>
        <?php
        $social_fields = array(
            'linkedin'  => 'LinkedIn URL',
            'twitter'   => 'Twitter / X URL',
            'facebook'  => 'Facebook URL',
            'scholar'   => 'Google Scholar URL',
        );
        foreach ($social_fields as $key => $label) :
            $val = get_post_meta($post->ID, '_humic_member_' . $key, true);
        ?>
        <p>
            <label for="humic_member_<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></label><br>
            <input type="url" id="humic_member_<?php echo esc_attr($key); ?>"
                name="humic_member_<?php echo esc_attr($key); ?>"
                value="<?php echo esc_attr($val); ?>" class="widefat"
                placeholder="https://">
        </p>
        <?php endforeach; ?>
        <p class="description">Use the editor for biography (Head of Research Center). Featured image = photo.</p>
        <?php
    }

    add_action('save_post_' . HUMIC_MEMBER_CPT, 'humic_member_save_meta');
    function humic_member_save_meta($post_id) {
        if (!isset($_POST['humic_member_nonce']) || !wp_verify_nonce($_POST['humic_member_nonce'], 'humic_member_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        update_post_meta($post_id, '_humic_member_role', sanitize_key($_POST['humic_member_role'] ?? 'researcher'));
        update_post_meta($post_id, '_humic_member_position', sanitize_text_field($_POST['humic_member_position'] ?? ''));
        foreach (array('linkedin', 'twitter', 'facebook', 'scholar') as $key) {
            $field = 'humic_member_' . $key;
            if (isset($_POST[$field])) {
                update_post_meta($post_id, '_humic_member_' . $key, esc_url_raw($_POST[$field]));
            }
        }
    }

    add_action('add_meta_boxes', 'humic_ipr_meta_boxes');
    function humic_ipr_meta_boxes() {
        add_meta_box('humic_ipr_details', 'IPR Type', 'humic_ipr_meta_render', HUMIC_IPR_CPT, 'side');
    }

    function humic_ipr_meta_render($post) {
        wp_nonce_field('humic_ipr_save', 'humic_ipr_nonce');
        $type = get_post_meta($post->ID, '_humic_ipr_type', true);
        ?>
        <p>
            <label for="humic_ipr_type"><strong>Category</strong></label><br>
            <select id="humic_ipr_type" name="humic_ipr_type" class="widefat">
                <option value="paten" <?php selected($type, 'paten'); ?>>Paten</option>
                <option value="paten_sederhana" <?php selected($type, 'paten_sederhana'); ?>>Paten Sederhana</option>
                <option value="hki" <?php selected($type, 'hki'); ?>>HKI</option>
            </select>
        </p>
        <?php
    }

    add_action('save_post_' . HUMIC_IPR_CPT, 'humic_ipr_save_meta');
    function humic_ipr_save_meta($post_id) {
        if (!isset($_POST['humic_ipr_nonce']) || !wp_verify_nonce($_POST['humic_ipr_nonce'], 'humic_ipr_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        update_post_meta($post_id, '_humic_ipr_type', sanitize_key($_POST['humic_ipr_type'] ?? 'hki'));
    }

    add_action('add_meta_boxes', 'humic_media_meta_boxes');
    function humic_media_meta_boxes() {
        add_meta_box('humic_media_details', 'Video', 'humic_media_meta_render', HUMIC_MEDIA_CPT, 'side');
    }

    function humic_media_meta_render($post) {
        wp_nonce_field('humic_media_save', 'humic_media_nonce');
        $url = get_post_meta($post->ID, '_humic_media_video_url', true);
        ?>
        <p>
            <label for="humic_media_video_url"><strong>YouTube URL or embed ID</strong></label><br>
            <input type="url" id="humic_media_video_url" name="humic_media_video_url"
                value="<?php echo esc_attr($url); ?>" class="widefat"
                placeholder="https://www.youtube.com/watch?v=...">
        </p>
        <?php
    }

    add_action('save_post_' . HUMIC_MEDIA_CPT, 'humic_media_save_meta');
    function humic_media_save_meta($post_id) {
        if (!isset($_POST['humic_media_nonce']) || !wp_verify_nonce($_POST['humic_media_nonce'], 'humic_media_save')) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        update_post_meta($post_id, '_humic_media_video_url', esc_url_raw($_POST['humic_media_video_url'] ?? ''));
    }

    /* ── URL helpers & shortcodes ── */

    function humic_get_vision_url() {
        return home_url('/vision-mission/');
    }

    function humic_get_members_url() {
        $url = get_post_type_archive_link(HUMIC_MEMBER_CPT);
        return $url ? $url : home_url('/members/');
    }

    function humic_get_partners_url() {
        return function_exists('humic_news_section_url')
            ? humic_news_section_url('partners')
            : (function_exists('humic_news_get_home_url') ? humic_news_get_home_url() . '#partners' : home_url('/#partners'));
    }

    function humic_get_vision_text() {
        $saved = get_option('humic_vision_text', '');
        if (is_string($saved) && $saved !== '') {
            return $saved;
        }
        return 'To become an excellent research center in the field of engineering to improve the human health and prosperity.';
    }

    function humic_get_mission_summary_text() {
        $saved = get_option('humic_mission_summary', '');
        if (is_string($saved) && $saved !== '') {
            return $saved;
        }
        return 'HUMIC Engineering pursues excellence across embedded IoT sensor systems for biomedical applications, remote health monitoring, Big Data Analytics, and ICT development for human health.';
    }

    function humic_get_mission_items() {
        $saved = get_option('humic_mission_items', '');
        if (is_string($saved) && trim($saved) !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $saved);
            $items = array();
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $items[] = $line;
                }
            }
            if ($items) {
                return $items;
            }
        }
        return array(
            'Becoming the science and technology excellent center in the field of embedded sensor systems to support biomedical applications based on the Internet of Things (IoT).',
            'Becoming the science and technology excellent center on development remote health monitoring systems based on Internet of Things (IoT).',
            'Becoming the science and technology excellent center on Big Data Analytic.',
            'Becoming the science and technology excellent center on health development of Information and Communication Technology (ICT).',
        );
    }

    function humic_get_timeline_image_url() {
        $image_id = (int) get_option('humic_timeline_image_id', 0);
        if ($image_id) {
            $url = wp_get_attachment_image_url($image_id, 'full');
            if ($url) {
                return $url;
            }
        }
        if (function_exists('humic_news_asset_url')) {
            return humic_news_asset_url('humic-timeline.png');
        }
        return '';
    }

    function humic_get_timeline_items() {
        return array(
            array('year' => '2018', 'text' => 'Research Group On Telecardiology'),
            array('year' => '2019', 'text' => 'Research Center for Human Centric Engineering, Focused on Telehealthcare'),
            array('year' => '2020–2022', 'text' => 'PUI Human Centric Engineering, Top 30 Indonesian Research Institute on Telehealthcare'),
            array('year' => '2023', 'text' => 'Top 30 Asian Research Institute on Telehealthcare'),
        );
    }

    function humic_get_ipr_url() {
        return home_url('/ipr/');
    }

    function humic_get_media_url() {
        $url = get_post_type_archive_link(HUMIC_MEDIA_CPT);
        return $url ? $url : home_url('/media/');
    }

    function humic_get_catalog_url() {
        $saved = get_option('humic_catalog_url', '');
        if ($saved !== '' && $saved !== false) {
            return $saved;
        }
        return HUMIC_CATALOG_URL;
    }

    function humic_get_research_page_url() {
        $saved = get_option('humic_research_page_url', '');
        if ($saved !== '' && $saved !== false) {
            return $saved;
        }
        return home_url('/research/');
    }

    function humic_get_research_intro() {
        $saved = get_option('humic_research_intro', '');
        if ($saved !== '' && $saved !== false) {
            return $saved;
        }
        return 'HUMIC Engineering focuses on human-centric technology across IoT, biomedical engineering, Big Data Analytics, and ICT development to support health and daily life.';
    }

    function humic_get_social_defaults() {
        return array(
            'facebook'  => 'https://www.facebook.com/humicengineering/',
            'instagram' => 'https://www.instagram.com/humicengineering/',
            'linkedin'  => 'https://id.linkedin.com/company/humic-engineering',
            'youtube'   => 'https://www.youtube.com/channel/UCYHvzg7lY2WuwyKlj8jRNAw',
        );
    }

    function humic_get_social_networks() {
        return array(
            'instagram' => array('label' => 'Instagram', 'icon' => 'fa-brands fa-instagram'),
        );
    }

    function humic_get_social_url($key) {
        $defaults = humic_get_social_defaults();
        $saved    = get_option('humic_social_' . $key, '');
        if ($saved !== '' && $saved !== false) {
            return $saved;
        }
        return $defaults[$key] ?? '';
    }

    function humic_render_social_links($wrapper_class = 'footer-social') {
        $networks = humic_get_social_networks();
        ob_start();
        echo '<div class="' . esc_attr($wrapper_class) . '">';
        foreach ($networks as $key => $meta) {
            $url = humic_get_social_url($key);
            if (!$url) {
                continue;
            }
            printf(
                '<a href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s"><i class="%s" aria-hidden="true"></i></a>',
                esc_url($url),
                esc_attr($meta['label']),
                esc_attr($meta['icon'])
            );
        }
        echo '</div>';
        return ob_get_clean();
    }

    add_action('admin_init', function () {
        if (get_option('humic_site_defaults_v1') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        if (get_option('humic_catalog_url', '') === '') {
            update_option('humic_catalog_url', HUMIC_CATALOG_URL);
        }
        $social_defaults = humic_get_social_defaults();
        foreach ($social_defaults as $key => $url) {
            if (get_option('humic_social_' . $key, '') === '') {
                update_option('humic_social_' . $key, $url);
            }
        }
        update_option('humic_site_defaults_v1', '1');
    }, 18);

    add_shortcode('humic_vision_url', function () { return esc_url(humic_get_vision_url()); });
    add_shortcode('humic_vision_text', function () { return esc_html(humic_get_vision_text()); });
    add_shortcode('humic_mission_summary', function () { return esc_html(humic_get_mission_summary_text()); });
    add_shortcode('humic_members_url', function () { return esc_url(humic_get_members_url()); });
    add_shortcode('humic_partners_url', function () { return esc_url(humic_get_partners_url()); });
    add_shortcode('humic_ipr_url', function () { return esc_url(humic_get_ipr_url()); });
    add_shortcode('humic_media_url', function () { return esc_url(humic_get_media_url()); });
    add_shortcode('humic_catalog_url', function () { return esc_url(humic_get_catalog_url()); });
    add_shortcode('humic_research_url', function () { return esc_url(humic_get_research_page_url()); });

    /* ── Homepage stats bar ── */

    function humic_get_stats_defaults() {
        return array(
            array('value' => '100+', 'label' => 'Research Papers'),
            array('value' => '30+', 'label' => 'Research Projects'),
            array('value' => '10+', 'label' => 'Patented Products'),
        );
    }

    function humic_get_stats_items() {
        $saved = get_option('humic_stats_items');
        if (!is_array($saved) || empty($saved)) {
            return apply_filters('humic_stats_items', humic_get_stats_defaults());
        }
        $items = array();
        foreach ($saved as $row) {
            if (!is_array($row)) {
                continue;
            }
            $value = sanitize_text_field($row['value'] ?? '');
            $label = sanitize_text_field($row['label'] ?? '');
            if ($value === '' && $label === '') {
                continue;
            }
            $items[] = array(
                'value' => $value,
                'label' => $label,
            );
        }
        if (empty($items)) {
            return apply_filters('humic_stats_items', humic_get_stats_defaults());
        }
        return apply_filters('humic_stats_items', $items);
    }

    /* ── Admin: Vision & Mission settings ── */

    add_action('admin_menu', 'humic_pages_admin_menu', 100);
    function humic_pages_admin_menu() {
        if (!defined('HUMIC_ADMIN_MENU')) {
            return;
        }
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Vision & Mission',
            'Vision & Mission',
            'edit_posts',
            'humic-vision-mission-settings',
            'humic_pages_vision_mission_admin_page'
        );
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Homepage Stats',
            'Homepage Stats',
            'edit_posts',
            'humic-homepage-stats',
            'humic_pages_stats_admin_page'
        );
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Contact Us',
            'Contact Us',
            'edit_posts',
            'humic-contact-settings',
            'humic_pages_contact_admin_page'
        );
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Homepage',
            'Homepage',
            'edit_posts',
            'humic-homepage-settings',
            'humic_pages_homepage_admin_page'
        );
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Research Areas',
            'Research Areas',
            'edit_posts',
            'humic-research-settings',
            'humic_pages_research_admin_page'
        );
        add_submenu_page(
            HUMIC_ADMIN_MENU,
            'Navigation Menu',
            'Navigation Menu',
            'edit_posts',
            'humic-nav-settings',
            'humic_pages_nav_admin_page'
        );
    }

    add_action('admin_enqueue_scripts', 'humic_pages_admin_assets');
    function humic_pages_admin_assets($hook) {
        $vision_hook   = 'humic-engineering_page_humic-vision-mission-settings';
        $contact_hook  = 'humic-engineering_page_humic-contact-settings';
        $homepage_hook = 'humic-engineering_page_humic-homepage-settings';
        $allowed       = array($vision_hook, $contact_hook, $homepage_hook);
        if (!in_array($hook, $allowed, true)) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'humic-pages-admin',
            includes_url('js/jquery/jquery.min.js'),
            array('jquery'),
            false,
            true
        );

        if ($hook === $vision_hook) {
            wp_add_inline_script('jquery', "
                jQuery(function ($) {
                    var frame;
                    $('#humic-timeline-image-btn').on('click', function (e) {
                        e.preventDefault();
                        if (frame) { frame.open(); return; }
                        frame = wp.media({
                            title: 'Select Timeline Image',
                            button: { text: 'Use this image' },
                            multiple: false
                        });
                        frame.on('select', function () {
                            var attachment = frame.state().get('selection').first().toJSON();
                            $('#humic_timeline_image_id').val(attachment.id);
                            $('#humic-timeline-image-preview').html('<img src=\"' + attachment.url + '\" style=\"max-width:100%;height:auto;border:1px solid #ddd;border-radius:4px;\" alt=\"\">');
                            $('#humic-timeline-image-remove').show();
                        });
                        frame.open();
                    });
                    $('#humic-timeline-image-remove').on('click', function (e) {
                        e.preventDefault();
                        $('#humic_timeline_image_id').val('');
                        $('#humic-timeline-image-preview').empty();
                        $(this).hide();
                    });
                });
            ");
        }

        if ($hook === $contact_hook) {
            wp_add_inline_script('jquery', "
                jQuery(function ($) {
                    var frame;
                    $('#humic-keluhan-qr-btn').on('click', function (e) {
                        e.preventDefault();
                        if (frame) { frame.open(); return; }
                        frame = wp.media({
                            title: 'Select QR Code Image',
                            button: { text: 'Use this image' },
                            multiple: false
                        });
                        frame.on('select', function () {
                            var attachment = frame.state().get('selection').first().toJSON();
                            $('#humic_keluhan_qr_id').val(attachment.id);
                            $('#humic-keluhan-qr-preview').html('<img src=\"' + attachment.url + '\" style=\"max-width:220px;height:auto;border:1px solid #ddd;border-radius:4px;\" alt=\"\">');
                            $('#humic-keluhan-qr-remove').show();
                        });
                        frame.open();
                    });
                    $('#humic-keluhan-qr-remove').on('click', function (e) {
                        e.preventDefault();
                        $('#humic_keluhan_qr_id').val('');
                        $('#humic-keluhan-qr-preview').empty();
                        $(this).hide();
                    });
                });
            ");
        }

        if ($hook === $homepage_hook) {
            wp_add_inline_script('jquery', "
                jQuery(function ($) {
                    function bindMedia(btnId, inputId, previewId, removeId, title) {
                        var frame;
                        $(btnId).on('click', function (e) {
                            e.preventDefault();
                            if (frame) { frame.open(); return; }
                            frame = wp.media({
                                title: title,
                                button: { text: 'Use this image' },
                                multiple: false
                            });
                            frame.on('select', function () {
                                var attachment = frame.state().get('selection').first().toJSON();
                                $(inputId).val(attachment.id);
                                $(previewId).html('<img src=\"' + attachment.url + '\" style=\"max-width:220px;height:auto;border:1px solid #ddd;border-radius:4px;\" alt=\"\">');
                                $(removeId).show();
                            });
                            frame.open();
                        });
                        $(removeId).on('click', function (e) {
                            e.preventDefault();
                            $(inputId).val('');
                            $(previewId).empty();
                            $(this).hide();
                        });
                    }
                    bindMedia('#humic-hero-bg-btn', '#humic_hero_bg_id', '#humic-hero-bg-preview', '#humic-hero-bg-remove', 'Select Hero Background');
                    bindMedia('#humic-about-photo-btn', '#humic_about_photo_id', '#humic-about-photo-preview', '#humic-about-photo-remove', 'Select About Photo');
                    bindMedia('#humic-about-logo-btn', '#humic_about_logo_id', '#humic-about-logo-preview', '#humic-about-logo-remove', 'Select About Logo');
                });
            ");
        }
    }

    function humic_pages_vision_mission_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }
        if (isset($_POST['humic_vision_save']) && check_admin_referer('humic_vision_save', 'humic_vision_nonce')) {
            update_option('humic_vision_text', sanitize_textarea_field($_POST['humic_vision_text'] ?? ''));
            update_option('humic_mission_summary', sanitize_textarea_field($_POST['humic_mission_summary'] ?? ''));
            update_option('humic_mission_items', sanitize_textarea_field($_POST['humic_mission_items'] ?? ''));
            update_option('humic_timeline_image_id', absint($_POST['humic_timeline_image_id'] ?? 0));
            echo '<div class="notice notice-success is-dismissible"><p>Vision &amp; Mission saved.</p></div>';
        }

        $vision   = get_option('humic_vision_text', humic_get_vision_text());
        $summary  = get_option('humic_mission_summary', humic_get_mission_summary_text());
        $missions = get_option('humic_mission_items', '');
        if ($missions === '') {
            $missions = implode("\n", humic_get_mission_items());
        }
        $image_id = (int) get_option('humic_timeline_image_id', 0);
        $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
    $preview_url = $image_url ?: humic_get_timeline_image_url();
        $page_url = humic_get_vision_url();
        ?>
        <div class="wrap">
            <h1>Vision &amp; Mission</h1>
            <p>Edit content for the <a href="<?php echo esc_url($page_url); ?>" target="_blank" rel="noopener noreferrer">Vision &amp; Mission page</a>.</p>
            <form method="post">
                <?php wp_nonce_field('humic_vision_save', 'humic_vision_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_vision_text">Vision</label></th>
                        <td><textarea id="humic_vision_text" name="humic_vision_text" rows="3" class="large-text"><?php echo esc_textarea($vision); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_mission_summary">Mission summary (homepage)</label></th>
                        <td><textarea id="humic_mission_summary" name="humic_mission_summary" rows="3" class="large-text"><?php echo esc_textarea($summary); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_mission_items">Mission items (one per line)</label></th>
                        <td><textarea id="humic_mission_items" name="humic_mission_items" rows="8" class="large-text"><?php echo esc_textarea($missions); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row">Timeline image</th>
                        <td>
                            <input type="hidden" id="humic_timeline_image_id" name="humic_timeline_image_id" value="<?php echo esc_attr($image_id); ?>">
                            <div id="humic-timeline-image-preview" style="margin-bottom:12px;max-width:900px;">
                            <?php if ($preview_url) : ?>
                                <img src="<?php echo esc_url($preview_url); ?>" alt="Timeline preview" style="max-width:100%;height:auto;border:1px solid #ddd;border-radius:4px;">
                            <?php endif; ?>
                            </div>
                            <p>
                                <button type="button" class="button" id="humic-timeline-image-btn">Select image</button>
                                <button type="button" class="button" id="humic-timeline-image-remove"<?php echo $image_id ? '' : ' style="display:none;"'; ?>>Remove</button>
                            </p>
                            <p class="description">Upload timeline graphic (recommended). Default file: <code>wp-content/uploads/2026/06/humic-timeline.png</code></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="humic_vision_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    function humic_pages_stats_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['humic_stats_save']) && check_admin_referer('humic_stats_save', 'humic_stats_nonce')) {
            $items = array();
            $values = $_POST['humic_stat_value'] ?? array();
            $labels = $_POST['humic_stat_label'] ?? array();
            if (is_array($values) && is_array($labels)) {
                $count = max(count($values), count($labels));
                for ($i = 0; $i < $count; $i++) {
                    $value = sanitize_text_field($values[$i] ?? '');
                    $label = sanitize_text_field($labels[$i] ?? '');
                    if ($value === '' && $label === '') {
                        continue;
                    }
                    $items[] = array(
                        'value' => $value,
                        'label' => $label,
                    );
                }
            }
            update_option('humic_stats_items', $items);
            echo '<div class="notice notice-success is-dismissible"><p>Homepage stats saved.</p></div>';
        }

        $items = get_option('humic_stats_items');
        if (!is_array($items) || empty($items)) {
            $items = humic_get_stats_defaults();
        }
        ?>
        <div class="wrap">
            <h1>Homepage Stats</h1>
            <p>Edit the red statistics bar on the homepage (<code>[humic_stats_bar]</code>).</p>
            <form method="post">
                <?php wp_nonce_field('humic_stats_save', 'humic_stats_nonce'); ?>
                <table class="form-table" role="presentation">
                    <thead>
                        <tr>
                            <th scope="col">Value</th>
                            <th scope="col">Label</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    for ($i = 0; $i < 3; $i++) {
                        $row = $items[$i] ?? array('value' => '', 'label' => '');
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="humic_stat_value[]"
                                    value="<?php echo esc_attr($row['value'] ?? ''); ?>"
                                    class="regular-text" placeholder="e.g. 100+">
                            </td>
                            <td>
                                <input type="text" name="humic_stat_label[]"
                                    value="<?php echo esc_attr($row['label'] ?? ''); ?>"
                                    class="regular-text" placeholder="e.g. Research Papers">
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
                <p class="description">Three statistics are shown in a row on the homepage. Include a number in each value (e.g. <code>100+</code>) for the count-up animation.</p>
                <p class="submit">
                    <button type="submit" name="humic_stats_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    /* ── Homepage: Hero, About, Research Areas ── */

    function humic_pages_get_option_image_url($option_key, $default_filename = '') {
        $image_id = (int) get_option($option_key, 0);
        if ($image_id) {
            $url = wp_get_attachment_image_url($image_id, 'full');
            if ($url) {
                return $url;
            }
        }
        if ($default_filename && function_exists('humic_news_asset_url')) {
            return humic_news_asset_url($default_filename);
        }
        return '';
    }

    function humic_sanitize_icon_class($icon) {
        return trim(preg_replace('/[^a-z0-9\s\-]/i', '', (string) $icon));
    }

    function humic_get_hero_defaults() {
        return array(
            'label'      => 'Research Center',
            'title'      => 'Human Centric',
            'highlight'  => 'Engineering',
            'desc'       => 'Advancing science through human-centered innovation in IoT, Big Data, Biomedical, and ICT research at Telkom University, Bandung.',
            'btn1_text'  => 'Vision & Mission',
            'btn1_url'   => '',
            'btn2_text'  => 'Join Us',
            'btn2_url'   => '#contact',
            'bg_file'    => 'image-5.png',
        );
    }

    function humic_get_about_defaults() {
        return array(
            'eyebrow'     => 'Who We Are',
            'title'       => 'About HUMIC',
            'badge_year'  => '2016',
            'badge_label' => 'Established',
            'photo_file'  => 'image-4.png',
            'logo_file'   => 'image-22.png',
        );
    }

    function humic_get_research_defaults() {
        return array(
            array(
                'num'    => '01',
                'icon'   => 'fa-solid fa-microchip',
                'slug'   => 'research-iot',
                'title'  => 'Internet of Things',
                'desc'   => 'Embedded sensor systems and connected devices for smart environments and healthcare monitoring.',
                'detail' => 'Our IoT research develops embedded sensor systems and connected devices for smart environments, remote monitoring, and healthcare applications. We integrate hardware, firmware, and data pipelines to support real-time decision making in clinical and community settings.',
            ),
            array(
                'num'    => '02',
                'icon'   => 'fa-solid fa-heart-pulse',
                'slug'   => 'research-biomed',
                'title'  => 'Biomedical Engineering',
                'desc'   => 'Health monitoring systems and biomedical signal processing to support clinical decision making.',
                'detail' => 'Biomedical engineering at HUMIC focuses on wearable health devices, biomedical signal processing, and monitoring systems that help clinicians and patients track vital signs, detect anomalies, and support timely intervention.',
            ),
            array(
                'num'    => '03',
                'icon'   => 'fa-solid fa-chart-line',
                'slug'   => 'research-bigdata',
                'title'  => 'Big Data Analytics',
                'desc'   => 'Large-scale data processing and machine learning to derive insights from complex datasets.',
                'detail' => 'We apply Big Data Analytics and machine learning to health, IoT, and human activity datasets—turning raw signals into actionable knowledge for research, diagnostics, and service improvement.',
            ),
            array(
                'num'    => '04',
                'icon'   => 'fa-solid fa-network-wired',
                'slug'   => 'research-ict',
                'title'  => 'ICT Development',
                'desc'   => 'Information and communication technologies for national digital infrastructure and services.',
                'detail' => 'Our ICT development work supports digital health platforms, communication systems, and human-centric engineering services that connect devices, people, and institutions across Telkom University and partner organizations.',
            ),
        );
    }

    function humic_get_research_items() {
        $saved = get_option('humic_research_items');
        if (!is_array($saved) || empty($saved)) {
            return apply_filters('humic_research_items', humic_get_research_defaults());
        }
        $defaults = humic_get_research_defaults();
        $items = array();
        foreach ($saved as $index => $row) {
            if (!is_array($row)) {
                continue;
            }
            $fallback = $defaults[$index] ?? array();
            $title = sanitize_text_field($row['title'] ?? ($fallback['title'] ?? ''));
            if ($title === '') {
                continue;
            }
            $items[] = array(
                'num'    => sanitize_text_field($row['num'] ?? ($fallback['num'] ?? '')),
                'icon'   => humic_sanitize_icon_class($row['icon'] ?? ($fallback['icon'] ?? 'fa-solid fa-flask')),
                'slug'   => sanitize_title($row['slug'] ?? ($fallback['slug'] ?? '')),
                'title'  => $title,
                'desc'   => sanitize_textarea_field($row['desc'] ?? ($fallback['desc'] ?? '')),
                'detail' => sanitize_textarea_field($row['detail'] ?? ($fallback['detail'] ?? '')),
            );
        }
        if (empty($items)) {
            return apply_filters('humic_research_items', humic_get_research_defaults());
        }
        return apply_filters('humic_research_items', $items);
    }

    function humic_hero_shortcode() {
        $defaults = humic_get_hero_defaults();
        $label     = get_option('humic_hero_label', $defaults['label']);
        $title     = get_option('humic_hero_title', $defaults['title']);
        $highlight = get_option('humic_hero_highlight', $defaults['highlight']);
        $desc      = get_option('humic_hero_desc', $defaults['desc']);
        $btn1_text = get_option('humic_hero_btn1_text', $defaults['btn1_text']);
        $btn1_url  = get_option('humic_hero_btn1_url', '');
        $btn2_text = get_option('humic_hero_btn2_text', $defaults['btn2_text']);
        $btn2_url  = get_option('humic_hero_btn2_url', $defaults['btn2_url']);
        $bg_url    = humic_pages_get_option_image_url('humic_hero_bg_id', $defaults['bg_file']);

        if ($btn1_url === '') {
            $btn1_url = humic_get_vision_url();
        }
        if ($btn2_url === '') {
            $btn2_url = function_exists('humic_news_section_url') ? humic_news_section_url('contact') : '#contact';
        }

        ob_start();
        ?>
        <section id="home" class="hero">
            <div class="hero-bg">
                <?php if ($bg_url) : ?>
                    <img src="<?php echo esc_url($bg_url); ?>" alt="<?php echo esc_attr($title . ' ' . $highlight); ?>">
                <?php endif; ?>
                <div class="hero-overlay"></div>
            </div>
            <div class="container hero-content">
                <?php if ($label) : ?>
                    <span class="hero-label"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
                <h1 class="hero-h1">
                    <?php echo esc_html($title); ?>
                    <?php if ($highlight) : ?>
                        <span class="text-red"><?php echo esc_html($highlight); ?></span>
                    <?php endif; ?>
                </h1>
                <?php if ($desc) : ?>
                    <p class="hero-desc"><?php echo esc_html($desc); ?></p>
                <?php endif; ?>
                <div class="hero-btns">
                    <?php if ($btn1_text && $btn1_url) : ?>
                        <a href="<?php echo esc_url($btn1_url); ?>" class="btn-red"><?php echo esc_html($btn1_text); ?></a>
                    <?php endif; ?>
                    <?php if ($btn2_text && $btn2_url) : ?>
                        <a href="<?php echo esc_url($btn2_url); ?>" class="btn-outline"><?php echo esc_html($btn2_text); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    add_shortcode('humic_hero', 'humic_hero_shortcode');

    function humic_about_shortcode() {
        $defaults = humic_get_about_defaults();
        $eyebrow     = get_option('humic_about_eyebrow', $defaults['eyebrow']);
        $title       = get_option('humic_about_title', $defaults['title']);
        $badge_year  = get_option('humic_about_badge_year', $defaults['badge_year']);
        $badge_label = get_option('humic_about_badge_label', $defaults['badge_label']);
        $photo_url   = humic_pages_get_option_image_url('humic_about_photo_id', $defaults['photo_file']);
        $logo_url    = humic_pages_get_option_image_url('humic_about_logo_id', $defaults['logo_file']);

        ob_start();
        ?>
        <section id="about" class="about">
            <div class="container about-grid">
                <div class="about-img-col">
                    <?php if ($photo_url) : ?>
                        <img src="<?php echo esc_url($photo_url); ?>" alt="<?php echo esc_attr($title); ?>" class="about-photo">
                    <?php endif; ?>
                    <?php if ($badge_year || $badge_label) : ?>
                        <div class="about-badge">
                            <?php if ($badge_year) : ?>
                                <div class="badge-year"><?php echo esc_html($badge_year); ?></div>
                            <?php endif; ?>
                            <?php if ($badge_label) : ?>
                                <div class="badge-label"><?php echo esc_html($badge_label); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <div class="about-img-bar" aria-hidden="true">
                        <div class="about-img-bar-red"></div>
                        <div class="about-img-bar-logo">
                            <?php if ($logo_url) : ?>
                                <img src="<?php echo esc_url($logo_url); ?>" alt="HUMiC Engineering">
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="about-txt-col">
                    <?php if ($eyebrow) : ?>
                        <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
                    <?php endif; ?>
                    <?php if ($title) : ?>
                        <h2 class="section-title"><?php echo esc_html($title); ?></h2>
                    <?php endif; ?>
                    <div class="vision-block">
                        <h3 class="block-title">Our Vision</h3>
                        <p class="vision-text"><?php echo esc_html(humic_get_vision_text()); ?></p>
                    </div>
                    <div class="mission-block">
                        <h3 class="block-title">Our Mission</h3>
                        <p class="about-mission-summary"><?php echo esc_html(humic_get_mission_summary_text()); ?></p>
                        <a href="<?php echo esc_url(humic_get_vision_url()); ?>" class="link-arr about-vision-link">Full Vision &amp; Mission <i class="fa-solid fa-arrow-up-right"></i></a>
                    </div>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    add_shortcode('humic_about', 'humic_about_shortcode');

    function humic_render_research_cards($mode = 'home') {
        $items = humic_get_research_items();

        foreach ($items as $index => $item) {
            $slug   = $item['slug'] ?: sanitize_title($item['title']);
            $icon   = humic_sanitize_icon_class($item['icon']);
            $detail = $item['detail'] ?? '';
            $desc   = $item['desc'] ?? '';

            if ($mode === 'home') {
                ?>
                <article class="rcard rcard-home" id="<?php echo esc_attr($slug); ?>">
                    <div class="rcard-top">
                        <?php if ($icon) : ?>
                            <i class="<?php echo esc_attr($icon); ?> rcard-icon"></i>
                        <?php endif; ?>
                        <?php if (!empty($item['num'])) : ?>
                            <span class="rcard-num"><?php echo esc_html($item['num']); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="rcard-title"><?php echo esc_html($item['title']); ?></h3>
                    <?php if ($desc) : ?>
                        <p class="rcard-desc"><?php echo esc_html($desc); ?></p>
                    <?php endif; ?>
                </article>
                <?php
                continue;
            }

            $panel_id = 'rcard-panel-' . $slug;
            ?>
            <article class="rcard rcard-accordion" id="<?php echo esc_attr($slug); ?>" data-humic-research-card data-humic-order="<?php echo (int) $index; ?>">
                <button type="button" class="rcard-accordion-head" aria-expanded="false" aria-controls="<?php echo esc_attr($panel_id); ?>">
                    <div class="rcard-top">
                        <?php if ($icon) : ?>
                            <i class="<?php echo esc_attr($icon); ?> rcard-icon"></i>
                        <?php endif; ?>
                        <?php if (!empty($item['num'])) : ?>
                            <span class="rcard-num"><?php echo esc_html($item['num']); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="rcard-accordion-head-main">
                        <h3 class="rcard-title"><?php echo esc_html($item['title']); ?></h3>
                        <?php if ($detail) : ?>
                            <span class="rcard-accordion-chevron" aria-hidden="true"><i class="fa-solid fa-chevron-down"></i></span>
                        <?php endif; ?>
                    </div>
                </button>
                <?php if ($desc || $detail) : ?>
                    <div class="rcard-accordion-body" id="<?php echo esc_attr($panel_id); ?>">
                        <?php if ($desc) : ?>
                            <p class="rcard-desc"><?php echo esc_html($desc); ?></p>
                        <?php endif; ?>
                        <?php if ($detail) : ?>
                            <div class="rcard-accordion-detail"><?php echo nl2br(esc_html($detail)); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>
            <?php
        }
    }

    function humic_research_areas_shortcode() {
        $research_url = humic_get_research_page_url();

        ob_start();
        ?>
        <section id="research" class="research">
            <div class="container">
                <div class="section-hdr">
                    <div>
                        <span class="eyebrow">What We Do</span>
                        <h2 class="section-title">Research Areas</h2>
                    </div>
                    <a href="<?php echo esc_url($research_url); ?>" class="link-arr">View All Research <i class="fa-solid fa-arrow-up-right"></i></a>
                </div>
                <div class="research-grid">
                    <?php humic_render_research_cards('home'); ?>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    }
    add_shortcode('humic_research_areas', 'humic_research_areas_shortcode');

    function humic_pages_render_research() {
        $home  = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
        $intro = humic_get_research_intro();
        humic_render_page_hdr('HUMIC Engineering', 'Research Areas', $home, 'Back to Home');
        ?>
        <div class="humic-research-page">
            <?php if ($intro) : ?>
                <p class="humic-research-intro"><?php echo esc_html($intro); ?></p>
            <?php endif; ?>
            <div class="research-grid humic-research-page-grid">
                <?php humic_render_research_cards('page'); ?>
            </div>
        </div>
        <?php
    }

    function humic_pages_render_admin_image_field($input_id, $option_key, $default_file = '') {
        $image_id = (int) get_option($option_key, 0);
        $preview  = $image_id ? wp_get_attachment_image_url($image_id, 'medium') : '';
        $slug     = str_replace('_', '-', preg_replace('/_id$/', '', $input_id));
        $btn_id   = $slug . '-btn';
        $preview_id = $slug . '-preview';
        $remove_id  = $slug . '-remove';
        ?>
        <input type="hidden" id="<?php echo esc_attr($input_id); ?>" name="<?php echo esc_attr($option_key); ?>" value="<?php echo esc_attr($image_id); ?>">
        <div id="<?php echo esc_attr($preview_id); ?>">
            <?php if ($preview) : ?>
                <img src="<?php echo esc_url($preview); ?>" style="max-width:220px;height:auto;border:1px solid #ddd;border-radius:4px;" alt="">
            <?php endif; ?>
        </div>
        <p>
            <button type="button" class="button" id="<?php echo esc_attr($btn_id); ?>">Select Image</button>
            <button type="button" class="button" id="<?php echo esc_attr($remove_id); ?>" <?php echo $image_id ? '' : 'style="display:none;"'; ?>>Remove</button>
        </p>
        <?php if ($default_file) : ?>
            <p class="description">Default file: <code>wp-content/uploads/2026/06/<?php echo esc_html($default_file); ?></code></p>
        <?php endif; ?>
        <?php
    }

    function humic_pages_homepage_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['humic_homepage_save']) && check_admin_referer('humic_homepage_save', 'humic_homepage_nonce')) {
            update_option('humic_hero_label', sanitize_text_field($_POST['humic_hero_label'] ?? ''));
            update_option('humic_hero_title', sanitize_text_field($_POST['humic_hero_title'] ?? ''));
            update_option('humic_hero_highlight', sanitize_text_field($_POST['humic_hero_highlight'] ?? ''));
            update_option('humic_hero_desc', sanitize_textarea_field($_POST['humic_hero_desc'] ?? ''));
            update_option('humic_hero_btn1_text', sanitize_text_field($_POST['humic_hero_btn1_text'] ?? ''));
            update_option('humic_hero_btn1_url', esc_url_raw($_POST['humic_hero_btn1_url'] ?? ''));
            update_option('humic_hero_btn2_text', sanitize_text_field($_POST['humic_hero_btn2_text'] ?? ''));
            update_option('humic_hero_btn2_url', esc_url_raw($_POST['humic_hero_btn2_url'] ?? ''));
            update_option('humic_hero_bg_id', absint($_POST['humic_hero_bg_id'] ?? 0));
            update_option('humic_about_eyebrow', sanitize_text_field($_POST['humic_about_eyebrow'] ?? ''));
            update_option('humic_about_title', sanitize_text_field($_POST['humic_about_title'] ?? ''));
            update_option('humic_about_badge_year', sanitize_text_field($_POST['humic_about_badge_year'] ?? ''));
            update_option('humic_about_badge_label', sanitize_text_field($_POST['humic_about_badge_label'] ?? ''));
            update_option('humic_about_photo_id', absint($_POST['humic_about_photo_id'] ?? 0));
            update_option('humic_about_logo_id', absint($_POST['humic_about_logo_id'] ?? 0));
            update_option('humic_vision_text', sanitize_textarea_field($_POST['humic_vision_text'] ?? ''));
            update_option('humic_mission_summary', sanitize_textarea_field($_POST['humic_mission_summary'] ?? ''));
            echo '<div class="notice notice-success is-dismissible"><p>Homepage settings saved.</p></div>';
        }

        $hero = humic_get_hero_defaults();
        $about = humic_get_about_defaults();
        ?>
        <div class="wrap">
            <h1>Homepage</h1>
            <p>Edit hero and about sections (<code>[humic_hero]</code>, <code>[humic_about]</code>).</p>
            <form method="post">
                <?php wp_nonce_field('humic_homepage_save', 'humic_homepage_nonce'); ?>

                <h2 class="title">Hero Section</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_hero_label">Label</label></th>
                        <td><input type="text" id="humic_hero_label" name="humic_hero_label" value="<?php echo esc_attr(get_option('humic_hero_label', $hero['label'])); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_hero_title">Title</label></th>
                        <td><input type="text" id="humic_hero_title" name="humic_hero_title" value="<?php echo esc_attr(get_option('humic_hero_title', $hero['title'])); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_hero_highlight">Highlighted Word</label></th>
                        <td><input type="text" id="humic_hero_highlight" name="humic_hero_highlight" value="<?php echo esc_attr(get_option('humic_hero_highlight', $hero['highlight'])); ?>" class="regular-text"><p class="description">Shown in red after the title.</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_hero_desc">Description</label></th>
                        <td><textarea id="humic_hero_desc" name="humic_hero_desc" rows="3" class="large-text"><?php echo esc_textarea(get_option('humic_hero_desc', $hero['desc'])); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_hero_btn1_text">Primary Button</label></th>
                        <td>
                            <input type="text" id="humic_hero_btn1_text" name="humic_hero_btn1_text" value="<?php echo esc_attr(get_option('humic_hero_btn1_text', $hero['btn1_text'])); ?>" class="regular-text" placeholder="Text">
                            <input type="url" name="humic_hero_btn1_url" value="<?php echo esc_attr(get_option('humic_hero_btn1_url', '')); ?>" class="large-text" placeholder="URL (empty = Vision & Mission page)">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_hero_btn2_text">Secondary Button</label></th>
                        <td>
                            <input type="text" id="humic_hero_btn2_text" name="humic_hero_btn2_text" value="<?php echo esc_attr(get_option('humic_hero_btn2_text', $hero['btn2_text'])); ?>" class="regular-text" placeholder="Text">
                            <input type="text" name="humic_hero_btn2_url" value="<?php echo esc_attr(get_option('humic_hero_btn2_url', $hero['btn2_url'])); ?>" class="large-text" placeholder="URL or #contact">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Background Image</th>
                        <td><?php humic_pages_render_admin_image_field('humic_hero_bg_id', 'humic_hero_bg_id', $hero['bg_file']); ?></td>
                    </tr>
                </table>

                <h2 class="title">About Section</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_about_eyebrow">Eyebrow</label></th>
                        <td><input type="text" id="humic_about_eyebrow" name="humic_about_eyebrow" value="<?php echo esc_attr(get_option('humic_about_eyebrow', $about['eyebrow'])); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_about_title">Section Title</label></th>
                        <td><input type="text" id="humic_about_title" name="humic_about_title" value="<?php echo esc_attr(get_option('humic_about_title', $about['title'])); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_about_badge_year">Badge Year</label></th>
                        <td><input type="text" id="humic_about_badge_year" name="humic_about_badge_year" value="<?php echo esc_attr(get_option('humic_about_badge_year', $about['badge_year'])); ?>" class="small-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_about_badge_label">Badge Label</label></th>
                        <td><input type="text" id="humic_about_badge_label" name="humic_about_badge_label" value="<?php echo esc_attr(get_option('humic_about_badge_label', $about['badge_label'])); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th scope="row">About Photo</th>
                        <td><?php humic_pages_render_admin_image_field('humic_about_photo_id', 'humic_about_photo_id', $about['photo_file']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Logo (bottom bar)</th>
                        <td><?php humic_pages_render_admin_image_field('humic_about_logo_id', 'humic_about_logo_id', $about['logo_file']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_vision_text">Our Vision</label></th>
                        <td><textarea id="humic_vision_text" name="humic_vision_text" rows="3" class="large-text"><?php echo esc_textarea(get_option('humic_vision_text', humic_get_vision_text())); ?></textarea></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_mission_summary">Our Mission Summary</label></th>
                        <td><textarea id="humic_mission_summary" name="humic_mission_summary" rows="3" class="large-text"><?php echo esc_textarea(get_option('humic_mission_summary', humic_get_mission_summary_text())); ?></textarea></td>
                    </tr>
                </table>
                <p class="description">Full vision and mission items (timeline, etc.) can also be edited under <strong>Vision &amp; Mission</strong>.</p>
                <p class="submit">
                    <button type="submit" name="humic_homepage_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    function humic_pages_research_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['humic_research_save']) && check_admin_referer('humic_research_save', 'humic_research_nonce')) {
            update_option('humic_catalog_url', esc_url_raw($_POST['humic_catalog_url'] ?? ''));
            update_option('humic_research_page_url', esc_url_raw($_POST['humic_research_page_url'] ?? ''));
            update_option('humic_research_intro', sanitize_textarea_field($_POST['humic_research_intro'] ?? ''));
            $items = array();
            $nums   = $_POST['humic_research_num'] ?? array();
            $icons  = $_POST['humic_research_icon'] ?? array();
            $slugs  = $_POST['humic_research_slug'] ?? array();
            $titles = $_POST['humic_research_title'] ?? array();
            $descs    = $_POST['humic_research_desc'] ?? array();
            $details  = $_POST['humic_research_detail'] ?? array();
            $count    = max(count($titles), count($descs), count($details));
            for ($i = 0; $i < $count; $i++) {
                $title = sanitize_text_field($titles[$i] ?? '');
                if ($title === '') {
                    continue;
                }
                $items[] = array(
                    'num'    => sanitize_text_field($nums[$i] ?? ''),
                    'icon'   => humic_sanitize_icon_class($icons[$i] ?? ''),
                    'slug'   => sanitize_title($slugs[$i] ?? ''),
                    'title'  => $title,
                    'desc'   => sanitize_textarea_field($descs[$i] ?? ''),
                    'detail' => sanitize_textarea_field($details[$i] ?? ''),
                );
            }
            update_option('humic_research_items', $items);
            echo '<div class="notice notice-success is-dismissible"><p>Research areas saved.</p></div>';
        }

        $items = get_option('humic_research_items');
        if (!is_array($items) || empty($items)) {
            $items = humic_get_research_defaults();
        }
        $catalog = humic_get_catalog_url();
        $catalog_saved = get_option('humic_catalog_url', '');
        $research_page_url = get_option('humic_research_page_url', '');
        $research_intro = get_option('humic_research_intro', humic_get_research_intro());
        ?>
        <div class="wrap">
            <h1>Research Areas</h1>
            <p>Edit homepage research cards (<code>[humic_research_areas]</code>) and the full research page at <code>/research/</code>.</p>
            <form method="post">
                <?php wp_nonce_field('humic_research_save', 'humic_research_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_research_page_url">View All Research URL</label></th>
                        <td>
                            <input type="url" id="humic_research_page_url" name="humic_research_page_url"
                                value="<?php echo esc_attr($research_page_url !== '' ? $research_page_url : home_url('/research/')); ?>"
                                class="large-text">
                            <p class="description">Internal research overview page. Default: <code><?php echo esc_html(home_url('/research/')); ?></code> (<code>[humic_research_url]</code>)</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_research_intro">Research Page Intro</label></th>
                        <td>
                            <textarea id="humic_research_intro" name="humic_research_intro" rows="3" class="large-text"><?php echo esc_textarea($research_intro); ?></textarea>
                            <p class="description">Shown at the top of the <strong>/research/</strong> page.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_catalog_url">Catalog Products URL</label></th>
                        <td>
                            <input type="url" id="humic_catalog_url" name="humic_catalog_url"
                                value="<?php echo esc_attr($catalog_saved !== '' ? $catalog_saved : $catalog); ?>"
                                class="large-text" placeholder="<?php echo esc_attr(HUMIC_CATALOG_URL); ?>">
                            <p class="description">External product catalog for the <strong>Catalog Products</strong> menu in the header (<code>[humic_catalog_url]</code>).</p>
                        </td>
                    </tr>
                </table>
                <h2 class="title">Research Cards</h2>
                <table class="widefat striped" style="max-width:960px">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Icon class</th>
                            <th>Slug / ID</th>
                            <th>Title</th>
                            <th>Summary</th>
                            <th>Detail (Learn More)</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php for ($i = 0; $i < 4; $i++) :
                        $row = $items[$i] ?? array();
                        ?>
                        <tr>
                            <td><input type="text" name="humic_research_num[]" value="<?php echo esc_attr($row['num'] ?? ''); ?>" class="small-text" style="width:3em"></td>
                            <td><input type="text" name="humic_research_icon[]" value="<?php echo esc_attr($row['icon'] ?? ''); ?>" class="regular-text" placeholder="fa-solid fa-microchip"></td>
                            <td><input type="text" name="humic_research_slug[]" value="<?php echo esc_attr($row['slug'] ?? ''); ?>" class="regular-text" placeholder="research-iot"></td>
                            <td><input type="text" name="humic_research_title[]" value="<?php echo esc_attr($row['title'] ?? ''); ?>" class="regular-text"></td>
                            <td><textarea name="humic_research_desc[]" rows="3" class="large-text"><?php echo esc_textarea($row['desc'] ?? ''); ?></textarea></td>
                            <td><textarea name="humic_research_detail[]" rows="4" class="large-text"><?php echo esc_textarea($row['detail'] ?? ''); ?></textarea></td>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
                <p class="description">Icon uses Font Awesome classes. <strong>Summary</strong> is always visible; <strong>Detail</strong> expands on the page when visitors click Learn More.</p>
                <p class="submit">
                    <button type="submit" name="humic_research_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    function humic_pages_nav_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['humic_nav_save']) && check_admin_referer('humic_nav_save', 'humic_nav_nonce')) {
            $raw = isset($_POST['humic_nav_menu']) ? wp_unslash($_POST['humic_nav_menu']) : '';
            update_option('humic_nav_menu', sanitize_textarea_field($raw));
            echo '<div class="notice notice-success is-dismissible"><p>Navigation menu saved.</p></div>';
        }

        $nav_text = humic_nav_get_text();
        ?>
        <div class="wrap">
            <h1>Navigation Menu</h1>
            <p>Edit the header menu (desktop &amp; mobile). The footer <strong>Quick Links</strong> column is built automatically from the same items.</p>
            <form method="post">
                <?php wp_nonce_field('humic_nav_save', 'humic_nav_nonce'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_nav_menu">Menu items</label></th>
                        <td>
                            <textarea id="humic_nav_menu" name="humic_nav_menu" rows="16" class="large-text code" style="font-family:monospace;"><?php echo esc_textarea($nav_text); ?></textarea>
                        </td>
                    </tr>
                </table>

                <h2 class="title">How to write the menu</h2>
                <ul style="list-style:disc;margin-left:20px;max-width:820px;">
                    <li>One item per line: <code>Label | URL</code></li>
                    <li>Indent a line (spaces or tab) to make it a <strong>sub-item</strong> (dropdown child) of the line above.</li>
                    <li>To open a link in a new tab, add <code>| external</code> at the end, e.g. <code>Catalog Products | https://example.com | external</code></li>
                    <li>Leave the URL empty for a plain header (a parent that only opens a dropdown).</li>
                </ul>

                <h2 class="title">Auto URL tokens</h2>
                <p>Use these tokens instead of typing the URL — they always point to the right page even if the slug changes:</p>
                <table class="widefat striped" style="max-width:620px">
                    <thead><tr><th>Token</th><th>Goes to</th></tr></thead>
                    <tbody>
                        <tr><td><code>{home}</code></td><td>Homepage</td></tr>
                        <tr><td><code>{vision}</code></td><td>Vision &amp; Mission</td></tr>
                        <tr><td><code>{members}</code></td><td>Members</td></tr>
                        <tr><td><code>{research}</code></td><td>Research Areas page</td></tr>
                        <tr><td><code>{catalog}</code></td><td>Catalog Products (external)</td></tr>
                        <tr><td><code>{ipr}</code></td><td>Intellectual Property Rights</td></tr>
                        <tr><td><code>{media}</code></td><td>HUMIC in Media</td></tr>
                        <tr><td><code>{news}</code></td><td>News archive</td></tr>
                        <tr><td><code>{events}</code></td><td>Events archive</td></tr>
                        <tr><td><code>{partners}</code></td><td>Homepage #partners</td></tr>
                        <tr><td><code>{contact}</code></td><td>Homepage #contact</td></tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" name="humic_nav_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    /* ── Contact: Office Location & Layanan Keluhan ── */

    function humic_get_office_defaults() {
        $maps = function_exists('humic_maps_url') ? humic_maps_url() : 'https://www.google.com/maps/place/Gedung+F+Telkom+University/@-6.9768982,107.6310164,17z';
        return array(
            'heading'     => 'Office Location',
            'address'     => "Jl. Telekomunikasi No. 1, Terusan Buah Batu\nBandung 40257, Indonesia",
            'phone'       => '+62 22 756 6458',
            'email'       => 'humic@telkomuniversity.ac.id',
            'maps_url'    => $maps,
            'button_text' => 'View on Map',
        );
    }

    function humic_get_office_heading() {
        $saved = get_option('humic_office_heading', '');
        return $saved !== '' ? $saved : humic_get_office_defaults()['heading'];
    }

    function humic_get_office_address() {
        $saved = get_option('humic_office_address', '');
        return $saved !== '' ? $saved : humic_get_office_defaults()['address'];
    }

    function humic_get_office_phone() {
        $saved = get_option('humic_office_phone', '');
        return $saved !== '' ? $saved : humic_get_office_defaults()['phone'];
    }

    function humic_get_office_email() {
        $saved = get_option('humic_office_email', '');
        return $saved !== '' ? $saved : humic_get_office_defaults()['email'];
    }

    function humic_get_office_maps_url() {
        $saved = get_option('humic_office_maps_url', '');
        if ($saved !== '') {
            return $saved;
        }
        return humic_get_office_defaults()['maps_url'];
    }

    function humic_get_office_button_text() {
        $saved = get_option('humic_office_button_text', '');
        return $saved !== '' ? $saved : humic_get_office_defaults()['button_text'];
    }

    function humic_contact_office_shortcode() {
        $heading = humic_get_office_heading();
        $address = humic_get_office_address();
        $phone   = humic_get_office_phone();
        $email   = humic_get_office_email();
        $maps    = humic_get_office_maps_url();
        $button  = humic_get_office_button_text();

        ob_start();
        ?>
        <div class="contact-box contact-box-office">
            <div class="contact-box-hdr"><?php echo esc_html($heading); ?></div>
            <div class="contact-box-body">
                <div class="contact-box-content">
                    <?php if ($address) : ?>
                        <div class="ci-row">
                            <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                            <address><?php echo nl2br(esc_html($address)); ?></address>
                        </div>
                    <?php endif; ?>
                    <?php if ($phone) : ?>
                        <div class="ci-row">
                            <i class="fa-solid fa-phone" aria-hidden="true"></i>
                            <span><?php echo esc_html($phone); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if ($email) : ?>
                        <div class="ci-row">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($maps && $button) : ?>
                    <a href="<?php echo esc_url($maps); ?>" target="_blank" rel="noopener noreferrer" class="btn-red btn-full contact-box-action"><?php echo esc_html($button); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('humic_contact_office', 'humic_contact_office_shortcode');

    function humic_get_keluhan_defaults() {
        return array(
            'heading' => 'Layanan Keluhan',
            'label'   => 'Form Layanan Keluhan RC HUMIC:',
            'url'     => 'https://bit.ly/Layanan_Keluhan_RCHUMIC',
        );
    }

    function humic_get_keluhan_heading() {
        $saved = get_option('humic_keluhan_heading');
        if ($saved !== false && $saved !== '') {
            return $saved;
        }
        return humic_get_keluhan_defaults()['heading'];
    }

    function humic_get_keluhan_label() {
        $saved = get_option('humic_keluhan_label');
        if ($saved !== false && $saved !== '') {
            return $saved;
        }
        return humic_get_keluhan_defaults()['label'];
    }

    function humic_get_keluhan_url() {
        $saved = get_option('humic_keluhan_url', '');
        if ($saved === '' || $saved === false) {
            return humic_get_keluhan_defaults()['url'];
        }
        return $saved;
    }

    function humic_get_keluhan_qr_url() {
        $image_id = (int) get_option('humic_keluhan_qr_id', 0);
        if ($image_id) {
            $url = wp_get_attachment_image_url($image_id, 'full');
            if ($url) {
                return $url;
            }
        }
        if (function_exists('humic_news_asset_url')) {
            return humic_news_asset_url('humic-keluhan-qr.png');
        }
        return '';
    }

    function humic_contact_keluhan_shortcode() {
        $heading = humic_get_keluhan_heading();
        $label   = humic_get_keluhan_label();
        $url     = humic_get_keluhan_url();
        $qr_url  = humic_get_keluhan_qr_url();

        if (!$url && !$qr_url) {
            return '';
        }

        ob_start();
        ?>
        <div class="contact-box contact-box-keluhan">
            <div class="contact-box-hdr"><?php echo esc_html($heading); ?></div>
            <div class="contact-box-body">
                <div class="contact-box-content contact-keluhan">
                    <?php if ($label) : ?>
                        <p class="contact-keluhan-label"><?php echo esc_html($label); ?></p>
                    <?php endif; ?>
                    <?php if ($url) : ?>
                        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="contact-keluhan-link"><?php echo esc_html($url); ?></a>
                    <?php endif; ?>
                    <?php if ($qr_url) : ?>
                        <a href="<?php echo esc_url($url ?: $qr_url); ?>" target="_blank" rel="noopener noreferrer" class="contact-keluhan-qr-link">
                            <img src="<?php echo esc_url($qr_url); ?>" alt="<?php echo esc_attr($label); ?>" class="contact-keluhan-qr" width="400" height="400" loading="lazy">
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    add_shortcode('humic_contact_keluhan', 'humic_contact_keluhan_shortcode');

    function humic_pages_contact_admin_page() {
        if (!current_user_can('edit_posts')) {
            return;
        }

        if (isset($_POST['humic_contact_save']) && check_admin_referer('humic_contact_save', 'humic_contact_nonce')) {
            update_option('humic_office_heading', sanitize_text_field($_POST['humic_office_heading'] ?? ''));
            update_option('humic_office_address', sanitize_textarea_field($_POST['humic_office_address'] ?? ''));
            update_option('humic_office_phone', sanitize_text_field($_POST['humic_office_phone'] ?? ''));
            update_option('humic_office_email', sanitize_email($_POST['humic_office_email'] ?? ''));
            update_option('humic_office_maps_url', esc_url_raw($_POST['humic_office_maps_url'] ?? ''));
            update_option('humic_office_button_text', sanitize_text_field($_POST['humic_office_button_text'] ?? ''));
            update_option('humic_keluhan_heading', sanitize_text_field($_POST['humic_keluhan_heading'] ?? ''));
            update_option('humic_keluhan_label', sanitize_text_field($_POST['humic_keluhan_label'] ?? ''));
            update_option('humic_keluhan_url', esc_url_raw($_POST['humic_keluhan_url'] ?? ''));
            update_option('humic_keluhan_qr_id', absint($_POST['humic_keluhan_qr_id'] ?? 0));
            update_option('humic_social_facebook', esc_url_raw($_POST['humic_social_facebook'] ?? ''));
            update_option('humic_social_instagram', esc_url_raw($_POST['humic_social_instagram'] ?? ''));
            update_option('humic_social_linkedin', esc_url_raw($_POST['humic_social_linkedin'] ?? ''));
            update_option('humic_social_youtube', esc_url_raw($_POST['humic_social_youtube'] ?? ''));
            echo '<div class="notice notice-success is-dismissible"><p>Contact &amp; footer settings saved.</p></div>';
        }

        $office_defaults = humic_get_office_defaults();
        $office_heading  = get_option('humic_office_heading', $office_defaults['heading']);
        $office_address  = get_option('humic_office_address', $office_defaults['address']);
        $office_phone    = get_option('humic_office_phone', $office_defaults['phone']);
        $office_email    = get_option('humic_office_email', $office_defaults['email']);
        $office_maps     = get_option('humic_office_maps_url', $office_defaults['maps_url']);
        $office_button   = get_option('humic_office_button_text', $office_defaults['button_text']);

        $keluhan_defaults = humic_get_keluhan_defaults();
        $heading  = get_option('humic_keluhan_heading', $keluhan_defaults['heading']);
        $label    = get_option('humic_keluhan_label', $keluhan_defaults['label']);
        $url      = get_option('humic_keluhan_url', $keluhan_defaults['url']);
        $qr_id    = (int) get_option('humic_keluhan_qr_id', 0);
        $qr_url   = $qr_id ? wp_get_attachment_image_url($qr_id, 'medium') : '';

        $social_defaults = humic_get_social_defaults();
        $social_facebook  = get_option('humic_social_facebook', $social_defaults['facebook']);
        $social_instagram = get_option('humic_social_instagram', $social_defaults['instagram']);
        $social_linkedin  = get_option('humic_social_linkedin', $social_defaults['linkedin']);
        $social_youtube   = get_option('humic_social_youtube', $social_defaults['youtube']);
        ?>
        <div class="wrap">
            <h1>Contact Us</h1>
            <p>Edit contact blocks on the homepage and shared footer/header info (address, email, social links).</p>
            <form method="post">
                <?php wp_nonce_field('humic_contact_save', 'humic_contact_nonce'); ?>

                <h2 class="title">Office Location</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_office_heading">Box Heading</label></th>
                        <td>
                            <input type="text" id="humic_office_heading" name="humic_office_heading"
                                value="<?php echo esc_attr($office_heading); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_office_address">Address</label></th>
                        <td>
                            <textarea id="humic_office_address" name="humic_office_address" rows="3" class="large-text"><?php echo esc_textarea($office_address); ?></textarea>
                            <p class="description">One line per row. Also shown in the site footer.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_office_phone">Phone</label></th>
                        <td>
                            <input type="text" id="humic_office_phone" name="humic_office_phone"
                                value="<?php echo esc_attr($office_phone); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_office_email">Email</label></th>
                        <td>
                            <input type="email" id="humic_office_email" name="humic_office_email"
                                value="<?php echo esc_attr($office_email); ?>" class="regular-text">
                            <p class="description">Also used in the header topbar and footer.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_office_maps_url">Maps URL</label></th>
                        <td>
                            <input type="url" id="humic_office_maps_url" name="humic_office_maps_url"
                                value="<?php echo esc_attr($office_maps); ?>" class="large-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_office_button_text">Button Text</label></th>
                        <td>
                            <input type="text" id="humic_office_button_text" name="humic_office_button_text"
                                value="<?php echo esc_attr($office_button); ?>" class="regular-text">
                        </td>
                    </tr>
                </table>

                <h2 class="title">Header &amp; Footer Social Media</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_social_facebook">Facebook</label></th>
                        <td><input type="url" id="humic_social_facebook" name="humic_social_facebook" value="<?php echo esc_attr($social_facebook); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_social_instagram">Instagram</label></th>
                        <td><input type="url" id="humic_social_instagram" name="humic_social_instagram" value="<?php echo esc_attr($social_instagram); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_social_linkedin">LinkedIn</label></th>
                        <td><input type="url" id="humic_social_linkedin" name="humic_social_linkedin" value="<?php echo esc_attr($social_linkedin); ?>" class="large-text"></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_social_youtube">YouTube</label></th>
                        <td><input type="url" id="humic_social_youtube" name="humic_social_youtube" value="<?php echo esc_attr($social_youtube); ?>" class="large-text"></td>
                    </tr>
                </table>
                <p class="description">Social links appear in the header topbar and footer. Research area links in the footer use settings from <strong>Research Areas</strong>.</p>

                <h2 class="title">Layanan Keluhan</h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="humic_keluhan_heading">Box Heading</label></th>
                        <td>
                            <input type="text" id="humic_keluhan_heading" name="humic_keluhan_heading"
                                value="<?php echo esc_attr($heading); ?>" class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_keluhan_label">Label Text</label></th>
                        <td>
                            <input type="text" id="humic_keluhan_label" name="humic_keluhan_label"
                                value="<?php echo esc_attr($label); ?>" class="large-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="humic_keluhan_url">Form URL</label></th>
                        <td>
                            <input type="url" id="humic_keluhan_url" name="humic_keluhan_url"
                                value="<?php echo esc_attr($url); ?>" class="large-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">QR Code Image</th>
                        <td>
                            <input type="hidden" id="humic_keluhan_qr_id" name="humic_keluhan_qr_id" value="<?php echo esc_attr($qr_id); ?>">
                            <div id="humic-keluhan-qr-preview">
                                <?php if ($qr_url) : ?>
                                    <img src="<?php echo esc_url($qr_url); ?>" style="max-width:220px;height:auto;border:1px solid #ddd;border-radius:4px;" alt="">
                                <?php endif; ?>
                            </div>
                            <p>
                                <button type="button" class="button" id="humic-keluhan-qr-btn">Select Image</button>
                                <button type="button" class="button" id="humic-keluhan-qr-remove" <?php echo $qr_id ? '' : 'style="display:none;"'; ?>>Remove</button>
                            </p>
                            <p class="description">Default file: <code>wp-content/uploads/2026/06/humic-keluhan-qr.png</code></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" name="humic_contact_save" class="button button-primary">Save Changes</button>
                </p>
            </form>
        </div>
        <?php
    }

    /* ── Body class ── */

    add_filter('body_class', 'humic_pages_body_class');
    function humic_pages_body_class($classes) {
        $virtual = get_query_var('humic_virtual_page');
        if ($virtual === 'vision-mission') {
            $classes[] = 'humic-vision-template';
        } elseif ($virtual === 'ipr') {
            $classes[] = 'humic-ipr-template';
        } elseif ($virtual === 'research') {
            $classes[] = 'humic-research-template';
        }
        if (is_post_type_archive(HUMIC_MEMBER_CPT)) {
            $classes[] = 'humic-members-template';
        }
        if (is_post_type_archive(HUMIC_MEDIA_CPT)) {
            $classes[] = 'humic-media-template';
        }
        if ($virtual || is_post_type_archive(HUMIC_MEMBER_CPT)
            || is_post_type_archive(HUMIC_MEDIA_CPT)) {
            $classes[] = 'humic-custom-layout';
        }
        return $classes;
    }

    /* ── Template redirect ── */

    add_action('template_redirect', 'humic_pages_template_redirect');
    function humic_pages_template_redirect() {
        if (!function_exists('humic_render_page_shell')) {
            return;
        }

        $virtual = get_query_var('humic_virtual_page');
        if ($virtual === 'vision-mission') {
            ob_start();
            humic_pages_render_vision_mission();
            humic_render_page_shell(ob_get_clean(), 'humic-vision-template', 'vision-mission');
            exit;
        }
        if ($virtual === 'research') {
            ob_start();
            humic_pages_render_research();
            humic_render_page_shell(ob_get_clean(), 'humic-research-template', 'research-areas');
            exit;
        }
        if ($virtual === 'ipr') {
            ob_start();
            humic_pages_render_ipr();
            humic_render_page_shell(ob_get_clean(), 'humic-ipr-template', 'ipr');
            exit;
        }

        if (is_post_type_archive(HUMIC_MEMBER_CPT)) {
            ob_start();
            humic_pages_render_members();
            humic_render_page_shell(ob_get_clean(), 'humic-members-template', 'members');
            exit;
        }

        if (is_post_type_archive(HUMIC_MEDIA_CPT)) {
            ob_start();
            humic_pages_render_media();
            humic_render_page_shell(ob_get_clean(), 'humic-media-template', 'media');
            exit;
        }
    }

    /* ── Official member roster (source: humic.telkomuniversity.ac.id/people/) ── */

    function humic_pages_get_official_roster() {
        $head_bio = '<p>Human Centric Engineering (HUMIC) is one of the research centers at Telkom University, officially founded in February 2020. Dr. Satria Mandala leads research in computing, informatics, electronics, robotics, mechanical, and biomedical engineering to improve human health and well-being.</p>';

        return array(
            'head' => array(
                'title'    => 'Satria Mandala, S.T., M.Sc., Ph.D.',
                'position' => 'Head of Research Center',
                'content'  => $head_bio,
                'order'    => 1,
                'social'   => array(
                    'linkedin' => 'https://id.linkedin.com/company/humic-engineering',
                    'scholar'  => '',
                ),
                'legacy_titles' => array(
                    'Taufik Aditiyanto, S.T., M.T., Ph.D.',
                ),
            ),
            'officers' => array(
                array('title' => 'Dr. Putu Harry Gunawan, S.Si., M.Si., M.Sc.', 'position' => 'Vice Director of Research Center', 'order' => 2, 'legacy' => 'Officer Staff 1'),
                array('title' => 'Amila Nafila Vidyana, S.I.Kom', 'position' => 'Staff', 'order' => 3, 'legacy' => 'Officer Staff 2'),
                array('title' => 'Muhammad Rakha, S.T', 'position' => 'Staff', 'order' => 4, 'legacy' => 'Officer Staff 3'),
                array('title' => 'Lastrimurni Dongoran, S.Kom', 'position' => 'Staff', 'order' => 5, 'legacy' => 'Officer Staff 4'),
            ),
            'researchers' => array(
                'Prof. Dr. Adiwijaya, S.Si., M.Si.',
                'Prof. Dr. Tri Arief Sardjono, S.T., M.T.',
                'Dr. Tjokorda Agung Budi Wirayuda, S.T., M.T.',
                'Niken Dwi Wahyu Cahyani, S.T., M.Kom, Ph.D.',
                'Dandi Yunidar, S.Sn., M.Ds., Ph.D',
                'Dr. Ema Rachmawati, S.T., M.T.',
                'Irma Palupi, S.Si, M.Si, Ph.D.',
                'Dr. Eng. Ir. Wikky Fawwaz Al Maki, S.T., M.Eng.',
                'Dr. Hilda Fahlena, S.Si., M.Si.',
                'Maulida Mazaya, Ph.D',
                'Siska Noviaristanti, S.Si., M.T., Ph.D',
                'Dr. Meta Kallista S.Si., M.Si.',
                'Dr. Helisyah Nur Fadhilah, S.Si., M.Mat.',
                'Dr. Anung Asmoro, S.T., M.T.',
                'Rajiv Dharma Mangruwa, D.B.A',
                'Dr. Budiman Putra Asma\'ur Rohman, S.T., M.T',
                'Dr. Risman Adnan Mattotorang, S.Si., M.Si',
                'Pima Hani Safitri, S.Kom., M.Kom.',
                'Rifdatun Ni\'mah, S.Si., M.Si.',
                'Aniq Atiqi Rohmawati, S.Si., M.Si',
                'Mira Rahayu, S.T., M.T.',
                'Bambang Pudjoatmodjo, S.Si., M.T',
                'Dra. Indwiarti, M.Si',
                'I Wayan Palton Anuwiksa, S.Si., M.Si',
                'Togar Mulya Raja, S.Ds., M.Ds.',
                'Eko Darwiyanto, S.T., M.T',
                'Yunita Nugrahaini Safrudin, S.T., M.T.',
                'Annisa Aditsania, S.Si., M.Si',
                'Widi Astuti, S.T., M.Kom',
                'dr. Ellyana Perwitasari, SpPK, MMRS',
                'Sheila Amalia Salma, S.T., M.T.',
                'Drs. Jondri, M.Si.',
                'Ilham Roni Yansyah, S.Kom., M.Kom',
                'Syifa Nurgaida Yutia, S.Tr., M.T',
                'Nurul Ilmi, S.Kom., M.T',
                'Deny Haryadi, S.Kom., M.Kom',
                'Sasmi Hidayatul Yulianing Tyas, S.Kom., M.Kom',
                'Deki Satria, S.T., M.Kom',
                'Sevierda Raniprima, S.T., M.T',
            ),
        );
    }

    function humic_pages_member_letter($title) {
        $title = trim(wp_strip_all_tags($title));
        if ($title === '') {
            return '#';
        }
        if (preg_match('/^dr\.?\s/i', $title)) {
            return 'D';
        }
        if (preg_match('/^prof\.?\s/i', $title)) {
            return 'P';
        }
        if (preg_match('/^dra\.?\s/i', $title)) {
            return 'D';
        }
        if (preg_match('/^drs\.?\s/i', $title)) {
            return 'D';
        }
        return strtoupper(substr($title, 0, 1));
    }

    function humic_pages_upsert_member($item) {
        $id = humic_pages_find_by_title(HUMIC_MEMBER_CPT, $item['title']);
        if (!$id && !empty($item['legacy_titles'])) {
            foreach ((array) $item['legacy_titles'] as $legacy) {
                $id = humic_pages_find_by_title(HUMIC_MEMBER_CPT, $legacy);
                if ($id) {
                    break;
                }
            }
        }
        if (!$id && !empty($item['legacy'])) {
            $id = humic_pages_find_by_title(HUMIC_MEMBER_CPT, $item['legacy']);
        }

        $post_data = array(
            'post_type'    => HUMIC_MEMBER_CPT,
            'post_title'   => $item['title'],
            'post_content' => $item['content'] ?? '',
            'post_status'  => 'publish',
            'menu_order'   => (int) ($item['order'] ?? 0),
        );

        if ($id) {
            $post_data['ID'] = $id;
            $result = wp_update_post($post_data, true);
        } else {
            $result = wp_insert_post($post_data, true);
        }

        if (is_wp_error($result)) {
            return 0;
        }

        $id = (int) $result;
        update_post_meta($id, '_humic_member_role', $item['role']);
        update_post_meta($id, '_humic_member_position', $item['position'] ?? 'Researcher');

        if (!empty($item['social']) && is_array($item['social'])) {
            foreach ($item['social'] as $key => $url) {
                update_post_meta($id, '_humic_member_' . sanitize_key($key), esc_url_raw($url));
            }
        }

        return $id;
    }

    function humic_pages_sync_official_roster() {
        $roster = humic_pages_get_official_roster();

        $head = $roster['head'];
        humic_pages_upsert_member(array(
            'title'         => $head['title'],
            'role'          => 'head',
            'position'      => $head['position'],
            'content'       => $head['content'],
            'order'         => $head['order'],
            'social'        => $head['social'],
            'legacy_titles' => $head['legacy_titles'],
        ));

        foreach ($roster['officers'] as $officer) {
            humic_pages_upsert_member(array(
                'title'    => $officer['title'],
                'role'     => 'officer',
                'position' => $officer['position'],
                'order'    => $officer['order'],
                'legacy'   => $officer['legacy'] ?? '',
            ));
        }

        foreach ($roster['researchers'] as $index => $name) {
            $order = 10 + $index + 1;
            humic_pages_upsert_member(array(
                'title'    => $name,
                'role'     => 'researcher',
                'position' => 'Researcher',
                'order'    => $order,
                'legacy'   => 'Researcher ' . ($index + 1),
            ));
        }
    }

    /* ── Render: Vision & Mission ── */

    function humic_pages_render_vision_mission() {
        $home = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
        humic_render_page_hdr('HUMIC Engineering', 'Vision & Mission', $home, 'Back to Home');
        ?>
        <div class="humic-vision-wrap">
            <section class="humic-vision-block">
                <h2 class="humic-section-label"><span class="humic-section-bar"></span> Vision</h2>
                <p class="humic-vision-text"><?php echo esc_html(humic_get_vision_text()); ?></p>
            </section>
            <section class="humic-vision-block">
                <h2 class="humic-section-label"><span class="humic-section-bar"></span> Mission</h2>
                <ol class="humic-mission-list humic-mission-list-page">
                <?php foreach (humic_get_mission_items() as $item) : ?>
                    <li><?php echo esc_html($item); ?></li>
                <?php endforeach; ?>
                </ol>
            </section>
            <?php
            $timeline_img = humic_get_timeline_image_url();
            if ($timeline_img) :
            ?>
            <section class="humic-timeline-section">
                <figure class="humic-timeline-figure">
                    <img src="<?php echo esc_url($timeline_img); ?>" alt="HUMIC Engineering timeline" class="humic-timeline-image" />
                </figure>
            </section>
            <?php endif; ?>
        </div>
        <?php
    }

    /* ── Render: Members ── */

    function humic_pages_get_members_by_role($role) {
        return new WP_Query(array(
            'post_type'      => HUMIC_MEMBER_CPT,
            'posts_per_page' => -1,
            'meta_key'       => '_humic_member_role',
            'meta_value'     => $role,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ));
    }

    function humic_pages_render_member_social($post_id) {
        $links = array(
            'linkedin' => array('icon' => 'fa-brands fa-linkedin-in', 'label' => 'LinkedIn'),
            'twitter'  => array('icon' => 'fa-brands fa-x-twitter', 'label' => 'Twitter'),
            'facebook' => array('icon' => 'fa-brands fa-facebook-f', 'label' => 'Facebook'),
            'scholar'  => array('icon' => 'fa-solid fa-graduation-cap', 'label' => 'Google Scholar'),
        );
        $has_any = false;
        foreach ($links as $key => $data) {
            if (get_post_meta($post_id, '_humic_member_' . $key, true)) {
                $has_any = true;
                break;
            }
        }
        if (!$has_any) {
            return;
        }
        ?>
        <div class="humic-head-social">
        <?php foreach ($links as $key => $data) :
            $url = get_post_meta($post_id, '_humic_member_' . $key, true);
            if (!$url) {
                continue;
            }
        ?>
            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer"
            aria-label="<?php echo esc_attr($data['label']); ?>">
                <i class="<?php echo esc_attr($data['icon']); ?>" aria-hidden="true"></i>
            </a>
        <?php endforeach; ?>
        </div>
        <?php
    }

    function humic_pages_render_member_card($post_id, $large = false) {
        $position = get_post_meta($post_id, '_humic_member_position', true);
        $role     = get_post_meta($post_id, '_humic_member_role', true);
        $img      = get_the_post_thumbnail_url($post_id, $large ? 'large' : 'medium');
    $label    = $position ?: ($role === 'researcher' ? 'Researcher' : ucfirst($role));
        $letter   = $role === 'researcher' ? humic_pages_member_letter(get_the_title($post_id)) : '';
        ?>
        <article class="humic-member-card<?php echo $large ? ' humic-member-card-lg' : ''; ?>"<?php echo $letter ? ' data-member-letter="' . esc_attr($letter) . '"' : ''; ?>>
            <?php if ($img) : ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($post_id)); ?>" class="humic-member-photo" />
            <?php else : ?>
            <div class="humic-member-photo humic-member-photo-placeholder" aria-hidden="true"></div>
            <?php endif; ?>
            <div class="humic-member-info">
                <?php if ($label) : ?>
                <span class="humic-member-role"><?php echo esc_html($label); ?></span>
                <?php endif; ?>
                <h3 class="humic-member-name"><?php echo esc_html(get_the_title($post_id)); ?></h3>
                <?php humic_pages_render_member_social($post_id); ?>
            </div>
        </article>
        <?php
    }

    function humic_pages_render_members() {
        $home = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
        humic_render_page_hdr('About HUMIC', 'People', $home, 'Back to Home');

        $head_q = humic_pages_get_members_by_role('head');
        $head_count = (int) $head_q->post_count;
        if ($head_q->have_posts()) {
            $head_q->the_post();
            $head_id = get_the_ID();
            ?>
            <section class="humic-members-section">
                <h2 class="humic-section-label humic-section-label-red">Head of Research Center</h2>
                <div class="humic-head-grid">
                    <div class="humic-head-photo-col">
                        <?php humic_pages_render_member_card($head_id, true); ?>
                    </div>
                    <div class="humic-head-bio">
                        <?php the_content(); ?>
                    </div>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        }

        $officer_q = humic_pages_get_members_by_role('officer');
        $officer_count = (int) $officer_q->post_count;
        if ($officer_q->have_posts()) {
            ?>
            <section class="humic-members-section">
                <h2 class="humic-section-label humic-section-label-red">Officer</h2>
                <p class="humic-section-desc">HUMIC Engineering research center staff who provide services to the research center, such as administration, finance, and other supporting roles.</p>
                <div class="humic-members-grid humic-members-grid-officer">
                <?php
                while ($officer_q->have_posts()) {
                    $officer_q->the_post();
                    humic_pages_render_member_card(get_the_ID());
                }
                ?>
                </div>
            </section>
            <?php
            wp_reset_postdata();
        }

        $researcher_q = humic_pages_get_members_by_role('researcher');
        $researcher_count = (int) $researcher_q->post_count;
        if ($researcher_q->have_posts()) {
            $letters = array();
            foreach ($researcher_q->posts as $post) {
                $letters[] = humic_pages_member_letter($post->post_title);
            }
            $letters = array_values(array_unique($letters));
            sort($letters);
            $researcher_q->rewind_posts();
            ?>
            <section class="humic-members-section">
                <h2 class="humic-section-label humic-section-label-red">Researcher</h2>
                <p class="humic-section-desc">Members of HUMIC Engineering research center who are involved in research activities and product development.</p>
                <nav class="humic-members-filter" aria-label="Filter researchers by name">
                    <button type="button" class="humic-members-filter-btn active" data-filter="all">All</button>
                    <?php foreach ($letters as $letter) : ?>
                    <button type="button" class="humic-members-filter-btn" data-filter="<?php echo esc_attr($letter); ?>"><?php echo esc_html($letter); ?></button>
                    <?php endforeach; ?>
                </nav>
                <div class="humic-members-grid humic-members-grid-researcher" id="humic-researcher-grid">
                <?php
                while ($researcher_q->have_posts()) {
                    $researcher_q->the_post();
                    humic_pages_render_member_card(get_the_ID());
                }
                ?>
                </div>
                <p class="humic-members-filter-empty" id="humic-members-filter-empty" hidden>No researchers found for this letter.</p>
            </section>
            <?php
            wp_reset_postdata();
        }

        if (!$head_count && !$officer_count && !$researcher_count) {
            echo '<p class="news-empty">No members yet. Add items under <strong>HUMIC Members</strong> in WordPress admin.</p>';
        }
    }

    /* ── Render: IPR ── */

    function humic_pages_render_ipr_section($type, $title) {
        $q = new WP_Query(array(
            'post_type'      => HUMIC_IPR_CPT,
            'posts_per_page' => -1,
            'meta_key'       => '_humic_ipr_type',
            'meta_value'     => $type,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
        ));
        ?>
        <section class="humic-ipr-section">
            <h2 class="humic-ipr-heading"><?php echo esc_html($title); ?></h2>
            <?php if ($q->have_posts()) : ?>
            <ul class="humic-ipr-list">
            <?php
            while ($q->have_posts()) {
                $q->the_post();
                echo '<li>' . esc_html(get_the_title()) . '</li>';
                if (get_the_content()) {
                    echo '<li class="humic-ipr-list-detail">' . wp_kses_post(wpautop(get_the_content())) . '</li>';
                }
            }
            ?>
            </ul>
            <?php else : ?>
            <p class="humic-ipr-empty">No items listed yet. Add entries under <strong>HUMIC IPR</strong> in WordPress admin.</p>
            <?php endif; wp_reset_postdata(); ?>
        </section>
        <?php
    }

    function humic_pages_render_ipr() {
        $home = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
        humic_render_page_hdr('Our Activity', 'Intellectual Property Rights', $home, 'Back to Home');
        ?>
        <div class="humic-ipr-intro">
            <p>Form kelayakan komersialisasi:
                <a href="<?php echo esc_url(HUMIC_IPR_DOC_URL); ?>" target="_blank" rel="noopener noreferrer" class="link-arr">
                    download here <i class="fa-solid fa-arrow-up-right"></i>
                </a>
            </p>
            <p>Berikut adalah daftar Intellectual Property Rights (IPR) Member HUMIC:</p>
        </div>
        <?php
        humic_pages_render_ipr_section('paten', 'Paten');
        humic_pages_render_ipr_section('paten_sederhana', 'Paten Sederhana');
        humic_pages_render_ipr_section('hki', 'HKI');
    }

    /* ── Render: Media ── */

    function humic_pages_youtube_embed($url) {
        if (!$url) {
            return '';
        }
        $video_id = '';
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $url, $m)) {
            $video_id = $m[1];
        } elseif (preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            $video_id = $url;
        }
        if (!$video_id) {
            return '';
        }
        return 'https://www.youtube.com/embed/' . $video_id;
    }

    function humic_pages_render_media() {
        $home = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
        humic_render_page_hdr('Our Activity', 'HUMIC in Media', $home, 'Back to Home');
        ?>
        <p class="humic-section-desc">Videos, articles, and coverage about HUMIC Engineering published on social media and news outlets.</p>
        <?php
        if (!have_posts()) {
            echo '<p class="news-empty">No media items yet. Add under <strong>HUMIC in Media</strong> in WordPress admin.</p>';
            return;
        }
        echo '<div class="humic-media-grid">';
        while (have_posts()) {
            the_post();
            $embed = humic_pages_youtube_embed(get_post_meta(get_the_ID(), '_humic_media_video_url', true));
            ?>
            <article class="humic-media-card">
                <h2 class="humic-media-title"><?php the_title(); ?></h2>
                <?php if ($embed) : ?>
                <div class="humic-media-embed">
                    <iframe src="<?php echo esc_url($embed); ?>" title="<?php echo esc_attr(get_the_title()); ?>"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen loading="lazy"></iframe>
                </div>
                <?php endif; ?>
                <?php if (get_the_content()) : ?>
                <div class="humic-media-desc"><?php the_content(); ?></div>
                <?php endif; ?>
            </article>
            <?php
        }
        echo '</div>';
    }

    /* ── Seed content ── */

    add_action('admin_init', function () {
        if (get_option('humic_pages_seeded_v1') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        humic_pages_seed_content();
        update_option('humic_pages_seeded_v1', '1');
    }, 35);

    add_action('admin_init', function () {
        if (get_option('humic_pages_seeded_v2') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        humic_pages_seed_extra_researchers();
        update_option('humic_pages_seeded_v2', '1');
    }, 36);

    add_action('admin_init', function () {
        if (get_option('humic_pages_seeded_v3') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        humic_pages_sync_official_roster();
        humic_pages_seed_media_content();
        update_option('humic_pages_seeded_v3', '1');
    }, 37);

    function humic_pages_seed_media_content() {
        $items = array(
            array(
                'title' => 'Liputan CNN — Jam Pendeteksi Gejala Stroke',
                'video' => 'https://www.youtube.com/watch?v=UCYHvzg7lY2WuwyKlj8jRNAw',
                'order' => 1,
            ),
            array(
                'title' => 'Liputan Tempo.co — Arrhythmia Deteksi Jantung Mandiri',
                'video' => 'https://www.youtube.com/channel/UCYHvzg7lY2WuwyKlj8jRNAw',
                'order' => 2,
            ),
            array(
                'title' => 'Wawancara Satria Mandala, Ph.D. dengan Channel YouTube Tel-U',
                'video' => 'https://www.youtube.com/channel/UCYHvzg7lY2WuwyKlj8jRNAw',
                'order' => 3,
            ),
        );
        foreach ($items as $item) {
            $id = humic_pages_find_by_title(HUMIC_MEDIA_CPT, $item['title']);
            if (!$id) {
                $id = wp_insert_post(array(
                    'post_type'   => HUMIC_MEDIA_CPT,
                    'post_title'  => $item['title'],
                    'post_status' => 'publish',
                    'menu_order'  => $item['order'],
                ), true);
            }
            if (!is_wp_error($id) && $id) {
                update_post_meta((int) $id, '_humic_media_video_url', esc_url_raw($item['video']));
            }
        }
    }

    function humic_pages_seed_extra_researchers() {
        for ($i = 11; $i <= 39; $i++) {
            $title = 'Researcher ' . $i;
            if (humic_pages_find_by_title(HUMIC_MEMBER_CPT, $title)) {
                continue;
            }
            $id = wp_insert_post(array(
                'post_type'   => HUMIC_MEMBER_CPT,
                'post_title'  => $title,
                'post_status' => 'publish',
                'menu_order'  => 10 + $i,
            ), true);
            if (!is_wp_error($id)) {
                update_post_meta($id, '_humic_member_role', 'researcher');
                update_post_meta($id, '_humic_member_position', 'Researcher');
            }
        }
    }

    function humic_pages_seed_content() {
        humic_pages_sync_official_roster();
        humic_pages_seed_media_content();
    }

    function humic_pages_find_by_title($post_type, $title) {
        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_title = %s AND post_status != 'trash' LIMIT 1",
            $post_type,
            $title
        ));
        return $id ? (int) $id : 0;
    }
