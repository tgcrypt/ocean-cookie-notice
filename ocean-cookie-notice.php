<?php
/**
 * Plugin Name:			Ocean Cookie Notice
 * Plugin URI:			https://oceanwp.org/extension/ocean-cookie-notice/
 * Description:			Add a Cookie notice on your website to inform users that you are using cookies to comply with the EU cookie law GDPR regulations.
 * Version:				1.0.4
 * Author:				OceanWP
 * Author URI:			https://oceanwp.org/
 * Requires at least:	4.5.0
 * Tested up to:		4.9.6
 *
 * Text Domain: ocean-cookie-notice
 * Domain Path: /languages/
 *
 * @package Ocean_Cookie_Notice
 * @category Core
 * @author OceanWP
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the main instance of Ocean_Cookie_Notice to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Ocean_Cookie_Notice
 */
function Ocean_Cookie_Notice() {
	return Ocean_Cookie_Notice::instance();
} // End Ocean_Cookie_Notice()

Ocean_Cookie_Notice();

/**
 * Main Ocean_Cookie_Notice Class
 *
 * @class Ocean_Cookie_Notice
 * @version	1.0.0
 * @since 1.0.0
 * @package	Ocean_Cookie_Notice
 */
final class Ocean_Cookie_Notice {
	/**
	 * Ocean_Cookie_Notice The single instance of Ocean_Cookie_Notice.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $token;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $version;

	// Admin - Start
	/**
	 * The admin object.
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $admin;

	// Time
	private $times = array();

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct() {
		$this->token 			= 'ocean-cookie-notice';
		$this->plugin_url 		= plugin_dir_url( __FILE__ );
		$this->plugin_path 		= plugin_dir_path( __FILE__ );
		$this->version 			= '1.0.4';

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		add_filter( 'ocean_register_tm_strings', array( $this, 'register_tm_strings' ) );

		add_action( 'init', array( $this, 'setup' ) );
		add_action( 'init', array( $this, 'updater' ), 1 );

		// Time
		$this->times = array(
			'1hour'				=> array( __( 'An hour', 'ocean-cookie-notice' ), 3600 ),
			'1day'		 		=> array( __( '1 day', 'ocean-cookie-notice' ), 86400 ),
			'1week'		 		=> array( __( '1 week', 'ocean-cookie-notice' ), 604800 ),
			'1month'		 	=> array( __( '1 month', 'ocean-cookie-notice' ), 2592000 ),
			'3months'	 		=> array( __( '3 months', 'ocean-cookie-notice' ), 7862400 ),
			'6months'	 		=> array( __( '6 months', 'ocean-cookie-notice' ), 15811200 ),
			'1year'		 		=> array( __( '1 year', 'ocean-cookie-notice' ), 31536000 ),
			'infinity'	 		=> array( __( 'infinity', 'ocean-cookie-notice' ), 2147483647 )
		);
	}

	/**
	 * Initialize License Updater.
	 * Load Updater initialize.
	 * @return void
	 */
	public function updater() {

		// Plugin Updater Code
		if( class_exists( 'OceanWP_Plugin_Updater' ) ) {
			$license	= new OceanWP_Plugin_Updater( __FILE__, 'Cookie Notice', $this->version, 'OceanWP' );
		}
	}

	/**
	 * Main Ocean_Cookie_Notice Instance
	 *
	 * Ensures only one instance of Ocean_Cookie_Notice is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Ocean_Cookie_Notice()
	 * @return Main Ocean_Cookie_Notice instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	} // End instance()

	/**
	 * Load the localisation file.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'ocean-cookie-notice', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), '1.0.0' );
	}

	/**
	 * Installation.
	 * Runs on activation. Logs the version number and assigns a notice message to a WordPress option.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install() {
		$this->_log_version_number();
	}

	/**
	 * Log the plugin version number.
	 * @access  private
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number() {
		// Log the version number.
		update_option( $this->token . '-version', $this->version );
	}

	/**
	 * Register translation strings.
	 */
	public static function register_tm_strings( $strings ) {

		if ( is_array( $strings ) ) {
			$strings['ocn_content'] 		= 'By continuing to use this website, you consent to the use of cookies in accordance with our Cookie Policy.';
			$strings['ocn_button_text'] 	= 'Accept';
		}

		return $strings;

	}

	/**
	 * Setup all the things.
	 * Only executes if OceanWP or a child theme using OceanWP as a parent is active and the extension specific filter returns true.
	 * @return void
	 */
	public function setup() {
		$theme = wp_get_theme();

		if ( 'OceanWP' == $theme->name || 'oceanwp' == $theme->template ) {
			add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
			add_action( 'customize_register', array( $this, 'customize_register' ) );
			add_action( 'wp_footer', array( $this, 'cookie_notice' ), 9999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ), 999 );
			add_filter( 'ocean_localize_array', array( $this, 'localize_array' ) );
			add_filter( 'ocean_head_css', array( $this, 'head_css' ) );
			add_action( 'wp_head', array( $this, 'header_scripts' ) );
			add_action( 'wp_print_footer_scripts', array( $this, 'footer_scripts' ) );
		}
	}

	/**
	 * Loads js file for customizer preview
	 *
	 * @since  1.0.0
	 */
	public function customize_preview_init() {
		wp_enqueue_script( 'ocn-customize-preview',
			plugins_url( '/assets/js/customizer.min.js', __FILE__ ),
			array( 'customize-preview' ),
			$this->version,
			true
		);
		wp_localize_script( 'ocn-customize-preview', 'ocn_cookie', array(
			'googleFontsUrl' 	=> '//fonts.googleapis.com',
			'googleFontsWeight' => '100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i',
		) );
	}

	/**
	 * Customizer Controls and settings
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 *
	 * @since  1.0.0
	 */
	public function customize_register( $wp_customize ) {

		/**
		 * Custom control
		 */
		require_once( $this->plugin_path .'/includes/customizer-helpers.php' );

		/**
	     * Add a new section
	     */
        $wp_customize->add_section( 'ocn_section' , array(
		    'title'      	=> esc_html__( 'Cookie Notice', 'ocean-cookie-notice' ),
		    'priority'   	=> 210,
		) );

		/**
		 * Content
		 */
		$wp_customize->add_setting( 'ocn_content', array(
			'transport'           	=> 'postMessage',
			'default'           	=> esc_html__( 'By continuing to use this website, you consent to the use of cookies in accordance with our Cookie Policy.', 'ocean-cookie-notice' ),
			'sanitize_callback' 	=> 'wp_kses_post',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Textarea_Control( $wp_customize, 'ocn_content', array(
			'label'	   				=> esc_html__( 'Content', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_content',
			'priority' 				=> 10,
		) ) );

		/**
		 * Close Target
		 */
		$wp_customize->add_setting( 'ocn_target', array(
			'default'           	=> 'button',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_target', array(
			'label'	   				=> esc_html__( 'Close Target', 'ocean-cookie-notice' ),
			'type' 					=> 'select',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_target',
			'priority' 				=> 10,
			'choices' 				=> array(
				'button' 		=> esc_html__( 'Button', 'ocean-cookie-notice' ),
				'close' 		=> esc_html__( 'Close Icon', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Button Text
		 */
		$wp_customize->add_setting( 'ocn_button_text', array(
			'transport' 			=> 'postMessage',
			'default'           	=> esc_html__( 'Accept', 'ocean-cookie-notice' ),
			'sanitize_callback' 	=> 'wp_kses_post',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_button_text', array(
			'label'	   				=> esc_html__( 'Button Text', 'ocean-cookie-notice' ),
			'type' 					=> 'text',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_button_text',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Style
		 */
		$wp_customize->add_setting( 'ocn_style', array(
			'transport' 			=> 'postMessage',
			'default'           	=> 'flyin',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_style', array(
			'label'	   				=> esc_html__( 'Style', 'ocean-cookie-notice' ),
			'type' 					=> 'select',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_style',
			'priority' 				=> 10,
			'choices' 				=> array(
				'flyin' 		=> esc_html__( 'Fly-Ins', 'ocean-cookie-notice' ),
				'floating' 		=> esc_html__( 'Floating Bar', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Cookie Expiry
		 */
		$wp_customize->add_setting( 'ocn_expiry', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '1month',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_expiry', array(
			'label'	   				=> esc_html__( 'Cookie Expiry', 'ocean-cookie-notice' ),
			'type' 					=> 'select',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_expiry',
			'priority' 				=> 10,
			'choices' 				=> array(
				'1hour' 		=> esc_html__( 'An Hour', 'ocean-cookie-notice' ),
				'1day' 			=> esc_html__( '1 day', 'ocean-cookie-notice' ),
				'1week' 		=> esc_html__( '1 Week', 'ocean-cookie-notice' ),
				'1month' 		=> esc_html__( '1 Month', 'ocean-cookie-notice' ),
				'3months' 		=> esc_html__( '3 Months', 'ocean-cookie-notice' ),
				'6months' 		=> esc_html__( '6 Months', 'ocean-cookie-notice' ),
				'1year' 		=> esc_html__( '1 Year', 'ocean-cookie-notice' ),
				'infinity' 		=> esc_html__( 'Infinity', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Scripts
		 */
		$wp_customize->add_setting( 'ocn_scripts_heading', array(
			'sanitize_callback' 	=> 'wp_kses',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'ocn_scripts_heading', array(
			'label'    				=> esc_html__( 'Scripts', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'priority' 				=> 10,
		) ) );

		/**
		 * Head
		 */
		$wp_customize->add_setting( 'ocn_head_scripts', array(
			'transport'           	=> 'postMessage',
			'sanitize_callback' 	=> false,
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Textarea_Control( $wp_customize, 'ocn_head_scripts', array(
			'label'	   				=> esc_html__( 'Head (before the closing head tag)', 'ocean-cookie-notice' ),
			'description'	   		=> esc_html__( 'Add cookies JavaScript code here, they will be used after users consent.', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_head_scripts',
			'priority' 				=> 10,
		) ) );

		/**
		 * Body
		 */
		$wp_customize->add_setting( 'ocn_body_scripts', array(
			'transport'           	=> 'postMessage',
			'sanitize_callback' 	=> false,
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Textarea_Control( $wp_customize, 'ocn_body_scripts', array(
			'label'	   				=> esc_html__( 'Body (before the closing body tag)', 'ocean-cookie-notice' ),
			'description'	   		=> esc_html__( 'Add cookies JavaScript code here, they will be used after users consent.', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_body_scripts',
			'priority' 				=> 10,
		) ) );

		/**
		 * Reloading
		 */
		$wp_customize->add_setting( 'ocn_reload', array(
			'transport'           	=> 'postMessage',
			'default'           	=> 'no',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'ocn_reload', array(
			'label'	   				=> esc_html__( 'Reloading', 'ocean-cookie-notice' ),
			'description'	   		=> esc_html__( 'Reload the page after the user consent.', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_reload',
			'priority' 				=> 10,
			'choices' 				=> array(
				'yes'  			=> esc_html__( 'Yes', 'ocean-cookie-notice' ),
				'no' 			=> esc_html__( 'No', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Overlay
		 */
		$wp_customize->add_setting( 'ocn_overlay', array(
			'transport'           	=> 'postMessage',
			'default'           	=> 'no',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'ocn_overlay', array(
			'label'	   				=> esc_html__( 'Display Overlay', 'ocean-cookie-notice' ),
			'description'	   		=> esc_html__( 'Display an overlay to force the user to consent to your cookies policy.', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_overlay',
			'priority' 				=> 10,
			'choices' 				=> array(
				'yes'  			=> esc_html__( 'Yes', 'ocean-cookie-notice' ),
				'no' 			=> esc_html__( 'No', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Styling: General Heading
		 */
		$wp_customize->add_setting( 'ocn_styling_general_heading', array(
			'sanitize_callback' 	=> 'wp_kses',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'ocn_styling_general_heading', array(
			'label'    				=> esc_html__( 'Styling: General', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'priority' 				=> 10,
		) ) );

		/**
		 * Max Width
		 */
		$wp_customize->add_setting( 'ocn_width', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_width', array(
			'label'	   				=> esc_html__( 'Max Width (px)', 'ocean-cookie-notice' ),
			'type' 					=> 'number',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_width',
			'priority' 				=> 10,
		    'input_attrs' 			=> array(
		        'min'   => 100,
		        'step'  => 1,
		    ),
		) ) );

		/**
		 * Padding
		 */
		$wp_customize->add_setting( 'ocn_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );

		$wp_customize->add_setting( 'ocn_tablet_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_tablet_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_tablet_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_tablet_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_setting( 'ocn_mobile_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_mobile_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_mobile_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_mobile_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Dimensions_Control( $wp_customize, 'ocn_padding_dimensions', array(
			'label'	   				=> esc_html__( 'Padding (px)', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',				
			'settings'   => array(
	            'desktop_top' 		=> 'ocn_top_padding',
	            'desktop_right' 	=> 'ocn_right_padding',
	            'desktop_bottom' 	=> 'ocn_bottom_padding',
	            'desktop_left' 		=> 'ocn_left_padding',
	            'tablet_top' 		=> 'ocn_tablet_top_padding',
	            'tablet_right' 		=> 'ocn_tablet_right_padding',
	            'tablet_bottom' 	=> 'ocn_tablet_bottom_padding',
	            'tablet_left' 		=> 'ocn_tablet_left_padding',
	            'mobile_top' 		=> 'ocn_mobile_top_padding',
	            'mobile_right' 		=> 'ocn_mobile_right_padding',
	            'mobile_bottom' 	=> 'ocn_mobile_bottom_padding',
	            'mobile_left' 		=> 'ocn_mobile_left_padding',
			),
			'priority' 				=> 10,
		    'input_attrs' 			=> array(
		        'min'   => 0,
		        'max'   => 300,
		        'step'  => 1,
		    ),
		) ) );

		/**
		 * Background Color
		 */
		$wp_customize->add_setting( 'ocn_background', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#ffffff',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_background', array(
			'label'	   				=> esc_html__( 'Background Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_background',
			'priority' 				=> 10,
		) ) );

		/**
		 * Border Width
		 */
		$wp_customize->add_setting( 'ocn_border_width', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_border_width', array(
			'label'	   				=> esc_html__( 'Border Width (px)', 'ocean-cookie-notice' ),
			'type' 					=> 'number',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_border_width',
			'priority' 				=> 10,
		    'input_attrs' 			=> array(
		        'min'   => 100,
		        'step'  => 1,
		    ),
		) ) );

		/**
		 * Border Style
		 */
		$wp_customize->add_setting( 'ocn_border_style', array(
			'transport' 			=> 'postMessage',
			'default'           	=> 'none',
			'sanitize_callback' 	=> 'oceanwp_sanitize_select',
		) );

		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'ocn_border_style', array(
			'label'	   				=> esc_html__( 'Border Style', 'ocean-cookie-notice' ),
			'type' 					=> 'select',
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_border_style',
			'priority' 				=> 10,
			'choices' 				=> array(
				'none' 		=> esc_html__( 'None', 'ocean-cookie-notice' ),
				'solid' 	=> esc_html__( 'Solid', 'ocean-cookie-notice' ),
				'double' 	=> esc_html__( 'Double', 'ocean-cookie-notice' ),
				'dotted' 	=> esc_html__( 'Dotted', 'ocean-cookie-notice' ),
				'dashed' 	=> esc_html__( 'Dashed', 'ocean-cookie-notice' ),
				'groove' 	=> esc_html__( 'Groove', 'ocean-cookie-notice' ),
			),
		) ) );

		/**
		 * Forms Border Color
		 */
		$wp_customize->add_setting( 'ocn_border_color', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_border_color', array(
			'label'	   				=> esc_html__( 'Border Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_border_color',
			'priority' 				=> 10,
		) ) );

		/**
		 * Styling: Content Heading
		 */
		$wp_customize->add_setting( 'ocn_styling_content_heading', array(
			'sanitize_callback' 	=> 'wp_kses',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'ocn_styling_content_heading', array(
			'label'    				=> esc_html__( 'Styling: Content', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'priority' 				=> 10,
		) ) );

		/**
		 * Text Color
		 */
		$wp_customize->add_setting( 'ocn_text_color', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#777777',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_text_color', array(
			'label'	   				=> esc_html__( 'Text Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_text_color',
			'priority' 				=> 10,
		) ) );

		/**
		 * Content Typography
		 */
		$wp_customize->add_setting( 'ocn_content_typo_font_family', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_content_typo_font_size',   	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_content_typo_font_weight', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_content_typo_font_style',  	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_content_typo_transform', 		array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_content_typo_line_height', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_content_typo_spacing', 		array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );

		$wp_customize->add_control( new OceanWP_Customizer_Typo_Control( $wp_customize, 'ocn_content_typo', array(
			'label'	   				=> esc_html__( 'Typography', 'ocean-portfolio' ),
			'section'  				=> 'ocn_section',
            'settings'    			=> array(
				'family'      	=> 'ocn_content_typo_font_family',
				'size'        	=> 'ocn_content_typo_font_size',
				'weight'      	=> 'ocn_content_typo_font_weight',
				'style'       	=> 'ocn_content_typo_font_style',
				'transform' 	=> 'ocn_content_typo_transform',
				'line_height' 	=> 'ocn_content_typo_line_height',
				'spacing' 		=> 'ocn_content_typo_spacing'
			),
			'priority' 				=> 10,
			'l10n'        			=> array(),
		) ) );

		/**
		 * Styling: Button Heading
		 */
		$wp_customize->add_setting( 'ocn_styling_button_heading', array(
			'sanitize_callback' 	=> 'wp_kses',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'ocn_styling_button_heading', array(
			'label'    				=> esc_html__( 'Styling: Button', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Background Color
		 */
		$wp_customize->add_setting( 'ocn_btn_background', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#13aff0',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_btn_background', array(
			'label'	   				=> esc_html__( 'Background Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_btn_background',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Color
		 */
		$wp_customize->add_setting( 'ocn_btn_color', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#ffffff',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_btn_color', array(
			'label'	   				=> esc_html__( 'Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_btn_color',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Hover: Background Color
		 */
		$wp_customize->add_setting( 'ocn_btn_hover_background', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#0b7cac',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_btn_hover_background', array(
			'label'	   				=> esc_html__( 'Hover: Background Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_btn_hover_background',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Hover: Color
		 */
		$wp_customize->add_setting( 'ocn_btn_hover_color', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#ffffff',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_btn_hover_color', array(
			'label'	   				=> esc_html__( 'Hover: Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_btn_hover_color',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Padding
		 */
		$wp_customize->add_setting( 'ocn_btn_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );

		$wp_customize->add_setting( 'ocn_btn_tablet_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_setting( 'ocn_btn_mobile_top_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_right_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_bottom_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_left_padding', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Dimensions_Control( $wp_customize, 'ocn_btn_padding_dimensions', array(
			'label'	   				=> esc_html__( 'Padding (px)', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',				
			'settings'   => array(
	            'desktop_top' 		=> 'ocn_btn_top_padding',
	            'desktop_right' 	=> 'ocn_btn_right_padding',
	            'desktop_bottom' 	=> 'ocn_btn_bottom_padding',
	            'desktop_left' 		=> 'ocn_btn_left_padding',
	            'tablet_top' 		=> 'ocn_btn_tablet_top_padding',
	            'tablet_right' 		=> 'ocn_btn_tablet_right_padding',
	            'tablet_bottom' 	=> 'ocn_btn_tablet_bottom_padding',
	            'tablet_left' 		=> 'ocn_btn_tablet_left_padding',
	            'mobile_top' 		=> 'ocn_btn_mobile_top_padding',
	            'mobile_right' 		=> 'ocn_btn_mobile_right_padding',
	            'mobile_bottom' 	=> 'ocn_btn_mobile_bottom_padding',
	            'mobile_left' 		=> 'ocn_btn_mobile_left_padding',
			),
			'priority' 				=> 10,
		    'input_attrs' 			=> array(
		        'min'   => 0,
		        'max'   => 300,
		        'step'  => 1,
		    ),
		) ) );

		/**
		 * Border Radius
		 */
		$wp_customize->add_setting( 'ocn_btn_top_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_right_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_bottom_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_setting( 'ocn_btn_left_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number',
		) );

		$wp_customize->add_setting( 'ocn_btn_tablet_top_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_right_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_bottom_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_tablet_left_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_setting( 'ocn_btn_mobile_top_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_right_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_bottom_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );
		$wp_customize->add_setting( 'ocn_btn_mobile_left_border_radius', array(
			'transport' 			=> 'postMessage',
			'sanitize_callback' 	=> 'oceanwp_sanitize_number_blank',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Dimensions_Control( $wp_customize, 'ocn_btn_border_radius_dimensions', array(
			'label'	   				=> esc_html__( 'Border Radius (px)', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',				
			'settings'   => array(
	            'desktop_top' 		=> 'ocn_btn_top_border_radius',
	            'desktop_right' 	=> 'ocn_btn_right_border_radius',
	            'desktop_bottom' 	=> 'ocn_btn_bottom_border_radius',
	            'desktop_left' 		=> 'ocn_btn_left_border_radius',
	            'tablet_top' 		=> 'ocn_btn_tablet_top_border_radius',
	            'tablet_right' 		=> 'ocn_btn_tablet_right_border_radius',
	            'tablet_bottom' 	=> 'ocn_btn_tablet_bottom_border_radius',
	            'tablet_left' 		=> 'ocn_btn_tablet_left_border_radius',
	            'mobile_top' 		=> 'ocn_btn_mobile_top_border_radius',
	            'mobile_right' 		=> 'ocn_btn_mobile_right_border_radius',
	            'mobile_bottom' 	=> 'ocn_btn_mobile_bottom_border_radius',
	            'mobile_left' 		=> 'ocn_btn_mobile_left_border_radius',
			),
			'priority' 				=> 10,
		    'input_attrs' 			=> array(
		        'min'   => 0,
		        'max'   => 300,
		        'step'  => 1,
		    ),
		) ) );

		/**
		 * Button Typography
		 */
		$wp_customize->add_setting( 'ocn_btn_typo_font_family', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_font_size',   	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_font_weight', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_font_style',  	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_transform', 		array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_key', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_line_height', 	array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );
		$wp_customize->add_setting( 'ocn_btn_typo_spacing', 		array( 'transport' => 'postMessage', 'sanitize_callback' => 'sanitize_text_field', ) );

		$wp_customize->add_control( new OceanWP_Customizer_Typo_Control( $wp_customize, 'ocn_btn_typo', array(
			'label'	   				=> esc_html__( 'Typography', 'ocean-portfolio' ),
			'section'  				=> 'ocn_section',
            'settings'    			=> array(
				'family'      	=> 'ocn_btn_typo_font_family',
				'size'        	=> 'ocn_btn_typo_font_size',
				'weight'      	=> 'ocn_btn_typo_font_weight',
				'style'       	=> 'ocn_btn_typo_font_style',
				'transform' 	=> 'ocn_btn_typo_transform',
				'line_height' 	=> 'ocn_btn_typo_line_height',
				'spacing' 		=> 'ocn_btn_typo_spacing'
			),
			'priority' 				=> 10,
			'l10n'        			=> array(),
			'active_callback' 		=> 'ocn_cac_has_btn_target',
		) ) );

		/**
		 * Styling: Close Icon Heading
		 */
		$wp_customize->add_setting( 'ocn_styling_close_heading', array(
			'sanitize_callback' 	=> 'wp_kses',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'ocn_styling_close_heading', array(
			'label'    				=> esc_html__( 'Styling: Close Icon', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_close_target',
		) ) );

		/**
		 * Color
		 */
		$wp_customize->add_setting( 'ocn_close_color', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#777777',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_close_color', array(
			'label'	   				=> esc_html__( 'Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_close_color',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_close_target',
		) ) );

		/**
		 * Color
		 */
		$wp_customize->add_setting( 'ocn_close_hover_color', array(
			'transport' 			=> 'postMessage',
			'default'           	=> '#333333',
			'sanitize_callback' 	=> 'oceanwp_sanitize_color',
		) );

		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'ocn_close_hover_color', array(
			'label'	   				=> esc_html__( 'Hover: Color', 'ocean-cookie-notice' ),
			'section'  				=> 'ocn_section',
			'settings' 				=> 'ocn_close_hover_color',
			'priority' 				=> 10,
			'active_callback' 		=> 'ocn_cac_has_close_target',
		) ) );
	}

	/**
	 * Gets the cookie notice template part.
	 *
	 * @since   1.0.0
	 */
	public function cookie_notice() {

		$file 		= $this->plugin_path . 'template/notice.php';
		$theme_file = get_stylesheet_directory() . '/templates/extra/notice.php';

		if ( file_exists( $theme_file ) ) {
			$file = $theme_file;
		}

		if ( file_exists( $file ) ) {
			include $file;
		}

	}

	/**
	 * Enqueue scripts.
	 *
	 * @since   1.0.0
	 */
	public function scripts() {

		// Load main stylesheet
		wp_enqueue_style( 'ocean-cookie-notice', plugins_url( '/assets/css/style.min.css', __FILE__ ) );
		
		// Load custom js methods.
		wp_enqueue_script( 'ocean-cookie-notice', plugins_url( '/assets/js/main.min.js', __FILE__ ), array( 'jquery' ), null, true );

		// Font
		$settings = array(
			'ocn_content_typo_font_family',
			'ocn_btn_typo_font_family',
		);

		foreach ( $settings as $setting ) {

	    	// Get fonts
			$fonts 	= array();
			$val 	= get_theme_mod( $setting );

			// If there is a value lets do something
			if ( ! empty( $val ) ) {

				// Sanitize
				$val = str_replace( '"', '', $val );

				$fonts[] = $val;

			}

			// Loop through and enqueue fonts
			if ( ! empty( $fonts ) && is_array( $fonts ) ) {
				foreach ( $fonts as $font ) {
					oceanwp_enqueue_google_font( $font );
				}
			}

		}

	}

	/**
	 * Localize array
	 *
	 * @since  1.0.0
	 */
	public function localize_array( $array ) {

		// Time
		$time 		= get_theme_mod( 'ocn_expiry', '1month' );
		$get_time 	= sanitize_text_field( isset( $time ) && in_array( $time, array_keys( $this->times ) ) ? $time : '1month' );

		$array['cookieName'] 		= 'ocn_accepted';
		$array['cookieTime'] 		= $this->times[$get_time][1];
		$array['cookiePath'] 		= ( defined( 'COOKIEPATH' ) ? COOKIEPATH : '' );
		$array['cookieDomain'] 		= ( defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '' );
		$array['cache'] 			= defined( 'WP_CACHE' ) && WP_CACHE;
		$array['secure'] 			= (int) is_ssl();
		$array['reload'] 			= get_theme_mod( 'ocn_reload', 'no' );
		$array['overlay'] 			= get_theme_mod( 'ocn_overlay', 'no' );

		return $array;

	}

	/**
	 * Add css in head tag.
	 *
	 * @since  1.0.0
	 */
	public function head_css( $output ) {
		
		// Global vars
		$width 								= get_theme_mod( 'ocn_width' );
		$top_padding 						= get_theme_mod( 'ocn_top_padding' );
		$right_padding 						= get_theme_mod( 'ocn_right_padding' );
		$bottom_padding 					= get_theme_mod( 'ocn_bottom_padding' );
		$left_padding 						= get_theme_mod( 'ocn_left_padding' );
		$tablet_top_padding 				= get_theme_mod( 'ocn_tablet_top_padding' );
		$tablet_right_padding 				= get_theme_mod( 'ocn_tablet_right_padding' );
		$tablet_bottom_padding 				= get_theme_mod( 'ocn_tablet_bottom_padding' );
		$tablet_left_padding 				= get_theme_mod( 'ocn_tablet_left_padding' );
		$mobile_top_padding 				= get_theme_mod( 'ocn_mobile_top_padding' );
		$mobile_right_padding 				= get_theme_mod( 'ocn_mobile_right_padding' );
		$mobile_bottom_padding 				= get_theme_mod( 'ocn_mobile_bottom_padding' );
		$mobile_left_padding 				= get_theme_mod( 'ocn_mobile_left_padding' );
		$background 						= get_theme_mod( 'ocn_background', '#ffffff' );
		$border_width 						= get_theme_mod( 'ocn_border_width' );
		$border_style 						= get_theme_mod( 'ocn_border_style', 'none' );
		$border_color 						= get_theme_mod( 'ocn_border_color' );
		$text_color 						= get_theme_mod( 'ocn_text_color', '#777777' );
		$btn_background 					= get_theme_mod( 'ocn_btn_background', '#13aff0' );
		$btn_color 							= get_theme_mod( 'ocn_btn_color', '#ffffff' );
		$btn_hover_background 				= get_theme_mod( 'ocn_btn_hover_background', '#0b7cac' );
		$btn_hover_color 					= get_theme_mod( 'ocn_btn_hover_color', '#ffffff' );
		$btn_top_padding 					= get_theme_mod( 'ocn_btn_top_padding' );
		$btn_right_padding 					= get_theme_mod( 'ocn_btn_right_padding' );
		$btn_bottom_padding 				= get_theme_mod( 'ocn_btn_bottom_padding' );
		$btn_left_padding 					= get_theme_mod( 'ocn_btn_left_padding' );
		$btn_tablet_top_padding 			= get_theme_mod( 'ocn_btn_tablet_top_padding' );
		$btn_tablet_right_padding 			= get_theme_mod( 'ocn_btn_tablet_right_padding' );
		$btn_tablet_bottom_padding 			= get_theme_mod( 'ocn_btn_tablet_bottom_padding' );
		$btn_tablet_left_padding 			= get_theme_mod( 'ocn_btn_tablet_left_padding' );
		$btn_mobile_top_padding 			= get_theme_mod( 'ocn_btn_mobile_top_padding' );
		$btn_mobile_right_padding 			= get_theme_mod( 'ocn_btn_mobile_right_padding' );
		$btn_mobile_bottom_padding 			= get_theme_mod( 'ocn_btn_mobile_bottom_padding' );
		$btn_mobile_left_padding 			= get_theme_mod( 'ocn_btn_mobile_left_padding' );
		$btn_top_border_radius 				= get_theme_mod( 'ocn_btn_top_border_radius' );
		$btn_right_border_radius 			= get_theme_mod( 'ocn_btn_right_border_radius' );
		$btn_bottom_border_radius 			= get_theme_mod( 'ocn_btn_bottom_border_radius' );
		$btn_left_border_radius 			= get_theme_mod( 'ocn_btn_left_border_radius' );
		$btn_tablet_top_border_radius 		= get_theme_mod( 'ocn_btn_tablet_top_border_radius' );
		$btn_tablet_right_border_radius 	= get_theme_mod( 'ocn_btn_tablet_right_border_radius' );
		$btn_tablet_bottom_border_radius 	= get_theme_mod( 'ocn_btn_tablet_bottom_border_radius' );
		$btn_tablet_left_border_radius 		= get_theme_mod( 'ocn_btn_tablet_left_border_radius' );
		$btn_mobile_top_border_radius 		= get_theme_mod( 'ocn_btn_mobile_top_border_radius' );
		$btn_mobile_right_border_radius 	= get_theme_mod( 'ocn_btn_mobile_right_border_radius' );
		$btn_mobile_bottom_border_radius 	= get_theme_mod( 'ocn_btn_mobile_bottom_border_radius' );
		$btn_mobile_left_border_radius 		= get_theme_mod( 'ocn_btn_mobile_left_border_radius' );
		$close_color 						= get_theme_mod( 'ocn_close_color', '#777' );
		$close_hover_color 					= get_theme_mod( 'ocn_close_hover_color', '#333' );

		// Text typography
		$text_font_family 					= get_theme_mod( 'ocn_content_typo_font_family' );
		$text_font_size 					= get_theme_mod( 'ocn_content_typo_font_size' );
		$text_font_weight 					= get_theme_mod( 'ocn_content_typo_font_weight' );
		$text_font_style 					= get_theme_mod( 'ocn_content_typo_font_style' );
		$text_text_transform 				= get_theme_mod( 'ocn_content_typo_transform' );
		$text_line_height 					= get_theme_mod( 'ocn_content_typo_line_height' );
		$text_letter_spacing 				= get_theme_mod( 'ocn_content_typo_spacing' );

		// Button typography
		$button_font_family 				= get_theme_mod( 'ocn_btn_typo_font_family' );
		$button_font_size 					= get_theme_mod( 'ocn_btn_typo_font_size' );
		$button_font_weight 				= get_theme_mod( 'ocn_btn_typo_font_weight' );
		$button_font_style 					= get_theme_mod( 'ocn_btn_typo_font_style' );
		$button_text_transform 				= get_theme_mod( 'ocn_btn_typo_transform' );
		$button_line_height 				= get_theme_mod( 'ocn_btn_typo_line_height' );
		$button_letter_spacing 				= get_theme_mod( 'ocn_btn_typo_spacing' );

		// Define css var
		$css = '';
		$text_typo_css = '';
		$button_typo_css = '';

		// Width
		if ( ! empty( $width ) ) {
			$css .= '#ocn-cookie-wrap.flyin,#ocn-cookie-wrap.floating #ocn-cookie-inner{width:'. $width .'px;}';
		}

		// Padding
		if ( isset( $top_padding ) && '' != $top_padding
			|| isset( $right_padding ) && '' != $right_padding
			|| isset( $bottom_padding ) && '' != $bottom_padding
			|| isset( $left_padding ) && '' != $left_padding ) {
			$css .= '#ocn-cookie-wrap.flyin,#ocn-cookie-wrap.floating{padding:'. oceanwp_spacing_css( $top_padding, $right_padding, $bottom_padding, $left_padding ) .'}';
		}

		// Tablet padding
		if ( isset( $tablet_top_padding ) && '' != $tablet_top_padding
			|| isset( $tablet_right_padding ) && '' != $tablet_right_padding
			|| isset( $tablet_bottom_padding ) && '' != $tablet_bottom_padding
			|| isset( $tablet_left_padding ) && '' != $tablet_left_padding ) {
			$css .= '@media (max-width: 768px){#ocn-cookie-wrap.flyin,#ocn-cookie-wrap.floating{padding:'. oceanwp_spacing_css( $tablet_top_padding, $tablet_right_padding, $tablet_bottom_padding, $tablet_left_padding ) .'}}';
		}

		// Mobile padding
		if ( isset( $mobile_top_padding ) && '' != $mobile_top_padding
			|| isset( $mobile_right_padding ) && '' != $mobile_right_padding
			|| isset( $mobile_bottom_padding ) && '' != $mobile_bottom_padding
			|| isset( $mobile_left_padding ) && '' != $mobile_left_padding ) {
			$css .= '@media (max-width: 480px){#ocn-cookie-wrap.flyin,#ocn-cookie-wrap.floating{padding:'. oceanwp_spacing_css( $mobile_top_padding, $mobile_right_padding, $mobile_bottom_padding, $mobile_left_padding ) .'}}';
		}

		// Add background
		if ( ! empty( $background ) && '#ffffff' != $background ) {
			$css .= '#ocn-cookie-wrap{background-color:'. $background .';}';
		}

		// Add border width
		if ( ! empty( $border_width ) ) {
			$css .= '#ocn-cookie-wrap{border-width:'. $border_width .'px;}';
		}

		// Add border style
		if ( ! empty( $border_style ) && 'none' != $border_style ) {
			$css .= '#ocn-cookie-wrap{border-style:'. $border_style .';}';
		}

		// Add border color
		if ( ! empty( $border_color ) ) {
			$css .= '#ocn-cookie-wrap{border-color:'. $border_color .';}';
		}

		// Add color
		if ( ! empty( $text_color ) && '#777777' != $text_color ) {
			$css .= '#ocn-cookie-wrap{color:'. $text_color .';}';
		}

		// Add button background
		if ( ! empty( $btn_background ) && '#13aff0' != $btn_background ) {
			$css .= '#ocn-cookie-wrap .ocn-btn{background-color:'. $btn_background .';}';
		}

		// Add button color
		if ( ! empty( $btn_color ) && '#ffffff' != $btn_color ) {
			$css .= '#ocn-cookie-wrap .ocn-btn{color:'. $btn_color .';}';
		}

		// Add button hover background
		if ( ! empty( $btn_hover_background ) && '#13aff0' != $btn_hover_background ) {
			$css .= '#ocn-cookie-wrap .ocn-btn:hover{background-color:'. $btn_hover_background .';}';
		}

		// Add button hover color
		if ( ! empty( $btn_hover_color ) && '#ffffff' != $btn_hover_color ) {
			$css .= '#ocn-cookie-wrap .ocn-btn:hover{color:'. $btn_hover_color .';}';
		}

		// Button padding
		if ( isset( $btn_top_padding ) && '' != $btn_top_padding
			|| isset( $btn_right_padding ) && '' != $btn_right_padding
			|| isset( $btn_bottom_padding ) && '' != $btn_bottom_padding
			|| isset( $btn_left_padding ) && '' != $btn_left_padding ) {
			$css .= '#ocn-cookie-wrap .ocn-btn{padding:'. oceanwp_spacing_css( $btn_top_padding, $btn_right_padding, $btn_bottom_padding, $btn_left_padding ) .'}';
		}

		// Tablet button padding
		if ( isset( $btn_tablet_top_padding ) && '' != $btn_tablet_top_padding
			|| isset( $btn_tablet_right_padding ) && '' != $btn_tablet_right_padding
			|| isset( $btn_tablet_bottom_padding ) && '' != $btn_tablet_bottom_padding
			|| isset( $btn_tablet_left_padding ) && '' != $btn_tablet_left_padding ) {
			$css .= '@media (max-width: 768px){#ocn-cookie-wrap .ocn-btn{padding:'. oceanwp_spacing_css( $btn_tablet_top_padding, $btn_tablet_right_padding, $btn_tablet_bottom_padding, $btn_tablet_left_padding ) .'}}';
		}

		// Mobile button padding
		if ( isset( $btn_mobile_top_padding ) && '' != $btn_mobile_top_padding
			|| isset( $btn_mobile_right_padding ) && '' != $btn_mobile_right_padding
			|| isset( $btn_mobile_bottom_padding ) && '' != $btn_mobile_bottom_padding
			|| isset( $btn_mobile_left_padding ) && '' != $btn_mobile_left_padding ) {
			$css .= '@media (max-width: 480px){#ocn-cookie-wrap .ocn-btn{padding:'. oceanwp_spacing_css( $btn_mobile_top_padding, $btn_mobile_right_padding, $btn_mobile_bottom_padding, $btn_mobile_left_padding ) .'}}';
		}

		// Button border radius
		if ( isset( $btn_top_border_radius ) && '' != $btn_top_border_radius
			|| isset( $btn_right_border_radius ) && '' != $btn_right_border_radius
			|| isset( $btn_bottom_border_radius ) && '' != $btn_bottom_border_radius
			|| isset( $btn_left_border_radius ) && '' != $btn_left_border_radius ) {
			$css .= '#ocn-cookie-wrap .ocn-btn{border-radius:'. oceanwp_spacing_css( $btn_top_border_radius, $btn_right_border_radius, $btn_bottom_border_radius, $btn_left_border_radius ) .'}';
		}

		// Tablet button border radius
		if ( isset( $btn_tablet_top_border_radius ) && '' != $btn_tablet_top_border_radius
			|| isset( $btn_tablet_right_border_radius ) && '' != $btn_tablet_right_border_radius
			|| isset( $btn_tablet_bottom_border_radius ) && '' != $btn_tablet_bottom_border_radius
			|| isset( $btn_tablet_left_border_radius ) && '' != $btn_tablet_left_border_radius ) {
			$css .= '@media (max-width: 768px){#ocn-cookie-wrap .ocn-btn{border-radius:'. oceanwp_spacing_css( $btn_tablet_top_border_radius, $btn_tablet_right_border_radius, $btn_tablet_bottom_border_radius, $btn_tablet_left_border_radius ) .'}}';
		}

		// Mobile button border radius
		if ( isset( $btn_mobile_top_border_radius ) && '' != $btn_mobile_top_border_radius
			|| isset( $btn_mobile_right_border_radius ) && '' != $btn_mobile_right_border_radius
			|| isset( $btn_mobile_bottom_border_radius ) && '' != $btn_mobile_bottom_border_radius
			|| isset( $btn_mobile_left_border_radius ) && '' != $btn_mobile_left_border_radius ) {
			$css .= '@media (max-width: 480px){#ocn-cookie-wrap .ocn-btn{border-radius:'. oceanwp_spacing_css( $btn_mobile_top_border_radius, $btn_mobile_right_border_radius, $btn_mobile_bottom_border_radius, $btn_mobile_left_border_radius ) .'}}';
		}

		// Add close icon color
		if ( ! empty( $close_color ) && '#777777' != $close_color ) {
			$css .= '#ocn-cookie-wrap .ocn-icon svg{fill:'. $close_color .';}';
		}

		// Add close icon hover color
		if ( ! empty( $close_hover_color ) && '#333333' != $close_hover_color ) {
			$css .= '#ocn-cookie-wrap .ocn-icon:hover svg{fill:'. $close_hover_color .';}';
		}

		// Add text font family
		if ( ! empty( $text_font_family ) ) {
			$text_typo_css .= 'font-family:'. $text_font_family .';';
		}

		// Add text font size
		if ( ! empty( $text_font_size ) ) {
			$text_typo_css .= 'font-size:'. $text_font_size .';';
		}

		// Add text font weight
		if ( ! empty( $text_font_weight ) ) {
			$text_typo_css .= 'font-weight:'. $text_font_weight .';';
		}

		// Add text font style
		if ( ! empty( $text_font_style ) ) {
			$text_typo_css .= 'font-style:'. $text_font_style .';';
		}

		// Add text text transform
		if ( ! empty( $text_text_transform ) ) {
			$text_typo_css .= 'text-transform:'. $text_text_transform .';';
		}

		// Add text line height
		if ( ! empty( $text_line_height ) ) {
			$text_typo_css .= 'line-height:'. $text_line_height .';';
		}

		// Add text letter spacing
		if ( ! empty( $text_letter_spacing ) ) {
			$text_typo_css .= 'letter-spacing:'. $text_letter_spacing .';';
		}

		// text typography css
		if ( ! empty( $text_typo_css ) ) {
			$css .= '#ocn-cookie-wrap .ocn-cookie-content{'. $text_typo_css .'}';
		}

		// Add button font family
		if ( ! empty( $button_font_family ) ) {
			$button_typo_css .= 'font-family:'. $button_font_family .';';
		}

		// Add button font size
		if ( ! empty( $button_font_size ) ) {
			$button_typo_css .= 'font-size:'. $button_font_size .';';
		}

		// Add button font weight
		if ( ! empty( $button_font_weight ) ) {
			$button_typo_css .= 'font-weight:'. $button_font_weight .';';
		}

		// Add button font style
		if ( ! empty( $button_font_style ) ) {
			$button_typo_css .= 'font-style:'. $button_font_style .';';
		}

		// Add button text transform
		if ( ! empty( $button_text_transform ) ) {
			$button_typo_css .= 'text-transform:'. $button_text_transform .';';
		}

		// Add button line height
		if ( ! empty( $button_line_height ) ) {
			$button_typo_css .= 'line-height:'. $button_line_height .';';
		}

		// Add button letter spacing
		if ( ! empty( $button_letter_spacing ) ) {
			$button_typo_css .= 'letter-spacing:'. $button_letter_spacing .';';
		}

		// button typography css
		if ( ! empty( $button_typo_css ) ) {
			$css .= '#ocn-cookie-wrap .ocn-btn{'. $button_typo_css .'}';
		}
			
		// Return CSS
		if ( ! empty( $css ) ) {
			$output .= $css;
		}

		// Return output css
		return $output;

	}

	/**
	 * Get allowed script blocking HTML.
	 *
	 * @since  1.0.0
	 */
	public function get_allowed_html() {
		return apply_filters(
			'ocn_refuse_code_allowed_html',
			array_merge(
				wp_kses_allowed_html( 'post' ),
				array(
					'script' => array(
						'type' 				=> array(),
						'src' 				=> array(),
						'charset' 			=> array(),
						'async' 			=> array()
					),
					'noscript' 				=> array(),
					'style' => array(
						'types' 			=> array()
					),
					'iframe' => array(
						'src' 				=> array(),
						'height' 			=> array(),
						'width' 			=> array(),
						'frameborder' 		=> array(),
						'allowfullscreen' 	=> array()
					)
				)
			)
		);
	}

	/**
	 * Check if cookies are accepted.
	 *
	 * @since  1.0.0
	 */
	public static function cookies_accepted() {
		return apply_filters( 'ocn_is_cookie_accepted', isset( $_COOKIE['ocn_accepted'] ) && $_COOKIE['ocn_accepted'] === 'true' );
	}

	/**
	 * Add head scripts.
	 *
	 * @since  1.0.0
	 */
	public function header_scripts() {
		if ( $this->cookies_accepted() ) {
			$scripts = apply_filters( 'ocn_header_scripts',
				html_entity_decode( trim( wp_kses( get_theme_mod( 'ocn_head_scripts' ), $this->get_allowed_html() ) ) )
			);

			if ( ! empty( $scripts ) ) {
				echo $scripts;
			}
		}
	}

	/**
	 * Add footer scripts.
	 *
	 * @since  1.0.0
	 */
	public function footer_scripts() {
		if ( $this->cookies_accepted() ) {
			$scripts = apply_filters( 'ocn_footer_scripts',
				html_entity_decode( trim( wp_kses( get_theme_mod( 'ocn_body_scripts' ), $this->get_allowed_html() ) ) )
			);

			if ( ! empty( $scripts ) ) {
				echo $scripts;
			}
		}
	}

} // End Class