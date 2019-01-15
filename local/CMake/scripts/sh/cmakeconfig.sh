#!/bin/bash

# paths

root=${this_script%\\sh\\*}		#= "cd .."
root=${root%\\CMake\\*}			#= "cd .."
root=${root//\\//}				#replace "\" to "/"

php_exe="${root}/bin/php -c ${root}/bin/php.ini"
cmake_exe="${root}/CMake/bin/cmake"
cmake_paths_txt="${root}/temp/cmake_paths.txt"
cmake_generate_php="${root}/CMake/scripts/php/collect_cmake_files.php"
cmake_clean_php="${root}/CMake/scripts/php/cleancmake.php"

# colors

source "${root}/sh/colors.sh"

cl_hdr_act=$bldcyn
cl_hdr_ina=$txtcyn
cl_imprtnt=$bldylw
cl_nimprtn=$bldgrn
cl_logging=$txtgrn
cl_err_nrm=$bldred
cl_err_hrd="\e[41;37m"
cl_default=$txtrst