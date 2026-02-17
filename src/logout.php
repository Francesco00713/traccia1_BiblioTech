<?php
session_start();
date_default_timezone_set('Europe/Rome');
$host = "db"; 
$user = "myuser"; 
$pass = "mypassword"; 
$db   = "myapp_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (isset($_SESSION['sessionId']) && $conn) {
    $sessionId = $_SESSION['sessionId'];
    $data_logout = date("Y-m-d H:i:s");
    $sql_update = "UPDATE SESSIONI SET logout = '$data_logout' WHERE sessionId = '$sessionId'";
    mysqli_query($conn, $sql_update);
}

$_SESSION = array();
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();
header("Location: index.php");
exit();
?>