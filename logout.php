<?php
session_start();
$email = $_SESSION['email'];
unlink('html.html');
$files = glob('media/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
}
$dir = $_SESSION['tmpdir'];
$files = glob($dir.'/*'); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
}
session_unset();
unset($_SESSION);
session_destroy();
header('Location: index.php');
?>
