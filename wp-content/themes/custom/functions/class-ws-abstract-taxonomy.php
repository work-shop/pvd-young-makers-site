<?php

abstract class WS_Taxonomy {

    public static function register() {
        if ( function_exists('register_taxonomy') ) {
            register_taxonomy(
                static::$slug,
                static::$registered_post_types,
                array(
                    'labels' => array(
                        'name'                              => static::$plural_name,
                        'singular_name'                     => static::$singular_name,
                        'menu_name'                         => static::$plural_name,
                        'all_items'                         => 'All ' . static::$plural_name,
                        'edit_item'                         => 'Edit ' . static::$singular_name,
                        'view_item'                         => 'View ' . static::$singular_name,
                        'update_item'                       => 'Update ' . static::$singular_name,
                        'add_new_item'                      => 'Add New ' . static::$singular_name,
                        'new_item_name'                     => 'New ' . static::$singular_name . ' Name',
                        'parent_item'                       => 'Parent '.static::$singular_name,
                        'parent_item_colon'                 => 'Parent ' . static::$singular_name . ':',
                        'search_items'                      => 'Search ' . static::$plural_name,
                        'popular_items'                     => 'Frequently used ' . static::$plural_name,
                        'separate_items_with_commas'        => 'Separate ' . static::$plural_name . ' with commas',
                        'add_or_remove_items'               => 'Add or Remove ' . static::$plural_name,
                        'choose_from_most_used'             => 'Choose from the most frequently used ' . static::$plural_name,
                        'not_found'                         => 'No ' . static::$plural_name . ' found.'
                    ),
                    'public' => static::$public,
                    'show_in_rest' => true,
                    'show_tag_cloud' => false,
                    'show_in_quick_edit' => false,
                    'show_admin_column' => true,
                    'hierarchical' => true,
                    'capabilities' => array(
                        'manage_terms'                      => 'manage_categories',
                        'edit_terms'                        => 'manage_categories',
                        'delete_terms'                      => 'manage_categories',
                        'assign_terms'                      => 'edit_posts'
                    ),
                    'sort' => true
                )
            );
        }
    }

}

?>
