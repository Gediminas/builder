<?php
echo "<style type='text/css'>";
echo "table { border-collapse: collapse; }";
echo "td { padding-left:5px; padding-right:5px; }";
echo "td { border-style:dotted; border-color:gray; border-width: 1px; }";
echo "td.d0 { background-color: lightgreen; color: black;   }";

echo "tr.header  td { border-style:solid;  border-color:gray; border-width: 1px; background-color:lightgray; }";
echo "tr.tr_changed td { border-bottom-style:solid; border-bottom-color:black; border-bottom-width: 1px;  }";

//echo "td.d1 { background-color: orange; color: black; }";
//echo "td.d2 { background-color: red; color: black; }";

echo "a.test:link, a.test:visited { text-decoration: none; color:black; }";
echo "a.test:hover           { text-decoration: underline; color:blue; }";

echo "tr:hover td   { background-color: lightblue; }";
echo "tr:hover font { color: black !important; }";
echo "</style>";
		
require_once("tools.php");

if (!isset($_GET['product']))
	die ("ERROR: 'product' parameter is empty");

//menu_links(true);

$product             = $_GET['product'];
$branch              = isset($_GET['branch'])   ? $_GET['branch']   : "";
$show_branch         = empty($branch);
$first_checked_count = 3;
$db                  = DB_open(autotester_db_path());	
$tests               = get_product_test_info($db, $product, $branch);
$product_full        = empty($branch) ? $product : "{$product} [{$branch}]";

echo "<a href='index.php'>back</a>\n";

echo "<form action='autotester_compare.php?product=$product&branch=$branch' method=\"post\">\n";
echo "<center>\n";
echo "<table border=0 ALIGN=center>\n";
echo "<caption><H2>".$product_full."</H2></caption>\n";
echo "<tr class='header' ALIGN='center'>\n";
echo "<td>  <input type='submit' value='Compare'/> </td>\n";
echo "<td> <b> Build start time</b>  </td>\n";
//echo "<td width='170'> <b> Test start time</b>  </td>\n";
echo "<td> <b> Build</b>  </td>\n";
if ($show_branch) echo "<td width='100'> <b>Branch</b> </td>\n";
echo "<td> <b> Errors</b> </td>\n";
echo "<td> <b> Failed tests</b> </td>\n";
echo "<td> <b> User comment </b> </td>\n";
	echo "</tr>\n\n";

//$count = isset($_GET['LogTestShow']) ? $_GET['LogTestShow'] : 0;
//if ($count < 15) $count = 15;

$line_nr  =  0;
$error_group = 0;
$checked_list = Array();

$difErr   = -1;
$difTest1 = -1;
$difTest2 = -1;
foreach (array_reverse($tests) as $test)
{
	$check = (($difErr != $test->total_errors) || ($difTest1 != $test->tests_count) || ($difTest2 != $test->tests_failed));
	$difErr	  = $test->total_errors;
	$difTest1 = $test->tests_count;
	$difTest2 = $test->tests_failed;
	$checked_list[$test->id] = $check;
}

$difErr   = -1;
$difTest1 = -1;
$difTest2 = -1;
$checked_list2 = Array();
foreach ($tests as $test)
{
	$check = (($difErr != $test->total_errors) || ($difTest1 != $test->tests_count) || ($difTest2 != $test->tests_failed));
	$difErr	  = $test->total_errors;
	$difTest1 = $test->tests_count;
	$difTest2 = $test->tests_failed;
	$checked_list2[$test->id] = $check;
}

foreach ($tests as $test)
{
	$check = $checked_list[$test->id];
	$tr_class = $check ? "tr_changed" : "tr_simple";
	$check = $check || $checked_list2[$test->id];
		
	if ($check && 0 < $test->total_errors)
		$error_group = ++$error_group % 2;
		
	$bgcolor = ($test->total_errors == 0) ? 'lightgreen' : '#FF2222';
	
	echo "<tr class='{$tr_class}' ALIGN='center'>";
	
	$checked = $check && 0 <= --$first_checked_count;
	$class  = 'd';
	$class .= ($test->total_errors == 0)
				? 0
				: 1 + $error_group;
				
	$link = "autotester_log.php?id={$test->id}";
	$build_started = $test->build_started;
	if (18 < strlen($build_started))
	{
		$build_started[10] = ' ';
		$build_started[13] = ':';
		$build_started[16] = ':';
	}
	
	if (0 == $test->total_errors)
		$first_checked_count = -1;//END

	if ($checked)
		echo "<td class='{$class}'><input type='checkbox' name='$test->id' checked/></td>\n";
	else 
		echo "<td class='{$class}'><input type='checkbox' name='$test->id' /></td>\n";
	
	
	//echo "<td class='{$class}'><font color='lightgray'>$test->build_started</font></td>\n";
	//echo "<td class='{$class}'><a href='autotester_log.php?id={$test->id}'>".$test->test_started."</a></td>";
	echo "<td class='{$class}'><a class='test' href='$link'>$build_started</a></td>\n";
	
	
	echo "<td class='{$class}'>$test->build</td>\n";
	if ($show_branch) echo "<td class='{$class}' align=left> &nbsp;$test->branch </td>\n";
	echo "<td bgcolor='$bgcolor'> <a class='test' href='$link'>$test->total_errors </a> </td>\n";
	echo "<td bgcolor='$bgcolor'> <a class='test' href='$link'>$test->tests_failed / $test->tests_count </a> </td>";

	$user_comment = CorrectUserComment($test->user_comment);
	echo "<td class='{$class}' align=left> &nbsp;$user_comment </td>\n";
	echo "<form name='job_del' action='../_AT/remove_job.php'>\n";
	echo "<td><nobr><center>";
	echo "<INPUT type='hidden' name='product' value='{$product}'>\n";
	echo "<INPUT type='hidden' name='branch'  value='{$branch}'>\n";
	echo "<INPUT type='hidden' name='testid'  value='{$test->id}'>\n";
	echo "<INPUT type='button' value='X' onclick='submit()'>\n";
	echo "</center></nobr></td>";
	echo "</form>\n";
	echo "</tr>\n\n";
}

echo "</table></center>\n</form>\n";
//echo "<center><a href='autotester_list.php?LogTestShow=600&product={$product}'>Show all</a></center>";

echo      "<hr/> ";

if (!$show_branch)
{
	$number = rand(10,99);
	
	echo    "<form action='remove_product.php?product={$product}&branch={$branch}' method='post'>\n";
	echo 	"enter digit (<b>" . $number . "</b>) to comfirm remove: ";
	echo 	"<INPUT NAME='check' value='' size=1>";
	echo	"<INPUT type='hidden' NAME='number' value='" . $number . "' HIDDEN size=1>";
	echo	"<input type='submit' value='Remove'/>";
	echo      "\n</form> ";
}

?>
