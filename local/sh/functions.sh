#!/bin/bash


function Color         { echo -en "$1"; }
function Print         { echo -e "$1"; }
function ChangeTitle   { echo -en "\033]2;$1\007"; }
function TrayNotify    { exec "$notify_exe" -p "$1" -m "$2" -d 0 -t $3 & } #http://www.paralint.com/projects/notifu/
function MakeLowerCase { ret_text=$( echo "$1" | "$tr_exe" "[:upper:]" "[:lower:]" ); }




function ShowStatusOnStart {
	
	local param_build_cfg=$1
	local param_configuration=$2

	Color ${cl_hdr_act}
	Print "******************************************************************************"
	Print " PARAMETERS:"
	Print "    path to cfg:   ${cl_imprtnt}${param_build_cfg}${cl_hdr_act}"
	Print "    build configs: ${cl_imprtnt}${param_configuration}${cl_hdr_act}"
	Print "******************************************************************************"
	Color ${cl_default}

	ChangeTitle "${param_build_cfg} - ${param_configuration}"
}

function ShowStatusOnFinish {

	local param_status=$1
	local param_start_time=$2
	local param_build_cfg=$3
	local param_configuration=$4
	
	TimeElapsed "$param_start_time"

	Color $cl_default
	Color $cl_imprtnt
	Print
	Print "==============================================================================="
	Print "[FINISHED]:                                              ${ret_min} min. ${ret_sec} sec."
	Print "==============================================================================="
	Print
	Color $cl_default

	ChangeTitle "${param_status}F: ${param_build_cfg} - ${param_configuration}"
	TrayNotify "${param_status}${param_build_cfg} - ${param_configuration}" "FINISHED" "info"
}

#function ShowStatusOnCollectCommands {
#	
#	local param_commands_txt=$1
#
#	Color ${cl_logging}
#	Print "Generating '${param_commands_txt}'"
#	Color ${cl_default}
#	Color ${cl_err_nrm}
#}

#function ShowStatusOnLoadCommands {
#	
#	local param_commands_txt=$1
#
#	Color ${cl_logging}
#	Print "Loading '${param_commands_txt}'"
#	Print
#	Color ${cl_default}
#	Color ${cl_err_nrm}
#}

function ShowStatus {

	local param_status=$1
	local param_build_nr=$2
	local param_commands_count=$3
	local param_start_time=$4
	local param_build_command=$5
	local param_prev_result=$6
	local hdr_clr

	TimeElapsed ${param_start_time}
	ExplodeCommand "${param_build_command}"

	if [ $param_prev_result -eq 9999 ]; then
		#first try has brighter header
		hdr_clr=${cl_hdr_act}
	else
		hdr_clr=${cl_hdr_ina}
	fi

	Color ${hdr_clr}
	Print
	Print "==============================================================================="
	Print "${cl_imprtnt}[$param_build_nr/$param_commands_count]:                                                       ${ret_min}${hdr_clr} min. ${cl_imprtnt}${ret_sec}${hdr_clr} sec.${hdr_clr}"
	Print "${ret_dsp_dir}/${cl_imprtnt}${ret_dsp_name}${hdr_clr}.${ret_dsp_ext}"
	Print "                                                              ${cl_imprtnt}[$ret_configuration]${hdr_clr}"
	Print "==============================================================================="
	Print
	Color ${cl_default}
	Color ${cl_logging}

	ChangeTitle "${param_status}${param_build_nr}/${param_commands_count} ${ret_dsp_name} - ${ret_configuration} [${ret_min}:${ret_sec}] - ${ret_build_cfg} - ${ret_configuration}"
}

function ShowStatusOnError {

	local param_status=$1
	local param_build_nr=$2
	local param_commands_count=$3
	local param_start_time=$4
	local param_build_command=$5

	TimeElapsed ${param_start_time}
	ExplodeCommand "${param_build_command}"

	Color ${cl_err_hrd}
	Print "                              ERRORS: $result                                      "
	Print
	Color ${cl_default}

	ChangeTitle "${param_status}$param_build_nr/$param_commands_count $ret_dsp_name - $ret_configuration [${ret_min}:${ret_sec}] - ${build_cfg} - ${configuration}"
	TrayNotify "${param_status}${build_cfg} - ${configuration}" "ERROR" "error"
}

function ShowMenu {

	Color ${cl_hdr_act}
	Print
	Print "Press ${cl_imprtnt}ENTER${cl_hdr_act} to continue"
    Print "      '${cl_imprtnt}s${cl_hdr_act}' - Skip on error"
	Print "      '${cl_imprtnt}r${cl_hdr_act}' - Rebuild All"
    Print "      '${cl_imprtnt}e${cl_hdr_act}' - Exit now"
    Print "Other:"
    Print "      '${cl_nimprtn}l${cl_hdr_act}' - project list"
    Print "      '${cl_nimprtn}L${cl_hdr_act}' - project list (show full paths)"
    Print "      '${cl_nimprtn}f${cl_hdr_act}' - find project by name part"
    Print "      '${cl_nimprtn}F${cl_hdr_act}' - find project by name part (show full paths)"
    Print "      '${cl_nimprtn}R${cl_hdr_act}' - Rebuild 1 project"
	Print
	Color ${cl_default}

	read ret_user_input

	Color ${cl_default}
	Color ${cl_err_nrm}

}

function TimeElapsed {
	
	local param_start_time=$1
	
	if [ "$param_start_time" == "-" ]; then
		ret_min="-"
		ret_sec="-"
	else
		tmp_end_time=$($date_exe +%s)
		time_difference=$(( $tmp_end_time - $param_start_time ))
		((ret_min = $time_difference / 60))
		((ret_sec = $time_difference % 60))
	fi
}

function ReadCommands {

	local param_commands_txt="$1"

	if [ ! -e "$param_commands_txt" ]; then
		echo "ERROR: "$commands_txt" not found"
		echo "Press ENTER to exit..."
		read
		exit
	fi

	local tmp_file_content=$(< "$param_commands_txt")

	local oldIFS=$IFS
	IFS=$'\n'
	ret_commands=( $tmp_file_content )        # split '$content' to array '$ret_commands'
	IFS=$oldIFS
}

function ExplodeCommand {

	local param_build_command=$1

	param_build_command=${param_build_command#* && }

	ret_dsp_path=${param_build_command% \* *}
	ret_dsp_dir=${ret_dsp_path%/*}
	ret_dsp_name=${ret_dsp_path##*/}
	ret_dsp_name=${ret_dsp_name%.*}
	ret_dsp_ext=${ret_dsp_path##*.}

	ret_configuration=${param_build_command#* - Win32 }
	
	#ret_configuration=${ret_configuration%*} #OLD
	ret_configuration=(${ret_configuration//* })

	#echo "$ret_dsp_path"
	#echo "${ret_dsp_dir} ${ret_dsp_name} ${ret_dsp_ext}"
}

