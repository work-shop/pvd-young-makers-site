<?php

abstract class WS_Custom_Post_Type {

    /**
     * A simple helper utility method to check a map for a specific key,
     * and return a default of it's not present.
     */
    private static function default_for_key( $key, $options, $default ) {
        return array_key_exists( $key, $options ) ? $options[$key] : $default;
    }

    /**
     * The register static method is used to register the instance post type
     * in WordPress
     */
    public static function register( ) {

        if ( function_exists( 'register_post_type' ) ) {
            register_post_type(
                static::$slug,
                array(
                    'labels' => array(
                        'name'                          => static::$plural_name,
                        'singular_name'                 => static::$singular_name,
                        'add_new'                       => 'Add New',
                        'add_new_item'                  => 'Add New ' . static::$singular_name,
                        'edit_item'                     => 'Edit ' . static::$singular_name,
                        'new_item'                      => 'New ' . static::$singular_name,
                        'view_item'                     => 'View ' . static::$singular_name,
                        'view_items'                    => 'View ' . static::$plural_name,
                        'search_items'                  => 'Search ' . static::$plural_name,
                        'not_found'                     => 'No ' . static::$plural_name . ' found',
                        'not_found_in_trash'            => 'No ' . static::$plural_name . ' found in the trash',
                        'parent_item_colon'             => 'Parent ' . static::$singular_name. ':',
                        'all_items'                     => 'All ' . static::$plural_name,
                        'archives'                      => static::$singular_name . ' List',
                        'attributes'                    => static::$singular_name . ' Attributes',
                        'insert_into_item'              => 'Insert into ' . static::$singular_name,
                        'uploaded_to_this_item'         => 'Uploaded to this ' . static::$singular_name,
                        'featured_image'                => static::$singular_name . ' Featured Image',
                        'set_featured_image'            => 'Set Featured Image',
                        'remove_featured_image'         => 'Remove ' . static::$singular_name . ' Image',
                        'use_featured_image'            => 'Use as ' . static::$singular_name . ' Image',
                        'menu_name'                     => static::$plural_name // Default
                        /*
                        'filter_items_list'             => '',
                        'items_list_navigation'         => '',
                        'items_list'                    => '',
                        'name_admin_bar'                => ''
                         */

                    ),
                    'public' => true,
                    'menu_position' => WS_Custom_Post_Type::default_for_key( 'menu_position', static::$post_options, 5), // Before Posts Divider
                    'menu_icon' => WS_Custom_Post_Type::default_for_key( 'menu_icon',  static::$post_options, 'dashicons-posts'),
                    // 'capabilities_type' => array(str_replace(' ', '_', strtolower(  static::$singular_name ) ), str_replace(' ', '_', strtolower(  static::$plural_name )) ),
                    'hierarchical' => WS_Custom_Post_Type::default_for_key( 'hierarchical',  static::$post_options, false),
                    'supports' => WS_Custom_Post_Type::default_for_key( 'supports', static::$post_options, array()),
                    'taxonomies' => array_merge( WS_Custom_Post_Type::default_for_key( 'taxonomy',  static::$post_options, array()), array('groups') ),
                    'has_archive' => WS_Custom_Post_Type::default_for_key( 'has_archive',  static::$post_options, true),
                    'rewrite' => WS_Custom_Post_Type::default_for_key( 'rewrite',  static::$post_options, array() ),
                    'query_var' => true,
                    'can_export' => true,
                    'show_in_rest' => true
                )

            );

        }

        add_action( 'acf/save_post', array( get_called_class(), 'link_referenced_posts' ), 1 );
        add_action( 'acf/save_post', array( get_called_class(), 'save_post' ), 50 );

    }

    /**
     * A template function for a save_post hook,
     * which can be overridden by subclasses.
     */
    public static function save_post( $post_id ) {}

    /**
     * Should be updated in subclasses that manage other post types.
     * This method gets the associated metadata for this post type.
     */
    public static function get_meta( $post_id ) { return array(); }

    /**
    * Should be updated in subclasses that manage other post types.
    * This method updates the associated metadata for this post type.
     */
    public static function update_meta( $post_id ) { return true; }


    /**
     * link any posts references in an ACF update to this page.
     *
     * @param int $post_id The post ID.
     */
    public static function link_referenced_posts( $post_id ) {
        // if we're not in the managed post type, drop it like it's hot.
        if ( static::$linked_post_type && static::$slug == get_post_type( $post_id ) ) {
            if ( !empty( $_POST['acf'] ) ) {

                static::remove_actions_for_managed_classes();

                static::update_meta( $post_id );

                static::restore_actions_for_managed_classes();

            }

        }

    }


    public static function remove_actions_for_managed_classes() {
        foreach ( static::$managed_classes as $managing_class ) { remove_action('acf/save_post', array( $managing_class, 'link_referenced_posts' ), 1 ); }
    }

    public static function restore_actions_for_managed_classes() {
        foreach ( static::$managed_classes as $managing_class ) { add_action('acf/save_post', array( $managing_class, 'link_referenced_posts' ), 1 ); }
    }


    /**
     * Utility method for transferring post-types relationships
     * from one side of a relationship field to the other.
     *
     * @param string field_name the name of the foreign field to update, on the target linked post type.
     * @param array<string> updating_field_state an array of id strings representing new posts.
     * @param array<int> $old_field_state an array of id ints representing the old posts in the field.
     *
     */
    public static function transfer_to_linked_post_type( $field_name, $new_field_state, $old_field_state, $source_post_id, $strict = false ) {

        $new_field_state = ( empty( $new_field_state ) ) ? array() : $new_field_state;

        // echo "\n\n 'new state:'";
        // var_dump( $new_field_state );
        // echo "\n\n 'old state:'";
        // var_dump( $old_field_state );


        foreach ( $new_field_state as $new_id ) {

            // cast the id to an int for clearer comparison.
            $new_id = (int) $new_id;

            // If this new element is already in the array, just carry on.
            // it should have been linked in a previous pass.
            // In this case, this id is in the old field state, and
            // in the new fieldstate, meaning it hasn't changed at all. pass along.
            if ( !in_array( $new_id, $old_field_state, true ) ) {

                // if it's down here,
                // otherwise, get the target field state from the other post, designated $id.
                $conserved_foreign_field_state = get_field( $field_name, $new_id );
                $conserved_foreign_field_state = ( $conserved_foreign_field_state ) ? array_map( function($x) { return (int) $x->ID; }, $conserved_foreign_field_state ) : array();
                // in php, arrays are assigned by copy, don't worry.
                // add the id to the a foreign update
                array_push( $conserved_foreign_field_state, $source_post_id );

                update_field( $field_name, $conserved_foreign_field_state, $new_id );

            } else {

                // remove this updated id from the old field state,
                // at the end of this loop, we'll have the set of posts to
                // remove this post id from.
                $old_field_state = array_remove( $new_id, $old_field_state );

            }

        }
        // echo "\n\n 'posts to remove:'";
        // var_dump( $old_field_state );

        foreach ( $old_field_state as $old_id ) {

            $old_id = (int) $old_id;

            $outdated_foreign_field_state = get_field( $field_name, $old_id );
            $outdated_foreign_field_state = ( $outdated_foreign_field_state ) ? array_map( function($x) { return (int) $x->ID; }, $outdated_foreign_field_state ) : array();

            $new_foreign_field_state = array_remove( $source_post_id, $outdated_foreign_field_state );

            // echo "\n\n 'removing post:'";
            // var_dump( $new_foreign_field_state );

            update_field( $field_name, $new_foreign_field_state, $old_id );

        }


    }


    /**
     * This static method retrieves a set of posts for the child's post-type.
     */
    public static function get_posts( $options = array() ) {
        if ( function_exists('get_posts') ) {

            $called_class = get_called_class();
            $opts = array_merge( static::$query_options, $options, array( 'post_type' => static::$slug ) );

            foreach ( ($posts = get_posts( $opts )) as $key => $value ) {
                $posts[ $key ] = new $called_class( $value );
            }

            return $posts;

        } else {

            return array();

        }
    }


    /**
     * ==== Instance Members and Methods ====
     */

    protected $post;

    public function __construct( $post ) {
        $this->post = $post;
    }

    public abstract function validate();

    public abstract function create();

    // public abstract function render_card();
    //
    // public abstract function render_page();

}




?>
