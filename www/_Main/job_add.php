<?php
$all        = isset($_GET['all'])      ? $_GET['all']      : 0;
$bcomment   = isset($_GET['bcomment'])   ? $_GET['bcomment']   : false;
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : false;

echo header("Refresh: 0; url=../_Main/index.php?all=$all&bcomment=" . rawurlencode($bcomment));

echo "COMMENT: $bcomment<br/>\n";
echo "PRODUCT: $product_id<br/>\n";

require_once("../conf/conf_fnc.php");
require_once("../tools/file_tools.php");
require_once("../tools/builder_script_fnc.php");
require_once("../tools/builder_sys_fnc.php");
require_once("../db/builder_db_jobs_fnc.php");

verify_temp_folder();

$time_build_added = GetSysDateTime();
_log("<i>time_build_added= $time_build_added</i>");

if (!isset($product_id) || 0 == strlen($product_id))
	die("ERROR: No product ID passed");

if (!get_product_info($product_id, $product_xml, $product_name, $product_mutex, $product_comment, $product_enabled, $product_night, $product_mailto, $product_script))
	die("ERROR: XML parser failed");

_log("product_id      = $product_id");
echo "product_xml     = $product_xml<br/>\n";
echo "product_name    = $product_name<br/>\n";
echo "product_comment = $product_comment<br/>\n";

add_job($product_id, $time_build_added, $bcomment);

echo "<center><h1>Build added </h1></center><br/>\n";

if (!debug('manual_proc_start'))
	StartDaemon();

?>