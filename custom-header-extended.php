<?php
/**
 * Plugin Name: Custom Header Extended
 * Plugin URI:  http://themehybrid.com/plugins/custom-header-extended
 * Description: Allows users to create <a href="http://codex.wordpress.org/Custom_Headers">custom headers</a> for individual posts, which are displayed on the single post page.  It works alongside any theme that supports the WordPress <code>custom-header</code> feature.
 * Version:     1.0.0
 * Author:      Justin Tadlock
 * Author URI:  http://justintadlock.com
 * Text Domain: custom-header-extended
 * Domain Path: /languages
 *
 * This plugin was created so that users could create custom headers for individual posts.  It ties
 * into the Wordpress 'custom-header' theme feature.  Therefore, it will only work with themes that
 * add support for 'custom-header' via 'functions.php.
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License as published by the Free Software Foundation; either version 2 of the License,
 * or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write
 * to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
 *
 * @package   CustomHeaderExtended
 * @version   1.0.0
 * @since     0.1.0
 * @author    Justin Tadlock <justintadlock@gmail.com>
 * @copyright Copyright (c) 2013 - 2015, Justin Tadlock
 * @link      http://themehybrid.com/plugins/custom-header-extended
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class CHE_Custom_Headers {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Stores the directory path for this plugin.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    string
	 */
	private $directory_path;

	/**
	 * Stores the directory URI for this plugin.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    string
	 */
	private $directory_uri;

	/**
	 * Plugin setup.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Set the properties needed by the plugin. */
		add_action( 'plugins_loaded', array( $this, 'setup' ), 1 );

		/* Internationalize the text strings used. */
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 2 );

		/* Load the functions files. */
		add_action( 'plugins_loaded', array( $this, 'includes' ), 3 );

		/* Load the admin files. */
		add_action( 'plugins_loaded', array( $this, 'admin' ), 4 );

		/* Register scripts and styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_scripts' ), 5 );

		/* Add post type support. */
		add_action( 'init', array( $this, 'post_type_support' ) );

		/* Register activation hook. */
		register_activation_hook( __FILE__, array( __CLASS__, 'activation' ) );
	}

	/**
	 * Defines the directory path and URI for the plugin.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function setup() {

		$this->directory_path = trailingslashit( plugin_dir_path( __FILE__ ) );
		$this->directory_uri  = trailingslashit( plugin_dir_url(  __FILE__ ) );
	}

	/**
	 * Loads the initial files needed by the plugin.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function includes() {

		require_once( "{$this->directory_path}inc/class-custom-headers-filter.php" );
	}

	/**
	 * Loads the translation files.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function i18n() {

		/* Load the translation of the plugin. */
		load_plugin_textdomain( 'custom-header-extended', false, 'custom-header-extended/languages' );
	}

	/**
	 * Loads the admin functions and files.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function admin() {

		if ( is_admin() )
			require_once( "{$this->directory_path}admin/class-custom-headers-admin.php" );
	}

	/**
	 * Adds post type support for the 'custom-header' feature.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function post_type_support() {
		add_post_type_support( 'post', 'custom-header' );
		add_post_type_support( 'page', 'custom-header' );
	}

	/**
	 * Registers scripts and styles for use in the WordPress admin (does not load theme).
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function admin_register_scripts() {

		wp_register_script(
			'custom-header-extended',
			"{$this->directory_uri}js/custom-headers.min.js",
			array( 'wp-color-picker', 'media-views' ),
			'20130926',
			true
		);
	}

	/**
	 * Method that runs only when the plugin is activated.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public static function activation() {

		/* Get the administrator role. */
		$role = get_role( 'administrator' );

		/* If the administrator role exists, add required capabilities for the plugin. */
		if ( !empty( $role ) )
			$role->add_cap( 'che_edit_header' );
	}

	/**
	 * Returns the instance.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return object
	 */
	public static function get_instance() {

		if ( !self::$instance )
			self::$instance = new self;

		return self::$instance;
	}
}

CHE_Custom_Headers::get_instance();

?>