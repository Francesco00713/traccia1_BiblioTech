<?php

echo "<br >";
$subject = "Test subject";
$msg = "Test body";
$headers = [];
$headers[] = "MIME-Version: 1.0";
$headers[] = "From: La mia app <noreply@test.it>";
$headers[] = "Reply-To: support@my.it";
$headers[] = "X-Mailer: PHP/".phpversion();

$success = mail("abc@def.it", $subject, $msg, implode("\r\n", $headers));

if($success){
    echo "OK!";
} else {
    echo "KO!";
}
?>