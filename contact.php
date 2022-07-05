<?php

//======================================================================
// Variables
//======================================================================


//E-mail address. Enter your email
define("__TO__", "");

//Success message
define('__SUCCESS_MESSAGE__', "Спасибо, Ваше сообщение отослано.Мы с Вами скоро свяжемся!");

//Error message
define('__ERROR_MESSAGE__', "Ваше сообщение не отослано. Попробуйте еще раз.");

//Messege when one or more fields are empty
define('__MESSAGE_EMPTY_FIELDS__', "Пожалуйста, заполните все поля");


//$smtp = [
//	"smtp_username"  => 'maugli_kir@mail.ru', // Адрес вашего почтового ящика
//	"smtp_port" 	 => '465', 		 // Порт работы
//	"smtp_host" 	 => ''ssl://smtp.mail.ru', 		 // Сервер для отправки почты
//	"smtp_password"  => 'vambam24', 	 // Пароль от почтового ящика
//	"smtp_debug" 	 => true,		 // Отображение ошибок
//	"smtp_charset" 	 => 'utf-8', 		 // Кодировка сообщений
//	"smtp_from" 	 => 'Apart Pulkovo' 		 // Ваше имя Вашего сайта
//	];
//	
$smtp = [
	"smtp_username"  => 's_v_alex@yahoo.com', // Адрес вашего почтового ящика
	"smtp_port" 	 => '465', 		 // Порт работы
	"smtp_host" 	 => 'ssl://smtp.mail.yahoo.com', 		 // Сервер для отправки почты
	"smtp_password"  => 'my_box58', 	 // Пароль от почтового ящика
	"smtp_debug" 	 => true,		 // Отображение ошибок
	"smtp_charset" 	 => 'utf-8', 		 // Кодировка сообщений
	"smtp_from" 	 => 'Apart Pulkovo' 		 // Ваше имя Вашего сайта
	];	
//======================================================================
// Do not change
//======================================================================

//E-mail validation
function check_email($email){
    if(!@eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)){
        return false;
    } else {
        return true;
    }
}


function smtpmail($to='', $mail_to, $subject, $message, $headers='') {
	global $smtp;
	$SEND =	"Date: ".date("D, d M Y H:i:s") . " UT\r\n";
	$SEND .= 'Subject: =?'.$smtp['smtp_charset'].'?B?'.base64_encode($subject)."=?=\r\n";
	if ($headers) $SEND .= $headers."\r\n\r\n";
	else
	{
		$SEND .= "Reply-To: ".$smtp['smtp_username']."\r\n";
		$SEND .= "To: \"=?".$smtp['smtp_charset']."?B?".base64_encode($to)."=?=\" <$mail_to>\r\n";
		$SEND .= "MIME-Version: 1.0\r\n";
		$SEND .= "Content-Type: text/html; charset=\"".$smtp['smtp_charset']."\"\r\n";
		$SEND .= "Content-Transfer-Encoding: 8bit\r\n";
		$SEND .= "From: \"=?".$smtp['smtp_charset']."?B?".base64_encode($smtp['smtp_from'])."=?=\" <".$smtp['smtp_username'].">\r\n";
		$SEND .= "X-Priority: 3\r\n\r\n";
	}
	$SEND .=  $message."\r\n";
	if( !$socket = fsockopen($smtp['smtp_host'], $smtp['smtp_port'], $errno, $errstr, 30) ) {
		if ($smtp['smtp_debug']) echo $errno."<br>".$errstr;
		return false;
	}

	if (!server_parse($socket, "220", __LINE__)) return false;

	fputs($socket, "HELO " . $smtp['smtp_host'] . "\r\n");
	if (!server_parse($socket, "250", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не могу отправить HELO!</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, "AUTH LOGIN\r\n");
	if (!server_parse($socket, "334", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не могу найти ответ на запрос авторизаци.</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, base64_encode($smtp['smtp_username']) . "\r\n");
	if (!server_parse($socket, "334", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Логин авторизации не был принят сервером!</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, base64_encode($smtp['smtp_password']) . "\r\n");
	if (!server_parse($socket, "235", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Пароль не был принят сервером как верный! Ошибка авторизации!</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, "MAIL FROM: <".$smtp['smtp_username'].">\r\n");
	if (!server_parse($socket, "250", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не могу отправить комманду MAIL FROM: </p>';
		fclose($socket);
		return false;
	}
	fputs($socket, "RCPT TO: <" . $mail_to . ">\r\n");

	if (!server_parse($socket, "250", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не могу отправить комманду RCPT TO: </p>';
		fclose($socket);
		return false;
	}
	fputs($socket, "DATA\r\n");

	if (!server_parse($socket, "354", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не могу отправить комманду DATA</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, $SEND."\r\n.\r\n");

	if (!server_parse($socket, "250", __LINE__)) {
		if ($smtp['smtp_debug']) echo '<p>Не смог отправить тело письма. Письмо не было отправленно!</p>';
		fclose($socket);
		return false;
	}
	fputs($socket, "QUIT\r\n");
	fclose($socket);
	return TRUE;
}

function server_parse($socket, $response, $line = __LINE__) {
	global $smtp;
	while (@substr($server_response, 3, 1) != ' ') {
		if (!($server_response = fgets($socket, 256))) {
			if ($smtp['smtp_debug']) echo "<p>Проблемы с отправкой почты!</p>$response<br>$line<br>";
			return false;
		}
	}
	if (!(substr($server_response, 0, 3) == $response)) {
		if ($smtp['smtp_debug']) echo "<p>Проблемы с отправкой почты!</p>$response<br>$line<br>";
		return false;
	}
	return true;
}



//Get data form and send mail
///if(isset($_POST['name']) and isset($_POST['email']) and isset($_POST['date_in']) and isset($_POST['date_out']///) and isset($_POST['message'])){
///$_POST['name'] = 'serg';$_POST['mail'] = 'ser@dd.com'; $_POST['message'] = 'dsdgs';
	if(isset($_POST['name']) and isset($_POST['mail'])  and isset($_POST['message'])){
	$name = $_POST['name'];
    $mail = $_POST['mail'];
	$date_in =$_POST['date_in'];
	$date_out =$_POST['date_out'];
    $hero = $_POST['hero'];
    $messageForm = $_POST['message'];
 echo $_POST['name'];


    if($name == '') {
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите Ваше имя."));
        exit();
//    } else if($mail == '' or check_email($mail) == false){
//        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите верный e-mail."));
//        exit();
//    } else if($mail == '' or check_email($mail) == false){
    } else if($mail == '' ){
    	echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите верный e-mail."));
        exit();
    } else if($messageForm == ''){
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите Ваше сообщение."));
        exit();
    } else {
        $to = __TO__;
		
        $subject = 'Letter from ' . $name;
        $message = '
        <html>
        <head>
          <title>Mail from '. $name .'</title>
        </head>
        <body>
          <table style="width: 500px; font-family: arial; font-size: 14px;" border="1">
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Name:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $name .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Дата заезда:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $date_in .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Дата выезда:</th>
             <td align="left" style="padding-left:5px; line-height: 20px;">'. $date_out .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">E-mail:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $mail .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Кол-во гостей:</th>
             <td align="left" style="padding-left:5px; line-height: 20px;">'. $hero .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Message:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $messageForm  .'</td>
            </tr>
          </table>
        </body>
        </html>
        ';

//        $headers  = 'MIME-Version: 1.0' . "\r\n";
//        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
 //       $headers .= 'From: ' . $mail . "\r\n";

 /////       send_mail($to,$subject,$message,$headers);
 echo $message;
    	$mail_to = "a_ermilov@mail.ru";
 //   	$message = "test of message";
             smtpmail($to, $mail_to, $subject, $message, $headers);
    }
} else {
    echo json_encode(array('info' => 'error', 'msg' => __MESSAGE_EMPTY_FIELDS__));
}
 ?>