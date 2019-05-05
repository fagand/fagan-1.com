<?php

define('BAN_IGNORE', 3);

ob_start();
error_reporting(E_ALL ^ E_NOTICE); 
$dir_this = get_dir(); $start_time = microtime(); make_microdiff();
register_shutdown_function ("get_page_time");

require($dir_this . "fs_extension/fsec.php");

FSEC::Enable(C_DBFILE);
FSEC::Enable(C_FILE);

$a_db = $a_db_handle = Array(); $alpha_version = "1003";

init_db("general");

ban_action();
global_hit();
calc_averages();

function init_db($db_name){

	global $a_db, $a_db_complete, $dir_this;
	$db_name = strtolower($db_name);	

	if (isset($a_db[$db_name])){
		return;
	}

	global $a_db_handle;
	$a_db_handle[$db_name] = &new C_DBFile($dir_this . "data/" . $db_name . ".dat", DB_OPEN | DB_CREATE);
	$a_db[$db_name] = &$a_db_handle[$db_name]->a_data;

}

function strlpos($f_haystack, $f_needle) {

     $rev_str = strrev($f_needle);
     $rev_hay = strrev($f_haystack);
     $hay_len = strlen($f_haystack);
     $ned_pos = strpos($rev_hay,$rev_str);
     $result  = $hay_len - $ned_pos - strlen($rev_str);

     return $result;
}

function make_microdiff(){
	if (!function_exists("microtime_diff")){
		function microtime_diff($a, $b) {
			list($a_dec, $a_sec) = explode(" ", $a);
			list($b_dec, $b_sec) = explode(" ", $b);
			return $b_sec - $a_sec + $b_dec - $a_dec;
		}
	}
}

function get_dir(){
	//Until I figure out what is happening:
	return str_replace("include/", "", str_replace("\\", "/", dirname(__FILE__)) . "/");
	//return (is_dir("./data")) ? "./" : str_replace("include/", "", str_replace("\\", "/", dirname(__FILE__)) . "/");
}

function cmp_realtime ($a, $b) {

    if ($a['realtime'] == $b['realtime'])
	 return 0;

    return ($a['realtime'] > $b['realtime']) ? -1 : 1;

}

function cmp_when ($a, $b) {

	if ($a['when'] == $b['when'])
		return 1;

	return ($a['when'] > $b['when']) ? -1 : 1;
}

function timediff_days($month, $day, $year){

	//Return the time difference in days
	$ydiff = gmdate("Y", mktime()+time_offset())-$year;

	return ((gmdate("z", mktime()+time_offset())-date("z", mktime(0,0,0,$month, $day, $year))) + (365*$ydiff));
}

function gmdate_divisable($string, $offset = NULL){

	if ($offset){
		$date = gmdate($string, $offset);	
	} else {
		$date = gmdate($string);	
	}

	if ($date <= 0) { $date = 1; }

	return $date;
}


function getbrowser($agent){

	//Parse an agent string and return the browser name

	if ($agent == "" || $agent == "Undefined")
		return "Unknown";

	$browsername = ""; $lagent = strtolower($agent);

	preg_match_all("/[\w\.]+\/[\w\.]+/", $agent, $browser); 
	preg_match_all("/\(.*\)/", $agent, $features); 

	$features = $features[0][0]; $features = substr($features, 1, -1); $features = explode("; ", $features); 

	foreach ($features as $key=>$value) { 
	     if (substr_count($value, "MSIE") > 0){
		
		//Internet Explorer
		
		$browsername = $value;	
		$browsername = str_replace("MSIE", "Internet Explorer", $browsername);

		if (substr_count($lagent, "opera") > 0) $browsername = "Opera";

	     }     
	}

	//Check for some browsers which are often overlooked
	$agent = $lagent;

        if (substr_count($agent, "safari") > 0)	$browsername = "OS X Safari";     
        else if (substr_count($agent, "firebird") > 0) $browsername = "Firebird (Mozilla)";     
        else if (substr_count($agent, "phoenix") > 0) $browsername = "Phoenix (Mozilla)";     
        else if (substr_count($agent, "gecko") > 0) $browsername = "Gecko";

	if ($browsername == "")
		$browsername = $browser[0][0];
	if ($browsername == "")
		$browsername = "Unknown";

	return $browsername;
}

function get_operating_system($agent){
	
	//Returns operating system based on user agent

	if (strlen($agent) < 3){ return "Unknown"; }

	$agent = strtolower($agent);
	$agent_names = Array();	$system_names = Array();

	$agent_names[] = "windows nt 5.1";	$system_names[] = "Windows XP";
	$agent_names[] = "windows nt 5.0";	$system_names[] = "Windows 2000";
	$agent_names[] = Array("windows nt", "windows-nt");	$system_names[] = "Windows NT";
	$agent_names[] = Array("windows 98", "win98");		$system_names[] = "Windows 98";
	$agent_names[] = Array("windows 95", "win95");		$system_names[] = "Windows 95";
	$agent_names[] = Array("windows 3.1", "win16"); 	$system_names[] = "Windows 3.x";
	$agent_names[] = "windows me";	$system_names[] = "Windows ME";
	$agent_names[] = "win9x"; 	$system_names[] = "Windows 9x";
	$agent_names[] = "windows_ce"; 	$system_names[] = "Windows CE";
	$agent_names[] = "windows"; 	$system_names[] = "Windows x.x";
	$agent_names[] = "os x"; 	$system_names[] = "Mac OS X";
	$agent_names[] = "mac"; 	$system_names[] = "Mac";
	$agent_names[] = "linux"; 	$system_names[] = "Linux";
	$agent_names[] = "freebsd"; 	$system_names[] = "Free BSD";
	$agent_names[] = "sunos"; 	$system_names[] = "Sun OS";
	$agent_names[] = "irix"; 	$system_names[] = "IRIX";
	$agent_names[] = "risc"; 	$system_names[] = "RISC OS";
	$agent_names[] = "amigaos"; 	$system_names[] = "Amiga OS";
	$agent_names[] = "hp-ux"; 	$system_names[] = "HP-UX";
	$agent_names[] = "webtv"; 	$system_names[] = "Web TV";
	$agent_names[] = "os/2"; 	$system_names[] = "OS/2";
	$agent_names[] = "palmos"; 	$system_names[] = "Palm OS";
	$agent_names[] = "unix"; 	$system_names[] = "Unix Based";

	for ($i=0; $i<count($agent_names); $i++){
		
		if (is_array($agent_names[$i])){
			foreach($agent_names[$i] as $key=>$agent_name){
				if (substr_count($agent, $agent_name) > 0){
					return $system_names[$i];
				}
			}
		}
		else if (substr_count($agent, $agent_names[$i]) > 0){
			return $system_names[$i];
		}
	}	
	return "Unknown";
}

function force_wrap($string, $wrapto){

	$string = " " . $string . " ";	$string = str_replace(">", ">~ ", str_replace("<", "<~ ", $string));
	$lastfind = 0; $thisfind = strpos($string, " ", $lastfind);

	while ($thisfind !== FALSE){
		if ($thisfind - $lastfind > $wrapto){			
			$str_chunk = substr($string, $lastfind+1, $thisfind-($lastfind+1));
			if (substr_count($str_chunk, "http://") == 0){				
				$string = (substr($string, 0, $lastfind+1) . wordwrap($str_chunk, $wrapto-1, " ", 1) . substr($string, $thisfind));
				$thisfind = $lastfind;
			}
		}
		strpos($string, " ", $lastfind);

		$lastfind = $thisfind;
		$thisfind = strpos($string, " ", $lastfind+1);
	}

	$string = str_replace(">~ ", ">", str_replace("<~ ", "<", $string));
	return substr($string, 1, strlen($string)-2);
}

function time_offset(){
	
	global $dir_this, $a_db;
	init_db("user");

	if (!isset($a_db['user']['offset'])) {
		$a_db['user']['offset'] = 0; 
	}

	$offset = $a_db['user']['offset'];
	if (substr_count($offset, "-") > 0){ $mfunc = "-"; }
	else { $mfunc = "+"; }
	
	$offset = str_replace(Array("+", "-"), "", $offset);
	return $mfunc . ((($offset/100)-1)*60)*60;
}

function privacy_check(){

	if (is_secret()){
		//Authorization required
		if (require_login() == FALSE)
			exit();
	}
}

function try_firstdate(){	

	//If this is the first time Elite Stats has been run, log the date

	global $dir_this, $a_db;
	$firstdate = &$a_db['general']['firstdate'];

	if ($firstdate <= 0 || $firstdate == ""){
	
		$firstdate = mktime(0,0,0, gmdate("m", mktime()+time_offset()), gmdate("d", mktime()+time_offset()), gmdate("Y", mktime()+time_offset()));

		//Try contacting FireStorm
		if (!isset($_COOKIE['installed']) && !file_exists($dir_this . "data/installed_1003.tmp")){
			@file_put_contents($dir_this . "data/installed_1003.tmp", "FS Notify");
			if (file_exists($dir_this . "data/installed_1003.tmp"))
				@mail("tracking@all-interviews.com", "ES: New User", "A new user is using Elite Stats at http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], "From: track04@all-interviews.com");
			setcookie ("installed", 1 ,time()+((((60*60)*60)*24)*5));
		}
	}
}

function calc_averages(){

	//Generate any averages
	global $dir_this, $a_db;

	$firstdate = $a_db['general']['firstdate'];
	$days = timediff_days(date("m", $firstdate), date("d", $firstdate), date("Y", $firstdate));

	if ($days <= 0 || !is_numeric($days)){
		$days = 1;
	}

	//Average hits per day
	$a_db['general']['average_hits_day'] = $a_db['general']['totalhits']/$days;

	//Average visitors per day
	$a_db['general']['average_unique_day'] = $a_db['general']['totalunique']/$days;

}

function check_records(){

	global $dir_this, $a_db;

	//Enumerate all of the static time based stats and see if they are records
	//Also compare online user number for record


	//Check this week's and today's hits
	//------------------

	$vars = Array("recorddayhits", "todayhits", "recorddaydate", "recordweekhits", "weekhits", "recordweekdate");

	for ($i=0; $i<count($vars); $i+=3){
		$record = &$a_db['general'][$vars[$i]];
		$current = $a_db['general'][$vars[$i+1]];
		if (!is_numeric($record) || $record <= $current){
			$record = $current;
			$a_db['general'][$vars[$i+2]] = gmdate("M-d-Y", mktime()+time_offset());
		}
		unset($record);
	}


	//Check online user record
	//------------------

	$users_online = stream_visitors_online();
	$record_users = $a_db['general']['recordusers'];
	$current_users = count($users_online);
	
	if ($record_users < 0 || !is_numeric($record_users))
		$record_users = 0;

	if($current_users > $record_users){			
		$a_db['general']['recordusers'] = $current_users;
		$a_db['general']['recorduserdate'] = gmdate("M-d-Y", mktime()+time_offset());
	}

	//------------------

}

function backup_data($emergency = false){

	//Backup all the files in "/data"
	global $dir_this;

	if ($emergency){ 
		$backupfile = "data/backup_emergency.bak"; 
		$files = Array("user.dat", "general.dat");
	} else {
		$backupfile = "data/backup.bak";
		$files = Array("banned.dat", "browsers.dat", "systems.dat", "daytrend.dat", "general.dat", "vinfo.dat", "user.dat", "vonline.dat", "track_main.dat");
	}

	$backup = new C_Backup ( $dir_this . $backupfile);   
	$backup->backup_db->a_data['btime'] = gmdate("M-d-Y h:i:s", mktime()+time_offset());
	
	foreach ($files as $key=>$filename){
		$backup->push_files($dir_this . "data/" . $filename);
	}
}

function restore_backup($emergency = false){

	//Restores the backup, returns TRUE on success and FALSE on failure
	global $dir_this;

	if ($emergency){ 
		$backupfile = "data/backup_emergency.bak"; 
		$files = Array(/*"user.dat", */"general.dat");
	} else {
		$backupfile = "data/backup.bak";
		$files = Array("banned.dat", "browsers.dat", "systems.dat", "daytrend.dat", "general.dat", "vinfo.dat", "user.dat", "vonline.dat", "track_main.dat");
	}

	if (!file_exists($dir_this . $backupfile)){
		return FALSE;
	}

	$backup = new C_Backup ($dir_this . $backupfile); 

	foreach ($files as $key=>$filename){
		$backup->restore($dir_this . "data/", $filename);
	}	

	return TRUE;
}


function visitor_info(){
	
	//Updates latest visitor info

	global $dir_this, $a_db;
	global $a_db_handle;

	init_db("vinfo"); init_db("user");
	init_db("systems"); init_db("browsers");

	$db_vinfo = &$a_db_handle['vinfo'];
	$db_systems = &$a_db_handle['systems'];
	$db_browsers = &$a_db_handle['browsers'];

	$vdata = array();

	//Get this visitor's info
	$vdata['ip'] = $_SERVER['REMOTE_ADDR'];
	$vdata['agent'] = $_SERVER['HTTP_USER_AGENT'];

	if (!isset($_SERVER['HTTP_REFERER'])){
		$_SERVER['HTTP_REFERER'] = "";
	}
	$vdata['refer'] = $_SERVER['HTTP_REFERER'];
	$vdata['time'] = gmdate("M-d-Y h:i:s", mktime()+time_offset());
	$vdata['realtime'] = mktime();
	$vdata['lastpage'] = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	if (substr_count($vdata['lastpage'], "include/functions.php") > 0 && substr_count($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) > 0){
		//Probably linked through an image or object
		$vdata['lastpage'] = $_SERVER['HTTP_REFERER'];
	}

	if ($vdata['refer'] == ""){ $vdata['refer'] = "Unknown"; }
	if ($vdata['agent'] == ""){ $vdata['agent'] = "Unknown"; }
	$vdata['browser'] = $browser = getbrowser($vdata['agent']);

	if (!isset($a_db['vinfo']['user']))
		$a_db['vinfo']['user'] = Array();

	$this_user = NULL;

	for ($i=0; $i<count($a_db['vinfo']['user']); $i++){
		if ($a_db['vinfo']['user'][$i]['ip'] == $vdata['ip']){
			$this_user = &$a_db['vinfo']['user'][$i];
			break;
		}
	}

	if (($this_user) && $this_user['views'] >= 0 && is_numeric($this_user['views'])){
		$vdata['views'] = $this_user['views'];
	}
	else {
		$vdata['views'] = 0;
	}
	if (!$this_user){
		$this_user = &$a_db['vinfo']['user'][count($a_db['vinfo']['user'])];
	}

	$vdata['views']++;
	$this_user = $vdata;
	
	usort($a_db['vinfo']['user'], "cmp_realtime");	


	//Experimental browser ranking	

	if ($vdata['views'] == 1 && !isset($_COOKIE['ES_Counted'])){

		$found = false;

		for ($i=0; $i < count($a_db['browsers']) && !$found; $i++){

			if (isset($sel_browser)){
				unset($sel_browser);
			}

			$sel_browser = &$a_db['browsers'][$i];
			$name = $sel_browser['name'];
		
			if (strtolower($browser) == strtolower($name)){

				//We have matched browsers
				$found = true;
				$user_count = &$sel_browser['users'];
					if ($user_count < 0 || !is_numeric($user_count))
						$user_count = 0;
				$user_count++;

				$sel_browser['name'] = $browser;				
			}			
		}
		if (!$found){			
			$this_id = count($a_db['browsers']);
			$a_db['browsers'][$this_id]['name'] = $browser;
			$a_db['browsers'][$this_id]['users'] = 1;
		}

		if (isset($user_count)){
			unset($user_count);
		}

		//Operating System Check
		$system = get_operating_system($vdata['agent']);	
		$found = false;

		for ($i=0; $i < count($a_db['systems']) && !$found; $i++){			
			if (isset($sel_system)){
				unset($sel_system);
			}
			$sel_system = &$a_db['systems'][$i];
			$name = $sel_system['name'];
		
			if (strtolower($system) == strtolower($name)){
				//We have matched systems
				$found = true;
				$user_count = &$sel_system['users'];

				if ($user_count < 0 || !is_numeric($user_count)){
					$user_count = 0;
				}

				$user_count++;
				$sel_system['name'] = $system;
			}			
		}
		if (!$found){
			$this_id = count($a_db['systems']);
			$a_db['systems'][$this_id]['name'] = $system;
			$a_db['systems'][$this_id]['users'] = 1;
		}

	}	
	//End experimental
	
	//If the count of logged visitor data is over the limit or 25 if no custom limit is set, trim the list

	$limit = $a_db['user']['tracklimit'];
	if ($limit < 5 || $limit > 100 || !is_numeric($limit)){
		$limit = 25;
	}

	if (count($a_db['vinfo']['user']) > $limit){

		$user_buf = Array();
//		usort($a_db['vinfo']['user'], "cmp_realtime");	

		for ($i=0; $i<$limit; $i++){
			$user_buf[] = $a_db['vinfo']['user'][$i];
		}

		$a_db['vinfo']['user'] = $user_buf;
	}

	$db_vinfo->check_modified();
	$db_systems->check_modified();
	$db_browsers->check_modified();
}

function visitor_info_online(){

	//Calculates the number of visitors online

	global $dir_this, $a_db, $a_db_handle;

	init_db("vonline");
	$this_user = Array(); $user_buf = Array();	
	$db_vonline = &$a_db_handle['vonline'];

	//Get general details
	$this_user['ip'] = $_SERVER['REMOTE_ADDR'];
	$this_user['time'] = mktime();
	$this_user['lastpage'] = "http://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

	if (substr_count($this_user['lastpage'], "include/functions.php") > 0 && substr_count($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) > 0){

		//Probably linked through an image or object
		$this_user['lastpage'] = $_SERVER['HTTP_REFERER'];
	}
	
	$user_buf[] = $this_user;
	for ($i=0; $i<count($a_db['vonline']['user']); $i++){

		$sel_user = $a_db['vonline']['user'][$i];
		if ($sel_user['ip'] != $this_user['ip']){

			$valid = true;
			$hour = gmdate("h", $sel_user['time']);
			$minute = gmdate("i", $sel_user['time']);			
	
			if (gmdate("h", mktime())-$hour == 1){
				$diff =  59 - $minute;
				$diff += gmdate("i", mktime());
					if ($diff > 20)
						$valid = false;
			}
			else if (gmdate("h", mktime())-$hour == 0){
				$diff =  gmdate("i", mktime()) - $minute;
					if ($diff > 20)
						$valid = false;
			} else {
				$valid = false;
			}

			if ($valid){
				$user_buf[] = $sel_user;
			}
			
		}
	}
	
	$a_db['vonline']['user'] = $user_buf;
	$db_vonline->check_modified();
}

function page_popularity(){

	//Calculates a page's total hits

	global $NO_COUNT, $a_db;

	if ($NO_COUNT){
		return;
	}

	init_db("user");

	$rank_style = $a_db['user']['rstyle'];
	$registered = false;

	$pagename = str_replace("//", "/", $_SERVER['REQUEST_URI']);
	$pagename = strtolower($pagename);

	if (substr_count($pagename, "http://www.") == 0 && substr_count($pagename, "http://") > 0)
		$pagename = str_replace("http://", "http://www.", $pagename);	

	if ($rank_style == 2){
		if (substr_count($pagename, "?") > 0){
			$pagename = substr($pagename, 0, strpos($pagename, "?"));
		}
	} 
	
	else {
	//Remove any session ID's that may be passed with the url
	$ba = Array("?", "&");	
	if (strlen($a_db['general']['dvars']) > 0){
		for ($i=0; $i<2; $i++){
			$a_dvars = explode('*', $a_db['general']['dvars']);
			for ($b=0; $b<count($a_dvars); $b++){
				if (substr_count($a_dvars[$b], '=') == 0){
					$a_dvars[$b] .= '=';
				}
				if (substr_count($pagename, $ba[$i] . $a_dvars[$b]) > 0){
					$argpos = strpos($pagename, $a_dvars[$b]);
					$f_1 = strpos($pagename, "&", $argpos);
			
					$pagebuf = $pagename;
					$pagename = substr($pagebuf, 0, $argpos-1);
			
					if ($f_1 !== FALSE)
						$pagename .= substr($pagebuf, $f_1);						
	}}}}
	else {
	for ($i=0; $i<2; $i++){
			if (substr_count($pagename, $ba[$i] . "phpsessid=") > 0){
				$argpos = strpos($pagename, "phpsessid=");
				$f_1 = strpos($pagename, "&", $argpos);
	
				$pagebuf = $pagename;
				$pagename = substr($pagebuf, 0, $argpos-1);
	
				if ($f_1 !== FALSE)
					$pagename .= substr($pagebuf, $f_1);						
			}
			if (substr_count($pagename, $ba[$i] . "sid=") > 0){
				$argpos = strpos($pagename, "sid=");
				$f_1 = strpos($pagename, "&", $argpos);
	
				$pagebuf = $pagename;
				$pagename = substr($pagebuf, 0, $argpos-1);
	
				if ($f_1 !== FALSE)
					$pagename .= substr($pagebuf, $f_1);						
			}
	}}}

	
	//We'll only use this advanced file system if complaints come in about speed
	//This system should really only be used on a site with lots of tracked pages (100+);
	//$safename = "data/track_" . path_to_file(dirname($pagename)) . ".dat";
	//---------------------------------------------------------------------------

	//$safename = "track_" . $_SERVER['SERVER_NAME'] . ".dat";
	$safename = "track_main";

	init_db($safename);

	if (substr_count($pagename, "include/functions.php") > 0 && substr_count($_SERVER['HTTP_REFERER'], $_SERVER['SERVER_NAME']) > 0){
		//Probably linked through an image or object
		$pagename = $_SERVER['HTTP_REFERER'];
	}

	if (!isset($a_db[$safename]['page'])){
		$a_db[$safename]['page'] = Array();
	}

	$sizeof_pages = count($a_db[$safename]['page']);

	for ($i=0; $i<$sizeof_pages; $i++){

		$sel_page = &$a_db[$safename]['page'][$i];

		if ($sel_page['name'] == $pagename){
			$sel_page['hits']++;
			$registered = true;
			break;
		}
		unset($sel_page);
	}

	if (!$registered){
		$a_db[$safename]['page'][$sizeof_pages]['name'] = $pagename;
		$a_db[$safename]['page'][$sizeof_pages]['hits'] = 1;
	}
}

function cmp_users ($a, $b) {
    if ($a['users'] == $b['users'])
	 return 1;

    return ($a['users'] > $b['users']) ? -1 : 1;
}

function cmp_hits ($a, $b) {
    if ($a['hits'] == $b['hits']){	
	 return 0;
    }

    return ($a['hits'] > $b['hits']) ? -1 : 1;
}

function stream_browser_list(){

	global $a_db; 
	init_db("browsers");

	$browsers = $a_db['browsers'];
	usort($browsers, "cmp_users");

	return $browsers;
}

function stream_os_list(){

	global $a_db;
	init_db("systems");

	$systems = $a_db['systems'];
	usort($systems, "cmp_users");

	return $systems;
}

function trends_day(){
	
	//Updates the five day list displayed upon the trends page
	global $a_db, $a_db_handle;

	init_db("daytrend");

	$db_daytrend = &$a_db_handle['daytrend'];
	$current_day = gmdate("mdy", mktime()+time_offset());

	if (!is_array($a_db['daytrend']['day'])){
		$a_db['daytrend']['day'] = Array();
	}
	
	$this_day = NULL; $upgrade = false;
	for ($i=0; $i<count($a_db['daytrend']['day']); $i++){		
		if (gmdate("mdy", $a_db['daytrend']['day'][$i]['when']) == $current_day){
			$this_day = &$a_db['daytrend']['day'][$i];			
		} else if (!is_numeric($a_db['daytrend']['day'][$i]['when'])) {
			$upgrade = true;		
		}
	} 

	if ($upgrade){

		//"Hack" to allow pre 1.0.0.3 upgrade support	

		$day_buf = Array();
		foreach($a_db['daytrend']['day'] as $num=>$value){
			if (is_numeric($value['when'])){
				$day_buf[] = $daybuf;
			}
		}
		$a_db['daytrend']['day'] = $day_buf;
	}

	if (!$this_day) {
		$this_day = &$a_db['daytrend']['day'][count($a_db['daytrend']['day'])];
	}

	$this_day['when'] = mktime()+time_offset();
	$this_day['hits'] = $a_db['general']['todayhits'];
	usort($a_db['daytrend']['day'], "cmp_when");
	$sizeof_day = count($a_db['daytrend']['day']);

	if ($sizeof_day > 5){
		
		$day_buf = Array();
		
		for ($i=0; $i<5; $i++){
			$day_buf[] = $a_db['daytrend']['day'][$i];
		}

		$a_db['daytrend']['day'] = $day_buf;
	}

	$db_daytrend->check_modified();
}

function stream_day_info(){

	//Returns an array of 5 logged trend days

	global $a_db;

	$tdata = Array();
	init_db("daytrend");

	for ($i=4; $i>-1; $i--){
		//Return proper dates if any trend days aren't logged
		$tdata[$i]['when'] = date("M d", mktime(0,0,0, gmdate("m", mktime()+time_offset()), gmdate("d", mktime()+time_offset())-$i, gmdate("Y", mktime()+time_offset())));
		$tdata[$i]['hits'] = 0;
	}

	for ($i=0; $i<count($a_db['daytrend']['day']); $i++){
		$tdata[$i] = $a_db['daytrend']['day'][$i];
		$tdata[$i]['when'] = date("M d", $tdata[$i]['when']);
	}

	return $tdata;	
}

function stream_banned_list(){

	//Returns banned users

	global $a_db;
	init_db("banned");

	return $a_db['banned']['ban'];
}

function stream_visitor_info(){

	//Returns an array of all the visitors	
	global $a_db; 
	init_db("vinfo");
	return $a_db['vinfo']['user'];
}

function stream_visitors_online(){

	//Returns an array of all visitors "online"
	global $a_db;

	$vdata = Array();
	init_db("vonline");

	if (!isset($a_db['vonline']['user'])){		
		$a_db['vonline']['user'] = Array();
	}

	for($i=0; $i<count($a_db['vonline']['user']); $i++){
		$sel_user = $a_db['vonline']['user'][$i];
		
		if ($sel_user['time'] == ""){
			$sel_user['time'] = "?";
		} else {
			$sel_user['time'] = "&nbsp;&nbsp;" . date("M-d-Y h:i:s", $sel_user['time']);
		}
		if ($sel_user['lastpage'] == ""){
			$sel_user['lastpage'] = "?";
		}
		$vdata[] = $sel_user;
	}

	return $vdata;	
}

function stream_page_popularity(){

	//Returns an array of page names and their hits
	global $a_db;
	init_db("track_main");

	if (!is_array($a_db['track_main']['page'])){
		$a_db['track_main']['page'] = Array();
	}

	$pages = $a_db['track_main']['page'];
        
	usort($pages, "cmp_hits");
	return $pages;
}

function update_user($newuser, $newpass = ""){

	//Update user's password and username
	global $a_db;
	init_db("user");
	
	if (strlen($newuser) > 0){
		$a_db['user']['username'] = $newuser;
	}
	if (strlen($newpass) > 0){
		$a_db['user']['uip'] = md5($newpass);
	}
}

function require_login(){
	
	//Called by pages requiring admin status
	//Returns TRUE if logged in, FALSE if not
	
	global $a_db;
	init_db("user");

	if (isset($_COOKIE['ES_UN']) && isset($_COOKIE['ES_UIP'])){
		//The cookies exist, now check the values
		if ($_COOKIE['ES_UN'] == $a_db['user']['username']){
			if (md5($_COOKIE['ES_UIP']) == $a_db['user']['uip']){
			//Success
			return TRUE;
			}
		}
	}

	//User is not logged in

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Administrator Login</b></font><BR><BR>
	<font face=arial size=2 color=black>You must login prior to accessing this page:<BR><BR><BR>
	<form action=index.php?act=plogin method=post>
	<b>Username:</b> &nbsp;&nbsp;<input type=text name=user size=20><BR><BR>
	<b>Password:</b> &nbsp;&nbsp;<input type=password name=pass size=12><BR><BR><BR>
	<input type=submit value=\"Proceed\"> &nbsp;&nbsp; <input type=reset value=\"Reset\">
	<BR><BR><BR><BR><BR>Show your support: &nbsp; <font color=blue><a href=\"index.php?act=donate\" target=_newWindow>Click here to support Elite Stats...</a></blue>
	</form>	</font>";	

	Message($message);

	return FALSE;
}

function try_login($username, $password){
	
	//Try to login using a certain User/Pass
	//Return TRUE on success and FALSE on failure

	global $a_db;
	init_db("user");

	if ($username == $a_db['user']['username']){
		
		//Username matches, check the password:		
		if (md5($password) == $a_db['user']['uip']){
			//Login was a success
			setcookie ("ES_UN", $username ,time()+10800);
			setcookie ("ES_UIP", $password ,time()+10800);
			return TRUE;
		}
	}

	return FALSE;
}

function ban_action(){

	global $NO_COUNT;

	$ban_result = is_banned($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
	if ((int)$ban_result == (int)BAN_IGNORE){	
		$NO_COUNT = true;
	}
	else if ($ban_result){
	        @header("Location: http://".$_SERVER['HTTP_HOST'] .dirname($_SERVER['PHP_SELF']) . "/index.php?act=isbanned");
		exit("You have been banned from this site.");
	}
}

function ban_user($ip, $agent, $ignore = false){

	//Ban a user based on IP Address and Agent
	global $a_db, $db_banned;
	init_db("banned");

	if (is_banned($ip, $agent)) {
		return; 
	}

	if (substr_count($ip, ".") > 1){
		$t_ip = substr($ip, 0, strlpos($ip, "."));
	} else {
		$t_ip = $ip;	
	}		
	
	$this_ban = Array();
	$this_ban['ip'] = $t_ip;
	$this_ban['agent'] = strtolower($agent); 
	$this_ban['when'] = gmdate("M-d-Y h:i:s", mktime()+time_offset());

	if ($ignore) {
		$this_ban['ignore'] = 1;
	} else {
		$this_ban['ignore'] = 0;
	}

	$a_db['banned']['ban'][count($a_db['banned']['ban'])] = $this_ban;
}

function is_banned($ip, $agent){

	//Return TRUE if banned, FALSE if not
	global $a_db;
	$agent = strtolower($agent);
	init_db("banned");

	if (substr_count($ip, ".") > 1){
		$ip = substr($ip, 0, strlpos($ip, "."));
	}

	if (!isset($a_db['banned']['ban'])){
		$a_db['banned']['ban'] = Array();
	}

	for ($i=0; $i<count($a_db['banned']['ban']); $i++){
		$sel_ban = $a_db['banned']['ban'][$i];
		if ($sel_ban['ip'] == $ip && strtolower($sel_ban['agent']) == $agent){

			if (isset($_COOKIE['ES_IP'])){
				setcookie ("ES_IP", $_COOKIE['ES_IP'], time()-(60*60*24*30));
			}

			setcookie ("ES_IP", $ip, time()+(60*60*24*60));

			if ($sel_ban['ignore'] == 1){
				return BAN_IGNORE;
			}

			return TRUE;

		} else if ((isset($_COOKIE['ES_IP'])) && $sel_ban['ip'] == $_COOKIE['ES_IP']){

			if ($sel_ban['ignore'] == 1){
				return BAN_IGNORE;
			}
			return TRUE;
		}
	}

	return FALSE;
}

function get_page_time(){

	global $start_time, $a_db;
	$duration = round(microtime_diff($start_time, microtime()), 3);

	if (!isset($a_db['general']['timesloaded']))
		$a_db['general']['timesloaded'] = 0;
	if (!isset($a_db['general']['meantime']))
		$a_db['general']['meantime'] = 0;

	$a_db['general']['timesloaded']++;
	$a_db['general']['meantime'] = round((($a_db['general']['meantime']*($a_db['general']['timesloaded']-1)) + $duration) / $a_db['general']['timesloaded'], 3);
}

function global_hit(){

	global $NO_COUNT, $dir_this, $a_db;

	try_firstdate();

	//First increase Total Hits
	//------------------

	//Get the current hit count
	$hits = $a_db['general']['totalhits'];
	$original = $hits;

	if ($hits < 0 || !is_numeric($hits))
		$hits=0;

	//Add the hit
	if (!$NO_COUNT)
	$hits++;
	$a_db['general']['totalhits'] = $hits;

	if ($hits != 0){
	$ten = $hits/10;

	if (substr_count($ten, ".") == 0){

		//Divisible of 10, Check with emergency backup
		if (!file_exists($dir_this . "data/backup_emergency.bak")){
			backup_data(true);
		}

		$backup = new C_Backup($dir_this . "data/backup_emergency.bak");
		$general_backup = NULL;

		$b_files = $backup->backup_db->a_data['b_file'];
		for ($i=0; $i<count($b_files); $i++){
			if (substr_count($b_files[$i]['name'], "general.dat") > 0){
				$tempdb = &new C_DBFile("", DB_CREATE | DB_NO_AUTOMODIFY | DB_VIRTUAL);
				$tempdb->DB_Read($b_files[$i]['data'], true);
				$general_backup = $tempdb->a_data;
			}
		}

		if (($general_backup) && $general_backup['hits'] > $hits){
			restore_backup(true);
		} else {
			backup_data(true);
		}

	}}

	//------------------

	$vars_hits = Array(); $vars_last = Array(); $vars_date = Array();
	$vars_hits[] = "hourhits";	$vars_last[] = "lasthour";	$vars_date[] = "hd";
	$vars_hits[] = "todayhits";	$vars_last[] = "lastdate";	$vars_date[] = "dY";
	$vars_hits[] = "weekhits";	$vars_last[] = "lastweek";	$vars_date[] = "W";
	$vars_hits[] = "monthhits";	$vars_last[] = "lastmonth";	$vars_date[] = "m";

	for ($i=0; $i<count($vars_hits); $i++){
		
		$hits = &$a_db['general'][$vars_hits[$i]];		
		if ($hits < 0 || !is_numeric($hits)){
			$hits = 0;
		} 

		$lastdate = $a_db['general'][$vars_last[$i]];
		if ($lastdate < 0 || !is_numeric($lastdate)){
			$lastdate = gmdate($vars_date[$i], mktime()+time_offset());
		}
		if ($lastdate != gmdate($vars_date[$i], mktime()+time_offset())){
			$hits=0;
		}

		if (!$NO_COUNT){ $hits++; }

		unset($hits);
	}
	
	$vars_unique = Array(); $vars_cookie = Array();
	$vars_unique[] = "hourunique";	$vars_cookie[] = "ES_LastHour";
	$vars_unique[] = "todayunique";	$vars_cookie[] = "ES_LastDay2";
	$vars_unique[] = "weekunique";	$vars_cookie[] = "ES_LastWeek";
	$vars_unique[] = "monthunique";	$vars_cookie[] = "ES_LastMonth";

	for ($i=0; $i<count($vars_unique); $i++){
		$unique = &$a_db['general'][$vars_unique[$i]];

		if ($unique < 0 || !is_numeric($unique))
			$unique=0;

		$last_date = $a_db['general'][$vars_last[$i]];
		$this_date = gmdate($vars_date[$i], mktime()+time_offset());

		if ($last_date != $this_date){
			$a_db['general'][$vars_last[$i]] = $this_date;
			$unique = 0;
		}

		if ($NO_COUNT){	unset($unique); continue; }

		if ((isset($_COOKIE[$vars_cookie[$i]])) && $_COOKIE[$vars_cookie[$i]] != $this_date){		
			$unique++;
		} else if (!isset($_COOKIE[$vars_cookie[$i]])){
			$unique++;
		}

		if (isset($_COOKIE[$vars_cookie[$i]])){
			//I'm positive this isn't needed, but just in case.
			setcookie ($vars_cookie[$i], $_COOKIE[$vars_cookie[$i]], time()-(60*60*24*1));
		}

		setcookie ($vars_cookie[$i], $this_date, time()+(60*60*24*1));

		unset($unique);
	}


	//Total Unique
	//---------------------

	$unique = &$a_db['general']['totalunique'];

	if ($unique < 0 || !is_numeric($unique))
		$unique=0;

	if (!isset($_COOKIE['ES_Counted']) && !$NO_COUNT){
		$unique++;
	}

	if (!$NO_COUNT){
		unset($_COOKIE['ES_Counted']);
		setcookie ("ES_Counted", 1 ,time()+99999999);
	}

	//----------------------


	//Check for record changes
	//------------------------
	check_records();
	//------------------------

	//Log Visitor Info
	//------------------------
	visitor_info();
	//------------------------

	//Update 5 Day Trend
	//------------------------
	trends_day();
	//------------------------

	//"Currently Online"
	//------------------------
	visitor_info_online();
	//------------------------

	//Page Popularity
	//------------------------
	page_popularity();
	//------------------------
}

function is_secret(){
	
	global $a_db;
	init_db("user");

	//Return TRUE if private, FALSE if not
	if ($a_db['user']['privacy'] == "private")
		return TRUE;

	return FALSE;
}

function stat_value($name){

	//Retrieve a stat based on name

	global $dir_this, $a_db;
	$name = strtolower($name);

	if ($name == "totalhits" || $name == "hits")
		return $a_db['general']['totalhits'];
	else if ($name == "totalvisitors" || $name == "totalunique" || $name == "visitors" || $name == "unique")
		return $a_db['general']['totalunique'];
	else if ($name == "hourhits" || $name == "hourshits" || $name == "hitshour")
		return $a_db['general']['hourhits'];
	else if ($name == "hourunique" || $name == "hoursunique" || $name == "uniquehour"){
		$total = $a_db['general']['totalunique'];
		$hour = $a_db['general']['hourunique'];
		if ($total < $hour)
			$hour = $total;

		return $hour;
	}
	else if ($name == "todayhits" || $name == "todayshits" || $name == "hitstoday")
		return $a_db['general']['todayhits'];
	else if ($name == "todayunique" || $name == "todaysunique" || $name == "uniquetoday"){
		$total = $a_db['general']['totalunique'];
		$today = $a_db['general']['todayunique'];
		if ($total < $today)
			$today = $total;

		return $today;
	}	
	else if ($name == "monthhits" || $name == "monthshits" || $name == "hitsmonth")
		return $a_db['general']['monthhits'];
	else if ($name == "weekhits" || $name == "hitsweek")
		return $a_db['general']['weekhits'];
	else if ($name == "weekunique" || $name == "weeksunique")
		return $a_db['general']['weekunique'];
	else if ($name == "monthunique" || $name == "monthsunique")
		return $a_db['general']['monthunique'];
	else if ($name == "averagehits" || $name == "averagedayhits")
		return $a_db['general']['average_hits_day'];
	else if ($name == "record_day" || $name == "dayrecord")
		return $a_db['general']['recorddayhits'];
	else if ($name == "record_day_date" || $name == "dayrecorddate")
		return $a_db['general']['recorddaydate'];
	else if ($name == "record_week" || $name == "weekrecord")
		return $a_db['general']['recordweekhits'];
	else if ($name == "record_user_date" || $name == "userrecorddate")
		return $a_db['general']['recorduserdate'];
	else if ($name == "record_user" || $name == "userrecord")
		return $a_db['general']['recordusers'];
	else if ($name == "averagetime")
		return $a_db['general']['meantime'];
	else if ($name == "averageunique" || $name == "averageuniquehits")
		return $a_db['general']['average_unique_day'];
	else if ($name == "days" || $name == "totaldays"){
		$firstdate = $a_db['general']['firstdate'];
		$days = timediff_days(date("m", $firstdate), date("d", $firstdate), date("Y", $firstdate));

		if ($days < 0 || !is_numeric($days)){
			$days = 0;
		}
		$days++;

		return $days;
	}
	else if ($name == "online"){
		init_db("vonline");
		return count($a_db['vonline']['user']);
	}
	
	return 0;	
}



ob_end_flush();
?>