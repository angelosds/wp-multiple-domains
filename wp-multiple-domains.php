<?php 

/*
Plugin Name: WP Multiple Domains
Plugin URI: https://github.com/angelosds/wp-multiple-domains
Description: A plugin to allow wordpress development with multiple domains 🤖
Version: 1.0.0
Author: Angelo Santos
Author URI: https://github.com/angelosds
License: MIT
*/

class MultipleDomains {
    private $file_url;
    
    function __construct() {
        $this->file_url = plugin_dir_path(__FILE__) . 'domains.wmd';
    }

    /**
     * Set current URL as home and siteurl
     */
    public function init() {
        $this->get_domains();

        define('WP_HOME', AMBIENT_DOMAIN);
        define('WP_SITEURL', AMBIENT_DOMAIN);

        ob_start('buffer_start');

        add_action('shutdown', 'buffer_end');
    }

    /**
     * Recover domains from current site and config file
     */
    private function get_domains() {
        define('AMBIENT_DOMAIN', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);

        $domains_string = file_get_contents($this->file_url);
        
        if (!empty($domains_string)) {
            define('AMBIENT_DOMAINS', explode(',', $domains_string));
        }
    }
}

/**
 * Replace domain in pages and admin
 */
add_action('after_setup_theme', 'setup');

function setup() {
    $controller = new MultipleDomains();
    $controller->init();
}

function buffer_start($buffer) {
    return str_replace(AMBIENT_DOMAINS, AMBIENT_DOMAIN, $buffer);
}

function buffer_end() {
    ob_end_flush();
}

/**
 * Replace domain in media gallery
 */
add_filter('wp_get_attachment_url', function ($url) {
    return str_replace(AMBIENT_DOMAINS, AMBIENT_DOMAIN, $url);
});

/**
 * Replace domain in themes gallery
 */
add_filter('wp_prepare_themes_for_js', function ($themes) {
    foreach ($themes as $theme_index => $theme) {
        foreach ($theme['screenshot'] as $screenshot_index => $screenshot) {
            $themes[$theme_index]['screenshot'][$screenshot_index] = str_replace(AMBIENT_DOMAINS, AMBIENT_DOMAIN, $screenshot);
        }
    }

    return $themes;
});
