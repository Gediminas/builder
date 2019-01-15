<?php

//*1 Include($another_product_id)
//* -------------
//*2 $another_product_id - Another product id
//* -------------
//*3 Include script lines from another product (<product_id>.xml file <script> section)

$another_product_id = $cmd_params[0];

require_once("../core/run_command_block.php");

if (!get_product_info($another_product_id, $another_product_xml, $another_product_name, $product_mutex, $another_product_comment, $another_product_enabled, $another_product_night, $another_product_mailto, $another_script))
{
	_log_to($command_log, "ERROR: XML parser failed!. Halting...");
	set_param("worker$worker_id", 'halt', 1);
	return;
}

$another_script        = str_replace("'", "\"", $another_script);
$sub_script_lines      = explode("\n", $another_script);
$sub_script_line_count = count($sub_script_lines);

//Owerwrite log "header"/first-line
_log_reset($command_log);
_log_to($command_log, "$cmd_line_full [$sub_script_line_count]");

if (!$sub_script_line_count)
{
	_log_to($command_log, "ERROR: No script lines found!. Halting...");
	set_param("worker$worker_id", 'halt', 1);
	return;
}

foreach ($sub_script_lines as $sub_cmd)
	_log_to($command_log, "$sub_cmd", false);

_log_to($command_log, "");
_log_to($command_log, "Running INCLUDE commands");

run_command_block($cmd_nr, $sub_script_lines, $sys_params);

?>