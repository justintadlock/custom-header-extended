# Custom Header Extended #

Allows users to create a custom header on a per-post basis.

## Description ##

A plugin for allowing users to set a custom header on a per-post basis. This plugin hooks into the WordPress `custom-header` theme feature and overwrites the values on single post views if the post has been given a custom header.

### Features ###

This plugin creates a custom meta box on the edit post screen. From that point, you can select a custom header image.  You can also select whether to display your header text and its color if your theme supports that option.  The options you choose will be shown on the single post page on the front end.

### Requirements ###

Your theme must support the core WordPress implementation of the [Custom Headers](http://codex.wordpress.org/Custom_Headers) theme feature.

## Installation ##

1. Upload the `custom-header-extended` folder to your `/wp-content/plugins/` directory.
2. Activate the "Custom Header Extended" plugin through the "Plugins" menu in WordPress.
3. Edit a post to add a custom header.

## Frequently Asked Questions ##

### Why was this plugin created? ###

I've always been interested in art direction on blogs. This is just one tool of many that I'm creating and making available via my [Web site](http://themehybrid.com "Theme Hybrid") for making it easier for users to take more control over their styles on a per-post basis.

### Why doesn't it work with my theme? ###

Most likely, this is because your theme doesn't support the `custom-header` WordPress theme feature.  Not all themes do.  This plugin requires that your theme utilize this theme feature to work properly. Unfortunately, there's just no reliable way for the plugin to overwrite the header if the theme doesn't support this feature. You'll need to check with your theme author to see if they'll add support or switch to a different theme.

### My theme supports 'custom-header' but it doesn't work! ###

That's unlikely. Just to make sure, check with your theme author and make sure that they support the WordPress `custom-header` theme feature. It can't be something custom your theme author created. It must be the WordPress feature.

Assuming your theme does support `custom-header` and this plugin still isn't working, your theme is most likely implementing the custom header feature incorrectly. However, I'll be more than happy to take a look.

### Why don't the header text show/hide and color options appear? ###

This actually depends on your theme.  Some themes support the `header-text` option for `custom-header` and some don't.  These options will only appear if your currently-active theme supports that feature.

### Why doesn't the header text show/hide option work? ###

This is because your theme has implemented this feature incorrectly.  I'll be more than happy to help your theme author with this if you let them know to get in touch with me.

### Why doesn't the header text color work? ###

This is because your theme has implemented this feature incorrectly.  I'll be more than happy to help your theme author with this if you let them know to get in touch with me.

### When I switch themes, why do some header images disappear? ###

Because of the way the WordPress `custom-header` feature works, themes can define varying size options for header images depending on their design.  These options are:

* Fixed width
* Fixed height
* Flexible width
* Flexible height

What this plugin does is create a custom image size based off your current theme's header dimension options.  This means that the dimensions aren't going to be accurate (and might not work) from theme to theme.  Therefore, you'll need to resize your images when you switch to a new theme (see plugin recommendations below).  I actually recommend this anyway, because you'll often run into the similar issues with featured images.

### How do I use old images as my header image? ###

This plugin creates image sizes for newly-uploaded images only.  However, if using one of the resizing plugins listed below, old images can be resized to be used as header images.

### How do I fix the "Your image width/height is too small/large" error? ###

This error message is only shown on the edit post screen.  It's never shown publicly on your site.

The error message means that the image you're attempting to use isn't the correct size.  You'll need to upload a larger image in order for it to be used on your site.

If you see this after switching themes, I recommend using one of the resizing plugins listed below to correct the image dimensions.

### Recommended plugins for resizing images? ###

Here are two plugins I highly recommend that will resize your images for you when you switch themes.  I've used them both on my own sites and tested them to be sure they work alongside this plugin.  Each plugin also has a high number of ratings and users.  I recommend using them any time you switch to a new theme.

* [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/)
* [AJAX Thumbnail Rebuild](http://wordpress.org/plugins/ajax-thumbnail-rebuild/)

### What are your recommended header dimensions? ###

Because each theme is different, it's impossible to say for sure.  However, this plugin will create a cropped version of any image that is too large once you upload it.  Therefore, it's usually better to upload a larger image rather than a smaller image.

With that said, some themes support completely flexible widths and heights for their header images.  When that's the case, the plugin doesn't create a custom image size.  It just uses the "full" (i.e., original) image size.

### How do I add support for this in a theme? ###

Your theme must support the [Custom Headers](http://codex.wordpress.org/Custom_Headers) feature for this plugin to work.

If you're a theme author, consider adding support for this if you can make it fit in with your design. The following is the basic code, but you'll need to do more work.  Check out the above link for more details.

	add_theme_support( 'custom-header' );

### Can other users on my site add headers? ###

Some sites have multiple writers/authors who write posts.  However, since custom headers tend to be a design-related option, only administrators have access to altering them in a default WordPress install.  There is a way around this, which is to give permission by assigning a capability to user roles.

In order to manage capabilities and roles, you need a plugin like [Members](http://wordpress.org/plugins/members), which is a plugin I created for managing sites with multiple users.  It's something you should be using for any site with multiple levels of users (i.e., all users are not admins).  This plugin will allow you to add or create new capabilities for any role.

The capability required for being able to add per-post headers is one of the following:

* `che_edit_header` - The user can edit headers on posts they have written.
* `edit_theme_options` - The user can edit all WordPress theme options (**not** recommended for anyone other than administrators).

Using the Members plugin, you can assign one of the above capabilities to allow other, non-administrator users to edit headers for their posts.

Also, a user must have the `upload_files` capability to upload new images, but this is a WordPress thing and not specific to the plugin.

### Does it support custom post types? ###

The plugin supports WordPress posts and pages out of the box.

Because it's impossible for me to accurately determine what a custom post type should do, I've left it up to those of you actually building custom post type plugins to support this. If you'd like to allow custom headers on singular views of your post type, add `'custom-header'` to your post type supports array during registration. Obviously, your post type would need to be publicly queryable and display something on the front end for single post views.

Or, if you have a plugin with post types that you'd like for me to add support for, let me know. I'll be more than happy to add the support via this plugin.

### Can you help me? ###

Unfortunately, I cannot provide free support for this plugin to everyone. I honestly wish I could. My day job requires too much of my time for that, which is how I pay the bills and eat. However, you can sign up for my [support forums](http://themehybrid.com/support) for full support of this plugin, all my other plugins, and all my themes for one price.

## Screenshots ##

1. Custom header meta box.
2. Custom header meta box on the edit post screen.
3. Custom header on a single post page.