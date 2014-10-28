<?php
	/**
	* Data Manipulation Class
	*
	* This class is used to manipulate data, as well as provide other functionality such as file handling.
	*
	* @version 2.0
	* @package core
	*/
	class clsDataManipulation
	{
		/**
		* @var double
		* @see funTimer()
		*/
		var $dblTimerStart = 0;
		/**
		* @var double
		* @see funTimer()
		*/
		var $dblTimer = 0;

		/**
		* Imports all of the variables into a working array, as well as cleaning the data
		*
		* The array returned from this function contains the following keys:
		*
		* - data - Contains the variables passed via GET, POST and the extended URL.
		* - button - Any variables with the prefix 'btn' are classed as buttons.
		* - cookie - Any cookie data.
		* - file - Any files that have been uploaded.
		* - raw_data - The data as it was before being processed, in case it is needed.
		* - source - Where the data came from.
		*
		* @param string $txtOrder The order in which the sources should be processed (E = Extended URL information, G = GET, P = POST, C = Cookie, F = File)
		* @param boolean $blnAllowHTML Default false. Whether or not to allow HTML to be passed through. Prevents things like people trying to pass script tags
		* @param string $txtExtendedSep A delimited list (/) which tells the parser what seperator is used for extended URLs
		* @return array
		* @uses funStripSlashes()
		*/
		function funImportHttpVariables($txtOrder='EGPCF',$blnAllowHTML=false,$txtExtendedSep='=/__')
		{
			if (empty($txtOrder))
				return false;
			$arrOrder = preg_split('//',(strtoupper($txtOrder)),-1,PREG_SPLIT_NO_EMPTY);

			$arrWorking = array();
			$arrSource = array();
			$arrButton = array();
			$arrFile = array();
			$arrRaw = array();
			$arrCookie = array();

			foreach ($arrOrder as $txtType)
			{
				switch ($txtType)
				{
					case 'E':
						$arrSep = explode('/',$txtExtendedSep);
						$txtPathInfo = $_SERVER['PATH_INFO'];
						if (empty($txtPathInfo))
							continue;

						foreach($arrSep as $txtSep)
							$txtPathInfo = str_replace($txtSep,'@~Sep~@',$txtPathInfo);

						$arrPathInfo = explode('/',$txtPathInfo);
						foreach ($arrPathInfo as $txtValue)
						{
							$arrData = explode('@~Sep~@',$txtPathInfo);
							if (substr($arrData[0],0,3) == 'btn')
							{
								if ($blnAllowHTML)
									$arrButton[trim($arrData[0])] = trim($this->funStripSlashes(urldecode($arrData[1])));
								else
									$arrButton[trim(strip_tags($arrData[0]))] = trim($this->funStripSlashes(strip_tags(urldecode($arrData[1]))));
								$arrRaw['EGP'][$arrData[0]] = $arrData[1];
								$arrSource[trim(strip_tags($arrData[0]))] = 'E';
							}
							else
							{
								if ($blnAllowHTML)
									$arrWorking[trim($arrData[0])] = trim($this->funStripSlashes(urldecode($arrData[1])));
								else
									$arrWorking[trim(strip_tags($arrData[0]))] = trim($this->funStripSlashes(strip_tags(urldecode($arrData[1]))));
								$arrRaw['EGP'][$arrData[0]] = $arrData[1];
								$arrSource[trim(strip_tags($arrData[0]))] = 'E';
							}
						}
						break;
					case 'G':
						foreach ($_GET as $txtKey => $txtValue)
						{
							if (substr($txtKey,0,3) == 'btn')
							{
								if ($blnAllowHTML)
									$arrButton[trim($txtKey)] = trim($this->funStripSlashes(urldecode($txtValue)));
								else
									$arrButton[trim(strip_tags($txtKey))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue))));
								$arrRaw['EGP'][trim($txtKey)] = trim($txtValue);
								$arrSource[trim(strip_tags($txtKey))] = 'G';
							}
							else
							{
								if (is_array($txtValue))
								{
									foreach($txtValue as $txtKey2 => $txtValue2)
									{
										if ($blnAllowHTML)
											$arrWorking[trim($txtKey)][trim($txtKey2)] = trim($this->funStripSlashes(urldecode($txtValue2)));
										else
											$arrWorking[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue2))));
										$arrRaw['EGP'][trim($txtKey)][trim($txtKey2)] = trim($txtValue2);
										$arrSource[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))] = 'G';
									}
								}
								else
								{
									if ($blnAllowHTML)
										$arrWorking[trim($txtKey)] = trim($this->funStripSlashes(urldecode($txtValue)));
									else
										$arrWorking[trim(strip_tags($txtKey))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue))));
									$arrRaw['EGP'][trim($txtKey)] = trim($txtValue);
									$arrSource[trim(strip_tags($txtKey))] = 'G';
								}
							}
						}
						break;
					case 'P':
						foreach ($_POST as $txtKey => $txtValue)
						{
							if (substr($txtKey,0,3) == 'btn')
							{
								if ($blnAllowHTML)
									$arrButton[trim($txtKey)] = trim($this->funStripSlashes(urldecode($txtValue)));
								else
									$arrButton[trim(strip_tags($txtKey))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue))));
								$arrRaw['EGP'][trim($txtKey)] = trim($txtValue);
								$arrSource[trim(strip_tags($txtKey))] = 'P';
							}
							else
							{
								if (is_array($txtValue))
								{
									foreach($txtValue as $txtKey2 => $txtValue2)
									{
                                        if(is_array($txtValue2)){
                                            foreach($txtValue2 as $txtKey3 => $txtValue3){
                                                if ($blnAllowHTML)
                                                    $arrWorking[trim($txtKey)][trim($txtKey2)][trim($txtKey3)] = trim($this->funStripSlashes(urldecode($txtValue3)));
                                                else
                                                    $arrWorking[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))][trim(strip_tags($txtKey3))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue3))));
                                                $arrRaw['EGP'][trim($txtKey)][trim($txtKey2)][trim($txtKey3)] = trim($txtValue3);
                                                $arrSource[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))][trim(strip_tags($txtKey3))] = 'P';
                                            }
                                        }else{
                                            if ($blnAllowHTML)
                                                $arrWorking[trim($txtKey)][trim($txtKey2)] = trim($this->funStripSlashes(urldecode($txtValue2)));
                                            else
                                                $arrWorking[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue2))));
                                            $arrRaw['EGP'][trim($txtKey)][trim($txtKey2)] = trim($txtValue2);
                                            $arrSource[trim(strip_tags($txtKey))][trim(strip_tags($txtKey2))] = 'P';
                                        }
									}
								}
								else
								{
									if ($blnAllowHTML)
										$arrWorking[trim($txtKey)] = trim($this->funStripSlashes(urldecode($txtValue)));
									else
										$arrWorking[trim(strip_tags($txtKey))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue))));
									$arrRaw['EGP'][trim($txtKey)] = trim($txtValue);
									$arrSource[trim(strip_tags($txtKey))] = 'P';
								}
							}
						}
						break;
					case 'C':
						foreach ($_COOKIE as $txtKey => $txtValue)
						{
							if ($blnAllowHTML)
								$arrCookie[trim($txtKey)] = trim($this->funStripSlashes(urldecode($txtValue)));
							else
								$arrCookie[trim(strip_tags($txtKey))] = trim($this->funStripSlashes(strip_tags(urldecode($txtValue))));
							$arrRaw['C'][trim($txtKey)] = trim($txtValue);
							$arrSource[trim(strip_tags($txtKey))] = 'C';
						}
						break;
					case 'F':
						$arrFile = $_FILES;
						break;
					default:
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - Called with an unknown type (' . $txtType . ')',E_USER_NOTICE);
				}
			}

			// Handle image buttons
			foreach ($arrButton as $txtKey => $txtValue)
			{
				if (substr($txtKey,-2) == '_x')
				{
					unset($arrButton[$txtKey]);
					$arrButton[substr($txtKey,0,-2)] = $txtValue;
				}
				elseif (substr($txtKey,-2) == '_y')
				{
					unset($arrButton[$txtKey]);
				}
			}

			return array(
							'data'		=>	$arrWorking,
							'button'	=>	$arrButton,
							'cookie'	=>	$arrCookie,
							'file'		=>	$arrFile,
							'raw_data'	=>	$arrRaw,
							'source'	=>	$arrSource
						);
		}

		/**
		* Searches through the text and replaces the tags with items from $arrReplacement. If a replacement is not found in the array it removes the tag.
		*
		* Returns an array with the following items:
		*
		* - Data - The text with all the replacements made
		* - UnmatchedTags - An array of the tags that were not found (largely for debugging)
		*
		* The tags are in the following format:
		* <code>#!--Key--#</code>
		* where "Key" is the name of the key in the replacement array
		*
		* @param string $txtData The text to search for replacements in.
		* @param array $arrReplacement An associative array of replacements
		* @return array
		*/
		function funReplaceTags($txtData,$arrReplacement)
		{
			$blnUnmatchedTags = false;
			preg_match_all('/\#\!\-\-([A-z0-9\_\-]+)\-\-\#/m',$txtData,$arrMatches,PREG_SET_ORDER);

			foreach($arrMatches as $arrRow)
			{
				if (isset($arrReplacement[$arrRow[1]]))
				{
					$txtData = str_replace($arrRow[0],$arrReplacement[$arrRow[1]],$txtData);
				}
				else
				{
					$txtData = str_replace($arrRow[0],'',$txtData);
					$blnUnmatchedTags = true;
				}
			}
			return array(
							'Data'			=>	$txtData,
							'UnmatchedTags'	=> $blnUnmatchedTags
						);
		}

		/**
		* Checks to see if the text contains any tags
		* @param string $txtData The text to search
		* @return boolean
		* @see funReplaceTags()
		*/
		function funContainsTags($txtData)
		{
			if (preg_match('/\#\!\-\-([A-z0-9\_\-\=\(\)]+)\-\-\#/m',$txtData))
				return true;
			else
				return false;
		}

		/**
		* Replaces tags in the string passed.
		*
		* <code>
		* // Examples of types of tag types - "Key" is the key in the array
		*
		* // Conditional - if the conditional key exists then the contents will be shown
		* #!--CONDITIONAL="Key"--#
		* Conditional stuff here
		* #!--/CONDITIONAL--#
		*
		* // Merge - replaces the tag with the value in the replacement array
		* #!--MERGE="Key"--#
		*
		* // Merge Date - Replaces a mysql formatted datetime with the formatted date
		* #!--MERGEDATE="Key"--#
		*
		* // Merge price - Replaces a number with a number formatted to 2 decimal places
		* #!--MERGEPRICE="Key"--#
		*
		* // Repeat - if the key is an array of replacement arrays then these will be repeated as many times as there are arrays
		* #!--REPEAT="Key"--#
		* Repeating stuff here
		* #!--/REPEAT--#
		*
		* // System - unlike the others this doesn't need a key in the array, the key specifies what it does
		* #!--SYSTEM="DATE"--# // The system date
		* #!--SYSTEM="TIME"--# // The system time
		* #!--SYSTEM="DATETIME"--# // The system date and time
		* </code>
		*
		* Returns an array with the following items:
		*
		* - Data - The text with all the replacements made
		* - UnmatchedTags - An array of the tags that were not found (largely for debugging)
		*
		* @param string $txtData The text to search for replacements in.
		* @param array $arrReplacement An associative array of replacements
		* @return array
		*/
		function funReplaceTagType($txtData,$arrReplacement)
		{
			global $arrVar;

			$blnUnmatchedTags = false;

			$intCount = 0;
			while (preg_match('/\#\!\-\-([A-z0-9\_\-\=\"\~]+)\-\-\#/m',$txtData,$arrMatches))
			{
				$intCount++;
				if ($intCount > 100)
					break;
				list($txtType,$txtKey) = explode('=',$arrMatches[1]);
				list(,$txtKey) = explode('"',$txtKey);
				list($txtType,$txtParameters) = explode('(',$txtType);
				list($txtParameters) = explode(')',$txtParameters);
				if (trim($txtParameters) == '')
				{
					$arrParameters = '';
				}
				else
				{
					$arrParameters = explode('~',$txtParameters);
				}

				if ($txtType == "")	// We should never have a tag type that has a null value
					return false;

				switch (strtoupper($txtType))
				{
					case 'CONDITIONAL':
							if (!preg_match('/\#\!\-\-' . addslashes($arrMatches[1]) . '\-\-\#(.*)\#\!\-\-\/' . addslashes($arrMatches[1]) . '\-\-\#/Ums',$txtData,$arrInnerMatches))
								return false;
							// Now lets get the whole contents of the tag
							if (isset($arrReplacement[$txtKey]))
							{
								if (is_array($arrReplacement[$txtKey]))
								{
									if (count($arrReplacement[$txtKey]) < 1)
									{
										$txtData = str_replace($arrInnerMatches[0],'',$txtData);
									}
									else
									{
										if (!$txtInnerData = $this->funReplaceTagType($arrInnerMatches[1],$arrReplacement))
											return false;
										$txtData = str_replace($arrInnerMatches[0],$txtInnerData['Data'],$txtData);
									}
								}
								else
								{
									if (!$txtInnerData = $this->funReplaceTagType($arrInnerMatches[1],$arrReplacement))
										return false;
									$txtData = str_replace($arrInnerMatches[0],$txtInnerData['Data'],$txtData);
								}
							}
							else
							{
								$txtData = str_replace($arrInnerMatches[0],'',$txtData);
							}
						break;
					case 'MERGE':
							if (array_key_exists($txtKey,$arrReplacement))
							{
								$intPos = strpos($txtData,$arrMatches[0]);
								$txtData = substr_replace($txtData,$arrReplacement[$txtKey],$intPos,strlen($arrMatches[0]));
							}
							else
							{
								$intPos = strpos($txtData,$arrMatches[0]);
								$txtData = substr_replace($txtData,'',$intPos,strlen($arrMatches[0]));
								$blnUnmatchedTags = true;
							}
						break;
					case 'MERGEDATE':
							if (array_key_exists($txtKey,$arrReplacement) && !empty($arrReplacement[$txtKey]))
							{
								$txtData = str_replace($arrMatches[0],date('d/m/Y H:i',$arrVar['objDb']->funDbDateToTime($arrReplacement[$txtKey])),$txtData);
							}
							else
							{
								$txtData = str_replace($arrMatches[0],'',$txtData);
								$blnUnmatchedTags = true;
							}
						break;
					case 'MERGEPRICE':
							if (array_key_exists($txtKey,$arrReplacement))
							{
								$txtData = str_replace($arrMatches[0],number_format($arrReplacement[$txtKey],2),$txtData);
							}
							else
							{
								$txtData = str_replace($arrMatches[0],'',$txtData);
								$blnUnmatchedTags = true;
							}
						break;
					case 'REPEAT':
							if (!is_array($arrReplacement[$txtKey]))
							{
								return false;
							}

							// Now lets get the whole contents of the tag
							if (!preg_match('/\#\!\-\-' . addslashes($arrMatches[1]) . '\-\-\#(.*)\#\!\-\-\/' . addslashes($arrMatches[1]) . '\-\-\#/Ums',$txtData,$arrInnerMatches))
								return false;
							$txtInnerData = '';
							foreach($arrReplacement[$txtKey] as $arrInnerReplacement)
							{
								if (!$txtTemp = $this->funReplaceTagType($arrInnerMatches[1],$arrInnerReplacement))
									return false;
								$txtInnerData .= $txtTemp['Data'];
							}
							$txtData = str_replace($arrInnerMatches[0],$txtInnerData,$txtData);
						break;
					case 'SYSTEM':
							switch (strtoupper($txtKey))
							{
								case 'DATE':
									$txtNewData = date('jS F Y');
									break;
								case 'TIME':
									$txtNewData = date('H:i');
									break;
								case 'DATETIME':
									$txtNewData = date('jS F Y H:i');
									break;
							}
						break;
				}
			}

			return array(
							'Data'			=>	$txtData,
							'UnmatchedTags'	=> $blnUnmatchedTags
						);
		}

		/**
		* Converts a string to time
		*
		* @param string $txtDate The date or date and time to convert
		* @return string
		*/
		function funStringToTime($txtDate)
		{
			// mktime only goes as far back as 1970 - do not use
			if (!preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,4}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/',$txtDate,$arrMatch))
			{
				if (!preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,4}) ([0-9]{1,2}):([0-9]{1,2})/',$txtDate,$arrMatch))
				{
					if (!preg_match('/([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,4})/',$txtDate,$arrMatch))
						return false;
				}
			}

			$txtDate = $arrMatch[2] . '/' . $arrMatch[1] . '/' . $arrMatch[3] . ' ' . (empty($arrMatch[4]) ? 0 : $arrMatch[4]) . ':' . (empty($arrMatch[5]) ? 0 : $arrMatch[5]) . ':' . (empty($arrMatch[6]) ? 0 : $arrMatch[6]);

			return strtotime($txtDate);
		}

		/**
		* Smart stripping of slashes.
		*
		* This function will only strip slashes if magic quotes is enabled. Used for POST / GET data.
		*
		* @param string $txtData The value to strip slashes from
		* @return string
		*/
		function funStripSlashes($txtData)
		{
			if (get_magic_quotes_gpc())
				$txtData = stripslashes($txtData);

			return $txtData;
		}

		/**
		* Converts a string so that it is safe to be used as a variable
		*
		* @param string $txtText
		* @return string
		*/
		function funVarSafe($txtText)
		{
			$txtText = preg_replace('/\s/','',$txtText);
			$txtText = str_replace('-','_',$txtText);

			return $txtText;
		}

		/**
		* Generates a random password of the length specified
		*
		* @param int $intLength The length of the password to generate
		* @return string
		*/
		function funGeneratePassword($intLength=8)
		{
			$arrChars = array(0,1,2,3,4,5,6,7,8,9);
			for ($i=65;$i<=90;$i++)
			{
				$arrChars[] = chr($i);
				$arrChars[] = strtolower(chr($i));
			}

			shuffle($arrChars);

			$txtPassword = '';
			while (strlen($txtPassword) < $intLength)
				$txtPassword .= $arrChars[rand(0,count($arrChars))];

			return $txtPassword;
		}

		/**
		* Coverts HTML into formatted text
		*
		* This function does not handle things like tables, other than just stripping out the tags
		*
		* @param string $txtData The text to convert
		* @return string
		* @uses funListToText()
		*/
		function funHtmlToText($txtData)
		{
			// First lets make sure that we actually have some html in our text
			$txtTemp = strip_tags($txtData);
			if ($txtTemp == $txtData)
				return $txtData;

			// First lets convert any special html characters back to normal
			$txtData = html_entity_decode($txtData,ENT_QUOTES,'UTF-8');

			// Replace all tabs
			$txtData = str_replace("\t",'',$txtData);

			// Replace all line breaks
			$txtData = str_replace("\r\n",'',$txtData);
			$txtData = str_replace("\r",'',$txtData);
			$txtData = str_replace("\n",'',$txtData);

			// Add line breaks where they are expected
			$txtData = str_replace('</p>',"</p>\n\n",$txtData);
			$txtData = str_replace('<br>',"<br />\n",$txtData);
			$txtData = str_replace('<br/>',"<br />\n",$txtData);
			$txtData = str_replace('<br />',"<br />\n",$txtData);
			for ($i=1;$i<7;$i++)
				$txtData = str_replace('</h' . $i . '>',"</h' . $i . '>\n",$txtData);

			// Convert lists
			$txtData = $this->funListToText($txtData);

			// Remove all of the tags
			$txtData = strip_tags($txtData);

			// Finally lets trim the result to get rid of any trailing line breaks
			$txtData = trim($txtData,chr(0xC2).chr(0xA0));

			return $txtData;
		}

		/**
		* Converts HTML lists to text
		*
		* @param string $txtData The text to convert
		* @return string
		* @see funHtmlToText()
		*/
		function funListToText($txtData,$intLevel=1)
		{
			global $arrVar;

			while (true)
			{
				$txtList = '';
				$intTemp1 = strpos($txtData,'<ol>');
				$intTemp2 = strpos($txtData,'<ul>');
				if ($intTemp1 === false && $intTemp2 === false)
				{
					break;
				}
				else if (($intTemp1 < $intTemp2 && $intTemp1 !== false) || ($intTemp2 === false))
				{
					$txtType = 'ol';
					$txtType2 = 'ul';
				}
				else
				{
					$txtType = 'ul';
					$txtType2 = 'ol';
				}

				$intStart = strpos($txtData,'<' . $txtType . '>');
				$intTemp = $intStart;
				while(true)
				{
					$intEnd = strpos($txtData,'</' . $txtType . '>',$intTemp);
					$intTemp = strpos($txtData,'<' . $txtType . '>',$intTemp+4);
					if ($intTemp === false)
						break;

					if ($intTemp > $intEnd)
						break;
				}

				$intTemp1 = strpos($txtData,'<ol>',$intStart+4);
				$intTemp2 = strpos($txtData,'<ul>',$intStart+4);
				if (($intTemp1 !== false && $intTemp1 < $intEnd) || ($intTemp2 !== false && $intTemp2 < $intEnd))
				{
					$txtData = substr_replace($txtData,$this->funListToText(substr($txtData,$intStart+4,$intEnd-($intStart+4)),$intLevel+1),$intStart+4,$intEnd-($intStart+4));
					continue;
				}

				$intCount = 1;
				$intStart2 = $intStart;
				while (($intStart2 = strpos($txtData,'<li>',$intStart2)) !== false)
				{
					if ($intStart2 > $intEnd)
						break;

					$intEnd2 = strpos($txtData,'</li>',$intStart2);
					if ($txtType == 'ol')
					{
						$txtList .= $this->funTabs($intLevel) . $intCount . '. ' . substr($txtData,$intStart2+4,$intEnd2-($intStart2+4)) . "\n";
						$intCount++;
					}
					else
					{
						$txtList .= $this->funTabs($intLevel) . '* ' . substr($txtData,$intStart2+4,$intEnd2-($intStart2+4)) . "\n";
					}
					$intStart2 += 4;
				}
				$txtData = substr_replace($txtData,($intLevel == 1 ? "\n\n" : "\n") . $txtList,$intStart,($intEnd+5)-$intStart);
			}

			$txtData = str_replace("\n\n\n","\n\n",$txtData);

			return $txtData;
		}

		/**
		* Returns the requested number of tabs
		*
		* @param int $intCount
		* @return string
		*/
		function funTabs($intCount)
		{
			$txtTabs = '';
			for($i=1;$i<$intCount;$i++)
				$txtTabs .= "\t";

			return $txtTabs;
		}

		/**
		* Calculates the number of pages to show, number of pages etc and returns an array
		*
		* The following items are returned:
		*
		* - Current - The current page
		* - Previous - The previous page number, or false is there isn't a previous page
		* - Next - The next page number, or false is there isn't a next page
		* - Pages - The total number of pages
		* - ShowPages - An array of page numbers to show
		* - Items - The total number of items
		* - Limit - The number of items per page
		* - Start - The item number to start from
		* - Finish - The item number to finish on
		*
		* @param int $intLimit The number of items per page
		* @param int $intItems The total number of items
		* @param int $intPage The current page number
		* @param int $intShowPages The number of pages to show
		* @return array
		*/
		function funPaging($intLimit,$intItems,$intPage=1,$intShowPages=5)
		{
			$intPages = ceil($intItems / $intLimit);

			$intStart = 1;
			$intHalfPages = floor($intShowPages / 2);

			if ($intPages <= $intShowPages)
			{
				$intStart = 1;
			}
			else if ($intPage+$intHalfPages > $intPages)
			{
				$intStart = ($intPages-$intShowPages)+1;
				if ($intStart < 1)
					$intStart = 1;
			}
			else
			{
				$intStart = $intPage-$intHalfPages;
				if ($intStart < 1)
					$intStart = 1;
			}

			$arrPaging = array(
								'Current'	=>	$intPage,
								'Previous'	=>	($intPage == 1 ? false : $intPage-1),
								'Next'		=>	(($intPage+1) > $intPages ? false : $intPage+1),
								'Pages'		=>	$intPages,
								'ShowPages'	=>	array(),
								'Items'		=> $intItems,
								'Limit'		=> $intLimit,
								'Start'		=> (($intPage-1)*$intLimit)+1,
								'Finish'	=> ((($intPage-1)*$intLimit)+$intLimit > $intItems ? $intItems : (($intPage-1)*$intLimit)+$intLimit)
							);

			for ($i=$intStart;(count($arrPaging['ShowPages']) < $intPages && count($arrPaging['ShowPages']) < $intShowPages);$i++)
				$arrPaging['ShowPages'][] = $i;

			return $arrPaging;
		}

		/**
		* Attempts to handle the uploaded file, as well as creating any missing directories
		*
		* On sucess the following array items are returned:
		*
		* - Directory - The directory the file has been uploaded to
		* - Filename - The name of the file
		* - Fullname - The full path and name of the file
		* - OrignalFilename - The original name of the file
		* - MimeType - The mimetype of the file
		*
		* @param string $txtFileReference The name of the field used to upload the file
		* @param string $txtTargetDirectory The directory that the file should be moved to - it must be under the site directory
		* @param string $txtFilename The new filename
		* @return array|boolean
		*/
		function funFileUpload($txtFileReference,$txtTargetDirectory,$txtFilename)
		{
			global $arrVar;

			if (empty($txtTargetDirectory) || empty($txtFilename))
				return false;

			if (strpos($txtTargetDirectory,$arrVar['txtSiteFileBase']) !== 0)
				return false;

			if (empty($_FILES[$txtFileReference]['name']))
				return false;

			$txtDirectory = str_replace($arrVar['txtSiteFileBase'],'',$txtTargetDirectory);
			$arrDirectory = explode(DIRECTORY_SEPARATOR,$txtDirectory);
			$txtDirectory = substr($arrVar['txtSiteFileBase'],0,-1);

			foreach ($arrDirectory as $txtDir)
			{
				$txtDirectory .= DIRECTORY_SEPARATOR . $txtDir;
				if (!is_dir($txtDirectory))
				{
					if (!mkdir($txtDirectory))
						return false;
				}
			}

			if ($_FILES[$txtFileReference]['error'] !== 0)
				return false;

			if (!move_uploaded_file($_FILES[$txtFileReference]['tmp_name'],$txtDirectory . DIRECTORY_SEPARATOR . $txtFilename))
				return false;

			return array(
							'Directory'		=>	$txtDirectory,
							'Filename'		=>	$txtFilename,
							'Fullname'		=>	$txtDirectory . DIRECTORY_SEPARATOR . $txtFilename,
							'OrignalFilename'	=>	$_FILES[$txtFileReference]['name'],
							'MimeType'		=>	$_FILES[$txtFileReference]['type']
					);
		}

		/**
		* Forces the browser to download the file rather than open it within the browser
		*
		* You need to make sure to call funSetCleanExit before this function, as if the download
		* takes too long PHP will timeout and we will get eroneous error messages
		*
		* @param string $txtFile The full path of the file to download
		* @param string $txtDownloadName The name the file should be downloaded as (if different)
		* @param string $txtMimeType The mime type of the file. If left empty the system will try to work it out
		* @return boolean
		*/
		function funForceFileDownload($txtFile,$txtDownloadName='',$txtMimeType='')
		{
			if (!file_exists($txtFile))
				return false;

			if (empty($txtDownloadName))
				$txtDownloadName = basename($txtFile);

			if (empty($txtMimeType))
			{
				if (function_exists(finfo_open))
				{
					$pntFinfo = finfo_open(FILEINFO_MIME);
					$txtMimeType = finfo_file($pntFinfo,$txtFile);
					finfo_close($pntFinfo);
				}
				else
				{
					$txtMimeType = mime_content_type($txtFile);
				}
			}

			header('Pragma: public');   // required
			header('Expires: 0');       // no cache
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private',false);
			header('Content-Type: ' . $txtMimeType);
			header('Content-Disposition: attachment; filename="' . $txtDownloadName . '"');
			header('Content-Transfer-Encoding: binary');
			header('Content-Length: ' . filesize($txtFile));    // provide file size
			readfile($txtFile);

			return true;
		}

		/**
		* Checks the text for HTML
		*
		* @param string $txtData The text to check
		* @return boolean
		*/
		function funContainsHtml($txtData)
		{
			if (strip_tags($txtData) == $txtData)
				return false;
			else
				return true;
		}

		/**
		* Builds a link using the parameters passed to it
		*
		* @param string $txtPage The start of the URL
		* @param array $arrValues An associative array of key / value pairs
		* @param string $txtSep The text to use as a seperator
		* @return string
		*/
		function funBuildURL($txtPage,$arrValues=array(),$txtSep='&')
		{
			if (!is_array($arrValues) || empty($txtSep))
				return false;

			$txtURL = $txtPage;

			if (count($arrValues) > 0)
			{
				if (strpos($txtURL,'?') === false)
				{
					$txtURL .= '?';
					$blnAddSep = false;
				}
				else
				{
					$blnAddSep = true;
				}

				foreach ($arrValues as $txtKey => $txtValue)
				{
					$txtURL .= ($blnAddSep ? $txtSep : '') . $txtKey . '=' . urlencode($txtValue);
					$blnAddSep = true;
				}
			}

			return $txtURL;
		}

		/**
		* Takes the string and returns the first x characters.
		*
		* If the text contains HTML that is first removed
		*
		* @param string $txtString The string to process
		* @param int $intLength The length of the string that should be returned
		* @param string $txtAppend This is only added onto the end of the string if was greater than $intLength
		* @param boolean $blnTrim If set to true it will return partial words, otherwise it only returns whole words
		* @return string
		* @uses funHtmlToText()
		*/
		function funPartialString($txtString,$intLength=255,$txtAppend='...',$blnTrim=false)
		{
			$txtString = $this->funHtmlToText($txtString);

			$arrTemp = explode("~@~",wordwrap($txtString,$intLength,'~@~',$blnTrim));
			return $arrTemp[0] . (empty($arrTemp[1]) ? '' : $txtAppend);
		}

		/**
		* Uses the time passed to it to work out their age
		*
		* @param int $intTime Their date of birth as time
		* @return int Their age
		*/
		function funAge($intTime)
		{
			$age = 0;
	        while( time() > $intTime = strtotime('+1 year', $intTime))
                ++$age;
	        return $age;
		}

		/**
		* Removes the contents of a folder, including all sub directories.
		*
		* @param string $txtFolder The full path to the folder
		* @param $blnDeleteFolder Whether or not to also delete the folder itself
		* @return boolean
		*/
		function funRecursiveDelete($txtFolder,$blnDeleteFolder=true)
		{
			if (!is_dir($txtFolder))
				return false;

			if ($pntDir = opendir($txtFolder))
			{
				while (($txtFile = readdir($pntDir)) !== false)
				{
					if ($txtFile == '.' || $txtFile == '..')
						continue;

					if (is_dir($txtFolder . DIRECTORY_SEPARATOR . $txtFile))
					{
						$this->funRecursiveDelete($txtFolder . DIRECTORY_SEPARATOR . $txtFile,true);
					}
					else
					{
						unlink($txtFolder . DIRECTORY_SEPARATOR . $txtFile);
					}
				}
				closedir($pntDir);
				if ($blnDeleteFolder)
					rmdir($txtFolder);
			}
			else
			{
				return false;
			}

			return true;
		}

		/**
		* Checks the date of the cache file.
		*
		* If the $intCacheTime has been set then it checks if the file is older,
		* otherwise it checks if it is older than the max cache time.
		* It returns the contents of the file. Otherwise it returns false.
		*
		* @param string $txtFile The full path to the file
		* @param int $intCache The length of time the file should be cached for
		* @param int $intCacheTime The last time that the file was cached
		* @return string|boolean
		* @see funFetchFile()
		*/
		function funFetchCacheFile($txtFile,$intCache=3600,$intCacheTime=0)
		{
			if (file_exists($txtFile))
			{
				$intFileModified = filemtime($txtFile);
				if (empty($intCacheTime) && (time() - $intFileModified) <= $intCache)
					return $this->funFetchFile($txtFile);
				else if (!empty($intCacheTime) && $intFileModified >= $intCacheTime)
					return $this->funFetchFile($txtFile);
			}

			return false;
		}

		/**
		* Reads a file from the filesystem or a url - allows for when remote file opening is turned off
		*
		* If the file is a remote file then the file can be cached locally
		*
		* @param string $txtFile The full path of the file to fetch
		* @param string $txtSaveFile The full path of the file to save locally as the cache file (remote only)
		* @param int $intCache The length of time the file should be cached for
		* @return string|boolean Returns the contents of the file on success
		* @uses funFetchCacheFile()
		* @uses funSaveFile()
		*/
		function funFetchFile($txtFile,$txtSaveFile='',$intCache=3600)
		{
			if (!empty($txtSaveFile))
			{
				if ($txtContent = $this->funFetchCacheFile($txtSaveFile,$intCache))
					return $txtContent;
			}

			if (strpos($txtFile,'://') !== false)
			{
				$intTest = ini_get('allow_url_fopen');
				if (empty($intTest))
				{
					$pntCurl = curl_init();
					curl_setopt($pntCurl, CURLOPT_URL, $txtFile);
					curl_setopt($pntCurl, CURLOPT_HEADER, 0);
					curl_setopt($pntCurl, CURLOPT_RETURNTRANSFER, 1);
					if (!$txtFileContent = curl_exec($pntCurl))
						return false;
					curl_close($pntCurl);
				}
				else
				{
					if (!$pntFp = fopen($txtFile,'r'))
						return false;
					$txtFileContent = '';
					while ($txtContent = fread($pntFp,4096))
						$txtFileContent .= $txtContent;
					fclose($pntFp);
				}

				if (!empty($txtSaveFile))
				{
					if (!$this->funSaveFile($txtFileContent,$txtSaveFile))
						return false;
				}
			}
			else
			{
				if (!$pntFp = fopen($txtFile,'r'))
					return false;
				$txtFileContent = '';
				while ($txtContent = fread($pntFp,4096))
					$txtFileContent .= $txtContent;
				fclose($pntFp);
			}

			return $txtFileContent;
		}

		/**
		* Fetches the contents of the directory and returns the contents in an array
		*
		* The array returned contains the following items:
		*
		* - directories - a list of sub directories
		* - files - a list of files
		*
		* @param string $txtDir The full path to the directory
		* @param boolean $blnIgnoreHtaccess Whether or not to ignore files that begin with a dot "."
		* @return array|boolean
		*/
		function funFetchDirectoryContents($txtDir,$blnIgnoreHtaccess=true)
		{
			global $arrVar;

			if (!is_dir($txtDir))
				return false;

			$arrDirectory = array('files' => array(), 'directories' => array());

			if (!$pntDir = opendir($txtDir))
				return false;
			while (($txtFile = readdir($pntDir)) !== false)
			{
				if ($txtFile == '.' || $txtFile == '..' || (substr($txtFile,0,1) == '.' && $blnIgnoreHtaccess))
					continue;

				if (is_dir($txtDir . DIRECTORY_SEPARATOR . $txtFile))
					$arrDirectory['directories'][] = $txtFile;
				else
					$arrDirectory['files'][] = $txtFile;
			}
			closedir($pntDir);

			return $arrDirectory;
		}

		/**
		* Takes an array of values and creates a tag cloud
		*
		* The format of the array $arrCloud is:
		* <code>$arrCloud = array('Tag Name' => array('count' => 999, 'url' => 'xxxx'));</code>
		*
		* @param array $arrCloud The array of tags
		* @param int $intMinFont The minimum font size (in pixels) or the minimum value to append to the class name
		* @param int $intMaxFont The maximum font size (in pixels) or the maximum value to append to the class name
		* @param boolean $blnClass If set to true then instead of setting the font size it sets the classname to "cloud" with the number appended to it
		* @return array
		*/
		function funTagCloud($arrCloud,$intMinFont=10,$intMaxFont=25,$blnClass=false)
		{
			global $arrVar;

			$intMin = 999;
			$intMax = 0;

			$txtTagCloud = '';

			foreach ($arrCloud as $arrRow)
			{
				if ($arrRow['count'] < $intMin)
					$intMin = $arrRow['count'];
				if ($arrRow['count'] > $intMax)
					$intMax = $arrRow['count'];
			}
			foreach ($arrCloud as $txtTag => $arrRow)
			{
				$intSize = round(($intMaxFont * $arrRow['count']) / ($intMax - $intMin));
				if ($intSize < $intMinFont)
					$intSize = $intMinFont;
				else if ($intSize > $intMaxFont)
					$intSize = $intMaxFont;

				if ($blnClass)
					$txtTagCloud .= '<a href="' . $arrRow['url'] . '" class="cloud' . $intSize . '" title="' . htmlspecialchars($txtTag) . '"' . (empty($arrRow['onclick']) ? '' : ' onclick="' . $arrRow['onclick'] . '"') . '>' . htmlspecialchars($txtTag) . '</a> ';
				else
					$txtTagCloud .= '<a href="' . $arrRow['url'] . '" style="font-size:' . $intSize . 'px" title="' . htmlspecialchars($txtTag) . '"' . (empty($arrRow['onclick']) ? '' : ' onclick="' . $arrRow['onclick'] . '"') . '>' . htmlspecialchars($txtTag) . '</a> ';
			}

			return trim($txtTagCloud);
		}

		/**
		* Converts text so that it is safe to use in a URL
		*
		* @param string $txtText The text to convert
		* @param boolean $blnIncludeForwardSlash Whether or not to also convert forward slashes "/"
		* @param boolean $blnStripDoubleDashes Whether or not to convert all double dashes to single dashes (risks duplication)
		* @return array
		*/
		function funEUrlSafe($txtText,$blnIncludeForwardSlash=true,$blnStripDoubleDashes=false)
		{
			$txtText = trim($txtText);

			$arrFind = array(
								'^',
								'[',
								']',
								'\\',
								'`'
							);
			if ($blnIncludeForwardSlash)
				$arrFind[] = '/';

			$txtText = strtolower($txtText);
			$txtText = str_replace($arrFind,'-',$txtText);
			$txtText = preg_replace('/[^A-z0-9-\/]/','-',$txtText);
			if ($blnStripDoubleDashes)
				$txtText = preg_replace('/[--]+/','-',$txtText);

			if (substr($txtText,0,1) == '-')
				$txtText = substr($txtText,1);
			if (substr($txtText,-1) == '-')
				$txtText = substr($txtText,0,-1);

			return $txtText;
		}

		/**
		* Attempts to use the built-in function, but if not present it uses fallback code
		*
		* @param mixed $mxdValue The value to encode
		* @return string
		* @uses funJsonAddSlashes()
		*/
		function funJsonEncode($mxdValue)
		{
			if (function_exists('json_encode'))
				return json_encode($mxdValue);

			$arrJson = array();

			if (is_array($mxdValue))
			{
				if ($this->funIsAssoc($mxdValue))
				{
					foreach ($mxdValue as $txtKey => $txtValue)
					{
						if (is_array($txtValue))
						{
							$arrJson[] = '"' . $this->funJsonAddSlashes($txtKey) . '":' . $this->funJsonEncode($txtValue);
						}
						else
						{
							$arrJson[] = '"' . $this->funJsonAddSlashes($txtKey) . '":' . (is_numeric($txtValue) ? $txtValue : '"' . $this->funJsonAddSlashes($txtValue) . '"');
						}
					}
				}
				else
				{
					$arrTemp = array();
					foreach ($mxdValue as $txtKey => $txtValue)
					{
						if (is_array($txtValue))
						{
							$arrTemp[] = $this->funJsonEncode($txtValue);
						}
						else
						{
							$arrTemp[] = (is_numeric($txtValue) ? $txtValue : '"' . $this->funJsonAddSlashes($txtValue) . '"');
						}
					}
					$arrJson[] = '[' . implode(',',$arrTemp) . ']';
				}
			}
			else
			{
				$arrJson[] = (is_numeric($txtValue) ? $txtValue : '"' . $this->funJsonAddSlashes($txtValue) . '"');
			}

			$txtJson = implode(',',$arrJson);
			if (substr($txtJson,0,1) != '[')
				$txtJson = '{' . $txtJson . '}';

			return $txtJson;
		}

		/**
		* Attempts to use the built-in function, but if not present it uses fallback code
		*
		* @param mixed $mxdValue The value to encode
		* @return array
		*/
		function funJsonDecode($txtJson)
		{
			if (function_exists('json_decode'))
				return json_decode($txtJson,true);

			//NR - 30/8/13
			//This function doesn't work for me on my local dev box running 5.1.6
			//but it does work on live. Not sure why.
			//so when i'm working with the LRS I run 5.2 locally (which has json_decode support built in)

			global $arrVar;

			$txtEval = '';

			$arrTemp = str_split($txtJson);
			$blnSkipNext=false;
			$blnInString=false;
			foreach ($arrTemp as $txtChar)
			{
				if ($blnSkipNext)
				{
					$txtEval .= $txtChar;
					$blnSkipNext = false;
					continue;
				}

				switch ($txtChar)
				{
					case '[':
					case '{':
						$txtEval .= ($blnInString ? $txtChar : 'array(');
						break;
					case ']':
					case '}':
						$txtEval .= ($blnInString ? $txtChar : ')');
						break;
					case ':':
						$txtEval .= ($blnInString ? $txtChar : ' => ');
						//$txtEval .= ($blnInString ? 'isTRUEis' : 'isFALSEis');
						break;
					case '\\':
						$blnSkipNext = true;
						$txtEval .= $txtChar;
						break;
					default:
						if ($txtChar == '"')
						{
							$blnInString = ($blnInString ? false : true);
							$txtEval .= ($blnInString ? 'stripslashes("' : '")');
						}
						else
						{
							$txtEval .= $txtChar;
						}
				}
			}

			$txtEval = str_replace(array('\u003c','\u003e'),array('<','>'),$txtEval);

			eval("\$arrReturn = $txtEval;");

			return $arrReturn;
		}

		/**
		* Checks whether or not an array is an associative array
		*
		* @param array $arr The array to check
		* @return boolean
		*/
		function funIsAssoc($arr)
		{
    		return (is_array($arr) && (count($arr)==0 || 0 !== count(array_diff_key($arr, array_keys(array_keys($arr))))));
    	}

    	/**
    	* Adds slashes to json data
    	*
    	* @param string $txtValue The value to add slashes to
    	* @return string
    	* @see funJsonEncode()
    	*/
    	function funJsonAddSlashes($txtValue)
    	{
    		$txtValue = str_replace('\\','\\\\',$txtValue);
    		$txtValue = str_replace('"','\\"',$txtValue);
    		$txtValue = str_replace('/','\\/',$txtValue);
    		$txtValue = str_replace("\r",'\\r',$txtValue);
    		$txtValue = str_replace("\n",'\\n',$txtValue);
    		$txtValue = str_replace("\t",'\\t',$txtValue);

    		return $txtValue;
    	}

    	/**
    	* Takes an associative array and returns the information in a tabular format
    	*
    	* The array it returns has the following:
    	*
    	* - text - The text version
    	* - html - The HTML version
    	*
    	* @param array $arrData The array of data
    	* @return array
    	*/
    	function funKVPFormat($arrData)
    	{
    		$txtHTML = '<table border="0" cellpadding="2" cellspacing="2">';
    		$txtText = '';

    		foreach ($arrData as $txtKey => $txtValue)
    		{
    			if (is_array($txtValue))
    				continue;

    			$txtKey = str_replace('-',' ',$txtKey);

    			$txtHTML .= '<tr><th align="right">' . htmlspecialchars($txtKey) . '</th><td>' . htmlspecialchars($txtValue) . '</td></tr>';
    			$txtText .= sprintf("%30s: %s\n",$txtKey,$txtValue);
    		}

    		$txtHTML .= '</table>';

    		return array('text' => $txtText, 'html' => $txtHTML);
    	}

    	/**
    	* Attempts to use the built-in function to find the temporary directory, if not found it uses fallback code
    	*
    	* @return string|null
    	*/
    	function funSystemTempDir()
    	{
    		if (function_exists('sys_get_temp_dir'))
    			return sys_get_temp_dir();

    		if (!empty($_ENV['TMP']))
				return realpath($_ENV['TMP']);
			if (!empty($_ENV['TMPDIR']))
				return realpath( $_ENV['TMPDIR']);
			if (!empty($_ENV['TEMP']))
				return realpath( $_ENV['TEMP']);

			$tempfile=tempnam(__FILE__,'');
			if (file_exists($tempfile))
			{
				unlink($tempfile);
				return realpath(dirname($tempfile));
			}

			return null;
    	}

    	/**
    	* Returns the time including microseconds
    	*
    	* @param boolean $blnFloat Whether or not to return the result as a float
    	* @return mixed
    	* @see funTimer()
    	*/
    	function funMicrotime($blnFloat=true)
		{
			if ($blnFloat)
			{
		    	list($usec, $sec) = explode(' ', microtime());
		    	return ((float)$usec + (float)$sec);
		    }

		    return microtime();
		}

		/**
		* Starts and stops the timer and returns the result
		*
		* @param boolean $blnStart Whether we are starting the timer
		* @param boolean $blnShowTime Whether or not to show the time
		* @return string
		* @uses funMicrotime()
		* @uses $dblTimerStart
		* @uses $dblTimer
		*/
		function funTimer($blnStart=false,$blnShowTime=true)
		{
			if ($blnStart)
			{
				$this->dblTimerStart = $this->funMicrotime();
				$this->dblTimer = $this->dblTimerStart;
			}

			$dblTimer = $this->funMicrotime();

			$dblTimeTaken = $dblTimer - $this->dblTimer;
			$dblTotalTimeTaken = $dblTimer - $this->dblTimerStart;

			$this->dblTimer = $dblTimer;

			return number_format($dblTimeTaken,4) . 's (' . number_format($dblTotalTimeTaken,4) . 's)' . ($blnShowTime ? ' ' . date('H:i:s') : '');
		}

		/**
		* Checks for the existance of a directory and if not found creates it (including any directories leading to it)
		*
		* @param string $txtTargetDirectory The full path to the directory (needs to be within the site directory)
		* @return boolean
		*/
		function funCheckCreateDirectory($txtTargetDirectory, $strChmod = 0755)
		{
			global $arrVar;

			if (empty($arrVar['txtSiteFileBase']) || empty($txtTargetDirectory))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 1');
				return false;
			}

			if (strpos($txtTargetDirectory,$arrVar['txtSiteFileBase']) !== 0)
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 2');
				return false;
			}

			$txtDirectory = str_replace($arrVar['txtSiteFileBase'],'',$txtTargetDirectory);
			$arrDirectory = explode(DIRECTORY_SEPARATOR,$txtDirectory);
			$txtDirectory = substr($arrVar['txtSiteFileBase'],0,-1);

			foreach ($arrDirectory as $txtDir)
			{
				$txtDirectory .= DIRECTORY_SEPARATOR . $txtDir;
				if (!is_dir($txtDirectory))
				{
					if (!mkdir($txtDirectory, $strChmod))
						return false;
				}
			}

			return true;
		}

		/**
		* Uses the browscap file and the user agent to identify the browser
		*
		* An example of the array returned:
		* <code>
		* array
		* (
		*     ['PropertyName'] => 'Mozilla/5.0 (*Windows NT 6.1*)*Gecko/*Firefox/16.*',
		*     ['AgentID'] => 21029,
		*     ['MasterParent'] => false,
		*     ['LiteMode'] => true,
		*     ['Parent'] => 'Firefox 16.0',
		*     ['Comments'] => 'Firefox 16.0',
		*     ['Browser'] => 'Firefox',
		*     ['Version'] => 16.0,
		*     ['MajorVer'] => 16,
		*     ['MinorVer'] => 0,
		*     ['Platform'] => 'Win7',
		*     ['Platform_Version'] => 6.1,
		*     ['Platform_Description'] => 'Windows 7',
		*     ['Alpha'] => false,
		*     ['Beta'] => true,
		*     ['Win16'] => false,
		*     ['Win32'] => true,
		*     ['Win64'] => false,
		*     ['Frames'] => true,
		*     ['IFrames'] => true,
		*     ['Tables'] => true,
		*     ['Cookies'] => true,
		*     ['BackgroundSounds'] => false,
		*     ['JavaScript'] => true,
		*     ['VBScript'] => false,
		*     ['JavaApplets'] => true,
		*     ['ActiveXControls'] => false,
		*     ['isMobileDevice'] => false,
		*     ['isSyndicationReader'] => false,
		*     ['Crawler'] => false,
		*     ['CssVersion'] => 0,
		*     ['AolVersion'] => 0,
		*     ['Device_Name'] => 'PC',
		*     ['Device_Maker'] => 'Various',
		*     ['RenderingEngine_Name'] => 'Gecko',
		*     ['RenderingEngine_Version'] => 0,
		*     ['RenderingEngine_Description'] => 'For Firefox, Camino, K-Meleon, SeaMonkey, Netscape, and other Gecko-based browsers.'
		* )
		* </code>
		*
		* @return array
		* @link http://browsers.garykeith.com/stream.asp?BrowsCapXML The lastest version can be downloaded here
		*/
		function funBrowserDetection()
		{
			global $arrVar;

			$txtFile = $arrVar['txtCoreFileBase'] . 'browscap.xml';
			$txtBrowserCacheFile = $arrVar['txtSiteFileBaseCache'] . 'browser-detection' . DIRECTORY_SEPARATOR . md5($_SERVER['HTTP_USER_AGENT']) . '.cache';

			$this->funCheckCreateDirectory($arrVar['txtSiteFileBaseCache'] . 'browser-detection');

			if (file_exists($txtBrowserCacheFile))
			{
				return unserialize(file_get_contents($txtBrowserCacheFile));
			}
			else
			{
				$arrAgent = $arrVar['objSite']->funFetchAgents();
				if (file_exists($txtFile))
				{
					$objXML = new DomDocument();
					$objXML->load($txtFile);
					$arrXML=array();
					foreach ($objXML->childNodes as $objNode)
					{
						$arrXML[$objNode->nodeName] = array();
						foreach ($objNode->childNodes as $objNode2)
						{
							if ($objNode2->nodeName === '#text' || $objNode2->nodeName === '#cdata-section')
							{
								if (strlen(trim($objNode2->nodeValue)) < 1)
									continue;
							}

							if ($objNode2->hasChildNodes() && $objNode2->nodeName != 'comments')
							{
								$arrXML[$objNode->nodeName][$objNode2->nodeName] = array();
								$txtParent = '';
								$intPattern = 0;
								foreach ($objNode2->childNodes as $objNode3)
								{
									if ($objNode3->nodeName === '#text' || $objNode3->nodeName === '#cdata-section')
									{
										if (strlen(trim($objNode3->nodeValue)) < 1)
											continue;
									}

									if ($objNode3->nodeName == 'item')
									{
										$arrXML[$objNode->nodeName][$objNode2->nodeName][$objNode3->getAttribute('name')] = $objNode3->getAttribute('value');
									}
									else
									{
										foreach ($objNode3->childNodes as $objNode4)
										{
											if ($objNode4->nodeName === '#text' || $objNode4->nodeName === '#cdata-section')
											{
												if (strlen(trim($objNode4->nodeValue)) < 1)
													continue;
											}

											if ($objNode4->nodeName == 'item')
												$arrXML[$objNode->nodeName][$objNode2->nodeName][$objNode3->nodeName][$objNode3->getAttribute('name')][$objNode4->getAttribute('name')] = $objNode4->getAttribute('value');
										}
									}
								}
							}
							else
							{
								$arrXML[$objNode->nodeName][$objNode2->nodeName] = trim($objNode2->nodeValue);
							}
						}
					}

				 	$arrDefaults = array();
					$arrBrowser = array();
					foreach ($arrXML['browsercaps']['browsercapitems']['browscapitem'] as $txtName => $arrRow)
					{
						if ($txtName == 'DefaultProperties')
						{
							$arrDefaults = $arrRow;
							unset($arrDefaults['Parent']);

			 				$arrVar['objSite']->funAddEditAgent($arrDefaults,(isset($arrAgent[$arrDefaults['AgentID']]) ? $arrDefaults['AgentID'] : 0));
						}
						else if ($txtName == '*')
						{
							// This is an unknown browser
							$arrBrowser = $arrRow;
							foreach ($arrRow as $txtKey => $txtValue)
			 				{
			 					if ($txtValue == 'default' || strlen($txtValue) < 1)
			 						continue;

			 					$arrBrowser[$txtKey] = $txtValue;
			 				}
			 				unset($arrBrowser['Parent']);

			 				$arrVar['objSite']->funAddEditAgent($arrBrowser,(isset($arrAgent[$arrBrowser['AgentID']]) ? $arrBrowser['AgentID'] : 0));
						}
						else
						{
							//if ($arrRow['MasterParent'] == 'true')
							//	continue;

							$arrBrowser = $arrDefaults;
				 			if (!empty($arrRow['Parent']))
				 			{
				 				foreach ($arrXML['browsercaps']['browsercapitems']['browscapitem'][$arrRow['Parent']] as $txtKey => $txtValue)
				 				{
				 					if ($txtKey == 'AgentID')
				 						$arrBrowser['ParentID'] = $txtValue;

				 					if ($txtValue == 'default' || strlen($txtValue) < 1)
				 						continue;

				 					$arrBrowser[$txtKey] = $txtValue;
				 				}
				 			}
			 				foreach ($arrRow as $txtKey => $txtValue)
			 				{
			 					if ($txtValue == 'default' || strlen($txtValue) < 1)
			 						continue;

			 					$arrBrowser[$txtKey] = $txtValue;
			 				}

			 				unset($arrBrowser['Parent']);
			 				$arrVar['objSite']->funAddEditAgent($arrBrowser,(isset($arrAgent[$arrBrowser['AgentID']]) ? $arrBrowser['AgentID'] : 0));
						}
					}

					rename($txtFile,$txtFile . '.imported');
					unset($arrXML);

					$arrAgent = $arrVar['objSite']->funFetchAgents();
				}

				$arrBrowser = array();
				foreach ($arrAgent as $arrRow)
				{
					if ($txtName == '*')
					{
						// This is an unknown browser
						$arrBrowser = $arrRow;
					}
					else
					{
						if ($arrRow['MasterParent'] == 'true')
							continue;

						$txtPattern = preg_quote($arrRow['PropertyName'],'/');
					 	$txtPattern = '/^' . str_replace(array('\*','\?'),array('.*','.'),$txtPattern) . '$/i';
				 		if (preg_match($txtPattern, $_SERVER['HTTP_USER_AGENT'], $arrMatches))
				 		{
				 			$arrBrowser = $arrVar['objSite']->funFetchAgent($arrRow['AgentID']);
				 			break;
				 		}
					}
				}
			}

			$pntFile = fopen($txtBrowserCacheFile,'w+');
		 	fwrite($pntFile,serialize($arrBrowser));
		 	fclose($pntFile);

			return $arrBrowser;
		}

		/**
		* Converts a number to a social number (i.e. 999, 1k, 1.2k, 2M)
		*
		* @param int $intValue The number to convert
		* @return string
		*/
		function funSocialNumber($intValue)
		{
			global $arrVar;

			if ($intValue < 1000)
			{
				return $intValue;
			}
			else if ($intValue < 1000000)
			{
				return str_replace('.0','',number_format($intValue/1000,1,'.','')) . 'k';
			}
			else
			{
				return str_replace('.0','',number_format($intValue/1000000,1,'.','')) . 'M';
			}
		}

		/**
		* Saves the file and creates the directory if required
		*
		* Line 2
		*
		* @param string $txtContent The data to write to the file
		* @param string $txtSaveFile The full path to the file to save. Must be within the site directory
		* @param boolean $blnAppend Whether or not to append to the file
		* @return boolean
		*/
		function funSaveFile($txtContent,$txtSaveFile,$blnAppend=false)
		{
			global $arrVar;

			if (strpos($txtSaveFile,$arrVar['txtSiteFileBase']) !== 0)
				return false;

			$arrTemp = explode(DIRECTORY_SEPARATOR,$txtSaveFile);
			array_pop($arrTemp);
			if (!$this->funCheckCreateDirectory(implode(DIRECTORY_SEPARATOR,$arrTemp)))
				return false;

			if (!$pntFp = fopen($txtSaveFile,($blnAppend ? 'a+' : 'w+')))
				return false;
			fwrite($pntFp,$txtContent);
			fclose($pntFp);

			return true;
		}

		/**
		* Parses a CSV string
		*
		* @param string $txtCSV The CSV formatted string
		* @param string $txtDelimeter The string delimiter
		* @param string $txtEnclosure The enclosure to use
		* @param string $txtEscape The escape character
		* @return array
		*/
		function funParseCSV($txtCSV,$txtDelimiter=',',$txtEnclosure='"',$txtEscape='\\')
		{
			if (function_exists('str_getcsv'))
				return str_getcsv($txtCSV,$txtDelimiter,$txtEnclosure,$txtEscape);

			$arrReturn = array();

			$arrTemp = str_split($txtCSV);
			$blnSkipNext=false;
			$blnInString=false;
			$txtWord = '';
			foreach ($arrTemp as $txtChar)
			{
				if ($blnSkipNext)
				{
					$txtWord .= $txtChar;
					$blnSkipNext = false;
					continue;
				}

				if ($txtChar == $txtDelimiter)
				{
					if ($blnInString)
					{
						$txtWord .= $txtChar;
					}
					else
					{
						$arrReturn[] = trim($txtWord);
						$txtWord = '';
					}
				}
				else if ($txtChar == $txtEnclosure)
				{
					$blnInString = ($blnInString ? false : true);
				}
				else if ($txtChar == $txtEscape)
				{
					$blnSkipNext = true;
				}
				else
				{
					$txtWord .= $txtChar;
				}

			}

			if (strlen($txtWord) > 0)
				$arrReturn[] = trim($txtWord);

			return $arrReturn;
		}

		/**
		* Clears the cache
		*
		* @param string $txtDir The directory to clear
		* @return boolean
		*/
		function funClearCache($txtDir='')
		{
			global $arrVar;

			if (empty($txtDir))
				$txtDir = $arrVar['txtSiteFileBaseCache'];

			if (strpos($txtDir,$arrVar['txtSiteFileBaseCache']) !== 0)
				return false;

			if (substr($txtDir,-1) == DIRECTORY_SEPARATOR)
				$txtDir = substr($txtDir,0,-1);

			if (!$pntDir = opendir($txtDir))
				return false;

			$intCount = 0;
			while (($txtFile = readdir($pntDir)) !== false)
			{
				if ($txtFile == '.' || $txtFile == '..')
					continue;

				$intCount++;

				if (is_dir($txtDir . DIRECTORY_SEPARATOR . $txtFile))
				{
					$this->funClearCache($txtDir . DIRECTORY_SEPARATOR . $txtFile);
					rmdir($txtDir . DIRECTORY_SEPARATOR . $txtFile);
				}
				else
				{
					unlink($txtDir . DIRECTORY_SEPARATOR . $txtFile);
				}
			}

			closedir($pntDir);

			return true;
		}

		function funDateRange($intStart,$intFinish,$txtDay='jS',$txtMonth='M',$txtYear='Y')
		{
			$txtDate = '';

			if (empty($intStart) || empty($intFinish))
				return $txtDate;

			$txtFormat = $txtYear;
			if (date($txtFormat,$intStart) == date($txtFormat,$intFinish))
			{
				$txtFormat = $txtMonth . ' ' . $txtYear;
				if (date($txtFormat,$intStart) == date($txtFormat,$intFinish))
				{
					$txtFormat = $txtDay . ' ' . $txtMonth . ' ' . $txtYear;
					if (date($txtFormat,$intStart) == date($txtFormat,$intFinish))
					{
						$txtDate = date($txtDay . ' ' . $txtMonth . ' ' . $txtYear,$intStart);
					}
					else
					{
						$txtFormat1 = $txtDay;
						$txtFormat2 = $txtDay . ' ' . $txtMonth . ' ' . $txtYear;
						$txtDate = date($txtFormat1,$intStart) . ' - ' . date($txtFormat2,$intFinish);
					}
				}
				else
				{
					$txtFormat1 = $txtDay . ' ' . $txtMonth;
					$txtFormat2 = $txtDay . ' ' . $txtMonth . ' ' . $txtYear;
					$txtDate = date($txtFormat1,$intStart) . ' - ' . date($txtFormat2,$intFinish);
				}
			}
			else
			{
				$txtFormat = $txtDay . ' ' . $txtMonth . ' ' . $txtYear;
				$txtDate = date($txtFormat,$intStart) . ' - ' . date($txtFormat,$intFinish);
			}

			return $txtDate;
		}

		function funCalendar($intMonth=NULL,$intYear=NULL,$intStartDay=1,$blnWrap=true)
		{
			global $arrVar;

			$arrCalendar = array();

			$arrColLookup = array();
			$intTemp = $intStartDay;
			for ($i=0;$i<7;$i++)
			{
				$arrColLookup[$intTemp] = $i;
				$intTemp++;
				if ($intTemp > 6)
					$intTemp = 0;
			}

			$intDays = date('t',mktime(0,0,0,$intMonth,1,$intYear));
			$intDay = date('w',mktime(0,0,0,$intMonth,1,$intYear));

			$intRow = 0;
			$intCol = $arrColLookup[$intDay];

			for($i=1;$i<=$intDays;$i++)
			{
				$arrCalendar[$intRow][$intCol] = mktime(0,0,0,$intMonth,$i,$intYear);
				$intCol++;
				if ($intCol > 6)
				{
					$intRow++;
					$intCol=0;
					if ($intRow > 4 && $blnWrap)
						$intRow = 0;
				}
			}

			foreach ($arrCalendar as $intRow => $arrRow)
			{
				for ($i=0;$i<7;$i++)
				{
					if (!isset($arrCalendar[$intRow][$i]))
						$arrCalendar[$intRow][$i] = '';
				}
				ksort($arrCalendar[$intRow]);
			}

			$arrVar['objErrorHandler']->funDebug($arrCalendar);

			return $arrCalendar;
		}

		function funFetchTweets()
		{
			global $arrVar;

			$arrOAuth = array(
				'oauth_consumer_key' => $arrVar['ini']['Config']['Twitter']['oauth_consumer_key'],
				'oauth_consumer_secret' => $arrVar['ini']['Config']['Twitter']['oauth_consumer_secret'],
				'oauth_token' => $arrVar['ini']['Config']['Twitter']['oauth_token'],
				'oauth_token_secret' => $arrVar['ini']['Config']['Twitter']['oauth_token_secret']
			);

			foreach ($arrOAuth as $txtValue)
			{
				if (empty($txtValue))
					return false;
			}

            //use the default user ID for the Twitter Feed.
            $intUserID = $arrVar['ini']['Config']['Twitter']['userid'];



            $txtFilename = $arrVar['txtSiteFileBaseCache'] . 'tweets' . DIRECTORY_SEPARATOR .
                                $arrOAuth['oauth_consumer_key'] .'-'.$intUserID. '.cache';
			$this->funCheckCreateDirectory($arrVar['txtSiteFileBaseCache'] . 'tweets');

			if ($txtContent = $this->funFetchCacheFile($txtFilename)){
                $arrContent = unserialize($txtContent);
                if(!empty($arrContent) && count($arrContent) >= 1){
				    return $arrContent;
                }
            }


			$txtURL = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
			$arrData = array(
				'user_id' => $intUserID,
				'count' => 20
			);

			$txtHeader = $this->funOAuth($arrOAuth,$arrData,$txtURL,'GET');

			$txtPostBody = '';
			foreach($arrData as $txtKey => $txtValue) {
				$txtPostBody .= $txtKey.'='.urlencode($txtValue).'&';
			}
			$txtPostBody = rtrim($txtPostBody,'&');

			$pntCurl = curl_init();
			curl_setopt($pntCurl, CURLOPT_URL, $txtURL . '?' . $txtPostBody);
			curl_setopt($pntCurl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded','Authorization: ' . $txtHeader));
			curl_setopt($pntCurl, CURLOPT_RETURNTRANSFER, 1);

			$txtResponse = curl_exec($pntCurl);
			$arrCurlInfo = curl_getinfo($pntCurl);
			curl_close($pntCurl);

			$arrJson = $this->funJsonDecode($txtResponse);

			$arrTweets = array();
    		foreach ($arrJson as $arrRow)
    		{
    			$arrTweets[] = array(
    				'tweet' => $this->funParseTweetText($arrRow['text']),
    				'name' => $arrRow['user']['screen_name'],
    				'time' => $this->funParseTweetTime($arrRow['created_at']),
    				'otime' => strtotime($arrRow['created_at']),
    				'link' => 'http://twitter.com/' . $arrRow['user']['screen_name'],
    				'link_tweet' => 'http://twitter.com/' . $arrRow['user']['screen_name'] . '/status/' . $arrRow['id_str'],
    				'link_reply' => 'http://twitter.com/intent/tweet?in_reply_to=' . $arrRow['id_str'],
    				'link_retweet' => 'http://twitter.com/intent/retweet?tweet_id=' . $arrRow['id_str'],
    				'link_favourite' => 'http://twitter.com/intent/favorite?tweet_id=' . $arrRow['id_str']
    			);
    		}

			$this->funSaveFile(serialize($arrTweets),$txtFilename);

			return $arrTweets;
		}

		function funFetchTweetRSS($arrData=array())
		{
			global $arrVar;

			$arrTweets = $this->funFetchTweets();

			$arrXML = array();
			$arrXML['rss'] = array();
			$arrXML['rss']['#attrib']['version'] = '2.0';

			$arrXML['rss'][0]['channel'] = array();
			$arrXML['rss'][0]['channel'][0]['title']['#text'] = (empty($arrData['title']) ? 'Tweets from ' . $arrTweets[0]['name'] : $arrData['title']);
			$arrXML['rss'][0]['channel'][1]['link']['#text'] = $arrData['link'];
			$arrXML['rss'][0]['channel'][2]['description']['#text'] = (empty($arrData['title']) ? 'Tweets from ' . $arrTweets[0]['name'] : $arrData['title']);

      		$i = 3;
      		foreach ($arrTweets as $arrRow)
      		{
      			$arrXML['rss'][0]['channel'][$i]['item'] = array();
      			$arrXML['rss'][0]['channel'][$i]['item'][0]['title']['#text'] = $this->funHtmlToText($arrRow['tweet']);
      			$arrXML['rss'][0]['channel'][$i]['item'][1]['link']['#text'] = $arrRow['link_tweet'];
      			$arrXML['rss'][0]['channel'][$i]['item'][2]['description']['#text'] = $arrRow['tweet'];
      			$arrXML['rss'][0]['channel'][$i]['item'][3]['pubDate']['#text'] = date('r',$arrRow['otime']);
      			$arrXML['rss'][0]['channel'][$i]['item'][4]['guid']['#text'] = $arrRow['link_tweet'];
      			$i++;
      		}

      		return $arrVar['objHTML']->funArrayToXML($arrXML);
		}

		function funParseTweetTime($txtDate)
		{
			$intTime = time() - strtotime($txtDate);
			$txtTime = '';
			if ($intTime < 60)
			{
				$txtTime = $intTime . ' seconds ago';
			}
			else if ($intTime < (60*60))
			{
				$intTemp = floor($intTime/60);
				$txtTime = '' . $intTemp  . ' minute' . ($intTemp > 1 ? 's' : '') . ' ago';
			}
			else if ($intTime < (60*60*24))
			{
				$intTemp = floor($intTime/(60*60));
				$txtTime = '' . $intTemp  . ' hour' . ($intTemp > 1 ? 's' : '') . ' ago';
			}
			else
			{
				$intTemp = floor($intTime/(60*60*24));
				$txtTime = $intTemp  . ' day' . ($intTemp > 1 ? 's' : '') . ' ago';
			}

			return $txtTime;
		}

		function funParseTweetText($txtText)
		{
			$txtText = htmlspecialchars($txtText);

			$txtRegex = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+(\S*)?/";
			if(preg_match($txtRegex, $txtText, $arrUrl))
				$txtText = preg_replace($txtRegex, '<a href="${0}" target="_blank">${0}</a> ', $txtText);

			$txtRegex = "/\S*\@([a-zA-Z0-9\-\_]+)\S*?/";
			if(preg_match($txtRegex, $txtText, $arrUrl))
				$txtText = preg_replace($txtRegex, '<a href="http://twitter.com/${1}" target="_blank">${0}</a> ', $txtText);

			return $txtText;
		}

		function funOAuth($arrOAuth,$arrData,$txtURL,$txtMethod='POST')
		{
			global $arrVar;

			$txtSigningKey = rawurlencode($arrOAuth['oauth_consumer_secret']) . '&' . rawurlencode($arrOAuth['oauth_token_secret']);
			unset($arrOAuth['oauth_consumer_secret']);
			unset($arrOAuth['oauth_token_secret']);

			$arrOAuth['oauth_nonce'] = md5(time() . $this->funGeneratePassword());
			$arrOAuth['oauth_timestamp'] = time();
			$arrOAuth['oauth_version'] = '1.0';
			$arrOAuth['oauth_signature_method'] = 'HMAC-SHA1';

			$arrTemp = array();
			foreach ($arrData as $txtKey => $txtValue)
				$arrTemp[$txtKey] = $txtKey . '=' . $txtValue;
			foreach ($arrOAuth as $txtKey => $txtValue)
				$arrTemp[$txtKey] = $txtKey . '=' . $txtValue;
			ksort($arrTemp);

			$txtString = $txtMethod . '&' . rawurlencode($txtURL) . '&' . rawurlencode(implode('&',$arrTemp));

			$arrOAuth['oauth_signature'] = base64_encode(hash_hmac('sha1',$txtString,$txtSigningKey,true));

			$arrTemp = array();
			foreach ($arrOAuth as $txtKey => $txtValue)
				$arrTemp[$txtKey] = $txtKey . '="' . rawurlencode($txtValue) . '"';
			ksort($arrTemp);
			$txtHeader = 'OAuth ' . implode(', ',$arrTemp);

			return $txtHeader;
		}

		function funSOAP($txtURL,$arrData,$intPort=0,$blnPost=true)
		{
			$pntCurl = curl_init();
			curl_setopt($pntCurl, CURLOPT_URL, $txtURL);
			if (!empty($intPort))
				curl_setopt($pntCurl, CURLOPT_PORT, $intPort);
			if ($blnPost)
				curl_setopt($pntCurl, CURLOPT_POST, 1);
			curl_setopt($pntCurl, CURLOPT_RETURNTRANSFER, 1);

			$txtPostBody = '';

			foreach($arrData as $txtKey => $txtValue) {
				$txtPostBody .= urlencode($txtKey).'='.urlencode($txtValue).'&';
			}
			$txtPostBody = rtrim($txtPostBody,'&');

			curl_setopt($pntCurl, CURLOPT_POSTFIELDS, $txtPostBody);
			$txtResponse = curl_exec($pntCurl);
			$arrCurlInfo = curl_getinfo($pntCurl);

			$blnReturn = true;

			if ($txtResponse == false)
			{
				$this->objErrorHandler->funDebug('cURL error: '. curl_error($pntCurl));
				return false;
			}
			else if ($arrCurlInfo['http_code'] != 200)
			{
				$this->objErrorHandler->funDebug('Error: non-200 HTTP status code: ' . $arrCurlInfo['http_code']);
				return false;
			}
			curl_close($pntCurl);

			return array($txtResponse,$arrCurlInfo);
		}

		function funDateDiffDays($intStart,$intEnd)
		{
			if (empty($intStart) || empty($intEnd))
				return false;

			if ($intStart > $intEnd)
			{
				$intTemp = $intStart;
				$intStart = $intEnd;
				$intEnd = $intStart;
			}

			$intDays = 0;
			if (date('Y',$intEnd) > date('Y',$intStart))
			{
				$intDays += date('z',$intEnd) + 1;

				$intTemp = $intStart;
				while (date('Y',$intTemp) < date('Y',$intEnd))
				{
					$intLeapYear = date('L',$intTemp);
					if ((date('Y',$intEnd) - date('Y',$intTemp)) == 1)
						$intDays += ((empty($intLeapYear) ? 366 : 365) - (date('z',$intTemp) + 1));
					else
						$intDays += (empty($intLeapYear) ? 366 : 365);

					$intTemp = mktime(0,0,0,1,1,date('Y',$intTemp)+1);
				}
			}
			else
			{
				$intDays += date('z',$intEnd) - date('z',$intStart);
			}

			return $intDays;
		}
	}
?>
