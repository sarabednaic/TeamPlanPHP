<?php
//otvaranje korisničke sesije
session_start();

//funkcija za hashiranje lozinke sa soli kao ona u .NET-u (koristeći SHA256 i Base64)
function sazimanje($lozinka, $sol) {
    //spajanje lozinke sa soli
    $lozinkaSol = $lozinka . $sol;

    //hashiranje
    $hashiranje = hash('sha256', $lozinkaSol, true);

    //usporedba lozinke korisnika sa dobivenog hashiranom lozinkom sa soli
    return base64_encode($hashiranje);
}

//varijabla za error, ako dođe do pogreške
$error = null;

//ako je forma submitana
if (isset($_POST['login'])) {
    //ucitati XML dokument i pretvoriti sadrzaj u objekte admin
    $xml = simplexml_load_file("C:\\admini.xml");
    //postavljanje podataka iz textboxeva u varijable
    $username = $_POST["username"];
    $password = $_POST["password"];
    //varijabla za uspjesnu prijavu
    $prijavaUspjesna = false;

    //iteracija kroz sve admine
    foreach ($xml->Admin as $admin) {
        //ako se korisničko ime podudara
        if ((string)$admin->KorisnickoIme === $username) {
            //postavi sol iz XML-a u varijablu
            $salt = (string)$admin->Sol;
            //odredi hshiranu lozinku
            $hashiranaLozinka = sazimanje($password, $salt);

            //ako se hash lozinke podudaraju
            if ($hashiranaLozinka === (string)$admin->Lozinka) {
                //prijava uspješna i sprema korisnika u sesiju
                $_SESSION["username"] = $username;
                $_SESSION["name"] = (string)$admin->Ime;
                $_SESSION["surname"] = (string)$admin->Prezime;
                //header funkcija koja preusmjerava korisnika na dashboard.php
                header("Location: dashboard.php"); 
                //izlazi iz PHP koda
                exit();
            }
        }
    }

    //ako prijava nije uspješna, prikaži grešku
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
</body>
</html>
