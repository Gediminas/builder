@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


pushd "%BLD%\portable\git\cmd"
PATH=%cd%;%PATH%
popd

pushd "%BLD%\portable\vc6\VC98\Bin"
PATH=%cd%;%PATH%
popd

pushd "%BLD%\portable\vc6\COMMON\MSDev98\Bin"
PATH=%cd%;%PATH%
popd


echo STARTING HTTPD

pushd "%BLD_REPO%\bin\"
start /min "httpd" "run_httpd.cmd"
popd

echo.
echo [DONE]


