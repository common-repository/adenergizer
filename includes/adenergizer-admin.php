<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) or exit;

class Adenergizer_Admin {

    /**
     * Holds settings option value for easy access.
     */
    private static $saved_settings = array();

    const SETTINGS_MENU_SLUG = 'adenergizer_settings';

    /**
     * Constructor.
     */
    public function __construct() {
        // Register settings
        add_action( 'admin_init', __CLASS__ . '::add_settings' );
        // Add an item in admin menu.
        add_action( 'admin_menu', __CLASS__ . '::add_menu_item' );
        // Add settings link
	    add_filter( 'plugin_action_links_' . plugin_basename( ADENERGIZER_PLUGIN_FILE ), [ __CLASS__, 'add_plugin_action_links' ] );
    }

	/**
	 * Add plugin action links on plugins page.
	 */
	public static function add_plugin_action_links( $actions ) {
		return array_merge( [
			'configure' => '<a href="' . admin_url( 'admin.php?page=' . self::SETTINGS_MENU_SLUG ) . '">' . __( 'Settings', 'adenergizer' ) . '</a>',
		], $actions );
	}

    /**
     * Register and add settings for options page.
     */
    public static function add_settings() {
        // Register setting
        register_setting(
            'adenergizer_settings',
            'adenergizer_setting',
            array( 'sanitize_callback' => __CLASS__ . '::sanitize_settings_fields' )
        );
        // Add settings sections
        add_settings_section( 
            'adenergizer_setting_robots',
            __( 'Robots.txt', 'adenergizer' ),
            __CLASS__ . '::settings_section_robots',
            'adenergizer_settings'
        );
        add_settings_section( 
            'adenergizer_setting_ads',
            __( 'Ads.txt', 'adenergizer' ),
            __CLASS__ . '::settings_section_ads',
            'adenergizer_settings'
        );
        // Add settings fields
        add_settings_field(
            'robots_enabled',
            'Enable Robots.txt',
            __CLASS__ . '::settings_field_robots_enabled',
            'adenergizer_settings',
            'adenergizer_setting_robots'
        );
        add_settings_field(
            'robots_content',
            'Robots Content',
            __CLASS__ . '::settings_field_robots_content',
            'adenergizer_settings',
            'adenergizer_setting_robots'
        );
        add_settings_field(
            'ads_enabled',
            'Enable Ads.txt',
            __CLASS__ . '::settings_field_ads_enabled',
            'adenergizer_settings',
            'adenergizer_setting_ads'
        );
        add_settings_field(
            'ads_content',
            'Ads Content',
            __CLASS__ . '::settings_field_ads_content',
            'adenergizer_settings',
            'adenergizer_setting_ads'
        );
    }

    /**
     * Output robots section.
     */
    public static function settings_section_robots() {
        _e( 'Fill in Robots.txt content and check the checkbox to enable it.', 'adenergizer' );
    }

    /**
     * Output ads section.
     */
    public static function settings_section_ads() {
        _e( 'Fill in Ads.txt content and check the checkbox to enable it.', 'adenergizer' );
    }

    /**
     * Output settings field robots_enabled.
     */
    public static function settings_field_robots_enabled() {
        $enabled = isset( self::$saved_settings['robots_enabled'] ) && self::$saved_settings['robots_enabled'] ? 1 : 0;
        ?>
        <input type="checkbox" id="robots-enabled" name="adenergizer_setting[robots_enabled]" value="1" <?php checked( 1, $enabled, true ) ?>>
    <?php
    }

    /**
     * Output settings field robots_content.
     */
    public static function settings_field_robots_content() {
        $content = isset( self::$saved_settings['robots_content'] ) ? self::$saved_settings['robots_content'] : '';
        if ( empty( $content ) ) {
            $site_url = parse_url( site_url() );
            $path     = ( ! empty( $site_url['path'] ) ) ? $site_url['path'] : '';
            $content = "User-agent: *\n";
            $content  .= "Disallow: $path/wp-admin/\n";
            $content  .= "Allow: $path/wp-admin/admin-ajax.php\n";
        }
        ?>
        <textarea name="adenergizer_setting[robots_content]" id="robots-content" cols="100" rows="5" class="code"><?php echo $content; ?></textarea>
    <?php
    }

    /**
     * Output settings field ads_enabled.
     */
    public static function settings_field_ads_enabled() {
        $enabled = isset( self::$saved_settings['ads_enabled'] ) && self::$saved_settings['ads_enabled'] ? 1 : 0;
        ?>
        <input type="checkbox" id="ads-enabled" name="adenergizer_setting[ads_enabled]" value="1" <?php checked( 1, $enabled, true ) ?>>
    <?php
    }

    /**
     * Output settings field ads_content.
     */
    public static function settings_field_ads_content() {
        $content = isset( self::$saved_settings['ads_content'] ) ? self::$saved_settings['ads_content'] : '';
        ?>
        <textarea name="adenergizer_setting[ads_content]" id="ads-content" cols="100" rows="5" class="code"><?php echo $content; ?></textarea>
    <?php
    }

    /**
     * Sanitize robots_content and ads_content fields.
     * 
     * @param array $setting
     * @return array
     */
    public static function sanitize_settings_fields( $setting ) {
        if( isset( $setting['robots_content'] ) ) {
            $setting['robots_content'] = sanitize_textarea_field( $setting['robots_content'] );
        }
        if( isset( $setting['ads_content'] ) ) {
            $setting['ads_content'] = sanitize_textarea_field( $setting['ads_content'] );
        }
        return $setting;
    }

    /**
     * Add Adenergizer menu item to admin sidebar.
     */
    public static function add_menu_item() {
        add_menu_page(
            __( 'Adenergizer', 'adenergizer' ),
            __( 'Adenergizer', 'adenergizer' ),
            'manage_options',
	        self::SETTINGS_MENU_SLUG,
            __CLASS__ . '::options_page',
            'dashicons-store'
        );
    }

    /**
     * Output options page for the plugin.
     */
    public static function options_page() {
        self::$saved_settings = get_option( 'adenergizer_setting', array() );
        include dirname( ADENERGIZER_PLUGIN_FILE ) . '/templates/options-page.php';
    }
}

// Instantiate the class.
return new Adenergizer_Admin();
