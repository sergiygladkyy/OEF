<?php
	include "include/config.php";
	include "include/DekiExt.php";

	// ------------------------------------------------------------------------
	// STOP! 
	// Do not make any changes below if you don't know what you are doing!	
	// ------------------------------------------------------------------------
	
	DekiExt(
		// Description 
	    "OILTEC Deki extended API PHP Service",                           

		// Metadata
	    array(
	        "description" => "External Config Extension.",               
	        "copyright"   => "OILTEC 2009", 
	        "namespace"   => "extconfig"
	    ), 

	    //List of Extension Functions
	    array (
	        "Fetch(section:str):array" => "FetchConfig",          
	    )
	);
	
	// ------------------------------------------------------------------------

	function FetchConfig( $section ) {
		if ( strlen ( $section )) {
			return @ExternalConfig::$extconfig[$section];
		} else {
			return @ExternalConfig::$extconfig;
		}
	}
?>