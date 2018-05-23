<?php

Parameters::processThem($argv);

// Help is displayed
if(Parameters::$help) {
	show_help();
	exit(0);
}

// Get input and check json format for errors, then decode
$input = readTheFile(Parameters::$inputFile);
checkJson($input);
$input = json_decode($input);


// Header using based on parameters
$header = Parameters::$head;

// Creating xmlElem class and saving to output
$result = new xmlElem(Parameters::$root, $input);
$final = $header.$result->buildIT();
writeTheFile(Parameters::$outputFile, $final);


// Print help function
function show_help() {
	echo "
-input=filename	input file in JSON format with UTF-8 encoding

--output=filename	output XML text file

-h=subst	replace invalid chars

-n	do not generate XML header

-r=root-element		name of root element

--array-name=array-element	set custom name of element around array

--item-name=item-element	set custom name of items in array, default value is item

-s	values of type string will be transformed to text elements, instead attributes

-i	values of type number will be transformed to text elements, instead attributes

-l	values of literals (true, false, null) will be transformed to text elements, instead attributes

-c	turn on translation of problematic characters

-a, --array-size	in array element add attribute size with number of items in array

-t, --index-items	every item of array will have index attribute with its number

--start=n	initialisation of incremental counter for array items indexing (needs to be combined with --index-items,
or error 1 will occur) to integer number >= 0 (default value n = 1).

--types	element of every scalar value will contain an attribute with type of value ,f.e. integer, real, string or literal.\n";
}

/*
 * Handle errors
 * @param string
 * @param int
 */
function err($msg = "", $errcode) {
	file_put_contents('php://stderr', $msg." Error code $errcode.\n");
	die($errcode);
}

/*
 * Check json for errors
 * @param string
 */
function checkJson($string) {
	// Delete whitespaces
	$string = trim($string);
	
	$temp = json_decode($string);
	if ($string !== "null" && is_null($temp)) {
		err("JSON is not in valid format", 4);
	}
	
	// File is empty
	$tmp = (array) $temp;
	if (empty($tmp)) {
		$vypis = "";
		if (Parameters::$header)
			$vypis .= Parameters::$head;
		if (Parameters::$root)
			$vypis .= "<".Parameters::$root."/>\n";
		
		writeTheFile(Parameters::$outputFile, $vypis);
		die (0);
	}
}

/*
 * Handle input file
 * @param string
 * @return string
 */ 
function readTheFile($inputFile) {
	if ($inputFile != 'php://stdin' && !is_readable($inputFile))
		err("Can't open input file.", 2);
		
	$input = @file_get_contents($inputFile);
	
	if ($input == false)
		err("Can't open input file.", 2);
		
	return $input;
}

/*
 * Handle output file
 * @param string
 * @param string
 */
function writeTheFile($outputFile, $content) {
	$output = @file_put_contents($outputFile, $content);
	if ($output == false)
		err("Can't open output file.", 3);
}


class Parameters { 
	// Variables for parameter values
	static $help = false;
	static $inputFile = NULL;
	static $outputFile = NULL;
	static $subst = NULL;
	static $header = true;
	static $root = NULL;
	static $arrayName = NULL;
	static $itemName = NULL;
	static $string = false;
	static $number = false;
	static $literal = false;
	static $convert = false;
	static $arraySize = false;
	static $index = false;
	static $start = NULL;
	static $types = false;
	static $head = "";
	static $padding = false;
	
	// Incompatible characters for XML format in regex
	const incompatibruStart = '/(^[0-9]+|^-)/';
	const incompatibruMid = '/(^-|^[0-9]|\(|\)|{|}|[|]|<|>|\?|\*|\+|\`|\/|\s|;|,|!|&|@|%|=|~|#|")/';
	
	/* 
	 * Function that goes through an array of parameters and 
	 * checks, if they are valid and used in proper way.
	 * @param array
	 */
	public static function processThem($argv) {
		$tempArr = array();
		for($i = 1; $i < count($argv); $i++) {
			$tempArr[$i-1] = $argv[$i];
		}
		
		foreach($tempArr as $param) {
				if (substr($param, 0, 2) === "--")
					$param = substr($param, 1, strlen($param));
				// -help	
				if ($param == "-help") {
					if(self::$help) {
						err("Help was set twice.", 1);
					}
					self::$help = true;
				}
				// -input=
				elseif (substr($param, 0, 7) === "-input=") {
					if(is_null(self::$inputFile))
						self::$inputFile = substr($param, 7, strlen($param));
					else {
						err("Input file was set more than once.", 1);
					}
				}
				// -output=
				elseif(substr($param, 0, 8) === "-output=") {
					if(is_null(self::$outputFile))
						self::$outputFile = substr($param, 8, strlen($param));
					else {
						err("Output file was set more than once.", 1);
					}
				}
				// -h=subst
				elseif(substr($param, 0, 3) === "-h=") {
					if(is_null(self::$subst)) 
						self::$subst = substr($param, 3, strlen($param));
					else {
						err("Substitution was set more than once.", 1);
					}
				}
				// -n
				elseif($param == "-n") {
					if(!self::$header) {
						err("No header was set more than once.", 1);
					}
					else
						self::$header = false;
				}
				// -r=root-element
				elseif(substr($param, 0, 3) === "-r=") {
					if(is_null(self::$root)) 
						self::$root = substr($param, 3, strlen($param));
					else {
						err("Root element was set more than once.", 1);
					}
				}
				// -array-name=
				elseif(substr($param, 0, 12) === "-array-name=") {
					if(is_null(self::$arrayName)) 
						self::$arrayName = substr($param, 12, strlen($param));
					else {
						err("Array name was set more than once.", 1);
					}
				}
				// -item-name=
				elseif(substr($param, 0, 11) === "-item-name=") {
					if(is_null(self::$itemName)) 
						self::$itemName = substr($param, 11, strlen($param));
					else {
						err("Item name was set more than once.", 1);
					}
				}
				// -s
				elseif($param == "-s") {
					if(self::$string) {
						err("String conversion was set more than once.", 1);
					}
					else
						self::$string = true;
				}
				// -i
				elseif($param == "-i") {
					if(self::$number) {
						err("Number conversion was set more than once.", 1);
					}
					else
						self::$number = true;
				}
				// -l
				elseif($param == "-l") {
					if(self::$literal) {
						err("Literal conversion was set more than once.", 1);
					}
					else
						self::$literal = true;
				}
				// -c
				elseif($param == "-c") {
					if(self::$convert) {
						err("XML problematic conversion was set more than once.", 1);
					}
					else
						self::$convert = true;
				}
				// -a, -array-size
				elseif($param == "-a" || $param == "-array-size") {
					if(self::$arraySize) {
						err("Array size was set more than once.", 1);
					}	
					else
						self::$arraySize = true; 
				}
				// -t, -index-items
				elseif($param == "-t" || $param == "-index-items") {
					if(self::$index) {
						err("Array indexing was set more than once.", 1);
					}	
					else
						self::$index = true; 
				}
				// -start=n
				elseif(substr($param, 0, 7) === "-start=") {
					if (is_null(self::$start)) {
						self::$start = substr($param, 7, strlen($param));
						if (self::$start < 0 || !is_numeric(self::$start)) {
							err("Wrong value for array indexing was set.", 1);
						}
					}
					else {
						err("Start of array indexing was set more than once.", 1);
					}
				}
				// -types
				elseif($param == "-types") {
					if (self::$types) {
						err("Types were set on more than once.", 1);
					}
					else
						self::$types = true;
				}
				// --padding
				elseif($param == "-padding") {
					if (self::$padding) {
						err("Padding set more than once.", 1);
					}
					else
						self::$padding = true;
				}
			}
			
			// Missing parameter --index-items
			if(!self::$index && !is_null(self::$start)) {
				err("Start index was set but missing --index-items.", 1);
			} 
			// Help not alone
			if(self::$help && count($argv) > 2) {
				err("Help was not as only one parameter.", 1);
			}
			
			// Padding without --index-items
			if(self::$padding && !self::$index) {
				err("Padding was set without --index-items.", 1);
			}
			
			if (is_null(self::$inputFile))
				self::$inputFile = 'php://stdin';	
			if (is_null(self::$outputFile))
				self::$outputFile = 'php://stdout';
			if (is_null(self::$arrayName))
				self::$arrayName = 'array';
			if (is_null(self::$itemName))
				self::$itemName = 'item';
			if (is_null(self::$start))
				self::$start = 1;
			if (is_null(self::$subst))
				self::$subst = '-';
			
			/*
			 * Function checks, if its argument is a valid XML name
			 * @param string
			 */
			function nameValidCheck($name) {
				if (preg_match(Parameters::incompatibruMid, $name, $out)) {
					err("Invalid element name.", 50);
				}
			}
			
			nameValidCheck(self::$root);
			nameValidCheck(self::$arrayName);
			nameValidCheck(self::$itemName);
			
			if (self::$header)
				self::$head .= '<?xml version="1.0" encoding="UTF-8"?>'."\n";
	}
}

class xmlElem {
	private $elemValue;
	private $elemTag;
	private $isArr = false;
	private $auxArray; // Array for storing keys and values if needed
	
	/* 
	 * Constructor of XML element. It decides if the element is an object,
	 * simple value, or an array, and calling the class recursively if needed,
	 * @param	string
	 * @param	mixed
	 */
	public function __construct($elemTag, $elemValue) {
		$this->elemTag = $elemTag;
		$this->processElemValue($elemValue);
	}
	
	/*
	 * Build XML structure
	 * @return string
	 */
	public function buildIT() { 
		$outIT = "";
		// Element value is not an array
		if (!is_array($this->elemValue)) {
			if ((is_null($this->elemValue) || is_bool($this->elemValue)) && Parameters::$literal) {
				$outIT .= "<".$this->getTag($this->elemTag);				
				if (count($this->auxArray) != 0) 
					foreach ($this->auxArray as $k => $v)
						$outIT .= " ".$k."=\"".$v."\"";
				$outIT .= (Parameters::$types ? " type=\"".$this->getTypeName($this->elemValue)."\"" : "");			
				$outIT .= "><".$this->getValue($this->elemValue)."/></".$this->getTag($this->elemTag).">\n";
			}
			elseif ((Parameters::$number && (is_float($this->elemValue) || is_int($this->elemValue)))||
				 (Parameters::$string && is_string($this->elemValue))) {
				$outIT .= "<".$this->getTag($this->elemTag);				
				if (count($this->auxArray) != 0) 
					foreach ($this->auxArray as $k => $v)
						$outIT .= " ".$k."=\"".$v."\"";	
				$outIT .= (Parameters::$types ? " type=\"".$this->getTypeName($this->elemValue)."\"" : "");				
				$outIT .= ">".$this->getValue($this->elemValue)."</".$this->getTag($this->elemTag).">\n";
			}
			// Element value is an item only
			else {
				$outIT .= "<".$this->getTag($this->elemTag);					
					if (count($this->auxArray) != 0) 
						foreach ($this->auxArray as $k => $v)
							$outIT .= " ".$k."=\"".$v."\"";
					
					$outIT .= " value=\"".$this->getValue($this->elemValue)."\"";
					$outIT .= (Parameters::$types ? " type=\"".$this->getTypeName($this->elemValue)."\"" : "")."/>\n";
			}
		}
		// Element value is an array 
		else {
			if (!is_null($this->elemTag)) {
				$outIT .= "<".$this->getTag($this->elemTag);
				if (count($this->auxArray) != 0) 
					foreach ($this->auxArray as $k => $v)
						$outIT .= " ".$k."=\"".$v."\"";		
				$outIT .= ">\n";
			}
			if ($this->isArr) {
				$outIT .= "<".$this->getTag(Parameters::$arrayName);
				if (Parameters::$arraySize)
					$outIT .= " size=\"".count($this->elemValue)."\"";
				$outIT .= ">\n";					     
			}		     
			foreach($this->elemValue as $d) 
				$outIT .= $d->buildIT();
			if ($this->isArr)
				$outIT .= "</".$this->getTag(Parameters::$arrayName).">\n";
			if (!is_null($this->elemTag))
				$outIT .= "</".$this->getTag($this->elemTag).">\n";
		}
		return $outIT;
	}
	
	/*
	 * Process elementValue based on its type
	 * @param mixed
	 */
	private function processElemValue($elemValue) {
		// It is an object
		if (is_object($elemValue)) 
			foreach($elemValue as $key => $value)
				$this->elemValue[] = new xmlElem($key, $value);
				
		// It is an array
		elseif (is_array($elemValue)) {		
			$this->isArr = true;
			$index = Parameters::$start;
			
			if(Parameters::$padding)
				$padLength = strlen($index -1 + count($elemValue));
			
			foreach($elemValue as $key => $value) {
				$this->elemValue[] = new xmlElem(Parameters::$itemName, $value);
				if (Parameters::$index) {
					if(Parameters::$padding) {
						$index = str_pad($index, $padLength, '0', STR_PAD_LEFT);
					}
					$this->elemValue[count($this->elemValue)-1]->auxArray["index"] = $index;
				}
				$index++;
			}
		}
		// It is an item
		else
			$this->elemValue = $elemValue;
	}
	
	/*
	 * Get valid element tag
	 * @param  string
	 * @return string
	 */ 
	private function getTag($tag) {
		$tag = preg_replace(Parameters::incompatibruMid, Parameters::$subst, $tag);
		if (preg_match(Parameters::incompatibruStart, $tag, $out)) {
			err("Element name $tag invalid.", 51);
		}
		return $tag;
	}
	
	/*
	 * Get valid element value
	 * @param  string
	 * @return string
	 */ 
	private function getValue($value) {	
		if(is_null($value))
			return "null";
		elseif (is_float($value))
			return floor($value);
		elseif (is_bool($value))
			if ($value)
				return "true";
			else
				return "false";
		else
			if (Parameters::$convert)
				return htmlspecialchars($value);
			else
				return str_replace("\"", "&quot;", $value);
	}
	
	/*
	 * Get valid type of value
	 * @param  string
	 * @return string
	 */ 
	private function getTypeName($value) {
		if(is_null($value) || is_bool($value))
			return "literal";
		elseif (is_float($value) || is_double($value))
			return "real";
		elseif (is_int($value))
			return "integer";
		else
			return "string";
	}		
}
?>