<?php
/**
 * Plugin Name: Artesans Search Redirect
 * Plugin URI: https://github.com/Artesans/Artesans-Search-Redirect
 * Description: Simple search redirection plugin. Allows to select some search terms that can be redirected to a custom url.
 * Text Domain: artesans-plugin-redirect
 * Author: Artesans
 * Author URI: https://www.artesans.eu
 * Version: 1.1
 * License: GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // exit if accessed directly!
}

class Artesans_Search_Redirect {
	
	var $path; // path to plugin dir
	var $artesans_plugin_name; // friendly name of this plugin for re-use throughout
	var $artesans_plugin_menu; // friendly menu title for re-use throughout
	var $artesans_plugin_slug; // slug name of this plugin for re-use throughout
	var $artesans_plugin_ref;  // reference name of the plugin for re-use throughout
	
	function __construct(){		
		$this->path = plugin_dir_path( __FILE__ );
		
		$this->artesans_plugin_name = "Artesans Search Redirect";
		$this->artesans_plugin_menu = "Artesans Search Redirect";
		$this->artesans_plugin_slug = "artesans-search-redirect";
		$this->artesans_plugin_ref = "artesans_search_redirect";
		
		add_action( 'plugins_loaded', array($this, 'setup_plugin') );
		add_action( 'admin_notices', array($this,'admin_notices'), 11 );
		add_action( 'network_admin_notices', array($this, 'admin_notices'), 11 );		
		add_action( 'admin_init', array($this,'register_settings_fields') );		
		add_action( 'admin_menu', array($this,'register_settings_page'), 20 );
		add_action( 'admin_enqueue_scripts', array($this, 'admin_assets') );

		

		add_action( 'admin_print_footer_scripts', array($this, 'add_javascript'), 100 );

	}

	function add_javascript(){
		echo "<script>jQuery(document).ready(function($){
			$('[data-slug=\"arteans-search-redirect\"] .row-actions').append('<span> | <a href=\"".admin_url()."options-general.php?page=artesans_search_redirect\">Settings</span>');
		});
		</script>";
		return;
	}
	
	/*********************************
	 * NOTICES & LOCALIZATION
	 */
	 
	 function setup_plugin(){
	 	load_plugin_textdomain( $this->artesans_plugin_slug, false, $this->path."lang/" ); 
	 }
	
	function admin_notices(){
		$message = '';	
		if ( $message != '' ) {
			echo "<div class='updated'><p>$message</p></div>";
		}
	}

	function admin_assets($page){
	 	wp_register_style( $this->artesans_plugin_slug, plugins_url("css/ns-custom.css",__FILE__), false, '1.0.0' );
	 	wp_register_script( $this->artesans_plugin_slug, plugins_url("js/ns-custom.js",__FILE__), false, '1.0.0' );
		if( strpos($page, $this->artesans_plugin_ref) !== false  ){
			wp_enqueue_style( $this->artesans_plugin_slug );
			wp_enqueue_script( $this->artesans_plugin_slug );
		}		
	}
	
	/**********************************
	 * SETTINGS PAGE
	 */
	
	function register_settings_fields() {
		
		add_settings_section( 
			$this->artesans_plugin_ref.'_set_section', 	// ID used to identify this section and with which to register options
			$this->artesans_plugin_name, 					// Title to be displayed on the administration page
			false, 									// Callback used to render the description of the section
			$this->artesans_plugin_ref 					// Page on which to add this section of options
		);
		
		add_settings_field( 
			$this->artesans_plugin_ref.'_field1', 	// ID used to identify the field
			'Setting Name', 					// The label to the left of the option interface element
			array($this,'show_settings_field'), // The name of the function responsible for rendering the option interface
			$this->artesans_plugin_ref, 				// The page on which this option will be displayed
			$this->artesans_plugin_ref.'_set_section',// The name of the section to which this field belongs
			array( 								// args to pass to the callback function rendering the option interface
				'field_name' => $this->artesans_plugin_ref.'_field1'
			)
		);
		register_setting( $this->artesans_plugin_ref, $this->artesans_plugin_ref.'_field1');
		
	}	

	function show_settings_field($args){
		$saved_value = get_option( $args['field_name'] );
		// initialize in case there are no existing options
		if ( empty($saved_value) ) {
			echo '<input type="text" name="' . $args['field_name'] . '" placeholder="Setting Value" /><br/>';
		} else {
			echo '<input type="text" name="' . $args['field_name'] . '" value="'.$saved_value.'" /><br/>';
		}
		
	}

	function register_settings_page(){
		add_submenu_page(
			'options-general.php',								// Parent menu item slug	
			__($this->artesans_plugin_name, $this->artesans_plugin_name),	// Page Title
			__($this->artesans_plugin_menu, $this->artesans_plugin_name),	// Menu Title
			'manage_options',									// Capability
			$this->artesans_plugin_ref,								// Menu Slug
			array( $this, 'show_settings_page' )				// Callback function
		);
	}
	
	function show_settings_page() { ?>
		<div class="wrap">
			<!-- BEGIN Left Column -->
			<div> <!-- .ns-col-left -->
				<form name="artesans_settings_redirect_form" id="artesans_settings_redirect_form" method="POST" action="options.php" style="width: 100%;">
					<?php settings_fields($this->artesans_plugin_ref); ?>

					<?php do_settings_sections($this->artesans_plugin_ref); ?>
					<p><?php echo __('Add terms in the left box, separated by comma. Do NOT write spaces before and after every comma. 
					Every time a user do a search in each term or expression from the left box, will be redirected to the url address on the right. 
					Accents, case and special characters will be ignored. Enjoy it!',$this->artesans_plugin_slug );?></p>

					<?php submit_button(); ?>
				</form>
				
				<table id="search_redirections_table" width="100%">
    
				</table>

				<p>
					<button id="save" class="btn btn-save"><span class="dashicons dashicons-yes"></span> <?php echo __('Save');?></button>
					<button id="add" class="btn btn-add"><span class="dashicons dashicons-plus"></span> <?php echo __('Add');?></button>
				</p>
			</div>
		</div>

		<div class="artesans-logo">
			<svg width="100%" height="100%" viewBox="0 0 105 37" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" xml:space="preserve" xmlns:serif="http://www.serif.com/" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:1.41421;">
				<g transform="matrix(1,0,0,1,-4267.74,-736.376)">
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1032.86,746.057)">
							<path d="M0,2.333C-0.804,2.653 -1.205,3.176 -1.205,3.901C-1.205,4.472 -1.046,4.873 -0.726,5.106C-0.406,5.34 0.082,5.456 0.738,5.456C1.187,5.456 1.637,5.336 2.086,5.094C2.535,4.852 2.911,4.506 3.214,4.057C3.516,3.607 3.667,3.106 3.667,2.553L3.667,1.854C2.025,1.854 0.804,2.014 0,2.333M3.11,-2.579C2.738,-2.941 2.138,-3.123 1.309,-3.123C0.825,-3.123 0.41,-3.041 0.064,-2.877C-0.281,-2.713 -0.545,-2.51 -0.726,-2.268C-0.907,-2.025 -0.998,-1.802 -0.998,-1.594L-0.998,-1.387L-4.653,-1.387C-4.687,-1.594 -4.705,-1.749 -4.705,-1.854C-4.705,-2.596 -4.458,-3.262 -3.966,-3.849C-3.474,-4.437 -2.787,-4.895 -1.905,-5.223C-1.024,-5.551 -0.014,-5.715 1.127,-5.715L1.672,-5.715C3.538,-5.715 4.954,-5.348 5.923,-4.613C6.89,-3.879 7.374,-2.873 7.374,-1.594L7.374,4.523C7.374,4.834 7.447,5.067 7.595,5.223C7.741,5.379 7.918,5.456 8.126,5.456C8.298,5.456 8.488,5.422 8.696,5.353C8.903,5.284 9.067,5.223 9.188,5.171L9.188,7.504C8.549,7.867 7.789,8.048 6.907,8.048C5.957,8.048 5.231,7.854 4.73,7.465C4.229,7.076 3.936,6.528 3.849,5.819C2.639,7.306 1.153,8.048 -0.609,8.048C-1.923,8.048 -3.003,7.78 -3.85,7.245C-4.696,6.709 -5.119,5.689 -5.119,4.187C-5.119,2.959 -4.77,2.021 -4.069,1.374C-3.37,0.726 -2.397,0.29 -1.153,0.064C0.091,-0.159 1.697,-0.272 3.667,-0.272L3.667,-1.153C3.667,-1.74 3.481,-2.216 3.11,-2.579" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1050.71,749.051)">
							<path d="M0,-3.914C0,-4.449 -0.146,-4.912 -0.44,-5.301C-0.734,-5.689 -1.201,-5.884 -1.84,-5.884C-2.445,-5.884 -2.907,-5.646 -3.227,-5.171C-3.547,-4.696 -3.706,-4.104 -3.706,-3.395L-3.706,4.795L-7.438,4.795L-7.438,-8.45L-4.64,-8.45L-4.173,-6.506C-4.104,-6.696 -3.927,-6.964 -3.642,-7.31C-3.356,-7.655 -2.941,-7.975 -2.397,-8.268C-1.853,-8.562 -1.209,-8.709 -0.466,-8.709C0.727,-8.709 1.646,-8.338 2.294,-7.595C2.942,-6.852 3.267,-5.867 3.267,-4.64C3.267,-4.381 3.253,-4.121 3.228,-3.862C3.201,-3.603 3.18,-3.422 3.162,-3.318L0,-3.318L0,-3.914Z" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1061.11,736.519)">
							<path d="M0,17.443C-0.562,17.538 -1.059,17.586 -1.49,17.586C-2.82,17.586 -3.822,17.3 -4.497,16.731C-5.171,16.161 -5.508,15.211 -5.508,13.88L-5.508,6.673L-6.959,6.673L-6.959,4.082L-5.378,4.082L-4.056,-0.143L-1.724,-0.143L-1.724,4.082L1.361,4.082L1.361,6.673L-1.724,6.673L-1.724,13.231C-1.724,13.922 -1.59,14.389 -1.321,14.631C-1.054,14.874 -0.695,14.994 -0.246,14.994C-0.038,14.994 0.229,14.96 0.558,14.89C0.886,14.822 1.154,14.744 1.361,14.657L1.361,17.093C1.016,17.232 0.562,17.348 0,17.443" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1068.32,750.708)">
							<path d="M0,-6.971C-0.476,-6.436 -0.748,-5.753 -0.816,-4.924L4.419,-4.924C4.419,-5.805 4.216,-6.5 3.81,-7.01C3.403,-7.52 2.751,-7.775 1.853,-7.775C1.093,-7.775 0.475,-7.507 0,-6.971M7.399,1.102C6.847,1.889 6.117,2.467 5.21,2.84C4.303,3.211 3.313,3.396 2.242,3.396L1.827,3.396C0.6,3.396 -0.506,3.137 -1.491,2.619C-2.476,2.1 -3.249,1.353 -3.811,0.377C-4.372,-0.599 -4.653,-1.761 -4.653,-3.11L-4.653,-3.861C-4.653,-5.192 -4.368,-6.349 -3.798,-7.334C-3.228,-8.32 -2.454,-9.071 -1.478,-9.589C-0.502,-10.108 0.6,-10.367 1.827,-10.367L2.242,-10.367C3.313,-10.367 4.307,-10.147 5.223,-9.706C6.139,-9.265 6.872,-8.626 7.426,-7.787C7.979,-6.95 8.255,-5.96 8.255,-4.82L8.255,-2.85L-0.816,-2.85L-0.816,-2.228C-0.816,-1.364 -0.562,-0.643 -0.052,-0.064C0.457,0.515 1.153,0.804 2.034,0.804C2.829,0.804 3.455,0.585 3.913,0.143C4.371,-0.297 4.601,-0.95 4.601,-1.814L8.229,-1.814C8.229,-0.656 7.952,0.316 7.399,1.102" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1078.99,743.917)">
							<path d="M0,6.611C0,7.388 0.345,7.98 1.036,8.386C1.728,8.793 2.695,8.995 3.939,8.995C5.218,8.995 6.229,8.723 6.972,8.178C7.715,7.634 8.087,6.93 8.087,6.066C8.087,5.548 7.922,5.133 7.594,4.822C7.266,4.511 6.855,4.278 6.363,4.123C5.87,3.966 5.21,3.811 4.38,3.656C3.412,3.448 2.635,3.246 2.047,3.047C1.46,2.848 0.959,2.533 0.544,2.1C0.129,1.669 -0.078,1.073 -0.078,0.312C-0.078,-0.413 0.125,-1.071 0.531,-1.658C0.937,-2.245 1.551,-2.711 2.371,-3.058C3.191,-3.403 4.189,-3.576 5.365,-3.576C6.437,-3.576 7.317,-3.42 8.009,-3.11C8.7,-2.798 9.201,-2.405 9.513,-1.93C9.823,-1.454 9.979,-0.95 9.979,-0.413C9.979,-0.258 9.966,-0.094 9.94,0.079C9.914,0.252 9.893,0.347 9.875,0.364L8.579,0.364C8.579,0.347 8.592,0.269 8.618,0.131C8.644,-0.007 8.657,-0.155 8.657,-0.31C8.657,-0.845 8.415,-1.325 7.932,-1.748C7.447,-2.172 6.54,-2.383 5.21,-2.383C3.619,-2.383 2.565,-2.089 2.047,-1.502C1.529,-0.914 1.27,-0.353 1.27,0.182C1.27,0.667 1.43,1.05 1.749,1.336C2.068,1.621 2.462,1.838 2.929,1.984C3.396,2.131 4.034,2.291 4.847,2.464C5.849,2.671 6.652,2.879 7.257,3.086C7.862,3.293 8.376,3.622 8.8,4.07C9.223,4.52 9.435,5.133 9.435,5.911C9.435,7.414 8.895,8.503 7.814,9.176C6.734,9.85 5.356,10.187 3.681,10.187C2.453,10.187 1.464,10.014 0.713,9.67C-0.039,9.324 -0.57,8.9 -0.882,8.399C-1.192,7.898 -1.349,7.388 -1.349,6.87C-1.349,6.524 -1.296,6.187 -1.192,5.859L0.104,5.859C0.034,6.066 0,6.317 0,6.611" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1098.98,742.61)">
							<path d="M0,9.227C0.752,8.51 1.296,7.594 1.633,6.48C1.97,5.365 2.139,4.177 2.139,2.915C2.139,1.585 1.823,0.587 1.192,-0.079C0.562,-0.744 -0.272,-1.076 -1.31,-1.076C-2.467,-1.076 -3.418,-0.709 -4.16,0.026C-4.903,0.76 -5.443,1.684 -5.78,2.799C-6.117,3.914 -6.286,5.058 -6.286,6.233C-6.286,7.65 -5.983,8.683 -5.379,9.331C-4.773,9.979 -3.944,10.302 -2.891,10.302C-1.715,10.302 -0.752,9.944 0,9.227M1.335,9.448C0.194,10.813 -1.318,11.495 -3.201,11.495C-4.532,11.495 -5.599,11.063 -6.402,10.199C-7.206,9.335 -7.607,8.04 -7.607,6.311C-7.607,4.876 -7.383,3.507 -6.934,2.203C-6.484,0.898 -5.785,-0.173 -4.834,-1.011C-3.884,-1.849 -2.691,-2.268 -1.257,-2.268C-0.342,-2.268 0.453,-2.083 1.127,-1.711C1.802,-1.339 2.311,-0.826 2.656,-0.169L2.761,-0.169L3.383,-2.009L4.237,-2.009C4.064,-0.817 3.754,1.391 3.305,4.613C2.855,7.836 2.631,9.517 2.631,9.655C2.631,10.086 2.829,10.302 3.227,10.302C3.624,10.302 4.082,10.164 4.601,9.888L4.445,11.002C3.927,11.331 3.383,11.495 2.813,11.495C2.276,11.495 1.892,11.339 1.659,11.029C1.426,10.717 1.309,10.312 1.309,9.81C1.309,9.655 1.317,9.534 1.335,9.448" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1116.8,749.518)">
							<path d="M0,-4.848C0,-5.902 -0.276,-6.688 -0.829,-7.206C-1.383,-7.725 -2.177,-7.984 -3.214,-7.984C-3.975,-7.984 -4.691,-7.803 -5.365,-7.44C-6.039,-7.077 -6.601,-6.576 -7.05,-5.937C-7.5,-5.297 -7.776,-4.588 -7.88,-3.811L-9.021,4.328L-10.342,4.328L-8.476,-8.917L-7.595,-8.917L-7.595,-6.948C-7.042,-7.638 -6.347,-8.183 -5.508,-8.58C-4.67,-8.978 -3.811,-9.176 -2.929,-9.176C-1.633,-9.176 -0.596,-8.844 0.182,-8.179C0.959,-7.513 1.348,-6.524 1.348,-5.21C1.348,-4.986 1.313,-4.588 1.244,-4.019C1.175,-3.449 1.076,-2.709 0.946,-1.802C0.816,-0.895 0.717,-0.2 0.648,0.284L0.078,4.328L-1.218,4.328C-0.406,-1.461 0,-4.52 0,-4.848" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)" class="artesans-lettering">
						<g transform="matrix(1,0,0,1,1122.04,743.917)">
							<path d="M0,6.611C0,7.388 0.345,7.98 1.036,8.386C1.728,8.793 2.695,8.995 3.939,8.995C5.218,8.995 6.229,8.723 6.972,8.178C7.715,7.634 8.087,6.93 8.087,6.066C8.087,5.548 7.922,5.133 7.594,4.822C7.266,4.511 6.855,4.278 6.363,4.123C5.87,3.966 5.21,3.811 4.38,3.656C3.412,3.448 2.635,3.246 2.047,3.047C1.46,2.848 0.959,2.533 0.544,2.1C0.129,1.669 -0.078,1.073 -0.078,0.312C-0.078,-0.413 0.125,-1.071 0.531,-1.658C0.937,-2.245 1.551,-2.711 2.371,-3.058C3.191,-3.403 4.189,-3.576 5.365,-3.576C6.437,-3.576 7.317,-3.42 8.009,-3.11C8.7,-2.798 9.201,-2.405 9.513,-1.93C9.823,-1.454 9.979,-0.95 9.979,-0.413C9.979,-0.258 9.966,-0.094 9.94,0.079C9.914,0.252 9.893,0.347 9.875,0.364L8.579,0.364C8.579,0.347 8.592,0.269 8.618,0.131C8.644,-0.007 8.657,-0.155 8.657,-0.31C8.657,-0.845 8.415,-1.325 7.932,-1.748C7.447,-2.172 6.54,-2.383 5.21,-2.383C3.619,-2.383 2.565,-2.089 2.047,-1.502C1.529,-0.914 1.27,-0.353 1.27,0.182C1.27,0.667 1.43,1.05 1.749,1.336C2.068,1.621 2.462,1.838 2.929,1.984C3.396,2.131 4.034,2.291 4.847,2.464C5.849,2.671 6.652,2.879 7.257,3.086C7.862,3.293 8.376,3.622 8.8,4.07C9.223,4.52 9.435,5.133 9.435,5.911C9.435,7.414 8.895,8.503 7.814,9.176C6.734,9.85 5.356,10.187 3.681,10.187C2.453,10.187 1.464,10.014 0.713,9.67C-0.039,9.324 -0.57,8.9 -0.882,8.399C-1.192,7.898 -1.349,7.388 -1.349,6.87C-1.349,6.524 -1.296,6.187 -1.192,5.859L0.104,5.859C0.034,6.066 0,6.317 0,6.611" style="fill:white;fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1030.47,765.477)">
							<path d="M0,-1.258L0,3.286C0.534,3.573 1.026,3.723 1.916,3.723C3.559,3.723 3.764,2.369 3.764,1.013C3.764,-0.341 3.559,-1.696 1.916,-1.696C1.026,-1.696 0.534,-1.546 0,-1.258M4.722,1.013C4.722,2.984 4.12,4.572 1.916,4.572C1.177,4.572 0.493,4.326 -0.219,3.874L-0.424,4.435L-0.958,4.435L-0.958,-5.83L0,-5.83L0,-1.984C0.589,-2.367 1.287,-2.545 1.916,-2.545C4.12,-2.545 4.722,-0.957 4.722,1.013" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1037.04,773.334)">
							<path d="M0,-10.266L1.916,-4.654L3.832,-10.266L4.927,-10.266L0.985,0L-0.041,0L1.505,-3.6L-1.096,-10.266L0,-10.266Z" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1050.86,768.489)">
							<path d="M0,-4.065C-0.479,-4.586 -1.122,-4.845 -1.902,-4.845C-2.683,-4.845 -3.299,-4.586 -3.777,-4.065C-4.271,-3.545 -4.503,-2.888 -4.503,-2.067C-4.503,-1.191 -4.271,-0.493 -3.805,0.013C-3.326,0.534 -2.696,0.78 -1.902,0.78C-1.108,0.78 -0.479,0.534 0,0.027C0.466,-0.493 0.712,-1.164 0.712,-2.026C0.712,-2.875 0.466,-3.559 0,-4.065M0.685,0.547C-0.027,1.232 -0.903,1.574 -1.93,1.574C-2.956,1.574 -3.805,1.218 -4.503,0.52C-5.201,-0.165 -5.543,-1.041 -5.543,-2.081C-5.543,-3.066 -5.187,-3.915 -4.489,-4.599C-3.777,-5.297 -2.915,-5.639 -1.902,-5.639C-0.89,-5.639 -0.014,-5.297 0.698,-4.599C1.396,-3.901 1.752,-3.039 1.752,-2.026C1.752,-1 1.396,-0.151 0.685,0.547" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1062.32,767.9)">
							<path d="M0,-2.957C0,-3.847 -0.52,-4.12 -1.3,-4.12C-2.148,-4.12 -2.614,-3.956 -3.161,-3.668C-3.106,-3.449 -3.079,-3.217 -3.079,-2.957L-3.079,2.012L-4.037,2.012L-4.037,-2.957C-4.037,-3.847 -4.558,-4.12 -5.338,-4.12C-6.132,-4.12 -6.583,-3.983 -7.117,-3.71L-7.117,2.012L-8.075,2.012L-8.075,-4.832L-7.541,-4.832L-7.336,-4.27C-6.679,-4.75 -5.968,-4.969 -5.338,-4.969C-4.421,-4.969 -3.777,-4.709 -3.422,-4.188C-2.71,-4.682 -1.984,-4.969 -1.3,-4.969C0.164,-4.969 0.958,-4.284 0.958,-2.916L0.958,2.012L0,2.012L0,-2.957Z" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1065.21,761.837)">
							<path d="M0,6.706L0,1.231L0.931,1.231L0.931,6.816C0.931,7.295 1.082,7.432 1.506,7.432L1.506,8.212C0.521,8.212 0,7.802 0,6.706M0.466,-1.506C0.849,-1.506 1.15,-1.205 1.15,-0.821C1.15,-0.439 0.849,-0.137 0.466,-0.137C0.082,-0.137 -0.219,-0.439 -0.219,-0.821C-0.219,-1.205 0.082,-1.506 0.466,-1.506" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1071.83,760.934)">
							<path d="M0,9.06C-0.164,9.101 -0.437,9.115 -0.616,9.115C-1.752,9.115 -2.806,8.595 -2.806,7.253L-2.806,2.887L-4.175,2.887L-4.175,2.463L-2.806,2.066L-2.806,0.218L-1.848,-0.055L-1.848,2.134L0.273,2.134L0.273,2.887L-1.848,2.887L-1.848,7.144C-1.848,7.979 -1.396,8.321 -0.534,8.321C-0.355,8.321 -0.178,8.307 0,8.293L0,9.06Z" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1075.69,766.995)">
							<path d="M0,-1.011C1.943,-0.833 2.408,-0.177 2.408,0.946C2.408,2 1.738,3.053 -0.343,3.053C-1,3.053 -1.944,2.889 -2.382,2.712L-2.382,1.89C-1.958,2.041 -1.301,2.205 -0.329,2.205C1.095,2.205 1.478,1.602 1.478,0.96C1.478,0.33 1.286,-0.094 -0.068,-0.218C-2.054,-0.382 -2.464,-1.094 -2.464,-2.038C-2.464,-3.01 -1.834,-4.064 0.096,-4.064C0.738,-4.064 1.464,-3.982 2.066,-3.722L2.066,-2.901C1.532,-3.078 0.999,-3.215 0.082,-3.215C-1.273,-3.215 -1.561,-2.708 -1.561,-2.038C-1.561,-1.436 -1.314,-1.121 0,-1.011" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1079.91,761.837)">
							<path d="M0,6.706L0,1.231L0.931,1.231L0.931,6.816C0.931,7.295 1.081,7.432 1.506,7.432L1.506,8.212C0.521,8.212 0,7.802 0,6.706M0.466,-1.506C0.849,-1.506 1.15,-1.205 1.15,-0.821C1.15,-0.439 0.849,-0.137 0.466,-0.137C0.082,-0.137 -0.219,-0.439 -0.219,-0.821C-0.219,-1.205 0.082,-1.506 0.466,-1.506" style="fill-rule:nonzero;"/>
						</g>
					</g>
					<g transform="matrix(1,0,0,1,3240,0)">
						<g transform="matrix(1,0,0,1,1085.26,766.995)">
							<path d="M0,-1.011C1.943,-0.833 2.408,-0.177 2.408,0.946C2.408,2 1.738,3.053 -0.343,3.053C-1,3.053 -1.944,2.889 -2.382,2.712L-2.382,1.89C-1.958,2.041 -1.301,2.205 -0.329,2.205C1.095,2.205 1.478,1.602 1.478,0.96C1.478,0.33 1.286,-0.094 -0.068,-0.218C-2.054,-0.382 -2.464,-1.094 -2.464,-2.038C-2.464,-3.01 -1.834,-4.064 0.096,-4.064C0.738,-4.064 1.464,-3.982 2.066,-3.722L2.066,-2.901C1.532,-3.078 0.999,-3.215 0.082,-3.215C-1.273,-3.215 -1.561,-2.708 -1.561,-2.038C-1.561,-1.436 -1.314,-1.121 0,-1.011" style="fill-rule:nonzero;"/>
						</g>
					</g>
				</g>
			</svg>
			<p class="artesans-urls"><a href="https://www.artesans.eu" target="_blank">artesans.eu</a> | <a href="https://www.omitsis.com" target="_blank">omitsis.com</a></p>
		</div>
<?php
	}

	function plugin_image( $filename, $alt='', $class='' ){
		echo "<img src='".plugins_url("/images/$filename",__FILE__)."' alt='$alt' class='$class' />";
	}
	
}
new Artesans_Search_Redirect();


function artesans_search_redirect_modify_search ( $query ) {

	if ( $query->is_main_query() && $query->is_search ) {
		
		$word = $_GET["s"];
		$word = strtolower( $word );
		$redirections = get_option( 'artesans_search_redirect_field1' );
		$redirections = explode( '[REDIRECTION]', $redirections );
		
		foreach ( $redirections as $redirection ) {
			if ( $redirection != "" ) {
				$redirection =explode('[URL]', $redirection );

				$keywords = $redirection[0]; // 'jander,clander, petejander way'
				$keywords = esc_html($keywords); // escape html
				$keywords = remove_accents($keywords); // Àngels - Angels
				$keywords = strtolower($keywords); // Angels - angels
				$kw = explode(",",$keywords); // array('jander','clander',' petejander way')

				$url = $redirection[1]; // http...
				
				if ( in_array( $word, $kw ) ) {
					wp_redirect($url);
					exit();
				}
			}
		}
	}
	return $query;
}
add_action( 'pre_get_posts', 'artesans_search_redirect_modify_search' );
