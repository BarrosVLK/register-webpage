<?php 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
function mandarEmail($nomeDestinatario,$To,$Subject,$Message) {

	require('./src/PHPMailer.php');
	require('./src/Exception.php');
	require('./src/SMTP.php');

	$mail = new PHPMailer;
	//$mail->SMTPDebug = 2;									// Enable verbose debug output
	$mail->isSMTP();										// Set mailer to use SMTP
	$mail->CharSet = 'UTF-8';
	$mail->Host = 'smtp.gmail.com';  		  				// Specify main and backup SMTP servers
	$mail->SMTPAuth = true;									// Enable SMTP authentication
	//$mail->SMTPSecure = 'tls';							// Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;										// TCP port to connect to
	$mail->Username = 'alunoweb2026barros';         	// SMTP username
	$mail->Password = 'sozc gegm wsfr tsfc';				// SMTP password
	$mail->From = 'alunoweb2026barros@gmail.com';
	$mail->FromName = 'Sistema do RM 25133!';
	if (gettype($To)=="array") {
		foreach ($To as $key => $value) {
			$mail->addAddress($value);  // Add a recipient
		}
	} else {
		$mail->addAddress($To);  // Add a recipient
	}
	$mail->isHTML(true);									// Set email format to HTML
	//$mail->addAttachment('/var/tmp/file.tar.gz');			// Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');	// Optional name

	$mail->Subject = $Subject;
	$mail->Body    = $Message;
	$mail->AltBody = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($Message), ENT_QUOTES | ENT_HTML5, 'UTF-8')));

	if(!$mail->send()) {
	    echo('Mensagem nao pode ser enviada: Mailer Error: ' . $mail->ErrorInfo);
	    return false;
	} else {
	    //echo("Email enviado para $nomeDestinatario, $Subject");
	    return true;
	}	
	//print_r($mail);
	//die();
} // termina aqui o mandarEmail

?>
