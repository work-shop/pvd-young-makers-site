<?php

$templates = array('index.twig');
if (is_home()) {
    array_unshift($templates, 'home.twig');
}

$context = Timber::get_context();
$context['posts'] = new Timber\PostQuery();

Timber::render($templates, $context);
