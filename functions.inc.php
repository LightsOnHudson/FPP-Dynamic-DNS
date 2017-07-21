<?php


//function to update the DNS using the DynamicDNS Host

function updateDynamicDNSIP($PROVIDER, $DNS_HOSTNAME, $API_TOKEN) {
	
	
	global $DEBUG, $IPINFO_PROVIDER;
	
	
	
	$CURL_CMD = "/usr/bin/curl -s ".$IPINFO_PROVIDER;
	
	

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$IPINFO_PROVIDER);
	//curl_setopt($ch, CURLOPT_POST, 1);
	//curl_setopt($ch, CURLOPT_USERPWD, PSSWDINFO);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	
	$IP_ADDRESS= curl_exec ($ch);
	curl_close ($ch);
	
	//$IP_ADDRESS = $output[0];
	
	//temporary IP adress to check in duckdns
//	$IP_ADDRESS = "65.102.234.185";
	
	//change based on the provider
	
	switch(strtoupper($PROVIDER)) {
		
		case "DUCKDNS.ORG":
			if($DEBUG) {
				logEntry("Provider is : ".$PROVIDER);
			}
			
			$PROVIDER_CMD = "https://www.duckdns.org/update?domains=".$DNS_HOSTNAME."&token=".$API_TOKEN."&ip=".$IP_ADDRESS;
		//	$PROVIDER_CMD = "https://duckdns.org/update/".$DNS_HOSTNAME."/".$API_TOKEN."/".$IP_ADDRESS;
			break;
			
		default:
			
			
	}
	
	$CURL_CMD_DNS_PROVIDER = "/usr/bin/curl -s '".$PROVIDER_CMD."'";
	

	$ch = curl_init ();
	curl_setopt ( $ch, CURLOPT_URL,trim($PROVIDER_CMD));
	
	curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt ( $ch, CURLOPT_WRITEFUNCTION, 'do_nothing' );
	curl_setopt ( $ch, CURLOPT_VERBOSE, true );
	
	$result = curl_exec ( $ch );
	logEntry ( "Curl result: " . $result ); // $result;
	curl_close ( $ch );
	
	
	
	logEntry("Updating IP address: " .$IP_ADDRESS." for provider: ".$PROVIDER. " for hostname: ".$DNS_HOSTNAME. " with command: ".$PROVIDER_CMD);
	//logEntry("Curl result: ".$resp);
}

function printHourFormats($ELEMENT_NAME,$ELEMENT_SELECTED)


{

	global $DEBUG;

	$T_FORMATS = array("12","24");


	echo "<select  name=\"".$ELEMENT_NAME."\">";

	//print_r($PLUGINS_READ);


	for($i=0;$i<=count($T_FORMATS)-1;$i++) {



		if($T_FORMATS[$i] == $ELEMENT_SELECTED) {

			echo "<option selected value=\"" . $ELEMENT_SELECTED . "\">" . $ELEMENT_SELECTED . " HR</option>";
		} else {

			echo "<option value=\"" . $T_FORMATS[$i] . "\">" . $T_FORMATS[$i] . " HR</option>";
		}

	}
	echo "</select>";
}

function printTimeFormats($ELEMENT_NAME,$ELEMENT_SELECTED)


{

	global $DEBUG;

	$T_FORMATS = array("h:i" => "HH:MM","h:i:s" => "HH:MM:SS");
	

	
	
	echo "<select  name=\"".$ELEMENT_NAME."\">";
	
	//print_r($PLUGINS_READ);
	foreach($T_FORMATS as $key => $value)
	{
		
		
		
		if($key == $ELEMENT_SELECTED) {

			echo "<option selected value=\"" . $key . "\">" . $value . "</option>";
		} else {

			echo "<option value=\"" . $key . "\">" .  $value . "</option>";
		}

	}
	echo "</select>";
}

//is fppd running?????
function isFPPDRunning() {
	$FPPDStatus=null;
	logEntry("Checking to see if fpp is running...");
        exec("if ps cax | grep -i fppd; then echo \"True\"; else echo \"False\"; fi",$output);

        if($output[1] == "True" || $output[1] == 1 || $output[1] == "1") {
                $FPPDStatus = "RUNNING";
        }
	//print_r($output);

	return $FPPDStatus;
        //interate over the results and see if avahi is running?

}
//get current running playlist
function getRunningPlaylist() {

	global $sequenceDirectory;
	$playlistName = null;
	$i=0;
	//can we sleep here????

	//sleep(10);
	//FPPD is running and we shoud expect something back from it with the -s status query
	// #,#,#,Playlist name
	// #,1,# = running

	$currentFPP = file_get_contents("/tmp/FPP.playlist");
	logEntry("Reading /tmp/FPP.playlist : ".$currentFPP);
	if($currentFPP == "false") {
		logEntry("We got a FALSE status from fpp -s status file.. we should not really get this, the daemon is locked??");
	}
	$fppParts="";
	$fppParts = explode(",",$currentFPP);
//	logEntry("FPP Parts 1 = ".$fppParts[1]);

	//check to see the second variable is 1 - meaning playing
	if($fppParts[1] == 1 || $fppParts[1] == "1") {
		//we are playing

		$playlistParts = pathinfo($fppParts[3]);
		$playlistName = $playlistParts['basename'];
		logEntry("We are playing a playlist...: ".$playlistName);
		
	} else {

		logEntry("FPPD Daemon is starting up or no active playlist.. please try again");
	}
	
	
	//now we should have had something
	return $playlistName;
}
function logEntry($data,$logLevel=1,$sourceFile, $sourceLine) {

	global $logFile,$myPid, $LOG_LEVEL;

	
	if($logLevel <= $LOG_LEVEL) 
		//return
		
		if($sourceFile == "") {
			$sourceFile = $_SERVER['PHP_SELF'];
		}
		$data = $sourceFile." : [".$myPid."] ".$data;
		
		if($sourceLine !="") {
			$data .= " (Line: ".$sourceLine.")";
		}
		
		$logWrite= fopen($logFile, "a") or die("Unable to open file!");
		fwrite($logWrite, date('Y-m-d h:i:s A',time()).": ".$data."\n");
		fclose($logWrite);


}



function processCallback($argv) {
	global $DEBUG,$pluginName;
	
	
	if($DEBUG)
		print_r($argv);
	//argv0 = program
	
	//argv2 should equal our registration // need to process all the rgistrations we may have, array??
	//argv3 should be --data
	//argv4 should be json data
	
	$registrationType = $argv[2];
	$data =  $argv[4];
	
	logEntry("PROCESSING CALLBACK: ".$registrationType);
	$clearMessage=FALSE;
	
	switch ($registrationType)
	{
		case "media":
			if($argv[3] == "--data")
			{
				$data=trim($data);
				logEntry("DATA: ".$data);
				$obj = json_decode($data);
	
				$type = $obj->{'type'};
				logEntry("Type: ".$type);	
				switch ($type) {
						
					case "sequence":
						logEntry("media sequence name received: ");	
						processSequenceName($obj->{'Sequence'},"STATUS");
							
						break;
					case "media":
							
						logEntry("We do not support type media at this time");
							
						//$songTitle = $obj->{'title'};
						//$songArtist = $obj->{'artist'};
	
	
						//sendMessage($songTitle, $songArtist);
						//exit(0);
	
						break;
						
						case "both":
								
						logEntry("We do not support type media/both at this time");
						//	logEntry("MEDIA ENTRY: EXTRACTING TITLE AND ARTIST");
								
						//	$songTitle = $obj->{'title'};
						//	$songArtist = $obj->{'artist'};
							//	if($songArtist != "") {
						
						
						//	sendMessage($songTitle, $songArtist);
							//exit(0);
						
							break;
	
					default:
						logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
						exit(0);
						break;
	
				}
	
	
			}
	
			break;
			exit(0);
	
		case "playlist":

			logEntry("playlist type received");
			if($argv[3] == "--data")
                        {
                                $data=trim($data);
                                logEntry("DATA: ".$data);
                                $obj = json_decode($data);
				$sequenceName = $obj->{'sequence0'}->{'Sequence'};	
				$sequenceAction = $obj->{'Action'};	
                                                processSequenceName($sequenceName,$sequenceAction);
                                                //logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
                                        //      logEntry("We do not understand: type: ".$obj->{'type'}. " at this time");
			}

			break;
			exit(0);			
		default:
			exit(0);
	
	}
	

}
?>
