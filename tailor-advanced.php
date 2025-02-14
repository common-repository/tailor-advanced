<?php

/**
 * Plugin Name: Tailor - Advanced features
 * Plugin URI: http://www.gettailor.com
 * Description: Adds advanced functionality to the Tailor plugin.
 * Version: 1.0.2
 * Author: The Tailor Team
 * Author URI:  http://www.gettailor.com
 * Text Domain: tailor-advanced
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'Tailor_Advanced' ) ) {

    /**
     * Plugin class.
     */
    class Tailor_Advanced {

        /**
         * Plugin instance.
         *
         * @access private
         * @var Tailor_Advanced
         */
        private static $instance;

        /**
         * The plugin version number.
         *
         * @access private
         * @var string
         */
        private static $version;

	    /**
	     * The plugin basename.
	     *
	     * @access private
	     * @var string
	     */
	    private static $plugin_basename;

        /**
         * The plugin name.
         *
         * @access private
         * @var string
         */
        private static $plugin_name;

        /**
         * The plugin directory.
         *
         * @access private
         * @var string
         */
        private static $plugin_dir;

        /**
         * The plugin URL.
         *
         * @access private
         * @var string
         */
        private static $plugin_url;

	    /**
	     * The minimum required version of Tailor.
	     *
	     * @since 1.0.0
	     * @access private
	     * @var string
	     */
	    private static $required_tailor_version = '1.7.7';

        /**
         * Returns the plugin instance.
         *
         * @since 1.0.0
         *
         * @return Tailor_Advanced
         */
        public static function instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Constructor.
         *
         * @since 1.0.0
         */
	    public function __construct() {

            $plugin_data = get_file_data( __FILE__, array( 'Plugin Name', 'Version' ) );

            self::$plugin_basename = plugin_basename( __FILE__ );
            self::$plugin_name = array_shift( $plugin_data );
            self::$version = array_shift( $plugin_data );
	        self::$plugin_dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	        self::$plugin_url = trailingslashit( plugin_dir_url( __FILE__ ) );

		    add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

	    /**
	     * Initializes the plugin.
	     *
	     * @since 1.0.0
	     */
	    public function init() {
		    if (
			    ! class_exists( 'Tailor' ) ||                                                       // Tailor is not active, or
			    ! version_compare( tailor()->version(), self::$required_tailor_version, '>=' )      // An unsupported version is being used
		    ) {
			    add_action( 'admin_notices', array( $this, 'display_version_notice' ) );
			    return;
		    }

		    load_plugin_textdomain( 'tailor-advanced', false, $this->plugin_dir() . 'languages/' );

		    $this->add_actions();
		    $this->includes();
	    }

	    /**
	     * Displays an admin notice if an unsupported version of Tailor is being used.
	     *
	     * @since 1.0.0
	     */
	    public function display_version_notice() {
		    printf(
			    '<div class="notice notice-warning is-dismissible">' .
			    '<p>%s</p>' .
			    '</div>',
			    sprintf(
				    __( 'Please ensure that Tailor %s (or newer) is active to use the Advanced extension.', 'tailor-advanced' ),
				    self::$required_tailor_version
			    )
		    );
	    }

	    /**
	     * Includes required plugin files.
	     *
	     * @since 1.0.0
	     * @access protected
	     */
	    protected function includes() {
		    require_once $this->plugin_dir() . 'includes/helpers-animations.php';
		    require_once $this->plugin_dir() . 'includes/helpers-elements.php';
		    require_once $this->plugin_dir() . 'includes/helpers-general.php';
		    require_once $this->plugin_dir() . 'includes/helpers-markup.php';
		    require_once $this->plugin_dir() . 'includes/helpers-video.php';
	    }

        /**
         * Adds required action hooks.
         *
         * @since 1.0.0
         * @access protected
         */
        protected function add_actions() {
	        add_filter( 'tailor_editor_styles', array( $this, 'add_editor_styles' ) );

	        // Enqueue scripts and styles
	        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_styles' ) );
	        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_scripts' ) );
	        add_action( 'tailor_canvas_enqueue_scripts', array( $this, 'enqueue_canvas_scripts' ), 10, 1 );
	        
        }

	    /**
	     * Adds custom styles to the editor.
	     *
	     * @since 1.0.0
	     */
	    public function add_editor_styles( $editor_styles ) {
		    $editor_styles[] = $this->plugin_url() . 'assets/css/tinymce' . $this->file_extension( '.css' );
		    return $editor_styles;
	    }

	    /**
	     * Enqueues frontend styles.
	     *
	     * @since 1.0.0
	     */
	    public function enqueue_frontend_styles() {
		    
		    /**
		     * Allow developers to prevent Tailor frontend styles from being enqueued.
		     *
		     * @since 1.0.0
		     *
		     * @param bool
		     */
		    if ( is_singular() && apply_filters( 'tailor_enable_frontend_styles', true ) ) {
			    wp_enqueue_style(
				    'tailor-advanced-styles',
				    $this->plugin_url() . 'assets/css/frontend' . $this->file_extension( '.css' ),
				    array( 'tailor-styles' ),
				    $this->version()
			    );
		    }
	    }

	    /**
	     * Enqueues canvas scripts.
	     *
	     * @since 1.0.0
	     */
	    public function enqueue_canvas_scripts( $handle ) {
		    wp_register_script(
			    'jquery-waypoints',
			    $this->plugin_url() . 'assets/js/dist/vendor/jquery.waypoints.min.js',
			    array(),
			    '4.0.1',
			    true
		    );
		    wp_register_script(
			    'jquery-waypoints-inview',
			    $this->plugin_url() . 'assets/js/dist/vendor/jquery.waypoints.inview.min.js',
			    array( 'jquery-waypoints' ),
			    '4.0.1',
			    true
		    );
		    wp_enqueue_script(
			    'tailor-advanced-canvas',
			    $this->plugin_url() . 'assets/js/dist/canvas' . $this->file_extension( '.js' ),
			    array( $handle, 'jquery-waypoints-inview' ),
			    $this->version(),
			    true
		    );
	    }

	    /**
	     * Enqueues frontend scripts.
	     *
	     * @since 1.0.0
	     */
	    public function enqueue_frontend_scripts() {

		    wp_register_script(
			    'jquery-waypoints',
			    $this->plugin_url() . 'assets/js/dist/vendor/jquery.waypoints.min.js',
			    array(),
			    '4.0.1',
			    true
		    );

		    wp_register_script(
			    'jquery-waypoints-inview',
			    $this->plugin_url() . 'assets/js/dist/vendor/jquery.waypoints.inview.min.js',
			    array( 'jquery-waypoints' ),
			    '4.0.1',
			    true
		    );

		    wp_enqueue_script(
			    'tailor-advanced-frontend',
			    $this->plugin_url() . 'assets/js/dist/frontend' . $this->file_extension( '.js' ),
			    array( 'tailor-frontend', 'jquery-waypoints-inview' ),
			    $this->version(),
			    true
		    );
	    }

	    /**
	     * Returns the appropriate file extension.
	     *
	     * @since 1.0.0
	     * @access private
	     */
	    private function file_extension( $file_extension ) {
		    return ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' ) . $file_extension;
	    }

        /**
         * Returns the version number of the plugin.
         *
         * @return string
         */
        public function version() {
            return self::$version;
        }

	    /**
	     * Returns the plugin basename.
	     *
	     * @return string
	     */
	    public function plugin_basename() {
		    return self::$plugin_basename;
	    }

        /**
         * Returns the plugin name.
         *
         * @return string
         */
        public function plugin_name() {
            return self::$plugin_name;
        }

        /**
         * Returns the plugin directory.
         *
         * @return string
         */
        public function plugin_dir() {
            return self::$plugin_dir;
        }

        /**
         * Returns the plugin URL.
         *
         * @return string
         */
        public function plugin_url() {
            return self::$plugin_url;
        }
    }
}

if ( ! function_exists( 'tailor_advanced' ) ) {

	/**
	 * Returns the plugin instance.
	 *
	 * @return Tailor_Advanced
	 */
	function tailor_advanced() {
		return Tailor_Advanced::instance();
	}
}

/**
 * Initializes the plugin.
 */
tailor_advanced();