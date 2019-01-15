@echo off
echo.
echo *********************************************************
echo %0
echo *********************************************************
echo.


W32tm /config /syncfromflags:MANUAL /manualpeerlist:time.windows.com
W32tm /config /update
W32tm /resync

echo.
echo [DONE]
if not "%1"=="silent" pause
