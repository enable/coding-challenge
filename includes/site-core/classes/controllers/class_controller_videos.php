<?php
/**
 * Created by PhpStorm.
 * User: adamnicholls
 * Date: 27/10/2014
 * Time: 16:48
 */

/**
 * Class clsSiteController_videos
 *
 * All Controller Logic Relating to Videos needs
 * to live in this class.
 */
class clsSiteController_videos extends clsController
{

    /**
     * Fetches a list of Videos
     *
     * @return string
     */
    function funPageProcessing_videos_index()
    {
        global $arrVar;

        $arrTable = array(
            "siteVideo",'siteVideo_lang',
            array('left'=>
                array('siteVideo_lang' => array(
                    array('siteVideo_lang' => 'VideoID', 'siteVideo' => 'VideoID')))
            )
        );

        $arrFields = array("Title","Speaker","PublicationDate");
        $arrWhere = array();
        $arrOrder = array('Speaker'=>'DESC');

        $arrVideos = $arrVar['objDb']->funCoreFetch("fetchVideos",$arrTable,$arrFields,$arrWhere,$arrOrder);

        ob_start();
        require($this->funSelectDisplay('videos/index.php'));
        $txtDisplay = ob_get_contents();
        ob_end_clean();

        return $txtDisplay;
    }

    /**
     * @TODO: Add your Video Gallery function here...
     */
} 