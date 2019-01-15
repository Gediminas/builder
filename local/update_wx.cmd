@echo off
echo Copy wx unicode dlls?

set PATH_SOURCE=..\..\Libraries\wx\lib\vc_dll
set PATH_DEST=..\..\bin
set XCOPY_SETTINGS=/Y
set PDB=pdb
set DLL=dll

rem Release
SET FILE.1=wxmsw31u_xrc_vc_custom
SET FILE.2=wxmsw31u_webview_vc_custom
SET FILE.3=wxmsw31u_richtext_vc_custom
SET FILE.4=wxmsw31u_ribbon_vc_custom
SET FILE.5=wxmsw31u_qa_vc_custom
SET FILE.6=wxmsw31u_propgrid_vc_custom
SET FILE.7=wxmsw31u_media_vc_custom
SET FILE.8=wxmsw31u_html_vc_custom
SET FILE.9=wxmsw31u_gl_vc_custom
SET FILE.10=wxmsw31u_core_vc_custom
SET FILE.11=wxmsw31u_aui_vc_custom
SET FILE.12=wxmsw31u_adv_vc_custom
SET FILE.13=wxbase31u_xml_vc_custom
SET FILE.14=wxbase31u_vc_custom
SET FILE.15=wxbase31u_net_vc_custom

FOR /F "tokens=2* delims=.=" %%A IN ('SET FILE.') DO copy "%PATH_SOURCE%\%%B.%DLL%" "%PATH_DEST%\%%B.%DLL%" %XCOPY_SETTINGS%

rem Debug
SET FILE.1=wxmsw31ud_xrc_vc_custom
SET FILE.2=wxmsw31ud_webview_vc_custom
SET FILE.3=wxmsw31ud_richtext_vc_custom
SET FILE.4=wxmsw31ud_ribbon_vc_custom
SET FILE.5=wxmsw31ud_qa_vc_custom
SET FILE.6=wxmsw31ud_propgrid_vc_custom
SET FILE.7=wxmsw31ud_media_vc_custom
SET FILE.8=wxmsw31ud_html_vc_custom
SET FILE.9=wxmsw31ud_gl_vc_custom
SET FILE.10=wxmsw31ud_core_vc_custom
SET FILE.11=wxmsw31ud_aui_vc_custom
SET FILE.12=wxmsw31ud_adv_vc_custom
SET FILE.13=wxbase31ud_xml_vc_custom
SET FILE.14=wxbase31ud_vc_custom
SET FILE.15=wxbase31ud_net_vc_custom

FOR /F "tokens=2* delims=.=" %%A IN ('SET FILE.') DO copy "%PATH_SOURCE%\%%B.%DLL%" "%PATH_DEST%\%%B.%DLL%" %XCOPY_SETTINGS%

rem Debug
SET FILE.1=wxmsw31ud_xrc_vc_custom
SET FILE.2=wxmsw31ud_webview_vc_custom
SET FILE.3=wxmsw31ud_richtext_vc_custom
SET FILE.4=wxmsw31ud_ribbon_vc_custom
SET FILE.5=wxmsw31ud_qa_vc_custom
SET FILE.6=wxmsw31ud_propgrid_vc_custom
SET FILE.7=wxmsw31ud_media_vc_custom
SET FILE.8=wxmsw31ud_html_vc_custom
SET FILE.9=wxmsw31ud_gl_vc_custom
SET FILE.10=wxmsw31ud_core_vc_custom
SET FILE.11=wxmsw31ud_aui_vc_custom
SET FILE.12=wxmsw31ud_adv_vc_custom
SET FILE.13=wxbase31ud_xml_vc_custom
SET FILE.14=wxbase31ud_vc_custom
SET FILE.15=wxbase31ud_net_vc_custom

FOR /F "tokens=2* delims=.=" %%A IN ('SET FILE.') DO copy "%PATH_SOURCE%\%%B.%PDB%" "%PATH_DEST%\%%B.%PDB%" %XCOPY_SETTINGS%
