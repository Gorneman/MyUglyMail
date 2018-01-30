<?php
session_start();
error_reporting(0);
//ini_set("display_errors", 1);
require('core/config.php');
require('core/functions.php');
set_time_limit(0);
ini_set('upload_max_filesize', '100M');

//Import PHPMailer classes into the global namespace
require 'Mailer6/src/PHPMailer.php';
require 'Mailer6/src/Exception.php';
require 'Mailer6/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Content-type: text/html; charset=utf-8');

// login first
$IMAPuser = $_SESSION['email'];
$IMAPpass = $_SESSION['pass'];
$ibox = imap_open($IMAP, $IMAPuser, $IMAPpass);
if ($ibox == false) {
	header('Location: index.php');
	exit;
}

$folders = imap_list($ibox, "{folders}", "*");
$menu = '<ul class="folders">';
foreach ($folders as $folder) {
    $folder = str_replace("{folders}", "", imap_utf7_decode($folder));

    if(strtolower($folder) == 'trash' || strtolower($folder) == 'cestino'){

    	$menu .= '<li><a href="inbox.php?folder=' . $folder . '&func=view">  <i class="fa fa-trash-o"></i> Cestino </a></li>';
	}else if(strtolower($folder) == 'sent' || strtolower($folder) == 'inviato'){

		$menu .= '<li><a href="inbox.php?folder=' . $folder . '&func=view">  <i class="fa fa-send-o"></i> Posta inviata </a></li>';
	}else if(strtolower($folder) == 'inbox' || strtolower($folder) == 'in arrivo' || strtolower($folder) == 'ricevuto'){

		$menu .= '<li><a href="inbox.php?folder=' . $folder . '&func=view">  <i class="fa fa-envelope-o"></i> Posta ricevuta </a></li>';
	}else if(strtolower($folder) == 'spam' || strtolower($folder) == 'posta indesiderata'){

		$menu .= '<li><a href="inbox.php?folder=' . $folder . '&func=view">  <i class="fa fa-ban"></i> Posta indesiderata </a></li>';
	}
}
$menu .= '<li><a href="sendemail.php">  <i class="fa fa-send"></i> Invia un messaggio </a></li>';
$menu .= '<li><a href="stats.php">  <i class="fa fa-bar-chart"></i> Statistiche </a></li>';
$menu .= '<li><a href="logout.php">  <i class="fa fa-sign-out"></i> Logout </a></li>';
$menu .= "</ul>";

function getDomain($host){
	$e = explode('.', $host);
	$c = count($e);
	return $h = $e[$c-2].'.'.$e[$c-1];
}

if (isset($_POST['add'])) {
$email = $_POST['email'];
$msg = $_POST['msg'];
$subject = $_POST['subject'];

if (filter_var($email, FILTER_VALIDATE_EMAIL) != false) {

$fn = basename($_FILES['file']['name']);
$ftmp = $_FILES['file']['tmp_name'];
$ext = pathinfo(basename($fn),PATHINFO_EXTENSION);
if ($ext == 'php' || $ext == 'cgi') {
	$error = "Tipo di file incompatibile.";
}else{
	if(!empty($fn))move_uploaded_file($ftmp,'tmp/'.$fn);
}

$ip = $_SERVER['REMOTE_ADDR'];
	if (empty($_POST['email']) || empty($_POST['msg']) || empty($_POST['subject'])) {
	echo '<p class="toperror">Per favore, compila tutti i campi.</p>';
	}else{
		error_reporting(E_ALL);

		// send mail
		$sub = $subject;
		$msg = preg_replace("[\\\]",'', $msg);
		$mail = new PHPMailer();
		// send in html format
		$mail->IsHTML(true);
		$mail->IsSMTP();

		$mail->SMTPDebug  = 1;
		//hostname
		$mail->Host       = $SMTPdomain; 		// SMTP server hostname
		$mail ->CharSet   = "utf-8";		// charset utf-8
		$Mail->Encoding   = '8bit';		// encoding
		$mail->SMTPSecure = $SMTPSecure;    // true or false
		$mail->Port       = $SMTPPort;          // set the SMTP port for the GMAIL server
		$mail->SMTPAuth   = $SMTPAuth;      // enable SMTP authentication
		$mail->Username   = $IMAPuser; 		// SMTP account username
		$mail->Password   = $IMAPpass;      // SMTP account password

		$mail->SetFrom($IMAPuser, $SetFrom);
		$mail->AddReplyTo($IMAPuser, $SetFrom);

		$mail->Subject    = $sub;
		$mail->AltBody    = $sub;
		$mail->MsgHTML(html_entity_decode($msg));

		// email to and name
		$mail->AddAddress($email, 'Salve');

		if (file_exists('tmp/'.$fn) && !empty($fn)) {
			$mail->AddAttachment('tmp/'.$fn);      // attachment
		}


		$dir = $_SERVER['DOCUMENT_ROOT'];
		$file = 'logs/SendMailLog-'.date('d-m-Y-H', time()).'.txt'; //operation log

		if(!$mail->Send()) {
			$time = date('d-m-Y H:i:s', time());
		  	$err = $time." ###Mailer Error confirmation : " . $mail->ErrorInfo . " ###MAIL " . $email."<br>\r\n";
		  	file_put_contents($file, $err, FILE_APPEND);
		  	echo '<p class="toperror">Nessun messaggio è stato inviato all\'indirizzo email fornito.</p>';
		} else {

		$ibox = imap_open($IMAP, $_SESSION['email'], $_SESSION['pass']);
		$dmy=date("d-m-Y H:i:s");
		$boundary = "------=".md5(uniqid(rand()));

		if(file_exists('tmp/'.$fn)){

		}

		$boundary1 = "###".md5(microtime())."###";
		$boundary2 = "###".md5(microtime().rand(9,999))."###";
		imap_append($ibox, $IMAP."Posta Inviata"
			, "From: <".$IMAPuser.">\r\n"
			. "To: ".$email."\r\n"
			. "Date: $dmy\r\n"
			. "Subject: ".quoted_printable_encode($sub)."\r\n"
		        . "MIME-Version: 1.0\r\n"
		        . "Content-Type: multipart/mixed; boundary=\"$boundary1\"\r\n"
		        . "\r\n\r\n"
		        . "--$boundary1\r\n"
		        . "Content-Type: multipart/alternative; boundary=\"$boundary2\"\r\n"
		        . "\r\n\r\n"
		        // ADD Plain text data
		        . "--$boundary2\r\n"
		        . "Content-Type: text/plain; charset=\"utf-8\"\r\n"
		        . "Content-Transfer-Encoding: quoted-printable\r\n"
		        . "\r\n\r\n"
		    	. $msg."\r\n"
		        . "\r\n\r\n"
		        // ADD Html content
		        . "--$boundary2\r\n"
		        . "Content-Type: text/html; charset=\"utf-8\"\r\n"
		        . "Content-Transfer-Encoding: quoted-printable \r\n"
		        . "\r\n\r\n"
		    	. html_entity_decode($msg)."\r\n"
		        . "\r\n\r\n"
		        . "--$boundary2\r\n"
		        . "\r\n\r\n"
		        // ADD attachment(s)
		        . "--$boundary1\r\n"
		        . "Content-Type: image/gif; name=\"$fn\"\r\n"
		        . "Content-Transfer-Encoding: base64\r\n"
		        . "Content-Disposition: attachment; filename=\"$fn\"\r\n"
		        . "\r\n\r\n"
		        . $attachment
		        . "\r\n\r\n"
		        . "--$boundary1--\r\n\r\n"
		);
			$time = date('d-m-Y H:i:s', time());
			$log = $time." ###CONFIRMATION Message sent! ".$email." Oggetto: ".$sub.' Messaggio: '.$msg."<br>\r\n";
			file_put_contents($file, $log, FILE_APPEND);
			//registra il destinatario nel database e incrementa il relativo contatore
			dbRegMailOut($email);
			echo '<p class="toperror">Messaggio inviato con successo.</p>';
		}

	}
}else{
	echo '<p class="toperror">Per favore controlla l\'indirizzo e-mail.</p>';
}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>My Ugly webmail - Invia una E-mail</title>
	  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
		<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700|Roboto+Condensed:300,400,700&subset=latin-ext" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/style.css">
		<link rel="stylesheet" type="text/css" href="css/theme.css">

</head>
<body>
<?php echo $menu; ?>
<form id="login" method="POST" action="" style="min-width: 90%;" enctype="multipart/form-data">
<label>Nuovo messaggio</label>
<p class="error"><?php echo $error; ?></p>
	<input type="text" name="email" placeholder="Destinatario">
	<input type="text" name="subject" placeholder="Oggetto">
	<textarea name="msg" placeholder="Messaggio" style="min-height: 250px; max-height: 400px; padding: 5px;"></textarea>
	<input type="file" name="file">
	<input type="submit" name="add" value="Invia" id="sendbtn">
</form>
</body>
</html>
