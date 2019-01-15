#!/bin/bash

# paths

root=${this_script%\\sh\\*}		#= "cd .."
root=${root//\\//}				#replace "\" to "/"

php_exe="${root}/bin/php -c ${root}/bin/php.ini"
date_exe="${root}/bin/date"
tr_exe="${root}/bin/tr"
notify_exe="${root}/bin/notifu"
commands_txt="${root}/temp/commands.txt"
result_txt="${root}/temp/result.txt"
generate_php="${root}/php/collect_builds_cli.php"
run_php="${root}/php/build_cli.php"
open_prj_php="${root}/php/open_prj_cli.php"

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


# debug info
#
#Color ${cl_logging}
#Print ==============
#Print $root
#Print $php_exe
#Print $date_exe
#Print $cat_exe
#Print $commands_txt
#Print $result_txt
#Print ==============
#Color ${cl_default}
