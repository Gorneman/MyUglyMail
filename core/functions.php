<?php
// funzione per la connessione al database
function dbConnect() {
  $conn = mysqli_connect("sabaik6fx8he7pua.chr7pe7iynqr.eu-west-1.rds.amazonaws.com", "ksg0yf698l1abox5", "bu9s3glga31y01wf", "ywpuphn4dt5y5qv7")
    or die("Errore nella connessione al db: " . mysqli_error($conn));

  return $conn;
}

// se l'indirizzo mail ricevuto é giá presente incrementa in_count di 1,
// altrimenti lo memorizza e imposta in_count a 1
function dbRegMailIn($from) {
  $conn = dbConnect();

  $sql = "INSERT INTO mail_counter (e_address,in_count) VALUES('" . $from . "','1')
    ON DUPLICATE KEY UPDATE in_count = in_count + 1;";
  mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  mysqli_close($conn);
}

// come dbRegMailIn ma per le mail inviate
function dbRegMailOut($email) {
  $conn = dbConnect();

  $sql = "INSERT INTO mail_counter (e_address,out_count) VALUES('" . $email . "','1')
    ON DUPLICATE KEY UPDATE out_count = out_count + 1;";
  mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  mysqli_close($conn);
}

// ritorna il numero di mail ricevute
function dbNoMailIn() {
  $conn = dbConnect();

  $sql = "SELECT SUM(in_count) AS in_sum FROM mail_counter;";
  $risposta = mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  $row = mysqli_fetch_assoc($risposta);
  $sum_in = $row['in_sum'];

  mysqli_free_result($risposta);
  mysqli_close($conn);

  return $sum_in;
}

// ritorna il numero di mail inviate
function dbNoMailOut() {
  $conn = dbConnect();

  $sql = "SELECT SUM(out_count) AS out_sum FROM mail_counter;";
  $risposta = mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  $row = mysqli_fetch_assoc($risposta);
  $sum_out = $row['out_sum'];

  mysqli_free_result($risposta);
  mysqli_close($conn);

  return $sum_out;
}

// ritorna un'array dei 10 indirizzi da cui si sono ricevute piú mail
function dbMostInRec() {
  $conn = dbConnect();
  $risultato_in = array();

  $sql = "SELECT e_address,in_count FROM mail_counter ORDER BY in_count DESC LIMIT 10;";
  $risposta = mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  return $risposta;
}

// ritorna un'array dei 10 indirizzi a cui si sono inviate piú mail
function dbMostOutRec() {
  $conn = dbConnect();
  $risultato_out = array();

  $sql = "SELECT e_address,out_count FROM mail_counter ORDER BY out_count DESC LIMIT 10;";
  $risposta = mysqli_query($conn,$sql) or die("Errore nella query: "
    . $sql . "\n" . mysqli_error($conn));

  return $risposta;
}

?>
