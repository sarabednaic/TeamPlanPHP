<?php
//započinjanje sesije
session_start();
//provjera da li je submitan ID zadatka
if (!isset($_POST["id"])) {
    header("Location: dashboard.php");
    exit();
}

//postavi ID zadatka u varijablu
$id = $_POST["id"];
//putanja do XML međuspremnika
$xmlPath = "Zadatak.xml";
//učitaj XML koristeći DOMDocument u objekt
$dom = new DOMDocument();
$dom->formatOutput = true;
$dom->load($xmlPath);
$zadaci = $dom->getElementsByTagName("Zadatak");

//iteracija kroz zadatke i brisanje onog s odgovarajućim ID-em
foreach ($zadaci as $zadatak) {
    $trenutniId = $zadatak->getElementsByTagName("ID")[0]->nodeValue;

    if ($trenutniId == $id) {
        $zadatak->parentNode->removeChild($zadatak);
        $dom->save($xmlPath);
        break;
    }
}

//prusmjeri na natrag na dashboard
header("Location: dashboard.php");
exit();
?>
