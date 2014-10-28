<?php
	require_once($arrVar['txtCoreFileBaseClasses'] . 'class_mysql_wrapper.php');

	/**
	* Database Class
	*
	* This class is simply designed to extend the functionaly of a database wrapper, so that (in theory) we could
	* simply write a new wrapper and extend that instead of the current MySQL one
	*
	* @version 2.0
	* @package core
	*/
	class clsDatabase extends clsMySQLWrapper
	{
		/**
		* An array of table prefixes used by the system that need to be removed
		* when generating the ID
		* @var array
		* @uses funRemovePrefix()
		*/
		var $arrTablePrefixes = array('core','site');
		/**
		* The name of the database connection in use
		* @var string
		*/
		var $txtDatabase = 'unknown';

        /**
         * Fetches the database credentials from ini file.
         * This can be reused by the clsDatabaseDbal() class
         *
         * @return array|boolean
         */
        function funGetDatabaseCredentials(){

            global $arrVar;

            $arrDatabase = $this->txtDatabase = $arrVar['ini']['Database'];

            if(!isset($arrDatabase['Username']) || empty($arrDatabase['Username'])){
                trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - Unable to connect to the database - credentials not found!',E_USER_ERROR);
                return false;
            }

            return $arrDatabase;
        }

		/**
		* Initialises the database using the settings from the INI (if available)
		*
		* @return boolean
		* @uses $txtDatabase
		* @uses funOpen()
		* @uses funTablePrefix
		* @uses funFetchVersion
		*/
		function clsDatabase()
		{
			global $arrVar;

			// Define the database constants
			define('CORE_DB_FETCH',1);
			define('CORE_DB_COUNT',2);
			define('CORE_DB_FIND',3);

			// Put the database connection variables in an easier to access variable
            $arrDatabase = $this->funGetDatabaseCredentials();

			// Check if the details were loaded - in case we need to use this class outside of the system
			if (!empty($arrDatabase['Username']))
			{
				// Attempt to connect to the database
				$this->funOpen($arrDatabase['Username'], $arrDatabase['Password'], $arrDatabase['Database'], $arrDatabase['Server'], $arrDatabase['Port']);
				// If we have a table prefix in use (i.e. we're having to share a database) then set it
				if (!empty($arrDatabase['TablePrefix']))
					$this->funTablePrefix($arrDatabase['TablePrefix']);
				// Now fetch the version information from coreVersion
				$this->funFetchVersion();
			}

			return true;
		}

		/**
		* A wrapper for the database insertion functionality
		*
		* This function allows the insert and update functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtTable The name of the table to insert into
		* @param array $arrData An array of fields to insert / update
		* @param int $intID The ID of the primary key
		* @param string $txtID The name of the primary key - if left blank it uses the standard format
		* @param boolean $blnUseTransaction Whether or not to use a transaction (in case we have one outside of the function)
		* @return int|boolean The Auto ID on success
		* @uses funRemovePrefix()
		* @uses funCheckTableRequired()
		* @uses funQueryInsert()
		* @uses funQueryUpdate()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		* @uses funStartTransaction()
		* @uses funRollback()
		* @uses funCommit()
		* @uses funFetchTables()
		* @uses funCoreFind()
		* @uses funTableInfo()
		*/
		function funCoreAddEdit($txtTable,$arrData,$intID=0,$txtID='',$blnUseTransaction=true)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (empty($txtTable) || count($arrData) < 1)
				return false;

			// If the name of the ID is blank we will assume it is the standard one
			if (empty($txtID))
				$txtID = $this->funRemovePrefix($txtTable) . 'ID';

			// If a transaction has already started then don't use transactions
			if($this->blnTransactionStarted && $blnUseTransaction)
				$blnUseTransaction = false;

			// Now lets see this table has sub tables
			// So first fetch the list of tables, in case it hasn't already been called
			$this->funFetchTables();

			// Now lets setup the arrays for sub tables
			$arrInsertData = array(
								'lang' => array(
									'txtID' => '',
									'ID' => 0,
									'Data' => array()
								));

			// Now lets see if we have a language table, and if so split out the language info
			foreach ($arrInsertData as $txtExt => $arrRow)
			{
				$txtTableOther = $txtTable . '_' . $txtExt;
				if (in_array($txtTableOther,$this->arrTables))
				{
					// First we get the table info
					$arrMFields = $arrVar['objDb']->funTableInfo($txtTable);
					$arrFields = $arrVar['objDb']->funTableInfo($txtTableOther);

					// Now lets find out the Table's ID
					$arrInsertData[$txtExt]['txtID'] = $this->funRemovePrefix($txtTableOther) . 'ID';
					// Now lets loop through the fields
					foreach ($arrFields as $txtField => $arrRow)
					{
						if (array_key_exists($txtField,$arrData))
						{
							$arrInsertData[$txtExt]['Data'][$txtField] = $arrData[$txtField];
							if ($txtField == $txtExtID)
								$arrInsertData[$txtExt]['ID'] = $arrData[$txtField];
							if (!array_key_exists($txtField,$arrMFields) || ($txtField == $txtID && !empty($intID)))
								unset($arrData[$txtField]);
						}
					}
				}
			}

			// Now lets check that the data we have is valid for the table
			if (!$this->funCheckTableRequired($txtTable,$intID,$arrData))
				return false;

			// Start the transaction (if needed)
			if ($blnUseTransaction)
				$this->funStartTransaction();

			// If we have an ID then UPDATE, otherwise INSERT
			if (empty($intID))
			{
				// Try and do the insert, returning on error
				$txtQueryRef = 'New' . $txtTable;
				if (!$this->funQueryInsert($txtQueryRef,$txtTable,$arrData))
				{
					// Rollback the transaction (if needed)
					if ($blnUseTransaction)
						$this->funRollback();
					return false;
				}
				// Insert the SQL into funDebug for easier debugging
				$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
				// Now fetch the last auto ID
				$intID = $this->funInsertID($txtQueryRef);
				// Finally free the resources used by the query
				$this->funFreeResult($txtQueryRef);
			}
			else
			{
				// Try and do the update, returning on error
				$txtQueryRef = 'Update' . $txtTable;
				if (!$this->funQueryUpdate($txtQueryRef,$txtTable,$arrData,array($txtID => array('=',$intID))))
				{
					// Rollback the transaction (if needed)
					if ($blnUseTransaction)
						$this->funRollback();
					return false;
				}
				// Insert the SQL into funDebug for easier debugging
				$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
				// Finally free the resources used by the query
				$this->funFreeResult($txtQueryRef);
			}

			// Now we loop through any extended table info and add it (if needed)
			foreach ($arrInsertData as $txtExt => $arrRow)
			{
				if (count($arrRow['Data']) < 1)
					continue;

				// Create the table name
				$txtTableOther = $txtTable . '_' . $txtExt;

				// Add the ID from our first insert, as that is the linking field
				$arrRow['Data'][$txtID] = $intID;

				// Although we may have an ID supplied we won't trust it and find it for ourselves
				// If it is a language field then we also need the language ID
				$arrWhere=array();
				if ($txtExt == 'lang')
					$arrWhere['LanguageID'] = array('=',$arrRow['Data']['LanguageID']);

				$arrRow['ID'] = $this->funCoreFind($txtTableOther,$txtID,$intID,'',$arrWhere);

				// Now lets check that the data we have is valid for the table
				if (!$this->funCheckTableRequired($txtTableOther,$arrRow['ID'],$arrRow['Data']))
				{
					// Rollback the transaction (if needed)
					if ($blnUseTransaction)
						$this->funRollback();
					return false;
				}

				// If we have an ID then UPDATE, otherwise INSERT
				if (empty($arrRow['ID']))
				{
					// Try and do the insert, returning on error
					$txtQueryRef = 'New' . $txtTableOther;
					if (!$this->funQueryInsert($txtQueryRef,$txtTableOther,$arrRow['Data']))
					{
						// Rollback the transaction (if needed)
						if ($blnUseTransaction)
							$this->funRollback();
						return false;
					}
					// Insert the SQL into funDebug for easier debugging
					$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
					// Now fetch the last auto ID
					$arrInsertData[$txtExt]['ID'] = $this->funInsertID($txtQueryRef);
					// Finally free the resources used by the query
					$this->funFreeResult($txtQueryRef);
				}
				else
				{
					// Try and do the update, returning on error
					$txtQueryRef = 'Update' . $txtTableOther;
					if (!$this->funQueryUpdate($txtQueryRef,$txtTableOther,$arrRow['Data'],array($arrRow['txtID'] => array('=',$arrRow['ID']))))
					{
						// Rollback the transaction (if needed)
						if ($blnUseTransaction)
							$this->funRollback();
						return false;
					}
					// Insert the SQL into funDebug for easier debugging
					$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
					// Finally free the resources used by the query
					$this->funFreeResult($txtQueryRef);
				}
			}

			// Commit the transaction (if needed)
			if ($blnUseTransaction)
				$this->funCommit();

			// Return the ID
			return $intID;
		}

		/**
		* A wrapper for the database insertion functionality when inserting data into multiple linked tables
		*
		* This function allows the insert and update functionality to be called on one line
		* removing the repetative process
		*
		* <code>
		* // An example of the $arrTable array based on Staff
		* // There is the table name, then in the array the field in that table, and then the table whose foreign key it is linked to
		* $arrTable = array(
		* 					'coreStaff' => array('PersonID','corePerson'),
		* 					'corePerson' => array()
		* );
		* </code>
		*
		* @param string $arrTable The name of the tables to insert into and the linked table / key
		* @param array $arrData An array of fields to insert / update in the tables
		* @param int $intID The ID of the primary key of the first table
		* @param boolean $blnUseTransaction Whether or not to use a transaction (in case we have one outside of the function)
		* @return int|boolean The Auto ID on success
		* @uses funRemovePrefix()
		* @uses funCheckTableRequired()
		* @uses funTableInfo()
		* @uses funCoreFind()
		* @uses funCoreAddEdit()
		* @uses funStartTransaction()
		* @uses funRollback()
		* @uses funCommit()
		*/
		function funCoreAddEditMultiple($arrTable,$arrData,$intID=0,$blnUseTransaction=true)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (count($arrTable) < 2 || count($arrData) < 1)
				return false;

			// If a transaction has already started then don't use transactions
			if($this->blnTransactionStarted && $blnUseTransaction)
				$blnUseTransaction = false;

			// Now lets initialise the array for the order in which we will run the insert / updates
			$arrInsert = array();

			// A variable to store the name of the first table as that is the ID we need to return
			$txtFirstTable = '';

			// Now we loop through the tables and find the one without a linked table / key, as that is our starting point
			foreach ($arrTable as $txtTable => $arrKey)
			{
				if (empty($txtFirstTable))
					$txtFirstTable = $txtTable;

				if (empty($arrKey[0]))
				{
					$arrInsert[] = array($txtTable => $arrKey);
					unset($arrTable[$txtTable]);
					break;
				}
			}
			// Now we loop through the rest of the tables and create the rest of the insert orders
			while (count($arrTable) > 0)
			{
				// We add the match found variable because we don't want to end up in an endless loop
				$blnMatchFound = false;
				foreach ($arrInsert as $arrRow)
				{
					foreach ($arrRow as $txtTable => $arrKey)
					{
						foreach ($arrTable as $txtTable2 => $arrKey2)
						{
							if ($txtTable == $arrKey2[1])
							{
								$arrInsert[] = array($txtTable2 => $arrKey2);
								unset($arrTable[$txtTable2]);
								$blnMatchFound = true;
							}
						}
					}
				}

				// If we haven't found a match then we are in a situation where an endless loop would occur, so we need to end
				if (!$blnMatchFound)
					trigger_error('Unable to match table for multiple insert / update',E_USER_ERROR);
			}

			// Now we initiase the array that will store the data for each table
			$arrInsertData = array();

			// Fetch the list of tables, in case it hasn't already been called
			$this->funFetchTables();

			// Now we can start looping through the tables and split out the data for each table
			foreach ($arrInsert as $intKey => $arrRow)
			{
				foreach ($arrRow as $txtTable => $arrKey)
				{
					// First we get the table info
					$arrFields = $arrVar['objDb']->funTableInfo($txtTable);

					// Now we also need to include any fields from any language and extension tables
					$arrTemp = array('lang');
					foreach ($arrTemp as $txtExt)
					{
						$txtTableOther = $txtTable . '_' . $txtExt;
						if (!in_array($txtTableOther,$this->arrTables))
							continue;

						$arrTemp2 = $arrVar['objDb']->funTableInfo($txtTable);
						foreach ($arrTemp2 as $txtField => $arrRow)
						{
							if (!isset($arrFields[$txtField]))
								$arrFields[$txtField] = $arrRow;
						}
					}

					// Now lets set the ID
					$arrInsertData[$txtTable]['ID'] = ($intKey == 0 ? $intID : 0);

					// Now lets find out the Table's ID
					$arrInsertData[$txtTable]['txtID'] = $this->funRemovePrefix($txtTable) . 'ID';

					// Now lets loop through the fields
					foreach ($arrFields as $txtField => $arrRow)
					{
						if (array_key_exists($txtField,$arrData))
						{
							$arrInsertData[$txtTable]['Data'][$txtField] = $arrData[$txtField];
							if ($txtField == $arrInsertData[$txtTable]['txtID'])
								$arrInsertData[$txtTable]['ID'] = $arrData[$txtField];
						}
					}
				}
			}

			// Start the transaction (if needed)
			if ($blnUseTransaction)
				$this->funStartTransaction();


			// Now lets start trying to insert / update the data
			foreach ($arrInsert as $arrRow)
			{
				foreach ($arrRow as $txtTable => $arrKey)
				{
					if (!empty($arrKey[0]))
					{
						// Add the linked field to the insert data
						$arrInsertData[$txtTable]['Data'][$arrKey[0]] = $arrInsertData[$arrKey[1]]['ID'];

						// Although we may have an ID supplied we won't trust it and find it for ourselves
						$arrInsertData[$txtTable]['ID'] = $this->funCoreFind($txtTable,$arrKey[0],$arrInsertData[$arrKey[1]]['ID']);
					}

					if (!$arrInsertData[$txtTable]['ID'] = $this->funCoreAddEdit($txtTable,$arrInsertData[$txtTable]['Data'],$arrInsertData[$txtTable]['ID']))
					{
						// Rollback the transaction (if needed)
						if ($blnUseTransaction)
							$this->funRollback();
						return false;
					}
				}
			}

			// Commit the transaction (if needed)
			if ($blnUseTransaction)
				$this->funCommit();

			// Return the ID
			return $arrInsertData[$txtFirstTable]['ID'];
		}

		/**
		* A wrapper for the database replace functionality
		*
		* This function allows the replace functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtTable The name of the table to insert into
		* @param array $arrData An array of fields to insert / update
		* @return boolean
		* @uses funCheckTableRequired()
		* @uses funQueryReplace()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		*/
		function funCoreReplace($txtTable,$arrData)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (empty($txtTable) || count($arrData) < 1)
				return false;

			// Now lets check that the data we have is valid for the table
			if (!$this->funCheckTableRequired($txtTable,$intID,$arrData))
				return false;

			// Try and do the replace, returning on error
			$txtQueryRef = 'Replace' . $txtTable;
			if (!$this->funQueryReplace($txtQueryRef,$txtTable,$arrData))
				return false;
			// Insert the SQL into funDebug for easier debugging
			$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
			// Finally free the resources used by the query
			$this->funFreeResult($txtQueryRef);

			return true;
		}

		/**
		* A wrapper for the database deletion functionality (for single rows)
		*
		* This function allows the delete functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtTable The name of the table for the deletion
		* @param int $intID The ID of the primary key
		* @param string $txtID The name of the primary key - if left blank it uses the standard format
		* @return boolean
		* @uses funRemovePrefix()
		* @uses funQueryDelete()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		*/
		function funCoreDelete($txtTable,$intID,$txtID='')
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (empty($txtTable) || empty($intID))
				return false;

			// If the name of the ID is blank we will assume it is the standard one
			if (empty($txtID))
				$txtID = $this->funRemovePrefix($txtTable) . 'ID';

			// Try and do the deletion, returning on error
			$txtQueryRef = 'Delete' . $txtTable;
			if (!$this->funQueryDelete($txtQueryRef,$txtTable,array($txtID => array('=',$intID))))
				return false;
			// Insert the SQL into funDebug for easier debugging
			$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
			// Finally free the resources used by the query
			$this->funFreeResult($txtQueryRef);

			return true;
		}

		/**
		* A wrapper for the database deletion functionality (for multiple rows)
		*
		* This function allows the delete functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtTable The name of the table to insert into
		* @param array $arrWhere The selection criteria for the deletion
		* @return boolean
		* @uses funQueryDelete()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		*/
		function funCoreDeleteMultiple($txtTable,$arrWhere)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (empty($txtTable) || !is_array($arrWhere))
				return false;

			// Try and do the deletion, returning on error
			$txtQueryRef = 'DeleteMultiple' . $txtTable;
			if (!$this->funQueryDelete($txtQueryRef,$txtTable,$arrWhere))
				return false;
			// Insert the SQL into funDebug for easier debugging
			$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
			// Finally free the resources used by the query
			$this->funFreeResult($txtQueryRef);

			return true;
		}

        /**
         * This will load in the 3rdParty Cache Library
         * @return void
         */
        private function funLoadPhpFastCache(){

            global $arrVar;

            if(!isset($arrVar['objConfig']) || !$arrVar['objConfig']->funGetIsCacheable())
                return false;

            if(!function_exists('__c')){
                $strPath = $arrVar['txtSiteCoreFileBaseClasses'];
                $strPath.= '/3rdparty/phpfastcache/'.
                    'phpfastcache-Stable-Version-1.x/'.
                    'phpfastcache_v2.1_release/phpfastcache/phpfastcache.php';
                require_once($strPath);
            }

            if(!isset($arrVar['objDbCache'])){

                if(function_exists("memcache_connect")) {
                    $arrVar['objDbCache'] = __c("memcache");
                    $strDriver = "memcache";
                }

                if(class_exists("memcached")) {
                    $arrVar['objDbCache'] = __c("memcached");
                    $strDriver = "memcached";
                }

                //set the options for memcache/memcached
                if(isset($arrVar['objDbCache'])){
                    $arrVar['objDbCache']->option("server", array(array("127.0.0.1",11211,100)));
                }

                //fallback to file caching
                if(!isset($arrVar['objDbCache'])){
                    $arrVar['strCachePath'] = $arrVar['txtSiteFileBaseCache'].'/database';
                    if(!is_dir($arrVar['strCachePath'])){
                        mkdir($arrVar['strCachePath']);
                    }

                    $arrVar['objDbCache'] = __c("files");
                    $arrVar['objDbCache']->option('path',$arrVar['strCachePath']);
                    $strDriver = "files";
                }

                $arrVar['objErrorHandler']->funDebug("Cache driver ".$strDriver." being used for database.");
            }

            return true;
        }

        /**
         * Fetches Cached Database Query from Caching Mechanism
         *
         * @param array $arrParams -- array of parameters (unique)
         * @return bool|mixed
         */
        private function funCoreQueryCached($arrParams = array()){

            global $arrVar;

            if(!$this->funLoadPhpFastCache()){
                $arrVar['objErrorHandler']->funDebug("Cacheable is turned off - no database caching will take place");
                return false;
            }

            if(isset($arrVar['objDbCache'])){
                $strCacheKey = '';
                foreach($arrParams as $strP){
                    $strCacheKey .= $strP;
                }
                $strCacheKey = md5($strCacheKey);

                if($objData = $arrVar['objDbCache']->get($strCacheKey)){
                    $arrVar['objErrorHandler']->funDebug("Successfully retrieved cache for database query: ".$arrParams[0]);
                    return $objData;
                }
            }

            return false;
        }

        /**
         * Save things into the cache
         *
         * @param array $arrParams - array of parameters (unique
         * @param array $objData - database results to cache
         */
        private function funCoreQueryCache($arrParams = array(), $objData, $intTTL = 300){

            global $arrVar;

            if(isset($arrVar['objDbCache'])){
                $strCacheKey = '';
                foreach($arrParams as $arrP){
                    $strCacheKey .= $arrP;
                }
                $strCacheKey = md5($strCacheKey);
                $arrVar['objDbCache']->set($strCacheKey, $objData, $intTTL);
            }
        }

		/**
		* A wrapper for the database selection functionality (for single rows)
		*
		* This function allows the select functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtTable The name of the table to insert into
		* @param int $intID The ID of the primary key
		* @param string $txtID The name of the primary key - if left blank it uses the standard format
		* @return boolean
		* @uses funRemovePrefix()
		* @uses funCoreFetch()
		*/
		function funCoreFetchSingle($txtTable,$intID,$txtID='',$intLanguageID=0,$blnExactMatch=NULL)
		{
			global $arrVar;

           /**
            * THIS IS DANGEROUS CODE IT HAS A RIPPLE EFFECT ACROSS ALL DATABASE LOOKUPS.
            *
            * COMMENTED OUT - AJN 02/01/2014
            *
            *
            * if(empty($intLanguageID) || $intLanguageID == 0)
			   * $intLanguageID = $_SESSION['LanguageID'];**/

			// First lets check that we have everything we need
			if (empty($txtTable) || empty($intID))
				return false;

            if($arrData = $this->funCoreQueryCached(func_get_args())){
                return $arrData[0];
            }

			if (is_null($blnExactMatch))
				$blnExactMatch = (empty($arrVar['objController']->txtAdminDir) ? true : false);

			// If the name of the ID is blank we will assume it is the standard one
			if (empty($txtID))
				$txtID = $this->funRemovePrefix($txtTable) . 'ID';

			// Setup the table array, as easier for adding langugae info if needed
			$arrTable = array();
			$arrTable[] = $txtTable;

			// Setup the field array, as easier for adding langugae info if needed
			$arrFields = array();
			$arrFields[] = $txtTable . '.*';

			// Setup the where array, as easier for adding langugae info if needed
			$arrWhere = array();
			$arrWhere[$txtTable . '.' . $txtID] = array('=',$intID);

			// If the the LanguageID is not empty then we have more to do
			if (!empty($intLanguageID))
			{
				// Now lets fetch the list of tables
				$this->funFetchTables();
				// Now lets see if the language table actually exists
				if (in_array($txtTable . '_lang',$this->arrTables))
				{
					//$arrTable[][($blnExactMatch ? 'inner' : 'left')] = array($txtTable . '_lang' => array($txtTable . '_lang' => $txtID, $txtTable => $txtID));

					$arrTable[][($blnExactMatch ? 'inner' : 'left')] = array($txtTable . '_lang' => array(array($txtTable . '_lang' => $txtID, $txtTable => $txtID),array($txtTable . '_lang' => 'LanguageID', '#VALUE#' => $intLanguageID)));

					$arrWhere[$txtTable . '_lang.LanguageID'] = array('=',$intLanguageID);
					array_unshift($arrFields,$txtTable . '_lang.*');
					$arrFields[] = $txtTable . '_lang.xDateAdded:xDateAdded_lang';
					$arrFields[] = $txtTable . '_lang.xLastUpdate:xLastUpdate_lang';
				}
			}

			$arrVar['objErrorHandler']->funDebug();

			// Try and fetch the row
			$txtQueryRef = 'FetchSingle' . $txtTable;
			$arrData = $this->funCoreFetch($txtQueryRef,$arrTable,$arrFields,$arrWhere,array(),1);

            $this->funCoreQueryCache(func_get_args(),$arrData);

			return ($arrData ? $arrData[0] : false);
		}

		/**
		* A wrapper for the database select functionality
		*
		* This function allows the select functionality to be called on one line
		* removing the repetative process
		*
		* @param string $txtQueryRef An identifier for the query (mainly used for debugging)
		* @param string|array $txtTable The name of the table(s) to use
		* @param array $arrField The fields that you want to fetch from the table
		* @param array $arrWhere The selection criteria
		* @param array $arrOrder The order in which the data should be returned
		* @param int $intLimit The maximum number of rows to return
		* @param int $intStart The row to start from
		* @param array $arrGroup The field(s) to group the results by
		* @param string $txtID The name of the field to be used as the key in the array returned - if left blank it is just a standard numeric array
		* @return boolean|array
		* @uses funQuerySelect()
		* @uses funFetchRow()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		* @uses funRemovePrefix()
		*/
		function funCoreFetch($txtQueryRef,$arrTable,$arrField=array('*'),$arrWhere=array(),$arrOrder=array(),$intLimit=0,$intStart=0,$arrGroup=array(),$txtID='')
		{
			global $arrVar;

			// Setup the blank data array
			$arrData = array();
			// Try and fetch the selection, returning on error
			if (!$this->funQuerySelect($txtQueryRef,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup))
				return false;
			// Insert the SQL into funDebug for easier debugging
			$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
			// Now loop through each row and add it to $arrData
			while ($arrRow = $this->funFetchRow($txtQueryRef))
			{
				// If we have been passed the name of a field in $txtID then use the value of that field as the key
				// Otherwise just create a normal numeric array
				if (!empty($txtID))
					$arrData[$arrRow[$txtID]] = $arrRow;
				else
					$arrData[] = $arrRow;
			}
			// Finally free the resources used by the query
			$this->funFreeResult($txtQueryRef);

			return $arrData;
		}

		/**
		* A wrapper for counting the number of matching rows
		*
		* This function allows the count of a selection to be called on one line
		* removing the repetative process
		*
		* @param string $txtQueryRef An identifier for the query (mainly used for debugging)
		* @param string|array $arrTable The name of the table(s) to use
		* @param array $arrField The field that you want to count
		* @param array $arrWhere The selection criteria
		* @return boolean|int
		* @uses funQuerySelect()
		* @uses funFetchRow()
		* @uses funFreeResult()
		* @uses clsErrorHandler::funDebug()
		* @uses funReturnSQL()
		*/
		function funCoreCount($txtQueryRef,$arrTable,$arrField,$arrWhere=array())
		{
			global $arrVar;

			// Make sure that we are getting a distinct count of the first field (normally there would be only one)
			$arrField[0] = array('count_distinct' => array($arrField[0] => 'intCount'));
			// Now lets fetch the selection count
			if (!$this->funQuerySelect($txtQueryRef,$arrTable,$arrField,$arrWhere,NULL,0,0))
				return false;
			// Insert the SQL into funDebug for easier debugging
			$arrVar['objErrorHandler']->funDebug($this->funReturnSQL($txtQueryRef),10000);
			// Set the default count incase the SQL finds nothing
			$intCount = 0;
			// Now try and fetch the true count
			if ($arrRow = $this->funFetchRow($txtQueryRef))
				$intCount = $arrRow['intCount'];
			// Finally free the resources used by the query
			$this->funFreeResult($txtQueryRef);

			return $intCount;
		}

		/**
		* A wrapper for finding the ID of a table
		*
		* This function allows your to find the ID of a table using any field. It is mainly used for
		* tables that extend others (i.e. extending corePerson)
		*
		* @param string|array $txtTable The name of the table to use
		* @param string $txtFindField The field that you want to search
		* @param mixed $txtFindValue The value of the find field
		* @param string $txtID The name of the primary key (if non-standard)
		* @param array $arrWhere Additional selection criteria (optional)
		* @return boolean|int
		* @uses funRemovePrefix()
		* @uses funCoreFetch()
		*/
		function funCoreFind($txtTable,$txtFindField,$txtFindValue,$txtID='',$arrWhere=array())
		{
			global $arrVar;

			// If the name of the ID is blank we will assume it is the standard one
			if (empty($txtID))
			{
				$txtTTable = $this->funRemovePrefix((is_array($txtTable) ? $txtTable[0] : $txtTable));
				$txtID = $txtTTable . 'ID';
			}

			// Add the find field and value to the where clause
			if (!empty($txtFindField))
				$arrWhere[$txtFindField] = array('=',$txtFindValue);

			// Fetch the first matching field (there should only be one)
			$arrData = $this->funCoreFetch('Find' . (is_array($txtTable) ? $txtTable[0] : $txtTable),$txtTable,array($txtID),$arrWhere,NULL,1);

			// If the ID has a dot in it (table) the remove it
			if (strpos($txtID,'.') !== false)
				list(,$txtID) = explode('.',$txtID);

			// Return the ID if we have it, or false if we don't
			return ($arrData ? $arrData[0][$txtID] : false);
		}

		/**
		* Removes the standard table prefixes in order for functions to auto generate the name of the primary ID
		*
		* @param string $txtTable The name of the table
		* @return string
		* @uses $arrTablePrefixes
		*/
		function funRemovePrefix($txtTable)
		{
			foreach ($this->arrTablePrefixes as $txtPrefix)
			{
				if (strpos($txtTable,$txtPrefix) === 0)
				{
					$txtTable = substr($txtTable,strlen($txtPrefix));
					break;
				}
			}

			return $txtTable;
		}

		/**
		* Merges two table ordering arrays
		*
		* The values from the first array take precedence. The values from the
		* second array will only be added if it is not already set
		*
		* @param array $arrOrder The main ordering array
		* @param array $arrOrder2 The ordering array to combine values from
		* @return array
		*/
		function funMergeOrder($arrOrder,$arrOrder2)
		{
			foreach ($arrOrder2 as $txtKey => $txtValue)
			{
				if (isset($arrOrder[$txtKey]))
					continue;

				$arrOrder[$txtKey] = $txtValue;
			}

			return $arrOrder;
		}
	}
?>
