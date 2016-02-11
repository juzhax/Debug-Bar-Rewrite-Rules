<?php

/***************************************************************************
Plugin Name: Debug Bar Rewrite Rules
Plugin URI: http://wordpress.org/extend/plugins/debug-bar-rewrite-rules
Description: Debug Bar Rewrite Rules adds a new panel to Debug Bar that displays information about rewrite rules
Author: Oleg Butuzov
Author URI: http://made.ua
Version: 1.0

Text Domain: debug-bar-rewrite-rules
Domain Path: /_locales
***************************************************************************/
	
	
if ( ! defined( 'ABSPATH' ) ) 
	exit;
	
 
 	
class Debug_Bar_Rewrite_Rules  {
 	
	var $_domain = 'debug-bar-rewrite-rules';
	var $_visible = true;
		
	function __construct(){
	 
		
		
		define('DBRR',		 		__CLASS__ );
			
		define('DBRR_URL',		 	plugin_dir_url(  __FILE__ ) );
		define('DBRR_DIR',		 	plugin_dir_path( __FILE__ ) );
	
		define('DBRR_JS_URL',		DBRR_URL .'_js/' );
		define('DBRR_JS_DIR',		DBRR_DIR .'_js/' );
		define('DBRR_JS_SUFFIX',	(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == true ? '.dev.js':'.js'));
		
		define('DBRR_CSS_URL',		DBRR_URL.'_css/' );
		define('DBRR_CSS_DIR',		DBRR_DIR.'_css/' );
		define('DBRR_CSS_SUFFIX',	(defined('SCRIPT_DEBUG') && SCRIPT_DEBUG == true ? '.dev.css':'.css'));
		
		define('DBRR_IMG_URL',		DBRR_URL .'_images/' );
		define('DBRR_IMG_DIR',		DBRR_DIR .'_images/' );
		
		
		define('DBRR_TPL_DIR',		plugin_dir_path( __FILE__ ).'_templates/');
		define('DBRR_TPL_URL',		plugin_dir_url ( __FILE__ ).'_templates/');
		
	   
		add_action( 'init', 	array( $this, 'init'));
		
		// catcher for rewrite rules
		add_action('generate_rewrite_rules', 	array($this, 'rewrite_rules_max'));
 		
		// ** in case if dbrr - installed as a plugin (default way)
		
		register_activation_hook(__FILE__,   	array($this, "plugin_install"));
		register_deactivation_hook(__FILE__,	array($this, "plugin_remove"));
	}
	
	
	function init(){
		
		load_plugin_textdomain( $this->_domain, false, basename(DBRR_DIR). '/_locales/' );
		
		if ( class_exists('Debug_Bar') ) {	
			
			$this->_title   = __('Rewrite Rules', $this->_domain);
			add_filter( 'debug_bar_panels', 	 array( $this, 'debug_bar_panels'  ), 20 );
			
			add_action('wp_ajax_DEBUG_BAR_REWRITE_RULES_ajax_flush_rewrite_rules', array($this, 'ajax_flush_rewrite_rules'));
			
		} else {
			
			$this->_title   = __('Debug Rewrite Rules', $this->_domain);
			
			add_action( 'admin_menu',	  						array(	$this, 'admin_menu' ), 30);
			add_action( 'wp_ajax_save_postboxes_settings_api',	array(	$this, 'ui_helper_save_postboxes' )  );
			add_action( 'admin_enqueue_scripts',				array(	$this, 'admin_enqueue_scripts' )  );
			add_action( 'admin_enqueue_scripts',				array(	$this, 'scripts_n_styles' )  );
		}
	}
	
	
	/***************************************************************************
	*** Admin Menu - Non Debug Bar UI ******************************************
	***************************************************************************/
    
	function admin_menu(){
		global $dbrr_hook;
		$dbrr_hook = add_management_page($this->_title , $this->_title, 'manage_options', 'permalinks', array($this, 'ui_pemalinks_view'));
		
		add_action('load-'.$dbrr_hook, array($this, 'ui_pemalinks_ui'), 1); 
	
		add_filter( "get_user_option_closedpostboxes_{$dbrr_hook}", 	array($this, 'ui_helper_mb_closed'));
		add_filter( "hidden_meta_boxes",								array($this, 'ui_helper_mb_hidden'), 10, 3);
		add_filter( "get_user_option_meta-box-order_{$dbrr_hook}", 		array($this, 'ui_helper_mb_order'));
 	}
	
	
	function admin_enqueue_scripts($s){
		global $dbrr_hook;
		
		if (isset($dbrr_hook) && $s == $dbrr_hook){
		 	wp_enqueue_script( 'customui.postboxes', 	DBRR_JS_URL . 'customui.postboxes' 	. DBRR_JS_SUFFIX,  array('jquery-ui-sortable'), false, true);
		}
		 
	}
	
	//*************************************************************************************************************/
	// This Plugin Helpers
	//*************************************************************************************************************/
 
	function rewrite_rules_max($object){
		global $wp_filter;
		
	//	add_filter('rewrite_rules_array',  array(&$this, 'rewrite_rules_filter'), @intval(max(array_keys($GLOBALS['wp_filter']['rewrite_rules_array'])))+1);
	//	add_filter('rewrite_rules_array',  array(&$this, 'rewrite_rules_catcher'), @intval(max(array_keys($GLOBALS['wp_filter']['rewrite_rules_array'])))+1);
	
		$rules = array('post','date','root','comments','search','author','page','tag');
	 
		foreach( array_merge( $rules, array_keys($object->extra_permastructs) ) as $name){
			$rules[] = "{$name}_rewrite_rules";
		}
		
		$rules[] = 'rewrite_rules_array';
		
	 
		 
		$filters = array('list' => array(), 'count' => 0);
		foreach($rules as $filter){
			if ( isset($wp_filter[$filter]) ){
				//$filters['list'][$filter] = $wp_filter[$filter];
				foreach($wp_filter[$filter] as $priority=>$callbacks){
			 		$filters['count'] += count($callbacks);
					foreach($callbacks as $_filter_callback){
					 	if (is_array($_filter_callback['function'])) {

							if (is_object($_filter_callback['function'][0])){
								$type  = array('object', '->');
								$class = '$'.get_class($_filter_callback['function'][0]);
							} else {
								$type  = array('static', '::');
								$class = $_filter_callback['function'][0];
							}

							$method = $_filter_callback['function'][1].'()';

							$callbackType =  $type[0] == 'static' ? 'static':'dynamic';//__('Static method', $this->_domain) : __('Dynamic method', $this->_domain);
							$callbackView = "{$class}{$type[1]}{$method}";
							// class -> method
							// class :: method


						} elseif ( is_string($_filter_callback['function']) && is_callable($_filter_callback['function']) 
									&& function_exists($_filter_callback['function']) 
									&& strpos($_filter_callback['function'], 'lambda_') === false){

							// simple function
							$callbackType = 'function';//__('Function', $this->_domain);
							$callbackView = $_filter_callback['function'];

						} 	elseif ( is_string($_filter_callback['function']) && is_callable($_filter_callback['function']) 
										&& function_exists($_filter_callback['function']) 
										&& strpos($_filter_callback['function'], 'lambda_') !== false){

							// lambda
							$callbackType = 'lambda'; //__('Lambda function', $this->_domain);
							$callbackView = "";

						} elseif (is_object($_filter_callback['function']) && get_class($_filter_callback['function']) == 'Closure'){

							$callbackType = 'anonymus';//__('Anonymous function', $this->_domain); 
							$callbackView = "";
			            } else {
							// in DEV ONLY!
							var_dump($_filter_callback['function']);
						}

						$filters['list'][$filter][$priority][] = array($callbackType, $callbackView);

					}
			
				}
			}
		}
	
		
		
		if (!add_option('rewrite_rules_filters_list',   $filters, '', 'no'))
			update_option('rewrite_rules_filters_list', $filters);
		
		return $object;
	}
	
	/***************************************************************************
	*** UI *********************************************************************
	***************************************************************************/
 
	
	
	function ui_pemalinks_ui(){
	 
		add_meta_box('rewrite_rules_array_stats', 	__( 'Stats',   $this->_domain), 
			array($this, '_helper_stats'),	 get_current_screen()->id, 'normal', 'high');
		
		add_meta_box('rewrite_rules_array_list', 	__( 'Rewrite Rules List',   $this->_domain), 
			array($this, '_helper_list_rules'),	 get_current_screen()->id, 'normal', 'core');
		
		add_meta_box('rewrite_rules_array_filters', __( 'WP Rewrite Rules Filters', $this->_domain), 
			array($this, '_helper_wp_filters'),  get_current_screen()->id, 'normal', 'core');
			
			
	//	add_meta_box('rewrite_rules_flush',		 	__( 'Actions', 		$this->_domain), 
	//		array($this, '_helper_flush_rewrite_rules'), 		 get_current_screen()->id, 'side');	
		 
		
		 
		add_meta_box('ui_pemalinks_ui_brought_by',	__( 'Made in Ukraine', 	$this->_domain), 
			array($this, 'ui_pemalinks_ui_donate'), 		 get_current_screen()->id, 'side');
			
		add_meta_box('rewrite_rules_donate',	 	__( 'Support Project', 		$this->_domain), 
			array($this, 'ui_pemalinks_ui_donate'), 		 get_current_screen()->id, 'side');
			
		add_meta_box('rewrite_rules_support',	 	__( 'Support Forum Messages', 		$this->_domain), 
			array($this, 'ui_pemalinks_ui_donate'), 		 get_current_screen()->id, 'side');	
	 	
	//	var_dump(get_current_screen());
	//	exit;
	}

	// empty for now
	function ui_pemalinks_save(){}
	function ui_pemalinks_head(){}
			
	function ui_pemalinks_view(){
		$this->template('page', $this->_title, array(), array());
	}
	
	// ready
	function _helper_stats(){
		$this->show('debug-bar/stats.php', array('rewrite_rules' => count(get_option('rewrite_rules')), 'filters' => get_option('rewrite_rules_filters_list')));
	}
	function _helper_list_rules(){
 		$this->show('debug-bar/rewrite_rules_array.php', array('rewrite_rules' => get_option('rewrite_rules')));
	}
	
	// ready
	function _helper_wp_filters(){
		$this->show('debug-bar/wp_filters.php', array('filters' => get_option('rewrite_rules_filters_list')));
	}
		

	
	function ui_pemalinks_ui_brought_by(){}
	function ui_pemalinks_ui_donate(){
		echo '<div style="padding:6px 9px 8px;">s</div>';
	}
	
	
	//*************************************************************************************************************/
	// AjaxAPI
	// - custom Ajax API Calls...
	// @ref - http://codex.wordpress.org/AJAX_in_Plugins
	//*************************************************************************************************************/
	
	// sent always text/html content type
	function ajax_header(){
		if (!headers_sent() && (!isset($_POST['request']) || $_POST['request'] != 'json') ){
			header("Content-Type: text/html; charset=utf-8");
		} else if ( isset($_POST['request']) && $_POST['request'] == 'json' ){
			header('Content-type: application/json; charset=utf-8');
		}
	}
	
 	function ajax_flush_rewrite_rules(){
		$return = array();
		
		if (!current_user_can('manage_options'))
			$return['message'] = __('You should be able to manage options to use this function');
		
		$GLOBALS['wp_rewrite']->flush_rules();
		
		$return['dbrr_rewrites_filters']	= $this->view('debug-bar/wp_filters-table.php', 			array('filters' 		=> get_option('rewrite_rules_filters_list')));
		$return['rewrite_rules_array']		= $this->view('debug-bar/rewrite_rules_array-table.php', 	array('rewrite_rules' 	=> get_option('rewrite_rules')));
		
		$filters = get_option('rewrite_rules_filters_list');
		$return['dbrr_rewrites_filters_n']	= $filters['count'];
		$return['rewrite_rules_array_n']	= count(get_option('rewrite_rules'));
		
		header('Content-type: application/json; charset=utf-8');
		die(json_encode($return));
	}
	
	// do not show the 0 or -1 (if method not exists)
	function ajax_end(){
		exit;
	}
	
	
	//*************************************************************************************************************/
	// Debug Bar Helpers
	//*************************************************************************************************************/

	function scripts_n_styles($s = false){
		wp_enqueue_style ( 'debug-bar-panel.'.DBRR, DBRR_CSS_URL. 'debug-bar-panel' 	. DBRR_CSS_SUFFIX, false,	false);
		wp_enqueue_script( 'debug-bar-panel.'.DBRR, DBRR_JS_URL . 'debug-bar-panel' 	. DBRR_JS_SUFFIX,  false,	false);
		
		$l10n = array(
			'trrf_h2' => __('Total Rewrite Rules Filters', $this->_domain)
		);
		wp_localize_script('debug-bar-panel.'.DBRR, DBRR.'_l10n',  $l10n);
		
		wp_enqueue_script( 'jquery.helpers' , 	 	DBRR_JS_URL . 'jquery.helpers' 		. DBRR_JS_SUFFIX,  array('jquery'), false, true);
	}
	
	function debug_bar_panels($panels){
		add_action(is_admin() ? 'admin_enqueue_scripts' : 'wp_head',	array( &$this, 'scripts_n_styles'), 1);
		$panels[] = $this;
		return $panels;
	}
	
 

	
 	function is_visible(){
		return $this->_visible;
	}
	function set_visible( $visible ) {
		$this->_visible = $visible;
	}
	function title(){
		return $this->_title;
	}
	function render() {
	 	global $is_IE;
	
		if ( get_option('permalink_structure') == "" ) {
			
			printf('<h2>%s</h2>', __('You should anable Permalinks before debug it ', $this->_domain));
			return false;
			
		}	else {
			
			$ieClass = ( $is_IE && preg_match('/MSIE (\d+)/si', $_SERVER['HTTP_USER_AGENT'], $m) ) 
				? ($m[0] <= 7 ? 'dbrr_lteie7' : '' ) : ''; 
			 	  
			 
			
			echo '<div id="dbrr" class="'.(is_admin()?'':'front').' '.$ieClass.'">';
				$this->_helper_stats();
				$this->_helper_list_rules();
				$this->_helper_wp_filters();
			echo '</div>';
		}
			
	 
	}
	function prerender() {}
    
	/*************************************************************************************************************/
	// Meta Boxes of Admin UI
	//*************************************************************************************************************/		
    
    
    
	// admin_menu helper
	// @group - ajax
	// - ajax callback for saving posts.
	function ui_helper_save_postboxes(){
		$POST = stripslashes_deep($_POST);
    
		$settings = $this->settings_user_get('page_settings_'.$POST['screen']);
		$settings = is_array($settings) ? $settings : array();
    
		$toSave['hidden'] = explode(',', $POST['hidden']);
		$toSave['closed'] = explode(',', $POST['closed']);
		$toSave['order']  =  $POST['sortings'];
    
    
		$this->settings_user_update('page_settings_'.$POST['screen'], $toSave);
		exit;
	}
    
	// admin_menu helper
	// - filter for hidden meta boxes of admin ui
	function ui_helper_mb_hidden($hidden, $screen, $use_defaults ){
		global $page_hook;
		$option = $this->settings_user_get("page_settings_".$screen->id);
    
		if (isset($option['hidden']))
	 		return $option['hidden'];
    
		return $hidden;
	}
    
	// admin_menu helper
	// - filter for closed meta boxes of admin ui	
	function ui_helper_mb_closed($value){
		global $page_hook;
		$option = $this->settings_user_get("page_settings_{$page_hook}");
    
	 	if (isset($option['closed'])){
			return  $option['closed'];
		}
    
		return $value;
	}
    
	// admin_menu helper
	// - filter for meta boxes order of admin ui	
	function ui_helper_mb_order($value){
		global $page_hook;
    
		$option = $this->settings_user_get("page_settings_{$page_hook}");
		if (isset($option['order'])){
			return  $option['order'];
		}
    
		return $value;
	}



	//*************************************************************************************************************/
	// User Settings API  
	//  *  Class Wrapers For Settings API
	//  @version 1
	//*************************************************************************************************************/

	// update user settings
	function settings_user_update($setting, $data){
		if (function_exists('update_user_option')){
			return update_user_option(get_current_user_id(), $setting, $data);
		}  
	}

	// retrive user settings
	function settings_user_get($setting){
	 	if (function_exists('get_user_option')){
			return  get_user_option($setting, get_current_user_id());
		} 
	}

	// delte user settings	
	function settings_user_delete($setting){
		if (function_exists('delete_user_option')){
			return  delete_user_option(get_current_user_id(), $setting);
		}
	}


	//*************************************************************************************************************/
	// Code Helpers 
	// {--
	//*************************************************************************************************************/
    function code_css($code, $echo = true){
		$string  = '';
		$string .= '<style type="text/css">'.PHP_EOL;
		$string .=  $code.PHP_EOL;
		$string .= '</style>'.PHP_EOL;
		if ($echo){
			echo $string;
		} else {
			return $string;
		}
	}
    function code_js ($code, $echo = true){
		$string  = '';
		$string .= '<script type="text/javascript">'.PHP_EOL;
		$string .= '//<![CDATA['.PHP_EOL.PHP_EOL;
		$string .=  $code.PHP_EOL;
		$string .= '//]]>'.PHP_EOL;
		$string .= '</script>'.PHP_EOL;
    
		if ($echo){
			echo $string;
		} else {
			return $string;
		}
	}	

	//*************************************************************************************************************/
	// }
	//*************************************************************************************************************/
	
	//*************************************************************************************************************/
	// Template Helpers
	//*************************************************************************************************************/
    
	// shortcode to show admin notice 
	function notice($message, $class = 'updated'){
		return '<div class="'.$class.'"><p>'.$message.'</p></div>';
	}
    
    
	/*
	** Basic Template Action 
	** @example - self::show($tempalte, array());
	** @example - self::show('sample.php', array('variable1' => 1, 'variable2' => 2));
	** @return void
	** @uses view
	*/
	function show( $template, $array = array() ){
		echo self::view($template, $array);
	}
    
	/*
	** Basic Template Action 
	** @example - self::view($tempalte, array());
	** @example - self::view('sample.php', array('variable1' => 1, 'variable2' => 2));
	** @return template contents or error message
	** @used show
	*/
	function view( $template, $array = array() ){
    
		$template = !file_exists($template) ? DBRR_TPL_DIR.$template : $template;
    
		$array['__DATA'] = array(
			'object'=> $this,
			'class' => __CLASS__, 
			'css' 	=> DBRR_CSS_SUFFIX, 
			'js' 	=> DBRR_JS_SUFFIX);
    
		if ( !file_exists( $template ) ){
			$content = '<h2>Template '.basename($template).' not found at '.dirname($template).'</h2>';
		} else {
			ob_start();
			extract( $array );
			include $template;
			$content = ob_get_contents();
			ob_end_clean();
		}
		return $content;
	}
    
    
	/*
	** Quick Include the tempalte.
	** @used show
	*/
	function template($view, $title, $data = array(), $args = array()){
		$this->ph($title);
		$this->_tpl('tpl', $view,  $data, $args);
		$this->pf();
	}			


	/*
	** Template Handler
	** @params type			      - $_POST['type']
	** @params handler (optional) - $_POST['handler']
	*/
	function _tpl($type, $handler, $data, $args){
		$INCLUDES = $type.'/'.$handler.'/index';
    
		if (file_exists( DBRR_TPL_DIR . $INCLUDES . DBRR_JS_SUFFIX ) ){
			wp_enqueue_script(str_replace("/","-",$type.'-'.$handler),  DBRR_TPL_URL . $INCLUDES . DBRR_JS_SUFFIX, false, false, true );
		}
    
		if (file_exists( DBRR_TPL_DIR . $INCLUDES . DBRR_CSS_SUFFIX )){
			 wp_enqueue_style( str_replace("/","-", $type.'-'.$handler), DBRR_TPL_URL . $INCLUDES . DBRR_CSS_SUFFIX, false, false );
		}
    
		if (file_exists(  DBRR_TPL_DIR . $INCLUDES .'.php')){
			$this->show(  DBRR_TPL_DIR . $INCLUDES .'.php', array('args' => $args, 'data' => $data));
		}
	}

	/*
	** Page Header
	*/
	function ph($t){
		echo '<div class="wrap">';
		echo '<div id="icon-tools" class="icon32"><br/></div>';
		echo '<h2>'.$t.'</h2><br/>';
	}

	/*
	** Page Footer
	*/
	function pf(){
		echo '</div>';
	}


	//*************************************************************************************************************/
	// Activate / Deactivate Helpers
	//*************************************************************************************************************/

	
	/*
	** Activation Hook for ::NAME::
	** @ref - http://codex.wordpress.org/Function_Reference/register_deactivation_hook
	*/
    
	function plugin_install(){
    	
		if (!get_option('rewrite_rules_filters_list')){
			global $wp_rewrite;
			$wp_rewrite->flush_rules();
		}
		
	}
    
    
	/*
	** Deactivation Hook for ::NAME::
	** @ref - http://codex.wordpress.org/Function_Reference/register_deactivation_hook
	*/
	function plugin_remove(){
    	delete_option('rewrite_rules_filters_list');
	}

	
}	

new Debug_Bar_Rewrite_Rules();


