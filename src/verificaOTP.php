<?php
session_start();

if (!isset($_SESSION['temp_login'])) {
    header("Location: login.php");
    exit();
}

$host = "db"; $user = "myuser"; $pass = "mypassword"; $db = "myapp_db";
$conn = mysqli_connect($host, $user, $pass, $db);

$errore = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_inserito = $_POST['otp'];
    $dati = $_SESSION['temp_login'];

    if ($otp_inserito == $dati['otp'] && date("Y-m-d H:i:s") <= $dati['scadenza_otp']) {
        
        $userId = $dati['userId'];
        $inizio = date("Y-m-d H:i:s");
        $scadenza = date("Y-m-d H:i:s", strtotime("+1 hour"));
        $otp = $dati['otp'];
        $scadenzaOTP = $dati['scadenza_otp'];

        $sql = "INSERT INTO Sessioni (userId, inizio, scadenza, OTP, scadenzaOTP) 
                VALUES ('$userId', '$inizio', '$scadenza', '$otp', '$scadenzaOTP')";
        
        if (mysqli_query($conn, $sql)) {
            $_SESSION['userId'] = $userId;
            $_SESSION['nome'] = $dati['nome'];
            $_SESSION['ruolo'] = $dati['ruolo']; 
            $_SESSION['email'] = $dati['email'];
            $_SESSION['otp_verificato'] = true;
            unset($_SESSION['temp_login']);
            header("Location: dashboard.php");
            exit();
        }
    } else {
        $errore = "Codice errato o scaduto!";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Verifica OTP - BiblioTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/style.css">
</head>
<body>
    <div class="content">
        <div class="signup-card">
            <h3>Verifica Identità</h3>
            <p>Inserisci il codice inviato alla tua email</p>
            
            <?php if ($errore) echo "<div class='alert alert-danger'>$errore</div>"; ?>

            <form action="verificaOTP.php" method="POST">
                <input type="text" name="otp" placeholder="Codice OTP" class="form-control" required maxlength="6">
                <button type="submit" class="btn-custom w-100">VERIFICA</button>
            </form>
        </div>
    </div>
</body>
</html>