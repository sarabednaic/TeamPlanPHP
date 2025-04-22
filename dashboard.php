<?php
//pokretanje sesije
session_start();
//ako u sesiji nije zabilježen username onda prebaciti natrag na login
if (isset($_SESSION["username"])==false) {
    header("Location: index.php");
    exit();
}

//ucitati XML dokument i pretvoriti sadrzaj u objekte zadatak
$xml = simplexml_load_file("Zadatak.xml");
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
        <h2>Dobrodošli, <?= $_SESSION["name"] . " " . $_SESSION["surname"]; ?></h2>
        <h3>Popis zadataka za odobravanje</h3>

        <table>
            <tr>
                <th>ID</th>
                <th>Naziv</th>
                <th>Opis</th>
                <th>Početak</th>
                <th>Kraj</th>
                <th>Akcije</th>
            </tr>
            <!--iteracija kroz XML file i pretvranja podataka u objekte zadatak te prikaz istih u tablici-->
            <?php foreach ($xml->Zadatak as $zadatak): ?>
            <tr>
                <td><?php echo $zadatak->ID; ?></td>
                <td><?php echo $zadatak->Naziv; ?></td>
                <td><?php echo $zadatak->Opis; ?></td>
                <td><?php echo $zadatak->Vrijeme_pocetak; ?></td>
                <td><?php echo $zadatak->Vrijeme_kraj; ?></td>
                <td>
                    <!--za odabrani zadatak koji se odobri povuci koji je zadatak u pitanju-->
                    <form action="odobri.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $zadatak->ID; ?>">
                        <button type="submit">Odobri</button>
                    </form>
                    <!--za odabrani zadatak koji se odbije povuci koji je zadatak u pitanju-->
                    <form action="odbi.php" method="post" style="display:inline;">
                        <input type="hidden" name="id" value="<?php echo $zadatak->ID; ?>">
                        <button type="submit">Odbij</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </main>
</body>
</html>
