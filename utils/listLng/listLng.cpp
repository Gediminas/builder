#include "stdafx.h"
#include "PragmaSet.h"
#include "StdString.h"

void FindLng(const char* fname)
{
	if (FILE *pFile = fopen(fname, "rb"))
	{
		fseek(pFile, 0, SEEK_END);
		size_t nFileSize = ftell(pFile);
		void* buffer     = malloc(nFileSize + 1);
		
		rewind(pFile);
		const size_t nBytesRead = fread(buffer, 1, nFileSize, pFile);
		ASSERT(nBytesRead == nFileSize);

		fclose(pFile);


		char *it = (char*) buffer;

		while (NULL != (it = (char*) memchr((void*) it, '.', nBytesRead - (it - (char*) buffer))))
		{
			ASSERT(it[0] == '.');
			if ((it[1] == 'l' || it[1] == 'L') &&
				(it[2] == 'n' || it[2] == 'N') &&
				(it[3] == 'g' || it[3] == 'G') )
			{
				
				while (isalnum(*(--it)));
				printf(it+1);
				printf("\n");
				break;
			}

			it = (char*)it + 1;

			ASSERT(it < (char*)buffer + nBytesRead);
		}

		free(buffer);
	}
}

int main( int argc, const char* argv[])
{
	for (int i=1; i< argc; i++)
	{
		FindLng(argv[i]);
	}

#ifdef _DEBUG
	getchar();
#endif

	return 0;
}
