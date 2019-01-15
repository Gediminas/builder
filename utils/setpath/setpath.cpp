#define WIN32_LEAN_AND_MEAN
#include <windows.h>
#include <stdio.h>


const LPTSTR c_szCmdAdd    = "add";
const LPTSTR c_szCmdDel    = "del";
const LPTSTR c_szCmdEnv    = "env";
const LPTSTR c_szPathEntry = "Path";
const LPTSTR c_szSubKey_LM = "SYSTEM\\CurrentControlSet\\Control\\Session Manager\\Environment";
const LPTSTR c_szSubKey_CU = "Environment";


static const LPCTSTR reg_type_to_str(DWORD dwType)
{
	switch (dwType)
	{
	case REG_NONE:							return "REG_NONE - No value type\n";
	case REG_SZ:							return "REG_SZ - Unicode nul terminated string";
	case REG_EXPAND_SZ:						return "REG_EXPAND_SZ - Unicode nul terminated string with environment variable references";
	case REG_BINARY:						return "REG_BINARY - Free form binary";
	case REG_DWORD:							return "REG_DWORD - 32-bit number)";
	//case REG_DWORD_LITTLE_ENDIAN:			return "REG_DWORD_LITTLE_ENDIAN - 32-bit number, same as REG_DWORD";
	case REG_DWORD_BIG_ENDIAN:				return "REG_DWORD_BIG_ENDIAN - 32-bit number";
	case REG_LINK:							return "REG_LINK - Symbolic Link, unicode";
	case REG_MULTI_SZ:						return "REG_MULTI_SZ - Multiple Unicode strings";
	case REG_RESOURCE_LIST:					return "REG_RESOURCE_LIST - Resource list in the resource map";
	case REG_FULL_RESOURCE_DESCRIPTOR:		return "REG_FULL_RESOURCE_DESCRIPTOR - Resource list in the hardware description";
	case REG_RESOURCE_REQUIREMENTS_LIST:	return "REG_RESOURCE_REQUIREMENTS_LIST)";
	};

	return "(unknown type)";
}

static bool get_env_var(bool bLM, const char* lpEntry, DWORD &dwRetType, char* lpRetValue)
{
	HKEY   hKey;
	HKEY   hRootKey = bLM ? HKEY_LOCAL_MACHINE : HKEY_CURRENT_USER;
	LPCSTR lpSubKey = bLM ? c_szSubKey_LM      : c_szSubKey_CU;
	LONG   lRet     = RegOpenKeyEx(hRootKey, lpSubKey, NULL, KEY_READ, &hKey);

	if (ERROR_SUCCESS != lRet)
	{
		printf("ERROR: RegOpenKeyEx (using KEY_READ) returned %d\n", lRet);
		printf("       [%s\\%s]\n", bLM ? "HKEY_LOCAL_MACHINE" : "HKEY_CURRENT_USER", lpSubKey);
		printf("       Try run with administrator privileges.\n");
		return false;
	}

	DWORD dwSize=65535;
	lRet = RegQueryValueEx(hKey, lpEntry, NULL, &dwRetType,(LPBYTE)lpRetValue, &dwSize);

	if (ERROR_FILE_NOT_FOUND == lRet)
	{
		dwRetType = REG_SZ;
		strcpy(lpRetValue, "");
	}
	else if (ERROR_SUCCESS != lRet)
	{
		printf("ERROR: RegQueryValueEx returned %d\n", lRet);
		printf("       [%s\\%s\\%s]\n", bLM ? "HKEY_LOCAL_MACHINE" : "HKEY_CURRENT_USER", lpSubKey, lpEntry);
		printf("       Try run with administrator privileges.\n");
		return false;
	}

	RegCloseKey(hKey);
	return true;
}

static bool set_env_var(bool bLM, const char* lpEntry, DWORD dwType, const char* lpValue)
{
	HKEY hKey;
	HKEY hRootKey   = bLM ? HKEY_LOCAL_MACHINE : HKEY_CURRENT_USER;
	LPCSTR lpSubKey = bLM ? c_szSubKey_LM      : c_szSubKey_CU;
	LONG lRet       = RegOpenKeyEx(hRootKey, lpSubKey, NULL, KEY_WRITE, &hKey);

	if (ERROR_SUCCESS != lRet)
	{
		printf("ERROR: RegOpenKeyEx (using KEY_WRITE) returned %d\n", lRet);
		printf("       [%s\\%s]\n", bLM ? "HKEY_LOCAL_MACHINE" : "HKEY_CURRENT_USER", lpSubKey);
		printf("       Try run with administrator privileges.\n");
		return false;
	}

	lRet = RegSetValueEx(hKey, lpEntry, NULL, dwType,(CONST BYTE*)lpValue, strlen(lpValue)+1);

	if (ERROR_SUCCESS != lRet)
	{
		printf("ERROR: RegSetValueEx returned %d\n", lRet);
		printf("       [%s\\%s\\%s]\n", bLM ? "HKEY_LOCAL_MACHINE" : "HKEY_CURRENT_USER", lpSubKey, lpEntry);
		printf("       Try run with administrator privileges.\n");
		return false;
	}

	lRet = RegFlushKey(hKey);

	if (ERROR_SUCCESS != lRet)
	{
		printf("WARNING: RegFlushKey returned %d\n", lRet);
		//getchar();
	}


	RegCloseKey(hKey);

	//SendMessageTimeout();
	SendMessage(HWND_BROADCAST, WM_SETTINGCHANGE, bLM ? 1 : 0, (LPARAM) lpSubKey);
	
	return true;
}



int main(int argc, char* argv[])
{
	const bool bLM = (0 == stricmp(argv[argc-1], "-m"));

	if (argc < 3 || (bLM && argc < 4))
	{
		printf("syntax:\n");
		printf("   setpath %s path [-m]\n",           c_szCmdAdd);
		printf("   setpath %s path [-m]\n",           c_szCmdDel);
		printf("   setpath %s variable value [-m]\n", c_szCmdEnv);
		printf("\n");
		printf("   Option -m - use HKEY_LOCAL_MACHINE, otherwise - HKEY_CURRENT_USER\n");
		printf("\n");
		//getchar();
		return -1;
	}

	char  szValue[65535];
	DWORD dwType=REG_SZ;

	const LPCSTR lpCommand   = argv[1];

	if (0 == stricmp(lpCommand, c_szCmdAdd))
	{
		const LPCSTR lpUserValue = argv[2];

		if (!get_env_var(bLM, c_szPathEntry, dwType, szValue))
			return false;
	
		if (0 < strlen(lpUserValue))
			strcat(szValue, ";");

		strcat(szValue, lpUserValue);

		if (!set_env_var(bLM, c_szPathEntry, dwType, szValue))
			return -1;

		if (get_env_var(bLM, c_szPathEntry, dwType, szValue))
			printf("\nPATH:\n(%s):\n\n%s\n\n", reg_type_to_str(dwType), szValue);
	}
	else if (0 == stricmp(lpCommand, c_szCmdDel))
	{
		printf("Command 'del' not supported yet\n", c_szCmdDel);
	
	}
	else if (0 == stricmp(lpCommand, c_szCmdEnv))
	{
		const LPCSTR lpEnvVar    = argv[2];
		const LPCSTR lpUserValue = (3 < argc || (bLM && 4 < argc)) ? argv[3] : NULL;
	
		if (!set_env_var(bLM, lpEnvVar, dwType, lpUserValue))
			return -1;

		if (get_env_var(bLM, lpEnvVar, dwType, szValue))
			printf("\n%s (%s): %s\n", lpEnvVar, reg_type_to_str(dwType), szValue);
	}
	else
	{
		printf("ERROR: Unknown command '%s'\n", lpCommand);
		//getchar();
		return -1;
	}


	//printf("FINISHED\n");
	//getchar();
	return 0;
}

