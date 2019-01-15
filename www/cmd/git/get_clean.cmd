@set SRC="%~1"
@set BRANCH="%~2"
@set SCRIPT_DIR=%~dp0

@echo.
@echo **********************************************************************
@set SRC
@set BRANCH
@set SCRIPT_DIR
@echo **********************************************************************
@echo.

if not exist %SRC% MD %SRC%
PUSHD %SRC%
if exist .\.git goto DO_UPDATE

:DO_CLONE
	@echo.
	@echo **********************************************************************
	@echo *** CLONE ************************************************************
	@echo.
	
	call git clone --recursive -b %BRANCH% git@vilkas:MxKozijn.git %SRC%
	@goto DO_NEXT
	
:DO_UPDATE
	@echo.
	@echo **********************************************************************
	@echo *** UPDATE ***********************************************************
	@echo.
	
	rem DEL .\.git\index.lock /F /S 2>NUL

	call git fetch --all
	call git reset --hard origin/%BRANCH%
	call git clean -fdx 
	call git pull

	rem rem call git submodule update --init --recursive
	rem call git submodule git fetch --all
	rem call git submodule git reset --hard origin/%BRANCH%
	rem call git submodule git clean -fdx 
	rem call git submodule git pull

:DO_NEXT

popd
