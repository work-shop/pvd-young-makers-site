<?php

class PVDYM_Location extends WS_Custom_Post_Type {

    public static $linked_post_type = true;

    public static $managed_classes = array( PVDYM_Event, PVDYM_Tool );

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
                                        'slug' => 'locations',
                                        'with_front' => false,
                                        'feeds' => true,
                                        'pages' => true
                                    ),
        'taxonomies'                => array( '' )

    );

    public static $query_options = array(

    );

    public static $field_keys = array(
        'tools' => 'field_5b0b1a4227383',
        'events' => 'field_5b0b1a6d27384'
    );


    /**
     * @param array $meta the meta items being passed for update.
     * @param int $post_id the id of the post being updated
     *
     */
    public static function update_meta( $post_id ) {

        $new_tools = $_POST['acf'][ self::$field_keys[ 'tools' ] ];
        $old_tools = get_field( 'tools', $post_id );
        $old_tools = ( $old_tools ) ? array_map( function( $x ) { return $x->ID; }, $old_tools) : array();

        $new_events = $_POST['acf'][ self::$field_keys[ 'events' ] ];
        $old_events = get_field('events', $post_id );
        $old_events = ( $old_events ) ? array_map( function($x){return $x->ID;}, $old_events) : array();

        self::transfer_to_linked_post_type( PVDYM_Tool::$field_keys[ 'locations' ], $new_tools, $old_tools, $post_id );
        self::transfer_to_linked_post_type( PVDYM_Event::$field_keys[ 'location' ], $new_events, $old_events, $post_id );

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
