<?php

if (!class_exists('Timber')) {
    add_action('admin_notices', function () {
        echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url(admin_url('plugins.php#timber')) . '">' . esc_url(admin_url('plugins.php')) . '</a></p></div>';
    });

    add_filter('template_include', function ($template) {
        return get_stylesheet_directory() . '/static/no-timber.html';
    });

    return;
}

Timber::$dirname = array('templates', 'views');

class WS_Site extends TimberSite
{
    public function __construct()
    {
        add_theme_support('post-formats');
        add_theme_support('post-thumbnails');
        add_theme_support('menus');
        add_theme_support('html5', array('comment-list', 'comment-form', 'search-form', 'gallery', 'caption'));

        add_filter('timber_context', array($this, 'add_to_context'));
        add_filter('get_twig', array($this, 'add_to_twig'));
        add_filter('show_admin_bar', '__return_false');

        add_action('init', array($this, 'register_post_types_and_taxonomies'));
        add_action('init', array($this, 'register_image_sizing'));
        add_action('init', array($this, 'register_theme_support'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_and_styles'));

        new WS_CDN_Url();

        parent::__construct();
    }

    public function register_post_types_and_taxonomies()
    {
        PVDYM_Tool::register();
        PVDYM_Location::register();
        PVDYM_Badge::register();
        PVDYM_News::register();
        PVDYM_Event::register();
        PVDYM_Person::register();

        PVDYM_Badge_Type::register();
        PVDYM_News_Category::register();
        PVDYM_Tool_Type::register();
        PVDYM_Event_Type::register();
    }

    public function register_image_sizing()
    {
        if (function_exists('add_image_size')) {
            add_image_size('social_card', 1200, 630, array('x_crop_position' => 'center', 'y_crop_position' => 'center'));
            add_image_size('acf_preview', 300, 300, false);
            add_image_size('page_hero', 1440, 660, false);
        }
    }

    public function register_theme_support()
    {
        if (function_exists('add_theme_support')) {
            add_theme_support('menus');
        }
    }

    public function enqueue_scripts_and_styles()
    {
        if (function_exists('get_template_directory_uri') && function_exists('wp_enqueue_style') && function_exists('wp_enqueue_script')) {
            wp_register_style('google-fonts', 'https://fonts.googleapis.com/css?family=Montserrat:500,700');

            $main_css = '/bundles/bundle.css';
            $main_js = '/bundles/bundle.js';

            $compiled_resources_dir = get_template_directory();
            $compiled_resources_uri = get_template_directory_uri();

            $main_css_ver = filemtime($compiled_resources_dir . $main_css); // version suffixes for cache-busting.
            $main_js_ver = filemtime($compiled_resources_dir . $main_css); // version suffixes for cache-busting.

            wp_enqueue_style('google-fonts');
            wp_enqueue_style('main', $compiled_resources_uri . $main_css);
            wp_enqueue_script('jquery');
            wp_enqueue_script('main', $compiled_resources_uri . $main_js, array('jquery'), $main_js_ver, true);
        }
    }

    public function add_to_context($context)
    {
        $context['nav'] = new TimberMenu('Primary Navigation');
        $context['footer_nav'] = new TimberMenu('Footer Navigation');
        $context['options'] = get_fields('option');
        $context['partners'] = get_field('partners', 9);
        $context['site'] = $this;

        return $context;
    }

    public function add_to_twig($twig)
    {
        $twig->addExtension(new Twig_Extension_StringLoader());

        return $twig;
    }
}
