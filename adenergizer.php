<?php
/**
 * Plugin Name: Ads.txt and Robot.txt - Adenergizer
 * Plugin URI: https://wordpress.org/plugins/adenergizer
 * Description: Manage robot.txt, ads.txt output from dashboard without actually creating them.
 * Version: 0.0.3
 * Requires at least: 4.7
 * Requires PHP: 7.4
 * Author: Adenergizer
 * Author URI: https://www.adenergizer.com/
 * License: GPLv3 or later
 * Text Domain: adenergizer
 * Domain Path: languages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or exit;

// Plugin file
define( 'ADENERGIZER_PLUGIN_FILE', __FILE__ );

/**
 * @class Adenergizer
 */
class Adenergizer {

    // Single instance of class.
    protected static $_instance = null;

    /**
	 * Ensures only one instance of Adenergizer is loaded or can be loaded.
	 *
	 * @static
	 * @return Adenergizer - Main instance.
	 */
    public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
    }
    
    /**
     * Constructor
     */
    public function __construct() {

        // Remove options when deactivated.
        register_deactivation_hook( __FILE__, __CLASS__ . '::deactivate' );

        // Admin stuff only.
        if( is_admin() ) {
            require_once __DIR__ . '/includes/adenergizer-admin.php';
        }

        // Add missing rewrite rules for robots.txt and ads.txt
        add_action( 'init', __CLASS__ . '::add_missing_rewrite_rules' );
        // Hook the parse_request to serve the ads.txt
        add_action( 'parse_request', __CLASS__ . '::ads_txt_request', 10, 1 );

        // Register query variable with WP::parse_request
        add_filter( 'query_vars', __CLASS__ . '::ads_txt_query_var', 10, 1 );
        // Filter robots.txt output
        add_filter( 'robots_txt', array( $this, 'robots_txt' ), 10, 2 );
    }

    /**
     * Clean up when plugin is being deactivated.
     */
    public static function deactivate() {
        delete_option( 'adenergizer_setting' );
    }

    /**
     * Filter the robots.txt output.
     * 
     * @param string $output
     * @param bool $public
     * @return string
     */
    public function robots_txt( $output, $public = true ) {
        if( is_robots() ) {
            $settings = get_option( 'adenergizer_setting', array() );
            if( $public && isset( $settings['robots_enabled'] ) && $settings['robots_enabled']
                    && ! empty( trim( $settings['robots_content'] ) ) ) {
                $output = $settings['robots_content'];
            }
        }

        return $output;
    }

    /**
     * Get the ads.txt content.
     */
    public static function ads_txt() {
        $output = '';
        
        return $output;
    }

    /**
     * Check rewrite rules and update by adding missing rules.
     */
    public static function add_missing_rewrite_rules() {
        // Get rewrite rules.
        $rewrite_rules = (array) get_option( 'rewrite_rules' );
        $rules = array();
        // Force update for missing rule
        if( ! in_array( 'index.php?robots=1', $rewrite_rules ) ) {
            $rules['^robots\.txt$'] = 'index.php?robots=1';
        }
        if( ! in_array( 'index.php?ads=1', $rewrite_rules ) ) {
            $rules['^ads\.txt$'] = 'index.php?ads=1';
        }
        
        if( ! empty( $rules ) ) {
            foreach( $rules as $key => $value ) {
                add_rewrite_rule( $key, $value, 'top' );
            }
            // Recreate the rules.
            flush_rewrite_rules();
        }
    }

    /**
     * Add ads query_var.
     */
    public static function ads_txt_query_var( $query_vars ) {
        $query_vars[] = 'ads';
        return $query_vars;
    }

    /**
     * Serve ads.txt if enabled.
     */
    public static function ads_txt_request( $wp ) {
        if( isset( $wp->query_vars['ads'] ) && 1 == $wp->query_vars['ads'] ) {
            $settings = get_option( 'adenergizer_setting', array() );
            if( isset( $settings['ads_enabled'] ) && $settings['ads_enabled']
                    && ! empty( trim( $settings['ads_content'] ) ) ) {
                // Set proper content-type
                header( 'Content-Type: text/plain; charset=utf-8' );
                echo $settings['ads_content'];
                exit;
            }
        }
    }
}

// Instantiate plugin class.
Adenergizer::instance();
