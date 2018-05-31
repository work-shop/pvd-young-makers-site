<?php

class PVDYM_Event extends WS_Custom_Post_Type {

    public static $linked_post_type = true;

    public static $managed_classes = array( PVDYM_Location, PVDYM_Tool );

    public static $slug = 'events';

    public static $singular_name = 'Event';

    public static $plural_name = 'Events';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-calendar',
        'hierarchical'              => false,
        'has_archive'               => true,
        'menu_position'             => 2,
        'supports'                  => array(
                                        'title',
                                        'revisions'
                                    ),
        'rewrite'                   => array(
                                        'slug' => 'events',
                                        'with_front' => false,
                                        'feeds' => true,
                                        'pages' => true
                                    ),
        'taxonomies'                => array( '' )

    );

    public static $query_options = array(

    );

    public static $field_keys = array(
        'tools' => 'field_5b0b28a54688b',
        'location' => 'field_5b0b28c94688c'
    );

    /**
     * @param array $meta the meta items being passed for update.
     * @param int $post_id the id of the post being updated
     *
     */
    public static function update_meta( $post_id ) {

        // $locations = get_field( 'location', $post_id );
        // $tools = get_field('tools', $post_id );

        $new_location = $_POST['acf'][ self::$field_keys[ 'location' ] ];
        $old_location = get_field( 'location', $post_id );
        $old_location = ( $old_location ) ? array_map( function( $x ) { return $x->ID; }, $old_location) : array();

        $new_tools = $_POST['acf'][ self::$field_keys[ 'tools' ] ];
        $old_tools = get_field('tools', $post_id );
        $old_tools = ( $old_tools ) ? array_map( function($x){return $x->ID;}, $old_tools) : array();

        self::transfer_to_linked_post_type( PVDYM_Location::$field_keys[ 'events' ], $new_location, $old_location, $post_id );
        self::transfer_to_linked_post_type( PVDYM_Tool::$field_keys[ 'events' ], $new_tools, $old_tools, $post_id );


    }

    public static function save_post( $post_id ) {

        if ( static::$slug != get_post_type( $post_id ) ) return;

        remove_action( 'acf/save_post', array( get_called_class(), 'save_post' ), 50  );

        $event_name = get_field( 'event_name', $post_id );
        $event_slug = strtolower( $event_name );

        $event_start = get_field('event_start', $post_id );
        $event_end = get_field('event_end', $post_id );

        $date_string = self::get_date_string( $event_start, $event_end );

        wp_update_post(array(
            'ID' => $post_id,
            'post_title' => $event_name . ' ' . $date_string['title'],
            'post_name' => $event_slug . '--' . $date_string['slug']
        ));

        add_action( 'acf/save_post', array( get_called_class(), 'save_post' ), 50  );

    }


    public static function get_date_string( $event_start, $event_end ) {
        $dt_start = DateTime::createFromFormat('d/m/Y g:i a', $event_start );
        $dt_end = DateTime::createFromFormat('d/m/Y g:i a', $event_end );

        $date_start = $dt_start->format('F j, Y');
        $date_end = $dt_end->format('F j, Y');

        if ( $date_start == $date_end ) {

            return array(
                'title' => '(' . $date_start . ')',
                'slug'  => strtolower( $dt_start->format('m-j-y') )
            );

        } else {

            return array(
                'title' => '(' . $date_start . ' to ' . $date_end . ')',
                'slug'  => strtolower( $dt_start->format('m-j-y') . '--' . $dt_end->format('m-j-y') )
            );

        }

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
