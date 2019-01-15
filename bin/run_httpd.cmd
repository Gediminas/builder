@echo off

rem echo %~dp0 > d:\mx\builder\bin\HTTPD.log

echo CONF: %1
echo.

cd "%~dp0"
pushd PHP
PATH=%cd%;%PATH%
popd
pushd ..\www\cmd\
PATH=%cd%;%PATH%
popd
set PATH

rem echo.
md     "%~dp0\..\..\_dat"
del /q "%~dp0\..\..\_dat\*.log"
del /q "%~dp0\..\..\_dat\*.lock"
del /q "%~dp0\..\..\_dat\*.tmp"
del /q "%~dp0\..\..\_dat\*.txt"
del /q "%~dp0\..\..\_dat\*.cmd"
del /q "%~dp0\..\..\_dat\builder-jobs.s3db"
del /q "%~dp0\..\www\*.log"


echo php_errors.log>.\..\www\php_errors.log
echo access.log>.\..\www\access.log
echo httpd.log>.\..\www\httpd.log

if not exist  .\..\www\conf\conf.php       copy  .\..\www\conf\conf.php_       .\..\www\conf\conf.php

echo HTTPD STARTING
start /B _cmd_httpd.exe /c Apache\bin\__httpd.exe -f "%~1"
echo HTTPD STARTED
pause