<?php



class WS_Map_Objects {

    /**
     * This method consumes an array of post objects from wordpress
     * and transforms them into an array to be passed to the
     * timber context.
     *
     * @param $objects array() an array of WP_Post objects
     * @return $results an array of objects suitable for JSON serialization.
     */
    public static function build_map_objects( $objects ) {

        $results = array();

        foreach ( $objects as $post ) {
            $map_objects = null;

            switch ( $post->post_type ) {
                case 'locations':
                    // each location has a single map object
                    $map_object = static::build_location( $post );
                    if ( $map_object != null ) {
                        $map_objects = array( $map_object );
                    }
                    break;

                case 'events':
                    // each event has a single map object
                    $map_object = static::build_event( $post );
                    if ( $map_object != null ) {
                        $map_objects = array( $map_object );
                    }
                    break;


                case 'tools':
                    // each tool can have multiple map objects
                    $map_objects = static::build_tool( $post );
                    break;


                default:
                    break;
            }

            if ( is_array( $map_objects ) ) {
                $results = array_merge( $results, $map_objects );
            }
        }

        $unique_results = static::remove_duplicates( $results );

        return $unique_results;

    }

    /**
     * Consumes a single WP_Post object, and creates a map object for it.
     *
     * If a location does not have valid geo cooridnates, return null.
     * 
     * @param  $location a WP_Post object
     * @return $map_object the resulting map object suitable for JSON serialization
     */
    public static function build_single_location( $location ) {
        $id = $location->ID;
        $position = get_field('location_address', $id);
        $address = $position['address'];

        if ( ! static::is_valid_position( $position ) ) {
            return null;
        }

        $position = static::ensure_position_float( $position );

        $card_rows = array(
            array(
                'type' => 'text-row',
                'text' => static::format_address( $address )
            )
        );

        // building a timber context from the resulting object.
        $timber_context = array(
            'title' => $location->post_title,
            'rows' => $card_rows
        );

        // rendering the timber context into the output buffer.
        ob_start();

        Timber::render('partials/map-card.twig', $timber_context);

        // storing the output buffer in a variable
        $marker_content = ob_get_clean();

        // returning the ready to render "json" object for timber rendering.
        return array(
            'post_type' => 'location',
            'id' => $id,
            'marker' => array(
                'position' => $position,
                'popup' => array(
                    'content' => $marker_content,
                )
            )
        );
    }

    /**
     * Consumes a single WP_Post object, and creates a map object for it.
     *
     * If a location does not have valid geo cooridnates, return null.
     * 
     * @param  $event       a WP_Post object of type `events`
     * @return $map_object  The map object that represents where the event occurs.
     */
    public static function build_single_event( $event ) {
        $location = static::location_for_event( $event );
        if ( $location ) {
            $map_object = static::build_single_location( $location );
        }
        else {
            $map_object = null;
        }
        return $map_object;
    }


    /**
     * Given a WP_Post of type `locations`, create a single map
     * object that represents the tools and events for the location.
     *
     * If a location does not have valid geo cooridnates, return null.
     * 
     * @param  $location    The location to extract a location from
     * @return $map_object  The map object representing the location.
     */
    public static function build_location( $location ) {

        // getting the id of the passed location WP_Post.
        $id = $location->ID;

        // building an object with ACF custom fields.
        $tools = get_field('tools', $id);
        $events = get_field('events', $id);
        $position = get_field('location_address', $id);

        if ( ! static::is_valid_position( $position ) ) {
            return null;
        }

        $position = static::ensure_position_float( $position );
        
        $card_rows = array();
        
        if ( is_array( $tools ) ) {
            $number_of_tools = count( $tools );
            array_push( $card_rows, array(
                'type' => 'text-pictogram-row',
                'text' =>  $number_of_tools . ' tools',
                'pictogram' => '(',
            ) );
        }
        $default_upcoming_event_text = 'No upcoming events';
        if ( is_array( $events ) ) {
            // get all event end dates
            $event_end_dates = array();
            for ($i=0; $i < count( $events ); $i++) { 
                $event = $events[ $i ];
                $event_id = $event->ID;
                $event_end_string = get_field( 'event_end', $event_id );
                if ( ! is_string( $event_end_string ) ) {
                    break;
                }
                $event_end_date = DateTime::createFromFormat( 'd/m/Y g:i a', $event_end_string );
                array_push( $event_end_dates, $event_end_date );
            }

            // filter event end dates to only include those that are after now
            $now = new DateTime( 'now' );
            $future_event_dates = array();
            for ($i=0; $i < count( $event_end_dates ); $i++) {
                $event_end_date = $event_end_dates[ $i ];
                if ( $event_end_date > $now ) {
                    array_push( $future_event_dates, $event_end_date );
                }
            }

            $number_of_future_events = count( $future_event_dates );

            // Define $upcoming_event_text depending on number of future events
            if ( $number_of_future_events > 0 ) {
                $upcoming_event_text = $number_of_future_events . ' upcoming event';
                if ( $upcoming_event_text > 1 ) {
                    $upcoming_event_text .= 's';
                }
            }
        }
        if ( ! isset( $upcoming_event_text ) ) {
            $upcoming_event_text = $default_upcoming_event_text;
        }

        array_push( $card_rows, array(
            'type' => 'text-pictogram-row',
            'text' =>  $upcoming_event_text,
            'pictogram' => '{',
        ) );

        array_push( $card_rows, array(
            'type' => 'link-pictogram-row',
            'text' => 'Visit page',
            'url' => get_post_permalink( $location )
        ) );


        // building a timber context from the resulting object.
        $timber_context = array(
            'title' => $location->post_title,
            'rows' => $card_rows
        );


        // rendering the timber context into the output buffer.
        ob_start();

        Timber::render('partials/map-card.twig', $timber_context);

        // storing the output buffer in a variable
        $marker_content = ob_get_clean();

        // returning the ready to render "json" object for timber rendering.
        $map_object = array(
            'post_type' => 'location',
            'id' => $id,
            'marker' => array(
                'position' => $position,
                'popup' => array(
                    'content' => $marker_content,
                )
            )
        );

        return $map_object;

    }


    /**
     * Given a WP_Post with type `events`, return an array of
     * of map objects that represent the locations for the tool.
     * 
     * @param  $event         The event to extract a map object from
     * @return $map_objects   The map objects representing the event location
     */
    public static function build_event( $event ) {
        $location = static::location_for_event( $event );
        if ( $location ) {
            $map_object = static::build_location( $location );
        }
        else {
            $map_object = null;
        }
        return $map_object;
    }

    /**
     * Given a WP_Post with type `tools`, return an array of
     * of map objects that represent the locations for the tool.
     * 
     * @param  $tool          The tool to extract map objects from
     * @return $map_objects   The map objects representing tool locations
     */
    public static function build_tool ( $tool ) {
        $id = $tool->ID;
        $locations = get_field( 'locations', $id );

        if ( ! is_array( $locations ) ) {
            // no locations? return an empty array
            return array();
        }

        $possible_map_objects = array_map( function ( $location ) {
            return static::build_location( $location );
        }, $locations );

        $map_objects = array_filter( $possible_map_objects, function ( $map_object ) {
            return $map_object != null;
        } );

        return $map_objects;
    }

    /**
     * Given a array of map objects, return an array of map objects
     * with all duplicate locations removed.
     * 
     * @param  $map_objects         Map objects to filter
     * @return $unique_map_objects  The filtered array of map objects.
     */
    private static function remove_duplicates ( $map_objects ) {
        $indices = array();
        $unique_map_objects = array();

        foreach( $map_objects as $map_object ) {
            $index = $map_object[ 'post_type' ] . $map_object[ 'id' ];
            if ( ! in_array( $index, $indices )  ) {
                array_push( $indices, $index );
                array_push( $unique_map_objects, $map_object );
            }
        }

        return $unique_map_objects;
    }

    private static function is_valid_position( $position ) {
        if ( ! is_array( $position ) ) {
            return false;
        }
        if ( ! ( array_key_exists( 'lat', $position ) &&
                 is_float( floatval( $position->lat ) ) ) ) {
            return false;
        }
        if ( ! ( array_key_exists( 'lng', $position ) &&
                 is_float( floatval( $position->lng ) ) ) ) {
            return false;
        }
        return true;
    }

    private static function ensure_position_float( $position ) {
        return array(
            'lat' => floatval( $position['lat'] ),
            'lng' => floatval( $position['lng'] )
        );
    }

    private static function location_for_event ( $event ) {
        $id = $event->ID;
        $location = get_field( 'location', $id );
        if ( is_array( $location ) && count( $location ) === 1 ) {
            return $location[ 0 ];
        }
        else {
            return null;
        }
    }

    /**
     * Given an `address` string, create line breaks after all commas
     * except the last two. These are use for defining the
     * City, State, Country. These should all be on one line.
     * @param  string $address
     * @return string $formatted_address
     */
    private static function format_address ( $address ) {
        $address_parts = explode( ', ', $address );
        $comma_count = count( $address_parts );
        $commas_to_remove = $comma_count - 2;
        if ( $commas_to_remove < 0 ) {
            $commas_to_remove = 0;
        }
        $address_parts = explode( ',', $address, $commas_to_remove );
        $formatted_address = implode( '<br />', $address_parts );
        return '<p>' . $formatted_address . '</p>';
    }

}
