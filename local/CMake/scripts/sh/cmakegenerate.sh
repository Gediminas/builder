#script parameters

this_script=$0
build_cfg=$1
generator=$2

#include
source "${root}/sh/functions.sh"
source "${root}/CMake/scripts/sh/cmakeconfig.sh"

#Saves all cmake paths in cmake_paths.txt
${php_exe} "${cmake_generate_php}" "${build_cfg}" "${cmake_paths_txt}"

#ShowStatusOnLoadCommands ${cmake_paths_txt}

ReadCommands $cmake_paths_txt
commands_count=${#ret_commands[@]}

Color ${cl_hdr_act}
Print "CMake will generate [${cl_imprtnt}${commands_count}${cl_hdr_act}] projects"
Color ${cl_err_nrm}

##################################### GENERATE #####################################

for (( iCommand=0;iCommand<commands_count;iCommand++)); do
	Color ${cl_hdr_ina}
	
	generate_path="${ret_commands[${iCommand}]}"
	
	Print "Generating in ${generate_path}"
	
	Color ${cl_logging}
	
	cd $generate_path
		
	eval "$cmake_exe" -G "\""$generator"\""""
		
	Print ""
	
	Color ${cl_err_nrm}
	${php_exe} "${cmake_clean_php}" "${generate_path}"
done
