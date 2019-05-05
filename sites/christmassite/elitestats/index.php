<?php

$debug = false; $NO_COUNT = !$debug;

ob_start();

if (!(isset($_GET['act']) && $_GET['act'] == "isbanned"))
	require("include/functions.php");

function Message($content, $headtitle="<font color=white face=arial style='font-size:15px'><b>Elite Statistics Center</b></font>",$title = "Elite Stats", $baseheight=25){

	if (substr_count(strtolower(getbrowser($_SERVER['HTTP_USER_AGENT'])), "explorer") > 0){
		$extra_br = "<br>";
	}
	else 
		$extra_br = "";

	$menu_items = Array();
	$menu_links = Array();
	$menu_items[] = "Main Menu";		$menu_links[] = "";
	$menu_items[] = "Summary";		$menu_links[] = "index.php?act=summary";;
	$menu_items[] = "Predictions";		$menu_links[] = "index.php?act=predict";
	$menu_items[] = "Averages";		$menu_links[] = "index.php?act=average";
	$menu_items[] = "Recent Visitors";	$menu_links[] = "index.php?act=lastinfo";
	$menu_items[] = "Visitors Online";	$menu_links[] = "index.php?act=onlinenow";
	$menu_items[] = "Page Ranking";		$menu_links[] = "index.php?act=page_sel_pop";
	$menu_items[] = "Records";		$menu_links[] = "index.php?act=records";
	$menu_items[] = "Browsers";		$menu_links[] = "index.php?act=browsers";
	$menu_items[] = "OS Overview";		$menu_links[] = "index.php?act=systems";
	$menu_items[] = "Banned IP's";		$menu_links[] = "index.php?act=banlist";
	$menu_items[] = "5 Day Trend";		$menu_links[] = "index.php?act=trend";
	$menu_items[] = "Contact/Help";		$menu_links[] = "index.php?act=help";
	$menu_items[] = "Administration";	$menu_links[] = "";
	$menu_items[] = "Settings";		$menu_links[] = "index.php?act=settings";
	$menu_items[] = "Password";		$menu_links[] = "index.php?act=pword";
	$sizeof_items = count($menu_items);

	$menu_contents = "<font color=black face=tahoma>";

	for ($i=0; $i<$sizeof_items; $i++){
		if ($i > $sizeof_items - 2){
			$breaks = $extra_br;
		}
		else if ($i == 0)
			$breaks = "";
		else 
			$breaks = $extra_br . "<br>";
		if (strlen($menu_links[$i]) > 0){			
			$menu_contents .= $breaks . "<LI><a href=" . $menu_links[$i] . "><font size=2 color=black>" . $menu_items[$i] . "</font></a></LI>";
		} else {						
			$menu_contents .= $breaks . "<br><b>" . $menu_items[$i] . "</b>";
			if (strlen($extra_br) == 0)
				$menu_contents .= "<br>";
		}
	}
	$menu_contents .= "<BR><BR></font>";

	echo "

	<HTML>
	<TITLE>" . $title . "</TITLE>
	<HEAD>
	<style type='text/css'>
	<!--
	font { font-size : 13px;}
	-->
	</style>
	</HEAD>
	
	<body bgcolor=white text=black>
	<center>
	<table width=700 height=86 cellpadding=0 cellspacing=0>
	<td width=100% height=100% bgcolor=white background='imgs/logo.png'>
	</td>
	</table>

	<table width=700 height=5 cellspacing=0 cellpadding=0>
	<td width=100% height=100% bgcolor=white></td>
	</table>

	<table width=700 height=2 cellspacing=0 cellpadding=0>
	<td width=160 bgcolor=white></td>
	<td width=30 bgcolor=white></td>
	<td width=510 bgcolor=black></td>
	</table>

	<table width=700 height=300 cellspacing=0 cellpadding=0>
	<td width=160 height=300 bgcolor=white valign=top>

	<table width=160 height=24 cellspacing=0 cellpadding=0>
	<td background='imgs/topbar.png' bgcolor='white'></td>
	</table>
	<table width=160 height=273 cellspacing=0 cellpadding=0>
	<td width=2 bgcolor=black></td>
	<td width=15 bgcolor=#F9F9F9></td>
	<td width=141 bgcolor=#F9F9F9 valign=bottom>
	" . $menu_contents . "
	</td>
	<td width=2 bgcolor=black></td>
	</table>

	<table width=160 height=2 cellspacing=0 cellpadding=0>
	<td width=160 height=2 bgcolor=black></td>
	</table>

	<table width=160 height=25 cellspacing=0 cellpadding=0>
	<td width=160 height=25 bgcolor=white></td>
	</table>
	</td>

	<td width=30 bgcolor=white></td>

	<td width=510 valign=top bgcolor=white>
	<table width=510 height=298 cellspacing=0 cellpadding=0>
	<td width=2 bgcolor=black></td>
	<td width=20 bgcolor=white></td>
	<td width=456 bgcolor=white valign=top>
	" . $content . "<BR><BR>
	</td>
	<td width=30 bgcolor=white></td>
	<td width=2 bgcolor=black></td>
	</table>
	<table width=510 height=2 cellspacing=0 cellpadding=0>
	<td width=510 bgcolor=black></td>
	</table>
	</td>
	</table>

	</center>";
}


if (isset($_GET['act'])){ $act = strtolower($_GET['act']);
} else { $act = ""; }

if ($act == "summary"){

	//Check page's privacy filter
	privacy_check();

	$stat_names = Array(); $stat_vars = Array();

	$stat_names[] = "Totals";			$stat_vars[] = "";
	$stat_names[] = "Total Hits";			$stat_vars[] = "totalhits";
	$stat_names[] = "Total Unique Visitors";	$stat_vars[] = "totalunique";
	$stat_names[] = "Hour";				$stat_vars[] = "";
	$stat_names[] = "Hits This Hour";		$stat_vars[] = "hourhits";
	$stat_names[] = "Unique Visitors";		$stat_vars[] = "hourunique";
	$stat_names[] = "Today";			$stat_vars[] = "";
	$stat_names[] = "Hits Today";			$stat_vars[] = "todayshits";
	$stat_names[] = "Unique Visitors";		$stat_vars[] = "todaysunique";

	$stat_names[] = "break";			$stat_vars[] = "";

	$stat_names[] = "This Week";			$stat_vars[] = "";
	$stat_names[] = "Hits This Week";		$stat_vars[] = "weekhits";
	$stat_names[] = "Unique Visitors";		$stat_vars[] = "weekunique";
	$stat_names[] = "This Month";			$stat_vars[] = "";
	$stat_names[] = "Hits This month";		$stat_vars[] = "monthhits";
	$stat_names[] = "Unique Visitors";		$stat_vars[] = "monthunique";

	$col_2 = $col_1 = "";
	$col = &$col_1;

	for ($i=0; $i<count($stat_names); $i++){
		if ($stat_names[$i] == "break"){
			unset($col);
			$col = &$col_2;
			continue;
		}
		if (strlen($stat_vars[$i]) == 0){
			$col .= "<br><b><u>" . $stat_names[$i] . ":</u></b><br>";
		}
		else {
			$col .=  $stat_names[$i] . ": " . stat_value($stat_vars[$i]) . "<br>";
		}
	}
	

	$message = "

	<table width=100% height=50 cellpadding=0 cellspacing=0>
	<td width=100% height=100% valign=top>	
	<font face=arial style='font-size:17px'><BR><b>Statistic Summary</b></font>
	<BR><BR><font face=arial>This is simply a brief overview of some of your site statistics, more detailed summaries are available.<BR></font>
	</td></table>

	<table width=100% height=225 cellpadding=0 cellspacing=0>
	<td width=50% height=100% valign=top>
	<font face=arial>" . $col_1 . "</font>
	</td>
	<td width=50% height=100% valign=top>
	<font face=arial><br><br>" . $col_2 . "</font>
	</td>
	</table>
	</font>";	

	Message($message);
}
else if ($act == "faq"){

	$message = "

	<BR><font face=arial style='font-size:17px' color=black><b>Frequently Asked Questions</b></font><BR><BR><font face=arial size=2 color=black>These are our answers to questions that we are regularly asked, see the <i>Readme</i> for extended information.<br><br>
	<br><br><b>Question:</b> When I start Elite Stats, I get a whole bunch of errors, what have I done wrong?     
	<br><br><b>Answer:</b> If one of the Elite Stats pages says something like \"cannot write to vinfo.dat\" make sure all of your files were uploaded and chmodded correctly. Also, be sure your server supports PHP. Another common mistake is forgetting to chmod your Elite Stats folder to 777 (or all boxes checked). 
	<br><br><br><b>Question:</b> On the page that called require(), I get an error saying header have already been sent. What's is wrong and can it be fixed?
	<br><br><b>Answer:</b> This occurs when header output has already started before the require line for Elite Stats has been executed, to fix it simply put the require line as the first line (be sure it's inside a php tag). If that does not work, then add the following line to the very top of the document \"<?php ob_start(); ?>\" (without quotes) and then add this line to the very bottom: \"<?php ob_end_flush(); ?>\"
	<br><br><br><b>Question:</b> My Elite Stats pages say that permission was denied to /data/file , how do I fix it?     
	<br><br><b>Answer:</b> You have chmodded the folders in data incorrectly or your server does not support chmodding, the data folder and all the files in it should be chmodded to 777, (or all boxes checked).
	<br><br><br><b>Question:</b> On the page that called require(), I get an error saying it cannot find the file. What do I do to fix this?
	<br><br><b>Answer:</b> First check to make sure the file is uploaded, if it is and you still get the error, than your require location is most likely wrong, remember the require function looks for the file in the current directory. So for example, if your page is located at \"/pages/page.php\" and Elite Stats is located in \"/stats/\" (not \"/pages/\"), then you'd use: \"require(\"../stats/include/functions.php\")\". 
	<br><br><br><b>Question:</b> I just installed Elite Stats and cannot login with the username 'Admin' and the password 'admin', is this a problem with my server?
	<br><br><b>Answer:</b> It's possible that your server has an old version of PHP (less than 4.1), but more than likely the problem is that your browser either does not support cookies, or does not have them enabled. Also make sure that you uploaded all the files in \"/data/\" properly. If you cannot figure out how to enable cookies, visit your browser support site.
	<br><br></font>";	

	Message($message);
}
else if ($act == "donate"){

	$message = "
	<font face=arial style='font-size:17px'><br><b>Support Elite Stats...</b></font>
	<br><br><font face=arial>By making a small donation to the developer behind Elite Stats, you'll help development immensly, without your support I cannot continue to release updates or entirely new products. Donations will go towards further developing products such as this, and in the end you get a more streamlined and powerful product. All donations are accepted through <b>PayPal</b>, please click the button below to continue.<br><br></font>
	<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\">
	<input type=\"hidden\" name=\"cmd\" value=\"_xclick\">
	<input type=\"hidden\" name=\"business\" value=\"resident_nutcase@hotmail.com\">
	<input type=\"hidden\" name=\"item_name\" value=\"FireStorm Research and Development\">
	<input type=\"hidden\" name=\"item_number\" value=\"EliteStats\">
	<input type=\"hidden\" name=\"no_shipping\" value=\"1\">
	<input type=\"hidden\" name=\"cn\" value=\"Questions or Comments\">
	<input type=\"hidden\" name=\"currency_code\" value=\"USD\">
	<input type=\"hidden\" name=\"tax\" value=\"0\">
	<input type=\"hidden\" name=\"lc\" value=\"US\">
	<input type=\"image\" src=\"https://www.paypal.com/en_US/i/btn/x-click-but21.gif\" border=\"0\" name=\"submit\" alt=\"Make payments with PayPal - it's fast, free and secure!\">
	</form>	<br><br>
	";	
	Message($message);
}
else if ($act == "checkupdate"){

	$message = "
	<font face=arial style='font-size:17px'><br><b>&nbsp;&nbsp;Checking for Updates...</b></font>
	<br><br><font face=arial size=2 color=black><br>
	<iframe src=\"http://firestorm.all-interviews.com/version.php?id=2&v=$alpha_version\" frameborder=0 width=90% height=175></iframe>
	<br><br></font>
	";	

	Message($message);
}
else if ($act == "help"){

	$help_titles = Array(); $help_links = Array(); $help_info = Array();

	$help_titles[] 	 = "Contact FireStorm";
	$help_links[] 	 = "mailto:support@all-interviews.com?subject=-Regarding Elite Stats-";
	$help_info[] 	 = "If you have any bug reports, questions or comments, click here to e-mail me.";

	$help_titles[] 	 = "FireStorm Website";
	$help_links[] 	 = "http://firestorm.all-interviews.com?act=elitestats";
	$help_info[] 	 = "Visit the official FireStorm website and learn more about our services and products.";

	$help_titles[] 	 = "Support Forum";
	$help_links[] 	 = "http://forum.all-interviews.com";
	$help_info[] 	 = "If you don't want to e-mail your questions or comments, you may use our support forum.";

	$help_titles[] = "break"; $help_links[] = ""; $help_info[] = "";

	$help_titles[] 	 = "FAQ";
	$help_links[] 	 = "index.php?act=faq";
	$help_info[] 	 = "Commonly asked questions and how to deal with them.<br>";

	$help_titles[] 	 = "Donate";
	$help_links[]	 = "index.php?act=donate";
	$help_info[] 	 = "Without donations what happens? Nothing... Support Elite Stats and its development by donating.";

	$help_titles[]	 = "Check for Updates";
	$help_links[]	 = "index.php?act=checkupdate";
	$help_info[]	 = "Check to see if any Elite Stats updates are available from FireStorm Research and Development.";

	$col_2 = $col_1 = "";
	$col = &$col_1;

	for ($i=0; $i<count($help_titles); $i++){
		if ($help_titles[$i] == "break"){
			unset($col);
			$col = &$col_2;
		} else {
			$col .= "\r\n	<br><br><a href='" . $help_links[$i] . "'><b><font color=black>" . $help_titles[$i] . "</font></b></a> - \r\n	<br><br>" . $help_info[$i] . "<br>";
		}
	}

	$message = "
	<table width=100% height=50 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top>
	<br><font face=arial style='font-size:17px'><b>Elite Stats Help</b></font>
	<br><br><font face=arial>It is suggested that you first refer to the readme file (readme.txt) prior to consulting this page.<br></font>
	</td></table>

	<table width=100% height=225 cellpadding=0 cellspacing=0>
	<td width=43% height=100% valign=top><font face=arial>" . $col_1 . "</font></td>
	<td width=10% height=100% valign=top></td>
	<td width=43% height=100% valign=top><font face=arial>" . $col_2 . "</font></td>
	<td width=4% height=100% valign=top></td>
	</table>

	<table width=100% height=80 cellpadding=0 cellspacing=0><td width=100% height=100% valign=bottom><font face=arial><small>Contact also possible via AOL Instant Messenger, screenname \"Griblik3\"</small></font></td></table>";

	Message($message);
}
else if ($act == "browsers"){

	//Check page's privacy filter
	privacy_check();

	$message = "
	<table width=100% height=100 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top><BR><font face=arial style='font-size:17px' color=black><b>Browser Overview</b></font><BR><BR><font face=arial size=2 color=black>The following is based on unique visitors and compares the usage of different browsers in which visitors use to display your site.<BR><BR><BR><BR></font></td></table>
	<table width=100% height=175 cellpadding=0 cellspacing=0>

	<td width=95% height=100% valign=top>
	<font face=arial size=2 color=black>";

	$browser = stream_browser_list();

	$count = 0;
	$highest = "";

	foreach($browser as $key=>$bi) {				
		$count++;
		if ($bi['users'] > $highest)
			$highest = $bi['users'];
	}

	$percent = 70;
	$left= 0;
	$totalheight = $count*45;

	for ($i=0; $i < $count;	$i++){
		$browser[$i]['name'] = str_replace("Internet Explorer", "Microsoft IE", $browser[$i]['name']);
		$percent = (70/$highest)*$browser[$i]['users'];
		if ($percent < 1){
			$percent = 1;
		}
		$left = 70 - $percent;
		
		$message .= "
		<table width=100% height=35 cellpadding=0 cellspacing=0>
		<td width=20% height=100% valign=center><font>" . force_wrap($browser[$i]['name'], 13) . "</font></td>
		<td width=" . ceil($percent) . "% height=100% valign=top bgcolor=darkblue background='imgs/graph.png'>
		</td>
		<td width=" . floor($left) . "% height=100%>
		</td>
		<td width=2% height=100%>
		</td>
		<td width=8% height=100% valign=center align=center>
		<font>" . $browser[$i]['users'] . "</font>
		</td>
		</table>
		<table width=100% height=10 cellpadding=0 cellspacing=0>
		<td width=100% height=100% valign=top>
		</td>
		</table>
		";
	}	

	$message .= "
	</font>
	</td>

	<td width=5% height=100% valign=top>
	</td>

	</table>
	</font>";	
	Message($message);
}
else if ($act == "systems"){

	//Check page's privacy filter
	privacy_check();

	$message = "
	<table width=100% height=100 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top><BR><font face=arial style='font-size:17px' color=black><b>Operating System Overview</b></font><BR><BR><font face=arial size=2 color=black>The following is based on unique visitors and compares the usage of Operating Systems people are using when they visit your site.<BR><BR><BR><BR></font></td></table>
	<table width=100% height=175 cellpadding=0 cellspacing=0>

	<td width=95% height=100% valign=top>
	<font face=arial size=2 color=black>";

	$system = stream_os_list();

	$count = 0;
	$highest = "";

	foreach($system as $si) {
		$count++;
		if ($si['users'] > $highest)
			$highest = $si['users'];
	}

	$percent = 70;
	$left= 0;
	$totalheight = $count*45;
	for ($i=0; $i < $count;	$i++){
		
		$percent = (70/$highest)*$system[$i]['users'];
		if ($percent < 1){
			$percent = 1;
		}
		$left = 70 - $percent;
		
		$message .= "
		<table width=100% height=35 cellpadding=0 cellspacing=0>
		<td width=20% height=100% valign=center><font>" . $system[$i]['name'] . "</font>
		</td>
		<td width=" . ceil($percent) . "% height=100% valign=top bgcolor=darkblue background='imgs/graph.png'>
		</td>
		<td width=" . floor($left) . "% height=100% valign=top>
		</td>
		<td width=2% height=100% valign=top>
		</td>
		<td width=8% height=100% valign=center>
		<font>" . $system[$i]['users'] . "</font>
		</td>
		</table>
		<table width=100% height=10 cellpadding=0 cellspacing=0>
		<td width=100% height=100% valign=top>
		</td>
		</table>
		";
	}	

	$message .= "</font></td><td width=5% height=100% valign=top></td></table></font>";	
	Message($message);
}
else if ($act == "onlinenow"){

	//Check page's privacy filter
	privacy_check();

	$vinfo = stream_visitors_online();
	$curinf = 0;	


	$message = "
	<BR><font face=arial style='font-size:17px' color=black><b>Visitors Currently Online</b></font><BR><BR><font face=arial size=2 color=black>To see more detailed information about recent visitors on your site click on \"Recent Visitors\" from the left navigation menu.
	<br><br>The most visitors ever online was <b>" . stat_value("record_user") . "</b> (" . stat_value("record_user_date") . ")
	<br>There are currently <b>" . count($vinfo) . "</b> visitor(s) on your site.<br><br>
	";

	foreach($vinfo as $cvi) {
		$curinf++;
		$message .= "<b>User #" . $curinf . "</b><BR><u>IP Address:</u> " . $cvi['ip'] . "<BR><BR><u>Currently Viewing:</u> " . $cvi['lastpage'] . "<BR><u>Viewing page since:</u> " . $cvi['time'] . "<BR><BR><BR>";
	}

	$message .= "</font>";	
	Message($message);
}

else if ($act == "page_sel_pop"){

	//Check page's privacy filter
	privacy_check();

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Page Ranking</b></font><BR><BR><font face=arial size=2 color=black>Please select whether you wish to view each ranking by percent or by total number of viewings:<BR><BR>
	<b>Select Display Style:</b><BR><BR><a href=index.php?act=page_hits_pop&style=2><font color=blue><u>View rankings by percent</u></font></a>
	<BR><BR><a href=index.php?act=page_hits_pop><font color=blue><u>View rankings by hits</u></font></a>
	<BR><BR><BR><font face=arial size=2 color=black><b>Administration: </b>&nbsp;&nbsp;&nbsp;<BR><BR><a href=index.php?act=rankreset><u><font color=blue>Click here to reset page ranks</font></u></a>
	<BR><BR><a href=index.php?act=discludevars><u><font color=blue>Click here to setup variable disclusion</font></u></a></font>
	</font>";
	
	Message($message);
}

else if ($act == "discludevars"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	if (!isset($_GET['mode']))
		$_GET['mode'] = "";

	$mode = $_GET['mode'];

	if (strlen($a_db['general']['dvars']) != 0)
		$a_dvars = explode('*', $a_db['general']['dvars']);
	else
		$a_dvars = Array();

	if (count($a_dvars) == 0){ $a_dvars[0] = "phpsessid"; $a_dvars[] = "search"; $a_dvars[] = "date"; $a_dvars[] = "time"; $a_dvars[] = "len"; $a_dvars[] = "string"; $a_dvars[] = "str";}

	if ($mode == "delete"){
		$dvar = strtolower($_POST['dvars']);
		for ($i=0; $i < count($a_dvars); $i++){
			if (strtolower($a_dvars[$i]) == $dvar){
				$a_dvars_buf = Array();				
				for ($b=0; $b < count($a_dvars); $b++){
					if (strtolower($a_dvars[$b]) != $dvar){
						$a_dvars_buf[] = $a_dvars[$b];
					}
				}
				$a_dvars = $a_dvars_buf; 
				$a_db['general']['dvars'] = implode($a_dvars, '*');
				break;
			}
		}
	} else if ($mode == "add"){
		$new = strtolower($_POST['dvar']); $srch_result = array_search($new, $a_dvars);
		if ($srch_result == FALSE && $srch_result == ""){
			$a_dvars[count($a_dvars)] = $new;
			$a_db['general']['dvars'] = implode($a_dvars, '*');
		}
	}

	$selbox = "<select name='dvars' size=5 multiple>";

	for ($i=0; $i < count($a_dvars); $i++){
		str_replace('=', "", $a_dvars[$i]);
		$selbox .= "<option value=" . $a_dvars[$i] . ">Variable: " . $a_dvars[$i] . "</option>";
	}
	$selbox .= "<option value=0>" . str_repeat("&nbsp;", 55) . "</option></select>";
	
	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Variable Disclusion</b></font><BR><BR><font face=arial size=2 color=black>If your site uses any url variables which almost never contain the same value or are unimportant to page navigation, it is suggested that you block the variable(s) from page ranking. <BR><BR><b>For example:</b><BR>index.php?act=login&uid=8929&time=7_14_04<BR><BR>You'd want to block \"uid\" and \"time\" as they will flood your page ranking with undesirable urls. This way the page would then be counted as 'index.php?act=login'.<BR><BR><BR>Blocked Variables:<BR><BR>
	<form action=index.php?act=discludevars&mode=delete method=POST>" . $selbox . "<BR><BR><input type=submit value='Remove Variable'></form>
	<BR><BR><b>Add a new variable:</b><BR><BR><form action=index.php?act=discludevars&mode=add method=POST><input type=text size=25 name='dvar'><BR><BR><input type=submit value='Add Variable'></form><BR><BR><BR>
	</font>";
	
	Message($message);
}

else if ($act == "rankreset"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	fclose(fopen("data/track_main.dat", 'w'));

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Page Ranking Reset</b></font><BR><BR><font face=arial size=2 color=black>The Elite Stats page ranks have been successfully reset.<BR><BR><a href=index.php?act=page_sel_pop><u><font color=blue>Click here to return to menu</font></u></a><BR><BR>
	</font>";
	
	Message($message);
}

else if ($act == "page_hits_pop"){

	//Check page's privacy filter
	privacy_check();

	$percent = false;

	if (isset($_GET['style']) && $_GET['style'] == 2)
		$percent = true;

	$pinfo = stream_page_popularity();
	$curinf = 0;
	$hitsum = 0;	

	$message = "
	<table width=100% height=75 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top>
	<BR><font face=arial style='font-size:17px' color=black><b>Page Ranking</b></font><BR><BR><font face=arial size=2 color=black>These rankings are based upon individual hits each page receives:<BR><BR>
	</td></table>
	";

	//Get the total number of hits, in case the style is percent
	for($i=0; $i<count($pinfo); $i++) 
		$hitsum += $pinfo[$i]['hits'];	

	$message .= "<table width=100% height=200 cellpadding=0 cellspacing=0>";

	$message .= "<td width=10% height=100% valign=top><font size=2><b><u>Rank</u></b><BR>";
	for($i=1; $i<=count($pinfo); $i++) {
			$message .= "<BR><b>#" . $i . "</b>";	
	}
	$message .= "<BR><BR><BR></font></td>";


	$message .= "<td width=5% height=100% valign=top></td>";


	$message .= "<td width=60% height=100% valign=top><font size=2><b><u>Page Location:</u></b><BR>";
	foreach($pinfo as $cpi) {
		$urlinfo = parse_url($cpi['name']);
		if ((isset($urlinfo['query'])) && $urlinfo['query'] != "")
			$f_url = $urlinfo['path'] . "?" . $urlinfo['query'];
		else
			$f_url = $urlinfo['path'];
		

		if (strlen($f_url) > 42){
		while (strlen($f_url) > 42 && substr_count($f_url, "/") > 1){			
			$f_url = substr($f_url, strpos($f_url, "/", 2));
		}
		$f_url = "..." . $f_url;
		}

		if (strlen($f_url) > 42){
		$f_url = substr($f_url, strlen($f_url)-39);
//		while (strlen($f_url) > 41){
//			$f_url = substr($f_url, 1);
//		}
		$f_url = "..." . $f_url;
		}

		$message .= "<BR><a href=\"" . $cpi['name'] . "\"><font color=blue>" . $f_url . "</font></a>";
	}
	$message .= "<BR><BR><BR></font></td>";


	$message .= "<td width=5% height=100% valign=top></td>";


	if (!$percent)
		$right_title = "Views";
	else
		$right_title = "Percent";
	$message .= "<td width=15% height=100% valign=top><font size=2><b><u>$right_title:</u></b><BR>";
	foreach($pinfo as $cpi) {
		if (!$percent)
			$message .= "<BR>" . $cpi['hits'];
		else
			$message .= "<BR>" . round(($cpi['hits']/$hitsum)*100, 2) . "%";
	}
	$message .= "<BR><BR><BR></font></td>";

	$message .= "<td width=5% height=100% valign=top></td>";


	$message .= "</table></font>";	

	Message($message);
}

else if ($act == "predict"){
	
	//Check page's privacy filter
	privacy_check();

	$message = "
	<table width=100% height=75 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top><BR><font face=arial style='font-size:17px' color=black><b>Traffic Predictions -</b> Hits</font><BR><BR><font face=arial size=2 color=black>Unlike many other premiere visitor predictions, Elite Stats uses a state of the art system designed to predict based on EXACT and current value divisons, contrasting to dangerous value multiples. These predictions are for the amount of hits expected within the stated timeframe.<BR><BR></font></td></table>
	<table width=100% height=150 cellpadding=0 cellspacing=0>

	<td width=20% height=100% valign=top>
	<font face=arial size=2 color=black>
	<b>Based On Last:</b><BR><BR><BR>
	<u>In Hour:</u><BR><BR>
	<u>In Day:</u><BR><BR>
	<u>In Week:</u><BR><BR>
	</font>
	</td>

	<td width=20% height=100% valign=top>
	<font face=arial size=2 color=black>
	<u>Hour</u><BR><BR><BR>
	" . round( (stat_value("hourshits")/gmdate_divisable("i", mktime()+time_offset()))*60 , 0) . "
	<BR><BR>" . round( ((stat_value("hourshits")/gmdate_divisable("i", mktime()+time_offset()))*60)*24 , 0) . "
	<BR><BR>" . round( (((stat_value("hourshits")/gmdate_divisable("i", mktime()+time_offset()))*60)*24)*7 , 0) . "

	</font>
	</td>

	<td width=20% height=100% valign=top>
	<font face=arial size=2 color=black>
	<u>Day</u><BR><BR><BR>
	" . round( (stat_value("todayshits")/gmdate_divisable("H", mktime()+time_offset())) , 0) . "
	<BR><BR>" . round( (stat_value("todayshits")/gmdate_divisable("H", mktime()+time_offset()))*24 , 0) . "
	<BR><BR>" . round( ((stat_value("todayshits")/gmdate_divisable("H", mktime()+time_offset()))*24)*7 , 0) . "
	</font>
	</td>

	<td width=20% height=100% valign=top>
	<font face=arial size=2 color=black>
	<u>Week</u><BR><BR><BR>
	" . round( ((stat_value("weekhits")/gmdate_divisable("w", mktime()+time_offset())))/24 , 0) . "
	<BR><BR>" . round( (stat_value("weekhits")/gmdate_divisable("w", mktime()+time_offset())) , 0) . "
	<BR><BR>" . round( ((stat_value("weekhits")/gmdate_divisable("w", mktime()+time_offset())*7)) , 0) . "
	</font>
	</td>

	</table>
	</font>";	
	Message($message);
}
else if ($act == "login"){
	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Administrator Login</b></font><BR><BR><font face=arial size=2 color=black>You must login prior to accessing this page:<BR>
	<BR><BR>
	<form action=index.php?act=plogin method=post>
	<b>Username:</b> &nbsp;&nbsp;<input type=text name=user size=20><BR><BR>
	<b>Password:</b> &nbsp;&nbsp;<input type=password name=pass size=12><BR><BR><BR>
	<input type=submit value=\"Proceed\"> &nbsp;&nbsp; <input type=reset value=\"Reset\">
	<BR><BR><BR><BR><BR>Show your support: &nbsp; <font color=blue><a href=index.php?act=donate target=_newWindow>Click here to support Elite Stats...</a></blue>
	</form>
	</font>";	
	Message($message);
}
else if ($act == "plogin"){

	$username = $_POST['user'];
	$password = $_POST['pass'];

	if (try_login($username, $password)){
		$message = "<BR><font face=arial style='font-size:17px' color=black><b>Welcome</b></font><BR><BR><font face=arial size=2 color=black>";
		$message .= "You have successfully logged into the Elite Stats administration account. You will now be able to configure Elite Stats to your desired specifications.<BR><BR>FireStorm Research and Development takes pride in offering quality in both our web-based and Windows applications, if you have any questions or comments, please send an e-mail to the FireStorm developers at firestorm@all-interviews.com";	
		$message .= "<BR><BR><BR><BR><b>To configure Elite Stats:</b><BR><BR><a href=index.php?act=settings><font color=black>Click here!</font></a>";	
		
		$message .= "<BR><BR></font>";	
	}
	else {
		$message = "<BR><font face=arial style='font-size:17px' color=black><b>Login Failure</b></font><BR><BR><font face=arial size=2 color=black><BR><BR>
		<form action=index.php?act=plogin method=post>
		<b>Username:</b> &nbsp;&nbsp;<input type=text name=user size=20><BR><BR>
		<b>Password:</b> &nbsp;&nbsp;<input type=password name=pass size=12><BR><BR><BR>
		<input type=submit value=\"Proceed\"> &nbsp;&nbsp; <input type=reset value=\"Reset\">
		</form>
		</font>
		";			
	}

	Message($message);
}
else if ($act == "average"){

	//Check page's privacy filter
	privacy_check();

	if (stat_value("totalunique") > 0)
		$average_visit = round(stat_value("totalhits")/stat_value("totalunique"), 2);
	else
		$average_visit = 0;

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Generated Averages</b></font><BR><BR><font face=arial size=2 color=black>
	These averages are generated based on the amount of time Elite Stats has been monitoring visitor activity, these averages may or may not reflect the overall statistics of your site.
	<br><br>Elite Stats has been monitoring your stats for <b>" . stat_value("totaldays") . "</b> days.

	<table width=100% height=125 cellpadding=0 cellspacing=0>
	<td width=50% height=100% bgcolor=white>
	<font>
	<b>
	<br><br>Hits Per Day:
	<br><br>Unique Per Day:
	<br><br>Views Per Visit:
	<br><br>Load Time:
	</b>
	</font>
	</td>
	<td width=50% height=100% bgcolor=white>
	<font>
	<br><br>" . ceil(stat_value("averagehits")) . "
	<br><br>" . round(stat_value("averageunique"), 2) . "
	<br><br>" . $average_visit . "
	<br><br>" . stat_value("averagetime") . " Seconds
	</font>
	</td>
	</table>
		
	</font>";	
	Message($message);
}
else if ($act == "lastinfo"){

	//Check page's privacy filter
	privacy_check();
	
	$limit = $a_db['user']['tracklimit'];
	if ($limit < 5 || $limit > 100 || !is_numeric($limit)){
		$limit = 25;
	}

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Last $limit Visitors</b></font><BR><BR><font face=arial size=2 color=black>This shows up to $limit of the last visitors to your site, including some valuable information about them.";
	
	$vd = stream_visitor_info();
	$cur_vis=0;
	$hitstoday = stat_value("hitstoday");

	foreach($vd as $v1) {
		$cur_vis++;

		$v1['refer_full'] = $v1['refer'];
		if (strlen($v1['refer']) > 60){
			while (strlen($v1['refer']) > 60 && substr_count($v1['refer'], "/") > 0){
				$v1['refer'] = substr($v1['refer'], 0, strlpos($v1['refer'], '/'));		
			}
			if (strlen($v1['refer']) > 60){
				$v1['refer'] = substr($v1['refer'], 0, 60) . "...";
			}
			else
				$v1['refer'] .= "/...";
		}
		
		$message .= "<BR><BR><b>User #$cur_vis:</b><BR><BR>";
		$message .= "<u>IP Address:</u> " . $v1['ip'] . "<BR>";
		$message .= "<BR><u>Referred:</u> <a href=\"" . $v1['refer_full'] . "\"><font color=blue face=arial>" . $v1['refer'] . "</font></a><BR>";
		$message .= "<u>Page Location:</u> <a href=\"" . $v1['lastpage'] . "\"><font color=blue face=arial>" . $v1['lastpage'] . "</font></a><BR>";

		if ($hitstoday > 0)
		$percent_today = round(($v1['views']/$hitstoday)*100, 2);
		else
		$percent_today = 100;

		if ($percent_today >= 50)
			$percent_today = "&nbsp;&nbsp;<font color=darkgreen></b>" . $percent_today . "</b></font> ";
		else
			$percent_today = "&nbsp;&nbsp;<font color=darkred></b>" . $percent_today . "</b></font> ";

		$message .= "<BR><u>Total Views:</u> " . $v1['views'] . str_repeat("&nbsp;", 6) . "<u>% of todays hits:</u> " . $percent_today . "%";
		$message .= "<BR><u>Time of Visit:</u> " . $v1['time'] . "<BR>";
		$message .= "<BR><u>Browser:</u> <i>" . $v1['browser'] . "</i><BR>";
		$message .= "<BR>Options: ";
		$message .= "<a href=\"index.php?act=banuser&ip=" . $v1['ip'] . "&agent=" . $v1['agent'] . "\"><font color=red>Ban Visitor</font></a>&nbsp; | &nbsp;<a href=\"index.php?act=ignore&ip=" . $v1['ip'] . "&agent=" . $v1['agent'] . "\"><font color=red>Ignore Visitor</font></a><BR>";
	}

	$message .= "<BR><BR></font>";	
	Message($message);
}
else if ($act == "banlist"){
	
	//Check page's privacy filter
	privacy_check();

	$blist = stream_banned_list();

	$message = "
	<table width=100% height=75 cellpadding=0 cellspacing=0><td width=100% height=100% valign=top><BR><font face=arial style='font-size:17px' color=black><b>Current Banlist and Ignores</b></font><BR><BR><font face=arial size=2 color=black>The following IP addresses are banned or ignored from your site, in order to have banning work successfully, you must have Elite Stats tracking each page you wish to have banning take effect.<BR><BR></font></td></table>
	<table width=100% height=200 cellpadding=0 cellspacing=0>


	<td width=35% height=100% valign=top>
	<font face=arial size=2 color=black>
	<b>IP Address</b><BR><BR>
	";
	
	for ($i=0; $i<count($blist); $i++){
		$message .= $blist[$i]['ip'] . ".*";
		if ($blist[$i]['ignore'] == 1)
			$message .= " &nbsp;<i><font color=red>Ignored</font></i>";

		$message .= "<BR>";
	}
	
	$message .="
	</font>
	</td>

	<td width=5% height=100% valign=top>
	</td>

	<td width=30% height=100% valign=top>
	<font face=arial size=2 color=black>
	<b>Time of Action</b><BR><BR>";

	for ($i=0; $i<count($blist); $i++)
		$message .= $blist[$i]['when'] . "<BR>";

	$message .="
	</font>
	</td>

	<td width=5% height=100% valign=top>
	</td>

	<td width=20% height=100% valign=top>
	<font face=arial size=2 color=black>
	<b>Options</b><BR><BR>";

	for ($i=0; $i<count($blist); $i++){
		if ($blist[$i]['ignore'] == 1)
			$message .= "<A HREF=index.php?act=unban&id=$i><u><font color=blue>Un-Ignore</font></u></a><BR>";
		else
			$message .= "<A HREF=index.php?act=unban&id=$i><u><font color=blue>Unban</font></u></a><BR>";
	}

	$message .="
	</font>
	</td>

	<td width=5% height=100% valign=top>
	</td>

	</table>
	</font>";	
	Message($message);
}

else if ($act == "unban"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	$id = $_GET['id']; init_db("banned");

	$blist = &$a_db['banned']['ban'];
	$ban_buf = Array();

	$sizeof_blist = count($blist);
	if ($id <= $sizeof_blist && $id >= 0){
		$b_ip = $blist[$id]['ip'];
		for ($i=0; $i<$sizeof_blist; $i++) {
			if ($i != $id){
				$ban_buf[] = $blist[$i];
			}
		}
	}

	$blist = $ban_buf;

	$message .= "<BR><font face=arial style='font-size:17px' color=black><b>User is no longer banned or ignored.</b></font><BR><BR><font face=arial size=2 color=black>" . $b_ip . ".*. has been successfully unbanned or unignored.<BR><BR><a href=index.php?act=banlist><u><font color=blue>Click here to return to the ban/ignore list.</font></u></a><BR><BR>
	</font>";
	
	Message($message);
}
else if ($act == "banuser"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	$agent = $_GET['agent'];
	$ip = $_GET['ip'];


	if ($ip != $_SERVER['REMOTE_ADDR']){
	ban_user($ip, $agent);	
	$message .= "<BR><font face=arial style='font-size:17px' color=black><b>Successfully Banned</b></font><BR><BR><font face=arial size=2 color=black>" . $ip . " has been successfully banned.<BR><BR><a href=index.php?act=banlist><u><font color=blue>Click here to view ban list.</font></u></a><BR><BR></font>";
	} else {
	$message .= "<BR><font face=arial style='font-size:17px' color=black><b>Ban Failed</b></font><BR><BR><font face=arial size=2 color=black>As an extra security measure, Elite Stats will not allow the banning of yourself.<BR><BR><a href=index.php?act=banlist><u><font color=blue>Click here to view ban list.</font></u></a><BR><BR></font>";
	}
	
	Message($message);
}

else if ($act == "isbanned"){
	exit("You have been banned from this site.");
}

else if ($act == "ignore"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	$ip = $_GET['ip'];
	$agent = $_GET['agent'];

	ban_user($ip, $agent, true);	
	$message .= "<BR><font face=arial style='font-size:17px' color=black><b>Successfully Ignored</b></font><BR><BR><font face=arial size=2 color=black>" . $ip . " has been successfully ignored.<BR><BR><a href=index.php?act=banlist><u><font color=blue>Click here to view the banned/ignored list.</font></u></a><BR><BR></font>";
	
	Message($message);
}

else if ($act == "records"){

	//Check page's privacy filter
	privacy_check();

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Current Records</b></font><BR><BR><font face=arial size=2 color=black>In addition to tracking current visitor activity, Elite Stats also logs any records achieved, making it easy to calculate when your site was at the peak of its popularity.";
	$message .= "<BR><BR><BR><b><big>Daily Record:</big><BR><BR><u>Record hits in one day:</u></b><BR>" . stat_value("dayrecord") . "<BR><BR><b><u>Record was set on: </u></b><BR>" . stat_value("dayrecorddate");	
	$message .= "<BR><BR><BR><b><big>Weekly Record:</big><BR><BR><u>Record hits in one week:</u></b><BR>" . stat_value("weekrecord");	
	$message .= "<BR><BR><BR><b><big>User Record:</big><BR><BR><u>Most users online:</u></b><BR>" . stat_value("userrecord") . "<BR><BR><b><u>Record was set on: </u></b><BR>" . stat_value("userrecorddate");	
	$message .= "<BR><BR><BR></font>";	
	Message($message);
}
else if ($act == "pword"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	//Get the current username
	init_db("user");
	$curuser = $a_db['user']['username'];

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Password and Username</b></font><BR><BR><font face=arial size=2 color=black>";
	$message .= "If you wish to change your password and/or username, please do so from here. The suggested password length is 4 or more characters.";	
	$message .= "
			<form action=index.php?act=upass method=post>
			<BR><b>Username:</b><BR> <input type=text name=user value=\"" . $curuser . "\" size=20>
			<BR><BR><b>New Password:</b><BR> (Leave blank to remain unchanged)<BR> <input type=password name=pass size=20>
			<BR><BR><BR><input type=submit value=Update> &nbsp;&nbsp;&nbsp; <input type=reset value=Reset>
			</form>
	";	
	$message .= "<BR><BR></font>";	
	Message($message);
}
else if ($act == "upass"){

	//Authorization required
	if (require_login() == FALSE)
		exit();

	$username = $_POST['user'];
	$password = $_POST['pass'];
	update_user($username, $password);

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>User Updated</b></font><BR><BR><font face=arial size=2 color=black>";
	$message .= "Your user information has been successfully updated, from now on log into Elite Stats using the altered Username and Password.";	
	$message .= "<BR><BR><BR><BR><b>To log in using the altered account:</b><BR><BR><a href=index.php?act=login><font color=black>Click here!</font></a>";	
	
	$message .= "<BR><BR></font>";	
	Message($message);
}

else if ($act == "settings"){

	//Authorization required
	if (require_login() == FALSE)
		exit();
	
	$offset = $a_db['user']['offset'];
	$setary = Array();

	for ($i=12;$i >= 1;$i--){
		if ($i < 10){
			$setary = array_merge($setary, array("-0$i:00"));
		} else{
			$setary = array_merge($setary, array("-$i:00"));
	}}

	for ($i=0;$i <= 12;$i++){
		if ($i < 10){
			$setary = array_merge($setary, array("+0$i:00"));
		} else{
			$setary = array_merge($setary, array("+$i:00"));
	}}


	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Elite Stats Configuration</b></font><font face=arial size=2 color=black><BR><BR>If this is your first time logging into Elite Stats, or you have not previously configured Elite Stats, these values will be set to their defaults, which are selected to best fit the average site. However, it is recommended that you select your proper time offset.";
	$message .= "
			<form action=index.php?act=usettings method=post>
			<BR><b><BIG>General:</BIG></b><BR><BR>
			<!--
			 </b>Site Name:</b><br><input type=text size=20 name=site><BR><BR>
			</b>Main URL:</b><br><input type=text size=20 name=url value=\"http://\"><BR><BR>
			-->
	";

	$message .= "<B>Time offset:</B><br><select name=offset>";

	foreach($setary as $value){
		if (str_replace(":", "", $value) == $offset){
			$message .= "<option selected>$value";
		} else{
			$message .= "<option>$value";
	}}
	$message .= "</select><BR><BR><BR>";


	$p_info = "<br>Note: Upon selecting private Elite Stats pages will only be viewable by admins, this does effect any stats displayed on outside pages.<br>";
	if ($a_db['user']['privacy'] == "private")
		$message .= "<b>Privacy Control:</b>$p_info<br><input type=radio name=privacy value=public> Public <input type=radio name=privacy value=private checked> Private<BR><BR><BR>";
	else
		$message .= "<b>Privacy Control:</b>$p_info<br><input type=radio name=privacy value=public checked> Public <input type=radio name=privacy value=private> Private<BR><BR><BR>";

	$message .= "	<BR><b><BIG>Specific:</BIG></b><BR><BR>
			Track up to &nbsp;<input type=text size=2 name=limit value=" . $a_db['user']['tracklimit'] . ">&nbsp; users.<BR><BR>";
			//"Trend Chart Color:<BR><input type=text size=20 name=chartcolor value=" . $a_db['user']['chartcolor'] . "><BR><BR>";

	if ($a_db['user']['rstyle'] == 2)
		$message .= "<br><b>Page Ranking:</b><br><br><input type=radio name=ranking value=2 checked> Remove variables (http://yoursite.com/page.php)<BR><input type=radio name=ranking value=1> Include variables (http://yoursite.com/page.php?id=2&name=staff)<BR><BR><BR>";
	else
		$message .= "<br><b>Page Ranking:</b><br><br><input type=radio name=ranking value=2> Remove variables (http://yoursite.com/page.php)<BR><input type=radio name=ranking value=1 checked> Include variables (http://yoursite.com/page.php?id=2&name=staff)<BR><BR><BR>";

	$message .= "
			<BR><input type=submit value=\"Update\"> &nbsp;&nbsp; <input type=reset value=\"Reset\"><BR><BR><BR>
			</form>
		    ";
	$message .= "<BR><BR><BR></font>";	
	Message($message);
}

else if ($act == "usettings"){
	

	//Authorization required
	if (require_login() == FALSE)
		exit();
	
	$limit = $_POST['limit'];
	//$chart_color = $_POST['chartcolor'];
	//$chart_color = strtolower(str_replace(" ", "", $chart_color));
	$privacy = $_POST['privacy'];
	$ranking = $_POST['ranking'];
	$offset = $_POST['offset'];
	
	if ($limit >= 5 && $limit <= 100 && is_numeric($limit)){
		$a_db['user']['tracklimit'] = $limit;
	}
	//if (strlen($chart_color) > 2){
	//	$a_db['user']['chartcolor'] = $chart_color;
	//}

	$a_db['user']['privacy'] = $privacy;
	$a_db['user']['rstyle'] = $ranking;

	if (substr_count($offset, "-") >= 1){
		$mfunc = "-";
	} else if (substr_count($offset, "+") >= 0){
		$mfunc = "+";
	}

	$offset = str_replace("-", "", $offset); $offset = str_replace("+", "", $offset); $offset = str_replace(":", "", $offset);
		
	if (strlen($offset)<4){
		$offset = "0" . $offset;
	}
	$offset = $mfunc . $offset;

	$a_db['user']['offset'] = $offset;

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Configuration Updated</b></font><BR><BR><font face=arial size=2 color=black>";
	$message .= "Your Elite Stats settings have been successfully altered...";

	$message .= "<BR><BR></font>";	
	Message($message);
}

else if ($act == "trend"){

	//Check page's privacy filter
	privacy_check();

	$day_info = stream_day_info();

	$chart_color = $a_db['user']['chartcolor'];
	
	if ($chart_color == "")
		$chart_color=darkblue;


	$day1=0;
	$day2=0;
	$day3=0;
	$day4=0;
	$day5=0;
	$day1_when=0;
	$day2_when=0;
	$day3_when=0;
	$day4_when=0;
	$day5_when=0;

	$cur_loc=4;

	for ($i=1; $i < 6; $i++){
		$var = "day" . $i;
		$$var = $day_info[$cur_loc]['hits'];
		if ($$var == 0){
		//$$var = 1;
		}

		$var = "day" . $i . "_when";
		$$var = $day_info[$cur_loc]['when'];
		$cur_loc--; 
	}

	$highest=0;

	for ($i=1; $i < 6; $i++){
		$var = "day" . $i;
		if ($$var > $highest){
			$highest = $$var;
		}
	}

	if ($highest > 0)
	$multiple = 200/$highest;
	else
	$multiple = 0;
	
	$day1 = $day1*$multiple;
	$day1--;
	$day2 = $day2*$multiple;
	$day2--;
	$day3 = $day3*$multiple;
	$day3--;
	$day4 = $day4*$multiple;
	$day4--;
	$day5 = $day5*$multiple;
	$day5--;

	$day1=round($day1, 0);
	$day2=round($day2, 0);
	$day3=round($day3, 0);
	$day4=round($day4, 0);
	$day5=round($day5, 0);


	$day1_sp = 200-$day1;
	$day2_sp = 200-$day2;
	$day3_sp = 200-$day3;
	$day4_sp = 200-$day4;
	$day5_sp = 200-$day5;

	while ($day1_sp + $day1 > 200)
		$day1_sp--;
	while ($day2_sp + $day2 > 200)
		$day2_sp--;
	while ($day3_sp + $day3 > 200)
		$day3_sp--;
	while ($day4_sp + $day4 > 200)
		$day4_sp--;
	while ($day5_sp + $day5 > 200)
		$day5_sp--;

	while ($day1_sp + $day1 < 200)
		$day1_sp++;
	while ($day2_sp + $day2 < 200)
		$day2_sp++;
	while ($day3_sp + $day3 < 200)
		$day3_sp++;
	while ($day4_sp + $day4 < 200)
		$day4_sp++;
	while ($day5_sp + $day5 < 200)
		$day5_sp++;

	$step_amount = round($highest/5, 0);
	$step5 = $step_amount;
	$step4 = $step_amount*2;
	$step3 = $step_amount*3;
	$step2 = $step_amount*4;
	$step1 = $step_amount*5;

	$message = "
	<table width=100% height=105 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 valign=center>
	<BR><font face=arial style='font-size:17px' color=black><b>Activity Trend</b></font><BR><BR><font face=arial size=2 color=black>This chart shows activity for the last 5 days, all values are based upon your daily hits.
	</td>
	</table>
	<table width=100% height=220 cellpadding=0 cellspacing=0>

	<td width=8% height=220 cellpadding=0 cellspacing=0 bgcolor=white>

	<table width=100% height=200 cellpadding=0 cellspacing=0>
	<td width=100% height=100% bgcolor=white valign=top>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b><font>$step1</font></b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b><font>$step2</font></b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b><font>$step3</font></b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b><font>$step4</font></b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b><font>$step5</font></b>
	</td>
	</table>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% bgcolor=white valign=top>
	</td>
	</table>

	</td>

	<td width=4% height=220 cellpadding=0 cellspacing=0 bgcolor=white valign=top>

	<table width=100% height=200 cellpadding=0 cellspacing=0>
	<td width=100% height=100% bgcolor=white valign=top>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b>-</b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b>-</b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b>-</b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b>-</b>
	</td>
	</table>

	<table width=100% height=20% cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white valign=top>
	<b>-</b>
	</td>
	</table>

	</td>
	</table>

	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% bgcolor=white valign=top>
	</td>
	</table>

	</td>

	<td width=10% height=220 cellpadding=0 cellspacing=0 bgcolor=white>
	<table width=100% height=$day1_sp cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white>
	</td>
	</table>
	<table width=100% height=$day1 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=" . $chart_color . " background='imgs/vgraph.png'>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% valign=center align=center bgcolor=white><b><font>$day1_when</font></b></td>
	</table>
	</td>

	<td width=5% height=220 cellpadding=0 cellspacing=0></td>

	<td width=10% height=220 cellpadding=0 cellspacing=0 bgcolor=white>
	<table width=100% height=$day2_sp cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white>
	</td>
	</table>
	<table width=100% height=$day2 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=" . $chart_color . " background='imgs/vgraph.png'>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% valign=center align=center bgcolor=white><b><font>$day2_when</font></b></td>
	</table>
	</td>

	<td width=5% height=220 cellpadding=0 cellspacing=0></td>

	<td width=10% height=220 cellpadding=0 cellspacing=0 bgcolor=white>
	<table width=100% height=$day3_sp cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white>
	</td>
	</table>
	<table width=100% height=$day3 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=" . $chart_color . " background='imgs/vgraph.png'>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% valign=center align=center bgcolor=white><b><font>$day3_when</font></b></td>
	</table>
	</td>

	<td width=5% height=220 cellpadding=0 cellspacing=0></td>
	
	<td width=10% height=220 cellpadding=0 cellspacing=0 bgcolor=white>
	<table width=100% height=$day4_sp cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white>
	</td>
	</table>
	<table width=100% height=$day4 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=" . $chart_color . " background='imgs/vgraph.png'>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0 valign=top>
	<td width=100% height=100% valign=center align=center bgcolor=white><b><font>$day4_when</font></b></td>
	</table>
	</td>

	<td width=5% height=220 cellpadding=0 cellspacing=0></td>

	<td width=10% height=220 cellpadding=0 cellspacing=0 bgcolor=white>
	<table width=100% height=$day5_sp cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=white>
	</td>
	</table>
	<table width=100% height=$day5 cellpadding=0 cellspacing=0>
	<td width=100% height=100% cellpadding=0 cellspacing=0 bgcolor=" . $chart_color . " background='imgs/vgraph.png'>
	</td>
	</table>
	<table width=100% height=20 cellpadding=0 cellspacing=0>
	<td width=100% height=100% valign=center align=center bgcolor=white><b><font>$day5_when</font></b></td>
	</table>
	</td>

	<td width=5% height=220 cellpadding=0 cellspacing=0></td>

	<td width=13% height=220 cellpadding=0 cellspacing=0></td>
	</table>
	";	
	$message .= "<BR><BR></font>";	
	Message($message);

}
else{
	//Check page's privacy filter
	privacy_check();

	$message = "<BR><font face=arial style='font-size:17px' color=black><b>Welcome</b></font><BR><BR><font face=arial size=2 color=black>Welcome to Elite Stats, another free product developed by the same person who brought you Elite News. Elite Stats makes viewing your sites visitor statistics easier than ever before.<BR><BR>Being in current development, any comments and suggestions are greatly appreciated. I hope I can offer the same quality as all of my other projects, while striving to be the best of the best.<BR><BR>I'm also looking for employement opportunities, if you're interested in hiring me, send an email to Stuart Konen (myself) at <a href='mailto:stuart@all-interviews.com'>stuart@all-interviews.com</a><BR><BR><BR><b>To continue to administration:</b><BR><BR><a href=index.php?act=login><font color=black>Click here!</font></a><BR><BR></font>";
	Message($message);
}


	ob_end_flush();
?>