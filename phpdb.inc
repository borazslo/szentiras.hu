<?php
/*--------------------------------------------------------------------------------------
	
	Name: phpDB General module
	Version: 1.02bR5
	Description: A PHP database wrapper for various database servers.  Database results 
	are flushed automatically by phpDB.  Supported database servers are MySQL, MSQL, 
	PostgreSQL, Microsoft SQL Server and Sybase. 
	Author: Joe Thong
	Site URL: http://phpdb.linuxbox.com/	
	Last Modified Date: 11/23/1999

	Copyright (c) Joe Thong Chean Fonk.
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions
	are met:
	1. Redistributions of source code must retain the above copyright
	   notice, this list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above copyright
	   notice, this list of conditions and the following disclaimer in the
	   documentation and/or other materials provided with the distribution.
	3. All advertising materials mentioning features or use of this software
	   must display the following acknowledgement:
	        This product includes software developed by Joe Thong
			and its contributors.
	4. Neither the name of the author nor the names of its contributors
	   may be used to endorse or promote products derived from this software
	   without specific prior written permission.
	
	THIS SOFTWARE IS PROVIDED BY THE REGENTS AND CONTRIBUTORS ``AS IS'' AND
	ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
	IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
	ARE DISCLAIMED.  IN NO EVENT SHALL THE REGENTS OR CONTRIBUTORS BE LIABLE
	FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
	DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS
	OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
	HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
	OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF
	SUCH DAMAGE.	
	
--------------------------------------------------------------------------------------*/	

	if (!defined("_PHPDB_GENERAL_LAYER")) {
	  define("_PHPDB_GENERAL_LAYER", 1 );
	}
	
	$databaseType = 'mysql';	/*	Fill in the database server that you're going
									to use.  Consult the phpDB Reference Manual for
									more information.	*/

	$phpDBRootPath = '/home2/www.kereszteny.hu/biblia';	/*	The phpDB module root path.
								No trailing slash.	*/
	
	function useDB($dbType = "") {
	GLOBAL $phpDBRootPath;
		switch (strtolower($dbType)) {
			case "mysql":
			case "msql":
			case "postgresql":
			case "mssql":
			case "sybase":
			case "informix":
				//include("$phpDBRootPath". "/phpDB-" . "$dbType.lib");
				include("phpDB-$dbType.lib");
				break;
			case "":
				die("Please edit phpDB.inc in order to use phpDB");
				return false;
			default:
				die("Invalid database selection");
				return false;		
		}					
		return true;		
	}
	
	useDB($databaseType);
?>
