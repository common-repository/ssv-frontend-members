<?php
if (!defined('ABSPATH')) {
    exit;
}
function ssv_add_ssv_menu()
{
    add_menu_page('MP SSV Options', 'SSV Options', 'manage_options', 'ssv_settings', 'ssv_settings_page');
    add_submenu_page('ssv_settings', 'General', 'General', 'manage_options', 'ssv_settings');

}

function ssv_settings_page()
{
    ?>
    <div class="wrap">
        <h1>SSV General Options</h1>
    </div>
    <?php /** @noinspection PhpIncludeInspection */
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    if (is_plugin_active('ssv-events/ssv-events.php')) {
        ?>
        <h2><a href="?page=ssv-events/options/options.php">Events Options</a></h2>
        <?php
    }
    if (is_plugin_active('ssv-frontend-members/ssv-frontend-members.php')) {
        ?>
        <h2><a href="?page=ssv-frontend-members/options/options.php">Frontend Members Options</a></h2>
        <?php
    }
    if (is_plugin_active('ssv-mailchimp/ssv-mailchimp.php')) {
        ?>
        <h2><a href="?page=ssv-mailchimp/options/options.php">MailChimp Options</a></h2>
        <?php
    }
}

add_action('admin_menu', 'ssv_add_ssv_menu', 9);
?>