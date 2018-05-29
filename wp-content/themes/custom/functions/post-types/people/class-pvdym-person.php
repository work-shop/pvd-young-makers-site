<?php

class PVDYM_Person extends WS_Custom_Post_Type {

    public static $linked_post_type = false;

    public static $slug = 'people';

    public static $singular_name = 'Person';

    public static $plural_name = 'People';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-groups',
        'hierarchical'              => false,
        'has_archive'               => false,
        'menu_position'             => 4,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'people',
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
