<?php

require_once("../conf/conf_fnc.php");

	$run = "_cmd_git_branch"; //realpath("../../cmd/_cmd_git_branch");
	$cmd1 = "$run /c git branch -a > ".$log_path."branches.txt";
	$cmd2 = "$run /c git branch > ".$log_path."branches2.txt";

	run_in_bg($cmd1, 0, true);
	run_in_bg($cmd2, 0, true);

	$branches_reqest = file($log_path."branches.txt");
	$branches_exist = file($log_path."branches2.txt");
	$branch_list = ";".implode(";", $branches_exist);

	foreach ($branches_reqest as $branch)
	{
		$branch_name = trim($branch, "\n");
		
		if (!strpos($branch_list, $branch_name))
		{
			echo "Adding branch:\t".$branch_name."\n";
			$cmd = "$run /c git branch ".$branch_name;
			run_in_bg($cmd, 0, true);
		}
		else
		{
			echo "Branch exist:\t".$branch_name."\n";
		}
	}
	
?>