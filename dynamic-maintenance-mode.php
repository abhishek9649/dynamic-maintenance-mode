<?php
/**
 * Plugin Name: Dynamic Maintenance Mode
 * Description:  Enable maintenance mode with custom page, user scope and scheduling.
 * Version: 1.0.0
 * License: GPLv2 or later
 * Author: Robust Decoders
 */


defined('ABSPATH') || exit;

require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/settings-page.php';

add_action('template_redirect', 'dmm_handle_maintenance_mode');
