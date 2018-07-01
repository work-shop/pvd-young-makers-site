<?php

$post = new TimberPost();
$post_name = $post->post_name;

$templates = array('page-' . $post_name . '.twig', 'page.twig');
if (is_front_page()) {
    array_unshift($templates, 'home.twig');
}

$context = Timber::get_context();
$context['post'] = $post;

// Home page
if (is_front_page()) {
    $events = Timber::get_posts('post_type=events&numberposts=3');
    foreach ($events as $event) {
        $event->location_name = Timber::get_post('post_type=locations&p=' . $event->location[0])->post_title;
    }
    $context['events'] = $events;
    
    $locations_for_map = Timber::get_posts('post_type=locations');
    $map_data = WS_Map_Objects::build_map_objects( $locations_for_map );
    if ( count( $map_data ) > 0 ) {
        $context['map_options'] = array(
            'data' => $map_data
        );
    }
}

// Making and Learning page
if ($post_name === 'making-and-learning') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=8');
    
    $locations_for_map = Timber::get_posts('post_type=locations');
    $map_data = WS_Map_Objects::build_map_objects( $locations_for_map );
    if ( count( $map_data ) > 0 ) {
        $context[ 'map_options' ] = array(
            'data' => $map_data
        );
    }
}

// Tools page
if ($post_name === 'tools') {
    $machines_args = array(
        'post_type' => 'tools',
        'tax_query' => array(
            array(
                'taxonomy' => 'tool-types',
                'field' => 'slug',
                'terms' => 'machines',
            ),
        ),
    );
    $context['machines'] = Timber::get_posts($machines_args);

    $materials_args = array(
        'post_type' => 'tools',
        'tax_query' => array(
            array(
                'taxonomy' => 'tool-types',
                'field' => 'slug',
                'terms' => 'materials',
            ),
        ),
    );
    $context['materials'] = Timber::get_posts($materials_args);

    $all_tools = array_merge( $context['machines'], $context['materials'] );
    $map_data = WS_Map_Objects::build_map_objects( $all_tools );
    if ( count( $map_data ) > 0 ) {
        $context[ 'map_options' ] = array(
            'data' => $map_data
        );
    }
}

// Locations page
if ($post_name === 'locations') {
    $context['locations'] = Timber::get_posts('post_type=locations');
    $map_data = WS_Map_Objects::build_map_objects( $context['locations'] );
    if ( count( $map_data ) > 0 ) {
        $context[ 'map_options' ] = array(
            'data' => $map_data
        );
    }
}

// Badges page
if ($post_name === 'badges') {
    $pathways_badges_args = array(
        'post_type' => 'badges',
        'tax_query' => array(
            array(
                'taxonomy' => 'badge-types',
                'field' => 'slug',
                'terms' => 'pathways-badges',
            ),
        ),
    );
    $context['pathways_badges'] = Timber::get_posts($pathways_badges_args);

    $machine_badges_args = array(
        'post_type' => 'badges',
        'tax_query' => array(
            array(
                'taxonomy' => 'badge-types',
                'field' => 'slug',
                'terms' => 'machine-badges',
            ),
        ),
    );
    $context['machine_badges'] = Timber::get_posts($machine_badges_args);
}

// News page
if ($post_name === 'news') {
    $context['news'] = Timber::get_posts('post_type=news');
}

// About page
if ($post_name === 'about') {
    $context['faq_page'] = Timber::get_post('post_type=page&p=27');
}

// Contact page
if ($post_name === 'contact') {
    $context['locations'] = Timber::get_posts('post_type=locations');
}

Timber::render($templates, $context);
