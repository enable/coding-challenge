<?php

/**
 * Front Controller Class
 *
 * @version 2.0
 * @package core
 */
class clsController
{
    function funStartController($strEnvironment)
    {
        global $arrVar;

        $arrVar['ini']['Config'] = parse_ini_file($arrVar['txtSiteIniFileBase'] . 'config.ini',true);
        $arrVar['ini']['Module'] = parse_ini_file($arrVar['txtSiteIniFileBase'] . 'modules.ini',true);
        $arrVar['ini']['Debug'] = parse_ini_file($arrVar['txtSiteIniFileBase'] . 'debug.ini', true);
        $arrVar['ini']['Database'] = parse_ini_file($arrVar['txtSiteIniFileBase'] . 'database.ini',true);
        $arrVar['ini']['Database'] = $arrVar['ini']['Database'][$strEnvironment];

        ob_start();
        require $this->funSelectDisplay('templates/parts/top.php');
        $strTop = ob_get_contents();
        ob_end_clean();
        $arrVar['txtDisplay'] = $strTop;

        require_once $arrVar['txtSiteCoreFileBase'] . 'routes.php';

        $this->txtTemplateDir = '';//'templates' . DIRECTORY_SEPARATOR;
        $this->arrFetchBaseName['includes_dir_core_dir_virtual'] = $arrRoutes;

        $this->funLoadObjects("core");
        $this->funFetchBaseName();
        $arrVar['txtDisplay'] .= $this->funPageProcessing();

        ob_start();
        require $this->funSelectDisplay('templates/parts/bottom.php');
        $strTop = ob_get_contents();
        ob_end_clean();

        return $arrVar['txtDisplay'];
    }

    /**
     * Loads all of the classes for the specified module
     *
     * @param string $txtModue The module to load
     * @return boolean
     * @uses $arrVar
     * @uses funSelectClass()
     * @uses $arrModulesLoaded
     */
    function funLoadObjects($txtModule)
    {
        global $arrVar;

        // Fetch the list of class filenames from module.php ini for the module
        $arrTemp = explode(',',$arrVar['ini']['Module']['modules'][$txtModule]);

        // Now lets loop through those classes
        foreach ($arrTemp as $txtClass)
        {
            // First lets remove any whitespace
            $txtClass = trim($txtClass);
            // If it is empty ignore
            if (empty($txtClass)) continue;
            // Now lets convert any slashes to _dir_ so that we can look it up
            $txtClass = str_replace('/','_dir_',$txtClass);
            // Now lets fetch the class name and the name of the object
            $arrClass = explode(',',$arrVar['ini']['Module']['classes'][$txtClass]);
            // If we haven't been given an object name then we use the same name as the class
            if (!empty($arrClass[0]) && (!isset($arrClass[0]) || empty($arrClass[1])))
                $arrClass[1] = $arrClass[0];
            // Now we convert the _dir_ in the filename to the correct directory separator
            $txtClass = str_replace(array('_dir_','_dot_'),array(DIRECTORY_SEPARATOR,'.'),$txtClass);

            // If we have a class name then we load and instantiate the object
            // Otherwise we just load the file
            if (isset($arrClass[1]) && !empty($arrClass[1]))
            {
                // Load the class
                $txtInclude = $this->funSelectClass('class_' . $txtClass . '.php');
                // If we have a site class then try and load the original first (if one exists)
                if (strpos($txtInclude,DIRECTORY_SEPARATOR . 'site-core' . DIRECTORY_SEPARATOR) !== false)
                {
                    $txtOrigInclude = str_replace(DIRECTORY_SEPARATOR . 'site-core' . DIRECTORY_SEPARATOR,DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR,$txtInclude);
                    include_once($txtOrigInclude);
                }
                include_once($txtInclude);

                switch ($txtClass)
                {
                    // The session class is a special case, so we instantiate this with its required parameers
                    case 'session':
                        eval('$arrVar[\'obj' . $arrClass[1] . '\'] = new cls' . (strpos($txtInclude,DIRECTORY_SEPARATOR . 'site-core' . DIRECTORY_SEPARATOR) !== false ? 'Site' : '') . $arrClass[0] . '(\'' . $this->txtSessionName . '\',(3600*' . $this->intSessionLength . '),true,false,$_GET[\'' . $this->txtSessionName . '\']);');
                        break;
                    default:
                        //using reflection is probably
                        $strClassName = 'cls' . (strpos($txtInclude,DIRECTORY_SEPARATOR . 'site-core' . DIRECTORY_SEPARATOR) !== false ? 'Site' : '') . $arrClass[0];
                        $strObjectName = 'obj' . $arrClass[1] . '';
                        $arrVar[$strObjectName] = new $strClassName();
                        break;
                }
            }
            else
            {
                include_once($this->funSelectClass($txtClass . '.php'));
            }
        }

        $this->arrModulesLoaded[$txtModule] = true;

        return true;
    }


    /**
     * A simple way of finding the correct class to use
     *
     * Normally a function like this would be kept in the Data Manipulation class, but it needs to be
     * used before that can be loaded.
     *
     * @param string $txtClass The filename of the class
     * @return string The full path to the class
     */
    function funSelectClass($txtClass)
    {
        global $arrVar;

        if (file_exists($arrVar['txtSiteCoreFileBaseClasses'] . $txtClass))
            return $arrVar['txtSiteCoreFileBaseClasses'] . $txtClass;
        else
            if (file_exists($arrVar['txtCoreFileBaseClasses'] . $txtClass))
                return $arrVar['txtCoreFileBaseClasses'] . $txtClass;

        /**
         * Added in by AJN 24/04/2014 - the funLoadObjects() method
         * will require in classes without instantiating them, but
         * oddly it doesn't pass through the class_ prefix for the
         * file name. I've added it here a fall back.
         */
        if(substr($txtClass,0,5) !== 'class')
            $txtClass = 'class_'.$txtClass;

        if (file_exists($arrVar['txtSiteCoreFileBaseClasses'] . $txtClass))
            return $arrVar['txtSiteCoreFileBaseClasses'] . $txtClass;
        else
            if (file_exists($arrVar['txtCoreFileBaseClasses'] . $txtClass))
                return $arrVar['txtCoreFileBaseClasses'] . $txtClass;

    }

    /**
     * A simple way of finding the correct display to use
     *
     * Normally a function like this would be kept in the Data Manipulation class, but it needs to be
     * used before that can be loaded.
     *
     * @param string $txtDisplay The filename of the display
     * @return string The full path to the display
     * @return string The full path to the display
     */
    function funSelectDisplay($txtDisplay)
    {
        global $arrVar;

        // Determine language specific filename of the display

        $txtSubFolder = substr($txtDisplay,0,stripos($txtDisplay,'-'));

        $arrPaths = array(
            //look for includes/site-core/display/video/video-index.php
            'DisplaySiteCoreSub' =>             $arrVar['txtSiteCoreFileBaseDisplay'].$txtSubFolder.DIRECTORY_SEPARATOR.$txtDisplay,
            //look for includes/site-core/display/video-index.php
            'DisplaySiteCore' =>                $arrVar['txtSiteCoreFileBaseDisplay'].$txtDisplay,
            //look for includes/core/display/video/video-index.php
            'DisplayCoreSub' =>                 $arrVar['txtCoreFileBaseDisplay'].$txtSubFolder.DIRECTORY_SEPARATOR.$txtDisplay,
            //look for includes/core/display/video-index.php
            'DisplayCore' =>                    $arrVar['txtCoreFileBaseDisplay'].$txtDisplay,
        );

        foreach($arrPaths as $strPath){
            if(file_exists($strPath))
                return $strPath;
            if(substr($strPath,-4) != '.php')
                $strPath .= '.php';
            if(file_exists($strPath))
                return $strPath;
        }
    }

    /**
     * Calls any page specific functions, as well as the default page functions
     *
     * If you have common code for pages then you should setup one of the following functions:
     * - funPageProcessing_internal_common_pre - Called before page specific functions
     * - funPageProcessing_internal_common_post - Called after page specific functions
     *
     * @return boolean
     * @uses $arrVar
     * @uses $txtCodeRef
     */
    function funPageProcessing()
    {
        global $arrVar;

        // Set up the display variable
        $arrVar['txtDisplay'] = false;

        // Now try and call the common page processing for actions before any page specific functions
        if (method_exists($this,'funPageProcessing_internal_common_pre'))
            eval('$this->funPageProcessing_internal_common_pre();');

        if ($arrVar['objErrorHandler']->blnDebugTiming)
        {
            $dblTimer = $arrVar['objDataManipulation']->dblTimer;
            $arrVar['objErrorHandler']->funDebug('Page Processing - Common Pre');
            $arrVar['objErrorHandler']->funDebug($arrVar['objDataManipulation']->funTimer());
        }

        if (method_exists($this,'funPageProcessing_' . $this->txtCodeRef))
        {
            $arrVar['objErrorHandler']->funDebug('funPageProcessing_' . $this->txtCodeRef);
            eval('$txtDisplay = $this->funPageProcessing_' . $this->txtCodeRef . '();');
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
            $arrVar['objDataManipulation']->dblTimer = $dblTimer;
        }

        return isset($txtDisplay) ? $txtDisplay : '';

    }

    /**
     * Works out the name of the file called, and handles virtual pages
     *
     * @return boolean
     * @uses $txtPage
     * @uses $arrFetchBaseName
     * @uses $txtVirtualPage
     * @uses $blnNoCMS
     * @uses $txtCodeRef
     * @uses clsContentManagement::funFindPage()
     * @uses clsContentManagement::funFetchPage()
     * @uses clsErrorHandler::funDebug()
     * @uses clsErrorHandler::funSetCleanExit()
     * @uses funSelectDisplay()
     */
    function funFetchBaseName()
    {
        global $arrVar;

        // First lets fetch the basename of the page
        $this->txtPage = basename($_SERVER['PHP_SELF'],'.php');

        // Now we find out what directory we are in - some servers directory paths
        // do not match up for some reason, so we have to allow for this
        $txtDir = dirname($_SERVER['SCRIPT_FILENAME']) . DIRECTORY_SEPARATOR;
        $intTemp = strpos($txtDir,$arrVar['txtFileBase']);
        $txtTemp = '';
        if ($intTemp > 0)
            $txtTemp = substr($txtDir,0,$intTemp);

        // Now we can check to see if we are in a directory / sub directory
        $arrTemp = explode(DIRECTORY_SEPARATOR,str_replace($txtTemp,'',$txtDir));
        array_pop($arrTemp); // get rid of the trailing slash
        $arrTemp2 = explode(DIRECTORY_SEPARATOR,$arrVar['txtFileBase']);
        array_pop($arrTemp2); // get rid of the trailing slash

        // Now we loop through the file directories, adding them to our folder path until
        // the number of directories matches the number of directories in txtFileBase
        $txtTemp = '';
        while(count($arrTemp) > count($arrTemp2))
            $txtTemp = array_pop($arrTemp) . '_dir_' . $txtTemp;

        // Now we update the page with the directories (if any)
        $this->txtPage = $txtTemp . $this->txtPage;

        // Now we check to see if we are a special file - in which case
        // extra processing will need to take place
        //

        if (is_array($this->arrFetchBaseName[$this->txtPage]))
        {

            $this->txtVirtualPage = $this->txtPage;

            $this->blnNoCMS = false;

            // Now we need to find the page name from the REQUEST_URI
            // So first we get rid of the query string
            list($txtURL,$txtQueryString) = explode('?',$_SERVER['REQUEST_URI']);
            // Now we decode the URL - mainly if there are any spaces
            $txtURL = urldecode($txtURL);
            // Now we need to remove the BaseURL from the start
            if (substr($txtURL,0,strlen($arrVar['txtBaseURL'])) == $arrVar['txtBaseURL'])
                $txtURL = substr($txtURL,strlen($arrVar['txtBaseURL']));
            // Now we explode the URL so that it is in it's parts
            $arrURL = explode('/',$txtURL);
            // Then we pop of the last item and get the basename of the file
            $txtURL = basename(array_pop($arrURL),'.php');
            // If there is no filename then we set it to index
            if ($txtURL == '')
            {
                $txtURL = 'index';
                $_SERVER['REQUEST_URI'] = $arrVar['txtBaseURL'] . (count($arrURL) > 0 ? implode('/',$arrURL) . '/' : '') . 'index.php' . (empty($txtQueryString) ? '' : '?' . $txtQueryString);
            }
            // Finally we combine the directories (if any) with the filename
            $this->txtPage = implode('_dir_',$arrURL) . (count($arrURL) > 0 ? '_dir_' : '') . $txtURL;

            // Now we try and look for a match
            $blnMatchFound = false;
            list($txtTURL) = explode('?',$_SERVER['REQUEST_URI']);
            foreach ($this->arrFetchBaseName[$this->txtVirtualPage] as $txtPage => $arrRow)
            {
                $arrRow['Regex'] = '/' . preg_quote($arrVar['txtBaseURL'],'/') . substr($arrRow['Regex'],1);
                if (preg_match($arrRow['Regex'],$txtTURL,$arrMatches))
                {
                    $this->txtPage = (empty($arrRow['Page']) ? $txtPage : $arrRow['Page']);
                    $this->txtCodeRef = $arrRow['CodeRef'];
                    if(isset($arrRow['Eval']))
                        foreach ($arrRow['Eval'] as $txtEval)
                            if(!empty($txtEval))
                                eval($txtEval);
                    $this->blnNoCMS = (!isset($arrRow['NoCMS']) || empty($arrRow['NoCMS']) ? false : true);
                    $txtTemplate = $arrRow['Template'];
                    $blnMatchFound = true;
                    $arrVar['arrPage'] = $arrRow;

                    break;
                }
            }

            if (empty($txtTemplate) || !file_exists($this->funSelectDisplay($this->txtTemplateDir . $txtTemplate)))
            {
                header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
                print '<h1>404 - Page Not Found</h1>';
                $arrVar['objErrorHandler']->funDebug('404 - template not found');
                $arrVar['objErrorHandler']->funDebug($this->txtTemplateDir . $txtTemplate);
                $arrVar['objErrorHandler']->funDebug($this->txtPage);
                $arrVar['objErrorHandler']->funDebug($arrVar['arrPage']);
                $arrVar['objErrorHandler']->funSetCleanExit();
                exit();
            }

            $this->blnVirtualPage = true;
            $arrVar['txtVirtualTemplate'] = $this->funSelectDisplay($this->txtTemplateDir . $txtTemplate);
            $arrVar['objErrorHandler']->funDebug($this->txtPage);
            $arrVar['objErrorHandler']->funDebug($this->txtTemplateDir . $txtTemplate);
        }

        $arrVar['txtPage'] = $this->txtPage;

        return true;
    }


}