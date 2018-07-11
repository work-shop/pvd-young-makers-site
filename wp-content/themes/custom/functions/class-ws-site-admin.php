<?php

class WS_Site_Admin {

    public function __construct() {

        add_action('admin_menu', array( $this, 'manage_admin_menu_options' ) );
        add_action('acf/init', array($this, 'add_options_pages'));
        add_action('acf/init', array($this, 'add_google_maps_key'));
        add_action( 'admin_head', array( $this, 'admin_css'));
        add_action( 'admin_head', array( $this, 'render_svg_in_media') );

        add_action('wp_dashboard_setup', array($this, 'remove_dashboard_widgets') );
        add_action('wp_before_admin_bar_render', array($this, 'remove_admin_bar_items'));

        add_filter( 'get_user_metadata', array( $this, 'pages_per_page_wpse_23503'), 10, 4 );
        add_filter( 'upload_mimes', array($this, 'enable_svg_mime_type' ) );


    }


    /**
     * This pair of functions allows and properly renders SVG content
     * in the wordpress media uploader.
     */
    public function enable_svg_mime_type( $mimes ) {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }


    public function render_svg_in_media( ) {
        echo '<style type="text/css">
                .attachement-266x266, .thumbnail-img {
                    width: 100% !important;
                    height: auto !important;
                }
             </style>';
    }

    /**
     * This function manages visibility of different parts of the Admin view.
     */
    public function manage_admin_menu_options() {

        global $submenu;

        remove_meta_box("dashboard_primary", "dashboard", "side");   // WordPress.com blog
        remove_meta_box("dashboard_secondary", "dashboard", "side"); // Other WordPress news

        remove_post_type_support('post', 'comments');
        remove_post_type_support('page', 'comments');

        remove_menu_page('index.php');  // Remove the dashboard link from the Wordpress sidebar.
        remove_menu_page('edit.php');   // Remove the posts link from the Wordpress sidebar.
        remove_menu_page('edit-comments.php');   // Remove the comments link from the Wordpress sidebar.

        if ( !current_user_can( 'administrator' ) ) {
            remove_menu_page('admin.php?page=wc-settings'); // Remove WC Configuration Settings
            remove_menu_page('admin.php?page=gf_edit_forms'); // Remove Gravity Forms Edit Page

            if ( isset( $submenu['themes.php']) ) {
                foreach ($submenu['themes.php'] as $key => $menu_item ) {
                    if ( in_array('Customize', $menu_item ) ) {
                        unset( $submenu['themes.php'][$key] );
                    }
                    if ( in_array('Themes', $menu_item ) ) {
                        unset( $submenu['themes.php'][$key] );
                    }
                }
            }

        }

    }

    /**
     * Additional ACF options pages can be registered here.
     */
    public function add_options_pages() {
        if ( function_exists('acf_add_options_page') ) {
            acf_add_options_page(array(
                "page_title" => "Site Options & Menus",
                "capability" => "edit_posts",
                "position" => 10,
                "icon_url" => "dashicons-admin-home"
            ));
        }
    }

    /**
     * Google Maps API Key is registered here,
     * so that ACF can make requests against
     * Google Maps Platform.
     */
    public function add_google_maps_key() {
        if ( function_exists( 'acf_update_setting' ) ) {
            acf_update_setting('google_api_key', 'AIzaSyDTkjwJK80N7YCWoKjhKz8c3J1tNEbJpRg');
        }
    }


    public function pages_per_page_wpse_23503( $check, $object_id, $meta_key, $single ) {
        if( 'edit_page_per_page' == $meta_key )
            return 100;
        return $check;
    }

    public function admin_css( ) {

        $main_css = '/bundles/admin-bundle.css';

        $compiled_resources_dir = get_template_directory();
        $compiled_resources_uri = get_template_directory_uri();

        $main_css_ver = filemtime( $compiled_resources_dir . $main_css ); // version suffixes for cache-busting.

        wp_enqueue_script('admin_css', $compiled_resources_uri . $main_css, array(), $main_css_ver);

    }


    /**
     * Removes comments icon from the admin bar.
     */
    public function remove_admin_bar_items() {
        global $wp_admin_bar;
        $wp_admin_bar->remove_menu("comments");
    }

    /**
     * remove admin menu home page widgets
     */
    public function remove_dashboard_widgets() {
        remove_meta_box("dashboard_primary", "dashboard", "side");   // WordPress.com blog
        remove_meta_box("dashboard_secondary", "dashboard", "side"); // Other WordPress news

        global $wp_meta_boxes;

        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
        unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
        unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
    }

}

?>
