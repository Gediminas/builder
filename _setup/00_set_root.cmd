@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


echo.
echo ------------------------------
echo CURRENT environment variables:
echo          BLD='%BLD%'
echo     BLD_REPO='%BLD_REPO%'
echo   BUILDERCFG='%BUILDERCFG%'
echo.
echo USER parameters:
echo     BLD_REPO='%~1'
echo   BUILDERCFG='%~2'
echo ------------------------------
echo.

pushd ..
if not "%~1" == "" set BLD=%~1
if "%BLD%" == "" set BLD=%cd%
popd
echo Enter instalation root folder (Press ENTER for: '%BLD%')
if "%~1" == "" set /p BLD=

echo.

if not "%~2" == "" set BLD_REPO=%~2
if "%BLD_REPO%" == "" set BLD_REPO=%BLD%\builder
echo Enter repository folder (Press ENTER for: '%BLD_REPO%')
if "%~1" == "" set /p BLD_REPO=

echo.

set BUILDERCFG=%BLD_REPO%\www\conf\conf.php


echo.
echo WILL SET environment variables:
echo          BLD=%BLD%
echo     BLD_REPO=%BLD_REPO%
echo   BUILDERCFG=%BUILDERCFG%
echo.
pause


echo Setting...
setx BLD "%BLD%"
setx BLD_REPO "%BLD_REPO%"
setx BUILDERCFG "%BUILDERCFG%"


echo [DONE]
if "%~1" == "" pause
