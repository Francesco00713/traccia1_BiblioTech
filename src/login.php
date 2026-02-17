<?php
session_start();

$host = "db"; 
$user = "myuser";
$pass = "mypassword";
$db   = "myapp_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password_inserita = $_POST['password'];

    $sql = "SELECT * FROM Utenti WHERE email = '$email'";
    $risultato = mysqli_query($conn, $sql);

    if (mysqli_num_rows($risultato) > 0) {
        $utente = mysqli_fetch_assoc($risultato);

        if (password_verify($password_inserita, $utente['passwordH'])) {
            $otp = rand(100000, 999999); 
            
            $oggetto = "Codice OTP BiblioTech";
            $messaggio = "Ciao " . $utente['nome'] . ", il tuo codice è: " . $otp;
            $headers = "From: noreply@bibliotech.it";

            if (mail($email, $oggetto, $messaggio, $headers)) {
                $_SESSION['temp_login'] = [
                    'userId' => $utente['userId'],
                    'email' => $utente['email'],
                    'nome' => $utente['nome'],
                    'ruolo' => $utente['ruolo'],
                    'otp' => $otp,
                    'scadenza_otp' => date("Y-m-d H:i:s", strtotime("+5 minutes"))
                ];
                
                header("Location: verificaOTP.php");
                exit();
            } else {
                $errore = "Errore invio email!";
            }
        } else {
            $errore = "Password errata!";
        }
    } else {
        $errore = "Email non trovata!";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BiblioTech - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/style.css">
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
            <h3>Bentornato!</h3>
            <p>Accedi per entrare in biblioteca</p>

            <?php if ($errore) echo "<div class='alert alert-danger'>$errore</div>"; ?>

            <form action="login.php" method="POST">
                <input type="email" name="email" placeholder="Email" class="form-control" required>
                <input type="password" name="password" placeholder="Password" class="form-control" required>
                
                <button type="submit" class="btn-custom w-100">ACCEDI</button>
            </form>
            
            <p class="mt-3">Nuovo studente? <a href="signup.php">Registrati ora</a></p>
        </div>
    </div>

</body>
</html>