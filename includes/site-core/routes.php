<?php

$arrRoutes = array();


$arrRoutes['videos'] = array(
    'Regex' => '/videos/', //<--- this is regular expression (don't forget the delimiters)
    'Page' => 'index',
    'NoCMS' => false,
    'CodeRef' => 'videos_index',
    'LoginRequired' => false,
    'Template' => 'videos/index.php',
    'Eval' => array()
);

$arrRoutes['index'] = array(
    'Regex' => '/index.php/',
    'Page' => 'index',
    'NoCMS' => false,
    'CodeRef' => 'index',
    'LoginRequired' => false,
    'Template' => 'index.php',
    'Eval' => array()
);