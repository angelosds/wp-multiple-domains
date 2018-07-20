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
    private $config_url;
    
    function __construct() {
        $file_name = 'domains.php';
        $this->file_url = plugin_dir_path(__FILE__) . $file_name;
        $this->config_url = ABSPATH . 'wp-content/uploads/' . $file_name;

        $this->init();
    }

    /**
     * Set current URL as home and siteurl
     */
    public function init() {
        $this->add_config_file();

        define('AMBIENT_DOMAIN', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);

        define('WP_HOME', AMBIENT_DOMAIN);
        define('WP_SITEURL', AMBIENT_DOMAIN);
    }

    /**
     * Copy domains file to uploads folder
     */
    private function add_config_file() {
        if (!file_exists($this->config_url)) {
            echo copy($this->file_url, $this->config_url);
        }

        require_once $this->config_url;
    }
}

add_action('after_setup_theme', 'setup');

function setup() {
    $controller = new MultipleDomains();
    
    add_action('shutdown', 'buffer_end');

    ob_start('buffer_start');
}

function buffer_start($buffer) {
    $buffer = str_replace(AMBIENT_DOMAINS, AMBIENT_DOMAIN, $buffer);
    return $buffer; 
}

function buffer_end() {
    ob_end_flush();
}