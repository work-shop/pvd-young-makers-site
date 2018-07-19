<?php

$post = new TimberPost();
$post_type = $post->post_type;

$templates = array('single-' . $post_type . '.twig', 'single.twig');

$context = Timber::get_context();
$context['post'] = $post;

// Single location
if ($post_type === 'locations') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=4');

    $map_data = WS_Map_Objects::build_single_location($post);
    if ($map_data != null) {
        $context['map_options'] = array(
            'data' => $map_data,
        );
    }
}

// Single event
if ($post_type === 'events') {
    $tools_args = array(
        'post_type' => 'tools',
        'meta_query' => array(
            array(
                'key' => 'events',
                'value' => '"' . $post->id . '"',
                'compare' => 'LIKE',
            ),
        ),
    );
    $tools = Timber::get_posts($tools_args);
    $context['tools'] = $tools;

    // Get array of badges from tools that will be at the event
    $array_of_arrays_of_badge_ids = [];
    foreach ($tools as $tool) {
        array_push($array_of_arrays_of_badge_ids, $tool->badge);
    }
    $array_of_badge_ids = array_column($array_of_arrays_of_badge_ids, 0);
    array_unique($array_of_badge_ids);
    $badges_args = array(
        'post_type' => 'badges',
        'post__in' => $array_of_badge_ids
    );
    $context['badges'] = Timber::get_posts($badges_args);

    $map_data = WS_Map_Objects::build_single_event($post);
    if ($map_data != null) {
        $context['map_options'] = array(
            'data' => $map_data,
        );
    }
}

// Single tool
if ($post_type === 'tools') {
    $map_data = WS_Map_Objects::build_map_objects(array($post));
    if ($map_data != null) {
        $context['map_options'] = array(
            'data' => $map_data,
        );
    }
}


Timber::render($templates, $context);
