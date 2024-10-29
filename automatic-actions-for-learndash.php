<?php
/**
 * Plugin Name:  Automatic Actions for LearnDash
 * Plugin URI: https://wptrat.com/automatic-actions-for-learndash/
 * Description: [Actions] Enroll users to courses, add them to groups or change their roles on [Triggers] registration/login or on course complete. More actions and triggers to come!
 * Author: Luis Rock
 * Author URI: https://wptrat.com/
 * Version: 1.0.0
 * Text Domain: automatic-actions-for-learndash
 * Domain Path: /languages
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package   Automatic Actions for LearnDash
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define("TRAA_LDACTIONS_VERSION", "1.0.0");
define("TRAA_LDACTIONS_DIR", WP_PLUGIN_DIR . '/automatic-actions-for-learndash');

// Check if LearnDash is active. If not, deactivate...
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if( !is_plugin_active('sfwd-lms/sfwd_lms.php' ) ) {
    add_action( 'admin_init', 'traa_deactivate' );
    add_action( 'admin_notices', 'traa_admin_notice' );
    function traa_deactivate() {
        deactivate_plugins( plugin_basename( __FILE__ ) );
    }
    // Notice
    function traa_admin_notice() { ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong>
                    <?php echo esc_html_e( 'LearnDash LMS is not active: AUTOMATIC ACTIONS FOR LEARNDASH needs it, that\'s why was deactivated', 'automatic-actions-for-learndash' ); ?>
                </strong>
            </p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">
                    Dismiss this notice.
                </span>
            </button>
        </div><?php
                if ( isset( $_GET['activate'] ) ) {
                    unset( $_GET['activate'] ); 
                }        
    } //end function traa_admin_notice
} //end if( !is_plugin_active('sfwd-lms/sfwd_lms.php' ) )

add_action( 'init', 'traa_load_textdomain' );
function traa_load_textdomain() {
  load_plugin_textdomain( 'automatic-actions-for-learndash', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

//Options counter of actions executed
if (false === get_option('traa_ldactions_all_actions') && false === update_option('traa_ldactions_all_actions',false)) {
	add_option('traa_ldactions_all_actions',0); 
}


// Requiring plugin files
require_once('admin/traa-metaboxes.php');
require_once('includes/functions.php');
require_once('includes/callbacks-actions.php');


function traa_ldactions_register_all_scripts_and_styles() {
    wp_register_script('traa_admin_js', plugins_url('assets/js/traa-admin.js',__FILE__ ), ['jquery'],'1.0.0',true);
}
add_action( 'wp_loaded', 'traa_ldactions_register_all_scripts_and_styles' );


//Scripts end styles
function traa_ldactions_enqueue_admin_script( $hook ) {

	global $post_type;
    if( 'ld-actions' !== $post_type) {
        return;
    }
    wp_enqueue_script('traa_admin_js');
}
add_action( 'admin_enqueue_scripts', 'traa_ldactions_enqueue_admin_script' );


//CPT
function traa_ldactions_register_cpt_ld_actions() {

	$labels = [
		"name" => __( "LDActions", "automatic-actions-for-learndash" ),
		"singular_name" => __( "LDAction", "automatic-actions-for-learndash" ),
		"menu_name" => __( "LDActions", "automatic-actions-for-learndash" ),
		"all_items" => __( "All LDActions", "automatic-actions-for-learndash" ),
		"add_new" => __( "Add new", "automatic-actions-for-learndash" ),
		"add_new_item" => __( "Add new LDAction", "automatic-actions-for-learndash" ),
		"edit_item" => __( "Edit LDAction", "automatic-actions-for-learndash" ),
		"new_item" => __( "New LDAction", "automatic-actions-for-learndash" ),
		"view_item" => __( "View LDAction", "automatic-actions-for-learndash" ),
		"view_items" => __( "View LDActions", "automatic-actions-for-learndash" ),
		"search_items" => __( "Search LDActions", "automatic-actions-for-learndash" ),
		"not_found" => __( "No LDActions found", "automatic-actions-for-learndash" ),
		"not_found_in_trash" => __( "No LDActions found in trash", "automatic-actions-for-learndash" ),
		"parent" => __( "Parent LDAction:", "automatic-actions-for-learndash" ),
		"featured_image" => __( "Featured image for this LDAction", "automatic-actions-for-learndash" ),
		"set_featured_image" => __( "Set featured image for this LDAction", "automatic-actions-for-learndash" ),
		"remove_featured_image" => __( "Remove featured image for this LDAction", "automatic-actions-for-learndash" ),
		"use_featured_image" => __( "Use as featured image for this LDAction", "automatic-actions-for-learndash" ),
		"archives" => __( "LDAction archives", "automatic-actions-for-learndash" ),
		"insert_into_item" => __( "Insert into LDAction", "automatic-actions-for-learndash" ),
		"uploaded_to_this_item" => __( "Upload to this LDAction", "automatic-actions-for-learndash" ),
		"filter_items_list" => __( "Filter LDActions list", "automatic-actions-for-learndash" ),
		"items_list_navigation" => __( "LDActions list navigation", "automatic-actions-for-learndash" ),
		"items_list" => __( "LDActions list", "automatic-actions-for-learndash" ),
		"attributes" => __( "LDActions attributes", "automatic-actions-for-learndash" ),
		"name_admin_bar" => __( "LDAction", "automatic-actions-for-learndash" ),
		"item_published" => __( "LDAction published", "automatic-actions-for-learndash" ),
		"item_published_privately" => __( "LDAction published privately.", "automatic-actions-for-learndash" ),
		"item_reverted_to_draft" => __( "LDAction reverted to draft.", "automatic-actions-for-learndash" ),
		"item_scheduled" => __( "LDAction scheduled", "automatic-actions-for-learndash" ),
		"item_updated" => __( "LDAction updated.", "automatic-actions-for-learndash" ),
		"parent_item_colon" => __( "Parent LDAction:", "automatic-actions-for-learndash" ),
	];

	$args = [
		"label" => __( "LDActions", "automatic-actions-for-learndash" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => false,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"rest_namespace" => "wp/v2",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => true,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"can_export" => true,
		"rewrite" => [ "slug" => "ld-actions", "with_front" => true ],
		"query_var" => true,
		"menu_icon" => "dashicons-lightbulb",
		"supports" => [ "title" ],
		"show_in_graphql" => false,
	];
	register_post_type( "ld-actions", $args );
}
add_action( 'init', 'traa_ldactions_register_cpt_ld_actions' );

//Remove Custom Fields meta box
add_action( 'admin_menu' , function() {
	remove_meta_box( 'postcustom' , 'ld-actions' , 'normal' ); 
});
