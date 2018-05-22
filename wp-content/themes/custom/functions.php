<?php

    /** Theme-specific global constants for NAM */
    define( '__ROOT__', dirname( __FILE__ ) );

    require_once( __ROOT__ . '/functions/class-ws-abstract-taxonomy.php' );
    require_once( __ROOT__ . '/functions/class-ws-abstract-custom-post-type.php' );

    require_once( __ROOT__ . '/functions/library/class-ws-cdn-url.php');



    require_once( __ROOT__ . '/functions/post-types/tool/class-pvdym-tool.php');
    require_once( __ROOT__ . '/functions/post-types/location/class-pvdym-location.php');
    require_once( __ROOT__ . '/functions/post-types/badge/class-pvdym-badge.php');
    require_once( __ROOT__ . '/functions/post-types/news/class-pvdym-news.php');
    // The events post type is managed by events calendar.

    require_once( __ROOT__ . '/functions/taxonomies/badge-type/class-pvdym-badge-type.php');
    require_once( __ROOT__ . '/functions/taxonomies/news-category/class-pvdym-news-category.php');
    require_once( __ROOT__ . '/functions/taxonomies/tool-type/class-pvdym-tool-type.php');

    require_once( __ROOT__ . '/functions/class-ws-site-admin.php' );
    require_once( __ROOT__ . '/functions/class-ws-site-init.php' );


    new WS_Site();
    new WS_Site_Admin();


?>
