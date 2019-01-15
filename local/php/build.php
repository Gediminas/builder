<?php

function get_build_commands($ide_version, $build_info, $rebuild, $result_file = NULL)
{
	if (!include_once("$ide_version.php"))
	{
		echo "ERROR: Couldn't load $ide_version.php (get_build_command)";
		return NULL;
	}

	$cmds = GetBuildCommands($build_info, $rebuild, $result_file);
	return $cmds;
}

function build($ide_version, $build_info, $rebuild, $result_file)
{
	$cmds = get_build_commands($ide_version, $build_info, $rebuild);

	if (is_null($cmds))
		return;

	foreach($cmds as $cmd) if (0 < strlen($cmd))
	{
		passthru($cmd, $result);
		
		$file = fopen($result_file, 'w');
		$write_rez = fwrite($file, $result . "\r\n");
		if (!$write_rez) echo "ERROR: writing $result to $file \n";
		fclose($file);
		
		echo "\n----------------------------------------------------------------------------\n";
	}
}

?>