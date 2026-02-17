<?php
session_start();
date_default_timezone_set('Europe/Rome');
$host = "db"; 
$user = "myuser"; 
$pass = "mypassword"; 
$db   = "myapp_db";
$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
    die("Connessione fallita: " . mysqli_connect_error());
}

if (!isset($_SESSION['userId']) || $_SESSION['ruolo'] !== 'STUDENTE') {
    header("Location: login.php");
    exit();
}
$userId = $_SESSION['userId'];
$action = $_GET['action'] ?? '';
$libroId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($action === 'noleggia' && $libroId > 0) {
    $res = mysqli_query($conn, "SELECT cDisponibili FROM LIBRI WHERE id = $libroId");
    $libro = mysqli_fetch_assoc($res);

    if ($libro && $libro['cDisponibili'] > 0) {
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "UPDATE LIBRI SET cDisponibili = cDisponibili - 1 WHERE id = $libroId");
            
            $dPrestito = date("Y-m-d H:i:s");
            $dScadenza = date("Y-m-d H:i:s", strtotime("+1 month"));
            
            $sql_p = "INSERT INTO PRESTITI (userId, id, dPrestito, dScadenza) VALUES ($userId, $libroId, '$dPrestito', '$dScadenza')";
            mysqli_query($conn, $sql_p);
            
            $codP = mysqli_insert_id($conn);
            mysqli_query($conn, "INSERT INTO EFFETTUA (userId, codP) VALUES ($userId, $codP)");

            mysqli_commit($conn);
            header("Location: dashboard_studente.php?msg=success");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
        }
    }
}

if ($action === 'notifica_restituzione' && isset($_GET['codP'])) {
    $codP = intval($_GET['codP']);
    $data_restituzione = date("Y-m-d H:i:s");

    $check_p = mysqli_query($conn, "SELECT codP FROM PRESTITI WHERE codP = $codP AND userId = $userId");
    
    if (mysqli_num_rows($check_p) > 0) {
        $sql_upd = "UPDATE PRESTITI SET dRestituzione = '$data_restituzione' WHERE codP = $codP";
        
        if (mysqli_query($conn, $sql_upd)) {
            header("Location: dashboard_studente.php?msg=notificato");
            exit();
        }
    }
}

if ($action === 'offri' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = mysqli_real_escape_string($conn, $_POST['titolo']);
    $genere = mysqli_real_escape_string($conn, $_POST['genere']);
    $copie  = intval($_POST['copie']);

    $sql_offri = "INSERT INTO LIBRI (titolo, genere, cTotali, cDisponibili) VALUES ('$titolo', '$genere', 0, 0)";
    if (mysqli_query($conn, $sql_offri)) {
        header("Location: dashboard_studente.php?msg=offerto");
        exit();
    }
}
header("Location: dashboard_studente.php?msg=error");
exit();