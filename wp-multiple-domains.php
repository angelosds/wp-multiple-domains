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

/**
 * Replace domain in pages and admin
 */
add_action('after_setup_theme', 'setup_domains');

$controller;

function setup_domains() {
    $controller = new MultipleDomains();
    $controller->init();
}

class MultipleDomains {
    public $domain;
    public $domains;
    private $file_url;
    
    function __construct() {
        $this->file_url = plugin_dir_path(__FILE__) . 'domains.txt';
    }

    /**
     * Set current URL as home and siteurl
     */
    public function init() {
        $this->get_domains();

        define('WP_HOME', $this->domain);
        define('WP_SITEURL', $this->domain);

        // Rendered HTML
        ob_start('buffer_start');
        add_action('shutdown', 'buffer_end');

        // Replace domain in certain wordpress filters
        add_filter('wp_get_attachment_url', 'media_gallery');
        add_filter('wp_prepare_themes_for_js', 'themes');
        add_filter('admin_post_thumbnail_html', 'thumbnail');
    }

    /**
     * Recover domains from current site and config file
     */
    private function get_domains() {
        $this->domain = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
    
        $domains_string = file_get_contents($this->file_url);
        
        if (!empty($domains_string)) {
            $this->domains = explode(',', $domains_string);
        }
    }
}

/**
 * Replace domain ocorrences in rendered HTML
 */
function buffer_start($buffer) {
    return replace_string($buffer);
}

/**
 * Apply new html content with correct domain name
 */
function buffer_end() {
    ob_end_flush();
}

/**
 * Replace domain in media gallery
 */
function media_gallery($url) {
    return replace_string($url);
}

/**
 * Replace domain in themes gallery
 */
function themes($themes) {
    foreach ($themes as $theme_index => $theme) {
        foreach ($theme['screenshot'] as $screenshot_index => $screenshot) {
            $themes[$theme_index]['screenshot'][$screenshot_index] = replace_string($screenshot);
        }
    }

    return $themes;
}

/**
 * Replace domain in featured thumbnail box
 */
function thumbnail($html) {
    return replace_string($html);
}

/**
 * Replaces original domain with new
 */
function replace_string($string) {
    return str_replace($controller->domains, $controller->domain, $string);
}