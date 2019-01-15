<?php
	$product = isset($_GET['product']) ? $_GET['product'] : '';
	$branch  = isset($_GET['branch'])  ? $_GET['branch']  : '';
	$testid = isset($_GET['testid']) ? $_GET['testid'] : '';
	
	if (!empty($testid))
	{
		require_once("tools.php");
		$db_path = autotester_db_path();
		$db      = DB_open($db_path);
		$removed = remove_autotester_test($db, $testid);
			
		if ($removed)
		{
			assert($removed == 1);
			echo header("Refresh: 2; url=../_AT/autotester_list.php?product={$product}&branch={$branch}");  
			echo "<b>TEST REMOVED</b><br/><br/>";
			return;
		}
	}
	
	echo "<b>REMOVE FAILED</b><br/><br/>";
	echo "<a href='../_AT/autotester_list.php?product={$product}&branch={$branch}'>back</a>";
?>