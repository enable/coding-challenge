<?php

define('strDirSep', DIRECTORY_SEPARATOR);
define("strEnvironment", "development");

$arrVar = array();
$arrVar['txtFileBase'] = realpath(__DIR__ . strDirSep . '..' . strDirSep . '..') . strDirSep . '';
$arrVar['txtIncludesFileBase'] = $arrVar['txtFileBase'] . 'includes' . strDirSep . '';

$arrVar['txtCoreFileBase'] = $arrVar['txtIncludesFileBase'] . strDirSep . 'core' . strDirSep . '';
$arrVar['txtCoreFileBaseClasses'] = $arrVar['txtCoreFileBase'] . 'classes' . strDirSep . '';

$arrVar['txtSiteCoreFileBase'] = $arrVar['txtIncludesFileBase'] . strDirSep . 'site-core' . strDirSep . '';
$arrVar['txtSiteCoreFileBaseClasses'] = $arrVar['txtSiteCoreFileBase'] . 'classes' . strDirSep . '';
$arrVar['txtSiteCoreFileBaseControllers'] = $arrVar['txtSiteCoreFileBaseClasses'] . 'controllers' . strDirSep;

$arrVar['txtSiteFileBase'] = $arrVar['txtFileBase'] . 'site' . strDirSep . '';
$arrVar['txtSiteIniFileBase'] = $arrVar['txtSiteFileBase'] . 'ini' . strDirSep . '';

$arrVar['txtSiteFileBaseCache'] = $arrVar['txtSiteFileBase'] . 'cache' . strDirSep;

$arrVar['txtSiteCoreFileBaseDisplay'] = $arrVar['txtSiteCoreFileBase'] . 'display' . strDirSep . '';
$arrVar['txtCoreFileBaseDisplay'] = $arrVar['txtCoreFileBase'] . 'display' . strDirSep . '';

require_once $arrVar['txtFileBase'] . '/vendor/autoload.php';

/**
 * This variable stores the website root
 * @var string
 */
// Now we work out the Base URL
$arrTemp = explode(DIRECTORY_SEPARATOR,dirname($_SERVER['SCRIPT_FILENAME']));
$arrTemp2 = explode(DIRECTORY_SEPARATOR,$arrVar['txtFileBase']);
array_pop($arrTemp2);
$intPop = count($arrTemp) - count($arrTemp2) + 1;
$arrTemp = explode('/',$_SERVER['SCRIPT_NAME']);
for ($i=0;$i<$intPop;$i++)
    array_pop($arrTemp);
/**
 * This variable stores the website root
 * @var string
 */
$arrVar['txtBaseURL'] = implode('/',$arrTemp);
if (substr($arrVar['txtBaseURL'],-1) != '/')
    $arrVar['txtBaseURL'] .= '/';

$arrVar['txtBaseCSSURL'] = $arrVar['txtBaseURL'] . 'css' . strDirSep;
$arrVar['txtBaseImageURL'] = $arrVar['txtBaseURL'] . 'images' . strDirSep;
$arrVar['txtBaseURLSite'] = $arrVar['txtBaseURL'] . strDirSep . 'site' . strDirSep;
$arrVar['txtBaseURLCache'] = $arrVar['txtBaseURLSite'] . strDirSep . 'cache' . strDirSep;


require_once $arrVar['txtSiteCoreFileBaseClasses'] . 'class_controller.php';

$arrVar['objClsController']  = new clsSiteController();
echo $arrVar['objClsController']->funStartController(strEnvironment);

