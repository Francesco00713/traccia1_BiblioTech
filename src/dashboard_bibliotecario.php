<?php
session_start();
date_default_timezone_set('Europe/Rome');

$host = "db"; $user = "myuser"; $pass = "mypassword"; $db = "myapp_db";
$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) { die("Connessione fallita."); }
if (!isset($_SESSION['userId']) || $_SESSION['ruolo'] !== 'BIBLIOTECARIO') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'accetta_offerta' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $res_prop = mysqli_query($conn, "SELECT * FROM NUOVI_LIBRI WHERE id = $id");
    $prop = mysqli_fetch_assoc($res_prop);

    if ($prop) {
        $titolo = mysqli_real_escape_string($conn, $prop['titolo']);
        $genere = mysqli_real_escape_string($conn, $prop['genere']);
        $copie  = intval($prop['copie']);

        mysqli_begin_transaction($conn);
        try {
            $check = mysqli_query($conn, "SELECT id FROM LIBRI WHERE titolo = '$titolo'");
            if (mysqli_num_rows($check) > 0) {
                mysqli_query($conn, "UPDATE LIBRI SET cTotali = cTotali + $copie, cDisponibili = cDisponibili + $copie WHERE titolo = '$titolo'");
            } else {
                mysqli_query($conn, "INSERT INTO LIBRI (titolo, genere, cTotali, cDisponibili) VALUES ('$titolo', '$genere', $copie, $copie)");
            }
            mysqli_query($conn, "DELETE FROM NUOVI_LIBRI WHERE id = $id");
            
            mysqli_commit($conn);
            header("Location: dashboard_bibliotecario.php?msg=offerta_accettata");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
        }
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'convalida' && isset($_GET['codP'])) {
    $codP = intval($_GET['codP']);
    $res_p = mysqli_query($conn, "SELECT id FROM PRESTITI WHERE codP = $codP");
    $prestito = mysqli_fetch_assoc($res_p);

    if ($prestito) {
        $idLibro = $prestito['id'];
        mysqli_begin_transaction($conn);
        try {
            mysqli_query($conn, "UPDATE LIBRI SET cDisponibili = cDisponibili + 1 WHERE id = $idLibro");
            mysqli_query($conn, "DELETE FROM EFFETTUA WHERE codP = $codP");
            mysqli_query($conn, "DELETE FROM PRESTITI WHERE codP = $codP");
            
            mysqli_commit($conn);
            header("Location: dashboard_bibliotecario.php?msg=convalidato");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
        }
    }
}

$res_patrimonio = mysqli_query($conn, "SELECT SUM(cTotali) as tot FROM LIBRI");
$somma_totale = mysqli_fetch_assoc($res_patrimonio)['tot'] ?? 0;

$res_scaffale = mysqli_query($conn, "SELECT SUM(cDisponibili) as disp FROM LIBRI");
$somma_disponibili = mysqli_fetch_assoc($res_scaffale)['disp'] ?? 0;

$proposte_attesa = mysqli_query($conn, "SELECT n.*, u.nome FROM NUOVI_LIBRI n JOIN UTENTI u ON n.userId = u.userId");

$restituzioni_pendenti = mysqli_query($conn, "
    SELECT p.codP, l.titolo, u.nome, p.dRestituzione 
    FROM PRESTITI p 
    JOIN LIBRI l ON p.id = l.id 
    JOIN UTENTI u ON p.userId = u.userId 
    WHERE p.dRestituzione IS NOT NULL
");

$catalogo = mysqli_query($conn, "SELECT * FROM LIBRI");
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>BiblioTech - Dashboard Bibliotecario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .main-container { margin-top: 50px; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .stat-card { border-radius: 12px; padding: 20px; color: white; margin-bottom: 20px; }
        .bg-patrimonio { background-color: #4e73df; }
        .bg-disponibili { background-color: #1cc88a; }
        .card-loan { border-left: 4px solid #ffc107; }
        .card-new-book { border-left: 4px solid #007bff; }
    </style>
</head>
<body>

<div class="container main-container">
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h1 class="h3">BiblioTech Bibliotecario</h1>
        <a href="logout.php" class="btn btn-outline-secondary btn-sm">Logout</a>
    </div>

    <div class="row mb-4">
        <div class="col-md-6"><div class="stat-card bg-patrimonio">Patrimonio: <?php echo (int)$somma_totale; ?></div></div>
        <div class="col-md-6"><div class="stat-card bg-disponibili">Disponibili: <?php echo (int)$somma_disponibili; ?></div></div>
    </div>

    <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success">Operazione completata con successo!</div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-5">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-dark text-white fw-bold">Restituzioni da Convalidare</div>
                <ul class="list-group list-group-flush">
                    <?php while($r = mysqli_fetch_assoc($restituzioni_pendenti)): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div><?php echo htmlspecialchars($r['titolo']); ?><br><small><?php echo htmlspecialchars($r['nome']); ?></small></div>
                            <a href="dashboard_bibliotecario.php?action=convalida&codP=<?php echo $r['codP']; ?>" class="btn btn-sm btn-success">OK</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">Libri Offerti dagli Studenti</div>
                <ul class="list-group list-group-flush">
                    <?php if (mysqli_num_rows($proposte_attesa) > 0): ?>
                        <?php while($p = mysqli_fetch_assoc($proposte_attesa)): ?>
                            <li class="list-group-item card-new-book">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?php echo htmlspecialchars($p['titolo']); ?></strong> (x<?php echo $p['copie']; ?>)<br>
                                        <small>Da: <?php echo htmlspecialchars($p['nome']); ?></small>
                                    </div>
                                    <a href="dashboard_bibliotecario.php?action=accetta_offerta&id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary">Accetta</a>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-muted text-center py-3">Nessuna proposta.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-7">
            <h4 class="mb-3">Inventario</h4>
            <div class="row">
                <?php while($libro = mysqli_fetch_assoc($catalogo)): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-1"><?php echo htmlspecialchars($libro['titolo']); ?></h6>
                                <small class="text-muted">Disp: <?php echo $libro['cDisponibili']; ?> / <?php echo $libro['cTotali']; ?></small>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>