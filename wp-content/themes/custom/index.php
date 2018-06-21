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
}

// Making and Learning page
if ($post_name === 'making-and-learning') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=8');
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
}

// Locations page
if ($post_name === 'locations') {
    $context['locations'] = Timber::get_posts('post_type=locations');
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

Timber::render($templates, $context);
