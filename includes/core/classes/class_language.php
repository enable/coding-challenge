<?php
	/**
	* Langauage Class
	* 
	* Handles all of the language and translation functionality
	*
	* @version 2.0
	* @package core
	*/
	class clsLanguage
	{
		/**
		* Adds / Edits the coreTranslation record
		* 
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditTranslation($arrData,$intID=0)
		{
			global $arrVar;
			
			return $arrVar['objDb']->funCoreAddEdit('coreTranslation',$arrData,$intID);
		}
		
		/**
		* Fetches the Translation by ID
		* 
		* @param int $intID The ID of the record to fetch from coreTranslation
		* @param int $intLanguageID The LanguageID to filter by
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchTranslation($intID,$intLanguageID)
		{
			global $arrVar;
			
			return $arrVar['objDb']->funCoreFetchSingle('coreTranslation',$intID,'',$intLanguageID);
		}
		
		/**
		* Fetches the records from coreTranslation
		* 
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intLanguageID The LanguageID to filter by
		* @param boolean $blnLanguageExact Whether or not to just fetch where the LanguageID matches, or whether to also get the null rows
		* @param string $txtText The text to find
		* @param int $intLanguageID The LanguageID to filter by
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchTranslations($intMode=CORE_DB_FETCH,$intLanguageID=0,$blnLanguageExact=false,$txtText='',$intCaseSensitive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;
			
			$arrTable = array();
			$arrTable[] = 'coreTranslation';
			if (!empty($intLanguageID))
				$arrTable[][($blnLanguageExact ? 'inner' : 'left')] = array('coreTranslation_lang' => array(array('coreTranslation_lang' => 'TranslationID', 'coreTranslation' => 'TranslationID'),array('coreTranslation_lang' => 'LanguageID', '#VALUE#' => $intLanguageID)));
			else if (!empty($txtText))
				$arrTable[]['left'] = array('coreTranslation_lang' => array('coreTranslation_lang' => 'TranslationID', 'coreTranslation' => 'TranslationID'));
			
			$arrWhere = array();
			if (!empty($txtText))
				$arrWhere['coreTranslation_lang.Translation'] = array('=',$txtText);
			if (!empty($intCaseSensitive))
			{
				if ($intCaseSensitive < 0)
					$arrWhere['coreTranslation.CaseSensitive'] = array('=',0);
				else if ($intCaseSensitive > 0)
					$arrWhere['coreTranslation.CaseSensitive'] = array('<>',0);
			}
			
			$arrOrder = array();
			if (!empty($intLanguageID))
				$arrOrder['coreTranslation_lang.Translation'] = '';
			
			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);
			
			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
			{
				$arrField[] = 'coreTranslation.TranslationID';
			}
			else
			{
				if (!empty($intLanguageID))
					$arrField[] = 'coreTranslation_lang.*';
				$arrField[] = 'coreTranslation.*';
				if (!empty($intLanguageID))
				{
					$arrField[] = 'coreTranslation_lang.xDateAdded:xDateAdded_lang';
					$arrField[] = 'coreTranslation_lang.xLastUpdate:xLastUpdate_lang';
					$arrField[] = array('length' => array('coreTranslation_lang.Translation' => 'intLength'));
				}
			}
			
			$arrGroup = array();
			$arrGroup[] = 'coreTranslation.TranslationID';
			
			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}
		
		/**
		* Deletes a record from coreTranslation
		* 
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteTranslation($intID)
		{
			global $arrVar;
			
			return $arrVar['objDb']->funCoreDelete('coreTranslation',$intID);
		}
		
		/**
		* Adds / Edits the coreLanguage record
		* 
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditLanguage($arrData,$intID=0)
		{
			global $arrVar;
			
			return $arrVar['objDb']->funCoreAddEdit('coreLanguage',$arrData,$intID);
		}
		
		/**
		* Fetches the Language by ID
		* 
		* @param int $intID The ID of the record to fetch from coreLanguage
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchLanguage($intID)
		{
			global $arrVar;

            //get it from memory first
            if (isset($this->arrCache['languages'][$intID]))
                return $this->arrCache['languages'][$intID];

            $txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'database' . DIRECTORY_SEPARATOR .__FUNCTION__.'_'.$intID.'.cache';
            //lets then check the local file cache...
            if (!isset($this->arrCache['languages'][$intID])){
                if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile)){
                    $this->arrCache['languages'][$intID] = unserialize($txtCacheContent);
                    return $this->arrCache['languages'][$intID];
                }
            }

			$arrLanguage = $arrVar['objDb']->funCoreFetchSingle('coreLanguage',$intID);

            if($arrLanguage){
                $this->arrCache['languages'][$intID] = $arrLanguage;
                $arrVar['objDataManipulation']->funSaveFile(serialize($arrLanguage),$txtCacheFile);
            }

            return $arrLanguage;
		}
		
		/**
		* Fetches the records from coreLanguage
		* 
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intActive Whether or not to filter by the active flag (-1 = not active, 1 = active)
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchLanguages($intMode=CORE_DB_FETCH,$txtLanguageCode='',$intActive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;
			
			$arrTable = array();
			$arrTable[] = 'coreLanguage';
			
			$arrWhere = array();
			if (!empty($txtLanguageCode))
				$arrWhere['coreLanguage.LanguageCode'] = array('=',$txtLanguageCode);
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreLanguage.Active'] = array('=',0);
				else
					$arrWhere['coreLanguage.Active'] = array('<>',0);
			}
			
			$arrOrder = array();
			$arrOrder['coreLanguage.SortOrder'] = '';
			$arrOrder['coreLanguage.Language'] = '';
			
			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);
			
			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreLanguage.LanguageID';
			else
				$arrField[] = 'coreLanguage.*';
			
			$arrGroup = array();
			$arrGroup[] = 'coreLanguage.LanguageID';
			
			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}
		
		/**
		* Deletes a record from coreLanguage
		* 
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteLanguage($intID)
		{
			global $arrVar;
			
			return $arrVar['objDb']->funCoreDelete('coreLanguage',$intID);
		}
		
		function funTranslate($txtText,$intLanguageID)
		{
			global $arrVar;
			
			$arrSite = $arrVar['objSite']->funFetchSite($_SESSION['SiteID']);
			
			if ($intLanguageID == $arrSite['LanguageID'])
				return $txtText;

            if(!empty($intLanguageID)){
                $txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'translations' . DIRECTORY_SEPARATOR .$intLanguageID.
                                        DIRECTORY_SEPARATOR. 'site-'.$_SESSION['SiteID'].'.cache';
                //lets then check the local file cache...
                if (!isset($this->arrCache['translations'])){
                    if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile,0)){
                        $this->arrCache['translations'] = unserialize($txtCacheContent);
                    }
                }
            }

            //get it from memory first
            if (isset($this->arrCache['translations'][md5(strtolower($txtText))][$intLanguageID]))
                return $this->arrCache['translations'][md5(strtolower($txtText))][$intLanguageID];
			
			$arrTranslation = $this->funFetchTranslations(CORE_DB_FETCH,$arrSite['LanguageID'],true,$txtText);
			$intID = 0;
			foreach ($arrTranslation as $arrRow)
			{
				$arrVar['objErrorHandler']->funDebug($txtText);
				$arrVar['objErrorHandler']->funDebug($arrRow);
				if (($arrRow['Translation'] == $txtText && !empty($arrRow['CaseSensitive'])) || (strtolower($txtText) == strtolower($arrRow['Translation']) && empty($arrRow['CaseSensitive'])))
				{
					$intID = $arrRow['TranslationID'];
					break;
				}
			}
			
			$txtReturn = $txtText;
			if (!empty($intID))
			{
				$arrTranslation = $this->funFetchTranslation($intID,$intLanguageID);
				if (!empty($arrTranslation['Translation']))
					$txtReturn = $arrTranslation['Translation'];
			}
			
			$this->arrCache['translations'][md5(strtolower($txtText))][$intLanguageID] = $txtReturn;

            if(!empty($intLanguageID)){
                $txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'translations' . DIRECTORY_SEPARATOR .$intLanguageID.
                    DIRECTORY_SEPARATOR. 'site-'.$_SESSION['SiteID'].'.cache';
                $arrVar['objDataManipulation']->funSaveFile(serialize($this->arrCache['translations']),$txtCacheFile);
            }

			return $txtReturn;
		}
	}
?>