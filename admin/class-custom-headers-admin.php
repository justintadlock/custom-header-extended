<?php
/**
 * The admin class for the plugin.  This sets up a "Custom Header" meta box on the edit post screen in
 * the admin.  It loads the WordPress media views script and a custom JS file for allowing the user to
 * select a custom header image that will overwrite the header on the front end for the singular view
 * of the post.
 *
 * @package   CustomHeaderExtended
 * @since     0.1.0
 * @author    Justin Tadlock <justintadlock@gmail.com>
 * @copyright Copyright (c) 2013 - 2015, Justin Tadlock
 * @link      http://themehybrid.com/plugins/custom-header-extended
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class CHE_Custom_Headers_Admin {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Minimum width allowed for the image.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $min_width = 0;

	/**
	 * Minimum height allowed for the image.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $min_height = 0;

	/**
	 * Maximum width allowed for the image.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $max_width = 9999;

	/**
	 * Maximum height allowed for the image.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $max_height = 9999;

	/**
	 * Array of error strings for display when the image size isn't correct.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    array
	 */
	public $error_strings = array();

	/**
	 * Adds our classes actions on the edit post screen only.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {

		/* Custom meta for plugin on the plugins admin screen. */
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

		/* If the current user can't edit custom backgrounds, bail early. */
		if ( !current_user_can( 'che_edit_header' ) && !current_user_can( 'edit_theme_options' ) )
			return;

		/* Only load on the edit post screen. */
		add_action( 'load-post.php',     array( $this, 'load_post' ) );
		add_action( 'load-post-new.php', array( $this, 'load_post' ) );
	}

	/**
	 * Sets up actions to run on specific hooks on the edit post screen if both the theme and current
	 * post type supports the 'custom-header' feature.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function load_post() {

		$screen = get_current_screen();

		/* If the current theme doesn't support custom headers, bail. */
		if ( !current_theme_supports( 'custom-header' ) || !post_type_supports( $screen->post_type, 'custom-header' ) )
			return;

		/* Get the theme's 'custom-header' arguments needed. */
		$flex_width   = get_theme_support( 'custom-header', 'flex-width'  );
		$flex_height  = get_theme_support( 'custom-header', 'flex-height' );
		$theme_width  = get_theme_support( 'custom-header', 'width'       );
		$theme_height = get_theme_support( 'custom-header', 'width'       );

		/* Set up min/max width/height properties for error checking. */
		$this->min_width  = $flex_width  ? 0    : $theme_width;
		$this->min_height = $flex_height ? 0    : $theme_height;
		$this->max_width  = $flex_width  ? 9999 : $theme_width;
		$this->max_height = $flex_height ? 9999 : $theme_height;

		/* Set up error strings. */
		$this->error_strings = array(
			'min_width_height_error' => __( 'Your image width and height are too small.', 'custom-header-extended' ),
			'max_width_height_error' => __( 'Your image width and height are too large.', 'custom-header-extended' ),
			'min_width_error'        => __( 'Your image width is too small.',             'custom-header-extended' ),
			'min_height_error'       => __( 'Your image height is too small.',            'custom-header-extended' ),
			'max_width_error'        => __( 'Your image width is too large.',             'custom-header-extended' ),
			'max_height_error'       => __( 'Your image height is too large.',            'custom-header-extended' ),
		);

		/* Load scripts and styles. */
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		/* Add meta boxes. */
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		/* Save metadata. */
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
	}

	/**
	 * Loads scripts/styles for the image uploader.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  string  $hook_suffix  The current admin screen.
	 * @return void
	 */
	public function enqueue_scripts( $hook_suffix ) {

		/* Make sure we're on the edit post screen before loading media. */
		if ( !in_array( $hook_suffix, array( 'post-new.php', 'post.php' ) ) )
			return;

		/* Set up variables to pass to the custom headers script. */
		$localize_script = array(
			'title'        => __( 'Set Header Image', 'custom-header-extended' ),
			'button'       => __( 'Set header image', 'custom-header-extended' ),
			'min_width'    => $this->min_width,
			'min_height'   => $this->min_height,
			'max_width'    => $this->max_width,
			'max_height'   => $this->max_height
		);

		/* Merge with error strings and escape for use in JS. */
		$localize_script = array_map( 'esc_js', array_merge( $localize_script, $this->error_strings ) );

		/* Pass custom variables to the script. */
		wp_localize_script( 'custom-header-extended', 'che_custom_headers', $localize_script );

		/* Load the needed scripts and styles. */
		wp_enqueue_script( 'custom-header-extended' );
		wp_enqueue_style(  'wp-color-picker'        );
	}

	/**
	 * Creates the custom header meta box.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  string  $post_type
	 * @return void
	 */
	function add_meta_boxes( $post_type ) {

		add_meta_box(
			'che-custom-headers',
			__( 'Custom Header', 'custom-header-extended' ),
			array( $this, 'do_meta_box' ),
			$post_type,
			'side',
			'core'
		);
	}

	/**
	 * Display the custom header meta box.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  object  $post
	 * @return void
	 */
	function do_meta_box( $post ) {

		/* This filter makes sure the theme's $content_width doesn't mess up our header image size. */
		add_filter( 'editor_max_image_size', array( $this, 'editor_max_image_size' ), 10, 2 );

		/* Get the header image attachment ID. */
		$attachment_id = get_post_meta( $post->ID, '_custom_header_image_id', true );

		/* If an attachment ID was found, get the image source. */
		if ( !empty( $attachment_id ) )
			$image = wp_get_attachment_image_src( absint( $attachment_id ), 'che_header_image' );

		/* Get the image URL. */
		$url = !empty( $image ) && isset( $image[0] ) ? $image[0] : ''; ?>

		<!-- Begin hidden fields. -->
		<?php wp_nonce_field( plugin_basename( __FILE__ ), 'che_meta_nonce' ); ?>
		<input type="hidden" name="che-header-image" id="che-header-image" value="<?php echo esc_attr( $attachment_id ); ?>" />
		<!-- End hidden fields. -->

		<!-- Begin header image. -->
		<p>
			<a href="#" class="che-add-media che-add-media-img"><img class="che-header-image-url" src="<?php echo esc_url( $url ); ?>" style="max-width: 100%; max-height: 200px; height: auto; display: block;" /></a>
			<a href="#" class="che-add-media che-add-media-text"><?php _e( 'Set header image', 'custom-header-extended' ); ?></a>
			<a href="#" class="che-remove-media"><?php _e( 'Remove header image', 'custom-header-extended' ); ?></a>
		</p>
		<!-- End header image. -->

		<div class="che-errors"><p>
		<?php
			if ( !empty( $image ) ) {

				if ( $image[1] < $this->min_width && $image[2] < $this->min_height )
					echo $this->error_strings['min_width_height_error'];

				elseif ( $image[1] > $this->max_width && $image[2] > $this->max_height )
					echo $this->error_strings['max_width_height_error'];

				elseif ( $image[1] < $this->min_width )
					echo $this->error_strings['min_width_error'];

				elseif ( $image[2] < $this->min_height )
					echo $this->error_strings['min_height_error'];

				elseif ( $image[1] > $this->max_width )
					echo $this->error_strings['max_width_error'];

				elseif ( $image[2] > $this->max_height )
					echo $this->error_strings['max_height_error'];
			}
		?>
		</p></div>

		<?php if ( current_theme_supports( 'custom-header', 'header-text' ) ) {

			/* Get the header text display option. */
			$display_text = get_post_meta( $post->ID, '_custom_header_text_display', true );

			/* Get the header text color. */
			$text_color = get_post_meta( $post->ID, '_custom_header_text_color', true );

			$color = 'blank' === $text_color ? '' : $text_color; ?>

			<!-- Begin header text color. -->
			<p>
				<label for="che-header-text-show"><?php _e( 'Show header text with your image.', 'custom-header-extended' ); ?>
				<select name="che-header-text-show" id="che-header-text-show">
					<option value="<?php echo display_header_text() ? 'default-show' : 'default-hide'; ?>" <?php selected( empty( $display_text ), true ); ?>><?php _e( 'Default', 'custom-header-extended' ); ?></option>
					<option value="show" <?php selected( $display_text, 'show' ); ?>><?php _e( 'Show header text', 'custom-header-extended' ); ?></option>
					<option value="hide" <?php selected( $display_text, 'hide' ); ?>><?php _e( 'Hide header text', 'custom-header-extended' ); ?></option>
				</select>
			</p>

			<p class="che-header-text-color-section">
				<label for="che-header-text-color"><?php _e( 'Color', 'custom-backgrounds' ); ?></label>
				<input type="text" name="che-header-text-color" id="che-header-text-color" class="che-wp-color-picker" value="#<?php echo esc_attr( $color ); ?>" />
			</p>
			<!-- End header text color. -->

		<?php }

		/* Remove the filter that constrains image sizes. */
		remove_filter( 'editor_max_image_size', array( $this, 'editor_max_image_size' ) );
	}

	/**
	 * Filters the 'editor_max_image_size' hook so that the header image isn't contrained by the theme's
	 * $content_width variable.  This will cause the image width, which can be wider than the content
	 * width to be the incorrect size.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  array   $width_height  Array of the width/height to contrain the image size to.
	 * @param  string  $size          The name of the image size.
	 * @return array
	 */
	public function editor_max_image_size( $width_height, $size ) {

		/* Only modify if the size matches or custom size. */
		return 'che_header_image' === $size ? array( 0, 0 ) : $width_height;
	}

	/**
	 * Saves the data from the custom headers meta box.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  int     $post_id
	 * @param  object  $post
	 * @return void
	 */
	function save_post( $post_id, $post ) {

		/* Verify the nonce. */
		if ( !isset( $_POST['che_meta_nonce'] ) || !wp_verify_nonce( $_POST['che_meta_nonce'], plugin_basename( __FILE__ ) ) )
			return;

		/* Get the post type object. */
		$post_type = get_post_type_object( $post->post_type );

		/* Check if the current user has permission to edit the post. */
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) || 'revision' == $post->post_type )
			return;

		/* Get the attachment ID. */
		$image_id = absint( $_POST['che-header-image'] );

		/* Set up an array of meta keys and values. */
		$meta = array(
			'_custom_header_image_id' => $image_id,
		);

		/* Add the image to the pool of uploaded header images for this theme. */
		if ( 0 < $image_id ) {

			$is_custom_header = get_post_meta( $image_id, '_wp_attachment_is_custom_header', true );

			if ( $is_custom_header !== get_stylesheet() )
				update_post_meta( $image_id, '_wp_attachment_is_custom_header', get_stylesheet() );
		}

		/* Only run if the current theme allows for header text. */
		if ( current_theme_supports( 'custom-header', 'header-text' ) ) {

			/* Determine the display header text meta value. */
			if ( in_array( $_POST['che-header-text-show'], array( 'show', 'hide' ) ) )
				$display_header_text = $_POST['che-header-text-show'];
			else
				$display_header_text = '';

			/* Determine the header text color meta value. */
			if ( 'hide' === $display_header_text )
				$color = 'blank';
			else
				$color = preg_replace( '/[^0-9a-fA-F]/', '', $_POST['che-header-text-color'] );

			/* Add the meta key/value pairs to the meta array. */
			$meta['_custom_header_text_display'] = $display_header_text;
			$meta['_custom_header_text_color']   = $color;
		}

		/* Loop through the meta array and add, update, or delete the post metadata. */
		foreach ( $meta as $meta_key => $new_meta_value ) {

			/* Get the meta value of the custom field key. */
			$meta_value = get_post_meta( $post_id, $meta_key, true );

			/* If a new meta value was added and there was no previous value, add it. */
			if ( $new_meta_value && '' == $meta_value )
				add_post_meta( $post_id, $meta_key, $new_meta_value, true );

			/* If the new meta value does not match the old value, update it. */
			elseif ( $new_meta_value && $new_meta_value != $meta_value )
				update_post_meta( $post_id, $meta_key, $new_meta_value );

			/* If there is no new meta value but an old value exists, delete it. */
			elseif ( '' == $new_meta_value && $meta_value )
				delete_post_meta( $post_id, $meta_key, $meta_value );
		}
	}

	/**
	 * Adds support, rating, and donation links to the plugin row meta on the plugins admin screen.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  array  $meta
	 * @param  string $file
	 * @return array
	 */
	public function plugin_row_meta( $meta, $file ) {

		if ( preg_match( '/custom-header-extended\.php/i', $file ) ) {
			$meta[] = '<a href="http://themehybrid.com/support">' . __( 'Plugin support', 'custom-header-extended' ) . '</a>';
			$meta[] = '<a href="http://wordpress.org/plugins/custom-header-extended">' . __( 'Rate plugin', 'custom-header-extended' ) . '</a>';
			$meta[] = '<a href="http://themehybrid.com/donate">' . __( 'Donate', 'custom-header-extended' ) . '</a>';
		}

		return $meta;
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

CHE_Custom_Headers_Admin::get_instance();

?>