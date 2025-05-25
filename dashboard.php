<?php
//započinjanje sesije te ako nije pronađena preusmjerava na login
session_start();
if (!isset($_SESSION["username"])) {
    header("Location: index.php");
    exit();
}

//driverov DSN file za povezivanje na Access bazu 
$dsnFile = "C:\\TeamPlanDBProba.dsn";
//povezivanje na bazu
try {
    $pdo = new PDO("odbc:FILEDSN=$dsnFile;", '', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Greška kod spajanja na bazu.");
}

//učitavanje XML datoteke u objekt
$xml = simplexml_load_file("Zadatak.xml");
//funkcija za pretraživanje korisnika u bazi po ID-u
function getKorisnik($id, PDO $pdo) {
    // upit
    $korisnik = $pdo->prepare("SELECT ime, prezime FROM korisnik WHERE ID = ?");
    $korisnik->execute([$id]);
    $row = $korisnik->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        return '';
    }

    $ime = $row['ime'];
    $prezime = $row['prezime'];
    //ak prezime završava na "c" zamijeni zadnji znak s "ć"
    if (mb_substr($prezime, -1) === 'c') {
        $prezime = mb_substr($prezime, 0, -1) . 'ć';
    }

    $imePrezime = $ime . ' ' . $prezime;
    return $imePrezime;
}


//pregleda da li ima razlika u stringu
//trim uklanja whitespaceove, tabove i nove redove s pocetka i kraja
function prikaziRazliku($original, $novi) {
    //ako postoji razlika onda je novo zeleno
    if (trim($original) !== trim($novi)) {
        return '<s>' . htmlspecialchars($original) . '</s><br><span style="color:green;">' . htmlspecialchars($novi) . '</span>';
    }
    return htmlspecialchars($original);
}

//pregleda da li ima razlika u datumu
function prikaziDatumRazliku($original, $novi) {
    $o = new DateTime($original);
    $n = new DateTime($novi);
    //ako postoji razlika onda je novo zeleno
    if ($o != $n) {
        return '<s>' . $o->format('m/d/Y') . '</s><br><span style="color:green;">' . $n->format('m/d/Y') . '</span>';
    }
    return $o->format('m/d/Y');
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <h1>TeamPlan</h1>
    <a href="logout.php" class="logout">Odjava</a>
</header>
<main>
    <h2>Dobrodošli, <?php echo $_SESSION["name"] . "!"; ?></h2>
    <h3>Popis zadataka za odobravanje</h3>
    <table>
        <tr>
            <th>ID</th><th>Naziv</th><th>Opis</th><th>Početak</th><th>Kraj</th>
            <th>Dodani članovi</th><th>Izbrisani članovi</th><th>Akcije</th>
        </tr>
        <?php foreach ($xml->Zadatak as $zadatak): ?>
            <?php if ((string)$zadatak->ID_admina_projekta !== $_SESSION["admin_id"]) continue; ?>
            <?php
                $idZadatka = (int)$zadatak->ID;
                $stmtZadatak = $pdo->prepare("SELECT naziv, opis, vrijeme_pocetak, vrijeme_kraj FROM zadatak WHERE ID = ?");
                $stmtZadatak->execute([$idZadatka]);
                $zadatakBaza = $stmtZadatak->fetch(PDO::FETCH_ASSOC);
            ?>
            <tr>
                <td><?php echo $idZadatka; ?></td>
                <td><?php echo prikaziRazliku($zadatakBaza['naziv'], (string)$zadatak->Naziv); ?></td>
                <td><?php echo prikaziRazliku($zadatakBaza['opis'], (string)$zadatak->Opis); ?></td>
                <td><?php echo prikaziDatumRazliku($zadatakBaza['vrijeme_pocetak'], (string)$zadatak->Vrijeme_pocetak); ?></td>
                <td><?php echo prikaziDatumRazliku($zadatakBaza['vrijeme_kraj'], (string)$zadatak->Vrijeme_kraj); ?></td>
                <td>
                    <?php foreach ($zadatak->Dodani_clanovi->Clan as $clan): ?>
                        <?php echo getKorisnik((int)$clan->ID_clana, $pdo); ?><br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <?php foreach ($zadatak->Izbrisani_clanovi->Clan as $clan): ?>
                        <?php echo getKorisnik((int)$clan->ID_clana, $pdo); ?><br>
                    <?php endforeach; ?>
                </td>
                <td>
                    <form action="odobri.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $idZadatka; ?>">
                        <button type="submit">Odobri</button>
                    </form>
                    <form action="odbi.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $idZadatka; ?>">
                        <button type="submit">Odbij</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</main>
</body>
</html>
