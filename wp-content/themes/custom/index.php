<?php

$templates = array('index.twig');

$context = Timber::get_context();
$context['posts'] = new Timber\PostQuery();

Timber::render($templates, $context);
