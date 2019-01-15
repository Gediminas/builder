@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.

md "%cd%\_tmp"
set LOG=%cd%\_tmp\extract.log
echo log: '%LOG%'
echo %0 > "%LOG%"


pushd "%BLD%\portable"

rem 7za x *.7z
rem 7za x _progs/*.7z -o"_progs"


for /f %%f in ('dir /b *.7z') do (

	echo Extracting %%f...
	cmd /c 7za x %%f -y>>"%LOG%"
)


for /f %%f in ('dir /b _progs\*.7z') do (

	echo Extracting _progs\%%f...
	cmd /c 7za x _progs\%%f -o"_progs" -y>>"%LOG%"
)


popd

echo.
echo [DONE]
if not "%1"=="silent" pause

