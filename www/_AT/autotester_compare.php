<?php
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">\n";
//echo "<title>MxKBuilder " . version() . "</title>\n\n";
echo "<head>\n";
	echo "<meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
	echo "<meta http-equiv=\"content-type\" content=\"text/html;charset=UTF-8\">\n";
	$favicon  = 'favicon.ico';		
	echo "  <link rel='shortcut icon' href='$favicon' type='image/x-icon'>\n";
	echo "  <link rel='icon'          href='$favicon' type='image/x-icon'>\n";
echo "</head>\n\n";

echo "<style type='text/css'>";
//echo "table { border:outset 0px; border:inset 1px; }";
echo "td { padding-left:10px; padding-right:10px; }";
echo "td { border-style:solid; border-color:lightgray; border-width: 1px; }";
echo "tr.removed td{ color:black; text-decoration:line-through; }";
echo "tr.newday td{ border-top-color: black; border-top-width: 1px; }";
echo "</style>";
	
require_once("tools.php");
require_once("../_History/builder_db_history_fnc.php");

$product =  isset($_GET['product']) ? $_GET['product'] : "";;
$branch  =  isset($_GET['branch'])     ? $_GET['branch']     : "";;
$group   =  isset($_GET['group'])   ? $_GET['group']   : "";

if (empty($product))
	return(0);

$ids = Array();
foreach($_POST as $param => $value) if (is_numeric($param) && $value == 'on')
	$ids[] = $param;
sort($ids);
	
menu_links(true); // menu links.

echo "<a href='autotester_list.php?product=$product&branch=$branch&group=$group'>back</a>";
//echo "<form action='autotester_list.php?product=$product&branch=$branch&group=$group' method=\"post\">\n";
//echo "<input type='submit' value='Back'/>\n";
//echo "</form>\n";


$db     = DB_open(autotester_db_path());
$tests  = get_product_test_info($db, $product, $branch, $group);
$details   = get_tests_details($db, $ids);
$row_names = get_row_names($db, $ids);
$table     = cache_table_data($ids, $tests, $details, $row_names);

print_table_from_cache($product, $table, false);

echo "<caption><h2>GIT differences</h2></caption>\n";

$product_id = '';
$test1 = $test2 = NULL;
$build_time1 = '';
$build_time2 = '';
$ids = array_reverse($ids);

foreach ($ids as $id)
{
	$test1 = $test2;
	$test2 = get_product_test_info_by_id($db, $id);
	
	if (empty($test1) || empty($test2))
		continue;
		
	$build_time1 = $test1->build_started;
	$build_time2 = $test2->build_started;
	
	if (empty($product_id))
	{
		$parts = explode('_', $build_time2);
		$date = $parts[0];
		$time = str_replace('-', ':', $parts[1]);

		$result = get_history_product_by_build_started("{$date} {$time}");
		$product_id  = $result['product_id'];
		//echo $product_id."<br/>";
	}

	if (empty($build_time1) || empty($build_time2))
		continue;

	$ok = diff($build_time2, $build_time1, $commits_added, $commits_removed, $path2, $path1);
	$time1 = str_replace('_', ' ', $build_time1);
	$time2 = str_replace('_', ' ', $build_time2);

	if (is_file($path1)) echo "<a href='../_Main/show_file.php?fname=$path1'>$time1</a>";
	else                echo "$time1";
	echo ": " . $comment = CorrectUserComment($test1->user_comment) . "<br/>";

	if (is_file($path2)) echo "<a href='../_Main/show_file.php?fname=$path2'>$time2</a>";
	else                 echo "$time2";
	echo ": " . $comment = CorrectUserComment($test2->user_comment) . "<br/>";

	if (!$ok)
		echo "N/A";
	else if (empty($commits_added) && empty($commits_removed))
		echo "no changes";
	
	echo "<table border=1 cellpadding=0 cellspacing=0>\n";
	foreach ($commits_added as $commit)
	{
		$prev_date = isset($time_a[0]) ? $time_a[0] : '';
		
		$tmp    = explode(' | ', $commit);
		$time_a = explode(' ', $tmp[1]);
		$comment = CorrectUserComment($tmp[3]);
		$hash_link = "<a href='../_AT/show_changes.php?product_id={$product_id}&hash={$tmp[0]}'><code>{$tmp[0]}</code></a>";

		$class = ($prev_date != $time_a[0] && !empty($prev_date)) ? 'newday' : 'std';

		echo "<tr class=$class>\n";
		echo "<td><nobr>$hash_link</nobr></td>";
		echo "<td><nobr>{$time_a[0]}</nobr></td>";
		echo "<td><nobr>{$time_a[1]}</nobr></td>";
		echo "<td><nobr>{$time_a[2]}</nobr></td>";
		echo "<td><nobr>{$tmp[2]}</nobr></td>";
		echo "<td>{$comment}</td>";
		echo "</tr>\n";
	}
	foreach ($commits_removed as $commit)
	{
		$tmp = explode(' | ', $commit);
		$time_a = explode(' ', $tmp[1]);
		$comment = CorrectUserComment($tmp[3]);
		$hash_link = "<a href='../_AT/show_changes.php?hash={$tmp[0]}'><code>{$tmp[0]}</code></a>";

		echo "<tr class='removed'>\n";
		echo "<td><nobr>{$hash_link}</nobr></td>";
		echo "<td><nobr>{$time_a[0]}</nobr></td>";
		echo "<td><nobr>{$time_a[1]}</nobr></td>";
		echo "<td><nobr>{$time_a[2]}</nobr></td>";
		echo "<td><nobr>{$tmp[2]}</nobr></td>";
		echo "<td> <nobr> <font color='gray'>{$comment}</font></nobr></td>";
		echo "</tr>\n";
	}
	echo "</table>\n";

	echo "<br/>";
}

print_table_from_cache($product, $table, true);

echo "<br/><i>Total tests: ".(count($row_names))."</t>";

?>

<?php
class CRowData
{
	public $errors = 0;
	public $cells  = array();
};

function get_table_header($ids, $tests)
{
	$row = new CRowData;
	$row->cells[] = "<td><b>Group</b></td> ";
	$row->cells[] = "<td><b>Test</b></td> ";

	foreach ($ids as $index => $id)
	{
		$test         = $tests[$id];
		$link         = "autotester_log.php?id={$test->id}";
		
		$parts = explode('_', $test->build_started);
		$date = $parts[0];
		$time = str_replace('-', ':', $parts[1]);
		
		//$build_time   = str_replace('_', "</b><br/><a href='{$link}'>", $test->build_started);
		$comment      = $test->user_comment ? CorrectUserComment(substr($test->user_comment, 0, 80)) : "";
		$row->cells[] =
			"<td width='100'>".
			"<b><center><font size='-1'>$date</b>".
			"<br/>".
			"<a href='{$link}'>$time</a></font></center>".
			"<hr/>".
			"<font size='-2'>$comment</font>".
			"</td>";
	}
	
	return $row;
}

function get_row_names($db, $ids)
{
	assert(is_array($ids));

	$id_set = implode(", ", $ids);
	$sql_query   = "SELECT main_group, sub_group, project FROM tests_details WHERE fk_test IN ($id_set) GROUP BY main_group, project, sub_group";
	$records     = DB_query($db, $sql_query);
	sql_check($sql_query, $records);
	
	$row_names = array();
	foreach ($records as $record)
	{
		$mxkb = $record['main_group'];
		$mxkb = str_replace('.mxkbu', '', $mxkb);
		$mxkb = str_replace('.mxkb',  '', $mxkb);
		$row_names[] = $mxkb . '###' . $record['sub_group'] . '###' . $record['project'];
	}
	
	//sort($row_names);
	//$row_names = array_unique($row_names);
	return $row_names;
}

function get_tests_details($db, $ids)
{
	assert(is_array($ids));

	$id_set = implode(", ", $ids);
	$sql_query   = "SELECT id, fk_test, errors, main_group, sub_group, project FROM tests_details WHERE fk_test IN ($id_set)";
	$records     = DB_query($db, $sql_query);
	sql_check($sql_query, $records);
	
	$details = array();
	foreach ($records as $record)
	{
		$det             = new CTestDetails;
		$det->id         = $record[0];
		$det->fk_test    = $record[1];
		$det->errors     = $record[2];
		$det->main_group = $record[3];
		$det->sub_group  = $record[4];
		$det->project    = $record[5];
		
		$det->main_group = str_replace('.mxkbu', '', $det->main_group);
		$det->main_group = str_replace('.mxkb',  '', $det->main_group);
		
		$details[]       = $det;
	}

	//sort($row_names);
	return $details;
}

function cache_table_data($ids, $tests, $details, $row_names)
{
	$col_count       = count($ids);
	$table           = Array();
	$table['header'] = get_table_header($ids, $tests);
	
	foreach ($row_names as $row_name)
	{
		$row_name_a  = explode('###', $row_name);
		$main_group  = $row_name_a[0];
		$sub_group   = $row_name_a[1];
		$project     = $row_name_a[2];
	
		$row_data = new CRowData;
		$row_data->cells[0] =  "<td>{$main_group}</td>";
		$row_data->cells[1] =  "<td><font size='-1'>$project</font>" . "<br /> <font size='-2'><i>'$sub_group'</i></font></td>";

		for ($i=0; $i < $col_count; $i++)
			$row_data->cells[2+$i] = "<td><font color='gray'><center><i>n/a</i></center></font></td>";
	
		$table[$row_name] = $row_data;
	}

	foreach ($details as $detail)
	{
		$row_name  = "{$detail->main_group}###{$detail->sub_group}###{$detail->project}";
		$test_nr = array_search($detail->fk_test, $ids);
		ASSERT($test_nr !== FALSE);
		$table[$row_name]->cells[2+$test_nr] = (0 < $detail->errors)	? "<td bgcolor='#FF4444'><center> $detail->errors </center></td>\n"
																		: "<td bgcolor='#00F000'><center> OK           </center></td>\n";
		$table[$row_name]->errors += $detail->errors;
		$table['header']->errors  += $detail->errors;
	}

	return $table;
}

function print_table_from_cache($product, $table, $all)
{
	if (!$all && 0 == $table['header']->errors)
		return;

	echo "<caption><h2>{$product} (" . ($all ? 'all' : 'errors') . ") </h2></caption>\n";
	echo "<table border=1 cellpadding=0 cellspacing=0>\n";

	foreach($table as $row_data) if ($all || 0 < $row_data->errors)
	{
		echo "<tr>";

		for ($i = 0; $i < count($row_data->cells); $i++)
			echo $row_data->cells[$i];
			
		echo "</tr>\n";
	}			

	echo "</table>";
}

?>