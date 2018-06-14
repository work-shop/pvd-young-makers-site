<?php

$post = new TimberPost();

$templates = array('page-' . $post->post_name . '.twig', 'page.twig');
if (is_front_page()) {
    array_unshift($templates, 'home.twig');
}

$context = Timber::get_context();
$context['post'] = $post;
if (is_front_page()) {
    $events = Timber::get_posts('post_type=events&numberposts=3');
    foreach ($events as $event) {
        $event->location_name = Timber::get_post('post_type=locations&p=' . $event->location[0])->post_title;
    }
    $context['events'] = $events;
}

Timber::render($templates, $context);
