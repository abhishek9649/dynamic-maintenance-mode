
<?php 

defined('ABSPATH') || exit;

function dmm_handle_maintenance_mode() {
    if (is_admin()) return;
   if (!get_option('dmm_enable_mode')) return;
    $custom_page_id = get_option('dmm_custom_page_id');
    $scope = get_option('dmm_mode_scope', 'all');
    $type = get_option('dmm_maintenance_type');
    $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
    $now_ts = $now->getTimestamp();

    if (!$custom_page_id) return;
    if ($scope === 'loggedin' && is_user_logged_in()) return;
    if (!is_page($custom_page_id)) return;

    if ($type === 'alltime') {
        dmm_show_maintenance_page($custom_page_id);
    }

    if ($type === 'schedule') {
        $start_time_str = get_option('dmm_start_time');
        $end_time_str = get_option('dmm_end_time');
        if (!$start_time_str || !$end_time_str) return;

        $start = get_timestamp_from_indian_datetime($start_time_str);
        $end = get_timestamp_from_indian_datetime($end_time_str);
        if ($start === false || $end === false) return;

        if ($now_ts >= $start && $now_ts <= $end) {
            dmm_show_maintenance_page($custom_page_id);
        }
    }
}

function get_timestamp_from_indian_datetime($datetime_str) {
    try {
        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $datetime_str, new DateTimeZone('Asia/Kolkata'));
        return $dt ? $dt->getTimestamp() : false;
    } catch (Exception $e) {
        return false;
    }
}

function dmm_show_maintenance_page($page_id) {
    $page = get_post($page_id);
    if ($page) {
        status_header(503);
        echo "<div style='text-align:center;padding:50px;font-size:24px;color:#555'>";
        // echo wp_kses_post(apply_filters('the_content', $page->post_content));
        echo "<p style='margin-top:20px;'>" . esc_html__('This page is currently under maintenance.', 'dynamic-maintenance-mode') . "</p>";
        echo "</div>";
        exit;
    }
}
