<?php
	$all     = isset($_GET['all'])     ? $_GET['all']     : 0;
	$product = isset($_GET['product']) ? $_GET['product'] : '';
	$branch  = isset($_GET['branch'])  ? $_GET['branch']  : '';
	$number  = isset($_POST['number']) ? $_POST['number'] : -1;
	$check   = isset($_POST['check'])  ? $_POST['check']  : -2;
	$remove  = !empty($product) && !empty($branch) && ($number == $check);
	
	//echo header("Refresh: 10; url=../_AT/autotester_list.php?product={$product}&branch={$branch}");  
	echo "product: '{$product}'<br/>";
	echo "branch:  '{$branch}'<br/>";
	echo "number:  {$check} (should be {$number})<br/><br/>";

	if ($remove) 
	{
		echo "<b>REMOVING</b><br/>";
		require_once("tools.php");

		$db_path = autotester_db_path();
		$db      = DB_open($db_path);
		$removed = remove_autotester_product($db, $product, $branch);
		
		if ($removed)
		{
			echo "<b>REMOVED {$removed} TEST(S)</b><br/><br/>";
			echo "<a href='../_AT/index.php?group=0'>back</a>";
			return;
		}
	}

	echo "<b>REMOVE FAILED</b><br/><br/>";
	echo "<a href='=../_AT/autotester_list.php?product={$product}&branch={$branch}'>back</a>";
?>