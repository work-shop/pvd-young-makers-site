<?php

class PVDYM_News_Category extends WS_Taxonomy {

    public static $slug = 'news-categories';

    public static $singular_name = 'News Category';

    public static $plural_name = 'News Categories';

    public static $registered_post_types = array( 'news' );

    public static $public = true;

}

?>
