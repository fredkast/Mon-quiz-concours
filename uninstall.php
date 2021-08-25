<?php
/**
 * Fired when the plugin is uninstalled.
 */
// If uninstall not called from WordPress, then exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}


global $wpdb;
$mqc_plugin_game_settings = $wpdb->prefix . 'mqc_plugin_game_settings';
$mqc_plugin_user = $wpdb->prefix . 'mqc_plugin_user';


$wpdb->query("DROP TABLE IF EXISTS $mqc_plugin_game_settings");
$wpdb->query("DROP TABLE IF EXISTS $mqc_plugin_user");



  
