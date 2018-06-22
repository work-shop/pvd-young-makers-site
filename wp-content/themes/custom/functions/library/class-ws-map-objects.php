<?php



class WS_Map_Objects {

    /**
     * this method consumes an array of post objects from wordpress
     * and transforms them into an array to be passed to the
     * timber context.
     *
     * @param $objects array() an array of WP_Post objects
     * @return $array an array of objects suitable for JSON serialization.
     */
    public static function build_map_objects( $objects ) {

        $results = array();

        foreach ( $objects as $post ) {

            switch ( $post->post_type ) {
                case 'locations':
                    array_push( $results, static::build_location( $post ) );
                    break;

                case 'events':
                    array_push( $results, static::build_event( $post ) );
                    break;


                case 'tools':
                    array_push( $results, static::build_tool( $post ) );
                    break;


                default:
                    break;
            }

        }


    }


    public static function build_location( $location ) {

        //var_dump( $location );

        // getting the id of the passed location WP_Post.
        $id = $location->ID;

        // building an object with ACF custom fields.
        $result = array(
            'id' => $id,
            'title' => $location->post_title,
            'location_information' => array(
                'media_type' => get_field('media_type', $id ),
                'image' => get_field('image', $id ),
                'video' => get_field('video', $id )
            ),
            'tools' => get_field('tools', $id)

        );


        // mapping across a set of associated objects to get ACF custom fields.
        $result['tools'] = array_map( function( $tool_object ) {

            $tool_id = $tool_object->ID;

            return array(
                'id' => $tool_id,
                'title' => $tool_object->post_title,
                'description' => get_field('tool_description', $tool_id )
            );

        }, $result['tools'] );



        // building a timber context from the resulting object.
        $timber_context = array(
            'title' => $location->post_title,
            'primary_pictogram' => '',
            'primary_text' => '',
            'secondary_pictogram' => '',
            'secondary_text' => '',
            'image_src' => '',
        );



        // rendering the timber context into the output buffer.
        ob_start();

        Timber::render('partials/card.twig', $timber_context);

        // storing the output buffer in a variable
        $string_result = ob_get_clean();

        // returning the ready to render "json" object for timber rendering.
        return array(
            'post_type' => 'location',
            'id' => $id,
            'location' => get_field('location_address', $id),
            'rendered' => $string_result
        );

    }


}
