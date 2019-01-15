<?php
function CorrectPath(&$path)
{
	$path = realpath($path);
	$path = trim($path);
	$path = str_replace("\\", "/", "$path"); //common style
	//$path = str_replace("/", "\\", "$path");   //windows style
}

function ValidateFilePath(&$path)
{
	if (!is_file($path)) {
		echo "ERROR: file ['$path'] not found\n";
		return false;
	}

	CorrectPath($path);
	return true;
}

function ValidateFolderPath(&$path)
{
	if (!is_dir($path)) {
		echo "ERROR: folder '$path' not found\n";
		return false;	
	}

	CorrectPath($path);
	return true;
}

function ReadPathsFromFile($_file_path)
{
	$aPath    = array();

	if (!ValidateFilePath($_file_path)) {
		return $aPath;
	}
	
	$root_dir = pathinfo($_file_path, PATHINFO_DIRNAME);
	$aStr 	= file($_file_path);
	
	foreach ($aStr as $i => $str) {
		$str = trim($str);
		if (!empty($str) && false === strpos($str, "//")) {
			$str = $root_dir . "/" . $str;
			if (ValidateFilePath($str)) {
				array_push($aPath, $str);
			}
		}

	}
	
	return $aPath;
}
?>