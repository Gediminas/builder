#ifndef __PRAGMA_SET_H__
#define __PRAGMA_SET_H__
#pragma once

#pragma message("Pragma warnings and messages included")

// Disabling
// The given formal parameter was never referenced in the body of the function 
// for which it was declared
#pragma warning  (disable : 4100)
// nonstandard extension used : class rvalue used as lvalue
#pragma warning (disable : 4238)
// signed/unsigned mismatch
#pragma warning (disable : 4018)
// disables warning: identifier was truncated to '255' characters in the browser information
#pragma warning (disable : 4786)

//local variable may be used without having been initialized
#pragma warning (disable : 4701)

// warning C++ language change
#pragma warning (disable : 4663)

// cast truncates constant value
#pragma warning (disable : 4310)

// nonstandard extension used: 'argument' : conversion
#pragma warning (disable : 4239)

// nonstandard extension used : nameless struct/union
#pragma warning (disable : 4201)

// warning C4018: '<' : signed/unsigned mismatch
#pragma warning (disable : 4018)

//'function' : function not inlined
#pragma warning(default: 4710)

//'function' : function marked as __forceinline not inlined
#pragma warning(default: 4714)

#ifdef _DEBUG
	//identifier : local variable is initialized but not referenced"
	#pragma warning (error : 4189)
	// local variable 'name' used without having been initialized
	#pragma warning(error: 4700)
	//'identifier' : unreferenced local variable
	#pragma warning(error: 4101)
#else
	//identifier : local variable is initialized but not referenced"
	#pragma warning(default: 4189) 
	// local variable 'name' used without having been initialized
	#pragma warning(default: 4700) 
	//'identifier' : unreferenced local variable
	#pragma warning(default: 4101)
#endif

// we do no not flollow MS standart 
// strcpy, fopen and other (ANSI standart) "unsafe" functions are legal.
#define _CRT_SECURE_NO_DEPRECATE

#if _MSC_VER >= 1600 // VC++ 2010
	//disable depreciation message for DaoDatabase
	#pragma warning (disable : 4995)
#endif

#ifdef _DEBUG
	#define _STLP_USE_NEWALLOC 1
#endif

#define VC_EXTRALEAN		// Exclude rarely-used stuff from Windows headers

#ifndef _WIN32_WINNT
	// VC 10
	// This file requires _WIN32_WINNT to be #defined at least to 0x0403. Value 0x0501 or higher is recommended
	#define _WIN32_WINNT 0x0501 // Windows (VC 10 MFC requires minimal define)
// The following macros define the minimum required platform.  The minimum required platform
// is the earliest version of Windows, Internet Explorer etc. that has the necessary features to run 
// your application.  The macros work by enabling all features available on platform versions up to and 
// including the version specified.

// Modify the following defines if you have to target a platform prior to the ones specified below.
// Refer to MSDN for the latest info on corresponding values for different platforms.
#endif

#endif