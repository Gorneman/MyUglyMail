<?php
session_start();
error_reporting('E_ALL');
//ini_set("display_errors", 1);
require('core/config.php');

$f = htmlentities($_GET['folder'], ENT_QUOTES, 'utf-8');
if (empty($f)) {
	$f = 'Inbox';
}

$t = 'Trash';
$ibox = imap_open($IMAP.$f, $_SESSION['email'], $_SESSION['pass']);
imap_reopen($ibox,$IMAP.$t) or die(implode(", ", imap_errors()));
//$curr_obj = imap_check($ibox);
//var_dump($curr_obj);

imap_delete($ibox, '1:*');

imap_expunge($ibox);
imap_close($ibox);

?>
