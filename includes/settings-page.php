<?php
defined('ABSPATH') || exit;

add_action('admin_menu', function () {
    add_menu_page(
        'Maintenance Mode Settings',
        'Maintenance Mode',
        'manage_options',
        'dmm-settings',
        'dmm_settings_page_html',
        'dashicons-admin-generic',
        90
    );
});

add_action('admin_init', function () {
    register_setting('dmm_settings_group', 'dmm_enable_mode', [
        'sanitize_callback' => 'absint',
    ]);
    register_setting('dmm_settings_group', 'dmm_mode_scope', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('dmm_settings_group', 'dmm_custom_page_id', [
        'sanitize_callback' => 'absint',
    ]);
    register_setting('dmm_settings_group', 'dmm_maintenance_type', [
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    register_setting('dmm_settings_group', 'dmm_start_time', [
        'sanitize_callback' => 'dmm_sanitize_datetime',
    ]);
    register_setting('dmm_settings_group', 'dmm_end_time', [
        'sanitize_callback' => 'dmm_sanitize_datetime',
    ]);
});

function dmm_sanitize_datetime($value)
{
    return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $value) ? $value : '';
}

function dmm_settings_page_html()
{
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline"><?php esc_html_e('Dynamic Maintenance Mode', 'dynamic-maintenance-mode'); ?></h1>
        <hr class="wp-header-end">
        <?php
                if (
                    isset($_GET['settings-updated']) &&
                    sanitize_text_field(wp_unslash($_GET['settings-updated'])) === 'true' &&
                    current_user_can('manage_options') &&
                    check_admin_referer() 
                ) {
                    ?>
                    <div class="notice notice-success is-dismissible">
                        <p><strong><?php esc_html_e('Settings saved successfully.', 'dynamic-maintenance-mode'); ?></strong></p>
                    </div>
                    <?php
                }
                        ?>
        <form method="post" action="options.php">
            <?php
            settings_fields('dmm_settings_group');
            do_settings_sections('dmm-settings');
            wp_nonce_field('dmm_settings_verify', 'dmm_nonce_field');
            ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><label for="dmm_enable_mode"><?php esc_html_e('Enable Maintenance Mode', 'dynamic-maintenance-mode'); ?></label></th>
                        <td>
                            <input type="checkbox" name="dmm_enable_mode" id="dmm_enable_mode" value="1"
                                <?php checked(get_option('dmm_enable_mode'), '1'); ?> />
                            <label for="dmm_enable_mode"><?php esc_html_e('Yes', 'dynamic-maintenance-mode'); ?></label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="dmm_mode_scope"><?php esc_html_e('Enable For', 'dynamic-maintenance-mode'); ?></label></th>
                        <td>
                            <select name="dmm_mode_scope" id="dmm_mode_scope">
                                <option value="all" <?php selected(get_option('dmm_mode_scope'), 'all'); ?>>
                                    <?php esc_html_e('Maintenance mode for all users', 'dynamic-maintenance-mode'); ?>
                                </option>
                                <option value="loggedin" <?php selected(get_option('dmm_mode_scope'), 'loggedin'); ?>>
                                    <?php esc_html_e('Maintenance mode for logged in users only', 'dynamic-maintenance-mode'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="dmm_custom_page_id"><?php esc_html_e('Custom Page', 'dynamic-maintenance-mode'); ?></label></th>
                        <td>
                            <?php
                            wp_dropdown_pages([
                                'name' => 'dmm_custom_page_id',
                                'selected' => absint(get_option('dmm_custom_page_id')),
                                'show_option_none' => esc_html__('Select a Page', 'dynamic-maintenance-mode'),
                                'option_none_value' => ''
                            ]);
                            ?>
                            <p class="description"><?php esc_html_e('Select a page to show during maintenance mode.', 'dynamic-maintenance-mode'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php esc_html_e('Maintenance Type', 'dynamic-maintenance-mode'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="dmm_maintenance_type" value="alltime" <?php checked(get_option('dmm_maintenance_type'), 'alltime'); ?> />
                                    <?php esc_html_e('Always ON', 'dynamic-maintenance-mode'); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="dmm_maintenance_type" value="schedule" <?php checked(get_option('dmm_maintenance_type'), 'schedule'); ?> />
                                    <?php esc_html_e('Schedule', 'dynamic-maintenance-mode'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr class="schedule-fields">
                        <th scope="row"><label for="dmm_start_time"><?php esc_html_e('Start Time (IST)', 'dynamic-maintenance-mode'); ?></label></th>
                        <td>
                            <input type="datetime-local" name="dmm_start_time" id="dmm_start_time" value="<?php echo esc_attr(get_option('dmm_start_time')); ?>" />
                        </td>
                    </tr>

                    <tr class="schedule-fields">
                        <th scope="row"><label for="dmm_end_time"><?php esc_html_e('End Time (IST)', 'dynamic-maintenance-mode'); ?></label></th>
                        <td>
                            <input type="datetime-local" name="dmm_end_time" id="dmm_end_time" value="<?php echo esc_attr(get_option('dmm_end_time')); ?>" />
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(esc_html__('Save Settings', 'dynamic-maintenance-mode')); ?>
        </form>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                function toggleScheduleFields() {
                    var isSchedule = document.querySelector('input[name="dmm_maintenance_type"]:checked').value === 'schedule';
                    var fields = document.querySelectorAll('.schedule-fields');
                    fields.forEach(field => field.style.display = isSchedule ? 'table-row' : 'none');
                }

                toggleScheduleFields();
                document.querySelectorAll('input[name="dmm_maintenance_type"]').forEach(el => {
                    el.addEventListener('change', toggleScheduleFields);
                });
            });
        </script>
    </div>
    <?php
}