<?php
/**
 * Created by PhpStorm.
 * User: adamnicholls
 * Date: 28/10/2014
 * Time: 11:19
 */

class clsSiteVideoImages
{
    /**
     * Fetches the VideoToImage record by VideoID
     *
     * hint: the results of this method contains "ImageID"
     *
     * @param int $intID The ID of the record to fetch from siteVideoToImage
     * @return boolean|array
     * @uses clsDatabase::funCoreFetchSingle()
     */
    function funFetchVideoToImage($intID)
    {
        global $arrVar;

        return $arrVar['objDb']->funCoreFetchSingle('siteVideoToImage',$intID, 'VideoID');
    }
} 