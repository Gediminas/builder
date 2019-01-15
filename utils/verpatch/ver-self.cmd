: Add version info resource with some strings and a resource file to verpatch.exe
: (a kind of self test)

: run in Release or Debug
:
set _ver="1.0.1.6 [%date%]"
set _s1=/s desc "Version patcher tool" /s copyright "(C) 1998-2009, pavel_a"
set _s1=%_s1% /s pb "for CodeProject; static libs"
set _s1=%_s1% /pv "1.0.0.1 (free)" 

set _rf=/rf #64 ..\usage.txt

: Run a copy of verpatch on itself:

copy verpatch.exe v.exe || exit /b 1

v.exe verpatch.exe /va /rpdb %_ver% %_s1% %_s2% %_rf% 

@echo Errorlevel=%errorlevel%
