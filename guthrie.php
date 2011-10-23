<?php
/*
Plugin Name: Guthrie
Plugin URI: http://guthrie.sethmurphy.com
Description: A place to share your identity with those you know.
Version: 0.8
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
	private $ajax;
	function Guthrie() {
		$this->__construct();
	}

	function __construct() {
		// constructor

		// Do our install stuff upon activation
		register_activation_hook( __FILE__, array( &$this, 'install') );
		
		// Do our uninstall'ish stuff upon deactivation
		// NOTE: unistall, which removes all data, is in uninstall.php
		register_deactivation_hook( __FILE__, array( &$this, 'deactivate') );

		// Add the admin menu ( all other admin hooks are in menu() )
		add_action( 'admin_menu', array( &$this, 'menu') );
		
		// used to embed a profiles anywhere using [GUTHRIE]
		add_filter( 'the_content', array( &$this, 'profile') );
	
		// register our styles (non-admin)
		add_action('wp_print_styles', array(&$this, 'add_guthrie_stylesheet'));

		// register our ajax calls
		// this should probably be somewhere else, but I couldn't get it to work if I called while loading the admin scripts.
		add_action( 'wp_ajax_guthrie_update_profile_field_roles',            array( &$this, 'ajax_update_profile_field_roles' ) );            // action = 'guthrie_update_profile_field_roles'
		add_action( 'wp_ajax_guthrie_update_profile_invitation_roles',       array( &$this, 'ajax_update_profile_invitation_roles' ) );       // action = 'guthrie_update_profile_invitation_roles'
		add_action( 'wp_ajax_guthrie_update_profile_field_value',            array( &$this, 'ajax_update_profile_field_value' ) );            // action = 'guthrie_update_profile_field_value'
		add_action( 'wp_ajax_guthrie_update_profile_field_sequence',         array( &$this, 'ajax_update_profile_field_sequence' ) );         // action = 'guthrie_update_profile_field_sequence'	
		add_action( 'wp_ajax_guthrie_remove_profile_field_instance',         array( &$this, 'ajax_remove_profile_field_instance' ) );         // action = 'guthrie_remove_profile_field_instance'	

		add_action( 'wp_ajax_guthrie_update_profile_role_name',              array( &$this, 'ajax_update_profile_role_name' ) );              // action = 'guthrie_update_profile_role_name'
		add_action( 'wp_ajax_guthrie_update_profile_role_description',       array( &$this, 'ajax_update_profile_role_description' ) );       // action = 'guthrie_update_profile_role_description'

		add_action( 'wp_ajax_guthrie_update_profile_invitation_name',        array( &$this, 'ajax_update_profile_invitation_name' ) );        // action = 'guthrie_update_profile_invitation_name'
		add_action( 'wp_ajax_guthrie_update_profile_invitation_description', array( &$this, 'ajax_update_profile_invitation_description' ) ); // action = 'guthrie_update_profile_invitation_description'
		add_action( 'wp_ajax_guthrie_remove_profile_invitation',             array( &$this, 'ajax_remove_profile_invitation' ) );             // action = 'guthrie_remove_profile_invitation'
		add_action( 'wp_ajax_guthrie_remove_profile_role',                   array( &$this, 'ajax_remove_profile_role' ) );                   // action = 'guthrie_remove_profile_role'
		add_action( 'wp_ajax_guthrie_remove_profile_field_instance',         array( &$this, 'ajax_remove_profile_field_instance' ) );         // action = 'guthrie_remove_profile_field_instance'

	}
	
	function install () {
		require( WP_PLUGIN_DIR . '/guthrie/guthrie_install.php' );
		$g_install = new Guthrie_Install;
		$g_install->create_database(); // make sure we have a database
		$g_install->populate_database_defaults(); // populate the database tables with default values(inluding test values for now) if they are empty
		$g_install->create_default_guthrie_profile_page();
	}

	function deactivate () {
		// just remove our default page
		// data is removed upon deactivation
		require( WP_PLUGIN_DIR . '/guthrie/guthrie_install.php' );
		$g_install = new Guthrie_Install;
		$g_install->delete_default_guthrie_profile_page();
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
				wp_register_style('guthrie', $styleUrl);
				wp_enqueue_style('guthrie');
		}
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

		/* to update our field roles in place */
		$scriptUrl = plugins_url( '/js/ajax-update-field-roles.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/ajax-update-field-roles.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie-ajax-update-field-roles', $scriptUrl );
			wp_enqueue_script( 'guthrie-ajax-update-field-roles' );
		}

		/* to update our invitation roles in place */
		$scriptUrl = plugins_url( '/js/ajax-update-invitation-roles.js', __FILE__ );
		$scriptFile = WP_PLUGIN_DIR . '/guthrie/js/ajax-update-invitation-roles.js';
		if ( file_exists( $scriptFile ) ) {
			wp_register_script( 'guthrie-ajax-update-invitation-roles', $scriptUrl );
			wp_enqueue_script( 'guthrie-ajax-update-invitation-roles' );
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
			$role_id = 0;
			// check if we passed a key
			$key = "";
			if ( array_key_exists ( 'key', $_REQUEST ) ) {
				$key = $_REQUEST[ 'key' ];
			} else {
				// check if we are passed a role and can manage options
				if ( current_user_can( 'manage_options' ) && array_key_exists ( 'role_id', $_REQUEST ) ) {
					$role_id = $_REQUEST[ 'role_id' ];
				}
			}
			
			$invitation_id = null;
			if ( isset( $key ) && $key != "" ) {
				// get our invitation by key		
				$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
				$invitation_id = $wpdb->get_var( "select id from $table_name where guid='$key'" );
			}
			if ( ! isset( $invitation_id ) || '' == $invitation_id ) {
				$invitation_id = 1; // the default public profile
			}

			$profilefields = null;
			if ( $role_id > 0 ) {
				$profilefields = $this->get_profile_field_instances_by_role( $role_id );
			} else {
				// now get our profile based on the invitation id
				$profilefields = $this->get_profile_field_instances( $invitation_id );
			}			
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
				$html.='<div class="profile-field-value profile-field-value_' . $field->tag . '" >'. stripslashes( $value ) .'</div>';
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

	/****************************************
	 *  Data access methods
	 *  These are used accross screens and may be called more than once due to the modular nature of the code
	 *  Therefore they are cached in a private variable for each requests lifecyle
	 ****************************************/
	private $profile_field_types;
	// get our types
	function get_profile_field_types(){
		if( ! isset( $this->profile_field_types ) ){
			global $wpdb;
			$table_name = $wpdb->prefix . "guthrie_profile_field_type"; 
			$sql = "SELECT * FROM $table_name";
			$this->profile_field_types = $wpdb->get_results( $sql, OBJECT );
		}
		return $this->profile_field_types;
	}
	
	private $profile_roles;
	// get our types
	function get_profile_roles(){
		if( ! isset( $this->profile_roles ) ){
			global $wpdb;
			$table_name = $wpdb->prefix . "guthrie_profile_role"; 
			$sql = "SELECT pr.id, pr.name, pr.description " .
					"FROM $table_name AS pr " .
					"ORDER BY sequence, id";
			$this->profile_roles = $wpdb->get_results( $sql, OBJECT );
		}
		return $this->profile_roles;
	}

	private $profile_invitations;
	// get our invitations
	function get_profile_invitations(){
		if( ! isset( $this->profile_invitations ) ){
			global $wpdb;
			$table_name = $wpdb->prefix . "guthrie_profile_invitation"; 
			$sql = "SELECT pi.id, pi.name, pi.description, pi.email, pi.guid, group_concat(ir.profile_role_id) as roles " .
			       "FROM $table_name as pi " .
			       "LEFT JOIN " . $wpdb->prefix . "guthrie_profile_invitation_role ir ON ir.profile_invitation_id = pi.id " .
			       "GROUP BY pi.id ".
			       "ORDER BY pi.id;";
			$this->profile_field_roles = $wpdb->get_results( $sql, OBJECT );
		}
		return $this->profile_field_roles;
	}

	private $profile_field_instances;
	function get_profile_field_instances( $invitation_id = null ){
		if( ! isset( $this->profile_field_instances ) ){
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			$where_clause = '';
			$invitation_join = '';

			if( isset( $invitation_id ) && '0' != $invitation_id){
				$invitation_join .= 'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_invitation_role ir ON ir.profile_role_id = r.id ';
				$invitation_join .= 'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_invitation i ON i.id = ir.profile_invitation_id ';
				$where_clause = 'WHERE i.id=' . $invitation_id . ' ';
			} else {
				if ( '0' == $invitation_id ) {
					$where_clause = 'WHERE r.id=1 '; // default public profile
				}
			}
			$sql = 'SELECT f.id, fi.id AS profile_field_instance_id, f.tag, f.name, f.description, fi.value, group_concat(r.id) AS roles ' .
				   'FROM ' . $wpdb->prefix . 'guthrie_profile_field_instance as fi ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_field f ON f.id = fi.profile_field_id ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_field_role fr ON f.id = fr.profile_field_id ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_role r ON r.id = fr.profile_role_id ' .
				   $invitation_join .
				   $where_clause .
				   'GROUP BY f.id ' .
				   'ORDER BY fi.sequence, f.sequence, r.sequence, profile_field_instance_id;';
			//echo($sql);
			$this->profile_field_instances = $wpdb->get_results( $sql, OBJECT );
		}			
		return $this->profile_field_instances;
	}

	private $profile_field_instances_by_role;
	function get_profile_field_instances_by_role( $role_id ){
		if( ! isset( $this->profile_field_instances ) ){
			global $wpdb;
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$sql = 'SELECT f.id, fi.id AS profile_field_instance_id, f.tag, f.name, f.description, fi.value, group_concat(r.id) AS roles ' .
				   'FROM ' . $wpdb->prefix . 'guthrie_profile_field_instance as fi ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_field f ON f.id = fi.profile_field_id ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_field_role fr ON f.id = fr.profile_field_id ' .
				   'LEFT JOIN ' . $wpdb->prefix . 'guthrie_profile_role r ON r.id = fr.profile_role_id ' .
			     'WHERE r.id = ' . $role_id . ' ' .
				   'GROUP BY f.id ' .
				   'ORDER BY fi.sequence, f.sequence, r.sequence, profile_field_instance_id;';
			//echo($sql);
			$this->profile_field_instances_by_role = $wpdb->get_results( $sql, OBJECT );
		}			
		return $this->profile_field_instances_by_role;
	}

	/****************************************
	 *  End data access methods
	 ****************************************/

	/***************************************************
	 *  Wrappers for our AJAX calls in guthrie_ajax.php
	 ***************************************************/
	function initAJAX(){
		if( ! isset( $this->ajax ) ) {
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

	function ajax_remove_profile_invitation() {
		$this->initAJAX();
		$this->ajax->remove_profile_invitation();
	}	

	function ajax_remove_profile_role() {
		$this->initAJAX();
		$this->ajax->remove_profile_role();
	}	

	function ajax_update_profile_role_name() {
		$this->initAJAX();
		$this->ajax->update_profile_role_name();
	}

	function ajax_update_profile_role_description() {
		$this->initAJAX();
		$this->ajax->update_profile_role_description();
	}

	function ajax_update_profile_invitation_name() {
		$this->initAJAX();
		$this->ajax->update_profile_invitation_name();
	}

	function ajax_update_profile_invitation_description() {
		$this->initAJAX();
		$this->ajax->update_profile_invitation_description();
	}

	function ajax_update_profile_invitation_roles() {
		$this->initAJAX();
		$this->ajax->update_profile_invitation_roles();
	}	

	/*******************************************************
	 *  End of AJAX wrappers
	 ********************************************************/
	/********************************************************
	 * common gui stuff
	 ********************************************************/
	function delete_button( $element_id ) {
		return '<span class="delete-button" id="' . $element_id . '"/>';
	}

	function drag_button() {
		return '<span class="drag-button" />';
	}

	function preview_button() {
		return '<span class="preview-button" />';
	}
}
