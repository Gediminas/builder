<?php

//*1 RegDistr(disrt_path)
//* -------------
//*2 disrt_path - Path to distribution file
//* -------------
//*3 Register distribution file path for report

$disrt_path  = $cmd_params[0];

//$product_dir  = $sys_params['product_dir'];
//$error        = GetFlag("$product_dir", 'error');
//$warning      = GetFlag("$product_dir", 'warning');
//$status       = $error ? 1 : $warning ? 2 : 0;
//set_param($param_owner, "build_status", $status);

$sys_params['distr_path'] = $disrt_path;

?>