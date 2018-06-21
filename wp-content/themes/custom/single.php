<?php

$post = new TimberPost();
$post_type = $post->post_type;

$templates = array('single-' . $post_type . '.twig', 'single.twig');

$context = Timber::get_context();
$context['post'] = $post;

if ($post_type === 'locations') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=4');
}

if ($post_type === 'events') {
    $tools_args = array(
        'post_type' => 'tools',
        'meta_query' => array(
            array(
                'key'     => 'events',
                'value'   => '"' . $post->id . '"',
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
}

Timber::render($templates, $context);
