<?php
/*
Plugin Name: Guthrie
Plugin URI: http://guthrie.sethmurphy.com
Description: A place to share your identity with those you know.
Version: 1.0
Author: Seth Murphy
Author URI: http://sethmurphy.com
License: GPL2
Copyright 2011	Seth Murphy	(email : seth@sethmurphy.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, AS 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.	See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA	02110-1301	USA
*/

$guthrie = new Guthrie;

class Guthrie {
	public $status_message = '';
	private $ajax = '';
	function Guthrie() {
		$this->__construct();
	}
	function __construct() {
		// constructor
		// Do our install stuff upon activation
		register_activation_hook( __FILE__, array( &$this, 'install') );
		
		// Do our uninstall stuff upon activation
		// WARNING: Removes all data
		register_deactivation_hook( __FILE__, array( &$this, 'uninstall') );

		// Add the admin menu
		add_action( 'admin_menu', array( &$this, 'menu') );
		
		// used to embed a profiles anywhere
		add_filter( 'the_content', array( &$this, 'profile') );
	
		// register our ajax calls
		// this should probably be somewhere else, but I couldn't get it to work if I called while loading the admin scripts.
		add_action( 'wp_ajax_guthrie_update_profile_field_roles',          array( &$this, 'ajax_update_profile_field_roles' ) );          // action = 'guthrie_update_profile_field_roles'
		add_action( 'wp_ajax_guthrie_update_profile_field_value',          array( &$this, 'ajax_update_profile_field_value' ) );          // action = 'guthrie_update_profile_field_value'
		add_action( 'wp_ajax_guthrie_update_profile_field_sequence', array( &$this, 'ajax_update_profile_field_sequence' ) ); // action = 'guthrie_update_profile_field_sequence'	
		add_action( 'wp_ajax_guthrie_remove_profile_field_instance', array( &$this, 'ajax_remove_profile_field_instance' ) ); // action = 'guthrie_remove_profile_field_instance'	
	
		add_action('wp_print_styles', array(&$this, 'add_guthrie_stylesheet'));
	}
	
	/*********************************************************
	 *  Add a minimal stylesheet for our Guthrie Profile
	 *  Loads on every page, but it is small ...
	 *  Does NOT include any CSS for the admin section
	 *  Themes should overide the styles if they wish
	 ********************************************************/
	function add_guthrie_stylesheet(){
		$styleUrl = plugins_url('/css/guthrie.css', __FILE__);
		$styleFile = WP_PLUGIN_DIR . '/guthrie/css/guthrie.css';
		if ( file_exists($styleFile) ) {
				//echo($styleUrl."<br />");
				wp_register_style('guthrie', $styleUrl);
				wp_enqueue_style('guthrie');
		}
	}

	function install () {
		require( WP_PLUGIN_DIR . '/guthrie/guthrie_install.php' );
		$g_install = new Guthrie_Install;
		$g_install->create_database(); // make sure we have a database
		$g_install->populate_database_defaults(); // populate the database tables with default values(inluding test values for now) if they are empty
		$g_install->create_default_guthrie_profile_page();
	}

	function uninstall () {
		require( WP_PLUGIN_DIR . '/guthrie/guthrie_install.php' );
		$g_install = new Guthrie_Install;
		$g_install->remove_database(); // remove our data
		$g_install->delete_default_guthrie_profile_page();
	}

	/**
	 * Generic function to show a message to the user using WP's
	 * standard CSS classes to make use of the already-defined
	 * message colour scheme.
	 *
	 * @param $message The message you want to tell the user.
	 * @param $errormsg If true, the message is an error, so use
	 * the red message style. If false, the message is a status
	  * message, so use the yellow information message style.
	 */
	function show_message( $message, $errormsg = false )
	{
		if ( $errormsg ) {
			echo '<div id="message" class="error">';
		} else {
			echo '<div id="message" class="updated fade">';
		}
		echo "<p><strong>$message</strong></p></div>";
	}

	/**
	 * Just show our message (with possible checking if we only want
	 * to show message to certain users.
	 */
	function show_admin_messages($errormsg = false)
	{
		if($this->status_message != '') {
      $this->show_message( $this->status_message, $errormsg );
    	$this->status_message = '';
    }
	}

	function menu() {
		$mypage = add_options_page( 'Guthrie Options', 'Guthrie', 'manage_options', 'guthrie', array( &$this, 'options') );
		//$mypage = add_management_page( 'guthrie', 'settings', 9, __FILE__, 'admin_page' );
		add_action( "admin_print_scripts-$mypage", array(&$this,'admin_head') );
		add_action( 'admin_notices', array( &$this, 'show_admin_messages' ) );
	}
	
	/* manage our plugin option in admin */
	function options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		include WP_PLUGIN_DIR . '/guthrie/admin_options_page.php';
	}
	
	function admin_head() {
		$this->admin_print_scripts();
		$this->admin_print_styles();
		wp_print_styles();
	}
	
	function admin_print_styles() {
		// enqueue here
		$styleUrl = plugins_url('/css/admin-style.css', __FILE__);
		$styleFile = WP_PLUGIN_DIR . '/guthrie/css/admin-style.css';
		if ( file_exists($styleFile) ) {
				wp_register_style('guthrie-admin', $styleUrl);
				wp_enqueue_style('guthrie-admin');
		}
	
		$styleUrl = plugins_url( '/css/chosen.css', __FILE__ );
		$styleFile = WP_PLUGIN_DIR . '/guthrie/css/chosen.css';
		if ( file_exists( $styleFile ) ) {
				wp_register_style( 'chosen', $styleUrl );
				wp_enqueue_style( 'chosen' );
		}
		$styleUrl = plugins_url( '/css/guthrie.editinplace.jquery.css', __FILE__ );
		$styleFile = WP_PLUGIN_DIR . '/guthrie/css/guthrie.editinplace.jquery.css';
		if ( file_exists( $styleFile ) ) {
				wp_register_style( 'guthrie-editinplace', $styleUrl );
				wp_enqueue_style( 'guthrie-editinplace' );
		}
		// we are still in our head, but have written the styles already, so force a new write.
		//wp_enqueue_style('guthrie-style')
	}
	
	function admin_print_scripts() {

		$scriptUrl = plugins_url( '/js/jquery.ui.min.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/jquery.ui.min.js';
		if ( file_exists( $scriptFile ) ) {
			wp_deregister_script( 'jquery-ui' );
			wp_register_script( 'jquery-ui', $scriptUrl );
			wp_enqueue_script( 'jquery-ui' );
		} else {
			wp_deregister_script( 'jquery-ui' );
			wp_register_script( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js' );
			wp_enqueue_script( 'jquery-ui' );
		}

		$scriptUrl = plugins_url( '/js/jquery.min.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/jquery.min.js';
		if ( file_exists( $scriptFile ) ) {
			wp_deregister_script( 'jquery' );
			wp_register_script( 'jquery', $scriptUrl );
			wp_enqueue_script( 'jquery' );
		} else {
			wp_register_script( 'jquery', 'http://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js' );
			wp_enqueue_script( 'jquery' );
		}

		$scriptUrl = plugins_url( '/js/chosen.jquery.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/chosen.jquery.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'chosen', $scriptUrl );
			wp_enqueue_script( 'chosen' );
		}
	
		$scriptUrl = plugins_url( '/js/guthrie.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/guthrie.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie', $scriptUrl );
			wp_enqueue_script( 'guthrie' );
		}
	
		$scriptUrl = plugins_url( '/js/validate.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/validate.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie-validate', $scriptUrl );
			wp_enqueue_script( 'guthrie-validate' );
		}
		
		$scriptUrl = plugins_url( '/js/guthrie.editinplace.jquery.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/guthrie.editinplace.jquery.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie-editinplace', $scriptUrl );
			wp_enqueue_script( 'guthrie-editinplace' );
		}

		/* our ajax files for the options page*/

		wp_localize_script( 'guthrie-ajax-request', 'GuthrieAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		/* to update our roles in place */
		$scriptUrl = plugins_url( '/js/ajax-update-field-roles.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/ajax-update-field-roles.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie-ajax-update-field-roles', $scriptUrl );
			wp_enqueue_script( 'guthrie-ajax-update-field-roles' );
		}

	}
	
	// display our profile
	function profile( $content = false ) {
		global $wpdb;
		global $page_id;
		if ( ! ( strpos( $content , '[GUTHRIE]' ) === false ) ) {
			$html = '';
			if ( get_the_ID() == get_option( "guthrie_default_post_id" ) ) {
				// dirty, inline css
				$html = "<style>h3.post-title {display:none;}</style>";
			}
			$role_id = 1;
			// check if we passed a key
			$key = "";
			if ( array_key_exists ( 'key', $_REQUEST ) ) {
				$key = $_REQUEST['key'];
			}
			$invitation_id = 1;
			if ( isset( $key ) && $key != "" ) {
				// get our invitation by key		
				$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
				$invitation_id = $wpdb->get_var( "select id from $table_name where guid='$key'" );
			}
			// now get our profile based on the invitation id

				$profilefields = $this->get_profile_field_instances( $invitation_id );
				$html.='<div class="profile-field-values" >';
				foreach ( $profilefields as $field ) {
					$value = $field->value;
					if( $this->validate_url( $value ) ) {
						$mailto = '';
						if( $this->validate_email_address( $value ) ) {
							$mailto = 'mailto:';
						}
						$value = '<a href="' . $mailto . $value . '">' . $value . '</a>';
					}
					$html.='<div class="profile-field-value profile-field-value_' . $field->tag . '" >'. $value .'</div>';
				}
				$html.='<div class="clearfix"></div></div>';
				$content = str_replace ( '[GUTHRIE]' , $html , $content );
			}
			return $content;
	}
	/********************************
	 *  See if we are a valid Email Address
	 *******************************/
	private function validate_url( $url )
	{
		$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
		return preg_match( $pattern, $url );
	}

	/********************************
	 *  See if we are a valid Email Address
	 *  From: http://www.linuxjournal.com/article/9585
	 *******************************/
	function validate_email_address($email) {
		$pattern = "/^[-\w.]+@([A-z0-9][-A-z0-9]+\.)+[A-z]{2,4}$/";
		return preg_match( $pattern, $email );
	}

	private $profile_field_types;
	// get our types
	function get_profile_field_types(){
		if( ! isset( $profile_field_types ) ){
			global $wpdb;
			$table_name = $wpdb->prefix . "guthrie_profile_field_type"; 
			$sql = "SELECT * FROM $table_name;";
			$profile_field_types = $wpdb->get_results( $sql, OBJECT );
		}
		return $profile_field_types;
	}
	
	private $profile_field_roles;
	// get our types
	function get_profile_field_roles(){
		if( ! isset( $profile_field_roles ) ){
			global $wpdb;
			$table_name = $wpdb->prefix . "guthrie_profile_role"; 
	$sql = "select pr.id, pr.name " .
					"from $table_name as pr " .
					"order by sequence, id;";
			$profile_field_roles = $wpdb->get_results( $sql, OBJECT );
		}
		return $profile_field_roles;
	}


	function get_profile_field_instances( $invitation_id = null ){
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$where_clause = '';
		$invitation_join = '';
		if( isset( $invitation_id ) ){
			$invitation_join .= "LEFT JOIN guthrie_guthrie_profile_invitation_role ir ON ir.profile_role_id = r.id ";
			$invitation_join .= "LEFT JOIN guthrie_guthrie_profile_invitation i ON i.id = ir.profile_invitation_id ";
			$where_clause = "WHERE i.id=" . $invitation_id . " ";
		}
	  $sql = "SELECT f.id, fi.id AS profile_field_instance_id, f.tag, f.name, f.description, fi.value, group_concat(r.id) AS roles " .
        "FROM guthrie_guthrie_profile_field_instance as fi " .
        "LEFT JOIN guthrie_guthrie_profile_field f ON f.id = fi.profile_field_id " .
        "LEFT JOIN guthrie_guthrie_profile_field_role fr ON f.id = fr.profile_field_id " .
        "LEFT JOIN guthrie_guthrie_profile_role r ON r.id = fr.profile_role_id " .
        $invitation_join .
				$where_clause .
        "GROUP BY f.id " .
        "ORDER BY fi.sequence, f.sequence, r.sequence, profile_field_instance_id;";
		//echo($sql);
  	$profile_field_instances = $wpdb->get_results( $sql, OBJECT );
  	return $profile_field_instances;
	}

	/***************************************************
	 *  Wrappers for our AJAX calls in guthrie_ajax.php
	 ***************************************************/
	function initAJAX(){
		if( ! isset( $guthrie_ajax ) ) {
			require_once( 'guthrie_ajax.php' );
			$this->ajax = new Guthrie_Ajax( &$this );
		}
	}

  function ajax_update_profile_field_sequence() {
		$this->initAJAX();
		$this->ajax->update_profile_field_sequence();
	}


  function ajax_update_profile_field_value() {
		$this->initAJAX();
		$this->ajax->update_profile_field_value();
	}

	function ajax_update_profile_field_roles() {
		$this->initAJAX();
		$this->ajax->update_profile_field_roles();
	}	

	function ajax_remove_profile_field_instance() {
		$this->initAJAX();
		$this->ajax->remove_profile_field_instance();
	}	


	/*******************************************************
	 *  End of AJAX wrappers
	 ********************************************************/
}
?>
