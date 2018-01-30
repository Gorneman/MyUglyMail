<?php
header('Content-type: text/html; charset=utf-8');
session_start();
error_reporting('E_ALL');
require('core/config.php');


if (isset($_POST['send'])) {
  if (!empty($_POST['email']) && !empty($_POST['pass'])) {
    $email = htmlentities($_POST['email'],ENT_QUOTES,'utf-8');
    $pass = htmlentities($_POST['pass'], ENT_QUOTES, 'utf-8');
    $ibox = imap_open($IMAP, $email, $pass);
    if ($ibox === false) {
      echo '<p class="toperror"> Impossibile connettersi! Controlla la tua email e/o la tua password.</p>';
    }else{
      $_SESSION['login'] = 1;
      $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
      $_SESSION['email'] = $email;
      $_SESSION['pass'] = $pass;
      echo "Login effettuato con successo";
      header('Location: inbox.php');
      die();
    }
  }else{
    echo '<p class="toperror">Per favore, immetti nome utente e password</p>';
  }
}

?>
<!DOCTYPE html>
<html>
  <head>
  <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" type="text/css" href="css/style.css">
    <title>My Ugly webmail</title>
  </head>
  <body>
      <h1>My Ugly webmail</h1>
      <form id="login" method="POST" action="">
      <label><i class="fa fa-envelope"></i> Login <small></small> </label>
      <input type="text" id="mnick" autocomplete="off" placeholder="Inserisci e-mail" name="email">
      <input type="password" id="mnick" autocomplete="off" placeholder="Inserisci password" name="pass" >
      <input type="submit" name="send" value="Accedi" id="sendbtn">
    </form>
</body>
<?php $color = '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
$_SESSION['color'] = $color;
if ($color == '#fff' || $color == '#ffffff') {
  $color = '#ff6600';
}
?>
</html>
