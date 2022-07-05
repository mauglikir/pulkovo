<?php 

//======================================================================
// Variables
//======================================================================


//E-mail address. Enter your email
define("__TO__", "info@2pistons.ru");

//Success message
define('__SUCCESS_MESSAGE__', "Спасибо, Ваше сообщение отослано.Мы с Вами скоро свяжемся!");

//Error message 
define('__ERROR_MESSAGE__', "Ваше сообщение не отослано. Попробуйте еще раз.");

//Messege when one or more fields are empty
define('__MESSAGE_EMPTY_FIELDS__', "Пожалуйста, заполните все поля");

$config['smtp_username'] = 'info@2pistons.ru'; // smtp username
$config['smtp_port'] = '465'; // smtp port
$config['smtp_host'] = 'ssl://smtp.2pistons.ru'; // smtp server
$config['smtp_password'] = '1MvMaFJb'; // password
$config['smtp_charset'] = 'utf-8'; // encoding, usually utf-8
$config['smtp_from'] = 'Apart Pulkovo'; // sender name 
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

//Send mail
function send_mail($to,$subject,$message,$headers){
    if(@mail($to,$subject,$message,$headers)){
        echo json_encode(array('info' => 'success', 'msg' => __SUCCESS_MESSAGE__));
    } else {
        echo json_encode(array('info' => 'error', 'msg' => __ERROR_MESSAGE__));
    }
}

function smtpmail($to='', $mail_to, $subject, $message, $headers='') {
	global $config;
	$SEND =	"Date: ".date("D, d M Y H:i:s") . " UT\r\n";
	$SEND .= 'Subject: =?'.$config['smtp_charset'].'?B?'.base64_encode($subject)."=?=\r\n";
	if ($headers) $SEND .= $headers."\r\n\r\n";
	else
	{
		$SEND .= "Reply-To: ".$config['smtp_username']."\r\n";
		$SEND .= "To: \"=?".$config['smtp_charset']."?B?".base64_encode($to)."=?=\" <$mail_to>\r\n";
		$SEND .= "MIME-Version: 1.0\r\n";
		$SEND .= "Content-Type: text/html; charset=\"".$config['smtp_charset']."\"\r\n";
		$SEND .= "Content-Transfer-Encoding: 8bit\r\n";
		$SEND .= "From: \"=?".$config['smtp_charset']."?B?".base64_encode($config['smtp_from'])."=?=\" <".$config['smtp_username'].">\r\n";
		$SEND .= "X-Priority: 3\r\n\r\n";
	}
	$SEND .=  $message."\r\n";

	if (!$socket = fsockopen($config['smtp_host'], $config['smtp_port'], $errno, $errstr, 30) ) {
		return array(false, $errno.": ".$errstr);
	}
 
	if (!server_parse($socket, "220", __LINE__)) return array(false, '');
 
	fputs($socket, "HELO " . $config['smtp_host'] . "\r\n");
	if (!server_parse($socket, "250", __LINE__)) {
		return array(false, 'Cannot sent HELLO!');
		fclose($socket);
	}
	fputs($socket, "AUTH LOGIN\r\n");
	if (!server_parse($socket, "334", __LINE__)) {
		return array(false, 'No answer for authorization');
		fclose($socket);
	}
	fputs($socket, base64_encode($config['smtp_username']) . "\r\n");
	if (!server_parse($socket, "334", __LINE__)) {
		return array(false, 'Login error');
		fclose($socket);
	}
	fputs($socket, base64_encode($config['smtp_password']) . "\r\n");
	if (!server_parse($socket, "235", __LINE__)) {
		return array(false, 'Password error');
		fclose($socket);
	}
	fputs($socket, "MAIL FROM: <".$config['smtp_username'].">\r\n");
	if (!server_parse($socket, "250", __LINE__)) {
		return array(false, 'Can\'t send MAIL FROM');
		fclose($socket);
	}
	fputs($socket, "RCPT TO: <" . $mail_to . ">\r\n");
 
	if (!server_parse($socket, "250", __LINE__)) {
		return array(false, 'Can\'t send RCPT TO');
		fclose($socket);
	}
	fputs($socket, "DATA\r\n");
 
	if (!server_parse($socket, "354", __LINE__)) {
		return array(false, 'Can\'t send DATA');
		fclose($socket);
	}
	fputs($socket, $SEND."\r\n.\r\n");
 
	if (!server_parse($socket, "250", __LINE__)) {
		return array(false, 'Can\'t send message body');
		fclose($socket);
	}

	fputs($socket, "QUIT\r\n");
	fclose($socket);
	return array(true, '');
}

function server_parse($socket, $response, $line = __LINE__) {
	global $config;
	while (@substr($server_response, 3, 1) != ' ') {
		if (!($server_response = fgets($socket, 256))) {
			return array(false, 'Error sending: '.$response.', '.$line);
		}
	}
	if (!(substr($server_response, 0, 3) == $response)) {
		return array(false, 'Error sending: '.$response.', '.$line);
	}
	return true;
}



//Get data form and send mail
if(isset($_POST['name']) and isset($_POST['mail']) and isset($_POST['date_in']) and isset($_POST['date_out']) and isset($_POST['messageForm'])){
    $name = $_POST['name'];
    $mail = $_POST['mail'];
	$date_in =$_POST['date_in'];
	$date_out =$_POST['date_out'];
    $subjectForm = $_POST['subjectForm'];
    $messageForm = $_POST['messageForm'];

    if($name == '') {
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите Ваше имя."));
        exit();
    } else if($mail == '' or check_email($mail) == false){
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите верный e-mail."));
        exit();
    } else if($date_in == '' ){
        echo json_encode(array('info' => 'error', 'msg' => "Выберите дату заезда."));
        exit();
    } else if($date_out == '' ){
        echo json_encode(array('info' => 'error', 'msg' => "Выберите дату выезда."));
        exit();
    } else if($mail == '' or check_email($mail) == false){
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите верный e-mail."));
        exit();
    } else if($messageForm == ''){
        echo json_encode(array('info' => 'error', 'msg' => "Пожалуйста, введите Ваше сообщение."));
        exit();
    } else {
        $to = __TO__;
        $subject = $subjectForm . ' ' . $name;
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
              <th align="right" style="width:150px; padding-right:5px;">Subject:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $subjectForm .'</td>
            </tr>
            <tr style="height: 32px;">
              <th align="right" style="width:150px; padding-right:5px;">Message:</th>
              <td align="left" style="padding-left:5px; line-height: 20px;">'. $messageForm  .'</td>
            </tr>
          </table>
        </body>
        </html>
        ';

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
        $headers .= 'From: ' . $mail . "\r\n";

 /////       send_mail($to,$subject,$message,$headers);
             smtpmail($to, $mail_to, $subject, $message, $headers);
    }
} else {
    echo json_encode(array('info' => 'error', 'msg' => __MESSAGE_EMPTY_FIELDS__));
}
 ?>