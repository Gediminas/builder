@echo off

rem echo %~dp0 > d:\mx\builder\bin\daemon.log
cd "%~dp0"

pushd PHP
PATH=%cd%;%PATH%
popd
set PATH

echo.
md     "%~dp0..\..\_dat"
del /q "%~dp0..\..\_dat\*.log"
del /q "%~dp0..\..\_dat\*.tmp"
del /q "%~dp0..\..\_dat\*.txt"
del /q "%~dp0..\..\_dat\*.cmd"

pushd ..\www\core

echo.
echo CURRENT DIR %cd%
echo PHPDir: %~dp0PHP 

rem echo CURRENT DIR %cd%
rem echo DAEMON STARTING: %cd% >> d:\mx\builder\bin\daemon.log
rem echo start /B _php-win.exe -f "%~dp0..\www\core\start_stop_daemon.php" 1

start /B _php-win.exe -c %~dp0PHP -f "%~dp0..\www\core\start_stop_daemon.php" 1
rem php.exe -c %~dp0PHP -f "%~dp0..\www\core\start_stop_daemon.php" 1

popd

rem echo DAEMON STARTED >> d:\mx\builder\bin\daemon.log
echo DAEMON STARTED
rem pause