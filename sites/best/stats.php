<?php require_once("elitestats/include/functions.php"); ?>

<body style="background-attachment: fixed" bgcolor="#EBEBEB"><p>
	<font face="Arial" style="font-size: 8pt" color="000000">
	<br>
	<?php //include("stats.php"); 
	echo "Total Visits: <b>" . stat_value("totalhits") . "</b><Br>
Todays Visits: <b>" . stat_value("todayhits") . "</b><Br>
Total Unique Visits: <b>" . stat_value("totalunique") . "</b><Br>
Todays Uniques Visits: <b>" . stat_value("todaysunique") . "</b><br>
Online Now: <b>" . stat_value("online") . "</b><bR>
The most people ever online was <b>" . stat_value("record_user") . "</b>
on <b>" . stat_value("record_user_date")."</b>";
?></font></p>
