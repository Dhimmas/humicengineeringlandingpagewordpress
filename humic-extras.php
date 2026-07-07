<?php
/**
 * HUMIC Partners & Events — WPCode PHP snippet (Run Everywhere).
 * Requires humic-news.php for shared image helpers.
 */

if (!defined('ABSPATH')) {
    exit;
}

define('HUMIC_PARTNER_CPT', 'humic_partner');
define('HUMIC_EVENT_CPT', 'humic_event');

add_action('init', 'humic_extras_register_cpts');
function humic_extras_register_cpts() {
    register_post_type(HUMIC_PARTNER_CPT, array(
        'labels' => array(
            'name'          => 'HUMIC Partners',
            'singular_name' => 'Partner',
            'add_new_item'  => 'Add Partner',
            'edit_item'     => 'Edit Partner',
        ),
        'public'       => false,
        'publicly_queryable' => false,
        'has_archive'  => false,
        'rewrite'      => false,
        'show_ui'      => true,
        'show_in_menu' => defined('HUMIC_ADMIN_MENU') ? HUMIC_ADMIN_MENU : 'edit.php?post_type=humic_news',
        'supports'     => array('title', 'thumbnail', 'page-attributes'),
    ));

    register_post_type(HUMIC_EVENT_CPT, array(
        'labels' => array(
            'name'          => 'HUMIC Events',
            'singular_name' => 'Event',
            'add_new_item'  => 'Add Event',
            'edit_item'     => 'Edit Event',
        ),
        'public'       => true,
        'has_archive'  => true,
        'rewrite'      => array('slug' => 'events'),
        'show_ui'      => true,
        'show_in_menu' => defined('HUMIC_ADMIN_MENU') ? HUMIC_ADMIN_MENU : 'edit.php?post_type=humic_news',
        'supports'     => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
    ));
}

add_action('init', function () {
    if (get_option('humic_partners_rewrite_v3') !== '1') {
        flush_rewrite_rules(false);
        update_option('humic_partners_rewrite_v3', '1');
    }
}, 99);

add_action('template_redirect', 'humic_partners_redirect_to_home');
function humic_partners_redirect_to_home() {
    if (is_admin()) {
        return;
    }
    if (!is_post_type_archive(HUMIC_PARTNER_CPT) && !is_singular(HUMIC_PARTNER_CPT)) {
        return;
    }
    $target = function_exists('humic_news_section_url')
        ? humic_news_section_url('partners')
        : home_url('/#partners');
    wp_safe_redirect($target, 301);
    exit;
}

add_action('add_meta_boxes', 'humic_event_meta_boxes');
function humic_event_meta_boxes() {
    add_meta_box(
        'humic_event_details',
        'Event Details',
        'humic_event_meta_box_render',
        HUMIC_EVENT_CPT,
        'side',
        'default'
    );
}

function humic_event_meta_box_render($post) {
    wp_nonce_field('humic_event_save', 'humic_event_nonce');
    $sort_date = get_post_meta($post->ID, '_humic_event_date_sort', true);
    $display   = get_post_meta($post->ID, '_humic_event_date', true);
    if ($sort_date === '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $display)) {
        $sort_date = $display;
    }
    if ($sort_date === '' && $display !== '') {
        $sort_date = humic_event_parse_display_to_sort($display);
    }
    ?>
    <p>
        <label for="humic_event_date_sort"><strong>Event date</strong></label><br>
        <input type="date" id="humic_event_date_sort" name="humic_event_date_sort"
               value="<?php echo esc_attr($sort_date); ?>" class="widefat">
    </p>
    <p>
        <label for="humic_event_date"><strong>Display label</strong> (optional)</label><br>
        <input type="text" id="humic_event_date" name="humic_event_date"
               value="<?php echo esc_attr($display); ?>" class="widefat"
               placeholder="e.g. June 2026 — leave empty to auto-format">
    </p>
    <?php
}

add_action('save_post_' . HUMIC_EVENT_CPT, 'humic_event_save_meta');
function humic_event_save_meta($post_id) {
    if (!isset($_POST['humic_event_nonce']) || !wp_verify_nonce($_POST['humic_event_nonce'], 'humic_event_save')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
    update_post_meta($post_id, '_humic_event_date_sort', humic_event_sanitize_sort_date($_POST['humic_event_date_sort'] ?? ''));
    update_post_meta($post_id, '_humic_event_date', sanitize_text_field($_POST['humic_event_date'] ?? ''));
    humic_event_sync_display_date($post_id);
}

function humic_event_sanitize_sort_date($value) {
    $value = sanitize_text_field($value);
    if ($value === '') {
        return '';
    }
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if (!$date || $date->format('Y-m-d') !== $value) {
        return '';
    }
    return $value;
}

function humic_event_parse_display_to_sort($display) {
    $display = trim((string) $display);
    if ($display === '') {
        return '';
    }
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $display)) {
        return $display;
    }
    $timestamp = strtotime($display);
    if (!$timestamp) {
        return '';
    }
    return wp_date('Y-m-d', $timestamp);
}

function humic_event_sync_display_date($post_id) {
    $label = get_post_meta($post_id, '_humic_event_date', true);
    if ($label !== '') {
        return;
    }
    $sort = get_post_meta($post_id, '_humic_event_date_sort', true);
    if ($sort === '') {
        return;
    }
    update_post_meta($post_id, '_humic_event_date', wp_date('F Y', strtotime($sort . ' 00:00:00 UTC')));
}

function humic_event_get_sort_date($post_id) {
    $sort = get_post_meta($post_id, '_humic_event_date_sort', true);
    if (humic_event_sanitize_sort_date($sort) !== '') {
        return $sort;
    }
    $legacy = get_post_meta($post_id, '_humic_event_date', true);
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $legacy)) {
        return $legacy;
    }
    return humic_event_parse_display_to_sort($legacy);
}

function humic_event_get_display_date($post_id) {
    $display = get_post_meta($post_id, '_humic_event_date', true);
    if ($display !== '') {
        return $display;
    }
    $sort = humic_event_get_sort_date($post_id);
    if ($sort === '') {
        return '';
    }
    return wp_date('F j, Y', strtotime($sort . ' 00:00:00 UTC'));
}

function humic_event_upcoming_query_args($limit = 3) {
    return array(
        'post_type'      => HUMIC_EVENT_CPT,
        'posts_per_page' => (int) $limit,
        'meta_key'       => '_humic_event_date_sort',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array(
            array(
                'key'     => '_humic_event_date_sort',
                'value'   => wp_date('Y-m-d'),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        ),
    );
}

function humic_event_backfill_sort_dates() {
    $posts = get_posts(array(
        'post_type'      => HUMIC_EVENT_CPT,
        'posts_per_page' => -1,
        'post_status'    => 'any',
        'fields'         => 'ids',
    ));
    foreach ($posts as $post_id) {
        $sort = get_post_meta($post_id, '_humic_event_date_sort', true);
        if (humic_event_sanitize_sort_date($sort) !== '') {
            continue;
        }
        $parsed = humic_event_get_sort_date($post_id);
        if ($parsed !== '') {
            update_post_meta($post_id, '_humic_event_date_sort', $parsed);
            humic_event_sync_display_date($post_id);
        }
    }
}

function humic_extras_set_image($post_id, $filename) {
    if (function_exists('humic_news_set_featured_image')) {
        humic_news_set_featured_image($post_id, $filename);
    }
}

function humic_extras_find_by_title($post_type, $title) {
    global $wpdb;
    $id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = %s AND post_title = %s AND post_status != 'trash'
         LIMIT 1",
        $post_type,
        $title
    ));
    return $id ? (int) $id : 0;
}

function humic_extras_get_image_url($post_id, $fallback_filename = '') {
    if (has_post_thumbnail($post_id)) {
        $src = get_the_post_thumbnail_url($post_id, 'medium');
        if ($src) {
            return $src;
        }
    }
    if (!$fallback_filename) {
        return '';
    }
    if (function_exists('humic_news_resolve_upload_path')) {
        $path = humic_news_resolve_upload_path($fallback_filename);
        if ($path) {
            $upload_dir = wp_upload_dir();
            return str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $path);
        }
    }
    $upload_dir = wp_upload_dir();
    $path       = $upload_dir['basedir'] . '/2026/06/' . $fallback_filename;
    if (is_readable($path)) {
        return $upload_dir['baseurl'] . '/2026/06/' . $fallback_filename;
    }
    return '';
}

function humic_extras_get_sample_partners() {
    return array(
        array('title' => 'RS Hasan Sadikin', 'image' => 'image-9.png', 'order' => 1),
        array('title' => 'RSUD Dr. Soetomo', 'image' => 'image-10.png', 'order' => 2),
        array('title' => 'RSSA Malang', 'image' => 'image-11.png', 'order' => 3),
        array('title' => 'Universitas Brawijaya', 'image' => 'image-12.png', 'order' => 4),
        array('title' => 'Institut Teknologi Bandung', 'image' => 'image-13.png', 'order' => 5),
        array('title' => 'Hiroshima University', 'image' => 'image-17.png', 'order' => 6),
        array('title' => 'Universitas Gadjah Mada', 'image' => 'image-18.png', 'order' => 7),
        array('title' => 'PT Len Industri', 'image' => 'image-19.png', 'order' => 8),
        array('title' => 'PT INTI', 'image' => 'image-20.png', 'order' => 9),
        array('title' => 'Universiti Teknologi Malaysia', 'image' => 'image-21.png', 'order' => 10),
    );
}

function humic_extras_get_sample_events() {
    return array(
        array(
            'title'     => 'Seminar Nasional HUMIC 2026',
            'date'      => 'June 2026',
            'date_sort' => '2026-06-01',
            'excerpt'   => 'National seminar showcasing HUMIC Engineering research outcomes in human-centric engineering and IoT.',
            'content'   => '<p>National seminar showcasing HUMIC Engineering research outcomes in human-centric engineering and IoT.</p>',
            'order'     => 1,
        ),
        array(
            'title'     => 'Workshop IoT for Healthcare',
            'date'      => 'July 2026',
            'date_sort' => '2026-07-01',
            'excerpt'   => 'Hands-on workshop on IoT applications for healthcare monitoring and biomedical sensors.',
            'content'   => '<p>Hands-on workshop on IoT applications for healthcare monitoring, biomedical sensors, and clinical data systems.</p>',
            'order'     => 2,
        ),
        array(
            'title'     => 'Annual Research Colloquium',
            'date'      => 'August 2026',
            'date_sort' => '2026-08-01',
            'excerpt'   => 'Annual gathering of HUMIC researchers to present progress and plan upcoming projects.',
            'content'   => '<p>Annual gathering of HUMIC researchers to present progress, discuss collaborations, and plan upcoming projects.</p>',
            'order'     => 3,
        ),
    );
}

function humic_extras_seed_content() {
    foreach (humic_extras_get_sample_partners() as $item) {
        if (humic_extras_find_by_title(HUMIC_PARTNER_CPT, $item['title'])) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_type'   => HUMIC_PARTNER_CPT,
            'post_title'  => $item['title'],
            'post_status' => 'publish',
            'menu_order'  => $item['order'],
        ), true);

        if (!is_wp_error($post_id) && !empty($item['image'])) {
            humic_extras_set_image($post_id, $item['image']);
        }
    }

    foreach (humic_extras_get_sample_events() as $item) {
        if (humic_extras_find_by_title(HUMIC_EVENT_CPT, $item['title'])) {
            continue;
        }

        $post_id = wp_insert_post(array(
            'post_type'    => HUMIC_EVENT_CPT,
            'post_title'   => $item['title'],
            'post_content' => $item['content'],
            'post_excerpt' => $item['excerpt'] ?? '',
            'post_status'  => 'publish',
            'menu_order'   => $item['order'],
        ), true);

        if (!is_wp_error($post_id)) {
            update_post_meta($post_id, '_humic_event_date', $item['date']);
            if (!empty($item['date_sort'])) {
                update_post_meta($post_id, '_humic_event_date_sort', $item['date_sort']);
            }
        }
    }
}

add_action('admin_init', function () {
    if (get_option('humic_event_dates_v1') === '1') {
        return;
    }
    if (!current_user_can('edit_posts')) {
        return;
    }
    humic_event_backfill_sort_dates();
    update_option('humic_event_dates_v1', '1');
}, 35);

add_action('admin_init', function () {
    if (get_option('humic_extras_seeded_v2') === '1') {
        return;
    }
    if (!current_user_can('edit_posts')) {
        return;
    }
    humic_extras_update_event_excerpts();
    update_option('humic_extras_seeded_v2', '1');
}, 36);

function humic_extras_update_event_excerpts() {
    $map = array(
        'Seminar Nasional HUMIC 2026' => 'National seminar showcasing HUMIC Engineering research outcomes in human-centric engineering and IoT.',
        'Workshop IoT for Healthcare' => 'Hands-on workshop on IoT applications for healthcare monitoring and biomedical sensors.',
        'Annual Research Colloquium' => 'Annual gathering of HUMIC researchers to present progress and plan upcoming projects.',
    );
    foreach ($map as $title => $excerpt) {
        $id = humic_extras_find_by_title(HUMIC_EVENT_CPT, $title);
        if ($id) {
            wp_update_post(array(
                'ID'           => $id,
                'post_excerpt' => $excerpt,
            ));
        }
    }
}

add_action('admin_init', function () {
    if (get_option('humic_extras_seeded_v1') === '1') {
        return;
    }
    if (!current_user_can('edit_posts')) {
        return;
    }
    humic_extras_seed_content();
    update_option('humic_extras_seeded_v1', '1');
}, 30);

add_shortcode('humic_partners', 'humic_partners_shortcode');
function humic_partners_shortcode() {
    $query = new WP_Query(array(
        'post_type'      => HUMIC_PARTNER_CPT,
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));

    if (!$query->have_posts()) {
        return '<p class="partners-empty">No partners yet. Add items under <strong>HUMIC Partners</strong> in WordPress admin.</p>';
    }

    $catalog = array();
    foreach (humic_extras_get_sample_partners() as $item) {
        $catalog[$item['title']] = $item['image'];
    }

    $cards = array();
    while ($query->have_posts()) {
        $query->the_post();
        $pid  = get_the_ID();
        $name = get_the_title();
        $img  = humic_extras_get_image_url($pid, $catalog[$name] ?? '');
        
        $card_html = '<div class="pcard">';
        if ($img) {
            $card_html .= '<img src="' . esc_url($img) . '" alt="' . esc_attr($name) . '" loading="eager" decoding="async" />';
        }
        $card_html .= '<span class="pname">' . esc_html($name) . '</span>';
        $card_html .= '</div>';
        $cards[] = $card_html;
    }
    wp_reset_postdata();

    if (empty($cards)) {
        return '';
    }

    $cards_list_html = implode("\n", $cards);

    ob_start();
    ?>
    <div class="partners-carousel-container">
      <div class="partners-carousel-track">
        <div class="partners-carousel-list">
          <?php echo $cards_list_html; ?>
        </div>
        <div class="partners-carousel-list" aria-hidden="true">
          <?php echo $cards_list_html; ?>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('humic_events', 'humic_events_shortcode');
add_shortcode('humic_events_home', 'humic_events_home_shortcode');

function humic_events_home_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 3), $atts, 'humic_events_home');

    $query = new WP_Query(humic_event_upcoming_query_args((int) $atts['limit']));

    if (!$query->have_posts()) {
        return '<p class="events-empty">No upcoming events yet.</p>';
    }

    ob_start();
    echo '<div class="humic-events-home-grid">';
    while ($query->have_posts()) {
        $query->the_post();
        $date = humic_event_get_display_date(get_the_ID());
        $url  = get_permalink();
        $img  = function_exists('humic_news_get_image') ? humic_news_get_image(get_the_ID()) : get_the_post_thumbnail_url(get_the_ID(), 'medium');
        ?>
        <a href="<?php echo esc_url($url); ?>" class="humic-event-home-card">
            <?php if ($img) : ?>
            <div class="humic-event-home-thumb">
                <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" />
            </div>
            <?php endif; ?>
            <?php if ($date) : ?>
            <span class="humic-event-home-date"><?php echo esc_html($date); ?></span>
            <?php endif; ?>
            <h3 class="humic-event-home-title"><?php the_title(); ?></h3>
            <?php if (has_excerpt()) : ?>
            <p class="humic-event-home-excerpt"><?php echo esc_html(get_the_excerpt()); ?></p>
            <?php endif; ?>
            <span class="humic-event-home-link">Learn More <i class="fa-solid fa-arrow-up-right"></i></span>
        </a>
        <?php
    }
    wp_reset_postdata();
    echo '</div>';
    return ob_get_clean();
}

function humic_events_shortcode($atts) {
    $atts = shortcode_atts(array('limit' => 3), $atts, 'humic_events');

    $query = new WP_Query(humic_event_upcoming_query_args((int) $atts['limit']));

    if (!$query->have_posts()) {
        return '<p class="events-empty">No upcoming events yet.</p>';
    }

    ob_start();
    while ($query->have_posts()) {
        $query->the_post();
        $date = humic_event_get_display_date(get_the_ID());
        $url  = get_permalink();
        ?>
      <a href="<?php echo esc_url($url); ?>" class="event-item">
        <?php if ($date) : ?>
        <span class="edate"><?php echo esc_html($date); ?></span>
        <?php endif; ?>
        <span class="etitle"><?php echo esc_html(get_the_title()); ?></span>
      </a>
        <?php
    }
    wp_reset_postdata();
    return ob_get_clean();
}

add_shortcode('humic_events_url', 'humic_events_url_shortcode');
function humic_events_url_shortcode() {
    $url = get_post_type_archive_link(HUMIC_EVENT_CPT);
    if ($url) {
        return esc_url($url);
    }
    if (function_exists('humic_news_section_url')) {
        return esc_url(humic_news_section_url('events'));
    }
    return esc_url(home_url('/#events'));
}

add_action('pre_get_posts', 'humic_events_filter_upcoming_archive');
function humic_events_filter_upcoming_archive($query) {
    if (is_admin() || !$query->is_main_query()) {
        return;
    }
    if (!$query->is_post_type_archive(HUMIC_EVENT_CPT)) {
        return;
    }

    $query->set('meta_key', '_humic_event_date_sort');
    $query->set('orderby', 'meta_value');
    $query->set('order', 'ASC');
    $query->set('meta_query', array(
        array(
            'key'     => '_humic_event_date_sort',
            'value'   => wp_date('Y-m-d'),
            'compare' => '>=',
            'type'    => 'DATE',
        ),
    ));
}

add_filter('body_class', 'humic_events_body_class');
function humic_events_body_class($classes) {
    if (is_post_type_archive(HUMIC_EVENT_CPT) || is_singular(HUMIC_EVENT_CPT)) {
        $classes[] = 'humic-events-template';
        $classes[] = 'humic-custom-layout';
    }
    return $classes;
}

add_action('template_redirect', 'humic_events_template_redirect');
function humic_events_template_redirect() {
    if (!function_exists('humic_render_page_shell')) {
        return;
    }

    if (is_post_type_archive(HUMIC_EVENT_CPT)) {
        ob_start();
        humic_events_render_archive();
        $html = ob_get_clean();
        humic_render_page_shell($html, 'humic-events-template');
        exit;
    }

    if (is_singular(HUMIC_EVENT_CPT)) {
        ob_start();
        humic_events_render_single();
        $html = ob_get_clean();
        humic_render_page_shell($html, 'humic-events-template');
        exit;
    }
}

function humic_events_render_archive() {
    $home = function_exists('humic_news_get_home_url') ? humic_news_get_home_url() : home_url('/');
    if (function_exists('humic_render_page_hdr')) {
        humic_render_page_hdr('What\'s On', 'Upcoming Events', $home, 'Back to Home');
    } else {
        ?>
    <div class="section-hdr">
    <div>
        <span class="eyebrow">What's On</span>
        <h1 class="section-title">Upcoming Events</h1>
    </div>
    </div>
        <?php
    }
    ?>
    <?php if (have_posts()) : ?>
    <div class="humic-events-archive-grid">
    <?php
    while (have_posts()) :
        the_post();
        humic_events_render_card(get_the_ID());
    endwhile;
    ?>
    </div>
    <?php
    global $wp_query;
    $pagination = paginate_links(array(
        'total'   => $wp_query->max_num_pages,
        'current' => max(1, get_query_var('paged')),
        'type'    => 'array',
    ));
    if ($pagination) {
        echo '<nav class="humic-news-pagination" aria-label="Events pagination">';
        foreach ($pagination as $link) {
            echo $link;
        }
        echo '</nav>';
    }
    ?>
    <?php else : ?>
    <p class="events-empty">No upcoming events yet.</p>
    <?php endif;
}

function humic_events_render_card($pid) {
    $date    = humic_event_get_display_date($pid);
    $excerpt = has_excerpt($pid) ? get_the_excerpt($pid) : wp_trim_words(get_post_field('post_content', $pid), 24);
    $url     = get_permalink($pid);
    $img     = function_exists('humic_news_get_image') ? humic_news_get_image($pid) : get_the_post_thumbnail_url($pid, 'medium');
    ?>
    <a href="<?php echo esc_url($url); ?>" class="humic-event-card">
        <?php if ($img) : ?>
        <div class="humic-event-card-thumb">
            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" />
        </div>
        <?php endif; ?>
        <div class="humic-event-card-body">
        <?php
        if ($date && function_exists('humic_news_render_meta_row')) {
            humic_news_render_meta_row('', $date);
        } elseif ($date) {
            ?>
        <span class="edate"><?php echo esc_html($date); ?></span>
            <?php
        }
        ?>
        <h2 class="humic-event-card-title"><?php echo esc_html(get_the_title($pid)); ?></h2>
        <?php if ($excerpt) : ?>
        <p><?php echo esc_html($excerpt); ?></p>
        <?php endif; ?>
        <span class="nmore">View Details <i class="fa-solid fa-arrow-up-right"></i></span>
        </div>
    </a>
    <?php
}

function humic_events_render_single() {
    the_post();
    $pid         = get_the_ID();
    $date        = humic_event_get_display_date($pid);
    $archive_url = get_post_type_archive_link(HUMIC_EVENT_CPT);
    if (function_exists('humic_render_single_hdr')) {
        humic_render_single_hdr('Events', get_the_title(), $archive_url, 'All Events', 'none', $date);
    } else {
        ?>
    <div class="section-hdr">
    <div>
        <span class="eyebrow">Events</span>
        <h1 class="section-title"><?php echo esc_html(get_the_title()); ?></h1>
    </div>
    </div>
        <?php
    }
    $img = function_exists('humic_news_get_image') ? humic_news_get_image($pid) : get_the_post_thumbnail_url($pid, 'large');
    ?>
    <article class="humic-event-single-wrap">
    <?php if ($img) : ?>
    <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr(get_the_title($pid)); ?>" class="humic-event-single-img" />
    <?php endif; ?>
    <div class="humic-news-single-content">
        <?php the_content(); ?>
    </div>
    </article>
    <?php
}
