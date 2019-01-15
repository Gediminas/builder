@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


md _tmp

echo %BLD%>"_tmp\root_path.tmp"
ssr 0 "\\" "\\\\" "_tmp\root_path.tmp" "_tmp\root_path_mod.tmp"
set /p ROOT2=<"_tmp\root_path_mod.tmp"

echo %ROOT2%

ssr 0 "{ROOT}" "%ROOT2%" "vc6_dirs.reg_" "_tmp\vc6_dirs.reg"

echo.
echo reg import "_tmp\vc6_dirs.reg"
reg import "_tmp\vc6_dirs.reg"

echo.
echo [DONE]

if not "%1"=="silent" pause
