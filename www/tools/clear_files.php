<?php

include("../tools/file_tools.php");
$path = "\\\\ftp\\ftproot\\MxKozijnPlastic\\Version_Current";

	if ($argv[1])
	{
		$path = $argv[1];
		echo "got params: $path\n";
	}

	echo "listing files:\n";
	$list = file_list($path, "7z");

	$files = explode(';', $list);
	
	echo "\n\n\n analyzing dir: $path\n*****************\n";
	foreach ($files as $file) if (strlen($file)>3)
	{
		echo "$file: \t";
		$fileDate = getFileDate($path."\\".$file);
		//echo $fileDate;
		if (needDelete($fileDate, 2))
		{
			unlink($path."\\".$file);
			echo " \tREMOVE\n";
		}
		else
		{
			echo " \tkeep\n";
		}
	}
?>