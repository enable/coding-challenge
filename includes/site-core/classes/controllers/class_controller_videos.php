<?php
/**
 * Created by PhpStorm.
 * User: adamnicholls
 * Date: 27/10/2014
 * Time: 16:48
 */

class clsSiteController_videos extends clsController
{

    function funPageProcessing_videos_index()
    {
        global $arrVar;

        $arrVideos = $arrVar['objDb']->funCoreFetch("fetchVideos","siteVideo");

        ob_start();
        require($this->funSelectDisplay('videos/index.php'));
        $txtDisplay = ob_get_contents();
        ob_end_clean();

        return $txtDisplay;
    }

} 