<?php

$post = Timber::query_post();

$templates = array('single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig');

$context = Timber::get_context();
$context['post'] = $post;

Timber::render($templates, $context);
