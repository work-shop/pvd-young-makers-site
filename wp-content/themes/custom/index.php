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
    $map_data = WS_Map_Objects::build_map_objects($locations_for_map);
    if (count($map_data) > 0) {
        $context['map_options'] = array(
            'data' => $map_data,
        );
    }
}

// Making and Learning page
if ($post_name === 'making-and-learning') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=8');

    $locations_for_map = Timber::get_posts('post_type=locations');
    $map_data = WS_Map_Objects::build_map_objects($locations_for_map);
    if (count($map_data) > 0) {
        $context['map_options'] = array(
            'data' => $map_data,
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
    $machines = Timber::get_posts($machines_args);
    // Replace each machine's event IDs with events objects
    foreach($machines as $machine) {
        if ($machine->events) {
            $eventIds = $machine->events;
            $events = [];
            foreach($eventIds as $eventId) {
                $event = Timber::get_post($eventId);
                array_push($events, $event);
            }
            $machine->events = $events;
        }
    }
    $context['machines'] = $machines;

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
    $materials = Timber::get_posts($materials_args);
    // Replace each material's event IDs with events objects
    foreach($materials as $material) {
        if ($material->events) {
            $eventIds = $material->events;
            $events = [];
            foreach($eventIds as $eventId) {
                $event = Timber::get_post($eventId);
                array_push($events, $event);
            }
            $material->events = $events;
        }
    }
    $context['materials'] = $materials;

    $all_tools = array_merge($context['machines'], $context['materials']);

    $map_data = WS_Map_Objects::build_map_objects($all_tools);
    if (count($map_data) > 0) {
        $context['map_options'] = array(
            'data' => $map_data,
        );
    }
}

// Locations page
if ($post_name === 'locations') {
    $locations = Timber::get_posts('post_type=locations');
    // Replace each location's event IDs with events objects
    foreach($locations as $location) {
        if ($location->events) {
            $eventIds = $location->events;
            $events = [];
            foreach($eventIds as $eventId) {
                $event = Timber::get_post($eventId);
                array_push($events, $event);
            }
            $location->events = $events;
        }
    }
    $context['locations'] = $locations;

    $map_data = WS_Map_Objects::build_map_objects($context['locations']);
    if (count($map_data) > 0) {
        $context['map_options'] = array(
            'data' => $map_data,
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

// Events page
if ($post_name === 'events') {
    $date = date('m Y');
    if ($_GET['month'] && $_GET['year']) {
        $date = htmlspecialchars($_GET['month'] . ' ' . $_GET['year']);
    }
    $dateObj = DateTime::createFromFormat('!m Y', $date);
    $dateStr = $dateObj->format('F Y');
    $context['selected_date_string'] = $dateStr;

    $context['event_types'] = Timber::get_terms('event-types');
    $context['locations'] = Timber::get_posts('post_type=locations');
    $context['tools'] = Timber::get_posts('post_type=tools');
}

Timber::render($templates, $context);
