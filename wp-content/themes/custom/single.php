<?php

$post = new TimberPost();
$post_type = $post->post_type;

$templates = array('single-' . $post_type . '.twig', 'single.twig');

$context = Timber::get_context();
$context['post'] = $post;

if ($post_type === 'locations') {
    $context['tools'] = Timber::get_posts('post_type=tools&numberposts=4');
}

Timber::render($templates, $context);
