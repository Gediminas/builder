<?xml version="1.0" encoding="ISO-8859-1"?>

<!-- Edited by XMLSpy® -->
<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
  <body style="font-family:Arial;font-size:12pt;background-color:#EEEEEE">
	<xsl:variable name="space" select="' '" /> 
	
	<xsl:for-each select="AUTOTESTER/SUMMARY">
		<div style="font-weight:bold;background-color:#668866;color:white;font-size:10pt;padding:1px">
				SUMMARY
		</div>
		<div style="margin-left:20px;margin-bottom:1em;font-size:10pt">
			<div style="font-weight:bold;">	
				<table border="0" width="400">

					<tr><td>Date:</td>
						<td><b>	<xsl:value-of select="DATE"/>	</b></td></tr>
					<tr><td>Time:</td>
						<td><b>	<xsl:value-of select="TIME"/>	</b></td></tr>
					<tr><td>Total errors:</td>

						<td><b><span style="color:red">	<xsl:value-of select="TOTAL_ERRORS"/>	</span></b></td></tr>
					<tr><td>Errors in projects:</td>
						<td><b><span style="color:red">	<xsl:value-of select="ERRORS_IN_PROJECTS"/>	</span></b></td></tr>
					<tr><td>Projects checked:</td>
						<td><b>	<xsl:value-of select="PROJECTS_CHECKED"/>	</b></td></tr>

					<tr><td>Projects with errors:</td>
						<td><b>	<xsl:value-of select="PROJECTS_WITH_ERRORS"/>	</b></td></tr>
				</table>
			</div>
		</div>
	</xsl:for-each>
	
   <xsl:for-each select="AUTOTESTER/BENCHMARK">

		<div style="margin-left:20px;margin-bottom:1em;font-size:10pt">
		<xsl:for-each select="TEST">
			<xsl:if test="ERROR_COUNT != 0">
				<div style="font-weight:bold;">	
					<xsl:value-of select="PROJECT"/>
					 <xsl:copy-of select="$space" />
					<span style="color:red"> <xsl:value-of select="ERROR_COUNT"/></span>
					errors	
				</div>

			</xsl:if>
				
		</xsl:for-each>
      </div>
	  </xsl:for-each>  
    <hr/><h1>Full data</h1><hr/>
  
    <xsl:for-each select="AUTOTESTER/BENCHMARK">
		<div style="background-color:#668866;color:white;padding:1px">
			<span style="font-weight:bold">
				<xsl:value-of select="TITLE"/>

			</span>
		</div>
    
		<div style="margin-left:20px;margin-bottom:1em;font-size:10pt">
		
		<xsl:for-each select="TEST">
			<div style="font-weight:bold;">	<xsl:value-of select="PROJECT"/> </div>

			<xsl:for-each select="SQL">
				<xsl:if test="ERROR_COUNT != 0">

				<div style="margin-left:24px">
					<xsl:value-of select="SQL_QUERY"/>
					<xsl:value-of select="ERROR_COUNT"/> errors	
				</div>
				<div style="margin-left:32px;font-style:italic">
					<span style="color:red">
						<xsl:for-each select="ERROR">
							<xsl:value-of select="."/> <br/>

						</xsl:for-each>
					</span>
					<span style="color:green">
						<xsl:for-each select="WARNING">
							<xsl:value-of select="."/> <br/>
						</xsl:for-each>
					</span>
					<br/>

				</div>
				</xsl:if>
			</xsl:for-each>
			
		</xsl:for-each>
      </div>
	  
    </xsl:for-each>
	
	
  </body>
</html>
