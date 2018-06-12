<?php

$post = new TimberPost();

$templates = array('page-' . $post->post_name . '.twig', 'page.twig');
if (is_front_page()) {
    array_unshift($templates, 'home.twig');
}

$context = Timber::get_context();
$context['post'] = $post;
$context['news'] = Timber::get_posts('post_type=news&numberposts=2');

Timber::render($templates, $context);
