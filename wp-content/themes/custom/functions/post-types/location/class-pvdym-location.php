<?php

class PVDYM_Location extends WS_Custom_Post_Type {

    public static $slug = 'locations';

    public static $singular_name = 'Location';

    public static $plural_name = 'Locations';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-location-alt',
        'hierarchical'              => false,
        'has_archive'               => false,
        'menu_position'             => 4,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'badges',
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
