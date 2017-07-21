#!/usr/bin/php
<?
error_reporting(0);
ob_flush();flush();

//Version 1.0 applying permissions

$pluginName ="DynamicDNS";
$MatrixMessageVersion = "1.0";
$myPid = getmypid();

$DEBUG=false;

$IPINFO_PROVIDER = "ipinfo.io/ip";

$skipJSsettings = 1;
$fppWWWPath = '/opt/fpp/www/';
set_include_path(get_include_path() . PATH_SEPARATOR . $fppWWWPath);

require("common.php");

include_once("functions.inc.php");

require ("lock.helper.php");
define('LOCK_DIR', '/tmp/');
define('LOCK_SUFFIX', $pluginName.'.lock');

$logFile = $settings['logDirectory']."/".$pluginName.".log";


$pluginConfigFile = $settings['configDirectory'] . "/plugin." .$pluginName;
if (file_exists($pluginConfigFile))
	$pluginSettings = parse_ini_file($pluginConfigFile);

	
	
	
	//	Hostname on DuckDNS
	$DNS_HOSTNAME = urldecode($pluginSettings['DNS_HOSTNAME']);
	//	$ENABLED = urldecode(ReadSettingFromFile("ENABLED",$pluginName));
	$ENABLED = $pluginSettings['ENABLED'];
	$API_TOKEN = urldecode($_POST["API_TOKEN"]);
	$DEBUG = urldecode($pluginSettings['DEBUG']);




if($ENABLED != "ON") {

	logEntry("Plugin Status: DISABLED Please enable in Plugin Setup to use & Restart FPPD Daemon");
	lockHelper::unlock();
	exit(0);

}

$PROVIDER = "duckdns.org";

$DNS_STATUS = updateDynamicDNSIP($PROVIDER, $DNS_HOSTNAME);


lockHelper::unlock();
exit(0);
?>