<?php
$host = "db"; 
$user = "myuser";
$pass = "mypassword";
$db   = "myapp_db";

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

$errore = "";
$successo = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_chiara = $_POST['password'];
    $ruolo = $_POST['ruolo'];

    $password_sicura = password_hash($password_chiara, PASSWORD_DEFAULT);

    $sql_check = "SELECT userId FROM Utenti WHERE email = '$email'";
    $risultato_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($risultato_check) > 0) {
        $errore = "Email già usata, provane un'altra!";
    } else {
        $sql_insert = "INSERT INTO Utenti (nome, email, passwordH, ruolo, sospensione) VALUES ('$nome', '$email', '$password_sicura', '$ruolo', NULL)";
        if (mysqli_query($conn, $sql_insert)) {
            $successo = "Registrazione fatta! Ora puoi andare al login.";
        } else {
            $errore = "Errore nell'inserimento: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BiblioTech - Registrazione</title>
    <head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/style.css">
</head>
</head>
<body>
    <div class="logo-container">
        <a href="https://www.panettipitagora.edu.it/" target="_blank">
            <img src="static/images/logo_panetti-pitagora.png" alt="Logo" class="logo-image">
        </a>
    </div>
    <div class="content">
        <h1 class="title schoolbell-regular">
            <a href="index.php" style="text-decoration:none; color:inherit;">BiblioTech</a>
        </h1>
        <div class="signup-card">
            <h3>Crea il tuo Account</h3>
            <?php if ($errore) echo "<div class='alert alert-danger'>$errore</div>"; ?>
            <?php if ($successo) echo "<div class='alert alert-success'>$successo</div>"; ?>
            <form action="signup.php" method="POST">
                <input type="text" name="nome" placeholder="Nome" class="form-control" required maxlength="20">
                <input type="email" name="email" placeholder="Email" class="form-control" required maxlength="50">
                <input type="password" name="password" placeholder="Password" class="form-control" required>
                <select name="ruolo" class="form-select">
                    <option value="STUDENTE">Studente</option>
                    <option value="BIBLIOTECARIO">Bibliotecario</option>
                </select>
                <button type="submit" class="btn-custom w-100">REGISTRATI</button>
            </form>
            <p class="mt-3">Sei già registrato? <a href="login.php">Accedi</a></p>
        </div>
    </div>
</body>
</html>