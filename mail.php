<?php
header('Content-type: text/html; charset=utf-8');
session_start();
error_reporting('E_ALL');
require('core/config.php');

// chenge folder destination
$f = htmlentities($_GET['folder'], ENT_QUOTES, 'utf-8');
if (empty($f)) {
	$f = 'INBOX';
}

$tmpfolder = md5($_SESSION['email']);
mkdir('media/'.$tmpfolder);
$tmpdir = 'media/'.$tmpfolder;
$_SESSION['tmpdir'] = $tmpdir;
chmod($tmpdir, 755);

// get credentials from session
$ibox = imap_open($IMAP.$f, $_SESSION['email'], $_SESSION['pass']);
$folders = imap_list($ibox, "{folders}", "*");

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

$uid = (int)$_GET['msgid'];

$nr = imap_msgno($ibox,$uid);

$status = imap_setflag_full($ibox, $nr, "\Seen \Flagged", ST_UID);


		// header details IP, traceroute
		$info = imap_fetchheader($ibox, $nr);

		// random avatar icon
		$src = 'http://www.gravatar.com/avatar/'.rand(999,974983).'?s=90&d=identicon&r=PG';

		// from email and indicazione lettura
		$header = imap_headerinfo($ibox, $nr);
		$from = $header->from[0]->mailbox . "@" . $header->from[0]->host;
		$unseen = '<i class="fa fa-eye-slash-o" style="color: #f23"></i>';
		if($header->Unseen == 'U') {
       		$unseen = '<i class="fa fa-eye"></i>';
    	}

		$message = imap_fetchbody($ibox,$nr);

		$se = imap_fetchstructure($ibox, $nr, 1);


		if(true){
			require('core/class.EmailMessage.php');
			// the number in constructor is the message number
			$emailMessage = new EmailMessage($ibox, $nr);
			// set to true to get the message parts (or don't set to false, the default is true)
			$emailMessage->getAttachments = true;
			$emailMessage->fetch();
			preg_match_all('/src="cid:(.*)"/Uims', $emailMessage->bodyHTML, $matches);
			if(count($matches)) {
				$search = array();
				$replace = array();

				$DOMAIN = $_SERVER["HTTP_HOST"].pathinfo($_SERVER["REQUEST_URI"], PATHINFO_DIRNAME);
				foreach($matches[1] as $match) {
					$uniqueFilename = explode('@',$match)[0];
					$ext = pathinfo($uniqueFilename, PATHINFO_EXTENSION);

					if($ext != 'php' && $ext != 'cgi'){
					file_put_contents($tmpdir."/".$uid."-$uniqueFilename", $emailMessage->attachments[$match]['data']);
					$search[] = "src=\"cid:$match\"";
					$replace[] = "src=\"http://$DOMAIN/".$tmpdir."/".$uid."-$uniqueFilename\"";
					}
				}
				$part = explode('<br />', $emailMessage->bodyHTML);
				$emailMessage->bodyHTML = implode('', $part);
				$emailMessage->bodyHTML = str_replace($search, $replace, $emailMessage->bodyHTML);
			}
		}

		//get message body
        if($message == ''){

        	$message = html_entity_decode($emailMessage->bodyHTML);
        }
		if($message == '')
        {
           $message = quoted_printable_decode(imap_fetchbody($ibox,$nr,1.1));
        }
        if($message == '')
        {
        	print_r(imap_fetchbody($ibox,$nr,1));

        }
        file_put_contents($tmpdir."/".$uid.'.html', $message);

    	$overview = imap_fetch_overview($ibox,$nr);
		echo '
			<div class="imsg nohover">
				<img src="'.$src.'" class="avatar">
				<p class="isub"> '.quoted_printable_decode(imap_utf8($overview[0]->subject)).'</p>
				<p class="isub">
					<a class="unseen">'.$unseen.'</a>
				</p>
				<p class="isub"> <a class="from">'.imap_utf8($overview[0]->from).'</a> <a class="email">'.$from.'</a></p>
			</div>
		';


$structure = imap_fetchstructure($ibox,$nr,0);

$attachments = array();
if(isset($structure->parts) && count($structure->parts) > 0) {
	for($i = 0; $i < count($structure->parts); $i++) {

		$attachments[$i] = array(
			'is_attachment' => false,
			'filename' => '',
			'name' => '',
			'attachment' => ''
		);
		if($structure->parts[$i]->ifdparameters) {
			foreach($structure->parts[$i]->dparameters as $object) {
				if(strtolower($object->attribute) == 'filename') {
					$attachments[$i]['is_attachment'] = true;
					$attachments[$i]['filename'] = $object->value;
				}
			}
		}

		if($structure->parts[$i]->ifparameters) {
			foreach($structure->parts[$i]->parameters as $object) {
				if(strtolower($object->attribute) == 'name') {
					$attachments[$i]['is_attachment'] = true;
					$attachments[$i]['name'] = $object->value;
				}
			}
		}

		if($attachments[$i]['is_attachment']) {
			$attachments[$i]['attachment'] = imap_fetchbody($ibox, $nr, $i+1);
			if($structure->parts[$i]->encoding == 3) { // 3 = BASE64
				$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
			}
			elseif($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
				$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
			}
		}
	}
}

/* iterate through each attachment and save it */
foreach($attachments as $attachment)
{

    if($attachment['is_attachment'] == 1 && !empty($attachment['name']))
    {
        $filename = $attachment['name'];
        if(empty($filename)) $filename = $attachment['filename'];

        if(empty($filename)) $filename = time().".dat";

        $fp = fopen($tmpdir."/".$filename, "w+");
        fwrite($fp, $attachment['attachment']);
        fclose($fp);
    }

}

echo '<div class="bottom">';
foreach ($attachments as $v) {
	if (!empty($v['filename']) && pathinfo(basename($attachment['name']),PATHINFO_EXTENSION) != 'dat') {
		echo '<a class="file" target="_blank" href="'.$tmpdir."/".$v['filename'].'">'.$v['filename'].'</a>';
	}
}
echo '</div>';

echo '<div class="bottom1">
	<p class="title"> Dettagli  <a class="unseen btnclose" title="Dettagli" style="float: right; margin-right: 5px; cursor: pointer; color: #f22"> <i class="fa fa-close"></i> </a> </p>
';
echo nl2br($info);
echo '</div>';


echo '<div class="body"><iframe src="'.$tmpdir."/".$uid.'.html" sandbox="allow-same-origin allow-popups allow-forms"></iframe></div>';

imap_expunge($ibox);
imap_close($ibox,CL_EXPUNGE);
?>

<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700|Roboto+Condensed:300,400,700&subset=latin-ext" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/theme.css">
