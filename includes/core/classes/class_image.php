<?php
	/**
	* Image Class
	*
	* Handles all of the image functionality
	*
	* @version 2.0
	* @package core
	*/
	class clsImage
	{
		/**
		* Whether or not the system should use dynamic images
		* @var boolean
		*/
		var $blnDynamicImages = true;
		/**
		* The location of the image cache directory
		* @var string
		*/
		var $txtImageCacheDir = '';
		/**
		* The URL for the image cache directory
		* @var string
		*/
		var $txtImageCacheURL = '';
		/**
		* The deafult thumbnail height
		* @var int
		*/
		var $intThumbnailHeight = 100;
		/**
		* The default thumbnail width
		* @var int
		*/
		var $intThumbnailWidth = 100;
		/**
		* The location of the images stored by the database
		* @var string
		*/
		var $txtImageDir = '';
		/**
		* The image quality of the JPGs produced - if the images are already optimised then can be left at 100
		*/
		var $intJpegQuality = 80;

		/**
		* The location of the font directory
		* @var string
		*/
		var $txtFontDir = '';

		/**
		* The seed to use when generating the captchas
		* @var string
		*/
		var $txtSeed = 'hacker';

		/**
		* Initialises the class and its variables
		*
		* @return boolean
		* @uses clsDatabase::intCoreVersion
		* @uses clsDatabase::txtDatabase
		* @uses $blnDynamicImages
		* @uses $txtImageCacheDir
		* @uses $txtImageCacheURL
		* @uses $txtMovieDir
		* @uses $txtMovieURL
		* @uses clsDataManipulation::funCheckCreateDirectory()
		* @uses $txtImageDir
		* @uses clsValidation::funValidateInteger()
		* @uses $intThumbnailHeight
		* @uses $intThumbnailWidth
		* @uses $txtFontDir
		* @uses $txtCaptchaDir
		* @uses $txtCaptchaURL
		*/
		function clsImage()
		{
			global $arrVar;

			if ($arrVar['objDb']->arrVersion['core'] > 0)
			{
				// As default we use dynamic images, but if the functions don't exist then we don't
				if ($this->blnDynamicImages)
				{
					if (!function_exists('imagejpeg') || !function_exists('imagegif') || !function_exists('imagepng'))
						$this->blnDynamicImages = false;
				}

				// Now lets set the location of the image cache directory
				$this->txtImageCacheURL = $arrVar['txtBaseURLCache'] . 'images';

				// Now lets create the full path
				$this->txtImageCacheDir = $arrVar['txtSiteFileBaseCache'] . 'images';

				// Now we check if the directory exists - if not we try to create it
				if (!is_dir($this->txtImageCacheDir))
				{
					if (!$arrVar['objDataManipulation']->funCheckCreateDirectory($this->txtImageCacheDir));
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image cache directory (' . $this->txtImageCacheDir . ') does not exist, or is not writable',E_USER_ERROR);
						return false;
					}
				}

				// Next we check that the cache directory is writable
				if (!is_writable($this->txtImageCacheDir))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image cache directory (' . $this->txtImageCacheDir . ') is not writable',E_USER_ERROR);
					return false;
				}

				// Now we check that the files.db directory exists - if not we try to create it
				$this->txtImageDir = $arrVar['txtSiteFileBaseCache'] . strEnvironment;
				if (!is_dir($this->txtImageDir))
				{
					if (!$arrVar['objDataManipulation']->funCheckCreateDirectory($this->txtImageDir))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image directory (' . $this->txtImageDir . ') does not exist, or is not writable',E_USER_ERROR);
						return false;
					}
				}

				// Now we setup the image directory
				$this->txtImageDir .= DIRECTORY_SEPARATOR . 'images';
				//$arrVar['objErrorHandler']->funDebug($this->txtImageDir);

				// Now we check if the directory exists - if not we try to create it
				if (!is_dir($this->txtImageDir))
				{
					if (!$arrVar['objDataManipulation']->funCheckCreateDirectory($this->txtImageDir));
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image directory (' . $this->txtImageDir . ') does not exist, or is not writable',E_USER_ERROR);
						return false;
					}
				}
				// Now we check that the directory is writeable
				if (!is_writable($this->txtImageDir))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image directory (' . $this->txtImageDir . ') does not exist, or is not writable',E_USER_ERROR);
					return false;
				}

				// Now we get the maximum thumbnail width
				if (!$intThumbnailWidth = $arrVar['ini']['Config']['Images']['MaxThumbnailWidth'])
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image thumbnail width has not been specified',E_USER_ERROR);
					return false;
				}

				// Now we get the maximum thumbnail height
				if (!$intThumbnailHeight = $arrVar['ini']['Config']['Images']['MaxThumbnailHeight'])
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image thumbnail height has not been specified',E_USER_ERROR);
					return false;
				}

				// Finally we assign them to the class variables for later use
				$this->intThumbnailWidth = $intThumbnailWidth;
				$this->intThumbnailHeight = $intThumbnailHeight;
			}

			return true;
		}

		/**
		* Fetches the Image by ID
		*
		* @param int $intID The ID of the record to fetch from coreImage
		* @param int $intLanguageID The LanguageID of the record to fetch
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		protected function funFetchImage($intID,$intLanguageID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreImage',$intID,'',$intLanguageID,false);
		}

		/**
		* Fetches the records from coreImage
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intImageCategoryID The image category to filter by
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		protected function funFetchImages($intMode=CORE_DB_FETCH,$intImageCategoryID=0,$txtSearch='',$txtFileType='',$intStart=0,$intLimit=0,$arrOrderAdditional=array(),$txtExactSearch='')
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreImage';
			$arrTable[]['left'] = array('coreImage_lang' => array('coreImage_lang' => 'ImageID', 'coreImage' => 'ImageID'),array('coreImage_lang' => 'LanguageID', '#VALUE#' => $_SESSION['LanguageID']));
			if (!empty($txtFileType))
				$arrTable[]['inner'] = array('coreFileType' => array('coreFileType' => 'FileTypeID', 'coreImage' => 'FileTypeID'));

			$arrWhere = array();
			if (!empty($intImageCategoryID))
			{
				if ($intImageCategoryID < 0)
					$arrWhere['coreImage.ImageCategoryID'] = array('IS NULL');
				else
					$arrWhere['coreImage.ImageCategoryID'] = array('=',$intImageCategoryID);
			}
			if (!empty($txtSearch))
				$arrWhere['coreImage.Description'] = array('LIKE',$txtSearch);
			if (!empty($txtFileType))
				$arrWhere['coreFileType.Type'] = array('=',$txtFileType);
			if (!empty($txtExactSearch))
				$arrWhere['coreImage.Description'] = array('=',$txtExactSearch);

			$arrOrder = array();
			$arrOrder['coreImage.Description'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
			{
				$arrField[] = 'coreImage.ImageID';
			}
			else
			{
				$arrField[] = 'coreImage_lang.*';
				$arrField[] = 'coreImage.*';
				$arrField[] = 'coreImage_lang.xDateAdded:xDateAdded_lang';
				$arrField[] = 'coreImage_lang.xLastUpdate:xLastUpdate_lang';
			}

			$arrGroup = array();
			//$arrGroup[] = 'coreImage.ImageID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}



		/**
		* Attempts to fetch an image as close to the dimensions requested
		*
		* @param int $intImageID The image to fetch
		* @param int $intWidth The maximum width that we want the image to be
		* @param int $intHeight The maximum height that we want the image to be
		* @param boolean $blnOriginal Whether or not we should just get the original
		* @return boolean|array
		* @uses clsValidation::funValidateInteger()
		* @uses $intThumbnailHeight
		* @uses $intThumbnailWidth
		* @uses funFetchImage()
		* @uses $blnDynamicImages
		* @uses funCheckCreateImage()
		* @uses funCheckCreateDynamicImage()
		*/

		public function funFetch($intImageID,$intWidth=0,$intHeight=0,$blnOriginal=false,$intLanguageID=0)
		{
			global $arrVar;

			// If the width and height are empty then we will use the default thumbnail dimensions
			if (empty($intWidth) || empty($intHeight))
			{
				$intWidth = $this->intThumbnailWidth;
				$intHeight = $this->intThumbnailHeight;
			}


			// Fetch the image
			if (!$arrImage = $this->funFetchImage($intImageID, $intLanguageID))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 2');
                $arrVar['objErrorHandler']->funDebug($intImageID . '::' . $intWidth . '::' . $intHeight.'::'.$intLanguageID);
				return false;
            }

			// Check to see if we need to fetch the original, or generate the image
			if ($blnOriginal || !$this->blnDynamicImages)
			{
				if (!$this->funCheckCreateImage($arrImage,true))
				{
					$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 3');
					return false;
				}
			}
			else // See if we have the correct size, otherwise if we have a larger image then we will resize and return it, otherwise we will return the largest size that we have the have
			{
				// Lets see if we have a larger image that we can scale down
				if ($arrImage['Width'] >= $intWidth || $arrImage['Height'] >= $intHeight)
				{
					if (!$this->funCheckCreateDynamicImage($arrImage,$intWidth,$intHeight))
					{
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 4');
						return false;
					}
				}
				else
				{
					if (!$this->funCheckCreateImage($arrImage))
					{
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 5');
						return false;
					}
				}
			}

            if(is_array($arrImage) && isset($arrImage['Filename'])){
                $arrImage['Src'] = $arrVar['txtBaseSiteURL'] . $arrImage['Filename'];
            }

			return $arrImage;
		}

		/**
		* Creates a cached version of the image, the same size as the one in the database
		*
		* @param array $arrImage The image array
		* @param boolean $blnOriginal Whether or not we should be creating an original image
		* @return boolean
		* @uses clsErrorHandler::funDebug
		* @uses funFetchFileType()
		* @uses funFileExtension()
		* @uses $txtImageCacheURL
		* @uses $txtImageCacheDir
		*/
		private function funCheckCreateImage(&$arrImage,$blnOriginal=false)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (!is_array($arrImage))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 1');
				return false;
			}

			// Now lets check that we are actually dealiing with an image
			$arrFileType = $this->funFetchFileType($arrImage['FileTypeID']);
			if ($arrFileType['Type'] != 'Image')
				return true;

			// Check that we have been given a width and height
			if ($arrImage['Width'] < 1 || $arrImage['Height'] < 1)
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 2');
				return false;
			}

			// Now fetch the file type information
			if (!$arrFileExtension = $this->funFileExtension($arrImage['FileTypeID']))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 3');
				return false;
			}

			// Set the name of the file
			$txtFilename = $arrImage['ImageID'] . '_' . ($blnOriginal ? 'orig_' : '') . $arrImage['Width'] . 'x' . $arrImage['Height'] . '.' . $arrFileExtension['Extension'];

			// If we have asked for the original then the the filenames
			if ($blnOriginal)
			{
				$txtOriginalFilename = $arrImage['ImageID'] . '_orig_' . $arrImage['Width'] . 'x' . $arrImage['Height'] . '.' . $arrFileExtension['Extension'];
				$arrImage['OriginalFilename'] = $this->txtImageCacheURL . '/' . $txtOriginalFilename;
				$arrImage['OriginalSystemFilename'] = $this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtOriginalFilename;
			}

			// If the file already exists then set the filename and return
			if (file_exists($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
			{
				$arrImage['Filename'] = $this->txtImageCacheURL . '/' . $txtFilename;
				$arrImage['SystemFilename'] = $this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename;

				return true;
			}

			// Do some final checks and make sure that the image extension is correct!
			if ($blnOriginal)
			{
				$arrMimeType = $this->funFetchMimeType($arrFileExtension['MimeType']);
				$arrTemp = getimagesize($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']);
				if ($arrMimeType['MimeType'] != $arrTemp['mime'])
				{
					switch ($arrTemp['mime'])
					{
						case 'image/jpeg':
						case 'image/pjpeg':	// Microsoft!!
							$txtExtension = 'jpg';
							break;
						case 'image/gif':
							$txtExtension = 'gif';
							break;
						case 'image/png':
							$txtExtension = 'png';
							break;
					}

					$intFileTypeID = $this->funFetchFileTypes(CORE_DB_FIND,$txtExtension);
					$arrData = array(
						'FileTypeID' => $intFileTypeID
					);

					$arrVar['objErrorHandler']->funDebug('Image ' . $arrImage['ImageID'] . ' had the wrong mime type - 1');

					$arrImage['FileTypeID'] = $intFileTypeID;
					return $this->funCheckCreateImage($arrImage,$blnOriginal);
				}
			}

			// If we have reached this point then we need to create the image
			if (!copy($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID'],$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
				return false;

			// Now set the filename
			$arrImage['Filename'] = $this->txtImageCacheURL . '/' . $txtFilename;
			$arrImage['SystemFilename'] = $this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename;

			return true;
		}



		/**
		* Fetches the FileType by ID
		*
		* @param int $intID The ID of the record to fetch from coreFileType
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		protected function funFetchFileType($intID)
		{
			global $arrVar;

			if (isset($this->arrCache[$intID]))
				return $this->arrCache[$intID];

			if ($arrData = $arrVar['objDb']->funCoreFetchSingle('coreFileType',$intID))
				$this->arrCache[$intID] = $arrData;

			return $arrData;
		}

		/**
		* Fetches the records from coreFileType
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $txtExtension The extension to search for
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		protected function funFetchFileTypes($intMode=CORE_DB_FETCH,$txtExtension='',$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreFileType';

			$arrWhere = array();
			if (!empty($txtExtension))
				$arrWhere['coreFileType.Extension'] = array('=',$txtExtension);

			$arrOrder = array();
			$arrOrder['coreFileType.Type'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreFileType.FileTypeID';
			else
				$arrField[] = 'coreFileType.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreFileType.FileTypeID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}



		/**
		* Fetches the MimeType by ID
		*
		* @param int $intID The ID of the record to fetch from coreMimeType
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		protected function funFetchMimeType($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreMimeType',$intID);
		}

		/**
		* Fetches the records from coreMimeType
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $txtExtension The extension to search for
		* @param int $txtMimeType The MimeType to find
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		protected function funFetchMimeTypes($intMode=CORE_DB_FETCH,$txtExtension='',$txtMimeType='',$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreMimeType';
			if (!empty($txtExtension))
				$arrTable[]['inner'] = array('coreFileType' => array('coreFileType' => 'MimeTypeID', 'coreMimeType' => 'MimeTypeID'));

			$arrWhere = array();
			if (!empty($txtExtension))
				$arrWhere['coreFileType.Extension'] = array('=',$txtExtension);
			if (!empty($txtMimeType))
				$arrWhere['coreMimeType.MimeType'] = array('=',$txtMimeType);

			$arrOrder = array();
			$arrOrder['coreMimeType.MimeType'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreMimeType.MimeTypeID';
			else
				$arrField[] = 'coreMimeType.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreMimeType.MimeTypeID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}



		/**
		* Returns the extension and mime type of a file type
		*
		* @param int $intFileType The file type to lookup
		* @return array
		*/
		private function funFileExtension($intFileTypeID)
		{
			global $arrVar;


			// Check if we already have the answer
			if (isset($this->arrCache[__FUNCTION__][$intFileTypeID]))
				return $this->arrCache[__FUNCTION__][$intFileTypeID];

			// Now lets fetch the file type
			if (!$arrFileType = $this->funFetchFileType($intFileTypeID))
				return false;

			// Now lets fetch the mime type
			if (!$arrMimeType = $this->funFetchMimeType($arrFileType['MimeTypeID']))
				return false;

			// Cache the information
			$this->arrCache[__FUNCTION__][$intFileTypeID] = array('Extension' => $arrFileType['Extension'], 'MimeType' => $arrMimeType['MimeTypeID']);

			return $this->arrCache[__FUNCTION__][$intFileTypeID];
		}

		/**
		* Attempts to resize the an image, storing it in the cache
		*
		* @param array $arrImage The image array
		* @param int $intWidth The maximum width we want the image to be
		* @param int $intHeight The maximum height we want the image to be
		* @return boolean
		* @uses clsValidation::funValidateInteger()
		* @uses clsErrorHandler:::funDebug()
		* @uses funFetchFileType()
		* @uses funFileExtension()
		* @uses funCalculateImageDimensions()
		* @uses $txtImageCacheDir
		* @uses $txtImageCacheURL
		* @uses funCheckCreateImage()
		*/
		private function funCheckCreateDynamicImage(&$arrImage,$intWidth,$intHeight)
		{
			global $arrVar;

			// First lets check that we have everything we need
			if (!is_array($arrImage))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 1');
				return false;
			}

			// Check that we have a width and height
			if ($intWidth < 1 || $intHeight < 1)
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 2');
				return false;
			}

			// Now lets check that we are actually dealing with an image
			$arrFileType = $this->funFetchFileType($arrImage['FileTypeID']);
			if ($arrFileType['Type'] != 'Image')
				return true;

			// Now lets fetch the information about the file extension
			if (!$arrFileExtension = $this->funFileExtension($arrImage['FileTypeID']))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 3');
				return false;
			}

			// If we have reached this point then we need to create the image
			list($intWidth,$intHeight) = $this->funCalculateImageDimensions($arrImage['Width'],$arrImage['Height'],$intWidth,$intHeight);

			// First lets see if the image has already been created
			$txtFilename = $arrImage['ImageID'] . '_' . $intWidth . 'x' . $intHeight . '.' . $arrFileExtension['Extension'];
			if (file_exists($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
			{
				$arrImage['Filename'] = $this->txtImageCacheURL . '/' . $txtFilename;
				$arrImage['Width'] = $intWidth;
				$arrImage['Height'] = $intHeight;
				return true;
			}

			// Fetch the original image
			if (!$this->funCheckCreateImage($arrImage,true))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Fail 4');
				return false;
			}
			$txtLargeFilename = $arrImage['SystemFilename'];

			// Create the new image object
			$txtImageNew = imagecreatetruecolor($intWidth, $intHeight);

			// Create an image object from the original image
			switch ($arrFileExtension['Extension'])
			{
				case 'jpg':
				case 'jpeg':
					if (!$txtImageOld = imagecreatefromjpeg($txtLargeFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $arrFileExtension['Extension'],E_USER_WARNING);
						imagedestroy($txtImageNew);
						unlink($txtLargeFilename);
						return false;
					}
					break;
				case 'gif':
					if (!$txtImageOld = imagecreatefromgif($txtLargeFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $arrFileExtension['Extension'],E_USER_WARNING);
						imagedestroy($txtImageNew);
						unlink($txtLargeFilename);
						return false;
					}
					break;
				case 'png':
					if (!$txtImageOld = imagecreatefrompng($txtLargeFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $arrFileExtension['Extension'],E_USER_WARNING);
						imagedestroy($txtImageNew);
						unlink($txtLargeFilename);
						return false;
					}
					break;
				default:
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unknown image extension: ' . $arrFileExtension['Extension'],E_USER_WARNING);
					imagedestroy($txtImageNew);
					unlink($txtLargeFilename);
					return false;
			}

			// Remove the original to stop the server getting full up with images
			unlink($txtLargeFilename);

			// If we are a GIF or PNG make sure we keep any transparency
			if ($arrFileExtension['Extension'] == 'gif' || $arrFileExtension['Extension'] == 'png')
			{
				$intTransparent = imagecolortransparent($txtImageOld);

				if ($intTransparent >=0)
				{
					$arrTransparent = imagecolorsforindex($txtImageOld,$intTransparent);
					$intTransparent = imagecolorallocate($txtImageNew,$arrTransparent['red'],$arrTransparent['green'],$arrTransparent['blue']);
					imagefill($txtImageNew,0,0,$intTransparent);
					imagecolortransparent($txtImageNew,$intTransparent);
				}
				else if ($arrFileExtension['Extension'] == 'png')
				{
					imagealphablending($txtImageNew,false);
					$intTransparent = imagecolorallocatealpha($txtImageNew,0,0,0,127);
					imagefill($txtImageNew,0,0,$intTransparent);
					imagesavealpha($txtImageNew,true);
				}
			}

			// Now lets attempt to resize the image
			if (!imagecopyresampled($txtImageNew, $txtImageOld, 0, 0, 0, 0, $intWidth, $intHeight, $arrImage['Width'], $arrImage['Height']))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' Error resizing image!');
				return false;
			}

			// Now lets try and save the image
			switch ($arrFileExtension['Extension'])
			{
				case 'jpg':
				case 'jpeg':
					if (!imagejpeg($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,$this->intJpegQuality))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
				case 'gif':
					if (!imagegif($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
				case 'png':
					if (!imagepng($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,2))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
			}
			// Destroy the image objects
			imagedestroy($txtImageNew);
			imagedestroy($txtImageOld);

			// Update the image array information
			$arrImage['Filename'] = $this->txtImageCacheURL . '/' . $txtFilename;
			$arrImage['Width'] = $intWidth;
			$arrImage['Height'] = $intHeight;

			return true;
		}





		/**
		* Checks to see if an image exists
		*
		* @param int $intImageID The image to check
		* @return boolean
		* @uses funFetchImage()
		*/
		public function funImageExists($intImageID)
		{
			global $arrVar;

			$arrImage = $this->funFetchImage($intImageID);

			return (empty($arrImage['ImageID']) ? false : true);
		}

		/**
		* Calculates the new image dimensions by calculating the ratio of the original dimensions
		*
		* @param int $intOriginalWidth The width of the original
		* @param int $intOriginalHeight The height of the original
		* @param int $intNewWidth The maximum width you want
		* @param int $intNewHeight The maximum height you want
		* @return boolean|array
		* @uses clsValidation::funValidateInteger()
		*/
		private function funCalculateImageDimensions($intOriginalWidth,$intOriginalHeight,$intNewWidth,$intNewHeight)
		{
			global $arrVar;

			// Now lets calculate the ratio
			$dblRatio = $intOriginalWidth / $intOriginalHeight;

			// Now lets work out whether we need to adjust the width or the hight
			if ($intNewWidth / $intNewHeight > $dblRatio) {
			   $intNewWidth = round($intNewHeight*$dblRatio);
			} else {
			   $intNewHeight = round($intNewWidth / $dblRatio);
			}

			// Now lets return the new width and hieght
			return array($intNewWidth,$intNewHeight);
		}


		/**
		* Attempts to resize an image within the system
		*
		* @param int $intImageID The image to resize
		* @param int $intWidth The new width of the image
		* @param int $intHeight The new height of the image
		* @return boolean
		* @uses funFetch()
		* @uses $txtImageCacheDir
		* @uses funAddEditImage()
		* @uses $txtImageDir
		*/
		public function funResizeImage($intImageID,$intWidth,$intHeight)
		{
			// First lets try and fetch the original image
			if (!$arrImage = $this->funFetch($intImageID,5000,5000,true))
				return false;

			// Now lets fetch the extension
			$txtExtension = pathinfo($arrImage['OriginalFilename'],PATHINFO_EXTENSION);

			// Now lets create the placeholder for our new image
			$txtImageNew = imagecreatetruecolor($intWidth,$intHeight);

			// Now lets load in the old image
			switch ($txtExtension)
			{
				case 'jpg':
				case 'jpeg':
					if (!$txtImageOld = imagecreatefromjpeg($arrImage['OriginalSystemFilename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($txtImageNew);
						return false;
					}
					break;
				case 'gif':
					if (!$txtImageOld = imagecreatefromgif($arrImage['OriginalSystemFilename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($txtImageNew);
						return false;
					}
					break;
				case 'png':
					if (!$txtImageOld = imagecreatefrompng($arrImage['OriginalSystemFilename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($txtImageNew);
						return false;
					}
					break;
				default:
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unknown image extension: ' . $txtExtension,E_USER_WARNING);
					imagedestroy($txtImageNew);
					return false;
			}

			// If we are a gif or png then make sure we don't lose any transparency
			if ($txtExtension == 'gif' || $txtExtension == 'png')
			{
				$intTransparent = imagecolortransparent($txtImageOld);

				if ($intTransparent >=0)
				{
					$arrTransparent = imagecolorsforindex($txtImageOld,$intTransparent);
					$intTransparent = imagecolorallocate($txtImageNew,$arrTransparent['red'],$arrTransparent['green'],$arrTransparent['blue']);
					imagefill($txtImageNew,0,0,$intTransparent);
					imagecolortransparent($txtImageNew,$intTransparent);
				}
				else if ($txtExtension == 'png')
				{
					imagealphablending($txtImageNew,false);
					$intTransparent = imagecolorallocatealpha($txtImageNew,0,0,0,127);
					imagefill($txtImageNew,0,0,$intTransparent);
					imagesavealpha($txtImageNew,true);
				}
			}

			// Now lets try and resize the image
			if (!imagecopyresampled($txtImageNew, $txtImageOld, 0, 0, 0, 0, $intWidth, $intHeight, $arrImage['Width'], $arrImage['Height']))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error resizing the image',E_USER_WARNING);
				imagedestroy($txtImageNew);
				imagedestroy($txtImageOld);
				return false;
			}

			// Set the filename
			$txtFilename = $intImageID . '_' . $intWidth . 'x' . $intHeight . '.' . $txtExtension;

			// Now lets try and save the new image
			switch ($txtExtension)
			{
				case 'jpg':
				case 'jpeg':
					if (!imagejpeg($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,100))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
				case 'gif':
					if (!imagegif($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
				case 'png':
					if (!imagepng($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,2))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						return false;
					}
					break;
			}

			// Tidy up
			imagedestroy($txtImageNew);
			imagedestroy($txtImageOld);

			// Now try and update the image information
			if (!$this->funAddEditImage(array('Width' => $intWidth, 'Height' => $intHeight),$arrImage['ImageID']))
				return false;

			// Now lets try and backup the original image
			if (!rename($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID'], $this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup'))
				return false;

			// Now lets try and move the image we have just created
			if (!rename($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,$this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']))
			{
				rename($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup', $this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']);
				return false;
			}

			// Now we remove the old original
			unlink($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup');

			// Finally lets remove all cached versions of the old image
			if (!$pntDir = opendir($this->txtImageCacheDir))
				return false;
			while (($txtFile = readdir($pntDir)) !== false)
			{
				$arrTemp = explode('_',$txtFile);
				if ($arrTemp[0] == $arrImage['ImageID'])
					unlink($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFile);
	        }
	        closedir($pntDir);

	        return true;
		}

	}
?>
