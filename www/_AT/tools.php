<?php

	require_once("../conf/conf_fnc.php");

	class CTest
	{
		public $id;
		public $test_started;
		public $build_started;
		public $product;
		public $branch;
		public $build;
		public $total_errors;
		public $tests_failed;
		public $tests_count;
		public $XML;
		public $user_comment;
	};

	class CTestDetails
	{
		public $id;
		public $fk_test;
		public $errors;
		public $main_group;
		public $sub_group;
		public $project;
	};

	function sql_check($sql_query, $sql_result)
	{
		//if (!$sql_result)
		//{
		//	die('Invalid query: ' . $sql_query);
		//}
		
		//if (mysql_num_rows($sql_result) == 0)
		//{
		//	echo "No record found\n"; return;
		//}
	}

	function SQL_single_query($dbHandle, $sql_query)
	{
echo "SQL_single_query\n";
		$sql_result = DB_query($dbHandle, $sql_query);
		sql_check($sql_query, $sql_result);
		$rez = (mysql_fetch_row($sql_result)); 
		return $rez[0];
	}

	function get_product_test_info_by_id($db, $id) 
	{
		$sql_query = "SELECT id, build_started, test_started, product, branch, build, total_errors, tests_failed, tests_count, XML, user_comment FROM tests  WHERE id='$id' ORDER BY build_started DESC LIMIT 1";
		$sql_result = DB_query($db, $sql_query);
		//sql_check($sql_query, $sql_result);
		
		$info = $sql_result[0];

		$test = new CTest;
		$test->id            = $info[0];
		$test->build_started = $info[1];
		$test->test_started  = $info[2];
		$test->product       = $info[3];
		$test->branch   = $info[4];
		$test->build         = $info[5];
		$test->total_errors  = $info[6];
		$test->tests_failed  = $info[7];;
		$test->tests_count   = $info[8];
		$test->XML           = $info[9];
		$test->user_comment  = $info[10];
		
		if (empty($test->user_comment))
			$test->user_comment = "-";
			
		return $test;
	}
	
	function get_product_last_test_info($dbHandle, $product, $branch) 
	{
		if (empty($branch))
			$sql_query = "SELECT id, build_started, test_started, product, branch, build, total_errors, tests_failed, tests_count, XML, user_comment FROM tests  WHERE product='$product' ORDER BY build_started DESC LIMIT 1";
		else
			$sql_query = "SELECT id, build_started, test_started, product, branch, build, total_errors, tests_failed, tests_count, XML, user_comment FROM tests  WHERE product='$product' AND branch='$branch' ORDER BY build_started DESC LIMIT 1";
		$sql_result = DB_query($dbHandle, $sql_query);
		//sql_check($sql_query, $sql_result);
		
		$info = $sql_result[0];

		$test = new CTest;
		$test->id            = $info[0];
		$test->build_started = $info[1];
		$test->test_started  = $info[2];
		$test->product       = $info[3];
		$test->branch   = $info[4];
		$test->build         = $info[5];
		$test->total_errors  = $info[6];
		$test->tests_failed  = $info[7];;
		$test->tests_count   = $info[8];
		$test->XML           = $info[9];
		$test->user_comment  = $info[10];
		
		if (empty($test->user_comment))
			$test->user_comment = "-";
			
		return $test;
	}
	
	function get_product_test_info($dbHandle, $product, $branch) 
	{
		if (empty($branch))
			$sql_query = "SELECT id, build_started, test_started, product, branch, build, total_errors, tests_failed, tests_count, XML, user_comment FROM tests  WHERE product='$product' ORDER BY id";
		else
			$sql_query = "SELECT id, build_started, test_started, product, branch, build, total_errors, tests_failed, tests_count, XML, user_comment FROM tests  WHERE product='$product' AND branch='$branch' ORDER BY id";
		$sql_result = DB_query($dbHandle, $sql_query);
		//sql_check($sql_query, $sql_result);
		
		$tests = Array();
		foreach ($sql_result as $info)
		{
			$test = new CTest;
			$test->id            = $info[0];
			$test->build_started = $info[1];
			$test->test_started  = $info[2];
			$test->product       = $info[3];
			$test->branch        = $info[4];
			$test->build         = $info[5];
			$test->total_errors  = $info[6];
			$test->tests_failed  = $info[7];;
			$test->tests_count   = $info[8];
			$test->XML           = $info[9];
			$test->user_comment  = $info[10];

			if (empty($test->user_comment))
				$test->user_comment = "-";
				
			$tests[$test->id] = $test;
		}
		
		krsort($tests);
		return $tests;
	}
	
	function get_products($dbHandle, $grouped) 
	{
		$sql_query = $grouped	? "SELECT product, ''     FROM tests GROUP BY product"
								: "SELECT product, branch FROM tests GROUP BY product, branch";
		$sql_result = DB_query($dbHandle, $sql_query);
		//sql_check($sql_query, $sql_result);

		$results = Array();
		foreach($sql_result as $result)
		{
			$results[] = $result[0] . "###" . $result[1];
		}
		sort($results);
		return $results;
	}

	function remove_autotester_product($db, $product, $branch) 
	{
		DB_query($db, "VACUUM tests");
		DB_query($db, "VACUUM tests_details");
		
		$sql     = "SELECT id FROM tests WHERE product='{$product}' AND branch='{$branch}'";
		$result  = $db->query($sql);
		$records = $result->fetchAll();
		$ids     = '';
		foreach ($records as $record)
		{
			$id = $record[0];
			$ids .= empty($ids) ? $id : ", {$id}";
		}
	
		$sql = "DELETE FROM tests_details  WHERE fk_test IN ($ids)";
		$result = $db->exec($sql);
		
		ASSERT(0 < $result);
		if (0 < $result)
		{
			$sql = "DELETE FROM tests WHERE id IN ($ids)";
			$result = $db->exec($sql);
			assert(0 < $result);
		}
		
		return $result;
	}

	function remove_autotester_test($db, $test_id) 
	{
		DB_query($db, "VACUUM tests");
		DB_query($db, "VACUUM tests_details");
		
		$sql_query  = "SELECT * FROM tests_details WHERE fk_test=$test_id";
		$sql_result = DB_query($db, $sql_query);
		$sql_count  = count($sql_result);
		if ($sql_count) {
			$sql_query = "DELETE FROM tests_details WHERE fk_test = {$test_id}";
			$sql_result = $db->exec($sql_query);
			if (!$sql_result) {
				echo "ERROR: Delete failed from table 'tests_details' [{$sql_query}]<br />";
				return 0;
			}
		}

		$sql_query  = "SELECT * FROM tests WHERE id = {$test_id}";
		$sql_result = DB_query($db, $sql_query);
		$sql_count  = count($sql_result);
		if ($sql_count) {
			$sql_query = "DELETE FROM tests WHERE id = {$test_id}";
			$sql_result = $db->exec($sql_query);
			if (!$sql_result) {
				echo "ERROR: Delete failed from table 'tests' [{$sql_query}]<br />";
				return 0;
			}
		}
		
		return 1;
	}

	function DB_open ($dbName)
	{
		if (!is_file("$dbName"))
			die("ERROR: Database does not exist [$dbName]");
		
		try
		{
			$dbHandle = new PDO('sqlite:'.$dbName);
		}
		catch( PDOException $exception )
		{
			die($exception->getMessage());
		}

		if (!$dbHandle)
			die("ERROR: Unable to open database [$dbName]");
			
		return $dbHandle;
	}

	function DB_query ($dbHandle, $cmd)
	{
		if (!$dbHandle)
			die("ERROR: Database is not opened");

		$result = $dbHandle->query($cmd);
		return $result->fetchAll(); // store result in array
	}

	function DB_query_one ($dbHandle, $cmd)
	{
		if (!$dbHandle)
			die("ERROR: Database is not opened");

		$result = $dbHandle->query($cmd);
		$result = $result->fetch();
		return $result[0]; // store result in array
	}

	function menu_links($at)
	{
		//main meniu
		echo "<table><tr><td width=300 height=70><image src='http://www.matrix-software.com/nl/images/mxlogo.jpg'></td>\n\n";
		echo " <td><font size='+1'><b>";
		echo "<a href='http://wiki.matrixlt.local/mediawiki/index.php/MxKAutotester'>	[Wiki]		</a> &nbsp;\n";
		echo "<a href='../_Main/index.php?bcomment=comment'>					[MxkBuilder]</a> &nbsp;\n";
		if($at)	echo "<a href='index.php'>	[Autotester]</a> &nbsp;\n";
		echo "</font></b></td></tr>\n</table>\n";
	}
	
	/*
	function GetProjectFilter($project, $group)
	{
		if ($group)
		{
			$product_filter = "$project%";
		}
		else
		{
			$product_filter = "$project (%)";
			$product_filter = str_replace('[', '(%) ', $product_filter);
			$product_filter = str_replace('] (%)', '', $product_filter);
			$product_filter = str_replace('  ', ' ',   $product_filter);
		}
		
		return $product_filter;
	}
	*/

	function diff($time1, $time2, &$commits_added, &$commits_removed, &$path1, &$path2)
	{
		$commits_added = $commits_removed = Array();
		$client_data   = client_data_dir();
		$path1         = empty($time1)? '' : "{$client_data}\\git_log\\{$time1}_git.log";
		$path2         = empty($time2)? '' : "{$client_data}\\git_log\\{$time2}_git.log";
		
		if (empty($time1) || empty($time2) || !is_file($path1) || !is_file($path2))
			return false;

		$f1 = file($path1, FILE_IGNORE_NEW_LINES);
		$f2 = file($path2, FILE_IGNORE_NEW_LINES);
		$commits_added   = array_diff($f2, $f1);
		$commits_removed = array_diff($f1, $f2);
		$added_count     = count($commits_added);
		
		for ($i = 0; $i < $added_count; $i++)
			array_pop($commits_removed);
		
		return true;
	}
?>