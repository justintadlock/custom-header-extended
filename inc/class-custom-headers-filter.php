<?php
/**
 * Handles the front end display of custom headers.  This class will check if a post has a custom
 * header assigned to it and filter the custom header theme mods if so on singular post views. The
 * class also handles the creation of a custom header image size if the current theme doesn't allow
 * for both a flexible width and height header image.
 *
 * @package   CustomHeaderExtended
 * @since     0.1.0
 * @author    Justin Tadlock <justintadlock@gmail.com>
 * @copyright Copyright (c) 2013 - 2015, Justin Tadlock
 * @link      http://themehybrid.com/plugins/custom-header-extended
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

final class CHE_Custom_Headers_Filter {

	/**
	 * Holds the instance of this class.
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    object
	 */
	private static $instance;

	/**
	 * Name of the custom header image size added via add_image_size().
	 *
	 * @since  0.1.0
	 * @access private
	 * @var    string
	 */
	private $size = 'che_header_image';

	/**
	 * Width of the custom header image size.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $width = 0;

	/**
	 * Height of the custom header image size.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    int
	 */
	public $height = 0;

	/**
	 * Whether to hard crop the custom header image size.
	 *
	 * @since  0.1.0
	 * @access public
	 * @var    bool
	 */
	public $crop = true;

	/**
	 * The 'width' argument set by the theme.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    int
	 */
	protected $theme_width = 0;

	/**
	 * The 'height' argument set by the theme.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    int
	 */
	protected $theme_height = 0;

	/**
	 * The 'flex-width' argument set by the theme.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    bool
	 */
	protected $flex_width = false;

	/**
	 * The 'flex-height' argument set by the theme.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    bool
	 */
	protected $flex_height = false;

	/**
	 * The ID of the header image attachment.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    int
	 */
	protected $attachment_id = 0;

	/**
	 * The URL of the header image.
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $url = '';

	/**
	 * Sets up the custom headers support on the front end.  This method is just going to add an action
	 * to the `after_setup_theme` hook with a priority of `95`.  This allows us to hook in after themes
	 * have had a chance to set up support for the "custom-header" WordPress theme feature.  If themes
	 * are doing this any later than this, they probably shouldn't be.  If they're doing so for some
	 * valid reason, it's probably a custom implementation that we don't want to touch.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'add_theme_support' ), 95 );
	}

	/**
	 * Checks if the current theme supports the 'custom-header' feature. If not, we won't do anything.
	 * If the theme does support it, we'll add a custom header callback on 'wp_head' if the theme
	 * hasn't defined a custom callback.  This will allow us to add a few extra options for users.
	 *
	 * @since  0.1.0
	 * @access publc
	 * @return void
	 */
	public function add_theme_support() {

		/* If the current theme doesn't support custom headers, bail. */
		if ( !current_theme_supports( 'custom-header' ) )
			return;

		/* Adds an image size. */
		add_action( 'init', array( $this, 'add_image_size' ) );

		/* Only filter the header image on the front end. */
		if ( !is_admin() ) {

			/* Filter the header image. */
			add_filter( 'theme_mod_header_image', array( $this, 'header_image' ), 25 );

			/* Filter the header image data. */
			add_filter( 'theme_mod_header_image_data', array( $this, 'header_image_data' ), 25 );

			/* Filter the header text color. */
			if ( current_theme_supports( 'custom-header', 'header-text' ) )
				add_filter( 'theme_mod_header_textcolor', array( $this, 'header_textcolor' ), 25 );
		}
	}

	/**
	 * Adds a custom header image size for the heaader image.  The only situation in which a custom size
	 * is not created is when the theme supports both 'flex-width' and 'flex-height' header images.  In
	 * that case, the $size property is set to the WordPress 'full' image size.
	 *
	 * The image size created is based off the 'width', 'height', 'flex-width', and 'flex-height' arguments
	 * set by the theme when adding support for 'custom-header'.  If 'flex-width' or 'flex-height' is set
	 * to TRUE, then the image size values for width and height will be set to `9999`.  Otherwise, the
	 * width and height are set to the corresponding theme's 'width' and 'height' arguments.
	 *
	 * @since  0.1.0
	 * @access public
	 * @return void
	 */
	public function add_image_size() {

		/* Get the theme's 'custom-header' arguments needed. */
		$this->theme_width  = get_theme_support( 'custom-header', 'width'       );
		$this->theme_height = get_theme_support( 'custom-header', 'height'      );
		$this->flex_width   = get_theme_support( 'custom-header', 'flex-width'  );
		$this->flex_height  = get_theme_support( 'custom-header', 'flex-height' );

		/*
		 * Set the $crop property based off the $flex_width and $flex_height properties.  If either of
		 * of the properties are TRUE, we'll do a "soft" crop.  Otherwise, we'll use a "hard" crop.
		 */
		$this->crop = $this->flex_width || $this->flex_height ? false : true;

		/* If the theme has set a width/height, use them.  Otherwise set them to "9999". */
		$this->width  = 0 < $this->theme_width  ? absint( $this->theme_width )  : 9999;
		$this->height = 0 < $this->theme_height ? absint( $this->theme_height ) : 9999;

		/* === Set the image size. */

		/*
		 * Allow devs/users to hook in to overwrite the available object properties before an image
		 * size is added.  This will allow them to further define how their header image size is
		 * handled.  Really, the only things worth changing are the $width, $height, and/or $crop
		 * properties.  This script defines these based on the theme, but it's not as flexible as
		 * possible because WordPress really needs to allow for options like 'min-height', 'min-width',
		 * 'max-height', and 'max-width' to really make for the most accurate cropping.
		 */
		do_action( 'che_pre_add_image_size', $this );

		/*
		 * If the theme allows both flexible width and height, don't add an image size. Just use the
		 * default WordPress "full" size.
		 */
		if ( $this->flex_width && $this->flex_height )
			$this->size = 'full';

		/*
		 * If $flex_width is supported but not $flex_height, soft crop an image wih the set height and
		 * a width of "9999" to handle any width.
		 */
		elseif ( $this->flex_width && !$this->flex_height )
			add_image_size( $this->size, 9999, $this->height, $this->crop );

		/*
		 * If $flex_height is supported but not $flex_width, soft crop an image wih the set width and
		 * a height of "9999" to handle any height.
		 */
		elseif ( !$this->flex_width && $this->flex_height )
			add_image_size( $this->size, $this->width, 9999, $this->crop );

		/*
		 * If neither $flex_width nor $flex_width is supported, hard crop an image with the set width
		 * and height.
		 */
		else
			add_image_size( $this->size, $this->width, $this->height, $this->crop );
	}

	/**
	 * Filters the 'theme_mod_header_image' hook.  Checks if there's a featured image with the
	 * correct dimensions to replace the header image on single posts.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  string  $url  The URL of the header image.
	 * @return string
	 */
	public function header_image( $url ) {

		/* If we're not viewing a singular post, return the URL. */
		if ( !is_singular() )
			return $url;

		/* Get the queried post data. */
		$post    = get_queried_object();
		$post_id = get_queried_object_id();

		/* If the post type doesn't support 'custom-header', return the URL. */
		if ( !post_type_supports( $post->post_type, 'custom-header' ) )
			return $url;

		/* This filter makes sure the theme's $content_width doesn't mess up our header image size. */
		add_filter( 'editor_max_image_size', array( $this, 'editor_max_image_size' ), 10, 2 );

		/* Get the header image attachment ID. */
		$this->attachment_id = get_post_meta( $post_id, '_custom_header_image_id', true );

		/* If an attachment ID was found, proceed to setting up the the header image. */
		if ( !empty( $this->attachment_id ) ) {

			/* Get the attachment image data. */
			$image = wp_get_attachment_image_src( $this->attachment_id, $this->size );

			/* If no image data was found, return the original URL. */
			if ( empty( $image ) )
				return $url;

			/* If the theme supports both a flexible width and height, just set the image data. */
			if ( $this->flex_width && $this->flex_height ) {

				$this->url    = esc_url( $image[0] );
				$this->width  = absint(  $image[1] );
				$this->height = absint(  $image[2] );
			}

			/* If the theme supports a flexible width but not height, make sure the height is correct. */
			elseif ( $this->flex_width && !$this->flex_height ) {

				if ( $image[2] == $this->height ) {
					$this->url    = esc_url( $image[0] );
					$this->width  = absint(  $image[1] );
					$this->height = absint(  $image[2] );
				}
			}

			/* If the theme supports a flexible height but not width, make sure the width is correct. */
			elseif ( !$this->flex_width && $this->flex_height ) {

				if ( $image[1] == $this->width ) {
					$this->url    = esc_url( $image[0] );
					$this->width  = absint(  $image[1] );
					$this->height = absint(  $image[2] );
				}
			}

			/* If the theme doesn't support flexible width and height, make sure the width and height are correct. */
			elseif ( !$this->flex_width && !$this->flex_height ) {

				if ( $image[1] == $this->width && $image[2] == $this->height  ) {
					$this->url    = esc_url( $image[0] );
					$this->width  = absint(  $image[1] );
					$this->height = absint(  $image[2] );
				}
			}
		}

		/* Remove the filter that constrains image sizes. */
		remove_filter( 'editor_max_image_size', array( $this, 'editor_max_image_size' ) );

		/* Return the custom URL if we have one. Else, return the original URL. */
		return !empty( $this->url ) ? esc_url( $this->url ) : $url;
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
		return $this->size === $size ? array( 0, 0 ) : $width_height;
	}

	/**
	 * Filters the 'theme_mod_header_image_data' hook.  This is used to set the header image data for
	 * the custom header image being used.  Most importantly, it overwrites the `width` and `height`
	 * attributes so themes that use this will have the correct width and height.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  array  $data  Header image data.
	 * @return array
	 */
	public function header_image_data( $data ) {

		/* Only set the data if viewing a single post and if we have a header image URL. */
		if ( !is_singular() || !empty( $this->url ) ) {

			$new_data['attachment_id'] = $this->attachment_id;
			$new_data['url']           = $this->url;
			$new_data['thumbnail_url'] = $this->url;
			$new_data['width']         = !$this->flex_width  ? $this->theme_width  : $this->width;
			$new_data['height']        = !$this->flex_height ? $this->theme_height : $this->height;

			/*
			 * WordPress seems to be inconsistent with whether this is an array or object. If the
			 * user has saved header options, it seems to be an object. Else, it's an array.
			 */
			$data = is_object( $data ) ? (object) $new_data : $new_data;
		}

		return $data;
	}

	/**
	 * Filters the 'theme_mod_header_textcolor' hook.  This is a bit tricky since WP actually puts two
	 * options (whether to display header text and the text color itself) under the same
	 * 'header_textcolor' theme mod.  To deal with this, the plugin has two separate post meta keys.
	 * The first deals with showing the header text (default, show, hide).  The second allows the
	 * user to select a custom color.
	 *
	 * Note that a 'blank' text color means to hide the header text.
	 *
	 * @since  0.1.0
	 * @access public
	 * @param  array  $data  Header image data.
	 * @return array
	 */
	public function header_textcolor( $textcolor ) {

		/* If we're not viewing a singular post, return the URL. */
		if ( !is_singular() )
			return $textcolor;

		/* Get the queried post data. */
		$post    = get_queried_object();
		$post_id = get_queried_object_id();

		/* If the post type doesn't support 'custom-header', return the URL. */
		if ( !post_type_supports( $post->post_type, 'custom-header' ) )
			return $textcolor;

		/* Get the header text metadata for this post. */
		$has_color    = get_post_meta( $post_id, '_custom_header_text_color', true   );
		$display_text = get_post_meta( $post_id, '_custom_header_text_display', true );

		/* If the user has selected to explicitly show the display text. */
		if ( 'show' === $display_text && $has_color )
			$textcolor = $has_color;

		/* If the user has selected to explicitly hide the display text. */
		else if ( 'hide' === $display_text )
			$textcolor = 'blank';

		/* If the user chose the default display option and we're able to overwrite the color. */
		else if ( empty( $display_text ) && 'blank' !== $textcolor && $has_color )
			$textcolor = $has_color;

		/* Return the text color. */
		return $textcolor;
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

CHE_Custom_Headers_Filter::get_instance();

?>