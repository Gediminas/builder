To setup on Windows XP 32-bit PC
(e.g. VMCloud machine http://wiki.matrixlt.local/mediawiki/index.php/VM_Cloud ):

QUICK INSTALL:

	1. e.g.

	   \\ftp\ftproot\MxKBuilder\_setup\_setup.cmd
	   \\ftp\ftproot\MxKBuilder\_setup\_setup.cmd c:\_mx

	   xcopy \\ftp\ftproot\MxKBuilder\_setup c:\_mx\_setup /E /I && c:\_mx\_setup\_setup.cmd


	
	2. c:\_mx\_setup\_install_vc10.cmd
	   (required only for vc10 builds)


STEP BY STEP INSTALL:
 1) Copy this folder (builder/_setup) to newly created folder (e.g. c:\matrix, c:\_mx)
 
    (path should be very short because of vc6 limitation MAX_PATH = 256 characters)

    The copy of this folder exist in \\ftp\ftproot\MxKBuilder\_setup,
	command can be used:
	
      xcopy \\ftp\ftproot\MxKBuilder\_setup c:\_mx\_setup /E /Y /F /I

 2) Goto newly vreated folder and run:
	 
      _1_vc10_install.cmd (required only for Visual Studio 2010 project builds)
      00_sync_time.cmd
      01_set_root.cmd
      ...

NOTES:
 1) Do not install on your work PC
    because any file on the system/network/usb/cd/etc content can be listed,
    e.g.
      http://localhost:88/gui/show_file.php?fname=C:\boot.ini
      http://localhost:88/gui/show_file.php?fname=\\ftp\ftproot\MxKBuilder\_setup\_readme.txt

    (or modify ..\builder\bin\Apache2.2\conf\httpd.conf to allow local http access only)

 2) config.php should be changed (only if required) before httpd daemon launch launch

 3) VMCloud.
    Tested on
      win-xp-en/win-xp-en.xml.manifest image
      m1.large
      m1.xlarge
	  
    http://wiki.matrixlt.local/mediawiki/index.php/VM_Cloud
    Note: During VM start-up/shutdown small network interrupt inside VM may occur (sometimes up to 10 seconds).
    It may be Linux qemu/kvm (or bridge/network driver) bug,
    Linux community: bugs.launchpad.net/ubuntu/+source/qemu-kvm/+bug/584048 

    This can corrupt the very first build and sometimes instalation (when getting from GIT or FTP)
    If this install can fails, run once again.

