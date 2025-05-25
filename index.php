<?php
//pokretanje sesije
session_start();
//varijabla za ispis greške ak do nje dođe
$error = "";

//dodavanje soli i hashiranje unesene lozinke
function sazimanje($lozinka, $sol) {
    $lozinkaSol = $lozinka . $sol;
    $hashiranje = hash('sha256', $lozinkaSol, true);
    return base64_encode($hashiranje);
}

//driverov DSN file za povezivanje na Access bazu 
$dsnFile = "C:\\TeamPlanDB.dsn";

//povezivanje na bazu => PDO=PHP Data Objects
try {
    $pdo = new PDO("odbc:FILEDSN=$dsnFile;", '', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Greška kod spajanja na bazu.");
}

//prijava
if (isset($_POST['login'])) {
    //prebacivanje korisnickog imena i lozinke u varijable
    $username = $_POST["username"];
    $password = $_POST["password"];
    //upit za dohvat admina koji provjerava da li je osoba sa ovim korisnickim imenom na nekom projektu admin
    $sql = "SELECT korisnik.ID, korisnik.ime, korisnik.prezime, korisnik.korisnicko_ime, korisnik.lozinka AS lozinka_hash, korisnik.sol
            FROM korisnik
            INNER JOIN clanovi_projekta ON korisnik.ID = clanovi_projekta.korisnik_ID
            WHERE clanovi_projekta.admin = TRUE AND korisnik.korisnicko_ime = ?";
    //PHP document object priprema upit za bazu
    $admin_info = $pdo->prepare($sql);
    //izvršava upit sa korisnickim imenom
    $admin_info->execute([$username]);
    //uzima prvi redak rezultata upita i sprema ga u objekt admin
    $admin = $admin_info->fetch(PDO::FETCH_ASSOC);

    //ako postoji rezultat upita
    if ($admin) {
        //sprema sol za danog admina u bazu
        $salt = $admin["sol"];
        //dodaj sol i hashira lozinku u funkciji sazimanje
        $uneseniHash = sazimanje($password, $salt);
        //ako je obrađeni hash ove lozinke isti kao i onaj u bazi
        if ($uneseniHash === $admin["lozinka_hash"]) {
            //spremanje sesije
            $_SESSION["username"] = $admin["korisnicko_ime"];
            $_SESSION["name"] = $admin["ime"];
            $_SESSION["surname"] = $admin["prezime"];
            $_SESSION["admin_id"] = $admin["ID"];
            //prebaci na dashboard sa zadacima
            header("Location: dashboard.php");
            //izađi iz koda
            exit();
        }
    }
    //neuspješna prijava
    $error = "Neispravno korisničko ime ili lozinka!";
}
?>

<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Prijava</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1>TeamPlan</h1>
    </header>
    <main>
        <h2>Prijava admina</h2>
        <form method="post">
            <label>Korisničko ime:</label><br>
            <input type="text" name="username" required><br><br>
<label>Lozinka:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit" name="login">Prijava</button>
    </form>
    <?php if (isset($error)) {
        echo "<p style='color:red;'>$error</p>"; 
    }?>
</main>
