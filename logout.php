<?php
//pokretanje sesije
session_start();
//uništavanje sesije
session_destroy();

//preusmjeravanje korisnika na početnu stranicu
header("Location: index.php");

//izlaz iz koda
exit();
?>
