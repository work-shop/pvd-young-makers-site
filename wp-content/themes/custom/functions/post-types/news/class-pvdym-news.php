<?php

class PVDYM_News extends WS_Custom_Post_Type {

    public static $linked_post_type = false;

    public static $slug = 'news';

    public static $singular_name = 'News Story';

    public static $plural_name = 'News';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-welcome-widgets-menus',
        'hierarchical'              => false,
        'has_archive'               => false,
        'menu_position'             => 4,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'news',
                                        'with_front' => false,
                                        'feeds' => true,
                                        'pages' => true
                                    ),
        'taxonomies'                => array( '' )

    );

    public static $query_options = array(

    );


    /**
     * ==== Instance Members and Methods ====
     */

    public function __construct( $id ) {

        $this->id = $id;

    }

    public function validate() {

    }

    public function create() {

    }

}

?>
