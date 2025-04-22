<?php
session_start();

//provjera da li je submitan ID zadatka
if (!isset($_POST["id"])) {
    header("Location: dashboard.php");
    exit();
}

//postavi ID zadatka u varijablu
$id = $_POST["id"];

//putanja do XML-a
$xmlPath = "Zadatak.xml";
$dsnFile = "C:\\TeamPlanDB.dsn";

//učitati XML koristeći DOMDocument(lakše za brisanje objekata iz XML fajla)
$dom = new DOMDocument();
//formatira XML fajla kod spremanja
$dom->formatOutput = true;
//učitati XML fajl u ovaj objekt
$dom->load($xmlPath);

//u varijablu zadaci postavi listu objekata zadatak iz XML fajla
$zadaci = $dom->getElementsByTagName("Zadatak");

//iteracija kroz zadatke
foreach ($zadaci as $zadatak) {
    //iz svakog zadatka dohvaća vrijednost ID-a i nodeValue uzima tekst unutar taga id
    $trenutniId = $zadatak->getElementsByTagName("ID")[0]->nodeValue;

    //ako je to isti id kao onaj submitanog zadatka
    if ($trenutniId == $id) {
        //izbriši zadatak iz XML fajla
        $zadatak->parentNode->removeChild($zadatak);
        $dom->save($xmlPath);
        break;
    }
}

//vrati korisnika na dashboard
header("Location: dashboard.php");
exit();
?>
