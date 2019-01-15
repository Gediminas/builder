<body>

<?php

require_once("../conf/conf_fnc.php");

$cmd_dir = "../commands";

if (!is_dir("$cmd_dir"))
{
	echo "ERROR: Commands folder does not exist [$cmd_dir]";
}

echo "<b>Available commands:</b><br>\n<br>\n";

$php_files = glob("$cmd_dir/*.php");
sort($php_files);

echo "<table border=1> 	<TR> <TH width=95 align=left> <TH align=left> <TH align=left> <TH align=left>";
foreach($php_files as $php_file) //if (0 < strlen($php_file))
{
	$php_name = basename($php_file);
	$php_name = trim($php_name);
	$command  = str_replace('.php', '', $php_name);
	
	if ("prepareat.php" == $php_name)
		continue;

	echo "<tr>\n";
	echo "<td>\n";
	echo "<a href='show_file.php?fname=$php_file'>$command</a>";
	echo "</td>\n";

	echo "<td>" . get_help("$php_file", 1) . "</td>\n";
	echo "<td>" . get_help("$php_file", 2) . "</td>\n";
	echo "<td>" . get_help("$php_file", 3) . "</td>\n";
}


echo "<tr>\n";
echo "<td>></td>\n";
echo "<td>> some batch command 1<br/>> some batch command 2<br/>> some batch command 3</td>\n";
echo "<td>-</td>\n";
echo "<td> Execute batch command(s)<br/>Adjacent commands are collected to one block, *.cmd file is generated and then executed</td>\n";

echo "</table>";
	
?>

</body>

<!---------------------------------------------------------------------------->
<!---------------------------------------------------------------------------->
<!---------------------------------------------------------------------------->

<?php

function get_help($cmd_file, $part)
{
	$help  = "";
	$lines = file($cmd_file);
	$started = false;
	
	foreach ($lines as $line)
	{
		if (1 == strpos('_' . $line, "//*$part"))
		{
			$line = htmlentities(str_replace("//*$part", '', $line));
			
			if (0 == strlen($line))
				$line = "<br/>\n";
				
			$help  .= $line;
			$help  .= "<br/>\n";
		}
	}
	
	return (0 < strlen($help)) ? $help : "-";
}

?>