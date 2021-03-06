<?php


include_once "/opt/fpp/www/common.php";
include_once "functions.inc.php";
include_once "commonFunctions.inc.php";

$pluginName = "DynamicDNS";
$pluginVersion ="1.0";

$UPDATE_IP_CMD = "updateIP.php";

//1.0 Dyanmic DNS with DuckDNS

$logFile = $settings['logDirectory']."/".$pluginName.".log";

$pluginUpdateFile = $settings['pluginDirectory']."/".$pluginName."/"."pluginUpdate.inc";


$gitURL = "https://github.com/LightsOnHudson/FPP-Plugin-Dynamic-DNS.git";

logEntry("plugin update file: ".$pluginUpdateFile);

if(isset($_POST['updatePlugin']))
{
	logEntry("updating plugin...");
	$updateResult = updatePluginFromGitHub($gitURL, $branch="master", $pluginName);

	echo $updateResult."<br/> \n";
}


if(isset($_POST['submit']))
{
	
	
	WriteSettingToFile("DNS_HOSTNAME",urlencode($_POST["DNS_HOSTNAME"]),$pluginName);
	WriteSettingToFile("API_TOKEN",urlencode($_POST["API_TOKEN"]),$pluginName);
	WriteSettingToFile("DEBUG",urlencode($_POST["DEBUG"]),$pluginName);
	
	//read the settings back into the variables here???
	$DNS_HOSTNAME = urldecode($_POST["DNS_HOSTNAME"]);
	$API_TOKEN = urldecode($_POST["API_TOKEN"]);
	$ENABLED = urldecode($_POST["ENABLED"]);

}

sleep(1);

	//re-read the settings so they are read in
	$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
	if (file_exists($pluginConfigFile)) {
		$pluginSettings = parse_ini_file($pluginConfigFile);
	
		logEntry("Reading in settings from file for: ".$pluginName);
		
	}
	
	
	
	//	Hostname on DuckDNS
	$DNS_HOSTNAME = urldecode($pluginSettings['DNS_HOSTNAME']);
	//	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
	
	$API_TOKEN = urldecode($pluginSettings["API_TOKEN"]);
	$DEBUG = urldecode($pluginSettings['DEBUG']);



$ENABLED = $pluginSettings['ENABLED'];

$CRON_ENTRY = "5 * * * * /usr/bin/php ".$settings['pluginDirectory']."/".$pluginName."/".$UPDATE_IP_CMD." >> " . $logFile;

switch ($ENABLED) {
	
	case "ON":
		$cron_file = "/tmp/crontab.txt";
		file_put_contents($cron_file, $CRON_ENTRY.PHP_EOL);
		logEntry("Adding cron entry: ".$CRON_ENTRY." to cronjob");
		exec("crontab $cron_file");
		break;
		
	case "":
		logEntry("Removing Cronjob(s)");
		exec("crontab -r");
		break;
		
	default:
		logEntry("Removing Cronjob(s)");
		exec("crontab -r");
		break;
	
}
//check to see if Crontab entry is there

//$crontabOutput = shell_exec("/usr/bin/crontab -l");
// we'll execute a php script as an example:
$out = shell_exec("/usr/bin/crontab -l 2> output");
//echo $out ? $out : join("", file("output"));
logEntry("Crontab status: ".$out ? $out : join("", file("output")));//);//, $sourceFile, $sourceLine)
logEntry("OUT: ".$out);

if($out != "" || $out != null) {
	$crontTabEntries = explode("\n",$out);
	
	
//	print_r($crontTabEntries);
} else {
	logEntry("No crontab entries");
}

?>

<html>
<head>
</head>

<div id="<?echo $pluginName;?>" class="settings">
<fieldset>
<legend><?php echo $pluginName." Version: ".$pluginVersion;?> Support Instructions</legend>

<p>Known Issues:
<ul>
<li>NONE</li>
</ul>
<p>Configuration:
<ul>
<li>This plugin allows you to update your Dynamic DNS host on <a href="http://www.duckdns.org">DuckDNS</a>
</ul>



<form method="post" action="http://<? echo $_SERVER['SERVER_ADDR'].":".$_SERVER['SERVER_PORT']?>/plugin.php?plugin=<?echo $pluginName;?>&page=plugin_setup.php">


<?
echo "<input type=\"hidden\" name=\"LAST_READ\" value=\"".$LAST_READ."\"> \n";
$restart=0;
$reboot=0;

echo "ENABLE PLUGIN: ";

//if($ENABLED== 1 || $ENABLED == "on") {
//		echo "<input type=\"checkbox\" checked name=\"ENABLED\"> \n";
PrintSettingCheckbox($pluginName." Plugin", "ENABLED", $restart = 0, $reboot = 0, "ON", "OFF", $pluginName = $pluginName, $callbackName = "");
//	} else {
//		echo "<input type=\"checkbox\"  name=\"ENABLED\"> \n";
//}

echo "<p/> \n";


echo "Duck DNS Sub Hostname (lightsOnHudson): \n";
echo "<input type=\"text\" size=\"64\" name=\"DNS_HOSTNAME\" value=\"".$DNS_HOSTNAME."\"> \n";
echo "<p/> \n";




echo "API Token: \n";
echo "<input type=\"text\" size=\"64\" name=\"API_TOKEN\" value=\"".$API_TOKEN."\"> \n";
echo "<p/> \n";

?>
<p/>
<input id="submit_button" name="submit" type="submit" class="buttons" value="Save Config">
<?
 if(file_exists($pluginUpdateFile))
 {
 	//echo "updating plugin included";
	include $pluginUpdateFile;
}
?>
<p>To report a bug, please file it against <?php echo $gitURL;?>
</form>


</fieldset>
</div>
<br />
</html>

