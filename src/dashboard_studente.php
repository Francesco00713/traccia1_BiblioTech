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

$res_utente = mysqli_query($conn, "SELECT sospensione FROM UTENTI WHERE userId = $userId");
$utente = mysqli_fetch_assoc($res_utente);
$oggi = date("Y-m-d");
$sospeso = ($utente && $utente['sospensione'] && $utente['sospensione'] > $oggi);

$res_conta = mysqli_query($conn, "SELECT COUNT(*) as tot FROM PRESTITI WHERE userId = $userId AND dRestituzione IS NULL");
$prestiti_attivi = mysqli_fetch_assoc($res_conta)['tot'] ?? 0;
$puo_noleggiare = ($prestiti_attivi < 3 && !$sospeso);

$motivo_blocco = "";
if ($sospeso) {
    $motivo_blocco = "Il tuo account è sospeso fino al " . date("d/m/Y", strtotime($utente['sospensione'])) . ".";
} elseif ($prestiti_attivi >= 3) {
    $motivo_blocco = "Hai raggiunto il limite massimo di 3 prestiti contemporanei. Restituisci un libro per richiederne uno nuovo.";
}

$catalogo = mysqli_query($conn, "SELECT * FROM LIBRI");

$miei_libri = mysqli_query($conn, "
    SELECT p.codP, l.titolo, p.dScadenza 
    FROM PRESTITI p 
    JOIN LIBRI l ON p.id = l.id 
    WHERE p.userId = $userId AND p.dRestituzione IS NULL
");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiblioTech - Dashboard Studente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="static/css/style.css">
    <style>
        body { background-color: #f4f7f6; }
        .main-container { margin-top: 50px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .status-badge { font-size: 0.9rem; padding: 5px 12px; border-radius: 20px; }
        .card-loan { border-left: 4px solid #ffc107; }
    </style>
</head>
<body>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <div>
            <h1 class="h3 mb-0">BiblioTech Studente</h1>
            <p class="text-muted mb-0">Ciao, <strong><?php echo htmlspecialchars($_SESSION['nome']); ?></strong></p>
        </div>
        <div class="text-end">
            <span class="status-badge <?php echo $sospeso ? 'bg-danger text-white' : 'bg-success text-white'; ?>">
                Stato: <?php echo $sospeso ? 'Sospeso' : 'Attivo'; ?>
            </span>
            <a href="logout.php" class="btn btn-outline-secondary btn-sm ms-2">Logout</a>
        </div>
    </div>

    <?php if (!$puo_noleggiare): ?>
        <div class="alert alert-warning shadow-sm border-start border-warning border-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Attenzione:</strong> <?php echo $motivo_blocco; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                if($_GET['msg'] == 'success') echo "Richiesta di prestito completata!";
                if($_GET['msg'] == 'notificato') echo "Restituzione notificata al bibliotecario!";
                // AGGIUNTO: Messaggio per la proposta di donazione
                if($_GET['msg'] == 'success_offri') echo "Proposta inviata! Il libro sarà aggiunto dopo la verifica del bibliotecario.";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-dark text-white">Libri da riconsegnare (<?php echo $prestiti_attivi; ?>/3)</div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <?php if (mysqli_num_rows($miei_libri) > 0): ?>
                            <?php while($m = mysqli_fetch_assoc($miei_libri)): ?>
                                <li class="list-group-item p-3 card-loan">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($m['titolo']); ?></h6>
                                            <small class="text-muted">Scadenza: <?php echo date("d/m/Y", strtotime($m['dScadenza'])); ?></small>
                                        </div>
                                        <a href="prestito.php?action=notifica_restituzione&codP=<?php echo $m['codP']; ?>" 
                                           class="btn btn-sm btn-warning" onclick="return confirm('Confermi di voler consegnare il libro al banco del bibliotecario?')">
                                           Riconsegna
                                        </a>
                                    </div>
                                </li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li class="list-group-item text-muted text-center py-4">Nessun prestito attivo.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">Offri un libro</div>
                <div class="card-body">
                    <form action="offerta.php" method="POST">
                        <input type="text" name="titolo" class="form-control mb-2" placeholder="Titolo" required>
                        <input type="text" name="genere" class="form-control mb-2" placeholder="Genere" required>
                        <input type="number" name="copie" class="form-control mb-2" placeholder="Numero copie" min="1" required>
                        <button type="submit" class="btn btn-success w-100 btn-sm">Invia Proposta</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <h4 class="mb-3">Catalogo Libri</h4>
            <div class="row">
                <?php while($libro = mysqli_fetch_assoc($catalogo)): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title h6"><?php echo htmlspecialchars($libro['titolo']); ?></h5>
                                <p class="card-text small text-muted mb-2">Disponibili: <strong><?php echo $libro['cDisponibili']; ?></strong></p>
                                <?php if ($puo_noleggiare && $libro['cDisponibili'] > 0): ?>
                                    <a href="prestito.php?action=noleggia&id=<?php echo $libro['id']; ?>" 
                                       class="btn btn-primary btn-sm w-100">Richiedi Prestito</a>
                                <?php else: ?>
                                    <button class="btn btn-light btn-sm w-100 text-muted" disabled>Non disponibile</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>