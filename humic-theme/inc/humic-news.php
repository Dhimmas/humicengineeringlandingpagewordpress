    <?php
    /**
     * HUMIC Engineering Website Architecture
     * ----------------------------------------------------
     * Developed by : Dhimmas
     * Year         : 2026
     * Description  : Custom WordPress Engine, CPT, and UI System
     * Property of  : HUMIC Research Center - Telkom University
     * 
     * Unauthorized copying or redistribution is prohibited.
     */

    if (!defined('ABSPATH')) {
        exit;
    }

    define('HUMIC_NEWS_CPT', 'humic_news');
    define('HUMIC_ADMIN_MENU', 'humic-engineering');
    define('HUMIC_ASSET_VERSION', '1.4.3');

    add_action('admin_menu', 'humic_register_admin_menu', 5);
    function humic_register_admin_menu() {
        add_menu_page(
            'HUMIC Engineering',
            'HUMIC Engineering',
            'edit_posts',
            HUMIC_ADMIN_MENU,
            'humic_admin_menu_home',
            'dashicons-building',
            26
        );
    }

    function humic_admin_menu_home() {
        wp_safe_redirect(admin_url('edit.php?post_type=' . HUMIC_NEWS_CPT));
        exit;
    }

    add_action('admin_menu', 'humic_admin_menu_cleanup', 999);
    function humic_admin_menu_cleanup() {
        remove_submenu_page(HUMIC_ADMIN_MENU, HUMIC_ADMIN_MENU);
        remove_submenu_page(HUMIC_ADMIN_MENU, 'post-new.php?post_type=' . HUMIC_NEWS_CPT);
    }

    add_filter('body_class', 'humic_custom_layout_body_class');
    function humic_custom_layout_body_class($classes) {
        if (humic_should_suppress_theme_shell()) {
            $classes[] = 'humic-custom-layout';
        }
        return $classes;
    }

    function humic_should_suppress_theme_shell() {
        if (is_front_page()) {
            return true;
        }
        if (is_post_type_archive(HUMIC_NEWS_CPT) || is_singular(HUMIC_NEWS_CPT)) {
            return true;
        }
        if (is_post_type_archive('humic_event') || is_singular('humic_event')) {
            return true;
        }
        if (get_query_var('humic_virtual_page')) {
            return true;
        }
        if (is_post_type_archive('humic_member') || is_post_type_archive('humic_media')) {
            return true;
        }
        if (is_singular('page')) {
            $post = get_queried_object();
            if ($post && is_string($post->post_content)) {
                if (strpos($post->post_content, 'site-header') !== false
                    || strpos($post->post_content, '[humic_') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    add_filter('render_block', 'humic_suppress_block_theme_shell', 10, 2);
    function humic_suppress_block_theme_shell($block_content, $block) {
        if (!humic_should_suppress_theme_shell()) {
            return $block_content;
        }
        if (($block['blockName'] ?? '') !== 'core/template-part') {
            return $block_content;
        }
        $slug = $block['attrs']['slug'] ?? '';
        if ($slug === 'header' || $slug === 'footer') {
            return '';
        }
        return $block_content;
    }

    function humic_get_mount_header_js() {
        return "(function(){function insertRef(){var bar=document.getElementById('wpadminbar'),node=document.body.firstChild;if(bar&&bar.parentNode===document.body){node=bar.nextSibling;}while(node&&node.nodeType!==1){node=node.nextSibling;}if(node&&node.id==='wpadminbar'){node=node.nextSibling;while(node&&node.nodeType!==1){node=node.nextSibling;}}return node;}function moveHeader(){var h=document.getElementById('site-header');var b=document.body;if(!h||!b||h.parentElement===b)return;var p=h.parentElement;while(p&&p!==b){if(p.classList&&(p.classList.contains('elementor-section')||p.classList.contains('e-con')||p.classList.contains('elementor-column')||p.classList.contains('elementor-widget')||p.classList.contains('elementor-widget-container')||p.classList.contains('elementor-widget-shortcode')||p.classList.contains('elementor-widget-html'))){p.setAttribute('data-humic-header-slot','1');}p=p.parentElement;}b.insertBefore(h,insertRef());h.dataset.humicMounted='1';b.classList.add('humic-header-mounted');}function syncHeader(){if(typeof window.humicSyncHeader==='function'){window.humicSyncHeader();return true;}return false;}moveHeader();if(!syncHeader()){document.addEventListener('DOMContentLoaded',function(){moveHeader();syncHeader();});window.addEventListener('load',function(){moveHeader();syncHeader();});}else{syncHeader();}})();";
    }

    function humic_get_header_sync_js() {
        return "(function(){function sync(){if(typeof window.humicSyncHeader==='function'){window.humicSyncHeader();return true;}return false;}if(!sync()){document.addEventListener('DOMContentLoaded',sync);window.addEventListener('load',sync);}})();";
    }

    function humic_get_critical_header_css() {
        return '#site-header:not(.humic-header-fixed){display:flex!important;flex-direction:column!important;align-items:stretch!important;width:100%!important;max-width:100%!important;visibility:visible!important;opacity:1!important;background:#fff!important;overflow:visible!important;position:sticky!important;top:var(--humic-sticky-top,0)!important;gap:0!important;font-size:0!important;line-height:0!important;margin:0!important;padding:0!important;border:0!important;z-index:9999999!important}'
            . '#site-header.humic-header-fixed{display:flex!important;flex-direction:column!important;align-items:stretch!important;width:100%!important;max-width:100%!important;visibility:visible!important;opacity:1!important;background:#fff!important;overflow:visible!important;gap:0!important;font-size:0!important;line-height:0!important;margin:0!important;padding:0!important;border:0!important;z-index:9999999!important}'
            . '#site-header>*{font-size:14px!important;line-height:normal!important}'
            . 'body>#site-header,body.humic-header-mounted>#site-header{display:flex!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .topbar{position:relative!important;top:auto!important;left:auto!important;right:auto!important;width:100%!important;flex:0 0 auto!important;margin:0!important;padding:0!important;box-sizing:border-box!important;line-height:normal!important;font-size:14px!important}'
            . '#site-header .navbar{position:relative!important;top:auto!important;left:auto!important;right:auto!important;width:100%!important;flex:0 0 auto!important;margin:0!important;padding:0!important;background:#fff!important;box-sizing:border-box!important;display:block!important;visibility:visible!important;opacity:1!important;line-height:normal!important;font-size:14px!important}'
            . '#site-header .container.navbar-inner,#site-header .navbar-inner{display:flex!important;flex-direction:row!important;flex-wrap:nowrap!important;align-items:center!important;justify-content:space-between!important;width:100%!important;max-width:1200px!important;margin-left:auto!important;margin-right:auto!important;box-sizing:border-box!important;position:relative!important;gap:12px!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .container.topbar-inner{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:space-between!important;width:100%!important;max-width:1200px!important;margin-left:auto!important;margin-right:auto!important;box-sizing:border-box!important;padding:0 clamp(20px,5vw,32px)!important}'
            . '#site-header .topbar-left,#site-header .topbar-social{display:flex!important;align-items:center!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .logo,#site-header .logo a{position:static!important;float:none!important;flex:0 0 auto!important;order:0!important;margin:0!important;transform:none!important;display:flex!important;align-items:center!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .logo img{display:block!important;position:static!important;transform:none!important;height:40px!important;width:auto!important;max-width:200px!important;object-fit:contain!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .nav-links{flex:1 1 auto!important;order:1!important;margin:0!important;padding:0!important;list-style:none!important}'
            . '#site-header .desktop-cta{flex:0 0 auto!important;order:2!important;margin:0!important}'
            . '#site-header .burger{flex:0 0 auto!important;order:3!important;position:relative!important;margin:0!important;float:none!important;visibility:visible!important;opacity:1!important}'
            . '.elementor-element.elementor-hidden-mobile #site-header,.elementor-hidden-mobile #site-header,.e-con.elementor-hidden-mobile #site-header{display:flex!important;visibility:visible!important;opacity:1!important;height:auto!important;max-height:none!important}'
            . '@media (min-width:769px){'
            . 'body #site-header,body.humic-custom-layout #site-header,body.humic-news-template #site-header,body.humic-events-template #site-header,body[class*="humic-"][class*="-template"] #site-header{min-height:106px!important}'
            . 'body #site-header .topbar,#site-header .topbar,body[class*="humic-"][class*="-template"] #site-header .topbar{display:flex!important;visibility:visible!important;opacity:1!important;background:#CC0000!important;height:36px!important;min-height:36px!important;max-height:none!important;align-items:center!important;overflow:visible!important}'
            . '#site-header .topbar-inner,#site-header .topbar-left,#site-header .topbar-social{display:flex!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .topbar-left a,#site-header .topbar-left span,#site-header .topbar-left i,#site-header .topbar-social a,#site-header .topbar-social a i{display:inline-flex!important;color:#fff!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .nav-links{display:flex!important;visibility:visible!important;opacity:1!important;justify-content:center!important}'
            . '#site-header .desktop-cta{display:inline-flex!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .burger{display:none!important}'
            . 'body.humic-header-pinned>#site-header,body.humic-header-portaled>#site-header,#site-header.humic-header-fixed{position:fixed!important;left:0!important;right:0!important;top:var(--humic-sticky-top,0px)!important;z-index:9999999!important;width:100%!important;max-width:100%!important}'
            . 'body.humic-header-pinned{padding-top:var(--humic-body-offset,106px)!important}'
            . '}'
            . '@media (min-width:769px) and (max-width:782px){body.admin-bar.humic-header-portaled #site-header,body.admin-bar.humic-header-pinned #site-header,body.admin-bar #site-header.humic-header-fixed,body.admin-bar.humic-header-portaled>#site-header,body.admin-bar.humic-header-pinned>#site-header,body.admin-bar>#site-header.humic-header-fixed{top:var(--humic-sticky-top,0px)!important}}'
            . '@media (max-width:768px){'
            . 'body>#site-header:not(.humic-header-fixed),#site-header:not(.humic-header-fixed),body.humic-header-mounted>#site-header:not(.humic-header-fixed),body.humic-news-template #site-header:not(.humic-header-fixed),body.humic-events-template #site-header:not(.humic-header-fixed),body[class*="humic-"][class*="-template"] #site-header:not(.humic-header-fixed),body.humic-header-portaled>#site-header:not(.humic-header-fixed),body.humic-header-pinned>#site-header:not(.humic-header-fixed){display:flex!important;position:sticky!important;top:var(--humic-sticky-top,0)!important;left:auto!important;right:auto!important;min-height:96px!important;z-index:9999999!important;visibility:visible!important;opacity:1!important}'
            . 'body.humic-header-pinned,body.humic-header-portaled{padding-top:var(--humic-body-offset,96px)!important}'
            . '#site-header.humic-header-fixed,body.humic-header-pinned>#site-header.humic-header-fixed,body.humic-header-portaled>#site-header.humic-header-fixed,body.humic-header-portaled #site-header.humic-header-fixed{position:fixed!important;left:0!important;right:0!important;top:var(--humic-sticky-top,0px)!important;transform:none!important;z-index:9999999!important}'
            . '#site-header .topbar{display:flex!important;flex-direction:row!important;height:36px!important;min-height:36px!important;max-height:36px!important;padding:0!important;margin:0!important;border:0!important;overflow:visible!important;visibility:visible!important;gap:0!important;background:#CC0000!important}'
            . '#site-header .topbar-inner{flex-direction:row!important;align-items:center!important;justify-content:flex-start!important;gap:8px!important}'
            . '#site-header .topbar-left{flex-wrap:nowrap!important;justify-content:flex-start!important;text-align:left!important;line-height:1.4!important;gap:10px!important}'
            . '#site-header .topbar-left .topbar-sep{display:none!important}'
            . '#site-header .navbar{display:block!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .navbar-inner,#site-header .container.navbar-inner{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:space-between!important;min-height:60px!important;max-width:100%!important;padding-left:clamp(20px,5vw,32px)!important;padding-right:clamp(20px,5vw,32px)!important}'
            . '#site-header .logo,#site-header .logo a{display:flex!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .logo img{display:block!important;visibility:visible!important;opacity:1!important;height:34px!important;width:auto!important;max-width:180px!important}'
            . '#site-header .nav-links,#site-header .desktop-cta{display:none!important}'
            . '#site-header .burger,#site-header .navbar-inner .burger{display:flex!important;width:44px!important;height:44px!important;align-items:center!important;justify-content:center!important;visibility:visible!important;opacity:1!important}'
            . '#site-header .burger span{display:block!important;visibility:visible!important;opacity:1!important;background:#374151!important}'
            . 'body.admin-bar #site-header:not(.humic-header-fixed){top:auto!important}'
            . 'body.humic-layout-mobile:not(.humic-header-pinned),body.humic-header-static-mobile:not(.humic-header-pinned),body.humic-header-mounted:not(.humic-header-pinned),body.humic-news-template:not(.humic-header-pinned),body.humic-events-template:not(.humic-header-pinned){padding-top:0!important}'
            . 'body.humic-header-mounted [data-humic-header-slot]:not(:has(main)):not(:has(footer)):not(:has(.footer)),body.humic-header-portaled [data-humic-header-slot]:not(:has(main)):not(:has(footer)):not(:has(.footer)),#humic-header-spacer{display:none!important;height:0!important;min-height:0!important;overflow:hidden!important;margin:0!important;padding:0!important}'
            . '#mobile-menu{top:var(--humic-mobile-menu-top,96px)!important}'
            . '}';
    }

    add_action('wp_footer', 'humic_footer_mount_header', 1);
    function humic_footer_mount_header() {
        if (!humic_should_load_front_assets()) {
            return;
        }
        echo '<script id="humic-mount-header">' . humic_get_mount_header_js() . '</script>';
    }

    add_action('wp_head', 'humic_early_theme_shell_hide', 1);
    function humic_early_theme_shell_hide() {
        if (!humic_should_suppress_theme_shell()) {
            return;
        }
        echo '<style id="humic-theme-shell-hide">'
            . 'html,html.humic-custom-layout{margin-top:0!important;padding-top:0!important;border-top:0!important}'
            . 'body:has(#site-header){margin-top:0!important;border-top:0!important}'
            . 'header.wp-block-template-part:not(#site-header),footer.wp-block-template-part,'
            . '.wp-site-blocks>header:not(#site-header),.wp-site-blocks>footer{display:none!important;height:0!important;overflow:hidden!important;margin:0!important;padding:0!important;visibility:hidden!important}'
            . 'html.humic-custom-layout:not(.wp-toolbar),'
            . 'body.humic-custom-layout:not(.humic-header-pinned),body.humic-news-template:not(.humic-header-pinned),body:has(#site-header):not(.humic-header-pinned){'
            . '--wp--style--root--padding-top:0!important;--wp--style--root--padding-bottom:0;'
            . 'padding-top:0!important;margin-top:0!important;padding-bottom:0!important}'
            . 'body.humic-custom-layout .has-global-padding,body.humic-news-template .has-global-padding,'
            . 'body:has(#site-header) .has-global-padding,'
            . 'body.humic-custom-layout .is-root-container,body.humic-news-template .is-root-container,'
            . 'body:has(#site-header) .is-root-container{padding-top:0!important;margin-top:0!important}'
            . '.wp-site-blocks{gap:0!important;padding-top:0!important;padding-bottom:0!important;margin-top:0!important;margin-bottom:0!important}'
            . 'body.home .wp-block-post-title,body.humic-custom-layout .wp-block-post-title,body:has(#site-header) .wp-block-post-title,body:has(#site-header) .entry-header{display:none!important;height:0!important;margin:0!important;padding:0!important;overflow:hidden!important;visibility:hidden!important}'
            . humic_get_critical_header_css()
            . '#site-header .topbar,#site-header .navbar,#site-header .navbar-inner,#site-header .container.navbar-inner,#site-header .logo,#site-header .logo img,#site-header .nav-links,#site-header .burger{visibility:visible!important;opacity:1!important}'
            . 'body:not(.humic-header-portaled) #humic-header-spacer{display:none!important;height:0!important;margin:0!important;padding:0!important}'
            . 'body.humic-header-portaled #humic-header-spacer{display:none!important;height:0!important;margin:0!important;padding:0!important}'
            . 'body.humic-header-portaled [data-humic-header-slot]:not(:has(main)):not(:has(footer)):not(:has(.footer)),body.humic-header-portaled #humic-header-spacer{display:none!important;height:0!important;min-height:0!important;margin:0!important;padding:0!important;overflow:hidden!important}'
            . 'body.humic-header-pinned{padding-top:var(--humic-body-offset,106px)!important}'
            . '@media (min-width:769px){body.humic-header-pinned{padding-top:var(--humic-body-offset,106px)!important}}'
            . 'body.humic-news-template .news,body.humic-events-template .news{padding-top:clamp(48px,6vw,72px)!important;padding-bottom:clamp(64px,8vw,96px)!important}'
            . '</style>';
    }

    add_action('wp_head', 'humic_early_top_gap_class', 0);
    function humic_early_top_gap_class() {
        if (!humic_should_suppress_theme_shell()) {
            return;
        }
        echo '<script>(function(){document.documentElement.classList.add("humic-custom-layout");var m=window.matchMedia("(max-width:768px)");function adminOffset(){var bar=document.getElementById("wpadminbar");if(!bar)return 0;var st=window.getComputedStyle(bar);if(st.display==="none"||st.visibility==="hidden")return 0;var h=bar.offsetHeight||bar.getBoundingClientRect().height;return h>0?Math.ceil(h):(window.matchMedia("(max-width:782px)").matches?46:32);}function forceTopbar(){if(m.matches)return;var tb=document.querySelector("#site-header .topbar");if(!tb)return;tb.style.setProperty("display","flex","important");tb.style.setProperty("height","36px","important");tb.style.setProperty("min-height","36px","important");tb.style.setProperty("background","#CC0000","important");tb.style.setProperty("visibility","visible","important");tb.style.setProperty("opacity","1","important");}function sync(){var off=adminOffset();if(m.matches){document.documentElement.classList.add("humic-layout-mobile");document.documentElement.classList.remove("humic-layout-desktop");document.documentElement.style.setProperty("--humic-sticky-top",off+"px");document.documentElement.style.setProperty("--humic-mobile-menu-top",(off+96)+"px");document.documentElement.style.setProperty("--humic-mobile-menu-max-height","calc(100dvh - "+(off+96)+"px - 8px)");if(document.body){document.body.classList.add("humic-layout-mobile");document.body.classList.remove("humic-layout-desktop");}}else{document.documentElement.classList.add("humic-layout-desktop");document.documentElement.classList.remove("humic-layout-mobile");document.documentElement.style.setProperty("--humic-sticky-top",off+"px");if(document.body){document.body.classList.add("humic-layout-desktop");document.body.classList.remove("humic-layout-mobile");}forceTopbar();}}sync();if(m.addEventListener){m.addEventListener("change",sync);}else if(m.addListener){m.addListener(sync);}document.addEventListener("DOMContentLoaded",function(){sync();forceTopbar();});window.addEventListener("load",forceTopbar);})();</script>';
    }

    add_action('init', 'humic_news_register_cpt');
    function humic_news_register_cpt() {
        register_post_type(HUMIC_NEWS_CPT, array(
            'labels' => array(
                'name'               => 'HUMIC News',
                'singular_name'      => 'News Item',
                'add_new'            => 'Add News',
                'add_new_item'       => 'Add News',
                'edit_item'          => 'Edit News',
                'new_item'           => 'New News',
                'view_item'          => 'View News',
                'search_items'       => 'Search News',
                'not_found'          => 'No news found',
                'not_found_in_trash' => 'No news found in trash',
                'all_items'          => 'HUMIC News',
                'menu_name'          => 'HUMIC News',
            ),
            'public'       => true,
            'has_archive'  => true,
            'rewrite'      => array('slug' => 'news'),
            'show_in_menu' => HUMIC_ADMIN_MENU,
            'supports'     => array('title', 'editor', 'thumbnail', 'excerpt'),
        ));
    }

    add_action('add_meta_boxes', 'humic_news_meta_boxes');
    function humic_news_meta_boxes() {
        add_meta_box(
            'humic_news_details_v2',
            'News Details',
            'humic_news_meta_box_render',
            HUMIC_NEWS_CPT,
            'side',
            'default'
        );
    }

    function humic_news_meta_box_render($post) {
        wp_nonce_field('humic_news_save', 'humic_news_nonce');

        $category = get_post_meta($post->ID, '_humic_news_category', true);
        $date     = get_post_meta($post->ID, '_humic_news_date', true);
        $url      = get_post_meta($post->ID, '_humic_news_url', true);
        $featured = get_post_meta($post->ID, '_humic_news_featured', true);
        ?>
        <?php
        $predefined = array('Conference', 'Research', 'Community', 'Achievement', 'Announcement');
        $is_custom = $category && !in_array($category, $predefined);
        ?>
        <p>
            <label for="humic_news_category_select"><strong>Category label</strong></label><br>
            <select id="humic_news_category_select" class="widefat" style="margin-bottom: 8px;" onchange="
                var input = document.getElementById('humic_news_category');
                if(this.value === '__custom__') {
                    input.style.display = 'block';
                    input.value = '';
                    input.focus();
                } else {
                    input.style.display = 'none';
                    input.value = this.value;
                }
            ">
                <option value="">-- Select Category --</option>
                <?php foreach ($predefined as $opt) : ?>
                    <option value="<?php echo esc_attr($opt); ?>" <?php selected($category, $opt); ?>><?php echo esc_html($opt); ?></option>
                <?php endforeach; ?>
                <option value="__custom__" <?php selected($is_custom, true); ?>>+ Add New Category...</option>
            </select>
            
            <input type="text" id="humic_news_category" name="humic_news_category"
                value="<?php echo esc_attr($category); ?>" class="widefat" 
                style="<?php echo $is_custom ? 'display:block;' : 'display:none;'; ?>"
                placeholder="Enter custom category...">
        </p>
        <p>
            <label for="humic_news_date"><strong>Display date</strong> (e.g. Nov 2024)</label><br>
            <input type="text" id="humic_news_date" name="humic_news_date"
                value="<?php echo esc_attr($date); ?>" class="widefat">
        </p>
        <p>
            <label for="humic_news_url"><strong>Link URL</strong> (Read More destination)</label><br>
            <input type="url" id="humic_news_url" name="humic_news_url"
                value="<?php echo esc_attr($url); ?>" class="widefat"
                placeholder="https://example.com or leave empty to use post permalink">
        </p>
        <p>
            <label>
                <input type="checkbox" name="humic_news_featured" value="1" <?php checked($featured, '1'); ?>>
                <strong>Featured</strong> (large card on landing page)
            </label>
        </p>
        <p class="description">Upload the image via <strong>Featured Image</strong> (sidebar). Write a short summary in <strong>Excerpt</strong>.</p>
        <?php
    }

    add_action('save_post_' . HUMIC_NEWS_CPT, 'humic_news_save_meta');
    function humic_news_save_meta($post_id) {
        if (!isset($_POST['humic_news_nonce']) || !wp_verify_nonce($_POST['humic_news_nonce'], 'humic_news_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, '_humic_news_category', sanitize_text_field($_POST['humic_news_category'] ?? ''));
        update_post_meta($post_id, '_humic_news_date', sanitize_text_field($_POST['humic_news_date'] ?? ''));
        update_post_meta($post_id, '_humic_news_url', esc_url_raw($_POST['humic_news_url'] ?? ''));
        update_post_meta($post_id, '_humic_news_featured', isset($_POST['humic_news_featured']) ? '1' : '0');
    }

    function humic_news_get_link($post_id) {
        $custom = get_post_meta($post_id, '_humic_news_url', true);
        if ($custom) {
            return $custom;
        }
        return get_permalink($post_id);
    }

    function humic_news_is_external($url) {
        $home = home_url();
        return $url && strpos($url, $home) !== 0;
    }

    function humic_news_url_file_exists($url) {
        if (!$url) {
            return false;
        }
        $upload_dir = wp_upload_dir();
        if (strpos($url, $upload_dir['baseurl']) !== 0) {
            return true;
        }
        $path = wp_normalize_path(str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $url));
        return is_readable($path);
    }

    function humic_news_get_catalog_image_for_post($post_id) {
        $title = get_the_title($post_id);
        foreach (humic_news_get_repair_catalog() as $item) {
            if (humic_news_catalog_matches_post($item, $title) && !empty($item['image'])) {
                return $item['image'];
            }
        }
        return '';
    }

    function humic_news_get_catalog_image_url($post_id) {
        $filename = humic_news_get_catalog_image_for_post($post_id);
        if (!$filename) {
            return '';
        }
        $path = humic_news_resolve_upload_path($filename);
        if (!$path) {
            return '';
        }
        $upload_dir = wp_upload_dir();
        return str_replace(wp_normalize_path($upload_dir['basedir']), $upload_dir['baseurl'], wp_normalize_path($path));
    }

    function humic_news_fix_attachment_metadata($attachment_id) {
        if (!$attachment_id) {
            return;
        }

        $file = get_attached_file($attachment_id);
        if (!$file || !is_readable($file)) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $metadata      = wp_get_attachment_metadata($attachment_id);
        $needs_regen   = empty($metadata['sizes']);
        $thumb_dir     = wp_normalize_path(trailingslashit(dirname($file)));

        if (!$needs_regen && !empty($metadata['sizes'])) {
            foreach ($metadata['sizes'] as $size) {
                if (empty($size['file'])) {
                    continue;
                }
                if (!is_readable($thumb_dir . wp_normalize_path($size['file']))) {
                    $needs_regen = true;
                    break;
                }
            }
        }

        if ($needs_regen) {
            $new_meta = wp_generate_attachment_metadata($attachment_id, $file);
            if ($new_meta) {
                wp_update_attachment_metadata($attachment_id, $new_meta);
            }
        }
    }

    function humic_news_get_image($post_id) {
        $thumb_id = get_post_thumbnail_id($post_id);
        if ($thumb_id) {
            humic_news_fix_attachment_metadata($thumb_id);

            foreach (array('large', 'medium_large', 'medium', 'full') as $size) {
                $url = get_the_post_thumbnail_url($post_id, $size);
                if ($url && humic_news_url_file_exists($url)) {
                    return $url;
                }
            }

            $url = wp_get_attachment_url($thumb_id);
            if ($url && humic_news_url_file_exists($url)) {
                return $url;
            }
        }

        return humic_news_get_catalog_image_url($post_id);
    }

    function humic_news_render_card_attrs($url) {
        $attrs = ' href="' . esc_url($url) . '" class="nmore"';
        if (humic_news_is_external($url)) {
            $attrs .= ' target="_blank" rel="noopener noreferrer"';
        }
        return $attrs;
    }

    add_shortcode('humic_news', 'humic_news_shortcode');
    add_shortcode('humic_news_url', 'humic_news_archive_url_shortcode');
    add_shortcode('humic_home_url', 'humic_home_url_shortcode');
    add_shortcode('humic_header', 'humic_header_shortcode');
    add_shortcode('humic_footer', 'humic_footer_shortcode');
    add_shortcode('humic_maps_url', 'humic_maps_url_shortcode');
    add_shortcode('humic_asset', 'humic_asset_shortcode');
    add_shortcode('humic_stats_bar', 'humic_stats_bar_shortcode');

    function humic_asset_shortcode($atts) {
        $atts = shortcode_atts(array('file' => ''), $atts, 'humic_asset');
        if (!$atts['file']) {
            return '';
        }
        return esc_url(humic_news_asset_url($atts['file']));
    }

    function humic_stats_bar_shortcode() {
        $items = function_exists('humic_get_stats_items') ? humic_get_stats_items() : array(
            array('value' => '100+', 'label' => 'Research Papers'),
            array('value' => '30+', 'label' => 'Research Projects'),
            array('value' => '10+', 'label' => 'Patented Products'),
        );
        ob_start();
        echo '<div class="stats-bar" aria-label="Key statistics"><div class="container stats-inner">';
        foreach ($items as $stat) {
            $value = $stat['value'] ?? '';
            echo '<div class="stat-item">';
            echo '<div class="stat-val" data-stat-value="' . esc_attr($value) . '">' . esc_html($value) . '</div>';
            echo '<div class="stat-lbl">' . esc_html($stat['label']) . '</div>';
            echo '</div>';
        }
        echo '</div></div>';
        return ob_get_clean();
    }

    add_action('admin_init', 'humic_news_cleanup_event_posts', 45);
    function humic_news_cleanup_event_posts() {
        if (get_option('humic_news_event_cleanup_v1') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        $posts = get_posts(array(
            'post_type'      => HUMIC_NEWS_CPT,
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => array(
                array(
                    'key'     => '_humic_news_category',
                    'value'   => humic_news_get_excluded_event_categories(),
                    'compare' => 'IN',
                ),
            ),
        ));
        foreach ($posts as $post) {
            wp_trash_post($post->ID);
        }
        update_option('humic_news_event_cleanup_v1', '1');
    }

    function humic_home_url_shortcode() {
        return esc_url(humic_news_get_home_url());
    }

    function humic_news_get_home_url() {
        $front = get_option('page_on_front');
        if ($front) {
            return get_permalink($front);
        }
        return home_url('/');
    }

    function humic_news_archive_url_shortcode() {
        $url = get_post_type_archive_link(HUMIC_NEWS_CPT);
        return $url ? esc_url($url) : home_url('/news/');
    }

    function humic_maps_url() {
        return 'https://www.google.com/maps/place/Gedung+F+Telkom+University/@-6.9768982,107.6310164,17z/data=!3m1!4b1!4m6!3m5!1s0x2e68e9afafbf93a1:0x3ea2a1aaa010b691!8m2!3d-6.9768982!4d107.6310164!16s%2Fg%2F11dx8yxlkp?entry=ttu';
    }

    function humic_maps_url_shortcode() {
        return esc_url(humic_maps_url());
    }

    function humic_get_events_url() {
        $url = get_post_type_archive_link('humic_event');
        return $url ? $url : home_url('/events/');
    }

    function humic_detect_nav_active() {
        if (is_front_page()) {
            return 'home';
        }
        $virtual = get_query_var('humic_virtual_page');
        if ($virtual === 'vision-mission') {
            return 'vision-mission';
        }
        if ($virtual === 'research') {
            return 'research-areas';
        }
        if ($virtual === 'ipr') {
            return 'ipr';
        }
        if (is_post_type_archive('humic_member')) {
            return 'members';
        }
        if (is_post_type_archive('humic_media')) {
            return 'media';
        }
        if (is_post_type_archive('humic_event') || is_singular('humic_event')) {
            return 'events';
        }
        if (is_post_type_archive(HUMIC_NEWS_CPT) || is_singular(HUMIC_NEWS_CPT)) {
            return 'news';
        }

        $request = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : '';
        $request_path = trim((string) parse_url($request, PHP_URL_PATH), '/');

        if ($request_path !== '' && preg_match('#(^|/)news(/|$)#i', $request_path)) {
            return 'news';
        }
        if ($request_path !== '' && preg_match('#(^|/)events(/|$)#i', $request_path)) {
            return 'events';
        }

        return '';
    }

    function humic_nav_group_has_active($group, $active) {
        $groups = array(
            'about'    => array('about', 'vision-mission', 'members'),
            'research' => array('research', 'research-areas', 'catalog'),
            'activity' => array('activity', 'ipr', 'media'),
        );
        return in_array($active, $groups[$group] ?? array(), true);
    }

    function humic_nav_dropdown_class($group, $active) {
        return 'nav-item-has-dropdown' . (humic_nav_group_has_active($group, $active) ? ' active' : '');
    }

    function humic_nav_sublink_class($item, $active, $base = 'nav-sublink') {
        return $base . ($item === $active ? ' active' : '');
    }

    function humic_header_shortcode($atts) {
        $atts = shortcode_atts(array('active' => ''), $atts, 'humic_header');
        if ($atts['active'] === '') {
            $atts['active'] = humic_detect_nav_active();
            if ($atts['active'] === '' && is_front_page()) {
                $atts['active'] = 'home';
            }
        }
        ob_start();
        humic_render_header($atts['active']);
        return ob_get_clean();
    }

    function humic_footer_shortcode() {
        ob_start();
        humic_render_footer();
        ?>
        <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top" hidden>
        <i class="fa-solid fa-chevron-up"></i>
        </button>
        <?php
        return ob_get_clean();
    }

    function humic_nav_link_class($item, $active, $base = 'nav-link') {
        return $base . ($item === $active ? ' active' : '');
    }

    function humic_nav_link_attrs($item, $active, $base = 'nav-link') {
        return 'class="' . esc_attr(humic_nav_link_class($item, $active, $base)) . '" data-humic-nav="' . esc_attr($item) . '"';
    }

    function humic_news_render_meta_row($cat, $date) {
        if (!$cat && !$date) {
            return;
        }
        ?>
        <div class="news-card-meta">
            <?php if ($cat) : ?>
            <span class="ncat"><?php echo esc_html($cat); ?></span>
            <?php endif; ?>
            <?php if ($date) : ?>
            <time class="ndate" datetime="<?php echo esc_attr($date); ?>"><?php echo esc_html($date); ?></time>
            <?php endif; ?>
        </div>
        <?php
    }

    function humic_render_page_hdr($eyebrow, $title, $back_url, $back_label) {
        ?>
        <header class="humic-page-hdr">
            <a href="<?php echo esc_url($back_url); ?>" class="humic-back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <?php echo esc_html($back_label); ?>
            </a>
            <?php if ($eyebrow) : ?>
            <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <?php endif; ?>
            <h1 class="section-title"><?php echo esc_html($title); ?></h1>
        </header>
        <?php
    }

    function humic_render_single_hdr($eyebrow, $title, $back_url, $back_label, $cat = '', $date = '') {
        ?>
        <header class="humic-page-hdr humic-page-hdr-single">
            <a href="<?php echo esc_url($back_url); ?>" class="humic-back-link">
                <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                <?php echo esc_html($back_label); ?>
            </a>
            <?php if ($eyebrow) : ?>
            <span class="eyebrow"><?php echo esc_html($eyebrow); ?></span>
            <?php endif; ?>
            <?php if ($cat || $date) : ?>
                <?php humic_news_render_meta_row($cat === 'none' ? '' : ($cat ?: 'News'), $date); ?>
            <?php endif; ?>
            <h1 class="section-title"><?php echo esc_html($title); ?></h1>
        </header>
        <?php
    }

    function humic_news_shortcode() {
        $news_only = humic_news_get_news_only_meta_query();

        $featured_query = new WP_Query(array(
            'post_type'      => HUMIC_NEWS_CPT,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'   => '_humic_news_featured',
                    'value' => '1',
                ),
                $news_only,
            ),
        ));

        $featured_id = 0;
        if ($featured_query->have_posts()) {
            $featured_query->the_post();
            $featured_id = get_the_ID();
            wp_reset_postdata();
        }

        $list_query = new WP_Query(array(
            'post_type'      => HUMIC_NEWS_CPT,
            'posts_per_page' => 3,
            'post__not_in'   => $featured_id ? array($featured_id) : array(),
            'orderby'        => 'date',
            'order'          => 'DESC',
            'meta_query'     => $news_only,
        ));

        if (!$featured_id && $list_query->have_posts()) {
            $list_query->the_post();
            $featured_id = get_the_ID();
            wp_reset_postdata();
            $list_query = new WP_Query(array(
                'post_type'      => HUMIC_NEWS_CPT,
                'posts_per_page' => 3,
                'post__not_in'   => array($featured_id),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'meta_query'     => $news_only,
            ));
        }

        if (!$featured_id) {
            return '<p class="news-empty">No news yet. Add items under <strong>HUMIC News</strong> in WordPress admin.</p>';
        }

        ob_start();

        $feat_post = get_post($featured_id);
        $feat_url  = humic_news_get_link($featured_id);
        $feat_img  = humic_news_get_image($featured_id);
        $feat_cat  = get_post_meta($featured_id, '_humic_news_category', true) ?: 'News';
        $feat_date = get_post_meta($featured_id, '_humic_news_date', true);
        if (!$feat_date) {
            $feat_date = get_the_date('M Y', $featured_id);
        }
        $feat_excerpt = has_excerpt($featured_id) ? get_the_excerpt($featured_id) : wp_trim_words($feat_post->post_content, 20);
        ?>
        <div class="news-grid">
        <article class="news-feat" data-news-url="<?php echo esc_url($feat_url); ?>">
            <?php if ($feat_img) : ?>
            <img src="<?php echo esc_url($feat_img); ?>" alt="<?php echo esc_attr(get_the_title($featured_id)); ?>" class="news-feat-img" />
            <?php endif; ?>
            <div class="news-feat-body">
            <?php humic_news_render_meta_row($feat_cat, $feat_date); ?>
            <h3><?php echo esc_html(get_the_title($featured_id)); ?></h3>
            <?php if ($feat_excerpt) : ?>
                <p><?php echo esc_html($feat_excerpt); ?></p>
            <?php endif; ?>
            <a<?php echo humic_news_render_card_attrs($feat_url); ?>>Read More <i class="fa-solid fa-arrow-up-right"></i></a>
            </div>
        </article>
        <div class="news-list">
            <?php
            if ($list_query->have_posts()) :
                while ($list_query->have_posts()) :
                    $list_query->the_post();
                    $pid   = get_the_ID();
                    $url   = humic_news_get_link($pid);
                    $img   = humic_news_get_image($pid);
                    $cat   = get_post_meta($pid, '_humic_news_category', true) ?: 'News';
                    $date  = get_post_meta($pid, '_humic_news_date', true);
                    if (!$date) {
                        $date = get_the_date('M Y', $pid);
                    }
                    ?>
            <article class="news-item" data-news-url="<?php echo esc_url($url); ?>">
            <?php if ($img) : ?>
            <div class="news-item-thumb">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
            </div>
            <?php endif; ?>
            <div class="news-item-body">
                <?php humic_news_render_meta_row($cat, $date); ?>
                <h3><?php echo esc_html(get_the_title()); ?></h3>
                <a<?php echo humic_news_render_card_attrs($url); ?>>Read More <i class="fa-solid fa-arrow-up-right"></i></a>
            </div>
            </article>
                    <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
        </div>
        <?php
        return ob_get_clean();
    }

    add_filter('wpcode_snippet_output', 'humic_news_process_shortcodes_in_wpcode', 10, 2);
    function humic_news_process_shortcodes_in_wpcode($output, $snippet) {
        if (strpos($output, '[humic_') !== false) {
            return do_shortcode($output);
        }
        if (is_object($snippet) && method_exists($snippet, 'get_code_type')) {
            if ($snippet->get_code_type() === 'html') {
                return do_shortcode($output);
            }
        }
        return $output;
    }

    add_filter('elementor/widget/render_content', function ($content) {
        if (strpos($content, '[humic_') !== false) {
            return do_shortcode($content);
        }
        return $content;
    });

    add_action('init', function () {
        if (get_option('humic_news_rewrite_flushed') !== '1') {
            flush_rewrite_rules(false);
            update_option('humic_news_rewrite_flushed', '1');
        }
    }, 99);

    add_action('pre_get_posts', 'humic_news_exclude_events_from_archive');
    function humic_news_exclude_events_from_archive($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        if (!is_post_type_archive(HUMIC_NEWS_CPT)) {
            return;
        }
        if (!empty($_GET['humic_cat'])) {
            return;
        }
        $query->set('meta_query', humic_news_get_news_only_meta_query());
    }

    /* ── Sample news import ── */

    function humic_news_get_import_catalog() {
        $posts = humic_news_get_sample_posts();
        foreach (humic_news_get_repair_catalog() as $item) {
            if (empty($item['featured'])) {
                continue;
            }
            $exists = false;
            foreach ($posts as $existing) {
                if (($existing['title'] ?? '') === ($item['title'] ?? '')) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $posts[] = $item;
            }
        }
        return $posts;
    }

    function humic_news_get_sample_posts() {
        return array(
            array(
                'title'    => 'Winter School: AI for Advanced Multimedia Visualization',
                'excerpt'  => 'Intensive program on AI techniques for advanced multimedia visualization and interactive systems.',
                'content'  => '<p>HUMIC Engineering hosted the Winter School on AI for Advanced Multimedia Visualization, bringing together researchers and students to explore cutting-edge techniques in machine learning, computer vision, and interactive media.</p><p>Participants engaged in hands-on workshops covering deep learning frameworks, 3D visualization pipelines, and real-time rendering for biomedical and IoT applications.</p>',
                'category' => 'News',
                'date'     => 'Dec 2024',
                'image'    => 'image-7.png',
                'featured' => false,
                'url'      => '',
            ),
            array(
                'title'    => 'Sosialisasi KMS Digital Desa Lebakmuncang',
                'excerpt'  => 'Community outreach programme introducing digital knowledge management systems for rural development.',
                'content'  => '<p>The HUMIC community service team conducted a socialization programme on Digital Knowledge Management Systems (KMS) in Desa Lebakmuncang, introducing digital tools to support local governance and rural economic development.</p><p>The programme demonstrated practical applications of IoT and information systems for village-level data collection and decision support.</p>',
                'category' => 'Community',
                'date'     => 'Oct 2024',
                'image'    => 'image-4.png',
                'featured' => false,
                'url'      => '',
            ),
            array(
                'title'    => 'Research Collaboration Team Discussion',
                'excerpt'  => 'Internal research meeting to align collaboration strategies across IoT, biomedical, and data analytics teams.',
                'content'  => '<p>HUMIC Engineering held an internal research collaboration meeting to align strategies across IoT, biomedical engineering, and big data analytics research groups.</p><p>The discussion covered ongoing joint projects, publication targets, and partnership opportunities with hospitals and universities across Indonesia.</p>',
                'category' => 'Research',
                'date'     => 'Sep 2024',
                'image'    => 'image-5.png',
                'featured' => false,
                'url'      => '',
            ),
        );
    }

    function humic_news_get_excluded_event_categories() {
        return array('Event', 'Events');
    }

    function humic_news_is_event_category($category) {
        if ($category === '' || $category === null) {
            return false;
        }
        return in_array(strtolower(trim($category)), array('event', 'events'), true);
    }

    function humic_news_get_news_only_meta_query() {
        return array(
            'relation' => 'OR',
            array(
                'key'     => '_humic_news_category',
                'compare' => 'NOT EXISTS',
            ),
            array(
                'key'     => '_humic_news_category',
                'value'   => humic_news_get_excluded_event_categories(),
                'compare' => 'NOT IN',
            ),
        );
    }

    function humic_news_get_repair_catalog() {
        $catalog = humic_news_get_sample_posts();
        $catalog[] = array(
            'title'    => 'ICICyTA 2024',
            'match'    => 'ICICyTA',
            'excerpt'  => 'Hybrid event in Bali, co-organized by HUMIC Engineering & iHumEn.',
            'content'  => '<p>ICICyTA 2024 — The 4th International Conference on Cybernetics & Intelligent Systems was held as a hybrid event in Bali, co-organized by HUMIC Engineering and iHumEn.</p><p>The conference brought together international researchers to present advances in cybernetics, intelligent systems, IoT, and human-centric engineering applications.</p>',
            'category' => 'Conference',
            'date'     => 'Nov 2024',
            'image'    => 'image-8.png',
            'featured' => true,
            'url'      => '',
        );
        return $catalog;
    }

    function humic_news_find_attachment_by_filename($filename) {
        global $wpdb;
        $like = '%' . $wpdb->esc_like($filename);
        $id   = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta}
            WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s
            LIMIT 1",
            $like
        ));
        if ($id) {
            return (int) $id;
        }
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'attachment' AND guid LIKE %s
            LIMIT 1",
            $like
        ));
        return $id ? (int) $id : 0;
    }

    function humic_news_resolve_upload_path($filename) {
        $upload_dir = wp_upload_dir();
        $paths      = array(
            $upload_dir['basedir'] . '/2026/06/' . $filename,
        );
        $dirs = glob($upload_dir['basedir'] . '/*/*', GLOB_ONLYDIR) ?: array();
        foreach ($dirs as $dir) {
            $paths[] = $dir . '/' . $filename;
        }
        foreach ($paths as $path) {
            if (is_readable($path)) {
                return $path;
            }
        }
        return '';
    }

    function humic_news_set_featured_image($post_id, $filename) {
        if (!$filename) {
            return;
        }

        $attachment_id = humic_news_find_attachment_by_filename($filename);
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
            return;
        }

        $file_path = humic_news_resolve_upload_path($filename);
        if (!$file_path) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $filetype  = wp_check_filetype($filename, null);
        $attach_id = wp_insert_attachment(array(
            'post_mime_type' => $filetype['type'],
            'post_title'     => sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ), $file_path, $post_id);

        if (is_wp_error($attach_id)) {
            return;
        }

        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
        wp_update_attachment_metadata($attach_id, $attach_data);
        set_post_thumbnail($post_id, $attach_id);
    }

    function humic_news_catalog_matches_post($item, $post_title) {
        if (!empty($item['match']) && stripos($post_title, $item['match']) !== false) {
            return true;
        }
        return stripos($post_title, $item['title']) !== false
            || stripos($item['title'], $post_title) !== false;
    }

    function humic_news_repair_all_posts() {
        $posts = get_posts(array(
            'post_type'      => HUMIC_NEWS_CPT,
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ));

        foreach ($posts as $post) {
            foreach (humic_news_get_repair_catalog() as $item) {
                if (!humic_news_catalog_matches_post($item, $post->post_title)) {
                    continue;
                }

                if (!empty($item['image'])) {
                    if (!has_post_thumbnail($post->ID)) {
                        humic_news_set_featured_image($post->ID, $item['image']);
                    } else {
                        humic_news_fix_attachment_metadata(get_post_thumbnail_id($post->ID));
                    }
                }

                if (empty(trim($post->post_content)) && !empty($item['content'])) {
                    wp_update_post(array(
                        'ID'           => $post->ID,
                        'post_content' => $item['content'],
                    ));
                }

                if (empty($post->post_excerpt) && !empty($item['excerpt'])) {
                    wp_update_post(array(
                        'ID'           => $post->ID,
                        'post_excerpt' => $item['excerpt'],
                    ));
                }

                if (empty(get_post_meta($post->ID, '_humic_news_category', true)) && !empty($item['category'])) {
                    update_post_meta($post->ID, '_humic_news_category', $item['category']);
                }

                if (empty(get_post_meta($post->ID, '_humic_news_date', true)) && !empty($item['date'])) {
                    update_post_meta($post->ID, '_humic_news_date', $item['date']);
                }

                break;
            }
        }
    }

    function humic_news_post_exists_by_title($title) {
        $existing = get_page_by_title($title, OBJECT, HUMIC_NEWS_CPT);
        return $existing ? (int) $existing->ID : 0;
    }

    function humic_news_import_samples() {
        if (!current_user_can('edit_posts')) {
            return array('created' => 0, 'skipped' => 0);
        }

        $created = 0;
        $skipped = 0;

        foreach (humic_news_get_import_catalog() as $item) {
            if (humic_news_post_exists_by_title($item['title'])) {
                $skipped++;
                continue;
            }

            $post_id = wp_insert_post(array(
                'post_type'    => HUMIC_NEWS_CPT,
                'post_title'   => $item['title'],
                'post_excerpt' => $item['excerpt'],
                'post_content' => $item['content'] ?? '',
                'post_status'  => 'publish',
            ), true);

            if (is_wp_error($post_id)) {
                continue;
            }

            update_post_meta($post_id, '_humic_news_category', $item['category']);
            update_post_meta($post_id, '_humic_news_date', $item['date']);
            update_post_meta($post_id, '_humic_news_url', $item['url']);
            update_post_meta($post_id, '_humic_news_featured', $item['featured'] ? '1' : '0');

            humic_news_set_featured_image($post_id, $item['image']);
            $created++;
        }

        return array('created' => $created, 'skipped' => $skipped);
    }

    add_action('admin_init', function () {
        if (get_option('humic_news_auto_seeded_v1') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        $counts = wp_count_posts(HUMIC_NEWS_CPT);
        $published = isset($counts->publish) ? (int) $counts->publish : 0;
        if ($published < 4) {
            humic_news_import_samples();
            humic_news_repair_all_posts();
        }
        update_option('humic_news_auto_seeded_v1', '1');
    }, 21);

    add_action('admin_init', function () {
        if (get_option('humic_content_repair_v3') === '1') {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }
        humic_news_repair_all_posts();
        update_option('humic_content_repair_v3', '1');
    }, 25);

    add_action('admin_init', function () {
        if (!isset($_GET['humic_news_import']) || $_GET['humic_news_import'] !== '1') {
            return;
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'humic_news_import')) {
            return;
        }
        if (!current_user_can('edit_posts')) {
            return;
        }

        $result = humic_news_import_samples();
        $redirect = add_query_arg(array(
            'post_type'            => HUMIC_NEWS_CPT,
            'humic_news_imported'  => $result['created'],
            'humic_news_skipped'   => $result['skipped'],
        ), admin_url('edit.php'));
        wp_safe_redirect($redirect);
        exit;
    });

    add_action('admin_notices', function () {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== HUMIC_NEWS_CPT || $screen->base !== 'edit') {
            return;
        }

        if (isset($_GET['humic_news_imported'])) {
            $created = (int) $_GET['humic_news_imported'];
            $skipped = (int) ($_GET['humic_news_skipped'] ?? 0);
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo esc_html(sprintf('Imported %d news item(s). Skipped %d duplicate(s).', $created, $skipped));
            echo '</p></div>';
        }
    });

    /* ── Archive & single page templates ── */

    add_filter('body_class', 'humic_news_body_class');
    function humic_news_body_class($classes) {
        if (is_post_type_archive(HUMIC_NEWS_CPT) || is_singular(HUMIC_NEWS_CPT)) {
            $classes[] = 'humic-news-template';
            $classes[] = 'humic-custom-layout';
        }
        return $classes;
    }

    add_action('wp_enqueue_scripts', 'humic_news_enqueue_front_assets');
    function humic_news_enqueue_front_assets() {
        if (!humic_should_load_front_assets()) {
            return;
        }

        wp_enqueue_style(
            'humic-google-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap',
            array(),
            null
        );

        wp_enqueue_style(
            'humic-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css',
            array(),
            '6.5.0'
        );

        humic_enqueue_site_stylesheet();
        humic_enqueue_site_script();
    }

    function humic_should_load_front_assets() {
        return humic_should_suppress_theme_shell();
    }

    function humic_enqueue_site_stylesheet() {
        $version = HUMIC_ASSET_VERSION;
        $deps    = array('humic-google-fonts', 'humic-fontawesome');
        $css_url = get_template_directory_uri() . '/style.css';
        wp_enqueue_style('humic-site', $css_url, $deps, $version);
    }

    function humic_enqueue_site_script() {
        $version = HUMIC_ASSET_VERSION;
        $js_url  = get_template_directory_uri() . '/assets/script.js';
        wp_enqueue_script('humic-site', $js_url, array(), $version, true);
    }

    function humic_news_asset_url($filename) {
        return content_url('uploads/2026/06/' . ltrim($filename, '/'));
    }

    function humic_news_section_url($hash = '') {
        $home = humic_news_get_home_url();
        return $hash ? $home . '#' . ltrim($hash, '#') : $home;
    }

    function humic_nav_default_text() {
        return "Home | {home}\n"
            . "About Us\n"
            . "  Vision & Mission | {vision}\n"
            . "  Members | {members}\n"
            . "Research\n"
            . "  Research Areas | {research}\n"
            . "  Catalog Products | {catalog} | external\n"
            . "Our Activity\n"
            . "  Intellectual Property Rights | {ipr}\n"
            . "  HUMIC in Media | {media}\n"
            . "News | {news}\n"
            . "Events | {events}";
    }

    function humic_nav_get_text() {
        $saved = get_option('humic_nav_menu', '');
        if (is_string($saved) && trim($saved) !== '') {
            return $saved;
        }
        return humic_nav_default_text();
    }

    function humic_nav_token_url($token) {
        switch ($token) {
            case 'home':     return humic_news_section_url('home');
            case 'vision':   return function_exists('humic_get_vision_url') ? humic_get_vision_url() : humic_news_section_url('about');
            case 'members':  return function_exists('humic_get_members_url') ? humic_get_members_url() : home_url('/members/');
            case 'research': return function_exists('humic_get_research_page_url') ? humic_get_research_page_url() : humic_news_section_url('research');
            case 'catalog':  return function_exists('humic_get_catalog_url') ? humic_get_catalog_url() : (defined('HUMIC_CATALOG_URL') ? HUMIC_CATALOG_URL : '#');
            case 'ipr':      return function_exists('humic_get_ipr_url') ? humic_get_ipr_url() : home_url('/ipr/');
            case 'media':    return function_exists('humic_get_media_url') ? humic_get_media_url() : home_url('/media/');
            case 'news':     $u = get_post_type_archive_link(HUMIC_NEWS_CPT); return $u ? $u : home_url('/news/');
            case 'events':   return humic_get_events_url();
            case 'partners': return humic_news_section_url('partners');
            case 'contact':  return humic_news_section_url('contact');
        }
        return '';
    }

    function humic_nav_resolve_url($url) {
        $url = trim((string) $url);
        if ($url === '') {
            return '#';
        }
        if (preg_match('/^\{([a-z0-9_-]+)\}$/i', $url, $m)) {
            $resolved = humic_nav_token_url(strtolower($m[1]));
            return $resolved !== '' ? $resolved : '#';
        }
        return $url;
    }

    function humic_nav_item_active_key($url) {
        if (preg_match('/^\{([a-z0-9_-]+)\}$/i', trim((string) $url), $m)) {
            $map = array(
                'home' => 'home', 'vision' => 'vision-mission', 'members' => 'members',
                'research' => 'research-areas', 'catalog' => 'catalog', 'ipr' => 'ipr',
                'media' => 'media', 'news' => 'news', 'events' => 'events',
            );
            $token = strtolower($m[1]);
            return isset($map[$token]) ? $map[$token] : '';
        }
        return '';
    }

    function humic_nav_parse($text) {
        $lines   = preg_split('/\r\n|\r|\n/', (string) $text);
        $items   = array();
        $current = null;
        foreach ($lines as $rawline) {
            if (trim($rawline) === '') {
                continue;
            }
            $is_child = preg_match('/^[\t ]+\S/', $rawline);
            $parts    = array_map('trim', explode('|', trim($rawline)));
            $label    = $parts[0];
            if ($label === '') {
                continue;
            }
            $url      = isset($parts[1]) ? $parts[1] : '';
            $external = false;
            for ($i = 2; $i < count($parts); $i++) {
                if (strtolower($parts[$i]) === 'external') {
                    $external = true;
                }
            }
            $node = array('label' => $label, 'url' => $url, 'external' => $external, 'children' => array());
            if ($is_child && $current !== null) {
                $items[$current]['children'][] = $node;
            } else {
                $items[]  = $node;
                $current  = count($items) - 1;
            }
        }
        return $items;
    }

    function humic_nav_get_items() {
        return humic_nav_parse(humic_nav_get_text());
    }

    function humic_nav_link_target($item, $resolved) {
        if (!empty($item['external']) || humic_news_is_external($resolved)) {
            return ' target="_blank" rel="noopener noreferrer"';
        }
        return '';
    }

    function humic_nav_render_desktop($items, $active) {
        foreach ($items as $index => $it) {
            if (!empty($it['children'])) {
                $group_active = false;
                if ($active !== '') {
                    foreach ($it['children'] as $c) {
                        if (humic_nav_item_active_key($c['url']) === $active) {
                            $group_active = true;
                            break;
                        }
                    }
                }
                ?>
                <li class="nav-item-has-dropdown<?php echo $group_active ? ' active' : ''; ?>">
                    <button type="button" class="nav-link nav-dropdown-toggle" aria-expanded="false">
                        <?php echo esc_html($it['label']); ?> <i class="fa-solid fa-chevron-down nav-chevron" aria-hidden="true"></i>
                    </button>
                    <ul class="nav-dropdown">
                        <?php
                        foreach ($it['children'] as $c) :
                            $curl     = humic_nav_resolve_url($c['url']);
                            $ckey     = humic_nav_item_active_key($c['url']);
                            $cactive  = ($ckey !== '' && $ckey === $active);
                            ?>
                            <li><a href="<?php echo esc_url($curl); ?>" class="nav-sublink<?php echo $cactive ? ' active' : ''; ?>"<?php echo humic_nav_link_target($c, $curl); ?>><?php echo esc_html($c['label']); ?><?php echo !empty($c['external']) ? ' <i class="fa-solid fa-arrow-up-right-from-square nav-ext-icon" aria-hidden="true"></i>' : ''; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <?php
            } else {
                $url      = humic_nav_resolve_url($it['url']);
                $key      = humic_nav_item_active_key($it['url']);
                $isactive = ($key !== '' && $key === $active);
                $navkey   = $key !== '' ? $key : sanitize_title($it['label']);
                ?>
                <li><a href="<?php echo esc_url($url); ?>" class="nav-link<?php echo $isactive ? ' active' : ''; ?>" data-humic-nav="<?php echo esc_attr($navkey); ?>"<?php echo humic_nav_link_target($it, $url); ?>><?php echo esc_html($it['label']); ?></a></li>
                <?php
            }
        }
    }

    function humic_nav_render_mobile($items, $active) {
        foreach ($items as $index => $it) {
            if (!empty($it['children'])) {
                $submenu_id = 'mob-submenu-' . $index;
                ?>
                <div class="mob-nav-group">
                    <button type="button" class="mob-link mob-dropdown-toggle" aria-expanded="false" aria-controls="<?php echo esc_attr($submenu_id); ?>"><?php echo esc_html($it['label']); ?> <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
                    <div class="mob-submenu" id="<?php echo esc_attr($submenu_id); ?>">
                        <?php
                        foreach ($it['children'] as $c) :
                            $curl    = humic_nav_resolve_url($c['url']);
                            $ckey    = humic_nav_item_active_key($c['url']);
                            $cactive = ($ckey !== '' && $ckey === $active);
                            ?>
                            <a href="<?php echo esc_url($curl); ?>" class="mob-sublink<?php echo $cactive ? ' active' : ''; ?>"<?php echo humic_nav_link_target($c, $curl); ?>><?php echo esc_html($c['label']); ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php
            } else {
                $url      = humic_nav_resolve_url($it['url']);
                $key      = humic_nav_item_active_key($it['url']);
                $isactive = ($key !== '' && $key === $active);
                $navkey   = $key !== '' ? $key : sanitize_title($it['label']);
                ?>
                <a href="<?php echo esc_url($url); ?>" class="mob-link<?php echo $isactive ? ' active' : ''; ?>" data-humic-nav="<?php echo esc_attr($navkey); ?>"<?php echo humic_nav_link_target($it, $url); ?>><?php echo esc_html($it['label']); ?></a>
                <?php
            }
        }
    }

    function humic_nav_footer_items($items) {
        $out = array();
        foreach ($items as $it) {
            if (!empty($it['children'])) {
                foreach ($it['children'] as $c) {
                    $out[] = $c;
                }
            } else {
                $out[] = $it;
            }
        }
        return $out;
    }

    function humic_render_header($active = '') {
        $home        = humic_news_get_home_url();
        $logo        = humic_news_asset_url('image-22.png');
        $site_email  = function_exists('humic_get_office_email') ? humic_get_office_email() : 'humic@telkomuniversity.ac.id';
        $nav_items   = humic_nav_get_items();
        ?>
        <header id="site-header">
        <div class="topbar" style="display:flex;align-items:center;width:100%;height:36px;min-height:36px;background:#CC0000;">
            <div class="container topbar-inner">
            <div class="topbar-left">
                <a href="mailto:<?php echo esc_attr($site_email); ?>">
                <i class="fa-regular fa-envelope"></i>
                <?php echo esc_html($site_email); ?>
                </a>
                <span class="topbar-sep">|</span>
                <span><i class="fa-solid fa-building-columns"></i>&nbsp; Telkom University</span>
            </div>
            <?php
            if (function_exists('humic_render_social_links')) {
                echo humic_render_social_links('topbar-social');
            }
            ?>
            </div>
        </div>
        <nav class="navbar" id="navbar">
            <div class="container navbar-inner">
            <a href="<?php echo esc_url($home); ?>" class="logo">
                <img src="<?php echo esc_url($logo); ?>" alt="HUMiC Engineering" />
            </a>
            <ul class="nav-links" id="nav-links">
                <?php humic_nav_render_desktop($nav_items, $active); ?>
            </ul>
            <a href="<?php echo esc_url(humic_news_section_url('contact')); ?>" class="btn-cta desktop-cta">Join Us</a>
            <button class="burger" id="burger" type="button" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mobile-menu">
                <span></span><span></span><span></span>
            </button>
            </div>
            <div class="mobile-menu" id="mobile-menu" aria-hidden="true" role="navigation" aria-label="Mobile navigation">
            <?php humic_nav_render_mobile($nav_items, $active); ?>
            <a href="<?php echo esc_url(humic_news_section_url('contact')); ?>" class="btn-cta mob-cta">Join Us</a>
            </div>
        </nav>
        </header>
        <script><?php echo humic_get_header_sync_js(); ?></script>
        <?php
    }

    function humic_render_header_for_page($active = '') {
        if ($active === '') {
            $active = humic_detect_nav_active();
        }
        humic_render_header($active);
    }

    function humic_news_render_header() {
        humic_render_header('news');
    }

    function humic_events_render_header() {
        humic_render_header('events');
    }

    function humic_render_footer() {
        $logo = humic_news_asset_url('image-22.png');
        $site_email   = function_exists('humic_get_office_email') ? humic_get_office_email() : 'humic@telkomuniversity.ac.id';
        $site_address = function_exists('humic_get_office_address') ? humic_get_office_address() : "Jl. Telekomunikasi No. 1, Terusan Buah Batu\nBandung 40257, Indonesia";
        $catalog_url  = function_exists('humic_get_catalog_url') ? humic_get_catalog_url() : (defined('HUMIC_CATALOG_URL') ? HUMIC_CATALOG_URL : '');
        $research_url = function_exists('humic_get_research_page_url')
            ? humic_get_research_page_url()
            : humic_news_section_url('research');
        $research_items = function_exists('humic_get_research_items') ? humic_get_research_items() : array();
        ?>
        <footer class="footer">
        <div class="container footer-inner">
            <div class="footer-top">
            <div class="footer-brand">
                <div class="footer-logo-wrap">
                <img src="<?php echo esc_url($logo); ?>" alt="HUMiC Engineering" class="footer-logo" />
                </div>
                <p><?php echo nl2br(esc_html($site_address)); ?></p>
                <?php if ($site_email) : ?>
                <p><a href="mailto:<?php echo esc_attr($site_email); ?>"><?php echo esc_html($site_email); ?></a></p>
                <?php endif; ?>
            </div>
            <div class="fcol">
                <p class="fcol-title">Quick Links</p>
                <ul>
                <?php
                foreach (humic_nav_footer_items(humic_nav_get_items()) as $fitem) :
                    $furl = humic_nav_resolve_url($fitem['url']);
                    ?>
                <li><a href="<?php echo esc_url($furl); ?>"<?php echo humic_nav_link_target($fitem, $furl); ?>><?php echo esc_html($fitem['label']); ?></a></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <div class="fcol">
                <p class="fcol-title">Research Areas</p>
                <ul>
                <?php
                if (!empty($research_items)) {
                    foreach ($research_items as $item) {
                        $slug = !empty($item['slug']) ? $item['slug'] : sanitize_title($item['title']);
                        $link = trailingslashit($research_url) . '#' . ltrim($slug, '#');
                        ?>
                <li><a href="<?php echo esc_url($link); ?>"><?php echo esc_html($item['title']); ?></a></li>
                        <?php
                    }
                } else {
                    ?>
                <li><a href="<?php echo esc_url($research_url); ?>">Internet of Things</a></li>
                <li><a href="<?php echo esc_url($research_url); ?>">Biomedical Engineering</a></li>
                <li><a href="<?php echo esc_url($research_url); ?>">Big Data Analytics</a></li>
                <li><a href="<?php echo esc_url($research_url); ?>">ICT Development</a></li>
                    <?php
                }
                ?>
                </ul>
            </div>
            </div>
            <div class="footer-bottom">
            <p>&copy; <?php echo esc_html(date('Y')); ?> HUMIC Engineering Research Center</p>
            <?php
            if (function_exists('humic_render_social_links')) {
                echo humic_render_social_links('footer-social');
            }
            ?>
            </div>
        </div>
        </footer>
        <?php
    }

    function humic_news_render_footer() {
        humic_render_footer();
    }

    function humic_news_render_scripts() {
        // Front JS is enqueued via humic_enqueue_site_script() (script.js).
    }

    function humic_is_custom_template_page() {
        if (get_query_var('humic_virtual_page')) {
            return true;
        }
        if (is_post_type_archive('humic_member') || is_post_type_archive('humic_media')) {
            return true;
        }
        return is_post_type_archive(HUMIC_NEWS_CPT)
            || is_singular(HUMIC_NEWS_CPT)
            || is_post_type_archive('humic_event')
            || is_singular('humic_event');
    }

    function humic_render_page_shell($content, $template_class = 'humic-news-template', $nav_active = '') {
        ?><!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php wp_head(); ?>
        </head>
        <body <?php body_class($template_class . ' humic-custom-layout'); ?>>
        <?php wp_body_open(); ?>
        <style id="humic-critical-header-inline"><?php echo humic_get_critical_header_css(); ?></style>
        <?php
        if ($nav_active !== '') {
            humic_render_header($nav_active);
        } elseif ($template_class === 'humic-events-template') {
            humic_events_render_header();
        } else {
            humic_news_render_header();
        }
        ?>
        <main>
        <section class="news">
            <div class="container">
            <?php echo $content; ?>
            </div>
        </section>
        </main>
        <?php humic_render_footer(); ?>
        <button id="back-to-top" class="back-to-top" type="button" aria-label="Back to top" hidden>
        <i class="fa-solid fa-chevron-up"></i>
        </button>
        <?php humic_news_render_scripts(); ?>
        <?php wp_footer(); ?>
        </body>
        </html>
        <?php
    }

    function humic_news_render_page_shell($content) {
        humic_render_page_shell($content, 'humic-news-template');
    }

    add_action('template_redirect', 'humic_news_template_redirect');
    function humic_news_template_redirect() {
        if (is_post_type_archive(HUMIC_NEWS_CPT)) {
            ob_start();
            humic_news_render_archive();
            $html = ob_get_clean();
            humic_news_render_page_shell($html);
            exit;
        }

        if (is_singular(HUMIC_NEWS_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_humic_news_url', true);
            if ($custom && humic_news_is_external($custom)) {
                wp_redirect($custom, 302);
                exit;
            }

            ob_start();
            humic_news_render_single();
            $html = ob_get_clean();
            humic_news_render_page_shell($html);
            exit;
        }
    }

    function humic_news_render_archive() {
        global $wp_query;
        $home         = humic_news_get_home_url();
        $archive_url  = get_post_type_archive_link(HUMIC_NEWS_CPT);
        $current_cat  = isset($_GET['humic_cat']) ? sanitize_text_field(wp_unslash($_GET['humic_cat'])) : '';
        $categories   = humic_news_get_categories();
        $paged        = max(1, (int) get_query_var('paged'));
        ?>
        <?php humic_render_page_hdr('Stay Updated', 'Latest News', $home, 'Back to Home'); ?>
        <?php humic_news_render_category_filter($archive_url, $current_cat, $categories); ?>
        <?php
        if ($current_cat && humic_news_is_event_category($current_cat)) {
            $current_cat = '';
        }
        if ($current_cat) {
            $news_query = new WP_Query(array(
                'post_type'      => HUMIC_NEWS_CPT,
                'posts_per_page' => (int) get_option('posts_per_page'),
                'paged'          => $paged,
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_humic_news_category',
                        'value'   => $current_cat,
                        'compare' => '=',
                    ),
                    humic_news_get_news_only_meta_query(),
                ),
            ));
        } else {
            $news_query = $wp_query;
        }

        if ($news_query->have_posts()) :
        ?>
        <div class="humic-news-archive-grid">
        <?php
        while ($news_query->have_posts()) :
            $news_query->the_post();
            humic_news_render_archive_card(get_the_ID());
        endwhile;
        ?>
        </div>
        <?php
        $pagination = paginate_links(array(
            'total'     => $news_query->max_num_pages,
            'current'   => $paged,
            'type'      => 'array',
            'add_args'  => $current_cat ? array('humic_cat' => $current_cat) : false,
        ));
        if ($pagination) {
            echo '<nav class="humic-news-pagination" aria-label="News pagination">';
            foreach ($pagination as $link) {
                echo $link;
            }
            echo '</nav>';
        }
        wp_reset_postdata();
        ?>
        <?php else : ?>
        <p class="news-empty">No news found<?php echo $current_cat ? ' in this category' : ''; ?>.</p>
        <?php endif;
    }

    function humic_news_get_categories() {
        global $wpdb;
        $results = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT DISTINCT pm.meta_value
                FROM {$wpdb->postmeta} pm
                INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                WHERE pm.meta_key = %s
                AND p.post_type = %s
                AND p.post_status = 'publish'
                AND pm.meta_value != ''
                ORDER BY pm.meta_value ASC",
                '_humic_news_category',
                HUMIC_NEWS_CPT
            )
        );
        return array_values(array_filter($results, function ($cat) {
            return !humic_news_is_event_category($cat);
        }));
    }

    function humic_news_render_category_filter($archive_url, $current_cat, $categories) {
        if (empty($categories)) {
            return;
        }
        ?>
        <nav class="humic-news-filter" aria-label="Filter news by category">
        <a href="<?php echo esc_url($archive_url); ?>" class="humic-news-filter-link<?php echo $current_cat === '' ? ' active' : ''; ?>">All</a>
        <?php foreach ($categories as $cat) : ?>
        <a href="<?php echo esc_url(add_query_arg('humic_cat', $cat, $archive_url)); ?>" class="humic-news-filter-link<?php echo $current_cat === $cat ? ' active' : ''; ?>"><?php echo esc_html($cat); ?></a>
        <?php endforeach; ?>
        </nav>
        <?php
    }

    function humic_news_render_archive_card($pid) {
        $img     = humic_news_get_image($pid);
        $cat     = get_post_meta($pid, '_humic_news_category', true) ?: 'News';
        $date    = get_post_meta($pid, '_humic_news_date', true);
        if (!$date) {
            $date = get_the_date('M Y', $pid);
        }
        $excerpt = has_excerpt($pid) ? get_the_excerpt($pid) : wp_trim_words(get_post_field('post_content', $pid), 20);
        $url     = get_permalink($pid);
        ?>
        <a href="<?php echo esc_url($url); ?>" class="news-feat">
            <?php if ($img) : ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" class="news-feat-img" />
            <?php else : ?>
            <div class="news-feat-img-placeholder">No image</div>
            <?php endif; ?>
            <div class="news-feat-body">
            <?php humic_news_render_meta_row($cat, $date); ?>
            <h2><?php echo esc_html(get_the_title($pid)); ?></h2>
            <?php if ($excerpt) : ?>
                <p><?php echo esc_html($excerpt); ?></p>
            <?php endif; ?>
            <span class="nmore">Read More <i class="fa-solid fa-arrow-up-right"></i></span>
            </div>
        </a>
        <?php
    }

    function humic_news_render_single() {
        the_post();
        $pid   = get_the_ID();
        $img   = humic_news_get_image($pid);
        $cat   = get_post_meta($pid, '_humic_news_category', true) ?: 'News';
        $date  = get_post_meta($pid, '_humic_news_date', true);
        if (!$date) {
            $date = get_the_date('M Y', $pid);
        }
        $archive_url = get_post_type_archive_link(HUMIC_NEWS_CPT);
        ?>
        <?php humic_render_single_hdr('Latest News', get_the_title(), $archive_url, 'All News', $cat, $date); ?>
        <article class="humic-news-single-wrap">
        <?php if ($img) : ?>
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" class="humic-news-single-img" />
        <?php endif; ?>
        <div class="humic-news-single-content">
            <?php the_content(); ?>
        </div>
        </article>
        <?php
    }
