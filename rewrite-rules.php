<?php
/**
 * Debug Bar Rewrite Rules. Actuall plugin.
 *
 * @package     WordPress\Plugins\Debug Bar Rewrite Rules
 * @author      Oleg Butuzov
 * @link        https://github.com/butuzov/wp-debug-bar-rewrite-rules
 * @version     0.3
 * @license     http://creativecommons.org/licenses/GPL/2.0/ GNU General Public License, version 2 or higher
 *
 * @wordpress-plugin
 * Plugin Name:	Debug Bar Rewrite Rules
 * Plugin URI:	https://github.com/butuzov/wp-debug-bar-rewrite-rules
 * Description:	Debug Bar Rewrite Rules helps to debug WP Rewrite Rules with and without of the help of the Debug Bar.
 * Version:     0.3
 * Author:      Oleg Butuzov
 * Author URI:  https://github.com/butuzov
 * Depends:     Debug Bar
 * Text Domain: debug-bar-rewrite-rules
 * Domain Path: /languages/
 */

// Avoid direct calls to this file.
if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}


if ( ! function_exists( 'umdbrr_activate' ) ) {

	/**
	 * Activate function.
	 *
	 * @return void
	 */
	function umdbrr_activate() {
		if ( '' !== get_option( 'permalink_structure' ) ) {
			new UA_Made_Rewrite_Rules();
			flush_rewrite_rules();
		}

		update_option( 'debug_bar_rewrite_rules_installed', 1 );
	}

	// Registring Activation hook.
	register_activation_hook( __FILE__, 'umdbrr_activate' );
}

if ( ! function_exists( 'umdbrr_deactivate' ) ) {

	/**
	 * Uninstall/Deactivate function.
	 *
	 * On Uninstall/Deactivate action - removing settings/options.
	 *
	 * @return void
	 */
	function umdbrr_deactivate() {
		delete_option( 'debug_bar_rewrite_rules_filters_list' );
		delete_option( 'debug_bar_rewrite_rules_installed' );
	}

	// Deactivation or Uninstall.
	foreach ( array( 'deactivation', 'uninstall' ) as $hook ) {
		call_user_func( sprintf( 'register_%s_hook', $hook ), __FILE__, 'umdbrr_deactivate' );
	}
}


/**
 * 	Basic DBRR class.
 */
class UA_Made_Rewrite_Rules {

	const DBRR_STYLES_VERSION = '1.0';
	const DBRR_SCRIPTS_VERSION = '1.0';

	const DBRR_NAME = 'debug-bar-rewrite-rules';

	/**
	 * Single instance
	 *
	 * @var $instance Class Instance.
	 */
	private static $instance;

	/**
	 * Plugin Title.
	 *
	 * @var $title Plugin Title.
	 */
	public $title ;


	/**
	 * Get Instance.
	 */
	public static function i() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new UA_Made_Rewrite_Rules();
		}
		return self::$instance;
	}

	/**
	 * Class Cosntructor.
	 */
	public function __construct() {
		global $wpdb;

		// Running code as Singleton Pattern.
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS , 2 );
		$_is_security_class  = ! empty( $trace[1]['class'] ) && __CLASS__ === $trace[1]['class'];
		$_is_instance_method = ! empty( $trace[1]['function'] ) && 'i' === $trace[1]['function'];

		if ( ! $_is_security_class || ! $_is_instance_method ) {
			return self::i();
		}

		// Setting up a tracker.
		add_action( 'generate_rewrite_rules', array( $this, 'track_generate_rewrite_rules' ) );

		// Adding panel to Debug Bar
		add_filter( 'debug_bar_panels', array( $this, 'debug_bar_panel' ) );

		// Adding init method.
		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Init action.
	 *
	 * @return void
	 */
	function init() {
		$this->initialize();

		// In case if debug bar not available,
		// we will have own admin page in 'tools' section.
		if ( ! class_exists( 'Debug_Bar' ) ) {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
		}

		// Flush rules ajax action.
		add_action( 'wp_ajax_debug_bar_rewrite_rules', array( $this, 'ajax' ) );
	}

	function initialize(){
		if ( empty( $this->initialized ) ) {
			$this->initialized = true;

			// Localization loading.
			// That's sad but `load_plugin_textdomain` doesn't looks like it working, so we use other way.
			load_textdomain( self::DBRR_NAME, plugin_dir_path( __FILE__ ) . 'languages/' . self::DBRR_NAME . '-' . get_locale() . '.mo' );

			// Translations for title/panel title and "page title".
			$this->title   = __( 'Rewrite Rules', 'debug-bar-rewrite-rules' );
			$this->pagetitle = __( 'WordPress Rewrite Rules Inspection', 'debug-bar-rewrite-rules' );
		}
	}

	/**
	 * Debug Bar Rewrite Rules as WordPress Admin Page (in case if debug bar not available).
	 *
	 * @return void
	 */
	function admin_menu() {
		$this->hook = add_management_page( $this->pagetitle, $this->title, 'manage_options', 'rewrite-rules', array( $this, 'view' ) );
	}


	/**
	 * A Wordpress admin tool page 'view'.
	 *
	 * @return void
	 */
	function view() {
		echo // WPCS: XSS OK.
		 	'<div class="wrap debug-bar-rewrites-urls">',
			sprintf( '<h2>%s</h2>', $this->pagetitle ),
			$this->stats(),
			$this->rules(),
			$this->filters(),
			'</div>';
	}


	/**
	 * Adds panel, as defined in the included class, to Debug Bar.
	 *
	 * @param array $panels Existing debug bar panels.
	 *
	 * @return array
	 */
	function debug_bar_panel( $panels ) {

		// Initializing Settings.
		$this->initialize();

		// Initializing Panel.
		if ( ! class_exists( 'Debug_Bar_Rewrite_Rules_Panel' ) ) {
			require_once   'debug-bar.php';

			add_action( 'wp_enqueue_scripts', array( $this, 'assets') );

			$panels[] = new Debug_Bar_Rewrite_Rules_Panel( $this );
		}

		return $panels;
	}


	/**
	 * Loading assets
	 *
	 * Load styles and scripts for backend and frontend.
	 *
	 * @param  string $hook_suffix The dynamic portion of the hook, `$hook_suffix`, refers to the hook suffix for the admin page.
	 *
	 * @return void
	 */
	function assets( $hook_suffix = '' ) {

		$is_debug_bar = class_exists( 'Debug_Bar' ) && is_admin_bar_showing();

		if ( ( ! empty( $this->hook ) && $hook_suffix === $this->hook ) || $is_debug_bar ) {

			$file_suffix = ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min' );

			wp_enqueue_style(
				self::DBRR_NAME ,
				plugins_url( 'css/' . self::DBRR_NAME . $file_suffix . '.css', __FILE__ ),
				array(),
				self::DBRR_STYLES_VERSION,
				'all'
			);

			wp_register_script(
				self::DBRR_NAME,
				plugins_url( 'js/' . self::DBRR_NAME . $file_suffix . '.js', __FILE__ ),
				array( 'jquery' ),
				self::DBRR_SCRIPTS_VERSION,
				true
			);

			wp_localize_script( self::DBRR_NAME, 'debugBarRewriteRules', array(
				'nonce'     => wp_create_nonce( 'debug-bar-rewrite-rules-nonce' ),
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'validator' => plugins_url( 'validator.php', __FILE__ ),
				'home'	    => trailingslashit( get_home_url() ),
				'matches'	=> __( 'Matches', 'debug-bar-rewrite-rules' ),
			) );

			wp_enqueue_script( self::DBRR_NAME );

		}
	}

	/**
	 * Template functionality.
	 *
	 * This is a simple implementation of template functionality for WordPress.
	 *
	 * @param string $template	Template location in ffile system.
	 * @param array  $data 		Array of additional parameters used in tempalte.
	 */
	function template( $template, $data = array() ) {

		$content = ''; // Initializing empty variable.

		if ( ! file_exists( __DIR__ . '/' . $template ) && WP_DEBUG === true ) {

			$data = pathinfo( $template );
			$content = sprintf( '<h2>Template <em  class="debug">%s</em> not found at <em class="debug">%s</em></h2>', $data['basename'], $data['dirname'] );

		} elseif ( file_exists( __DIR__ . '/' . $template ) ) {

			ob_start();
			include __DIR__ . '/' . $template;
			$content = ob_get_contents();
			ob_end_clean();
		}

		return $content;
	}

	/**
	 * Info panel for Rewrite Rules.
	 *
	 * @return string
	 */
	function rules() {

		$domain = trailingslashit( get_home_url() );
		return $this->template( '/templates/info-rules.php', array(
			'rewrite_rules' => get_option( 'rewrite_rules' ),
			'i' => 0,
			'domain' => $domain,
			'width' => ( strlen( $domain ) * 8 ) . ' px',
		) );
	}

	/**
	 * Info panel for Available Hooks and Filters that affects rewrite rules.
	 *
	 * @return string
	 */
	function filters() {
		$stats = get_option( 'debug_bar_rewrite_rules_filters_list' );
		$data = array( 'filters' => array() );

		$stats['list'] = is_array( $stats['list'] ) && ! empty( $stats['list'] ) ? $stats['list'] : array();

		foreach ( $stats['list'] as $filter ) {

			$data['filters'][ $filter ] = array( 'rowcount' => 0 , 'filters' => array() );


			if ( empty( $stats['details'][ $filter ] ) ) {
				$data['filters'][ $filter ]['rowcount']++;
			} else {
				foreach ( $stats['details'][ $filter ] as $priority => $callbacks ) {
					foreach ( $callbacks as  $callback ) {
						$data['filters'][ $filter ]['rowcount']++;
						$data['filters'][ $filter ]['filters'][ $priority ][] = $callback;
					}
				}
			}
		}

		$data['l10n'] = array(
			// Functions.
			'function'	=> __( 'Function', 'debug-bar-rewrite-rules' ),
			'anonymus'	=> __( 'Anonymous lambda function', 'debug-bar-rewrite-rules' ),
			'closure'	=> __( 'Closure anonymous function', 'debug-bar-rewrite-rules' ),

			// Classes.
			'dynamic'	=> __( 'Dynamic method', 'debug-bar-rewrite-rules' ),
			'static'	=> __( 'Static method', 'debug-bar-rewrite-rules' ),
		);

		return $this->template( '/templates/info-filters.php', $data ); // WPCS: XSS OK.
	}

	/**
	 * Will print Debug-Bar styled stats for rewrite rules.
	 *
	 * @return string
	 */
	function stats() {

		$stats = get_option( 'debug_bar_rewrite_rules_filters_list' );
		$rules = get_option( 'rewrite_rules' );

		$data = array(
			'count_rules'			=> is_array( $rules ) ? count( $rules ) : 0,
			'count_filters'			=> count( $stats['list'] ),
			'count_filters_hooked'	=> empty( $stats['details'] ) ? 0 : $stats['count'],
		);

		return $this->template( '/templates/info-stats.php', $data ); // WPCS: XSS OK.
	}

	/**
	 * Ajax action - Reseting data.
	 *
	 * @return void
	 */
	function ajax() {

		$return = array();

		if ( function_exists( 'filter_input' ) ) {
			$nonce = filter_input( INPUT_POST, 'nonce' );
		} else {
			$inputs = array_map( 'sanitize_text_field' , array_map( 'wp_unslash' , $_POST ) ); // input var, CSRF.
			$nonce = ! empty( $inputs['nonce'] ) ? $inputs['nonce'] : null ;
		}

		if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( $nonce, 'debug-bar-rewrite-rules-nonce' ) ) {
			wp_die( wp_json_encode( array() ) );
		}

		global $wp_rewrite;
		$wp_rewrite->flush_rules();

		$stats = get_option( 'debug_bar_rewrite_rules_filters_list' );

		$results = array(
			'filters' => $this->filters(),
			'rules' => $this->rules(),
			'count_rules'	 => count( get_option( 'rewrite_rules' ) ),
			'count_filters'  => count( $stats['list'] ),
			'count_filters_hooked' => empty( $stats['details'] ) ? 0 : $stats['count'],
		);

		header( 'Content-type: application/json; charset=utf-8' );
		wp_die( wp_json_encode( $results ) );
	}


	// ************************************************************************************************************/
	// Tracking Filters
	// ************************************************************************************************************/


	/**
	 * Tracking all alterations of the rewrites rules by tracking all filters.
	 *
	 * @param object WP_Rewrite $wp_rewrite  WP Rewrite Object.
	 * @return void
	 */
	function track_generate_rewrite_rules( $wp_rewrite ) {
		// Current wp filters used atm.
		global $wp_filter;

		// List of native filters.
		$native = array(
			'post' => 'post',
			'date' => 'date',
			'root' => 'root',
			'comments' => 'comments',
			'search' => 'search',
			'author' => 'author',
			'page' => 'page',
			'tag' => 'post_tag',
		);

		foreach ( array_keys( $wp_rewrite->extra_permastructs )  as $name ) {
			if ( ! in_array( $name, $native, true ) ) {
				$rules[] = sprintf( '%s_rewrite_rules', $name );
			}
		}

		// Additional loop for native permanent structure.
		foreach ( array_keys( $native )  as $name ) {
			$rules[] = sprintf( '%s_rewrite_rules', $name );
		}

		// Final rule.
		$rules[] = 'rewrite_rules_array';

		$stat['rules'] = $rules;

		// FIlters array initiation.
		$stat = array(
			'list' => $stat['rules'], 	// Filters available.
			'count' => 0,				// Total filters actions.
			'details'  => array(), 		// Actions / Filters that changing rewrite rules.
		);

		foreach ( $rules as $filter ) {

			if ( ! empty( $wp_filter[ $filter ] ) ) {
				foreach ( $wp_filter[ $filter ] as $priority => $callbacks ) {

					// Incrementing counter.
					$stat['count'] += count( $callbacks );

					// Going thought callbacks.
					foreach ( $callbacks as $callback ) {

						$is_string = is_string( $callback['function'] );
						$is_array  = is_array( $callback['function'] );
						$is_object = is_object( $callback['function'] );

						$is_lambda = $is_string && strpos( $callback['function'], 'lambda_' ) !== false;
						$is_callable = is_callable( $callback['function'] );

						$function_exists = $is_string && function_exists( $callback['function'] );

						if ( $is_array ) {
							if ( is_object( $callback['function'][0] ) ) {

								// Method of class object called.
								$types  = array( 'object', '->' );
								$class = '$' . get_class( $callback['function'][0] );

							} else {

								// Static method of class called.
								$types  = array( 'static', '::' );
								$class = $callback['function'][0];

							}

							$method = $callback['function'][1] . '()';
							$type = 'static' === array_shift( $types ) ? 'static' : 'dynamic';
							$view = sprintf( '%s%s%s', $class, array_shift( $types ), $method );

						} elseif ( $is_string && $is_callable && $function_exists ) {

							if ( $is_lambda ) {

								// Lambda function call.
								$type = 'anonymus';
								$view  = '';

							} else {

								// Real Function Call.
								$type = 'function' ;
								$view = $callback['function'];
							}

						} elseif ( $is_object && 'Closure' === get_class( $callback['function'] ) ) {

							// Anonymos function call.
							$type = 'closure';
							$view = '';
						}


						$stat['details'][ $filter ][ $priority ][] = array( $type, $view );

					}
				}
			}
		}

		update_option( 'debug_bar_rewrite_rules_filters_list', $stat );
		return $wp_rewrite;
	}
}

new UA_Made_Rewrite_Rules();
