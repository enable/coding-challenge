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
		* The location of the movies stored by the database
		* @var string
		*/
		var $txtMovieDir = '';
		/**
		* The URL for the movie directory
		* @var array
		*/
		var $txtMovieURL = '';

		/**
		* The location of the font directory
		* @var string
		*/
		var $txtFontDir = '';
		/**
		* The location of the captcha directory
		* @var string
		*/
		var $txtCaptchaDir = '';
		/**
		* The base url for the captcha
		* @var array
		*/
		var $txtCaptchaURL = '';
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
		* Adds / Edits the coreImage record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditImage($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreImage',$arrData,$intID);
		}

		/**
		* Fetches the Image by ID
		*
		* @param int $intID The ID of the record to fetch from coreImage
		* @param int $intLanguageID The LanguageID of the record to fetch
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchImage($intID,$intLanguageID=0)
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
		function funFetchImages($intMode=CORE_DB_FETCH,$intImageCategoryID=0,$txtSearch='',$txtFileType='',$intStart=0,$intLimit=0,$arrOrderAdditional=array(),$txtExactSearch='')
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
		* Deletes a record from coreImage
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteImage($intID)
		{
			global $arrVar;

			if (!$arrVar['objDb']->funCoreDelete('coreImage',$intID))
				return false;

			// Now try and remove the image from the filesystem
			unlink($this->txtImageDir . DIRECTORY_SEPARATOR . $intID);

			return true;
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

		function funFetch($intImageID,$intWidth=0,$intHeight=0,$blnOriginal=false,$intLanguageID=0)
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
		function funCheckCreateImage(&$arrImage,$blnOriginal=false)
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
					if (!$this->funAddEditImage($arrData,$arrImage['ImageID']))
						trigger_error('Image ' . $arrImage['ImageID'] . ' has the wrong mime type',E_USER_ERROR);

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
		* Adds / Edits the coreFileType record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditFileType($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreFileType',$arrData,$intID);
		}

		/**
		* Fetches the FileType by ID
		*
		* @param int $intID The ID of the record to fetch from coreFileType
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchFileType($intID)
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
		function funFetchFileTypes($intMode=CORE_DB_FETCH,$txtExtension='',$intStart=0,$intLimit=0,$arrOrderAdditional=array())
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
		* Deletes a record from coreFileType
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteFileType($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreFileType',$intID);
		}

		/**
		* Adds / Edits the coreMimeType record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditMimeType($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreMimeType',$arrData,$intID);
		}

		/**
		* Fetches the MimeType by ID
		*
		* @param int $intID The ID of the record to fetch from coreMimeType
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchMimeType($intID)
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
		function funFetchMimeTypes($intMode=CORE_DB_FETCH,$txtExtension='',$txtMimeType='',$intStart=0,$intLimit=0,$arrOrderAdditional=array())
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
		* Deletes a record from coreMimeType
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteMimeType($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreMimeType',$intID);
		}

		/**
		* Returns the extension and mime type of a file type
		*
		* @param int $intFileType The file type to lookup
		* @return array
		*/
		function funFileExtension($intFileTypeID)
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
		function funCheckCreateDynamicImage(&$arrImage,$intWidth,$intHeight)
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
		* Takes the image array and using a template builds the image tag
		*
		* @param array $arrImage The image array
		* @param string $txtTemplate The name of the template to use (if different)
		* @return boolean|string
		* @uses funSelectDisplay()
		* @uses clsConfig::funGet()
		* @uses clsControllerCommon::$intSiteID
		*/
		function funBuildImageDisplay($arrImage, $txtTemplate='')
		{
			global $arrVar;

			// If we've been given a template then check if it exists
			if (!empty($txtTemplate))
			{
				if (!file_exists($this->funSelectDisplay('image' . DIRECTORY_SEPARATOR . $txtTemplate)))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image template ' . $txtTemplate . ' does not exist - falling back to default',E_USER_WARNING);
					$txtTemplate = '';
				}
			}

			// If the template is empty then use the one defined in the config, checking that it exists
			if (empty($txtTemplate))
			{
				if (!$txtTemplate = $arrVar['objConfig']->funGet('Images','DisplayTemplate',$arrVar['objController']->intSiteID))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - There was an error fetching the image display template',E_USER_ERROR);
					return false;
				}

				if (!file_exists($this->funSelectDisplay('image' . DIRECTORY_SEPARATOR . $txtTemplate)))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - The image template ' . $txtTemplate . ' does not exist - falling back to default',E_USER_WARNING);
					$txtTemplate = '';
				}
			}

			// Now lets call the file and build the image tag
			ob_start();
			require($this->funSelectDisplay('image' . DIRECTORY_SEPARATOR . $txtTemplate));
			$txtDisplay = ob_get_contents();
			ob_end_clean();

			return $txtDisplay;
		}

		/**
		* Adds an image to the system through either the file upload or the file system
		*
		* @param array $arrData An array containing the basic image properties
		* @param string $txtFilename The full path to the file (optional)
		* @param boolean $blnDeleteOriginal Whether or not to delete the source image
		* @return boolean|int
		* @uses clsDatabase::funCheckTableRequired()
		* @uses clsErrorHandler::funDebug()
		* @uses $txtImageCacheDir
		* @uses funUploadImageFromFile()
		* @uses funFetchFileTypes()
		* @uses clsDatabase::funStartTransaction()
		* @uses funAddEditImage()
		* @uses clsDatabase::funRollback()
		* @uses clsDatabase::funCommit()
		* @uses $txtImageDir
		*/
		function funAddImageFromFile($arrData,$txtFilename='',$blnDeleteOriginal=true)
		{
			global $arrVar;

			// If we don't have a filename then try and upload the image
			if (empty($txtFilename))
			{
				$txtTarget = $this->txtImageCacheDir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . session_id();
				if (!$txtFilename = $this->funUploadImageFromFile($_FILES['Image'],$txtTarget))
				{
					$arrVar['objErrorHandler']->funDebug($this->txtImageCacheDir);
					$arrVar['objErrorHandler']->funDebug($_FILES['Image']);
					$arrVar['objErrorHandler']->funDebug($txtTarget);
					return false;
				}
			}

			// Now lets get the images size and extension
			list($intWidth,$intHeight) = getimagesize($txtFilename);
			$txtExtension = pathinfo($txtFilename,PATHINFO_EXTENSION);

			// Make sure that we support the extension
			if (!$intFileTypeID = $this->funFetchFileTypes(CORE_DB_FIND,$txtExtension))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 3a' . $txtExtension);
				return false;
			}

			// Now lets start a transaction
			$arrVar['objDb']->funStartTransaction();

			// Now lets add what we have learnt
			$arrData['Width'] = $intWidth;
			$arrData['Height'] = $intHeight;
			$arrData['FileTypeID'] = $intFileTypeID;

			if (!$intImageID = $this->funAddEditImage($arrData))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . 'fail 5');
				$arrVar['objDb']->funRollback();
				return false;
			}

			// If for some reason we already have an image with this ID we remove it
			if (file_exists($this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID))
				unlink($this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID);

			// If we have been asked to delete the original then we'll try and move the file
			// Otherwise we'll just copy it
			if ($blnDeleteOriginal)
			{
				if (!rename($txtFilename,$this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID))
				{
					$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 7 (' . $txtFilename . ',' . $this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID);
					$arrVar['objDb']->funRollback();
					return false;
				}
			}
			else
			{
				if (!copy($txtFilename,$this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID))
				{
					$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 7 (' . $txtFilename . ',' . $this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID);
					$arrVar['objDb']->funRollback();
					return false;
				}
			}

			// Now we can commit the transaction
			$arrVar['objDb']->funCommit();

			return $intImageID;
		}

		/**
		* Adds a movie to the system through a file upload
		*
		* @param array $arrData An array containing the basic image properties
		* @return boolean|int
		* @uses clsDatabase::funCheckTableRequired()
		* @uses clsErrorHandler::funDebug()
		* @uses $txtMovieDir
		* @uses funUploadMovieFromFile()
		* @uses funFetchFileTypes()
		* @uses clsDatabase::funStartTransaction()
		* @uses clsDatabase::funRollback()
		* @uses clsDatabase::funCommit()
		* @uses funAddEditImage()
		*/
		function funAddMovieFromFile($arrData)
		{
			global $arrVar;

			// Now lets try and upload the movie
			$txtTarget = $this->txtMovieDir . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . session_id();
			if (!$txtFilename = $this->funUploadMovieFromFile($_FILES['Image'],$txtTarget))
			{
				$arrVar['objErrorHandler']->funDebug($_FILES['Image']);
				$arrVar['objErrorHandler']->funDebug($txtTarget);
				return false;
			}

			// Lets find out the extension
			$txtExtension = pathinfo($txtFilename,PATHINFO_EXTENSION);

			// Now lets make sure we support that extension
			if (!$intFileTypeID = $this->funFetchFileTypes(CORE_DB_FIND,$txtExtension))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 3a' . $txtExtension);
				return false;
			}

			// Now lets start a transaction
			$arrVar['objDb']->funStartTransaction();

			// Lets put togther the final bits to insert
			$arrData['Width'] = 0;
			$arrData['Height'] = 0;
			$arrData['FileTypeID'] = $intFileTypeID;

			// Now lets add the movie to the database
			if (!$intImageID = $this->funAddEditImage($arrData))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 5');
				$arrVar['objDb']->funRollback();
				return false;
			}

			// If for some reason the file exists then we'll remove it
			if (file_exists($this->txtMovieDir . DIRECTORY_SEPARATOR . $intImageID))
				unlink($this->txtMovieDir . DIRECTORY_SEPARATOR . $intImageID);

			// Now lets try and rename the file
			if (!rename($txtFilename,$this->txtMovieDir . DIRECTORY_SEPARATOR . $intImageID . '.' . $txtExtension))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 7 (' . $txtFilename . ',' . $this->txtMovieDir . DIRECTORY_SEPARATOR . $intImageID);
				$arrVar['objDb']->funRollback();
				return false;
			}

			// Now we need to update the information
			if (!$this->funAddEditImage(array('Filename' => $this->txtMovieURL . $intImageID . '.' . $txtExtension),$intImageID))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' fail 8');
				$arrVar['objDb']->funRollback();
				return false;
			}

			// Finally we commit the transaction
			$arrVar['objDb']->funCommit();

			return $intImageID;
		}

		/**
		* Attempts to crop an image
		*
		* @param int $intImageID The image that we want to crop
		* @param int $intX The starting X position
		* @param int $intY The starting Y position
		* @param int $intWidth The width of the cropping area
		* @param int $intHeight The height of the cropping area
		* @return boolean
		* @uses funFetch()
		* @uses $txtImageCacheDir
		* @uses clsDatabase::funStartTransaction()
		* @uses clsDatabase::funRollback()
		* @uses clsDatabase::funCommit()
		* @uses funAddEditImage()
		* @uses $txtImageDir

		*/
		function funCropImage($intImageID,$intX,$intY,$intWidth,$intHeight)
		{
			global $arrVar;

			// First lets fetch the image
			if (!$arrImage = $this->funFetch($intImageID,5000,5000,true))
			{
				$arrVar['objErrorHandler']->funDebug($intImageID);
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 1');
				return false;
			}

			// Now lets get its file extension
			$txtExtension = pathinfo($arrImage['OriginalFilename'],PATHINFO_EXTENSION);

			// Now lets create our new image
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
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 2');
						return false;
					}
					break;
				case 'gif':
					if (!$txtImageOld = imagecreatefromgif($arrImage['OriginalSystemFilename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($txtImageNew);
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 2');
						return false;
					}
					break;
				case 'png':
					if (!$txtImageOld = imagecreatefrompng($arrImage['OriginalSystemFilename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($txtImageNew);
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 2');
						return false;
					}
					break;
				default:
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unknown image extension: ' . $txtExtension,E_USER_WARNING);
					imagedestroy($txtImageNew);
					return false;
			}

			// If the file is a gif or png then we want to make sure we don't lose any transparency
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

			// Now we try and crop the image
			if (!imagecopyresampled($txtImageNew, $txtImageOld, 0, 0, $intX, $intY, $intWidth, $intHeight, $intWidth, $intHeight))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error cropping the image',E_USER_WARNING);
				imagedestroy($txtImageNew);
				imagedestroy($txtImageOld);
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 3');
				return false;
			}

			// Set the name of the image
			$txtFilename = $intImageID . '_' . $intWidth . 'x' . $intHeight . '.' . $txtExtension;

			// Now try and save the file
			switch ($txtExtension)
			{
				case 'jpg':
				case 'jpeg':
					if (!imagejpeg($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,100))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 4');
						return false;
					}
					break;
				case 'gif':
					if (!imagegif($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 4');
						return false;
					}
					break;
				case 'png':
					if (!imagepng($txtImageNew,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,2))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
						imagedestroy($txtImageNew);
						imagedestroy($txtImageOld);
						$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 4');
						return false;
					}
					break;
			}
			imagedestroy($txtImageNew);
			imagedestroy($txtImageOld);

			// Now we start a transaction
			$arrVar['objDb']->funStartTransaction();

			// Now lets update the image information
			if (!$this->funAddEditImage(array('Width' => $intWidth, 'Height' => $intHeight),$arrImage['ImageID']))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 5');
				$arrVar['objDb']->funRollback();
				return false;
			}

			// Now lets try and backup the original image
			if (!rename($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID'], $this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup'))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 6');
				$arrVar['objDb']->funRollback();
				return false;
			}

			// Now lets try copying over the cropped image we just created
			if (!rename($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,$this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']))
			{
				$arrVar['objErrorHandler']->funDebug($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename);
				$arrVar['objErrorHandler']->funDebug($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']);
				$arrVar['objDb']->funRollback();

				// Now lets try and restore the backup before returning
				rename($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup', $this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']);
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 7');
				return false;
			}

			// Now lets remove the backup
			unlink($this->txtImageDir . DIRECTORY_SEPARATOR . $arrImage['ImageID']. '.backup');

			// Now lets commit the transaction
			$arrVar['objDb']->funCommit();

			// Now lets go through the image cache and remove and cached versions
			if (!$pntDir = opendir($this->txtImageCacheDir))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - Fail 8');
				return false;
			}
			while (($txtFile = readdir($pntDir)) !== false)
			{
				$arrTemp = explode('_',$txtFile);
				if ($arrTemp[0] == $arrImage['ImageID'])
					unlink($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFile);
	        }
	        closedir($pntDir);

	        return true;
		}

		/**
		* Attempts to upload an image and makes sure the image is not too big
		*
		* @param array $arrFile The reference to the $_FILES to upload
		* @param string $txtTarget The directory to upload the file to
		* @param string $txtFilename The filename to use
		* @return boolean|string
		* @uses $txtImageCacheDir
		* @uses clsErrorHandler::funDebug()
		* @uses clsErrorHandler::funErrorMessage()
		* @uses clsDataManipulation::funCheckCreateDirectory()
		* @uses funCalculateImageDimensions()
		*/
		function funUploadImageFromFile($arrFile,$txtTarget,$txtFilename='')
		{
			global $arrVar;

			// If the target is outside of the image cache directory then return
			if (strpos($txtTarget,$this->txtImageCacheDir) !== 0)
				return false;

			// If we haven't been given a filename then we'll generate a random one
			if (empty($txtFilename))
				$txtFilename = rand();

			// Now lets make sure that there hasn't been an error with the upload
			if ($arrFile['error'] !== 0)
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 2 - ' . $txtTarget);
				return false;
			}

			// Now lets make sure that the target directory exists
			if (!$arrVar['objDataManipulation']->funCheckCreateDirectory($txtTarget))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 1 - ' . $txtTarget);
				return false;
			}

			// Now lets get the extension from the mime type
			switch ($arrFile['type'])
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
				default:
					// Unsupported file type
					$arrVar['objErrorHandler']->funErrorMessage('The image type that you have uploaded is not supported. Supported types are: JPG, GIF and PNG.');
					return false;
					break;
			}

			// Now lets try and move the uploaded file
			if (!move_uploaded_file($arrFile['tmp_name'],$txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 3 - ' . $txtTarget);
				return false;
			}

			// Now lets make sure that it is not too large!
			list($intOriginalWidth,$intOriginalHeight) = getimagesize($txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension);
			if ($intOriginalWidth > $arrVar['ini']['Config']['Images']['MaxStorageWidth'] || $intOriginalHeight > $arrVar['ini']['Config']['Images']['MaxStorageHeight'])
			{
				// Get the new width and height
				list($intWidth,$intHeight) = $this->funCalculateImageDimensions($intOriginalWidth,$intOriginalHeight,$arrVar['ini']['Config']['Images']['MaxStorageWidth'],$arrVar['ini']['Config']['Images']['MaxStorageHeight']);

				// Create the new image
				$txtImageNew = imagecreatetruecolor($intWidth,$intHeight);

				// Read in the old image
				switch ($txtExtension)
				{
					case 'jpg':
					case 'jpeg':
						if (!$txtImageOld = imagecreatefromjpeg($txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
						{
							trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
							imagedestroy($txtImageNew);
							return false;
						}
						break;
					case 'gif':
						if (!$txtImageOld = imagecreatefromgif($txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
						{
							trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file: ' . $txtExtension,E_USER_WARNING);
							imagedestroy($txtImageNew);
							return false;
						}
						break;
					case 'png':
						if (!$txtImageOld = imagecreatefrompng($txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
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

				// If we are a gif or png make sure we don't lose any transparency
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

				// Now try and resize the image
				if (!imagecopyresampled($txtImageNew, $txtImageOld, 0, 0, 0, 0, $intWidth, $intHeight, $intOriginalWidth, $intOriginalHeight))
				{
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error resizing the image',E_USER_WARNING);
					imagedestroy($txtImageNew);
					imagedestroy($txtImageOld);
					return false;
				}

				// Now try and save the new filename
				$txtFilename = $intImageID . '_' . $intWidth . 'x' . $intHeight . '.' . $txtExtension;
				switch ($txtExtension)
				{
					case 'jpg':
					case 'jpeg':
						if (!imagejpeg($txtImageNew,$txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension,100))
						{
							trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
							imagedestroy($txtImageNew);
							imagedestroy($txtImageOld);
							return false;
						}
						break;
					case 'gif':
						if (!imagegif($txtImageNew,$txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
						{
							trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
							imagedestroy($txtImageNew);
							imagedestroy($txtImageOld);
							return false;
						}
						break;
					case 'png':
						if (!imagepng($txtImageNew,$txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension,2))
						{
							trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtFilename,E_USER_WARNING);
							imagedestroy($txtImageNew);
							imagedestroy($txtImageOld);
							return false;
						}
						break;
				}

				// Clean up
				imagedestroy($txtImageNew);
				imagedestroy($txtImageOld);
			}

			// Return the full path
			return $txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension;
		}

		/**
		* Attempts to upload a movie from a file
		*
		* @param array $arrFile The reference to the $_FILES to upload
		* @param string $txtTarget The directory to upload the file to
		* @param string $txtFilename The filename to use
		* @return boolean|string
		* @uses $txtMovieDir
		* @uses clsErrorHandler::funDebug()
		* @uses clsErrorHandler::funErrorMessage()
		*/
		function funUploadMovieFromFile($arrFile,$txtTarget,$txtFilename='')
		{
			global $arrVar;

			// Check that the target is within the movie directory
			if (strpos($txtTarget,$this->txtMovieDir) !== 0)
				return false;

			// If we haven't been given a filename then lets generate one
			if (empty($txtFilename))
				$txtFilename = rand();

			// Lets double check that there hasn't been an error with the upload
			if ($arrFile['error'] !== 0)
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 2 - ' . $txtTarget);
				return false;
			}

			// Now lets make sure that the target directory exists
			if (!$arrVar['objDataManipulation']->funCheckCreateDirectory($txtTarget))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 1 - ' . $txtTarget);
				return false;
			}

			// Now lets find out the extension and check that it is supported
			switch ($arrFile['type'])
			{
				case 'application/octet-stream':
				case 'video/x-flv':
					$txtExtension = strtolower(array_pop(explode('.',$arrFile['name'])));
					break;
				default:
					// Unsupported file type
					$arrVar['objErrorHandler']->funErrorMessage('The movie type that you have uploaded ' . $txtExtension . ' is not supported. Supported types are: FLV.');
					return false;
					break;
			}

			// Now lets try and move the file
			if (!move_uploaded_file($arrFile['tmp_name'],$txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 3 - ' . $txtTarget);
				return false;
			}

			// Now lets return the full path
			return $txtTarget . DIRECTORY_SEPARATOR . $txtFilename . '.' . $txtExtension;
		}

		/**
		* Loops through the directory and removes any old files, or files belonging to images that no longer exist
		*
		* @param int $intDays How old a non-image file needs to be before it is removed
		* @param string $txtDir The directory to start from
		* @return boolean|int
		* @uses $txtImageCacheDir
		* @uses clsErrorHandler::funDebug()
		* @uses funImageExists()
		*/
		function funTidyImageCache($intDays=25,$txtDir='')
		{
			global $arrVar;

			// If the directory is empty then we'll use the image cache directory
			if (empty($txtDir))
				$txtDir = $this->txtImageCacheDir;

			// First well try and open the directory listing
			if (!$pntDir = opendir($txtDir))
				return false;

			// Now lets loop through the files / directories removing any old ones
			$intCount = 0;
			while (($txtFile = readdir($pntDir)) !== false)
			{
				// ignore these
				if ($txtFile == '.' || $txtFile == '..')
					continue;

				$intCount++;

				// If it is a directory then call ourself - if the directory is empty then delete it
				// If we are in the root of the image cache directory then remove those where the image has been deleted
				// Otherwise check to see how old it is, and delete if it is too old
				if (is_dir($txtDir . DIRECTORY_SEPARATOR . $txtFile))
				{
					$intTemp = $this->funTidyImageCache($intDays,$txtDir . DIRECTORY_SEPARATOR . $txtFile);
					$arrVar['objErrorHandler']->funDebug('Result = ' . $intTemp);
					if ($intTemp === 0)
					{
						rmdir($txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$arrVar['objErrorHandler']->funDebug('Remove Dir:' . $txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$intCount--;
					}
				}
				else if ($txtDir == $this->txtImageCacheDir)
				{
					$arrFile = explode('_',$txtFile);
					if (!$this->funImageExists($arrFile[0]))
					{
						unlink($txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$arrVar['objErrorHandler']->funDebug('Remove File:' . $txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$intCount--;
					}
				}
				else
				{
					$intTime = filectime($txtDir . DIRECTORY_SEPARATOR . $txtFile);
					$arrVar['objErrorHandler']->funDebug(date('Y-m-d H:i:s',$intTime));
					if ($intTime < mktime(date('H'),date('i'),date('s'),date('m'),date('d')-$intDays))
					{
						unlink($txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$arrVar['objErrorHandler']->funDebug('Remove File:' . $txtDir . DIRECTORY_SEPARATOR . $txtFile);
						$intCount--;
					}
				}
			}

			// Close the directory
			closedir($pntDir);

			// Return the count or files
			return $intCount;
		}

		/**
		* Checks to see if an image exists
		*
		* @param int $intImageID The image to check
		* @return boolean
		* @uses funFetchImage()
		*/
		function funImageExists($intImageID)
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
		function funCalculateImageDimensions($intOriginalWidth,$intOriginalHeight,$intNewWidth,$intNewHeight)
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
		* Copys an image
		*
		* @param int $intImageID The image to copy
		* @return array
		* @uses funFetchImage()
		* @uses clsDatabase::funStartTransaction()
		* @uses clsDatabase::funRollback()
		* @uses clsDatabase::funCommit()
		* @uses funAddEditImage()
		* @uses $txtImageDir
		*/
		function funCopyImage($intImageID)
		{
			global $arrVar;

			// Try and fetch the original image
			if (!$arrImage = $this->funFetchImage($intImageID))
				return false;

			// Now lets start a transaction
			$arrVar['objDb']->funStartTransaction();

			// Remove anything we don't need / shouldn't have
			unset($arrImage['ImageID']);
			unset($arrImage['xDateAdded']);
			unset($arrImage['xLastUpdate']);
			unset($arrImage['xDateAdded_lang']);
			unset($arrImage['xLastUpdate_lang']);

			// Update the description so we can easily tell which is the copy
			$arrImage['Description'] .= ' - copied ' . date('d/m/Y H:i:s');

			if (!$intNewImageID = $this->funAddEditImage($arrImage))
			{
				$arrVar['objDb']->funRollback();
				return false;
			}

			// Now lets try and copy the image from the filesystem
			if (!copy($this->txtImageDir . DIRECTORY_SEPARATOR . $intImageID,$this->txtImageDir . DIRECTORY_SEPARATOR . $intNewImageID))
			{
				$arrVar['objDb']->funRollback();
				return false;
			}
			$arrVar['objDb']->funCommit();

			return $intNewImageID;
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
		function funResizeImage($intImageID,$intWidth,$intHeight)
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

		/**
		* Adds watermarked text to an image
		*
		* @param int $intImageID The image to add the watermark to
		* @param int $intWidth The maximum width of the image
		* @param int $intHeight The maximum height of the image
		* @param string $txtText The text to add as a watermark
		* @param string $txtTTFont The font file to use
		* @return boolean|string
		* @uses funFetch()
		* @uses funFetchFileType()
		* @uses funFileExtension()
		* @uses $txtImageCacheDir
		* @uses $txtImageCacheURL
		* @uses $txtFontDir
		*/
		function funAddWatermarkText($intImageID,$intWidth=800,$intHeight=800,$txtText='SAMPLE',$txtTTFFont='verdana.ttf')
		{
			global $arrVar;

			// First lets try and fetch the image
			if (!$arrImage = $this->funFetch($intImageID,$intWidth,$intHeight))
				return false;

			// Now lets double check that we are dealing with an image
			$arrFileType = $this->funFetchFileType($arrImage['FileTypeID']);
			if ($arrFileType['Type'] != 'Image')
				return false;

			// Now lets fetch the file extension
			if (!$arrFileExtension = $this->funFileExtension($arrImage['FileTypeID']))
			{
				$arrVar['objErrorHandler']->funDebug(__CLASS__ . '::' . __FUNCTION__ . 'Fail');
				return false;
			}

			// Now lets create the filename
			$txtFilename = $intImageID . '_' . $arrImage['Width'] . 'x' . $arrImage['Height'] . '_watermark_' . md5($txtText) . '.jpg';

			// If the image already exists then return it
			if (file_exists($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
				return $this->txtImageCacheURL . '/' . $txtFilename;

			// Now load the image into memory
			switch ($arrFileExtension['Extension'])
			{
				case 'jpg':
				case 'jpeg':
					if (!$imgOld = imagecreatefromjpeg($arrVar['txtFileBase'] . $arrImage['Filename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file 2: ' . $txtExtension,E_USER_WARNING);
						return false;
					}
					break;
				case 'gif':
					if (!$imgOld = imagecreatefromgif($arrVar['txtFileBase'] . $arrImage['Filename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file 2: ' . $txtExtension,E_USER_WARNING);
						return false;
					}
					$txtTmpName = tempnam('/tmp','wm');
					if (!imagejpeg($imgOld,$txtTmpName,100))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file 2a: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($imgOld);
						unlink($txtTmpName);
						return false;
					}
					if (!$imgOld = imagecreatefromjpeg($txtTmpName))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file 2b: ' . $txtExtension,E_USER_WARNING);
						imagedestroy($imgOld);
						unlink($txtTmpName);
						return false;
					}
					unlink($txtTmpName);
					break;
				default:
					trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unknown image extension 2: ' . $txtExtension,E_USER_WARNING);
					return false;
			}

			// Set the path of the font to the securoty image directory
			$txtFont = $this->txtFontDir . DIRECTORY_SEPARATOR . $txtTTFFont;

			// Now lets create the text
			$arrBox = imageftbbox(180,-45,$txtFont,'   ' . $txtText . '   ');
			$imgWatermark = imagecreatetruecolor($arrBox[2],$arrBox[3]);
			$objColour = imagecolorallocatealpha($imgWatermark,0x00,0x00,0x00,50);
			$objBackgroundColour = imagecolorallocatealpha($imgWatermark,0xFF,0xFF,0xFF,127);
			imagefill($imgWatermark,0,0,$objBackgroundColour);
			imagettftext($imgWatermark,180,45,0,$arrBox[3],$objColour,$txtFont,'   ' . $txtText . '   ');

			// Now lets try merging the two images
			if (!imagecopyresampled($imgOld,$imgWatermark,0,0,0,0,imagesx($imgOld),imagesy($imgOld),$arrBox[2],$arrBox[3]))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error merging images',E_USER_WARNING);
				return false;
			}

			// Now try and save the image
			if (!imagejpeg($imgOld,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,100))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - unable to save resized image: ' . $txtNewFilename,E_USER_WARNING);
				imagedestroy($imgOld);
				imagedestroy($imgWatermark);
				return false;
			}

			// Tidy up
			imagedestroy($imgOld);
			imagedestroy($imgWatermark);

			// Return the image path
			return $this->txtImageCacheURL . '/' . $txtFilename;
		}

		/**
		* Adds a watermark image to an image
		*
		* @param int $intImageID The image to add the watermark to
		* @param string $txtWatermark The full path to the image to use as a watermark
		* @param string $txtPosition The positioning of the watermark
		* @param int $intWidth The maximum width of the image
		* @param int $intHeight The maximum height of the image
		* @return boolean|string
		* @uses funFetch()
		* @uses funFetchFileType()
		* @uses funFileExtension()
		* @uses clsErrorHandler::funDebug()
		* @uses $txtImageCacheDir
		* @uses $txtImageCacheURL
		*/
		function funAddWatermark($intImageID,$txtWatermark,$txtPosition='center',$intWidth=800,$intHeight=800)
		{
			global $arrVar;

			// First lets fetch the image
			if (!$arrImage = $this->funFetch($intImageID,$intWidth,$intHeight))
				return false;

			// Now lets check that we are dealing with an image
			$arrFileType = $this->funFetchFileType($arrImage['FileTypeID']);
			if ($arrFileType['Type'] != 'Image')
				return false;

			// Now lets fetch the file extension
			if (!$arrFileExtension = $this->funFileExtension($arrImage['FileTypeID']))
			{
				$arrVar['objErrorHandler']->funDebug(__CLASS__ . '::' . __FUNCTION__ . 'Fail');
				return false;
			}

			// Now lets set the filename
			$txtFilename = $intImageID . '_' . $arrImage['Width'] . 'x' . $arrImage['Height'] . '_iwatermark_' . md5($txtWatermark . $txtPosition) . '.jpg';

			// If the image already exists then return that
			if (file_exists($this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename))
				return $this->txtImageCacheURL . '/' . $txtFilename;

			// Now lets load in the old image
			switch ($arrFileExtension['Extension'])
			{
				case 'jpg':
				case 'jpeg':
					if (!$txtImageOld = imagecreatefromjpeg($arrVar['txtFileBase'] . $arrImage['Filename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file ' . $arrFileExtension['Extension'],E_USER_WARNING);
						return false;
					}
					break;
				case 'gif':
					if (!$txtImageOld = imagecreatefromgif($arrVar['txtFileBase'] . $arrImage['Filename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file ' . $arrFileExtension['Extension'],E_USER_WARNING);
						return false;
					}
					break;
				case 'png':
					if (!$txtImageOld = imagecreatefrompng($arrVar['txtFileBase'] . $arrImage['Filename']))
					{
						trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error creating image from file ' . $arrFileExtension['Extension'],E_USER_WARNING);
						return false;
					}
					break;
			}

			// Now lets get the size of the watermark
			list($intWatermarkWidth,$intWatermarkHeight) = getimagesize($txtWatermark);

			// Now lets try and load in the watermark
			if (!$txtImageWatermark = imagecreatefrompng($txtWatermark))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error fetching watermark',E_USER_WARNING);
				return false;
			}

			// Turn on alpha blending
			imagealphablending($txtImageOld, true);

			// Now lets work out the positioning
			switch ($txtPosition)
			{
				case 'top':
					$intX = ($arrImage['Width']/2)-($intWatermarkWidth/2);
					$intY = 3;
					break;
				case 'top-left':
					$intX = 3;
					$intY = 3;
					break;
				case 'top-right';
					$intX = $arrImage['Width']-$intWatermarkWidth-3;
					$intY = 3;
					break;
				case 'bottom':
					$intX = ($arrImage['Width']/2)-($intWatermarkWidth/2);
					$intY = $arrImage['Height']-$intWatermarkHeight-3;
					break;
				case 'bottom-left':
					$intX = 3;
					$intY = $arrImage['Height']-$intWatermarkHeight-3;
					break;
				case 'bottom-right':
					$intX = $arrImage['Width']-$intWatermarkWidth-3;
					$intY = $arrImage['Height']-$intWatermarkHeight-3;
					break;
				default: // center
					$intX = ($arrImage['Width']/2)-($intWatermarkWidth/2);
					$intY = ($arrImage['Height']/2)-($intWatermarkHeight/2);
			}

			// Now lets try and merge the 2 images
			if (!imagecopy($txtImageOld, $txtImageWatermark, $intX, $intY, 0, 0, $intWatermarkWidth, $intWatermarkHeight))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error adding watermark',E_USER_WARNING);
				return false;
			}

			// Now lets try and save the image
			if (!imagejpeg($txtImageOld,$this->txtImageCacheDir . DIRECTORY_SEPARATOR . $txtFilename,90))
			{
				trigger_error(__CLASS__ . '::' . __FUNCTION__ . ' - error saving',E_USER_WARNING);
				return false;
			}

			// Now lets return the path
			return $this->txtImageCacheURL . '/' . $txtFilename;
		}

		/**
		* Adds / Edits the coreGallery record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditGallery($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreGallery',$arrData,$intID);
		}

		/**
		* Fetches the Gallery by ID
		*
		* @param int $intID The ID of the record to fetch from coreGallery
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchGallery($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreGallery',$intID);
		}

		/**
		* Fetches the records from coreGallery
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intLanguageID The language to use
		* @param int $intActive Whether or not to filter by the active flag (-1 = not active, 1 = active)
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchGalleries($intMode=CORE_DB_FETCH,$intLanguageID=0,$intActive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreGallery';
			$arrTable[]['left'] = array('coreGallery_lang' => array('coreGallery_lang' => 'GalleryID', 'coreGallery' => 'GalleryID'));

			$arrWhere = array();
			if (!empty($intLanguageID))
				$arrWhere['coreGallery_lang.LanguageID'] = array('=',$intLanguageID);
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreGallery.Active'] = array('=',0);
				else
					$arrWhere['coreGallery.Active'] = array('<>',0);
			}

			$arrOrder = array();
			$arrOrder['coreGallery.SortOrder'] = '';
			$arrOrder['coreGallery_lang.Name'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreGallery.GalleryID';
			else
				$arrField[] = 'coreGallery.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreGallery.GalleryID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreGallery
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteGallery($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreGallery',$intID);
		}

		/**
		* Adds / Edits the coreGalleryCategory record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditGalleryCategory($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreGalleryCategory',$arrData,$intID);
		}

		/**
		* Fetches the GalleryCategory by ID
		*
		* @param int $intID The ID of the record to fetch from coreGalleryCategory
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchGalleryCategory($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreGalleryCategory',$intID);
		}

		/**
		* Fetches the records from coreGalleryCategory
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intGalleryID The gallery to filter by
		* @param int $intLanguageID The language to use
		* @param int $intActive Whether or not to filter by the active flag (-1 = not active, 1 = active)
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchGalleryCategories($intMode=CORE_DB_FETCH,$intGalleryID=0,$intLanguageID=0,$intActive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreGalleryCategory';
			$arrTable[]['left'] = array('coreGalleryCategory_lang' => array('coreGalleryCategory_lang' => 'GalleryCategoryID', 'coreGalleryCategory' => 'GalleryCategoryID'));

			$arrWhere = array();
			if (!empty($intGalleryID))
				$arrWhere['coreGalleryCategory.GalleryID'] = array('=',$intGalleryID);
			if (!empty($intLanguageID))
				$arrWhere['coreGalleryCategory_lang.LanguageID'] = array('=',$intLanguageID);
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreGalleryCategory.Active'] = array('=',0);
				else
					$arrWhere['coreGalleryCategory.Active'] = array('<>',0);
			}

			$arrOrder = array();
			$arrOrder['coreGalleryCategory.SortOrder'] = '';
			$arrOrder['coreGalleryCategory.Name'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreGalleryCategory.GalleryCategoryID';
			else
				$arrField[] = 'coreGalleryCategory.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreGalleryCategory.GalleryCategoryID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreGalleryCategory
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteGalleryCategory($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreGalleryCategory',$intID);
		}

		/**
		* Adds / Edits the coreGalleryCategoryToImage record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditGalleryCategoryToImage($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreGalleryCategoryToImage',$arrData,$intID);
		}

		/**
		* Fetches the GalleryCategoryToImage by ID
		*
		* @param int $intID The ID of the record to fetch from coreGalleryCategoryToImage
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchGalleryCategoryToImage($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreGalleryCategoryToImage',$intID);
		}

		/**
		* Fetches the records from coreGalleryCategoryToImage
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
		function funFetchGalleryCategoryToImages($intMode=CORE_DB_FETCH,$intActive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreGalleryCategoryToImage';

			$arrWhere = array();
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreGalleryCategoryToImage.Active'] = array('=',0);
				else
					$arrWhere['coreGalleryCategoryToImage.Active'] = array('<>',0);
			}

			$arrOrder = array();
			$arrOrder['coreGalleryCategoryToImage.SortOrder'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreGalleryCategoryToImage.GalleryCategoryToImageID';
			else
				$arrField[] = 'coreGalleryCategoryToImage.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreGalleryCategoryToImage.GalleryCategoryToImageID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreGalleryCategoryToImage
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteGalleryCategoryToImage($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreGalleryCategoryToImage',$intID);
		}

		/**
		* Adds / Edits the coreImageCategory record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditImageCategory($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreImageCategory',$arrData,$intID);
		}

		/**
		* Fetches the ImageCategory by ID
		*
		* @param int $intID The ID of the record to fetch from coreImageCategory
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchImageCategory($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreImageCategory',$intID);
		}

		/**
		* Fetches the records from coreImageCategory
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchImageCategories($intMode=CORE_DB_FETCH,$blnCurrent=false,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreImageCategory';
			if ($blnCurrent)
				$arrTable[]['inner'] = array('coreImage' => array('coreImage' => 'ImageCategoryID', 'coreImageCategory' => 'ImageCategoryID'));

			$arrWhere = array();

			$arrOrder = array();
			$arrOrder['coreImageCategory.SortOrder'] = '';
			$arrOrder['coreImageCategory.Category'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreImageCategory.ImageCategoryID';
			else
				$arrField[] = 'coreImageCategory.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreImageCategory.ImageCategoryID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreImageCategory
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteImageCategory($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreImageCategory',$intID);
		}

		/**
		* Adds / Edits the coreGalleryToSite record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditGalleryToSite($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreGalleryToSite',$arrData,$intID);
		}

		/**
		* Fetches the GalleryToSite by ID
		*
		* @param int $intID The ID of the record to fetch from coreGalleryToSite
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchGalleryToSite($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreGalleryToSite',$intID);
		}

		/**
		* Fetches the records from coreGalleryToSite
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
		function funFetchGalleryToSites($intMode=CORE_DB_FETCH,$intActive=0,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreGalleryToSite';

			$arrWhere = array();
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreGalleryToSite.Active'] = array('=',0);
				else
					$arrWhere['coreGalleryToSite.Active'] = array('<>',0);
			}

			$arrOrder = array();
			$arrOrder['coreGalleryToSite.SortOrder'] = '';
			$arrOrder['coreGalleryToSite.Name'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreGalleryToSite.GalleryToSiteID';
			else
				$arrField[] = 'coreGalleryToSite.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreGalleryToSite.GalleryToSiteID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreGalleryToSite
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteGalleryToSite($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreGalleryToSite',$intID);
		}

		/**
		* Adds / Edits the coreCaptcha record
		*
		* @param array $arrData The data that we want to insert / update
		* @param int $intID The ID of the record we want to update
		* @return boolean|array
		* @uses clsDatabase::funCoreAddEdit()
		*/
		function funAddEditCaptcha($arrData,$intID=0)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreAddEdit('coreCaptcha',$arrData,$intID);
		}

		/**
		* Fetches the Captcha by ID
		*
		* @param int $intID The ID of the record to fetch from coreCaptcha
		* @return boolean|array
		* @uses clsDatabase::funCoreFetchSingle()
		*/
		function funFetchCaptcha($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreFetchSingle('coreCaptcha',$intID);
		}

		/**
		* Fetches the records from coreCaptcha
		*
		* @param int $intMode The mode to use (Fetch, Count or Find)
		* @param int $intStart The row to start from
		* @param int $intLimit The maximum number of rows to fetch
		* @param array $arrOrderAdditional The start of the ordering array (to allow for column sorting)
		* @return array
		* @uses clsDatabase::funMergeOrder()
		* @uses clsDatabase::funCoreCount()
		* @uses clsDatabase::funCoreFetch()
		*/
		function funFetchCaptchas($intMode=CORE_DB_FETCH,$intStart=0,$intLimit=0,$arrOrderAdditional=array())
		{
			global $arrVar;

			$arrTable = array();
			$arrTable[] = 'coreCaptcha';

			$arrWhere = array();
			if (!empty($intActive))
			{
				if ($intActive < 0)
					$arrWhere['coreCaptcha.Active'] = array('=',0);
				else
					$arrWhere['coreCaptcha.Active'] = array('<>',0);
			}

			$arrOrder = array();
			$arrOrder['coreCaptcha.SortOrder'] = '';
			$arrOrder['coreCaptcha.Name'] = '';

			$arrOrder = $arrVar['objDb']->funMergeOrder($arrOrderAdditional,$arrOrder);

			$arrField = array();
			if ($intMode == CORE_DB_COUNT || $intMode == CORE_DB_FIND)
				$arrField[] = 'coreCaptcha.CaptchaID';
			else
				$arrField[] = 'coreCaptcha.*';

			$arrGroup = array();
			//$arrGroup[] = 'coreCaptcha.CaptchaID';

			if ($intMode == CORE_DB_COUNT)
				return $arrVar['objDb']->funCoreCount(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrGroup);
			else if ($intMode == CORE_DB_FIND)
				return $arrVar['objDb']->funCoreFind($arrTable,'','',$arrField[0],$arrWhere);
			else
				return $arrVar['objDb']->funCoreFetch(__FUNCTION__,$arrTable,$arrField,$arrWhere,$arrOrder,$intLimit,$intStart,$arrGroup);
		}

		/**
		* Deletes a record from coreCaptcha
		*
		* @param int $intID The ID of the record to delete
		* @return boolean
		* @uses clsDatabase::funCoreDelete()
		*/
		function funDeleteCaptcha($intID)
		{
			global $arrVar;

			return $arrVar['objDb']->funCoreDelete('coreCaptcha',$intID);
		}

		/**
		* Generates a Captcha image
		*
		* @param boolean $blnPlainBackground Whether or not to have a plain background
		* @param boolean $blnCheckerBackground Whether or not to use a checkered or random noise background
		* @param string $txtTTFFont The filename of the font to use
		* @return boolean|array
		* @uses funGenerateImageFromDb()
		* @uses $txtCaptchaDir
		* @uses $txtFontDir
		* @uses $txtSeed
		* @uses funAddEditCaptcha()
		* @uses $txtCaptchaURL
		*/
		function funGenerateCaptcha($blnPlainBackground=true,$blnCheckerBackground=true,$txtTTFFont='verdana.ttf')
		{
			global $arrVar;

			// First lets see if we can generate a captcha (Fasthosts SSL servers for example don't allow this)
			if (!function_exists('imagepng'))
				return $this->funGenerateCaptchaFromDb();

			// Check that we have a directory defined
			if (empty($this->txtCaptchaDir))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 3');
				return false;
			}

			// Now lets check that the font exists
			if (!file_exists($this->txtFontDir . DIRECTORY_SEPARATOR . $txtTTFFont))
			{
				$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 4 (' . $this->txtFontDir . DIRECTORY_SEPARATOR . $txtTTFFont . ')');
				return false;
			}

			// First lets generate our text
			$arrLetters = array('a','b','c','d','e','f','g','h','i','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z','0','1','2','3','4','5','6','7','8','9','A','B','C','D','E','F','G','H','I','J','K','L','M','N','P','Q','R','S','T','U','V','W','X','Y','Z');
			shuffle($arrLetters);
			$arrText = array();
			for ($i=0;$i<6;$i++)
				$arrText[$i] = $arrLetters[rand(0,count($arrLetters)-1)];
			$txtText = implode('',$arrText);

			// Now lets see if the images exists or if we have to create it
			if(!file_exists($this->txtCaptchaDir . DIRECTORY_SEPARATOR . ($blnPlainBackground ? '1' : '0') . md5($this->txtSeed . $txtText) . '.png'))
			{
				// Now select a random background/foreground colour
				$arrColours = array(
										array('bg' => array(0xff,0xff,0xff), 'fg' => array(0x00,0x00,0x00)),
										array('bg' => array(0x00,0x00,0x00), 'fg' => array(0xff,0xff,0xff)),
										array('bg' => array(0xED,0x61,0x32), 'fg' => array(0x00,0x00,0x00)),
										array('bg' => array(0x39,0xEE,0x0D), 'fg' => array(0x00,0x00,0x00)),
										array('bg' => array(0x73,0x91,0xF9), 'fg' => array(0x00,0x00,0x00)),
									);
				$intRand = 0;
				if (!$blnPlainBackground)
					$intRand = rand(0,count($arrColours)-1);
				$arrColour = $arrColours[$intRand];

				// Now lets create the backdrop
				$img = imagecreatetruecolor(150,30);
				$imgBg = imagecolorallocate($img,$arrColour['bg'][0],$arrColour['bg'][1],$arrColour['bg'][2]);
				imagefill($img, 0, 0, $imgBg);


				// Now if we are not having a plain image lets add some noise to the background
				if (!$blnCheckerBackground)
				{
					$imgRed = imagecolorallocate($img,255,0,0);
					$imgGreen = imagecolorallocate($img,0,255,0);
					$imgBlue = imagecolorallocate($img,0,0,255);

					for ($i=0;$i<20;$i++)
					{
						$intX1 = rand(1,149);
						$intY1 = rand(1,29);
						$intX2 = rand(1,149);
						$intY2 = rand(1,29);
						switch ($i)
						{
							case 0:
							case 3:
							case 6:
							case 9:
							case 12:
							case 15:
							case 18:
								imageline($img,$intX1,$intY1,$intX2,$intY2,$imgRed);
								break;
							case 1:
							case 4:
							case 7:
							case 10:
							case 13:
							case 16:
							case 19:
								imageline($img,$intX1,$intY1,$intX2,$intY2,$imgGreen);
								break;
							case 2:
							case 5:
							case 8:
							case 11:
							case 14:
							case 17:
							case 20:
								imageline($img,$intX1,$intY1,$intX2,$intY2,$imgBlue);
								break;
						}
					}
				}
				else
				{
					$imgGrey = imagecolorallocate($img,128,128,128);
					for ($i=10;$i<=140;$i+=10)
						imageline($img,$i,0,$i,30,$imgGrey);

					for ($i=10;$i<=20;$i+=10)
						imageline($img,0,$i,150,$i,$imgGrey);
				}

				// Now lets add the text
				$txtFont = $this->txtFontDir . '/' . $txtTTFFont;
				$intX = 10;
				for ($i=0;$i<6;$i++)
				{
					if (is_numeric($arrText[$i]))
						$imgText = imagecolorallocate($img,0x00,0x45,0x99);
					else if (strtolower($arrText[$i]) === $arrText[$i])
						$imgText = imagecolorallocate($img,$arrColour['fg'][0],$arrColour['fg'][1],$arrColour['fg'][2]);
					else
						$imgText = imagecolorallocate($img,0xDF,0x00,0x00);
					imagettftext($img,16,rand(-35,35),$intX,rand(20,27),$imgText,$txtFont,$arrText[$i]);
					$intX += 23;
				}

				// Now lets save the image
				if (!imagepng($img, $this->txtCaptchaDir . DIRECTORY_SEPARATOR . ($blnPlainBackground ? '1' : '0') . md5($this->txtSeed . $txtText) . '.png'))
				{
					imagedestroy($img);
					$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 1 - ' . $this->txtCaptchaDir . DIRECTORY_SEPARATOR . ($blnPlainBackground ? '1' : '0') . md5($this->txtSeed . $txtText) . '.png');
					return false;
				}

				// Now lets save the image to the database
				$arrData = array(
								'ImageText' => $txtText,
								'Seed' => $this->txtSeed,
								'Filename' => ($blnPlainBackground ? '1' : '0') . md5($this->txtSeed . $txtText) . '.png'
								);
				if (!$this->funAddEditCaptcha($arrData))
				{
					$arrVar['objErrorHandler']->funDebug(__FUNCTION__ . ' - fail 2');
					return false;
				}
			}

			return array(
							'text' => $txtText,
							'html' => '<img src="' . $this->txtCaptchaURL . '/' . ($blnPlainBackground ? '1' : '0') . md5($this->txtSeed . $txtText) . '.png" width="150" height="30" id="security_image" alt="" /><input type="hidden" name="_md5SecurityImage" value="' . md5($this->txtSeed . $txtText) . '" />'
						);
		}

		/**
		* Attempts to fetch an image using entries from the database
		*
		* @return boolean|array
		* @uses funFetchCaptchas
		* @uses $txtCaptchaDir
		* @uses clsSite::funFetchSiteToURLs()
		* @uses clsControllerCommon::$intSiteID
		* @uses clsDataManipulation::funFetchFile()
		* @uses $txtCaptchaURL
		* @uses $txtSeed
		*/
		function funGenerateImageFromDb()
		{
			global $arrVar;

			// First lets see how many images we have in the database
			$intCount = $this->funFetchCaptchas(CORE_DB_COUNT);
			if ($intCount < 1)
				return false;

			// Get a random number
			$intRand = 1;
			if ($intCount > 1)
				$intRand = rand(1,$intCount-1);

			// Fetch the record
			$arrData = $this->funFetchCaptchas(CORE_DB_FETCH,$intRand,1);

			// Now lets check that we don't already have the file - if not we try and fetch it
			if (!file_exists($this->txtCaptchaDir . DIRECTORY_SEPARATOR . $arrRow['Filename']))
			{
				// Lets get the non-SSL URL
				if (!$arrSiteToURL = $arrVar['clsSite']->funFetchSiteToURLs(CORE_DB_FETCH,$arrVar['objController']->intSiteID,'',-1,1,0,1))
					return false;

				// Now lets try and read the file
				if (!$txtCaptchaImage = $arrVar['objDataManipulation']->funFetchFile('http://' . $arrSiteToURL[0]['URL'] . $this->txtCaptchaURL . '/' . $arrRow['Filename']))
					return false;

				// Now lets save the file locally
				if (!$pntFile = fopen($this->txtCaptchaDir . DIRECTORY_SEPARATOR . $arrRow['Filename'],'w+'))
					return false;
				if (!fwrite($pntFile,$txtCaptchaImage))
				{
					fclose($pntFile);
					return false;
				}
				fclose($pntFile);

				// Tidy up
				unset($txtCaptchaImage);
			}

			return array(
							'text' => $arrRow['Text'],
							'html' => '<img src="' . $this->txtCaptchaURL . '/' . $arrRow['Filename'] . '" width="150" height="30" id="security_image" alt="" /><input type="hidden" name="_md5SecurityImage" value="' . md5($this->txtSeed . $arrRow['Text']) . '" />'
						);
		}

		/**
		* Validates the text for a captcha
		*
		* @param string $txtText The text entered by the user
		* @return array
		*/
		function funValidateCaptcha($txtText)
		{
			global $arrVar;

			if (md5($this->txtSeed . $txtText) == $arrVar['arrWorking']['data']['_md5SecurityImage'])
				return true;

			return false;
		}
	}
?>
