<?php
//========================================================================
// IMAP SETTINGS
$IMAPhost = 'imap.mail.yahoo.com';
$IMAPport = '993';
$IMAPssl = 'ssl';

//=======================================================================
// IMAP CONNECTION URL don't change line belove
$IMAP = '{'.$IMAPhost.':'.$IMAPport.'/imap/'.$IMAPssl.'/novalidate-cert}';

// SMTP settings hostname for phpmailer
$SMTPdomain = 'smtp.mail.yahoo.com';
//SMTP port 25 or 587 or 465
$SMTPPort = 587;
// SMTP tls or ssl type
$SMTPSecure = "ssl";
// SMTP authentication
$SMTPAuth = true;
// Set send from email Name
$SetFrom = 'Domenico Di Misa';
