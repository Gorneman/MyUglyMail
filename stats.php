<?php
header('Content-type: text/html; charset=utf-8');
session_start();
error_reporting('E_ALL');
require('core/config.php');
require('core/functions.php');
// get credentials from session

// chenge folder destination
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
	<title>My Ugly webmail - Statistiche</title>
<meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="Accedi alla casella">
  <meta name="keywords" content="email client,e-mail,email,">
	<link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
	<link href="https://fonts.googleapis.com/css?family=Roboto:300,400,700|Roboto+Condensed:300,400,700&subset=latin-ext" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="css/theme.css">
	<link rel="stylesheet" type="text/css" href="css/stats.css">
</head>
<body>
<?php
echo '<ul class="folders">';
foreach ($folders as $folder) {
    $folder = str_replace("{folders}", "", imap_utf7_decode($folder));

    if(strtolower($folder) == 'trash' || strtolower($folder) == 'cestino'){
    	//Cestino
    	echo '<li><a href="inbox.php?folder=' . $folder . '">  <i class="fa fa-trash-o"></i> Cestino </a></li>';
	}else if(strtolower($folder) == 'inviato' || strtolower($folder) == 'posta inviata' || strtolower($folder) == 'sent'){
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

?>

<?php
$sum_in = dbNoMailIn(); //richiama le funzioni per le statistiche
$sum_out = dbNoMailOut();
$lista_in = dbMostInRec();
$lista_out = dbMostOutRec();
?>

<div id="container">
<div id="leftdiv">
<p style="align-text:center">Totale E-mail ricevute&nbsp <?php echo $sum_in; ?> </p>

<table class="data-table">
	<caption class="tab-title">Mittenti piú ricorrenti</caption>
	<thead>
		<tr>
			<th>Indirizzo E-mail</th>
			<th>Numero mail</th>
		</tr>
	</thead>
	<tbody>
		<?php

		foreach($lista_in as $row)
		{
			echo '<tr>
					<td>'.$row['e_address'].'</td>
					<td>'.$row['in_count'].'</td>
				</tr>';

		}?>
		</tbody>
</table>
</div>

<div id="rightdiv">
<p style="align-text:center">Totale E-mail inviate&nbsp  <?php echo $sum_out; ?> </p>

<table class="data-table">
	<caption class="tab-title">Destinatari piú ricorrenti</caption>
	<thead>
		<tr>
			<th>Indirizzo E-mail</th>
			<th>Numero mail</th>
		</tr>
	</thead>
	<tbody>
		<?php

		foreach($lista_out as $row)
		{
			echo '<tr>
					<td>'.$row['e_address'].'</td>
					<td>'.$row['out_count'].'</td>
				</tr>';

		}?>
		</tbody>
</table>
</div>
</div>

<?php
imap_close($ibox);
?>

</body>
</html>
