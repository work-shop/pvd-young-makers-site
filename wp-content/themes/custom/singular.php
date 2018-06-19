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
    $context['tools'] = Timber::get_posts('post_type=tools');
}

// Locations page
if ($post_name === 'locations') {
    $context['locations'] = Timber::get_posts('post_type=locations');
}

// Badges page
if ($post_name === 'badges') {
    $context['badges'] = Timber::get_posts('post_type=badges');
}

// News page
if ($post_name === 'news') {
    $context['news'] = Timber::get_posts('post_type=news');
}

Timber::render($templates, $context);
