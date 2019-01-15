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

require_once("../_AT/tools.php");

$times = Array();
foreach($_POST as $param => $value) if ($value == 'on')
	$times[] = $param;
rsort($times);

$compare = 1 < count($times);
$build_time1 = $build_time2 = '';

foreach($times as $time)
{
	$build_time2 = $build_time1;
	$build_time1 = str_replace(':', '-', $time);

	if ((empty($build_time1) || empty($build_time2)) && $compare)
		continue;
		
	$ok = diff($build_time1, $build_time2, $commits_added, $commits_removed, $path1, $path2);
	$time1 = str_replace('_', ' ', $build_time1);
	$time2 = str_replace('_', ' ', $build_time2);

	if (is_file($path2)) echo "<a href='../_Main/show_file.php?fname=$path2'>$time2</a>";
	else                 echo "$time2";
//	echo ": " . $comment = CorrectUserComment($test2->user_comment) . "<br/>";
	echo "<br/>";

	if (is_file($path1)) echo "<a href='../_Main/show_file.php?fname=$path1'>$time1</a>";
	else                echo "$time1";
//	echo ": " . $comment = CorrectUserComment($test1->user_comment) . "<br/>";
	echo "<br/>";

	if (!$ok)
	{
		if (!is_file($path1) || !is_file($path2))
		{
			echo "N/A";
		}
		elseif (is_file($path1))
		{
			$commits_added = file($path1, FILE_IGNORE_NEW_LINES);
		}
		else
		{
			echo "N/A";
		}
	}
	else if (empty($commits_added) && empty($commits_removed))
		echo "no changes";

	echo "<table border=1 cellpadding=0 cellspacing=0>\n";
	foreach ($commits_added as $commit)
	{
		$prev_date = isset($time_a[0]) ? $time_a[0] : '';
		
		$tmp    = explode(' | ', $commit);
		$time_a = explode(' ', $tmp[1]);
		$comment = CorrectUserComment($tmp[3]);
		$hash_link = "<a href='../_AT/show_changes.php?hash={$tmp[0]}'><code>{$tmp[0]}</code></a>";

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

?>