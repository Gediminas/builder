@echo off

pushd "%BLD%\portable\git\cmd"
PATH=%cd%;%PATH%
popd


pushd "%BLD_REPO%"
echo on

cmd /c git pull

@echo off
popd

pause