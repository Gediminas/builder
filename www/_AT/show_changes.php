<?php
require_once("../common/header.php");
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : '';
$hash       = isset($_GET['hash'])       ? $_GET['hash']       : '';
?>

<style>
  .fixedfont { font-family:monospace }
</style>

<html>
<body>
<div class="fixedfont">

<?php
echo "hash=$hash<br/>\n<br/>\n";

$src_dir  = get_src_dir($product_id);
$ansi_exe = realpath('../../bin/AnsiFilter/ansifilter.exe');

assert(!empty($ansi_exe));
assert(!empty($src_dir));
assert(!empty($hash));

$convert = "echo str_replace('	', '~~~~TAB~~~~', str_replace(' ',    '~~~~SPC~~~~', fread(STDIN, 999999))) . PHP_EOL;";
exec("git --git-dir=\"$src_dir\\.git\" show --color --format=fuller $hash | php -R \"$convert\" | $ansi_exe --html --fragment", $rows);

$nr = -1;
$header = true;

foreach ($rows as $row)
{
	$row = str_replace('~~~~SPC~~~~', '&nbsp;', $row);//n
	$row = str_replace('~~~~TAB~~~~', '	&nbsp;&nbsp;&nbsp;&nbsp;', $row);//n
	
	if (4 < ++$nr && $header)
		$row = CorrectUserComment($row);

	//$row = str_replace('color:#800000', 'background-color:red', $row);//-
	//$row = str_replace('color:#008000', 'background-color:green', $row);//+
	//$row = str_replace('color:#000000', 'background-color:silver;font-weight:bold', $row);//n

	if (stripos($row, '">index&nbsp;'))
		continue;

	if (stripos($row, '">diff&nbsp;--git&nbsp;'))
	{
		echo "<br/><hr><br/>\n";
		$header = false;
		continue;
	}
	
	if (stripos($row, 'new&nbsp;file&nbsp;mode'))
	{
		$row = str_replace('background-color:#ffffff', 'background-color:green', $row);//-
	}
	elseif (stripos($row, 'deleted&nbsp;file&nbsp;mode'))
	{
		$row = str_replace('background-color:#ffffff', 'background-color:red', $row);//-
	}
	elseif (stripos($row, '">---&nbsp;') || stripos($row, '">+++&nbsp;'))
	{
		$row = str_replace('background-color:#ffffff', 'background-color:yellow', $row);//-
	}
	else
	{
		$row = str_replace('background-color:#ffffff', '', $row);
		$row = str_replace('color:#800000', 'color:red; text-decoration:line-through;', $row);//-
		$row = str_replace('color:#008000', 'color:green; font-weight:bold', $row);//+
		//$row = str_replace('color:#000000', 'background-color:silver; font-weight:bold', $row);//n
	}

	echo "$row<br/>\n";
}

?>

</div>
</body>
</html>