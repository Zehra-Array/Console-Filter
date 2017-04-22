<?php
// Copyright 2011 Andreas "Cobra_Fast" Seifert
// Distributed under the GNU General Public License v3

function UnControlcode($subj)
{
	return preg_replace("/\e\[(.?)[0-9]m|\e\[(.)[0-9]m|\e\[0;1m/", "", $subj);
}

function IsChecked($cname)
{
	if (isset($_POST[$cname])) echo 'checked="checked"';
}


$raw = "";
if ($_FILES["file"]["error"] == 0 & count($_FILES) > 0)
{
	$raw = file_get_contents($_FILES["file"]["tmp_name"]);
	unlink($_FILES["file"]["tmp_name"]);
	$raw = str_replace("\r", "", $raw);
}
else
{
	$raw = $_POST["ltext"];
	$raw = str_replace("\r", "", $raw);
}
?>
<html>
<head>
<title>/savemsgs console output php filter</title>
<style type="text/css">
* { font-family: Tahoma, DejaVu Sans, sans, sans-serif; font-size: 11pt; }
</style>
</head>
<body>
<fieldset>
<legend>Setup</legend>
<form method="post" enctype="multipart/form-data">
Upload /savemsgs-file <input type="file" name="file" /> <?php echo "Max " . ini_get("upload_max_filesize") . "iB"; ?><br />
or input /savemsgs-text <textarea name="ltext" rows="2" cols="32"><?php if (strlen($raw) > 2) { echo $raw; } ?></textarea>
<br />&nbsp;
 <fieldset>
 <legend>Filter setup</legend>
 Show 
 <input type="checkbox" name="pubm" <?php IsChecked("pubm"); ?> /> public chat messages, 
 <input type="checkbox" name="privm" <?php IsChecked("privm"); ?> /> private chat messages,
 <input type="checkbox" name="teamm" <?php IsChecked("teamm"); ?> /> team chat messages,
 <input type="checkbox" name="servm" <?php IsChecked("servm"); ?> /> server chat messages<br />
 and miscellaneous lines like 
 <input type="checkbox" name="killm" <?php IsChecked("killm"); ?> /> kills,
 <input type="checkbox" name="flagm" <?php IsChecked("flagm"); ?> /> flag actions,
 <input type="checkbox" name="jnqtm" <?php IsChecked("jnqtm"); ?> /> joins & quits.
 </fieldset>&nbsp;<br />
 <input type="submit" value="FILTER!" /> <a href="index.phps">Source Code</a>
</form>
</fieldset>
<fielset>
<?php

if (strlen($raw) > 2)
{
	$lines = explode("\n", $raw);
	foreach ($lines as $line)
	{
		if (preg_match("/\[36m(.*?)\[0;1m/", $line) && (isset($_POST["servm"]) || isset($_POST["teamm"]) || isset($_POST["privm"]) || isset($_POST["pubm"]) ))
			if ((ereg("SERVER", $line) && isset($_POST["servm"]))
			|| (ereg("\[Team\]", $line) && isset($_POST["teamm"]))
			|| (preg_match("/\[(.*?)\-\>\]/", $line) && !ereg("SERVER", $line) && isset($_POST["privm"]))
			|| (preg_match("/\[\-\>(.*?)\]/", $line) && !ereg("SERVER", $line) && isset($_POST["privm"]))
			|| (isset($_POST["pubm"]) && !ereg("SERVER", $line) && !ereg("\[Team\]", $line) && !preg_match("/\[(.*?)\-\>\]/", $line) && !preg_match("/\[\-\>(.*?)\]/", $line))
			)
				echo UnControlcode($line) . "<br />";
		
		if (((ereg("\[30m: grabbed", $line) || ereg("\[30m: dropped", $line)) && isset($_POST["flagm"]))
		|| ((ereg("\[37mkilled by", $line) || ereg("\[37mwas destroyed by", $line)) && isset($_POST["killm"]))
		|| ((ereg("\[30m: signing off", $line) || ereg("\[30m: joining", $line)) && isset($_POST["jnqtm"]))
		)
			echo "<span style='color: #909090;'>" . UnControlcode($line) . "</span><br />";
	}
}

?>
</fieldset>
</body>
</html>