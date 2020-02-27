<?php

    function routes_news(){

        $routes[] = array(
                            '_uri'  => '/^news\/add.html$/i',
                            'do'    => 'addarticle'
                         );

        $routes[] = array(
                            '_uri'  => '/^news\/edit([0-9]+).html$/i',
                            'do'    => 'editarticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^news\/delete([0-9]+).html$/i',
                            'do'    => 'deletearticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^news\/publish([0-9]+).html$/i',
                            'do'    => 'publisharticle',
                            1       => 'id'
                         );

        $routes[] = array(
                            '_uri'  => '/^news\/my.html$/i',
                            'do'    => 'my'
                         );

        $routes[] = array(
                            '_uri'  => '/^news\/my([0-9]+).html$/i',
                            'do'    => 'my',
                            1       => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^news\/(.+).html$/i',
                            'do'        => 'read',
                            1           => 'seolink'
                         );

        $routes[] = array(
                            '_uri'      => '/^news\/page\-([0-9]+)$/i',
                            'do'        => 'view',
                            1           => 'page'
                         );

        $routes[] = array(
                            '_uri'      => '/^news$/i',
                            'do'        => 'view'
                         );

        return $routes;

    }

?>
