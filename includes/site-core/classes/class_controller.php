<?php

require_once $arrVar['txtCoreFileBaseClasses'] . 'class_controller.php';

class clsSiteController extends clsController
{
    /**
     * This super-seeds the funPageProcessing() method in clsController.
     *
     * This method calls the parent, and if a PageProcessing method isn't found
     * we'll attempt to load it in from an external class.
     *
     * @author Adam Nicholls <adam@goramandvincent.com>
     * @since 2014/01/17
     * @access public
     * @return bool
     */
    function funPageProcessing(){
        global $arrVar;

        $blnProcessed = false;

        $strControllerParts = explode('_',$this->txtCodeRef);
        if(isset($strControllerParts[0]) && !empty($strControllerParts[0])){

            $strControllerClass = 'clsSiteController_'.ucfirst(strtolower($strControllerParts[0]));
            $strControllerFile = $arrVar['txtSiteCoreFileBaseControllers'].'class_controller_'.strtolower($strControllerParts[0]).'.php';
            if(!file_exists($strControllerFile)){
                $strControllerFile = $arrVar['txtSiteCoreFileBaseClasses'].'class_controller_'.strtolower($strControllerParts[0]).'.php';
            }

            if(file_exists($strControllerFile)){
                require_once($strControllerFile);
                if(class_exists($strControllerClass)){
                    $objController = new $strControllerClass();
                    $strControllerAction = 'funPageProcessing_'.$this->txtCodeRef;
                    if(method_exists($objController,$strControllerAction)){
                        //transfer some of the scope to the child.
                        foreach($this as $key=>$value){
                            $objController->{$key} = $value;
                        }
                        $txtDisplay = $objController->$strControllerAction();
                        $blnProcessed = true;
                    }
                }
            }
        }

        if($blnProcessed){
            // Now try and call any page specific functionality
            if (method_exists($this,'funPageProcessing_' . $this->txtCodeRef))
            {
                $arrVar['objErrorHandler']->funDebug('funPageProcessing_' . $this->txtCodeRef);
                eval('$txtDisplay = $this->funPageProcessing_' . $this->txtCodeRef . '();');
            }
        }

        if ($arrVar['objErrorHandler']->blnDebugTiming)
        {
            $arrVar['objErrorHandler']->funDebug('Page Processing - Page');
            $arrVar['objErrorHandler']->funDebug($arrVar['objDataManipulation']->funTimer());
        }

        // Now try and call the common page processing for actions after any page specific functions
        if (method_exists($this,'funPageProcessing_internal_common_post'))
            eval('$this->funPageProcessing_internal_common_post();');

        if ($arrVar['objErrorHandler']->blnDebugTiming)
        {
            $arrVar['objErrorHandler']->funDebug('Page Processing - Common Post');
            $arrVar['objErrorHandler']->funDebug($arrVar['objDataManipulation']->funTimer());
            //$arrVar['objDataManipulation']->dblTimer = $dblTimer;
        }

        return isset($txtDisplay) ? $txtDisplay : '';
    }

}