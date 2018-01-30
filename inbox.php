<?php
header('Content-type: text/html; charset=utf-8');
session_start();
error_reporting('E_ALL');
require('core/config.php');
require('core/functions.php');
// get credentials from session

// change folder destination
$f = htmlentities($_GET['folder'], ENT_QUOTES, 'utf-8');
if (empty($f)) {
	$f = 'INBOX';
}

$ibox = imap_open($IMAP.$f, $_SESSION['email'], $_SESSION['pass']);
$count = imap_num_msg($ibox);

if ($ibox == false) {
	header('Location: index.php');
	exit;
}

$folders = imap_list($ibox, "{folders}", "*");
?>
<!DOCTYPE html>
<html lang="it">
<head>
	<title>My Ugly webmail</title>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Accedi alla casella">
  <meta name="keywords" content="email client,e-mail,email,">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700|Roboto+Condensed:300,400,700&subset=latin-ext" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/theme.css">
</head>
<body>
<?php
echo '<ul class="folders">';
foreach ($folders as $folder) {
    $folder = str_replace("{folders}", "", imap_utf7_decode($folder));

    if(strtolower($folder) == 'trash' || strtolower($folder) == 'cestino'){
    	//Cestino
    	echo '<li><a href="inbox.php?folder=' . $folder . '">  <i class="fa fa-trash-o"></i> Cestino </a></li>';
	}else if(strtolower($folder) == 'inviato' || strtolower($folder) == 'sent'){
		//posta inviata
		echo '<li><a href="inbox.php?folder=' . $folder . '">  <i class="fa fa-send-o"></i> Posta inviata </a></li>';
	}else if(strtolower($folder) == 'in arrivo' || strtolower($folder) == 'inbox'){
		//posta ricevuta;
		echo '<li><a href="inbox.php?folder=' . $folder . '">  <i class="fa fa-envelope-o"></i> Posta ricevuta </a></li>';
	}else if(strtolower($folder) == 'posta indesiderata' || strtolower($folder) == 'spam'){
		//posta indesiderata;
		echo '<li><a href="inbox.php?folder=' . $folder . '">  <i class="fa fa-ban"></i> Posta indesiderata </a></li>';
	}
}
echo '<li><a href="sendemail.php">  <i class="fa fa-send"></i> Invia un messaggio </a></li>';
echo '<li><a href="stats.php">  <i class="fa fa-bar-chart"></i> Statistiche </a></li>';
echo '<li><a href="logout.php">  <i class="fa fa-sign-out"></i> Logout </a></li>';
echo "</ul>";

$del = (int)$_GET['msgid'];
if (!empty($del)) {
	$trash = 'Trash';
	if ($f == 'Trash') {
		$trash = 'INBOX';
	}

	$r=imap_mail_move($ibox, $del, $trash, CP_UID);
	if($r==false){die(imap_last_error());}

	imap_expunge($ibox);
}

if ($count == 0) {
	echo $err = '<p class="err"> <i class="fa fa-envelope-o"></i> Non ci sono messaggi </p>';
}else if(strtolower($f) == 'trash' || strtolower($f) == 'cestino'){
	echo $err = '<div class="err"> <p style="display: inline-block"><i class="fa fa-envelope-o"></i> '.$count.' messaggi &nbsp;</p><button type="button" style="display: inline-block">Svuota cestino</button></div>';
}else {
	echo $err = '<p class="err"> <i class="fa fa-envelope-o"></i> '.$count.' messaggi </p>';
}
?>
<div id="mail_list">
<?php
$emails = imap_search($ibox,'ALL');
if ($emails) {
	// sort new first
	rsort($emails);
	// for every email
	foreach($emails as $nr) {
		// random avatar icon
		$src = 'http://www.gravatar.com/avatar/'.rand(999,974983).'?s=90&d=identicon&r=PG';

		$header = imap_headerinfo($ibox, $nr);

		$from = $header->from[0]->mailbox . "@" . $header->from[0]->host;
		$subject = quoted_printable_decode(imap_utf8($header->subject));
		$date = date('d-m-Y H:i:s',strtotime($header->date));

		// registra il mittente nel database e incrementa relativo contatore
		dbRegMailIn($from);

		$unseen = '<i class="fa fa-eye-slash-o" style="color: #f23"></i>';
		if($header->Unseen == 'U') {
       		$unseen = '<i class="fa fa-eye"></i>';
    	}

    $uid = $nr;
    	// email uid
		$uid = imap_uid($ibox, $nr);
    	// msg info
    	$overview = imap_fetch_overview($ibox,$nr);
		echo '
			<div class="imsg">
				<a href="mail.php?folder='.$f.'&msgid='.$uid.'" title="Leggi"> <img src="'.$src.'" class="avatar"> </a>
				<p class="isub"> <a href="mail.php?folder='.$f.'&msgid='.$uid.'" title="Oggetto"> '. quoted_printable_decode(imap_utf8($overview[0]->subject)).'</a> <a class="unseen">'.$unseen.'</a></p>
				<p class="isub"> <a class="from">'.imap_utf8($overview[0]->from).'</a> </p>
				';

				if(strtolower($f) == 'trash' || strtolower($f) == 'cestino'){
		echo '
				<p class="itime" href=""> '.date('d-m-Y H:i:s',strtotime($overview[0]->date)).'  <a class="unseen detail" title="Elimina dal cestino" style="margin-left: 5px; cursor: pointer; color: #666" href="?folder='.$f.'&msgid='.$uid.'"> <i class="fa fa-trash"></i> </a> </p>
				';
			}
				else {
		echo '
				<p class="itime" href=""> '.date('d-m-Y H:i:s',strtotime($overview[0]->date)).'  <a class="unseen detail" title="Sposta nel cestino" style="margin-left: 5px; cursor: pointer; color: #666" href="?folder='.$f.'&msgid='.$uid.'"> <i class="fa fa-trash"></i> </a> </p>
				';
			}
		echo '
			</div>
				';
	}
}

imap_close($ibox);
?>
</div>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script type="text/javascript">
    $(document).ready(function(){
        $("button").click(function(){

            $.ajax({
                type: 'POST',
                url: 'deleteall.php',
								success: function(data) {
                    alert("Email cancellate");
										$('#mail_list').load(document.URL +  ' #mail_list');
                }
            });
   });
});
</script>
</body>
</html>
