@echo off

setpath add "%BLD%\portable\git\cmd;%BLD%\portable\vc6\VC98\Bin;%BLD%\portable\vc6\COMMON\MSDev98\Bin"

echo.
echo [DONE]
if not "%1"=="silent" pause
