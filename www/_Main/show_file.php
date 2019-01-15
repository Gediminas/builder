<?php
	require_once("../conf/conf_fnc.php");

	if (autoscroll())
		echo header("Refresh: 3");

	include_once("../tools/check_errors.php");
?>

<head>
<script>
	function on_load() {
		<?php
			 if (autoscroll())
			 	echo "setTimeout(window.location='#bottom', 20000);";
		?>
	}
</script>
</head>
<body onload='on_load()'>

<?php

$fname = $_GET['fname'];
    
if (!is_file($fname))
	die("[$fname] does not exist");

if (!is_readable($fname))
	die("[$fname] is not readable");

//The log
ShowFile($fname, true);

//Sub log
$hr = hr();
for ($sub_log_nr = 0; ;$sub_log_nr ++)
{
	$sub_fname = get_sub_log($fname, $sub_log_nr);
	
	if (!is_file($sub_fname))
		break;

	echo "<br/>\n";
	echo "<h3>$hr$hr<br/>[$sub_fname]</h3>";
	echo "<br/>\n";

	if (!is_readable($sub_fname))
	{
		echo "<h3>ACCESSS DENIED</h3>";
		continue;
	}
		
	ShowFile($sub_fname, false);
}

?>

<?php

function ShowFile($fname, $first_line_as_header)
{
	$lines      = file($fname);
	$line_count = count($lines);

	foreach ($lines as $line_num => $line)
	{
		$ignore_errors = false;
		
		if ($first_line_as_header && 0 == $line_num)
		{
			if (stripos('_'.$line, '~'))
				$ignore_errors = true;
				
			//HEADER
			echo "<br/>\n";
			echo "\n\t<font color=\"blue\"><b>" . htmlspecialchars($line) . "</b></font>";
			echo "<br/><br/>\n";
			echo "lines count: " . $line_count . "<br/><br/>\n\n";
		}

		if (1 == strpos("_" . $line, "20"))
		{
			$tmp1    = explode(': ', $line."", 2);
			$time    = isset($tmp1[1]) ? "$tmp1[0]: " : "";
			$text    = isset($tmp1[1]) ? "$tmp1[1]"   : "$tmp1[0]";
		}
		else
		{
			$time    = "";
			$text    = $line;
		}

		$x = strlen($line_count);
		echo sprintf("\n\t<font size=-1>#<b>%0{$x}d</b></font>: <font size=-2 color=green>$time</font> &nbsp;", $line_num);
		
		$err = $ignore_errors ? EErrorStatus::OK : CheckLineForErrors($line);

		switch ($err)
		{
		case EErrorStatus::ERROR:   echo "!!! <font style='BACKGROUND-COLOR: #FF8080' color=black>" . htmlspecialchars($text) . "</font><br/>"; break;
		case EErrorStatus::WARNING: echo "!!! <font style='BACKGROUND-COLOR: #FFFF80' color=black>" . htmlspecialchars($text) . "</font><br/>"; break;
		case EErrorStatus::NOTICE:  echo "!!! <font style='BACKGROUND-COLOR: #EEEEEE' color=black>" . htmlspecialchars($text) . "</font><br/>"; break;
		default: echo htmlspecialchars($text) . "<br/>"; break;
		}
	}
}
?>
<a name='bottom'>&nbsp;</a>
</body>

