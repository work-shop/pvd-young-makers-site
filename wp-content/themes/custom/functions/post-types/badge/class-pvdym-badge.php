<?php

class PVDYM_Badge extends WS_Custom_Post_Type {

    public static $linked_post_type = true;

    public static $managed_classes = array( 'PVDYM_Event' );

    public static $slug = 'badges';

    public static $singular_name = 'Badge';

    public static $plural_name = 'Badges';

    public static $post_options = array(
        'menu_icon'                 => 'dashicons-awards',
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

    public static $field_keys = array(
        'events' => 'field_5b47c120750aa'
    );

    /**
     * @param array $meta the meta items being passed for update.
     * @param int $post_id the id of the post being updated
     *
     */
    public static function update_meta( $post_id ) {

        $new_events = $_POST['acf'][ self::$field_keys[ 'events' ] ];
        $old_events = get_field( 'events', $post_id );
        $old_events = ( $old_events ) ? array_map( function( $x ) { return $x->ID; }, $old_location) : array();

        self::transfer_to_linked_post_type( PVDYM_Event::$field_keys[ 'badges' ], $new_events, $old_events, $post_id );

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
