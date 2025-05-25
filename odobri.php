<?php
//započinjanje sesije
session_start();
//provjera da li je submitan ID zadatka
if (!isset($_POST["id"])) {
    header("Location: dashboard.php");
    exit();
}

//ID zadatka koji se odobrava
$zadatak_id = $_POST["id"];

//putanja do XML međuspremnika
$xmlPath = "Zadatak.xml";
//driverov DSN file za povezivanje na Access bazu 
$dsnFile = "C:\\TeamPlanDBProba.dsn";
//učitaj XML dokumenta u DOM objekte
$dom = new DOMDocument();
$dom->load($xmlPath);
$dom->formatOutput = true;
$zadaci = $dom->getElementsByTagName("Zadatak");

//povezivanje na bazu
try {
    $pdo = new PDO("odbc:FILEDSN=$dsnFile;", '', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Greška kod spajanja na bazu.");
}

//formatiraj datuma za Access: iz YYYY-MM-DD u MM/DD/YYYY
function formatDatuma($date) {
    $timestamp = strtotime($date);
    return date("m/d/Y", $timestamp);
}

//iteracija kroz zadatke
foreach ($zadaci as $zadatak) {
    //ako zadatak ima naš zadani ID spremi njegove vrijednosti u varijable
    if ($zadatak->getElementsByTagName("ID")[0]->nodeValue == $zadatak_id) {
        $naziv = $zadatak->getElementsByTagName("Naziv")[0]->nodeValue;
        $opis = $zadatak->getElementsByTagName("Opis")[0]->nodeValue;
        $pocetak = formatDatuma($zadatak->getElementsByTagName("Vrijeme_pocetak")[0]->nodeValue);
        $kraj = formatDatuma($zadatak->getElementsByTagName("Vrijeme_kraj")[0]->nodeValue);

        //ažuriraj zadatak u bazi
        $updateUpit = $pdo->prepare("UPDATE zadatak SET naziv = ?, opis = ?, vrijeme_pocetak = ?, vrijeme_kraj = ? WHERE ID = ?");
        $updateUpit->execute([$naziv, $opis, $pocetak, $kraj, $zadatak_id]);

        //dodaj članove
        $dodani = $zadatak->getElementsByTagName("Dodani_clanovi")[0];
        if ($dodani) {
            foreach ($dodani->getElementsByTagName("Clan") as $clan) {
                $clan_id = $clan->getElementsByTagName("ID_clana")[0]->nodeValue;

                //postoji li veza između korisnika i projekta
                $clan_projekta = $pdo->prepare("SELECT ID FROM clanovi_projekta WHERE korisnik_ID = ? AND projekt_ID = (SELECT projekt_ID FROM zadatak WHERE ID = ?)");

                $clan_projekta->execute([$clan_id, $zadatak_id]);
                $clanProjekt = $clan_projekta->fetch(PDO::FETCH_ASSOC);

                if ($clanProjekt) {
                    $clan_projekta_id = $clanProjekt['ID'];

                    //postoji li već veza između člana projekta i zadatka
                    $check = $pdo->prepare("SELECT COUNT(*) FROM clanovi_zadatka WHERE clan_projekta_ID = ? AND zadatak_ID = ?");
                    $check->execute([$clan_projekta_id, $zadatak_id]);

                    if ($check->fetchColumn() == 0) {
                        //ubacivanje nove veze
                        $insert = $pdo->prepare("INSERT INTO clanovi_zadatka (clan_projekta_ID, zadatak_ID) VALUES (?, ?)");
                        $insert->execute([$clan_projekta_id, $zadatak_id]);
                    }
                }
            }
        }

        //izbriši članove
        $izbrisani = $zadatak->getElementsByTagName("Izbrisani_clanovi")[0];
        if ($izbrisani) {
            foreach ($izbrisani->getElementsByTagName("Clan") as $clan) {
                $clan_id = $clan->getElementsByTagName("ID_clana")[0]->nodeValue;

                //pronađi ID veze u tablici clanovi_projekta
                $clan_projekta = $pdo->prepare("SELECT ID FROM clanovi_projekta WHERE korisnik_ID = ? AND ID IN (SELECT clan_projekta_ID FROM clanovi_zadatka WHERE zadatak_ID=?)");
                $clan_projekta->execute([$clan_id, $zadatak_id]); 
                $clanProjekt = $clan_projekta->fetch(PDO::FETCH_ASSOC);

                if ($clanProjekt) {
                    $clan_projekta_id = $clanProjekt['ID'];
                    //briši iz tablice clanovi_zadatka ako postoji
                    $delete = $pdo->prepare("DELETE FROM clanovi_zadatka WHERE clan_projekta_ID = ? AND zadatak_ID = ?");
                    $delete->execute([$clan_projekta_id, $zadatak_id]);
                }
            }
        }

        //ukloni zadatak iz XML-a
        $zadatak->parentNode->removeChild($zadatak);
        $dom->save($xmlPath);

        break; //prekini jer smo pronašli odgovarajući zadatak
    }
}

//preusmjeri na natrag na dashboard
header("Location: dashboard.php");
exit();
?>
