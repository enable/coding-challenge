<?php
	/**
	* MySQL Wrapper Class
	* 
	* Wraps the MySQL functionality in common functions so that we can swap one database for another
	*
	* @version 2.0
	* @package core
	*/
	class clsMySQLWrapper
	{
		/**
		* Stores the pointer to the database
		* @var mixed
		*/
		var $pntDb = false;
		/**
		* Stores the details and pointers of queries until they are freed
		* @var array
		*/
		var $arrQuery = array();
		/**
		* Stores the table prefix - used when the system may need to share a database
		* @var string
		*/
		var $txtTablePrefix = '';
		/**
		* A cache of tables in the database
		* @var array
		*/
		var $arrTables = array();
		
		/**
		* Used to cache the version details of the database
		* @var array
		*/
		var $arrVersion = array();
		/**
		* The last version updated (any module)
		* @var array
		*/
		var $intVersionUpdated = 0;
		
		/**
		* Whether or not a transaction has been start
		* @var boolean
		*/
		var $blnTransactionStarted = false;
		
		/**
		* A lookup for the key types used in a table
		* @var array
		*/
		var $arrKeyType = array(
								'primary' => 'PRIMARY',
								'unique' => 'UNIQUE',
								'fulltext' => 'FULLTEXT'
							);
		/**
		* A lookup for the constraint options of foreign keys
		* @var array
		*/
		var $arrConstraintAction = array(
										'no action' => 'NO ACTION',
										'cascade' => 'CASCADE',
										'set null' => 'SET NULL'
									);
		
		/**
		* Opens the connection, connects to the database, and then sets the charset type to UTF8 for international support
		* 
		* @param string $txtUsername The username to use
		* @param string $txtPassword The password to use
		* @param string $txtDatabase The database to use
		* @param string $txtServer The server to use
		* @param int $intPort The port to use
		* @return boolean
		*/
		function funOpen($txtUsername, $txtPassword, $txtDatabase, $txtServer = 'localhost', $intPort = 3306)
		{
			// Attempt to open a connection
			$this->pntDb = mysql_connect($txtServer . ':' . $intPort, $txtUsername, $txtPassword);
			if (!$this->pntDb)
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - Unable to connect to the database!',E_USER_ERROR);
				return false;
			}
			
			// Try and connect to the database
			if (!mysql_select_db($txtDatabase,$this->pntDb))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - Unable to connect to the database!',E_USER_ERROR);
				return false;
			}
			
			// Now we set the charset to UTF8
			if (function_exists('mysql_set_charset'))
			{
				mysql_set_charset('utf8',$this->pntDb);
			}
			else
			{
				$this->funQuery('SetName','SET NAMES utf8');
				$this->funFreeResult('SetName');
			}
			
			return true;
		}
		
		/**
		* Closes the database connection
		* 
		* @return boolean
		*/
		function funClose()
		{
			mysql_close($this->pntDb);
			return true;
		}
		
		/**
		* Executes the SQL passed, or the SQL from the name passed if the SQL is empty
		* 
		* @param string $txtName The name of the query, used to reference the results
		* @param string $txtSQL The SQL to execute
		* @return boolean
		* @uses $arrQuery
		* @uses clsErrorHandler::funDebugSQL()
		*/
		function funQuery($txtName,$txtSQL='')
		{
			global $arrVar;
            
            
            if(!isset($arrVar['intQueryCount'])){
                $arrVar['intQueryCount'] = 0;
            }
            $arrVar['intQueryCount']++;
			
			// We need a name, so return if we don't have one
			if (empty($txtName))
				return false;
			
			// If the SQL is empty try and get the SQL using the name - if that fails return
			if (empty($txtSQL))
				$txtSQL = $this->arrQuery[$txtName]['SQL'];
			if (empty($txtSQL))
				return false;
			
			// Store the time before the SQL was run
			list($usec, $sec) = explode(' ', microtime());
			$intTime = ((float)$usec + (float)$sec);
			// Try and execute the SQL
			$pntResult = mysql_query($txtSQL,$this->pntDb);
			// Call the SQL debug code passing the start time and the end time
			list($usec, $sec) = explode(' ', microtime());
			$arrVar['objErrorHandler']->funDebugSQL($txtName,$txtSQL,$intTime,((float)$usec + (float)$sec));
			// If the SQL fails we store the error details, put the SQL in Debug and return
			if (!$pntResult)
			{
				$this->arrQuery[$txtName] = array('SQL' => $txtSQL, 'Resource' => $pntResult, 'ErrorNumber' => mysql_errno($this->pntDb), 'Error' => mysql_error($this->pntDb), 'AutoID' => NULL);
				$arrVar['objErrorHandler']->funDebug(array('Name' => $txtName,$this->arrQuery[$txtName]));
				return false;
			}
			
			// Store the details of the SQL and the pointer in the query array
			$this->arrQuery[$txtName] = array('SQL' => $txtSQL, 'Resource' => $pntResult, 'ErrorNumber' => 0, 'Error' => NULL, 'AutoID' => 'n/a');
			
			// If there is an auto id store that too
			if ($intID = mysql_insert_id())
				$this->arrQuery[$txtName]['AutoID'] = $intID;
			
			return true;
		}
		
		/**
		* Builds a SELECT query using the parameters passed and either executes it or returns the SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string|array $txtTable The name of the table(s) to use
		* @param array $arrFields The fields that you want to fetch from the table(s)
		* @param array $arrWhere The selection criteria
		* @param array $arrOrder The order in which the data should be returned
		* @param int $intLimit The maximum number of rows to return
		* @param int $intStart The row to start from
		* @param array $arrGroupBy The field(s) to group the results by
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return boolean|string
		* @uses funWhere()
		* @uses funJoins()
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses clsDataManipulation::funIsAssoc()
		* @uses clsValidation::funValidateDouble()
		*/
		function funQuerySelect($txtName, $txtTable, $arrFields, $arrWhere = array(), $arrOrder = array(), $intLimit=0, $intStart=0, $arrGroupBy = array(), $blnReturnSQL=false)
		{
			global $arrVar;
			
			// Check that we have the required parameters or return
			if (empty($txtName) || empty($txtTable) || !is_array($arrFields) || $intLimit != intval($intLimit))
				return false;
			
			// Build the WHERE portion of the SQL
			$txtWhere = $this->funWhere($arrWhere);
			
			// If $txtTable is an array then build an array and pass it to the funJoins to handle any joins
			// Otherwise just set the name of the table
			if (is_array($txtTable))
			{
				$arrTable = array();
				$arrTemp = $txtTable;
				$txtTable = '';
				$txtSep = '';
				
				foreach($arrTemp as $txtValue)
				{
					if (is_array($txtValue))
					{
						list($txtKey,$arrValues) = each($txtValue);
						switch(strtolower($txtKey))
						{
							case 'inner':
								list($txtKey2,$arrValues2) = each($arrValues);
								
								if (strpos($txtKey2,':') !== false)
								{
									$txtKey2 = explode(':',$txtKey2);
									$arrTable[$txtKey2[1]]['String'] = '`' . $this->txtTablePrefix . $txtKey2[0] . '` AS `' . $this->txtTablePrefix . $txtKey2[1] . '`';
									$txtKey2 = $txtKey2[1];
								}
								else
									$arrTable[$txtKey2]['String'] = '`' . $this->txtTablePrefix . $txtKey2 . '`';
								
								if ($arrVar['objDataManipulation']->funIsAssoc($arrValues2))
								{
									list($txtTable1,$txtField1) = each($arrValues2);
									list($txtTable2,$txtField2) = each($arrValues2);
									
									$txtWhere = $this->txtTablePrefix . $txtTable1 . '.' . $txtField1 . ' = ' . $this->txtTablePrefix . $txtTable2 . '.' . $txtField2 . (empty($txtWhere) ? '' : ' AND ' . $txtWhere);
								}
								else
								{
									foreach ($arrValues2 as $intCount => $arrValues3)
									{
										list($txtTable1,$txtField1) = each($arrValues3);
										list($txtTTable2,$txtField2) = each($arrValues3);
										break;
									}
									
									$txtTWhere = '';
									foreach ($arrValues2 as $intCount => $arrValues3)
									{
										list($txtTable1,$txtField1) = each($arrValues3);
										list($txtTable2,$txtField2) = each($arrValues3);
										
										$txtTWhere .= ($intCount > 0 ? ' AND ' : '') . '`' . $this->txtTablePrefix . $txtTable1 . '`.`' . $txtField1 . '` = ';
										if ($txtTable2 == '#VALUE#')
											$txtTWhere .= '`' . $this->funEscape($txtField2) . '`';
										else
											$txtTWhere .= '`' . $this->txtTablePrefix . $txtTable2 . '`.`' . $txtField2 . '`';
									}
									$txtWhere = $txtTWhere . (empty($txtWhere) ? '' : ' AND ' . $txtWhere);
								}
								break;
							case 'finner':
							case 'left':
							case 'right':
								list($txtKey2,$arrValues2) = each($arrValues);
								
								if (strpos($txtKey2,':') !== false)
								{
									list($txtKey2,$txtAlias) = explode(':',$txtKey2);
									$txtTJoin = ' ' . strtoupper($txtKey) . ' JOIN `' . $this->txtTablePrefix . $txtKey2 . '` AS `' . $this->txtTablePrefix . $txtAlias . '` ON ';
									$txtKey2 = $txtAlias;
								}
								else
								{
									$txtTJoin = ' ' . strtoupper($txtKey) . ' JOIN `' . $this->txtTablePrefix . $txtKey2 . '` ON ';
								}
								
								if ($arrVar['objDataManipulation']->funIsAssoc($arrValues2))
								{
									list($txtTable1,$txtField1) = each($arrValues2);
									list($txtTable2,$txtField2) = each($arrValues2);
									
									$arrTable[$txtTable2]['Joins'][$txtKey2] = $txtTJoin . '(';
									$arrTable[$txtTable2]['Joins'][$txtKey2] .= '`' . $this->txtTablePrefix . $txtTable1 . '`.`' . $txtField1 . '` = `' . $this->txtTablePrefix . $txtTable2 . '`.`' . $txtField2 . '`';
									$arrTable[$txtTable2]['Joins'][$txtKey2] .= ')';
								}
								else
								{
									foreach ($arrValues2 as $intCount => $arrValues3)
									{
										list($txtTable1,$txtField1) = each($arrValues3);
										list($txtTTable2,$txtField2) = each($arrValues3);
										break;
									}
									
									$arrTable[$txtTTable2]['Joins'][$txtKey2] = $txtTJoin . '(';
									foreach ($arrValues2 as $intCount => $arrValues3)
									{
										list($txtTable1,$txtField1) = each($arrValues3);
										list($txtTable2,$txtField2) = each($arrValues3);
										
										$arrTable[$txtTTable2]['Joins'][$txtKey2] .= ($intCount > 0 ? ' AND ' : '') . '`' . $this->txtTablePrefix . $txtTable1 . '`.`' . $txtField1 . '` = ';
										if ($txtTable2 == '#VALUE#')
											$arrTable[$txtTTable2]['Joins'][$txtKey2] .= '`' . $this->funEscape($txtField2) . '`';
										else
											$arrTable[$txtTTable2]['Joins'][$txtKey2] .= '`' . $this->txtTablePrefix . $txtTable2 . '`.`' . $txtField2 . '`';
									}
									$arrTable[$txtTTable2]['Joins'][$txtKey2] .= ')';
								}
								$arrTable[$txtKey2]['String'] = false;
								break;
							default:
								trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - an unknown join type of ' . $txtKey . ' was passed',E_USER_ERROR);
								return false;
								break;
						}
					}
					else
					{
						$arrTable[$txtValue]['String'] = '`' . $this->txtTablePrefix . $txtValue . '`';
					}
				}
				
				$txtTable = $this->funJoins($arrTable);
			}
			else
			{
				$txtTable = '`' . $this->txtTablePrefix . $txtTable . '`';
			}
			
			// Now lets loop through the fields building the field part of the query
			$txtSep = '';
			$txtFields = '';
			foreach ($arrFields as $txtField)
			{
				// If the field part is an array then we need to check for the additional functions (e.g. SUM, MIN, MAX, etc)
				// Otherwise just build the field string
				if (is_array($txtField))
				{
					list($txtKey,$arrValues) = each($txtField);
					$blnDistinct = false;
					switch(strtolower($txtKey))
					{
						case 'count_distinct':
								$txtKey = str_replace('_distinct','',$txtKey);
								$blnDistinct = true;
						case 'avg':
						case 'min':
						case 'max':
						case 'sum':
						case 'month':
						case 'year':
						case 'week':
						case 'count':
						case 'length':
								list($txtField1,$txtField2) = each($arrValues);
								$txtTempTable = '';
								$arrTemp = explode('.',$txtField1);
								if (count($arrTemp) > 1)
								{
									$txtTempTable = '`' . $this->txtTablePrefix . $arrTemp[0] . '`.';
									$txtField1 = $arrTemp[1];
								}
								$txtFields .= $txtSep . strtoupper($txtKey) . '(' . ($blnDistinct ? 'DISTINCT ' : '') . $txtTempTable . '`' . $txtField1 . '`) AS `' . $txtField2 . '`';
							break;
						case 'distinct':
								$txtField1 = $arrValues;
								$txtTempTable = '';
								$arrTemp = explode('.',$txtField1);
								if (count($arrTemp) > 1)
								{
									$txtTempTable = '`' . $this->txtTablePrefix . $arrTemp[0] . '`.';
									$txtField1 = $arrTemp[1];
								}
								$txtFields .= $txtSep . strtoupper($txtKey) . '(' . $txtTempTable . '`' . $txtField1 . '`)';
							break;
						default:
							break;
					}
				}
				else
				{
					$txtTempTable = '';
					list($txtField,$txtAlias) = explode(':',$txtField);
					$arrTemp = explode('.',$txtField);
					if (count($arrTemp) > 1)
					{
						$txtTempTable = '`' . $this->txtTablePrefix . $arrTemp[0] . '`.';
						$txtField = $arrTemp[1];
					}
					
					
					if ($txtField == '*')
					{
						$txtFields .= $txtSep . $txtTempTable . $txtField;
					}
					else
					{
						$txtFields .= $txtSep . $txtTempTable . '`' . $txtField . '`' . (empty($txtAlias) ? '' : ' AS ' . $txtAlias);
					}
				}
				$txtSep = ',';
			}
			
			// Now lets loop through and order by items
			$txtOrder = '';
			$txtSep = '';
			foreach ($arrOrder as $txtField => $txtValue)
			{
				$txtTempTable = '';
				$arrTemp = explode('.',$txtField);
				if (count($arrTemp) > 1)
				{
					$txtTempTable = '`' . $this->txtTablePrefix . $arrTemp[0] . '`.';
					$txtField = $arrTemp[1];
				}
				if (strpos($txtField,')') === false)
					$txtOrder .= $txtSep . trim($txtTempTable . '`' . $txtField . '` ' . $txtValue);
				else
				$txtOrder .= $txtSep . trim($txtTempTable . $txtField . ' ' . $txtValue);
				$txtSep = ',';
			}
			
			// Now lets loop through the grouping options
			$txtGroupBy = '';
			$txtSep = '';
			foreach ($arrGroupBy as $txtField)
			{
				$txtTempTable = '';
				$arrTemp = explode('.',$txtField);
				if (count($arrTemp) > 1)
				{
					$txtTempTable = '`' . $this->txtTablePrefix . $arrTemp[0] . '`.';
					$txtField = $arrTemp[1];
				}
				$txtGroupBy .= $txtSep . $txtTempTable . '`' . $txtField . '`';
				$txtSep = ',';
			}
			
			// Now lets put it all together
			$txtSQL = 'SELECT ' . ($arrVar['objErrorHandler']->blnSlowSQL && $arrVar['objErrorHandler']->blnDebug ? 'SQL_NO_CACHE ' : '') . $txtFields . ' FROM ' . $txtTable . (empty($txtWhere) ? '' : ' WHERE ' . $txtWhere) . (empty($txtGroupBy) ? '' : ' GROUP BY ' . $txtGroupBy) . (empty($txtOrder) ? '' : ' ORDER BY ' . $txtOrder) . (empty($intLimit) ? '' : ' LIMIT '. (empty($intStart) ? '0,' . $intLimit : $intStart . ',' . $intLimit));
			
			// If requested lets just return the SQL and return
			if ($blnReturnSQL)
				return $txtSQL;
			
			// Run the query and return the result
			return $this->funQuery($txtName,$txtSQL);
		}
		
		/**
		* Builds the INSERT query using the parameters passed and either executes it or returns the SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string $txtTable The name of the table to use
		* @param array $arrFields The fields and values to insert into the table. If it is an array of arrays then a multi insert query will be built
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return boolean|string
		* @uses funTableInfo()
		* @uses clsDataManipulation::funIsAssoc()
		* @uses funDateFormat()
		* @uses funEscape()
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFieldFormat()
		*/
		function funQueryInsert($txtName, $txtTable, $arrFields, $blnReturnSQL=false)
		{
			global $arrVar;
			
			// Check that we have all of the required items
			if (empty($txtName) || empty($txtTable) || !is_array($arrFields))
				return false;
			
			// Fetch the table info
			$arrTableInfo = $this->funTableInfo($txtTable);
			
			// Now we loop through $arrFields and build the field list and values
			$txtValueFields = '';
			$txtValues = '';
			$txtSep = '';
			if ($arrVar['objDataManipulation']->funIsAssoc($arrFields))
			{
				if (isset($arrTableInfo['xDateAdded']) && empty($arrFields['xDateAdded']))
					$arrFields['xDateAdded'] = time();
				if (isset($arrTableInfo['xLastUpdate']) && empty($arrFields['xLastUpdate']))
					$arrFields['xLastUpdate'] = time();
				
				// Format any fields (i.e. Dates)
				$arrFields = $this->funFieldFormat($arrFields,$arrTableInfo);
				
				$txtValues .= '(';
				foreach ($arrFields as $txtValueField => $txtValue)
				{
					$txtValueFields .= $txtSep . '`' . $this->funEscape($txtValueField) . '`';
					if (is_null($txtValue))
						$txtValues .= $txtSep . 'NULL';
					else
						$txtValues .= $txtSep . '"' . $this->funEscape($txtValue) . '"';
					$txtSep = ',';
				}
				$txtValues .= ')';
			}
			else
			{
				if (isset($arrTableInfo['xDateAdded']) && empty($arrRow['xDateAdded']))
					$arrRow['xDateAdded'] = time();
				if (isset($arrTableInfo['xLastUpdate']) && empty($arrRow['xLastUpdate']))
					$arrRow['xLastUpdate'] = time();
				
				foreach ($arrFields as $intKey => $arrRow)
				{
					// Format any fields (i.e. Dates)
					$arrFields = $this->funFieldFormat($arrFields,$arrTableInfo);
					
					$txtValues .= $txtSep . '(';
					$txtSep2 = '';
					foreach ($arrRow as $txtValueField => $txtValue)
					{
						if ($intKey < 1)
							$txtValueFields .= $txtSep2 . '`' . $this->funEscape($txtValueField) . '`';
						if (is_null($txtValue))
							$txtValues .= $txtSep2 . 'NULL';
						else
							$txtValues .= $txtSep2 . '"' . $this->funEscape($txtValue) . '"';
						$txtSep2 = ',';
					}
					$txtValues .= ')';
					$txtSep = ',';
				}
			}
			
			// Now lets put it all together
			$txtSQL = 'INSERT INTO `' . $this->txtTablePrefix . $txtTable . '` (' . $txtValueFields . ') VALUES ' . $txtValues;
			
			// If requested lets just return the SQL and return
			if ($blnReturnSQL)
				return $txtSQL;
			
			// Run the query and return the result
			return $this->funQuery($txtName,$txtSQL);
		}
		
		/**
		* Builds the REPLACE query using the parameters passed and either executes it or returns the SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string $txtTable The name of the table to use
		* @param array $arrFields The fields and values to insert into the table
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return boolean|string
		* @uses funTableInfo()
		* @uses funDateFormat()
		* @uses funEscape()
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFieldFormat()
		*/
		function funQueryReplace($txtName, $txtTable, $arrFields, $blnReturnSQL=false)
		{
			global $arrVar;
			
			// Check that we have all of the required items
			if (empty($txtName) || empty($txtTable) || !is_array($arrFields))
				return false;
			
			// Fetch the table info
			$arrTableInfo = $this->funTableInfo($txtTable);
			
			if (isset($arrTableInfo['xDateAdded']) && empty($arrFields['xDateAdded']))
				$arrFields['xDateAdded'] = time();
			if (isset($arrTableInfo['xLastUpdate']) && empty($arrFields['xLastUpdate']))
				$arrFields['xLastUpdate'] = time();
			
			// Format any fields (i.e. Dates)
			$arrFields = $this->funFieldFormat($arrFields,$arrTableInfo);
			
			// Now we loop through $arrFields and build the field list and values
			$txtValueFields = '';
			$txtValues = '';
			$txtSep = '';
			foreach ($arrFields as $txtValueField => $txtValue)
			{
				$txtValueFields .= $txtSep . '`' . $this->funEscape($txtValueField) . '`';
				if (is_null($txtValue))
					$txtValues .= $txtSep . 'NULL';
				else
					$txtValues .= $txtSep . '"' . $this->funEscape($txtValue) . '"';
				$txtSep = ',';
			}
			
			// Now lets put it all together
			$txtSQL = 'REPLACE INTO `' . $this->txtTablePrefix . $txtTable . '` (' . $txtValueFields . ') VALUES (' . $txtValues . ')';
			
			// If requested lets just return the SQL and return
			if ($blnReturnSQL)
				return $txtSQL;
			
			// Run the query and return the result
			return $this->funQuery($txtName,$txtSQL);
		}
		
		/**
		* Builds the UPDATE query using the parameters passed and either executes it or returns the SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string $txtTable The name of the table to use
		* @param array $arrFields The fields and values to insert into the table
		* @param array $arrWhere The update criteria
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return boolean|string
		* @uses funTableInfo()
		* @uses funDateFormat()
		* @uses funWhere()
		* @uses funEscape()
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFieldFormat()
		*/
		function funQueryUpdate($txtName, $txtTable, $arrFields, $arrWhere, $blnReturnSQL=false)
		{
			// First lets check that we have everything
			if (empty($txtName) || empty($txtTable) || !is_array($arrFields) || !is_array($arrWhere))
				return false;
			
			// Fetch the table info
			$arrTableInfo = $this->funTableInfo($txtTable);
			if (isset($arrTableInfo['xLastUpdate']) && empty($arrFields['xLastUpdate']))
				$arrFields['xLastUpdate'] = time();
			
			// Format any fields (i.e. Dates)
			$arrFields = $this->funFieldFormat($arrFields,$arrTableInfo);
			
			// Build the WHERE part
			$txtWhere = $this->funWhere($arrWhere);
			
			// Loop through the fields and build the names and values
			$txtSep = '';
			$txtFieldValues = '';
			foreach ($arrFields as $txtValueField => $txtValue)
			{
				$txtFieldValues .= $txtSep . '`' . $this->funEscape($txtValueField) . '` = ';
				if (is_null($txtValue))
					$txtFieldValues .= 'NULL';
				else
					$txtFieldValues .= '"' . $this->funEscape($txtValue) . '"';
				$txtSep = ',';
			}
			
			// Now lets put everything together
			$txtSQL = 'UPDATE `' . $this->txtTablePrefix . $txtTable . '` SET ' . $txtFieldValues . ' WHERE ' . $txtWhere;
			
			// If requested we will just return the SQL
			if ($blnReturnSQL)
				return $txtSQL;
			
			// Run the query and return the result
			return $this->funQuery($txtName,$txtSQL);
		}
		
		/**
		* Fetches the table creation SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string $txtTable The name of the table to use
		* @return boolean|array
		* @uses clsDataManipulation::funFetchCacheFile()
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFetchRow()
		* @uses funFreeResult()
		* @uses clsDataManipulation::funSaveFile()
		*/
		function funShowCreate($txtName,$txtTable)
		{
			global $arrVar;
			
			// First lets check that we have everything that we need
			if (empty($txtName) || empty($txtTable))
				return false;
			
			// Lets see if we have a cached version
			/*$txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'database' . DIRECTORY_SEPARATOR . 'show_create_' . strtolower($txtTable) . '.php';
			if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile,0,$this->intVersionUpdated))
				return unserialize($txtCacheContent);*/
			
			// Fetch the table creation SQL
			$txtSQL = 'SHOW CREATE TABLE `' . $this->txtTablePrefix . $txtTable . '`';
			$this->funQuery($txtName,$txtSQL);
			$arrData = $this->funFetchRow($txtName);
			$this->funFreeResult();
			
			// Save the result in a cache file
			//$arrVar['objDataManipulation']->funSaveFile(serialize($arrData),$txtCacheFile);
			
			return $arrData;
		}
		
		/**
		* Builds the DELETE query using the parameters passed and either executes it or returns the SQL
		* 
		* @param string $txtName An identifier for the query (used by other functions)
		* @param string $txtTable The name of the table to use
		* @param array $arrWhere The deletion criteria
		* @return boolean
		* @uses funWhere()
		* @uses $txtTablePrefix
		* @uses funQuery()
		*/
		function funQueryDelete($txtName,$txtTable,$arrWhere)
		{
			// First lets check we have everything
			if (empty($txtName) || empty($txtTable) || !is_array($arrWhere))
				return false;
			
			// Now lets build the WHERE part of the SQL
			$txtWhere = $this->funWhere($arrWhere);
			
			// Now lets put it all together
			$txtSQL = 'DELETE FROM `' . $this->txtTablePrefix . $txtTable . '` WHERE ' . $txtWhere;
			
			// Run the query and return the result
			return $this->funQuery($txtName,$txtSQL);
		}
		
		/**
		* Returns the number of rows returned by a query
		* 
		* @param string $txtName The query identifier
		* @return int|boolean
		*/
		function funRowsReturned($txtName)
		{
			return mysql_num_rows($this->arrQuery[$txtName]['Resource']);
		}
		
		/**
		* Returns the number of rows affected by a query
		* 
		* @param string $txtName The query identifier
		* @return int|boolean
		*/
		function funRowsAffected($txtName)
		{
			return mysql_affected_rows($this->arrQuery[$txtName]['Resource']);
		}
		
		/**
		* Starts a transaction
		* 
		* @return boolean
		* @uses funQuery()
		*/
		function funStartTransaction()
		{
			if ($this->blnTransactionStarted) 
			{
				return false;	
			}
			
			if ($this->funQuery('StartTransaction','START TRANSACTION'))
				$this->blnTransactionStarted = true;
			else 
				$this->blnTransactionStarted = false;
			
			return $this->blnTransactionStarted;
		}
		
		/**
		* Commits a transaction
		* 
		* @return boolean
		* @uses funQuery()
		*/
		function funCommit()
		{
			$this->blnTransactionStarted = false;
			return $this->funQuery('Commit','COMMIT');
		}
		
		/**
		* Rolls back a transaction
		* 
		* @return boolean
		* @uses funQuery()
		*/
		function funRollback()
		{
			$this->blnTransactionStarted = false;
			return $this->funQuery('Rollback','ROLLBACK');
		}
		
		/**
		* Escapes values so they are safe to be used in queries
		* 
		* @param string $txtString The value to escape
		* @return string
		*/
		function funEscape($txtString)
		{
			return mysql_real_escape_string($txtString,$this->pntDb);
		}
		
		/**
		* Fetches the error information for a query
		* 
		* @param string $txtName The query identifier
		* @return array
		* @uses $arrQuery
		*/
		function funError($txtName)
		{
			return array('ErrorNumber' => $this->arrQuery['ErrorNumber'], 'Error' => $this->arrQuery['Error']);
		}
		
		/**
		* Frees up the mysql resource and removes the data from $arrQuery
		* 
		* @param string $txtName The query identifier
		* @return boolean
		* @uses $arrQuery
		*/
		function funFreeResult($txtName)
		{
			if ($this->arrQuery[$txtName]['Resource'] === true)
				return true;
			
			mysql_free_result($this->arrQuery[$txtName]['Resource']);
			unset($this->arrQuery[$txtName]);
			
			return true;
		}
		
		/**
		* Fetch a row from the query
		* 
		* @param string $txtName The query identifier
		* @param boolean $blnAssoc Whether or not to return an associative or non-associative array
		* @return array|boolean
		* @uses $arrQuery
		*/
		function funFetchRow($txtName,$blnAssoc=true)
		{
			if ($blnAssoc)
				return mysql_fetch_assoc($this->arrQuery[$txtName]['Resource']);
			else
				return mysql_fetch_row($this->arrQuery[$txtName]['Resource']);
		}
		
		/**
		* Returns the auto ID generated by the query
		* 
		* @param string $txtName The query identifier
		* @return boolean
		* @uses $arrQuery
		*/
		function funInsertID($txtName)
		{
			if (empty($this->arrQuery[$txtName]['AutoID']))
				return false;
			
			return $this->arrQuery[$txtName]['AutoID'];
		}
		
		/**
		* Converts time into the date format used by the database
		* 
		* @param int $intTime The time to convert
		* @param boolean $blnIncludeTime Whether or not to include the time
		* @return string
		*/
		function funDateFormat($intTime,$blnIncludeTime=false)
		{
			if ($blnIncludeTime)
				return date('Y-m-d H:i:s',$intTime);
			else
				return date('Y-m-d',$intTime);
		}
		
		/**
		* Returns the SQL generated by the query
		* 
		* @param string $txtName The query identifier
		* @return boolean|string
		* @uses $arrQuery
		*/
		function funReturnSQL($txtName)
		{
			if (!isset($this->arrQuery[$txtName]))
				return false;
			
			return $this->arrQuery[$txtName]['SQL'];
		}
		
		/**
		* Sets the table prefix to use
		* 
		* @param string $txtPrefix The prefix to use
		* @return boolean
		* @uses $txtTablePrefix
		*/
		function funTablePrefix($txtPrefix)
		{
			if (!preg_match('/[a-z0-9]+/',$txtPrefix))
				return false;
			
			$this->txtTablePrefix = $txtPrefix;
			return true;
		}
		
		/**
		* Fetches the fields and their settings
		* 
		* @param string $txtTable The table to use
		* @return array
		* @uses clsDataManipulation::funFetchCacheFile()
		* @uses $intVersionUpdated
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFetchRow()
		* @uses funFreeResult()
		* @uses clsDataManipulation::funSaveFile()
		*/
		function funTableInfo($txtTable)
		{
			global $arrVar;
			
			// Check that we have everything we need
			if (empty($txtTable))
				return false;
			
			// First check if we have been called already this session
			if (isset($this->arrCache[__FUNCTION__][$txtTable]))
				return $this->arrCache[__FUNCTION__][$txtTable];
			
			// Check first if we have a cached version
			$txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'database' . DIRECTORY_SEPARATOR . 'table_info_' . strtolower($txtTable) . '.php';
			if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile)){
                $this->arrCache[__FUNCTION__][$txtTable] = unserialize($txtCacheContent);
                return $this->arrCache[__FUNCTION__][$txtTable];
            }
			
			// Fetch the field info
			if (!$this->funQuery('TableInfo','SHOW COLUMNS FROM `' . $this->txtTablePrefix . $txtTable . '`'))
				return false;
			
			// Process the table info and put it in a useful format
			$arrTableInfo = array();
			while ($arrRow = $this->funFetchRow('TableInfo'))
			{
				if (!preg_match('/([A-z]+)\(([0-9]+)\)/',$arrRow['Type'],$arrMatches))
					$arrMatches = array(NULL,$arrRow['Type'],NULL);
				$arrTableInfo[$arrRow['Field']] = array(
														'Type'		=>	$arrMatches[1],
														'Size'		=>	$arrMatches[2],
														'Required'	=>	($arrRow['Null'] == 'YES' ? false : true),
														'AutoID'	=>	($arrRow['Extra'] == 'auto_increment' ? true : false),
														'Default'	=>	($arrRow['Default'] == 'NULL' ? NULL : $arrRow['Default'])
													);
				switch ($arrRow['Field'])
				{
					case 'ForSale':
					case 'SortOrder':
					case 'ParentID':
						$arrTableInfo[$arrRow['Field']]['Required'] = false;
						break;
				}
			}
			$this->funFreeResult('TableInfo');
			
			// Save the results
			$arrVar['objDataManipulation']->funSaveFile(serialize($arrTableInfo),$txtCacheFile);
			
			// Save it in the class cache
			$this->arrCache[__FUNCTION__][$txtTable] = $arrTableInfo;
			
			return $arrTableInfo;
		}
		
		/**
		* Fetches the keys and constraints
		* 
		* @param string $txtTable The table to use
		* @return array
		* @uses clsDataManipulation::funFetchCacheFile()
		* @uses $intVersionUpdated
		* @uses $txtTablePrefix
		* @uses funQuery()
		* @uses funFetchRow()
		* @uses funFreeResult()
		* @uses clsDataManipulation::funSaveFile()
		*/
		function funAdvancedTableInfo($txtTable)
		{
			global $arrVar;
			
			// Check that we have everything we need
			if (empty($txtTable))
				return false;
			
			// First check if we have been called already this session
			if (isset($this->arrCache[__FUNCTION__][$txtTable]))
				return $this->arrCache[__FUNCTION__][$txtTable];
			
			// Check first if we have a cached version
			$txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'database' . DIRECTORY_SEPARATOR . 'advanced_table_info_' . strtolower($txtTable) . '.php';
			if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile)){
                $this->arrCache[__FUNCTION__][$txtTable] = unserialize($txtCacheContent);
                return $this->arrCache[__FUNCTION__][$txtTable];
            }
			
			// Fetch the create table SQL
			$arrCData = $this->funShowCreate(__FUNCTION__,$txtTable);
			
			// Fetch the list of tables
			$this->funFetchTables();
			
			// produce the table lookup
			$arrTLookup = array();
			foreach ($this->arrTables as $txtTable)
				$arrTLookup[strtolower($txtTable)] = $txtTable;
			
			// Process the table info and put it in a useful format
			$arrTableInfo = array(
				'keys' => array(),
				'fkeys' => array()
			);
			preg_match_all('/([A-Z ]*)KEY `(.*)` \((.*)\)/',$arrCData['Create Table'],$arrMatches);
			foreach ($arrMatches[0] as $intKey => $txtValue)
			{
				$arrTemp = explode(',',$arrMatches[3][$intKey]);
				$arrFields = array();
				foreach ($arrTemp as $txtTemp)
				{
					list(,$txtField) = explode('`',$txtTemp);
					$arrFields[] = $txtField;
				}
				
				$arrTableInfo['keys'][] = array(
					'type' => strtolower(trim($arrMatches[1][$intKey])),
					'name' => $arrMatches[2][$intKey],
					'fields' => $arrFields
				);
			}
			preg_match_all('/CONSTRAINT `(.*)` FOREIGN KEY \(`(.*)`\) REFERENCES `(.*)` \(`(.*)`\) ON DELETE ([A-Z ]+) ON UPDATE ([A-Z ]+)/',$arrCData['Create Table'],$arrMatches);
			foreach ($arrMatches[0] as $intKey => $txtValue)
			{
				$arrTableInfo['fkeys'][] = array(
					'name' => $arrMatches[1][$intKey],
					'field' => $arrMatches[2][$intKey],
					'ftable' => $arrTLookup[$arrMatches[3][$intKey]],
					'ffield' => $arrMatches[4][$intKey],
					'ondelete' => strtolower($arrMatches[5][$intKey]),
					'onupdate' => strtolower($arrMatches[6][$intKey])
				);
			}
			
			// Save the results
			$arrVar['objDataManipulation']->funSaveFile(serialize($arrTableInfo),$txtCacheFile);
			
			// Save it in the class cache
			$this->arrCache[__FUNCTION__][$txtTable] = $arrTableInfo;
			
			return $arrTableInfo;
		}
		
		/**
		* Processes the array of WHERE criteria and returns the SQL for the WHERE statement
		* 
		* @param array $arrWhere The selection criteria
		* @return string
		* @uses $txtTablePrefix
		* @uses funEscape()
		* @uses clsValidation::funValidateDouble()
		* @uses funFieldFormat()
		*/
		function funWhere($arrWhere)
		{
			global $arrVar;
			
			$txtWhere = '';
			$arrTemp = array();
			foreach($arrWhere as $txtKey => $arrValue)
			{
				if (strval($txtKey) == 'OR' || strval($txtKey) == 'AND')
				{
					$arrTemp2 = array();
					foreach($arrValue as $txtKey2 => $arrValue2)
					{
						if (is_numeric($txtKey2))
						{
							foreach ($arrValue2 as $txtKey3 => $arrValue3)
							{
								if (strval($txtKey3) == 'OR' || strval($txtKey3) == 'AND')
								{
									$arrTemp2[] = $this->funWhere(array($txtKey3 => $arrValue3));
								}
								else
								{
									list($txtTable3,$txtField3) = explode('.',$txtKey3);
									if (empty($txtField3))
									{
										$txtField3 = $txtTable3;
										$txtTable3 = '';
									}
									else
									{
										$arrTableInfo = $this->funTableInfo($txtTable3);
										$txtTable3 = $this->txtTablePrefix . $txtTable3;
									}
									switch ($arrValue3[0])
									{
										case 'IS NULL':
										case 'IS NOT NULL':
											$arrTemp2[] = (empty($txtTable3) ? '' : '`' . $txtTable3 . '`.') . '`' . $txtField3 . '` ' . $arrValue3[0];
											break;
										case 'LIKE':
											$arrTemp2[] = (isset($arrValue3[2]) && $arrValue3[2] == 'BINARY' ? 'BINARY ':'').(empty($txtTable3) ? '' : '`' . $txtTable3 . '`.') . '`' . $txtField3 . '` ' . $arrValue3[0] . ' "%' . $this->funEscape($arrValue3[1]) . '%"';
											break;
										case 'LIKE_S':
											$arrTemp2[] = (empty($txtTable3) ? '' : '`' . $txtTable3 . '`.') . '`' . $txtField3 . '` LIKE "' . $this->funEscape($arrValue3[1]) . '%"';
											break;
										// dws 09/06/13
										case 'MATCH':
											$arrTemp2[] = 'MATCH(' . $arrValue3[1][0] . ') AGAINST("' . $this->funEscape($arrValue3[1][1]) . '")';
											break;
										default:
											$arrTemp3 = $this->funFieldFormat(array($txtField3 => $arrValue3[1]),$arrTableInfo);
											$arrValue3[1] = $arrTemp3[$txtField3];
											$arrTemp2[] = (empty($txtTable3) ? '' : '`' . $txtTable3 . '`.') . '`' . $txtField3 . '` ' . $arrValue3[0] . ' ' . '"' . $this->funEscape($arrValue3[1]) . '"';
											break;
									}
								}
							}
						}
						else
						{
							list($txtTable2,$txtField2) = explode('.',$txtKey2);
							if (empty($txtField2))
							{
								$txtField2 = $txtTable2;
								$txtTable2 = '';
							}
							else
							{
								$arrTableInfo = $this->funTableInfo($txtTable2);
								$txtTable2 = $this->txtTablePrefix . $txtTable2;
							}
							switch ($arrValue2[0])
							{
								case 'IS NULL':
								case 'IS NOT NULL':
									$arrTemp2[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue2[0];
									break;
								case 'LIKE':
									$arrTemp2[] = (isset($arrValue2[2]) && $arrValue2[2] == 'BINARY' ? 'BINARY ':'').(empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue2[0] . ' "%' . $this->funEscape($arrValue2[1]) . '%"';
									break;
								case 'LIKE_S':
									$arrTemp2[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` LIKE "' . $this->funEscape($arrValue2[1]) . '%"';
									break;
								// dws 09/06/13
								case 'MATCH':
									$arrTemp2[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` MATCH(' . $arrValue2[1][0] . ') "' . $this->funEscape($arrValue2[1][1]) . '"';
									break;
								default:
									$arrTFF = $this->funFieldFormat(array($txtField2 => $arrValue2[1]),$arrTableInfo);
									$arrValue2[1] = $arrTFF[$txtField2];
									$arrTemp2[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue2[0] . ' ' . '"' . $this->funEscape($arrValue2[1]) . '"';
									break;
							}
						}
					}
					$arrTemp[] = '(' . implode(' ' . $txtKey . ' ',$arrTemp2) . ')';
				}
				else
				{
					list($txtTable2,$txtField2) = explode('.',$txtKey);
					if (empty($txtField2))
					{
						$txtField2 = $txtTable2;
						$txtTable2 = '';
					}
					else
					{
						$arrTableInfo = $this->funTableInfo($txtTable2);
						$txtTable2 = $this->txtTablePrefix . $txtTable2;
					}
					switch ($arrValue[0])
					{
						case 'IS NULL':
						case 'IS NOT NULL':
							$arrTemp[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue[0];
							break;
						case 'LIKE':
							$arrTemp[] = (isset($arrValue[3]) && $arrValue[3] == 'BINARY' ? 'BINARY ':'').(empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue[0] . ' "%' . $this->funEscape($arrValue[1]) . '%"';
							break;
						case 'LIKE_S':
							$arrTemp[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` LIKE "' . $this->funEscape($arrValue[1]) . '%"';
							break;
						default:
							$arrTFF = $this->funFieldFormat(array($txtField2 => $arrValue[1]),$arrTableInfo);
							$arrValue[1] = $arrTFF[$txtField2];
							$arrTemp[] = (empty($txtTable2) ? '' : '`' . $txtTable2 . '`.') . '`' . $txtField2 . '` ' . $arrValue[0] . ' ' . '"' . $this->funEscape($arrValue[1]) . '"';
							break;
					}
				}
			}
			$txtWhere = implode(' AND ',$arrTemp);
			
			return $txtWhere;
		}
		
		/**
		* Fetches the table info and then checks that the data we are wanting to insert is valid
		* 
		* @param string $txtTable The table to use
		* @param int $intID The ID of the row we want to update
		* @param array $arrData The data we want to check
		* @return boolean
		* @uses funTableInfo()
		* @uses clsErrorHandler::funDebug()
		*/
		function funCheckTableRequired($txtTable,$intID,&$arrData)
		{
			global $arrVar;
			
			// Fetch the table info
			if (!$arrInfo = $this->funTableInfo($txtTable))
				return false;
			
			// Unset the AutoID - it doesn't need any further checking
			foreach ($arrInfo as $txtField => $arrRow)
			{
				if ($arrRow['AutoID'] && !empty($intID))
				{
					unset($arrData[$txtField]);
					unset($arrInfo[$txtField]);
				}
			}
			
			// If we don't have an ID then we need to make sure that all the required fields are present
			if (empty($intID))
			{
				foreach ($arrInfo as $txtField => $arrRow)
				{
					if ($arrRow['Required'])
					{
						if (empty($arrData[$txtField]) && (strpos($arrRow['Type'],'int') !== false && $arrData['$txtField'] == '0'))
						{
							$arrVar['objErrorHandler']->funDebug('Fail required: ' . $txtField);
							return false;
						}
					}
				}
			}
			
			// Now lets check that we don't have any fields that we don't know about
			foreach ($arrData as $txtKey => $txtValue)
			{
				if (!isset($arrInfo[$txtKey]))
				{
					$arrVar['objErrorHandler']->funDebug('Fail not set: ' . $txtKey);
					$arrVar['objErrorHandler']->funDebug($arrInfo,2000);
					return false;
				}
			}
			
			// Now for the fields we don't have a value for set the field to the default
			if (empty($intID))
			{
				foreach ($arrInfo as $txtField => $arrRow)
				{
					if (!$arrRow['Required'])
					{
						if (empty($arrData[$txtField]) && strlen($arrData[$txtField]) < 1)
							$arrData[$txtField] = $arrRow['Default'];
					}
				}
			}
			else
			{
				foreach ($arrInfo as $txtField => $arrRow)
				{
					if (!$arrRow['Required'])
					{
						if (empty($arrData[$txtField]) && isset($arrData[$txtField]) && strlen($arrData[$txtField]) < 1)
							$arrData[$txtField] = $arrRow['Default'];
					}
				}
			}
			
			return true;
		}
		
		/**
		* Converts a database formatted date into time
		* 
		* @param string $txtDate The database formatted date to convert
		* @return int
		*/
		function funDbDateToTime($txtDate)
		{
			return strtotime($txtDate);
		}
		
		/**
		* Processes the joins and creates the table string
		* 
		* @param array $arrTable The array of joins
		* @param array $txtSep The seperator
		* @return string
		* @uses funSubJoins()
		*/
		function funJoins($arrTable,$txtSep = '')
		{
			$txtTable='';
			foreach ($arrTable as $arrRow)
			{
				if ($arrRow['String'] === false)
					continue;
				$txtTable .= $txtSep . $arrRow['String'];
				if (is_array($arrRow['Joins']))
				{
					foreach ($arrRow['Joins'] as $txtJoinTable => $txtJoin)
					{
						$txtTable .= $txtJoin;
						$txtTable .= $this->funSubJoins($arrTable,$txtJoinTable);
					}
				}
				$txtSep = ',';
			}
			return $txtTable;
		}
		
		/**
		* Processes the sub joins
		* 
		* @param string|array $arrTable The array of joins
		* @param string $txtJTable The join table
		* @return string
		*/
		function funSubJoins($arrTable,$txtJTable)
		{
			$txtTable = '';
			if (is_array($arrTable[$txtJTable]))
			{
				foreach ($arrTable[$txtJTable]['Joins'] as $txtJoinTable => $txtJoin)
				{
					if ($arrRow['String'] === false)
						continue;
					$txtTable .= $txtJoin;
					$txtTable .= $this->funSubJoins($arrTable,$txtJoinTable);
				}
			}
			return $txtTable;
		}
		
		/**
		* Locks the table(s)
		* 
		* @param string|array $arrTable The table(s) to lock
		* @param string $txtJTable The join table
		* @return string
		* @uses $txtTablePrefix
		* @uses funQuery()
		*/
		function funLock($arrTable)
		{
			$txtTable = '';
			$txtSep = '';
			if (is_array($arrTable))
			{
				foreach($arrTable as $arrRow)
				{
					if (is_array($arrRow))
					{
						list($txtTable1,$txtTable2) = each($arrRow);
						$txtTable .= $txtSep . '`' . $this->txtTablePrefix . $txtTable1 . '` AS `' . $this->txtTablePrefix . $txtTable1 . '` WRITE';
					}
					else
					{
						$txtTable .= $txtSep . '`' . $this->txtTablePrefix . $arrRow . '` WRITE';
					}
					$txtSep = ',';
				}
			}
			else
			{
				$txtTable = '`' . $this->txtTablePrefix . $arrTable . '` WRITE';
			}
			
			if (!$this->funQuery('LockTable','LOCK TABLES ' . $txtTable))
				return false;
			
			return true;
		}
		
		/**
		* Unlocks the table(s)
		* 
		* @return boolean
		* @uses funQuery()
		*/
		function funUnlock()
		{
			if (!$this->funQuery('UnlockTable','UNLOCK TABLES'))
				return false;
			
			return true;
		}
		
		/**
		* Truncates the table
		* 
		* @param string $txtTable The table to truncate
		* @return boolean
		* @uses funQuery()
		*/
		function funTruncate($txtTable)
		{
			if (!$this->funQuery('TruncateTable','TRUNCATE TABLE `' . $txtTable . '`'))
				return false;
			
			return true;
		}
		
		/**
		* Drops the table
		* 
		* @param string $txtTable The table to drop
		* @return boolean
		* @uses funQuery()
		* @uses $txtTablePrefix
		* @uses funFreeResult()
		*/
		function funDropTable($txtTable)
		{
			if (!$this->funQuery(__FUNCTION__,'DROP TABLE IF EXISTS `' . $this->txtTablePrefix . $txtTable . '`'))
				return false;
			$this->funFreeResult(__FUNCTION__);
			
			return true;
		}
		
		/**
		* Builds the CREATE TABLE sql either runs it or returns the SQL.
		* 
		* @param string $txtTable The name of the table to create
		* @param array $arrFields The fields that you want to create
		* @param array $arrKeys The keys to create
		* @param array $arrForeignKeys The foreign keys to create
		* @param boolean $blnTransactional Whether or not the table should be transactional
		* @param boolean $blnReplace Whether or not top drop any existing table first
		* @param int $intAutoincrementID The value that the autoincrement should start at
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return string|boolean
		* @uses funDropTable()
		* @uses $txtTablePrefix
		* @uses $arrKeyType
		* @uses $arrConstraintAction
		* @uses funQuery()
		* @uses funFreeResult()
		*/
		function funCreateTable($txtTable,$arrFields,$arrKeys=array(),$arrForeignKeys=array(),$blnTransactional=true,$blnReplace=false,$intAutoincrementID=0,$blnReturnSQL=false)
		{
			global $arrVar;
			
			// If we are are ask to replace the table then we first need to drop it
			if ($blnReplace)
				$this->funDropTable($txtTable);
			
			// Start the SQL
			$txtSQL = 'CREATE TABLE IF NOT EXISTS `' . $this->txtTablePrefix . $txtTable . '` (' . "\n";
			
			// Now loop through the fields and generate the SQL
			foreach ($arrFields as $intKey => $arrRow)
			{
				if ($intKey > 0)
					$txtSQL .= ",\t\n";
				$txtSQL .= '`' . $arrRow['Field'] . '` ';
				$txtSQL .= $arrRow['Type'];
				if (!empty($arrRow['Size']))
					$txtSQL .= '(' . $arrRow['Size'] . ')';
				if ($arrRow['AutoID'])
					$txtSQL .= ' NOT NULL AUTO_INCREMENT';
				else if (is_null($arrRow['Default']))
					$txtSQL .= ' DEFAULT NULL';
				else if (strlen($arrRow['Default']) > 0)
					$txtSQL .= ' DEFAULT \'' . $arrRow['Default'] . '\'';
				else
					$txtSQL .= ' NOT NULL';
				
			}
			
			// Now loop through the keys and create the SQL
			foreach ($arrKeys as $arrRow)
			{
				$txtSQL .= ",\t\n";
				$txtSQL .= $this->arrKeyType[$arrRow['Type']] . ' KEY `' . (empty($arrRow['Name']) ? implode('',$arrRow['Fields']) : $arrRow['Name']) .'` (';
				foreach ($arrRow['Fields'] as $intKey => $txtField)
					$txtSQL .= ($intKey > 0 ? ',' : '') . '`' . $txtField . '`';
				$txtSQL .= ')';
			}
			
			// Now loop through the foreign keys and create the SQL
			foreach ($arrForeignKeys as $arrRow)
			{
				list($txtTable,$txtField) = explode('.',$arrRow['FField']);
				$txtSQL .= ",\t\n";
				$txtSQL .= (empty($arrRow['Name']) ? '' : 'CONSTRAINT `' . $arrRow['Name'] . '` ');
				$txtSQL .= 'FOREIGN KEY (`' . $arrRow['Field'] . '`) ';
				$txtSQL .= 'REFERENCES `' . $this->txtTablePrefix . $txtTable . '` (`' . $txtField . '`) ';
				$txtSQL .= 'ON DELETE ' . $this->arrConstraintAction[$arrRow['OnDelete']] . ' ON UPDATE ' . $this->arrConstraintAction[$arrRow['OnUpdate']];
			}
			
			// Now add the table type, charset and autoincrement
			$txtSQL .= "\n" . ') ENGINE=' . ($blnTransactional ? 'InnoDB' : 'MyISAM') . ' CHARSET=utf8' . ($intAutoincrementID ? ' AUTO_INCREMENT=' . $intAutoincrementID : '') . ';';
			
			// If we have been asked to we'll just return the SQL
			if ($blnReturnSQL)
				return $txtSQL;
			
			// Now run the SQL
			if (!$this->funQuery(__FUNCTION__,$txtSQL))
				return false;
			$this->funFreeResult(__FUNCTION__);
			
			return true;
		}
		
		/**
		* Fetch the version information from coreVersion and the last update time (used for caching)
		* 
		* @return boolean
		* @uses funQuerySelect
		* @uses $arrVersion
		* @uses funDbDateToTime()
		* @uses $intVersionUpdated
		*/
		function funFetchVersion()
		{
			if (!$this->funQuerySelect(__FUNCTION__, 'coreVersion', array('coreVersion.Module','coreVersion.Version','coreVersion.xLastUpdate'), array(), array(), 0, 0, array('coreVersion.VersionID')))
				return false;
			$this->arrVersion = array();
			while ($arrRow = $this->funFetchRow(__FUNCTION__))
			{
				$this->arrVersion[$arrRow['Module']] = $arrRow['Version'];
				$intTime = $this->funDbDateToTime($arrRow['xLastUpdate']);
				if ($intTime > $this->intVersionUpdated)
					$this->intVersionUpdated = $intTime;
			}
			
			return true;
		}
		
		/**
		* Disable foreign key checks
		* 
		* @return boolean
		* @uses funQuery()
		* @uses funFreeResult()
		*/
		function funDisableFK()
		{
			if (!$this->funQuery(__FUNCTION__,'SET FOREIGN_KEY_CHECKS=0'))
				return false;
			$this->funFreeResult(__FUNCTION__);
			
			return true;
		}
		
		/**
		* Enable foreign key checks
		* 
		* @return boolean
		* @uses funQuery()
		* @uses funFreeResult()
		*/
		function funEnableFK()
		{
			if (!$this->funQuery(__FUNCTION__,'SET FOREIGN_KEY_CHECKS=1'))
				return false;
			$this->funFreeResult(__FUNCTION__);
			
			return true;
		}
		
		/**
		* Fetches all of the tables in the database
		* 
		* If we have a table prefix then only these tables will be returned
		* 
		* @param boolean $blnRefresh Whether or not we are going to fetch the tables afresh
		* @return boolean
		* @uses $arrTables
		* @uses clsDataManipulation::funFetchCacheFile()
		* @uses $intVersionUpdated
		* @uses $txtTablePrefix
		* @uses funEscape()
		* @uses funFreeResult()
		* @uses clsDataManipulation::funSaveFile()
		*/
		function funFetchTables($blnRefresh=false)
		{
			global $arrVar;
			
			// If we already have the list and aren't asked to refresh then we'll just return true
			if (count($this->arrTables) > 0 && !$blnRefresh)
				return true;
			
			// Check first if we have a cached version
			$txtCacheFile = $arrVar['txtSiteFileBaseCache'] . 'database' . DIRECTORY_SEPARATOR . 'tables.php';
			if (!$blnRefresh)
			{
				if ($txtCacheContent = $arrVar['objDataManipulation']->funFetchCacheFile($txtCacheFile))
				{
					$this->arrTables = unserialize($txtCacheContent);
					return true;
				}	
			}
			
			// Now Fetch the list of tables - we are only interested in our own tables, so filter by the table prefix if we have one
			if (!$this->funQuery(__FUNCTION__,'SHOW TABLES' . (empty($this->txtTablePrefix) ? '' : ' LIKE "' . $this->funEscape($this->txtTablePrefix) . '%"')))
				return false;
			// Reset the tables array
			$this->arrTables = array();
			// Now loop through all of the tables and add them
			while ($arrRow = $this->funFetchRow(__FUNCTION__,false))
				$this->arrTables[] = $arrRow[0];
			// Free the result
			$this->funFreeResult(__FUNCTION__);
			
			// Save the results
			$arrVar['objDataManipulation']->funSaveFile(serialize($this->arrTables),$txtCacheFile);
			
			return true;
		}
		
		/**
		* Deals with the conversion (if needed) of field types, i.e. Date / Date Time
		* 
		* @param array $arrFields The fields in a name / value pair format
		* @param array $arrTableInfo The table field details
		* @return array
		*/
		function funFieldFormat($arrFields,$arrTableInfo)
		{
			global $arrVar;
			
			foreach ($arrTableInfo as $txtField => $arrRow)
			{
				if (!isset($arrFields[$txtField]))
					continue;
				
				switch ($arrRow['Type'])
				{
					case 'date':
							// We will only convert the date if it is time (i.e. an integer)
							if (is_integer($arrFields[$txtField]))
								$arrFields[$txtField] = $this->funDateFormat($arrFields[$txtField]);
							else if (strpos($arrFields[$txtField],'-') === false)
								$arrFields[$txtField] = $this->funDateFormat($arrVar['objDataManipulation']->funStringToTime($arrFields[$txtField]));
						break;
					case 'datetime':
							// We will only convert the date if it is time (i.e. an integer)
							if (is_integer($arrFields[$txtField]))
								$arrFields[$txtField] = $this->funDateFormat($arrFields[$txtField],true);
							else if (strpos($arrFields[$txtField],'-') === false)
								$arrFields[$txtField] = $this->funDateFormat($arrVar['objDataManipulation']->funStringToTime($arrFields[$txtField]),true);
						break;
				}
			}
			
			return $arrFields;
		}
		
		/**
		* Builds the ALTER TABLE sql either runs it or returns the SQL.
		* 
		* @param string $txtTable The name of the table to alter
		* @param array $arrFields The fields that you want to alter
		* @param array $arrKeys The keys to alter
		* @param array $arrForeignKeys The foreign keys to alter
		* @param int $intAutoincrementID The value that the autoincrement should start at
		* @param boolean $blnReturnSQL Whether or not to just return the SQL it generates
		* @return string|boolean
		* @uses $txtTablePrefix
		* @uses $arrKeyType
		* @uses $arrConstraintAction
		* @uses funQuery()
		* @uses funFreeResult()
		*/
		function funAlterTable($txtTable,$arrFields,$arrKeys=array(),$arrForeignKeys=array(),$intAutoincrementID=-1,$blnReturnSQL=false)
		{
			global $arrVar;
			
			// The SQL statements to run
			$arrSQL = array();
			
			// Fetch the current table definition
			$arrTableInfo = $this->funTableInfo($txtTable);
			$arrAdvancedTableInfo = $this->funAdvancedTableInfo($txtTable);
			
			// Start the SQL
			$txtCommonSQL = 'ALTER TABLE `' . $this->txtTablePrefix . $txtTable . '` ';
			
			// Now loop through the fields and generate the SQL
			foreach ($arrFields as $intKey => $arrRow)
			{
				if (empty($arrTableInfo[$arrRow['Field']]))
				{
					$txtSQL = $txtCommonSQL . 'ADD COLUMN ';
					$txtSQL .= '`' . $arrRow['Field'] . '` ';
					$txtSQL .= $arrRow['Type'];
					if (!empty($arrRow['Size']))
						$txtSQL .= '(' . $arrRow['Size'] . ')';
					if ($arrRow['AutoID'])
						$txtSQL .= ' NOT NULL AUTO_INCREMENT';
					else if (is_null($arrRow['Default']))
						$txtSQL .= ' DEFAULT NULL';
					else if (strlen($arrRow['Default']) > 0)
						$txtSQL .= ' DEFAULT \'' . $arrRow['Default'] . '\'';
					else
						$txtSQL .= ' NOT NULL';
					if (!empty($arrRow['After']))
						$txtSQL .= ' AFTER `' . $arrRow['After'] . '`';
					
					$arrSQL[] = $txtSQL;
				}
				else if ($arrRow['Drop'])
				{
					$arrSQL[] = $txtCommonSQL . 'DROP COLUMN `' . $arrRow['Field'] . '`';
				}
				else
				{
					$txtSQL = $txtCommonSQL;
					if (!empty($arrRow['NewField']))
					{
						$txtSQL .= 'CHANGE COLUMN `' . $arrRow['Field'] . '` `' . $arrRow['NewField'] . '` ';
						foreach ($arrAdvancedTableInfo['keys'] as $arrRow2)
						{
							foreach ($arrRow2['fields'] as $txtField)
							{
								if ($txtField == $arrRow['Field'])
								{
									if ($arrRow2['type'] == 'primary')
										$arrSQL[] = $txtCommonSQL . 'DROP PRIMARY KEY';
									else
										$arrSQL[] = $txtCommonSQL . 'DROP KEY ' . $arrRow2['name'];
									break;
								}
							}
						}
						
						foreach ($arrAdvancedTableInfo['fkeys'] as $arrRow2)
						{
							if ($arrRow2['field'] == $arrRow['Field'])
								$arrSQL[] = $txtCommonSQL . 'DROP FOREIGN KEY ' . $arrRow2['name'];
						}
					}
					else
					{
						$txtSQL .= 'MODIFY COLUMN `' . $arrRow['Field'] . '` ';
					}
					$txtSQL .= $arrRow['Type'];
					if (!empty($arrRow['Size']))
						$txtSQL .= '(' . $arrRow['Size'] . ')';
					if ($arrRow['AutoID'])
						$txtSQL .= ' NOT NULL AUTO_INCREMENT';
					else if (is_null($arrRow['Default']))
						$txtSQL .= ' DEFAULT NULL';
					else if (strlen($arrRow['Default']) > 0)
						$txtSQL .= ' DEFAULT \'' . $arrRow['Default'] . '\'';
					else
						$txtSQL .= ' NOT NULL';
					
					$arrSQL[] = $txtSQL;
				}
			}
			
			// Now loop through the keys and create the SQL
			foreach ($arrKeys as $arrRow)
			{
				$txtName = (empty($arrRow['Name']) ? implode('',$arrRow['Fields']) : $arrRow['Name']);
				if ($arrRow['Drop'])
				{
					foreach ($arrAdvancedTableInfo['keys'] as $arrRow2)
					{
						if ($arrRow2['name'] == $txtName)
						{
							if ($arrRow2['type'] == 'primary')
								$arrSQL[] = $txtCommonSQL . 'DROP PRIMARY KEY';
							else
								$arrSQL[] = $txtCommonSQL . 'DROP KEY ' . $arrRow2['name'];
						}
					}
				}
				else
				{
					foreach ($arrAdvancedTableInfo['keys'] as $arrRow2)
					{
						if ($arrRow2['name'] == $txtName)
						{
							if ($arrRow2['type'] == 'primary')
								$arrSQL[] = $txtCommonSQL . 'DROP PRIMARY KEY';
							else
								$arrSQL[] = $txtCommonSQL . 'DROP KEY ' . $arrRow2['name'];
							break;
						}
					}
					
					$txtSQL = $txtCommonSQL . 'ADD ';
					$txtSQL .= $this->arrKeyType[$arrRow['Type']] . ' KEY `' . (empty($arrRow['Name']) ? implode('',$arrRow['Fields']) : $arrRow['Name']) .'` (';
					foreach ($arrRow['Fields'] as $intKey => $txtField)
						$txtSQL .= ($intKey > 0 ? ',' : '') . '`' . $txtField . '`';
					$txtSQL .= ')';
					
					$arrSQL[] = $txtSQL;
				}
			}
			
			// Now loop through the foreign keys and create the SQL
			foreach ($arrForeignKeys as $arrRow)
			{
				if ($arrRow['Drop'])
				{
					$arrSQL[] = $txtCommonSQL . 'DROP FOREIGN KEY `' . $arrRow['Name'] . '`';
					continue;
				}
				
				foreach ($arrAdvancedTableInfo['fkeys'] as $arrRow2)
				{
					if ($arrRow2['name'] == $arrRow['Name'])
					{
						$arrSQL[] = $txtCommonSQL . 'DROP FOREIGN KEY `' . $arrRow2['name'] . '`';
						continue 2;
					}
				}
				
				list($txtTable,$txtField) = explode('.',$arrRow['FField']);
				$txtSQL = $txtCommonSQL . 'ADD ';
				$txtSQL .= (empty($arrRow['Name']) ? '' : 'CONSTRAINT `' . $arrRow['Name'] . '` ');
				$txtSQL .= 'FOREIGN KEY (`' . $arrRow['Field'] . '`) ';
				$txtSQL .= 'REFERENCES `' . $this->txtTablePrefix . $txtTable . '` (`' . $txtField . '`) ';
				$txtSQL .= 'ON DELETE ' . $this->arrConstraintAction[$arrRow['OnDelete']] . ' ON UPDATE ' . $this->arrConstraintAction[$arrRow['OnUpdate']];
				
				$arrSQL[] = $txtSQL;
			}
			
			// If we have been asked to we'll just return the SQL
			if ($blnReturnSQL)
				return $arrSQL;
			
			// Now run the SQL
			foreach ($arrSQL as $txtSQL)
			{
				if (!$this->funQuery(__FUNCTION__,$txtSQL))
					return false;
			}
			$this->funFreeResult(__FUNCTION__);
			
			return true;
		}
	}
?>