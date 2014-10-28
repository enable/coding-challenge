<?php
/**
 * This is the routes file.
 *
 * The system uses the Regex key to match a route. It iterates over each
 * entry in the $arrRoutes variable and stops on the first match. The order
 * of the routes is therefore important.
 *
 * Regex reminder:
 * - all regex's must have a start and stop delimiter - usually a slash //
 * - anything that needs escaping should have a backslash - \/ is equal to /
 *
 * CodeRefs & Templates
 *
 * CodeRefs point the front controller in the direction of the controller and it's
 * class and action.
 *
 * Templates are used to check against the display folders.
 *
 */

$arrRoutes = array();

$arrRoutes['videos'] = array(
    'Regex' => '/videos/',
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