@echo off
echo Copy ImageMagic  dlls

set PATH_SOURCE=..\..\Libraries\ImageMagic\VisualMagick\bin\
set PATH_DEST=..\..\bin
set XCOPY_SETTINGS=/Y
set PDB=pdb
set DLL=dll

SET LIB_FILE.1=*

FOR /F "tokens=2* delims=.=" %%A IN ('SET LIB_FILE.') DO copy "%PATH_SOURCE%\%%B.%DLL%" "%PATH_DEST%\%%B.%DLL%" %XCOPY_SETTINGS%
FOR /F "tokens=2* delims=.=" %%A IN ('SET LIB_FILE.') DO copy "%PATH_SOURCE%\%%B.%PDB%" "%PATH_DEST%\%%B.%PDB%" %XCOPY_SETTINGS%

pause