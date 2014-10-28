<?php
	/**
	* Error Handler Class
	* 
	* Handles the error handling, debugging and error messages for the application.
	* All errors are hidden from public view, so that the application remains secure.
	* Emails are generated when serious errors occur so that we know about them as soon as they happen.
	*
	* @version 2.0
	* @package core
	*/
	class clsErrorHandler
	{
		/**
		* Whether or not dubug should be turned on
		* @var boolean
		*/
		var $blnDebug = false;
		/**
		* An array of debug messages
		* @var array
		*/
		var $arrDebug = array();
		/**
		* The level of debugging we want to do.
		* 
		* Only debug messages of this level and below will be shown
		* @var int
		*/
		var $intDebugLevel = 75;
		
		/**
		* Whether or not we want to enable SQL debugging
		*
		* SQL debuging will show us how many times a query is run, so
		* we can see where we need caching or improvement
		* @var boolean
		*/
		var $blnDebugSQL = false;
		/**
		* An array containing the SQL debug information
		* @var array
		*/
		var $arrDebugSQL = array();
		/**
		* Whether or not we want to enable slow SQL logging
		* @var boolean
		*/
		var $blnSlowSQL = false;
		/**
		* How long the SQL needs to take (in seconds) to be classed as slow
		* @var array
		*/
		var $dblSlowSQL = 2;
		/**
		* Whether or not we want to enable slow SQL logging
		* @var boolean
		*/
		var $blnDebugTiming = false;
		
		/**
		* Whether or not the funSetCleanExit function was called, and thus
		* that the application ended as expected
		* @var array
		*/
		var $blnCleanExit = false;
		
		/**
		* Whether or not an error message has been generated
		* @var boolean
		*/
		var $blnError = false;
		/**
		* The error message that was generated
		* @var string
		*/
		var $txtError = '';
		/**
		* An array of hashed errors that have been emailed, to make sure that
		* only one email is sent per unique error
		* @var array
		*/
		var $arrErrorsEmailed = array();
		/**
		* The email address that errors should be sent to.
		* 
		* This value is overridden by the config, but the default value exists
		* here so that if an error occurs before the config can be loaded that it
		* still emails someone
		* @var string
		*/
		var $txtErrorEmailsTo = 'technical@goramandvincent.com';
		
		/**
		* Initialise the error handler, overriding the defaults from the config if available
		* 
		* @return boolean
		* @uses $blnDebug
		* @uses $intDebugLevel
		* @uses $blnDebugSQL
		* @uses $txtErrorEmailsTo
		*/
		function clsErrorHandler()
		{
			global $arrVar;
			
			// First lets check that the debug options exist
			if (is_array($arrVar['ini']['Debug']))
			{
				// Lets parse the DebugByIP variable and see if we need to switch on debugging by IP
				$arrDebugIP = explode(',',$arrVar['ini']['Debug']['Debug']['DebugByIP']);
				if (empty($arrDebugIP[0]) || is_null($arrVar['ini']['Debug']['Debug']['DebugByIP']))
					$arrDebugIP = array();
				
				// If there are no entries in DebugByIP, or our IP matches an entry in it, or if for some reason there is no IP
				// available then enable debugging as per the config
				if (count($arrDebugIP) == 0 || in_array($_SERVER['REMOTE_ADDR'],$arrDebugIP) || empty($_SERVER['REMOTE_ADDR']))
				{
					$this->blnDebug = (empty($arrVar['ini']['Debug']['Debug']['Debug']) ? false : true);
					$this->intDebugLevel = intval($arrVar['ini']['Debug']['Debug']['DebugLevel']);
					$this->blnDebugSQL = (empty($arrVar['ini']['Debug']['Debug']['DebugSQL']) ? false : true);
					$this->blnSlowSQL = (empty($arrVar['ini']['Debug']['Debug']['SlowSQL']) ? false : true);
					$this->blnDebugTiming = (empty($arrVar['ini']['Debug']['Debug']['DebugTiming']) ? false : true);
				}
				
				// If we have it we'll set the email address to send errors to
				if (is_array($arrVar['ini']['Debug']['ErrorHandling']))
				{
					$this->txtErrorEmailsTo = $arrVar['ini']['Debug']['ErrorHandling']['ErrorEmailsTo'];
				}
			}
			
			// Lastly if we can we set the default timezone to Europe/London
			if (function_exists('date_default_timezone_set'))
				date_default_timezone_set('Europe/London');
			
			return true;
		}
		
		/**
		* Decides whether or not to add debug information to the debug array
		* 
		* If debugging is switched off, or the error level is greater than the debug
		* level then the function ignores the message and returns false. Otherwise it
		* is stored and then output at the end.
		* 
		* @param string|array $mxdMessage The message or array to store
		* @param int $intMinErrorLevel The debug level and below that the message should be logged at
		* @param boolean $blnPre Whether or not to wrap the message in <pre> tags
		* @return boolean
		* @uses $blnDebug
		* @uses $intDebugLevel
		*/
		function funDebug($mxdMessage,$intMinErrorLevel=0,$blnPre=false)
		{
			if (!$this->blnDebug || $intMinErrorLevel > $this->intDebugLevel)
				return false;
			
			$txtDebugMessage = '';
			
			if (is_array($mxdMessage))
				$txtDebugMessage = htmlspecialchars(print_r($mxdMessage,true));
			else
				$txtDebugMessage = htmlspecialchars($mxdMessage);
			
			if ($blnPre)
				$txtDebugMessage = '<pre>' . $txtDebugMessage . '</pre>';

            if(function_exists('memory_get_usage'))
                $this->arrDebug[] = "Memory Usage: ".memory_get_usage(true)." Time: ".microtime(true);

			$this->arrDebug[] = $txtDebugMessage;
			return true;
		}
		
		/**
		* Decides whether or not to store SQL debug information
		* 
		* @param string $txtName The reference for the SQL
		* @param string $txtSQL The SQL that has been run
		* @param int|boolean $intStart The time that the query started (or false if not applicable)
		* @param int|boolean $intFinish The time that the query finished (or false if not applicable)
		* @return boolean
		* @uses $blnDebugSQL
		* @uses $arrDebugSQL
		* @uses funSlowSQL
		*/
		function funDebugSQL($txtName,$txtSQL,$intStart=false,$intFinish=false)
		{
			// If we have a start and finish then pass results on to the slow SQL logger
			if ($intStart !== false && $intFinish !== false)
				$this->funSlowSQL($txtName,$txtSQL,$intStart,$intFinish);
			
			// First check if SQL debugging is enabled, otherwise return
			if (!$this->blnDebugSQL)
				return false;
			
			// Work out the time elapsed (if applicable)
			if ($intStart === false || $intFinish === false)
				$intElapsed = 'n/a';
			else
				$intElapsed = number_format($intFinish - $intStart,4);
			
			// Store the SQL information and timing
			$this->arrDebugSQL['SQL'][] = array(
											'md5'	=>	md5($txtSQL),
											'Name'	=>	$txtName,
											'SQL'	=>	$txtSQL,
											'Time'	=>	$intElapsed
										);
			// Store a count of the the number of times the same SQL has been executed
			$this->arrDebugSQL['md5'][md5($txtSQL)] = intval($this->arrDebugSQL['md5'][md5($txtSQL)])+1;
			
			return true;
		}
		
		/**
		* Decides whether or not to log slow SQL queries
		* 
		* @param string $txtName The reference for the SQL
		* @param string $txtSQL The SQL that has been run
		* @param int $intStart The time that the query started (or false if not applicable)
		* @param int $intFinish The time that the query finished (or false if not applicable)
		* @return boolean
		* @uses $blnDebugSQL
		* @uses $arrDebugSQL
		* @uses funSlowSQL
		*/
		function funSlowSQL($txtName,$txtSQL,$intStart,$intFinish)
		{
			if (!$this->blnSlowSQL)
				return false;
			
			$intElapsed = $intFinish - $intStart;
			if ($intElapsed < $this->dblSlowSQL)
				return false;
			
			$this->funDebug('SLOW SQL (' . $intElapsed . ' seconds) - ' . $txtName . "\n" . $txtSQL);
			
			return true;
		}
		
		/**
		* Takes an error message that has been generated and stores it
		* 
		* By default only the first error message is stored
		* 
		* @param string $txtErrorMessage The error message passed
		* @param string $blnAppend Whether or not to append the error message
		* @return boolean
		* @uses $txtError
		* @uses $blnError
		*/
		function funErrorMessage($txtErrorMessage,$blnAppend=false)
		{
			// If there is already an error message and we're not appending then return
			if (!empty($this->txtError) && $blnAppend == false)
				return false;
			
			// Set / append the error message and set $blnError to true
			$this->txtError .= (empty($this->txtError) ? '' : ', ') . $txtErrorMessage;
			$this->blnError = true;
			
			return true;
		}
		
		/**
		* Sets the clean exit flag so that we know the application ended properly
		* 
		* @return boolean
		* @uses $blnCleanExit
		*/
		function funSetCleanExit()
		{
			// Make sure that any session data has been written
			session_write_close();
			// Set the clean exit flag
			$this->blnCleanExit = true;
			
			return true;
		}
		
		/**
		* Prints the debugging info
		* 
		* @return boolean
		* @uses $blnDebug
		* @uses $arrDebug
		* @uses $blnDebugSQL
		* @uses $arrDebugSQL
		*/
		function funPrintDebug()
		{
			// First lets check that debugging is enabled, otherwise return
			if (!$this->blnDebug)
				return false;
			
			// Print the start of the debug wrapper
			print '<div style="clear:both;"></div><div id="debugOutput"><pre>';
			
			// Loop through and print all of the debug information
			foreach($this->arrDebug as $txtDebug)
				print $txtDebug . "\n\n";
			
			// Now check to see if SQL debugging is enabled, and of so output it
			if ($this->blnDebugSQL)
			{
                if(isset($this->arrDebugSQL['md5']))
				    asort($this->arrDebugSQL['md5']);
				
				foreach($this->arrDebugSQL as $txtDebug)
				{
					print_r($txtDebug);
					print "\n\n";
				}
			}
			
			// Close the debug wrapper
			print '</pre></div>';
			
			return true;
		}
		
		/**
		* Generates a backtrace of the error to try and help trace the source of the problem
		* 
		* This does not work for fatal errors unfortunately
		* 
		* @param int $intIgnore The levels of backtrace to ignore (i.e. the first level would be this function)
		* @return string
		*/
		function funReturnDebugTrace($intIgnore=1)
		{
			global $arrVar;
			
			// Initialise the return variable
			$txtDebugTrace = '';
			
			// Get the backtrace information from PHP
			$arrDebugBacktrace = debug_backtrace();
			
			// Get rid of the first x levels of debug
			for ($i=0;$i<$intIgnore;$i++)
				array_shift($arrDebugBacktrace);
			
			// Now lets loop through the backtrace and put it in a more readable format
			// What we are aiming to produce: #0  c() called at [/tmp/include.php:10]
			$arrTemp = array();
			foreach ($arrDebugBacktrace as $txtKey => $arrRow)
			{
				// Format the arguements so that they will appear in the output
				$arrTArgs = array();
				if (is_array($arrRow['args']))
				{
					foreach ($arrRow['args'] as $txtTKey => $mxdArg)
					{
						if (is_array($mxdArg))
							$arrTArgs[$txtTKey] = str_replace(array("\n","\t",'  '),array(' ','',''),print_r($mxdArg,true));
						else if (is_object($mxdArg))
							$arrTArgs[$txtTKey] = 'object(' . get_class($mxdArg) . ')';
						else if (is_null($mxdArg))
							$arrTArgs[$txtTKey] = 'NULL';
						else if (strlen($mxdArg) == 0)
							$arrTArgs[$txtTKey] = '';
						else if (!is_numeric($mxdArg))
							$arrTArgs[$txtTKey] = '"' . addslashes($mxdArg) . '"';
					}
				}
				
				// Put the line together	
				$arrTemp[] = '#' . $txtKey . ' ' . $arrRow['class'] . $arrRow['type'] . $arrRow['function'] . '(' . implode(', ',$arrTArgs) . ') called at [' . str_replace($arrVar['txtFileBase'],'',$arrRow['file']) . ':' . $arrRow['line'] . ']';
			}
			
			// Finally join all of the lines togethr
			$txtDebugTrace .= implode("\n",$arrTemp);
			
			return $txtDebugTrace;
		}
	}
	
	global $arrVar;
	
	// Initialise the error handler function 
	$arrVar['objErrorHandler'] = new clsErrorHandler();
	
	// Make sure that all PHP errors are removed from public view
	if (!$arrVar['objErrorHandler']->blnDebug || $arrVar['objErrorHandler']->intDebugLevel < 10000)
	{
		if ($_SERVER['SERVER_ADDR'] == '88.208.230.14' || $_SERVER['SERVER_ADDR'] == '95.138.168.25' || $_SERVER['SERVER_ADDR'] == '127.0.0.1')
			error_reporting(E_ALL^E_NOTICE);
		else
			error_reporting(0);
	}
	else
	{
		error_reporting(E_ALL^E_NOTICE);
	}
	
	// Lets register our own error handler and shutdown function
	set_error_handler('_funErrorHandler');
	register_shutdown_function('_funCleanShutdown');
	
	/**
	* Acts as the error handler for the system
	* 
	* This function should never be called direct!
	* 
	* @param int $intError The type of error
	* @param string $txtError The error message
	* @param string $txtFile The file in which the error occurred
	* @param int $intLine The line in the file in which the error occurred
	* @return boolean
	* @uses clsErrorHandler::$blnDebug
	* @uses clsErrorHandler::$arrDebug
	* @uses clsErrorHandler::funSetCleanExit()
	* @uses clsErrorHandler::$txtErrorEmailsTo
	*/
	function _funErrorHandler($intError, $txtError, $txtFile, $intLine)
	{
		global $arrVar;
        global $php_errormsg;

		// An array of error types and strings
		$arrErrors = array(
							E_ERROR				=>	'E_ERROR',					// Not handled unless called through trigger_error
							E_WARNING			=>	'E_WARNING',
							E_PARSE				=>	'E_PARSE',					// Not handled unless called through trigger_error
							E_NOTICE			=>	'E_NOTICE',
							E_CORE_ERROR		=>	'E_CORE_ERROR',				// Not handled unless called through trigger_error
							E_CORE_WARNING		=>	'E_CORE_WARNING',			// Not handled unless called through trigger_error
							E_COMPILE_ERROR		=>	'E_COMPILE_ERROR',			// Not handled unless called through trigger_error
							E_COMPILE_WARNING 	=>	'E_COMPILE_WARNING',		// Not handled unless called through trigger_error
							E_USER_ERROR		=>	'E_USER_ERROR',
							E_USER_WARNING		=>	'E_USER_WARNING',
							E_USER_NOTICE		=>	'E_USER_NOTICE',
							E_STRICT			=>	'E_STRICT',					// Not handled if the error is in or before this file is called
							E_RECOVERABLE_ERROR	=>	'E_RECOVERABLE_ERROR'
						);
                
                if(defined(E_DEPRECATED)){
                    $arrErrors[E_DEPRECATED] = 'E_DEPRECATED';
                }

        //for legacy support where get_error_last() isn't defined
        $php_errormsg = $txtError;
		
		// First we'll generate the email subject
		$txtSubject = $_SERVER['SERVER_NAME'] . '(' . $_SERVER['SERVER_ADDR'] . ') ' . $arrErrors[$intError];
		// Next we'll generate the email body containing the error information
		$txtBodyDebug = "The following error has occurred:\n\n\tError: \t" . $arrErrors[$intError] . "\n\tFile:\t" . $txtFile . "\n\tLine:\t" . $intLine . "\n\tMessage:" . $txtError . "\n\n" . $arrVar['objErrorHandler']->funReturnDebugTrace(2) . "\n\tDebug:\n\$_SERVER: " . serialize($_SERVER) . "\n\$arrWorking\n" . (array_key_exists('arrWorking',$GLOBALS['arrVar']) ? serialize($GLOBALS['arrVar']['arrWorking']) : '');
		// Next we generate an md5 hash of the subject and body to make sure that we don't keep reporting the same error
		$txtErrorMd5 = md5($txtSubject . $txtBodyDebug);

		// Now we'll decide what to do with the error
		switch ($intError)
	    {
			case E_USER_ERROR:	// This is fatal, we cannot carry on from this
				if ($arrVar['objErrorHandler']->blnDebug)
				{
					$arrVar['objErrorHandler']->arrDebug[] = $txtBodyDebug;
					$arrVar['objErrorHandler']->funSetCleanExit();
					die();
				}
				else
				{
					ini_set('sendmail_from', $arrVar['objErrorHandler']->txtErrorEmailsTo);
					mail($arrVar['objErrorHandler']->txtErrorEmailsTo, $txtSubject, $txtBodyDebug, 'From: ' . $arrVar['objErrorHandler']->txtErrorEmailsTo, '-f' . $arrVar['objErrorHandler']->txtErrorEmailsTo);
					$arrVar['objErrorHandler']->funSetCleanExit();
					die();
				}
				break;
			case E_NOTICE:
			case E_STRICT:
			case E_WARNING:
					// We do not want to be informed about every warning as they are non-fatal and cause no problems, so do nothing here
				break;
            case E_DEPRECATED:
                break;
			default:
				if ($arrVar['objErrorHandler']->blnDebug)
				{
					$arrVar['objErrorHandler']->arrDebug[] = $txtBodyDebug;
				}
				else
				{
					// Only send us an error email if we have not already been informed about this error
					if (!empty($arrVar['objErrorHandler']->arrErrorsEmailed[$txtErrorMd5]))
					{
						ini_set('sendmail_from', $arrVar['objErrorHandler']->txtErrorEmailsTo);
						mail($arrVar['objErrorHandler']->txtErrorEmailsTo, $txtSubject, $txtBodyDebug, 'From: ' . $arrVar['objErrorHandler']->txtErrorEmailsTo, '-f' . $arrVar['objErrorHandler']->txtErrorEmailsTo);
						$arrVar['objErrorHandler']->arrErrorsEmailed[$txtErrorMd5] = 1;
					}
				}
				break;
		}

	    /* Don't execute PHP internal error handler */
	    return true;
	}
	
	/**
	* Acts as the shutdown function
	* 
	* This function should never be called direct!
	* 
	* It checks to see if we have shutdown properly, and if not produces a friendly error message.
	* It also handles the printing of the debug
	* 
	* @return boolean
	* @uses clsErrorHandler::$blnCleanExit
	* @uses clsErrorHandler::funPrintDebug()
	* @uses clsErrorHandler::$blnDebug
	* @uses clsErrorHandler::funDebug()
	* @uses clsErrorHandler::$txtErrorEmailsTo
	*/
	function _funCleanShutdown()
	{
		global $arrVar;
        global $php_errormsg;

        // weird hack because of the over sensitive nature of the error handler.
        if(stripos($php_errormsg,"Undefined offset") !== false){
            return true;
        }
		
		// If the clean exit flag is true then the system exited as expected
		// so we just call the print debug function and return
		if ($arrVar['objErrorHandler']->blnCleanExit)
		{
			$arrVar['objErrorHandler']->funPrintDebug();
			return true;
		}

		// An error has occurred, so we need to decide what to show based on whether
		// or not debugging is enabled
		if ($arrVar['objErrorHandler']->blnDebug)
		{
			print '<p>A fatal error occurred and the system was unable to exit gracefully</p>';
			$arrVar['objErrorHandler']->funDebug((function_exists('error_get_last') ? error_get_last() : isset($php_errormsg) && !empty($php_errormsg) ? $php_errormsg: ''));
            $arrVar['objErrorHandler']->funDebug("STACK:\t".print_r((function_exists('debug_backtrace') ? debug_backtrace() : get_required_files()),true));
			$arrVar['objErrorHandler']->funPrintDebug();
		}
		else
		{
			print '
				<html>
					<head>
						<title>Site Experiencing Technical Difficulties</title>
					</head>
					<body>
						<h1>Site Experiencing Technical Difficulties</h1>
						<p>Sorry, the site is currently experiencing technical difficulties. It may be that the error was only temporary and a simply refreshing this page will return you to what you were doing.</p>
						<p>An email has already been sent informing us of the problem and we hope to have it resolved as quickly as possible.</p>
					</body>
				</html>
			';

			ini_set('sendmail_from', $arrVar['objErrorHandler']->txtErrorEmailsTo);

            $strErrorEmailMessage = 'A fatal error occurred when accessing the following URL: ' . $_SERVER['REQUEST_URI'] .
                "\n\n" . (function_exists('error_get_last') ? error_get_last() :
                                        isset($php_errormsg) && !empty($php_errormsg)? $php_errormsg: '') .
                "\n\tDebug:\n\$_SERVER: " . serialize($_SERVER) .
                "\n\$arrWorking\n" . (array_key_exists('arrWorking',$GLOBALS) ? serialize($GLOBALS['arrWorking']) :
                                        isset($arrVar['arrWorking'])?serialize($arrVar['arrWorking']):'') .
                (!empty($_SESSION)  ?"\n\$_SESSION: " . serialize($_SESSION)    :'').
                (!empty($_GET)      ?"\n\$_GET: ".serialize($_GET)              :'').
                (!empty($_POST)     ?"\n\$_POST: ".serialize($_POST)            :'').
                "\nStackTrace: ".serialize(debug_backtrace());

			mail($arrVar['objErrorHandler']->txtErrorEmailsTo,'Fatal error has occurred on ' . $_SERVER['SERVER_NAME'] . '(' . $_SERVER['SERVER_ADDR'] . ')',$strErrorEmailMessage,'From: ' . $arrVar['objErrorHandler']->txtErrorEmailsTo,'-f' . $arrVar['objErrorHandler']->txtErrorEmailsTo);
		}
		
		return true;
	}
?>