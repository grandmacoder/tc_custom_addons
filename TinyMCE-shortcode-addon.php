<?php
/*
Plugin Name: TinyMCE shortcode addon
Description: A WordPress plugin that will add a button to the tinyMCE editor to add shortcodes
Plugin URI: http://www.amyjocarlson.com
Author: Greg Carlson
Author URI: http://www.amyjocarlson.com
Version: 1.0
License: GPL2
*/
new Shortcode_Tinymce();
class Shortcode_Tinymce
{
    public function __construct()
    {
        add_action('admin_head', array($this, 'gc_shortcode_button'));
		
  
    }
    /**
     * Create a shortcode button for tinymce
     *
     * @return [type] [description]
     */
    public function gc_shortcode_button()
    {
		global $typenow;
		// check user permissions
		if ( !current_user_can('edit_posts') && !current_user_can('edit_pages') ) {
			return;
		}
		// verify the post type
		if( ! in_array( $typenow, array( 'post', 'page', 'course_unit' ) ) ){
			return;
			}
		if ( get_user_option('rich_editing') == 'true') 
        {
            add_filter( 'mce_external_plugins', array($this, 'gc_add_buttons' ));
            add_filter( 'mce_buttons_2', array($this, 'gc_register_buttons' ));
        }
    }
	

    /**
     * Add new Javascript to the plugin scrippt array
     *
     * @param  Array $plugin_array - Array of scripts
     *
     * @return Array
     */
    public function gc_add_buttons( $plugin_array )
    {
        $plugin_array['tc_shortcode_button'] = plugin_dir_url( __FILE__ ) . 'js/shortcode-tinymce-button.js';

        return $plugin_array;
    }

    /**
     * Add new button to tinymce
     *
     * @param  Array $buttons - Array of buttons
     *
     * @return Array
     */
    public function gc_register_buttons( $buttons )
    {
        array_push( $buttons, 'tc_shortcode_button' );
        return $buttons;
    }
}
?>