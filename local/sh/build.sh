#!/bin/bash

# script params

this_script=$0
ide_version=$1
build_cfg=$2
configuration=$3
force_platform=$4

# include

source "${root}/sh/functions.sh"
source "${root}/sh/config.sh"


################### MAIN #####################################

ShowStatusOnStart ${build_cfg} ${configuration}
#ShowStatusOnCollectCommands ${commands_txt}
${php_exe} "${generate_php}" "${ide_version}" "${build_cfg}" "${configuration}" "${commands_txt}"
#ShowStatusOnLoadCommands ${commands_txt}

ReadCommands $commands_txt
commands_count=${#ret_commands[@]}


if [ "${force_platform}" != "" ]
then
	Color ${cl_imprtnt}
	Print "WARNING: Forcing [${force_platform}] platform build"
	Print ""
	Color ${cl_err_nrm}

	for (( iCommand=0;iCommand<commands_count;iCommand++)); do
		ret_commands[${iCommand}]="${ret_commands[${iCommand}]} * ${force_platform}"
	done  #for (( iCommand=0;iCommand<commands_count;iCommand++))
fi #if [ ${force_platform} == "x64" ]


Color ${cl_hdr_act}
Print "Will build [${cl_imprtnt}${commands_count}${cl_hdr_act}] projects+configurations"
Color ${cl_err_nrm}



################### MENU LOOP #####################################


do_build=0

#Used for converting projects
if [ "$4" == "autostart" ]
then
	do_build=1;
fi

skip_on_error=0
while [ $do_build -ne 1 ]; do

	ShowMenu

	case "$ret_user_input" in
		"")
			do_build=1
			skip_on_error=0
			;;

		"s")
			do_build=1
			skip_on_error=1
			;;

		"r")
			do_build=1
			rebuild_all=1
			;;
			
		"e")
			exit 1
			;;

		"l")
			Color ${cl_logging}
			for (( iCommand=0;iCommand<commands_count;iCommand++)); do
				((build_nr = $iCommand+1))
				build_command=${ret_commands[${iCommand}]}
				ExplodeCommand "$build_command"
				Print "${build_nr}: ${cl_imprtnt}${ret_dsp_name}${cl_logging} - ${ret_configuration}"
			done  #for (( iCommand=0;iCommand<commands_count;iCommand++))
			;;
		
		"L")
			Color ${cl_logging}
			for (( iCommand=0;iCommand<commands_count;iCommand++)); do
				((build_nr = $iCommand+1))
				build_command=${ret_commands[${iCommand}]}
				ExplodeCommand "$build_command"
				Print "${build_nr}: ${ret_dsp_dir}/${cl_imprtnt}${ret_dsp_name}${cl_logging}.${ret_dsp_ext} - ${ret_configuration}"
			done  #for (( iCommand=0;iCommand<commands_count;iCommand++))
			;;

		"f")
			Color ${cl_hdr_act}
			Print "Enter project file name part (e.g. dxf)"
			Color ${cl_default}

			read name
			
			MakeLowerCase $name
			name_l=$ret_text
			#echo $name_l
			
			Color ${cl_imprtnt}
			for (( iCommand=0;iCommand<commands_count;iCommand++)); do
				((build_nr = $iCommand+1))
				build_command=${ret_commands[${iCommand}]}
				ExplodeCommand "$build_command"
				
				MakeLowerCase $ret_dsp_name
				dsp2=$ret_text
				#echo $dsp2
				
				if [[ "${dsp2}" == *"${name_l}"* ]]; then
					Color "${cl_logging}"
					Print "${build_nr}: ${cl_imprtnt}${ret_dsp_name}${cl_logging} - ${ret_configuration}"
				fi
				
			done #for
			Color ${cl_default}
			;;
			
		"F")
			Color ${cl_hdr_act}
			Print "Enter project file name part (e.g. dxf)"
			Color ${cl_default}

			read name
			
			MakeLowerCase $name
			name_l=$ret_text
			#echo $name_l
			
			Color ${cl_imprtnt}
			for (( iCommand=0;iCommand<commands_count;iCommand++)); do
				((build_nr = $iCommand+1))
				build_command=${ret_commands[${iCommand}]}
				ExplodeCommand "$build_command"
				
				MakeLowerCase $ret_dsp_name
				dsp2=$ret_text
				#echo $dsp2
				
				if [[ "${dsp2}" == *"${name_l}"* ]]; then
					Color "${cl_logging}"
					Print "${build_nr}: ${ret_dsp_dir}/${cl_imprtnt}${ret_dsp_name}${cl_logging} - ${ret_configuration}"
				fi
				
			done #for
			Color ${cl_default}
			;;
			
		"R")
			Color ${cl_hdr_act}
			Print "Enter dsp number ${cl_imprtnt}[1..${commands_count}]${cl_hdr_act}  to rebuild (0 - exit, use 'l'/'L' in main menu to list dsp)"
			Color ${cl_default}

			read number

			if [ 0 -lt $number ]; then
				if [ $number -le $commands_count ]; then
					(( dsp_nr = $number - 1))
					build_command=${ret_commands[${dsp_nr}]}
					
					ShowStatus "[1R]" "$number" "-" "-" "$build_command" "9999" # "9999"=first build - bright header

					ExplodeCommand "$build_command"
					$php_exe "${run_php}" "${ide_version}" "${build_command}" "1" "${result_txt}"
					result=$(< "$result_txt")

					##if [ $result -ne 0 ]; then
					##fi
					##Print "Not implemented yet"
				fi
			fi
			;;
			
		*)
			Print "Command unknown"
			;;
	esac

	Color ${cl_default}
	
done  #while [ $do_build -e 0 ]

	
################### BUILD #####################################

Color ${cl_err_nrm}

start_time=$($date_exe +%s)
status=""
rebuild_this=${rebuild_all}

for (( iCommand=0;iCommand<commands_count;iCommand++)); do

	Color ${cl_err_nrm}

	((build_nr = $iCommand+1))

	build_command="${ret_commands[${iCommand}]}"

	result=9999  # =was not tried to build
	
	while [ $result -ne 0 ]; do
		
		ShowStatus "${status}" "${build_nr}" "${commands_count}" "${start_time}" "${build_command}" "${result}"

		$php_exe "${run_php}" "${ide_version}" "${build_command}" "${rebuild_this}" "${result_txt}"

		rebuild_this=${rebuild_all}
		
		result=$(< "$result_txt")

		if [ $result -ne 0 ]; then
			
			status="[E]"
			ShowStatusOnError "${param_status}" "${build_nr}" "${commands_count}" "${start_time}" "${build_command}"

			
			if [ $skip_on_error -ne 0 ]; then
			
				status="*"    #With errors
				result=0
				
			else

				repeat=1
				while [ $repeat -eq 1 ]
				do
					Color ${cl_hdr_act}
					Print "${cl_hdr_act}Press ${cl_imprtnt}ENTER${cl_hdr_act} - build again"
					Print "  ('${cl_imprtnt}s${cl_hdr_act}' - skip, '${cl_imprtnt}r${cl_hdr_act}' - rebuild, '${cl_imprtnt}o${cl_hdr_act}' - open dsp, '${cl_imprtnt}e${cl_hdr_act}' - exit)"
					Color ${cl_default}

					read user_input
					
					if [ "$user_input" == "o" ]; then
						(${php_exe} "${open_prj_php}" "${ide_version}" "${ret_dsp_path}")&

					else
						repeat=0
					fi
					
				done #while [ $repeat -eq 1 ]
		
		
				status="[R]"     #Repeat
				
				if [ "$user_input" == "r" ]; then
					status="[RR]"     #With errors
					rebuild_this=1
				fi

				if [ "$user_input" == "s" ]; then
					status="*"     #With errors
					result=0
				fi

				if [ "$user_input" == "e" ]; then
					exit 1
				fi
				
				
			fi  #[ $skip_on_error -e 1]
			
		fi  #[ $result -ne 0 ]
		
	done  #while [ $result -ne 0 ]
	
done  #for (( iCommand=0;iCommand<commands_count;iCommand++))


#Used for converting projects
if [ "$4" != "autostart" ]
then
	ShowStatusOnFinish "${status}" "${start_time}" "${build_cfg}" "${configuration}"
	read
fi


####################################################################
