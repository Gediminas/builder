<?php

if($argc < 6)
{
	echo "ERROR: too few arguments. Need exactly 5.\n";
	return 1;
}

$targetFile = $argv[1];
$option = $argv[2];
$value = $argv[3];
$replace = $argv[4];
$group_code = $argv[5];

echo "Tag: " . $option . " | Value: " . $value . "\n";

$group_beg = "<" . $group_code . ">";
$group_end = "</" . $group_code . ">";

if(($handle = fopen($targetFile, "r")) === FALSE)
{
	echo "ERROR: could not open file for reading: [$targetFile]\n";
	return 2;
}

$iline = fgets($handle);

while(!feof($handle))
{
	if(strpos($iline, "<ItemDefinitionGroup Condition=") !== FALSE)
	{
		$oline[] = $iline;
		$iline = fgets($handle);
		while(strpos($iline, "</ItemDefinitionGroup>") === FALSE)
		{
			if(strpos($iline, $group_beg) !== FALSE)
			{
				$oline[] = $iline;
				$iline = fgets($handle);
				$optfound = false;
				while(strpos($iline, $group_end) === FALSE)
				{
					if(strpos($iline, "<$option>") !== FALSE)
					{
						if($replace == "true")
						{
							$oline[] = "<$option>$value</$option>\n";
						}
						else if($replace == "false")
						{
							if(strpos($iline, $value) === FALSE)
							{
								$value_to_add = $value . ";";
								$insert_pos = strpos($iline, "<$option>") + strlen("<$option>");
								$newstring = substr_replace($iline, $value_to_add, $insert_pos, 0);
								$oline[] = $newstring;
							}
							else
							{
								$oline[] = $iline;
							}
						}
							
						$iline = fgets($handle);
						$optfound = true;
					}
					else
					{
						$oline[] = $iline;
						$iline = fgets($handle);
					}
				}
				if($optfound == false)
				{
					$oline[] = "<$option>$value</$option>\n";
				}
				$oline[] = $iline;
				$iline = fgets($handle);
			}
			else
			{
				$oline[] = $iline;
				$iline = fgets($handle);
			}
		}
	}
	else
	{		
		$oline[] = $iline;
		$iline = fgets($handle);
	}
}
$oline[] = $iline;
fclose($handle);

if(($handle = fopen($targetFile, "w")) === FALSE)
{
	echo "ERROR: could not open file for writing: [$targetFile]\n";
	return 3;
}

for($i = 0; $i < count($oline); ++$i)
{
	if(fwrite($handle, $oline[$i]) === FALSE)
	{
		echo "ERROR: unable to write string to the file.\n";
		return 4;
	}
}
fclose($handle);

?>