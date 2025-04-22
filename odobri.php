<?php
session_start();

//provjera da li je submitan ID zadatka
if (!isset($_POST["id"])) {
    header("Location: dashboard.php");
    exit();
}

//postavi ID zadatka u varijablu
$id = $_POST["id"];

//putanja do XML-a i DSN driver fajla
$xmlPath = "Zadatak.xml";
$dsnFile = "C:\\TeamPlanDB.dsn";

//učitati XML koristeći DOMDocument(lakše za brisanje objekata iz XML fajla)
$dom = new DOMDocument();
//formatira XML fajla kod spremanja
$dom->formatOutput = true;
//učitati XML fajl u ovaj objekt
$dom->load($xmlPath);

//konekcija na access bazu
try {
    //PHP data objects -> DSN fajl, username, lozinka
    $pdo = new PDO("odbc:FILEDSN=$dsnFile;", '', '');
} catch (PDOException $greska) {
    echo "Došlo je do greške u bazi kod povezivanja na bazu.";
    exit();
}

//u varijablu zadaci postavi listu objekata zadatak iz XML fajla
$zadaci = $dom->getElementsByTagName("Zadatak");

//iteracija kroz zadatke
foreach ($zadaci as $zadatak) {
    //iz svakog zadatka dohvaća vrijednost ID-a i nodeValue uzima tekst unutar taga id
    $trenutniId = $zadatak->getElementsByTagName("ID")[0]->nodeValue;

    //ako je to isti id kao onaj submitanog zadatka
    if ($trenutniId == $id) {
        //priprema podataka za bazu
        $naziv = $zadatak->getElementsByTagName("Naziv")[0]->nodeValue;
        $opis = $zadatak->getElementsByTagName("Opis")[0]->nodeValue;
        $pocetak = $zadatak->getElementsByTagName("Vrijeme_pocetak")[0]->nodeValue;
        $kraj = $zadatak->getElementsByTagName("Vrijeme_kraj")[0]->nodeValue;

        //upis u bazu
        try {
            $upit = $pdo->prepare("UPDATE zadatak SET naziv = ?, opis = ?, vrijeme_pocetak = ?, vrijeme_kraj = ? WHERE id = ?");
            $upit->execute([$naziv, $opis, $pocetak, $kraj, $id]);
        } catch (PDOException $greska) {
            echo "Došlo je do greške kod upisa u bazu.";
            exit();
        }        

        //ukloni trenutno updateani zadatak iz XML-a
        $zadatak->parentNode->removeChild($zadatak);
        $dom->save($xmlPath);
        break;
    }
}

//vrati korisnika na dashboard
header("Location: dashboard.php");
exit();
?>
