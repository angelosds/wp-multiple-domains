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

        // Rendered HTML
        ob_start([$this, 'buffer_start']);
        add_action('shutdown', [$this, 'buffer_end']);

        // Replace domain in certain wordpress filters
        add_filter('wp_get_attachment_url', [$this, 'media_gallery']);
        add_filter('wp_prepare_themes_for_js', [$this, 'themes']);
        add_filter('admin_post_thumbnail_html', [$this, 'thumbnail']);
    }

    /**
     * Replace domain ocorrences in rendered HTML
     */
    function buffer_start($buffer) {
        return $this->replace_string($buffer);
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
        return $this->replace_string($url);
    }

    /**
     * Replace domain in themes gallery
     */
    function themes($themes) {
        foreach ($themes as $theme_index => $theme) {
            foreach ($theme['screenshot'] as $screenshot_index => $screenshot) {
                $themes[$theme_index]['screenshot'][$screenshot_index] = $this->replace_string($screenshot);
            }
        }
    
        return $themes;
    }

    /**
     * Replace domain in featured thumbnail box
     */
    function thumbnail($html) {
        return $this->replace_string($html);
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

    /**
     * Replaces original domain with new
     */
    private function replace_string($string) {
        return str_replace(AMBIENT_DOMAINS, AMBIENT_DOMAIN, $string);
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