<?php
header("Content-Type: text/xml"); 

require_once("tools.php");

if (!isset($_GET['id']))
	die ("ERROR: 'id' parameter is empty");

$id      = $_GET['id'];
$db_path = autotester_db_path();
$db      = DB_open($db_path);
$XML     = DB_query_one($db, "SELECT XML FROM tests WHERE id=$id");
$path    = pathinfo($db_path, PATHINFO_DIRNAME) . '\\' . pathinfo($db_path, PATHINFO_FILENAME) . '\\' . $XML;
$text    = file_get_contents($path);

if (strlen($text) < 100)
	die ("ERROR: Log does not exist.<br/>$rundate");

//$text = str_replace("&#39;", "'", $text);
//$text = str_replace("&#34;", '"', $text);
$text = str_replace('http://wiki.matrixlt.local/mxprojects/mxkozijn/Autotester/autotester.xsl', 'autotester.xsl', $text);
echo $text;

?>