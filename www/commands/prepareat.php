<?php

//*1 Prepareat($root, $distr_bin)
//* -------------
//*2 root - Source root
//*2 bin_path - Binaries path
//* -------------
//*3 

$root = path_to_dos($cmd_params[0]);
$distr_bin = path_to_dos($cmd_params[1]);
//echo "xxx$root\n";
//echo "xxx$distr_bin\n";
$src = "$root\\Libraries\\dc\\bin";
$dst = "$distr_bin";
//echo "xxx$src\n";
//echo "xxx$dst\n";
$src_file = "$src\\dc5.dll";
$dst_file = "$dst\\MxDnglAcs5.dll";
//echo "xxx$src_file\n";
//echo "xxx$dst_file\n";
if (is_file("$dst_file")) unlink("$dst_file");
$result = copy_file(NULL, $src_file, $dst_file);
$src_file = "$src\\dc6.dll";
echo "copy result: $result\n";
$dst_file = "$dst\\MxDnglAcs6.dll";
//echo "xxx$dst_file\n";
if (is_file("$dst_file")) unlink("$dst_file");
$result = copy_file(NULL, $src_file, $dst_file) && $result;
echo "copy result: $result\n";
_log_to($command_log, $result ? "[DONE]" : "[FAILED]");
//return $result;

?>