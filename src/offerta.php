<?php
session_start();
date_default_timezone_set('Europe/Rome');

$host = "db"; $user = "myuser"; $pass = "mypassword"; $db = "myapp_db";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) { die("Connessione fallita."); }

// Protezione: solo studenti
if (!isset($_SESSION['userId']) || $_SESSION['ruolo'] !== 'STUDENTE') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId'];
    $titolo = mysqli_real_escape_string($conn, $_POST['titolo']);
    $genere = mysqli_real_escape_string($conn, $_POST['genere']);
    $copie  = intval($_POST['copie']);

    // Inserimento nella tabella NUOVI_LIBRI
    // Assumiamo che la tabella abbia: idProp (AI), userId, titolo, genere, copie
    $sql = "INSERT INTO NUOVI_LIBRI (userId, titolo, genere, copie) 
            VALUES ($userId, '$titolo', '$genere', $copie)";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard_studente.php?msg=success_offri");
    } else {
        die("Errore durante l'invio della proposta: " . mysqli_error($conn));
    }
}
?>