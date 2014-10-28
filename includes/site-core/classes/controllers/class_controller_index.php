<?php
/**
 * Created by PhpStorm.
 * User: adamnicholls
 * Date: 27/10/2014
 * Time: 16:48
 */

class clsSiteController_index extends clsController
{

    /**
     * Index Action in the Index Controller
     *
     * @return string
     */
    function funPageProcessing_index()
    {
        //ensure you pull in the $arrVar service into to
        //your own actions/controllers
        global $arrVar;

        ob_start();
        require($this->funSelectDisplay('index.php'));
        $txtDisplay = ob_get_contents();
        ob_end_clean();

        return $txtDisplay;
    }

} 