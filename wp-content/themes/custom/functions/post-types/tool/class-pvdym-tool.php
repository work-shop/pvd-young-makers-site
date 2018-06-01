<?php

class PVDYM_Tool extends WS_Custom_Post_Type {

    public static $linked_post_type = true;

    public static $managed_classes = array( 'PVDYM_Event', 'PVDYM_Location' );

    public static $slug = 'tools';

    public static $singular_name = 'Tool';

    public static $plural_name = 'Tools';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-hammer',
        'hierarchical'              => false,
        'has_archive'               => true,
        'menu_position'             => 4,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'tools',
                                        'with_front' => false,
                                        'feeds' => true,
                                        'pages' => true
                                    ),
        'taxonomies'                => array( '' )

    );

    public static $query_options = array(

    );

    public static $field_keys = array(
        'locations' => 'field_5b0b11328eff0',
        'events' => 'field_5b0b11708eff1'
    );


    /**
     * @param array $meta the meta items being passed for update.
     * @param int $post_id the id of the post being updated
     *
     */
    public static function update_meta( $post_id ) {

        $new_locations = $_POST['acf'][ self::$field_keys[ 'locations' ] ];
        $old_locations = get_field( 'locations', $post_id );
        $old_locations = ( $old_locations ) ? array_map( function( $x ) { return $x->ID; }, $old_locations) : array();

        $new_events = $_POST['acf'][ self::$field_keys[ 'events' ] ];
        $old_events = get_field('events', $post_id );
        $old_events = ( $old_events ) ? array_map( function($x){return $x->ID;}, $old_events) : array();

        self::transfer_to_linked_post_type( PVDYM_Location::$field_keys[ 'tools' ], $new_locations, $old_locations, $post_id );
        self::transfer_to_linked_post_type( PVDYM_Event::$field_keys[ 'tools' ], $new_events, $old_events, $post_id );

    }


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
