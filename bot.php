<?php

// Bot criado por @Nanx_NF

date_default_timezone_set ('America/Sao_Paulo'); // define timestamp padrão

// Incluindo arquivos nescessários
include __DIR__.'/Telegram.php';

if (!file_exists('dadosBot.ini')){

	echo "¡Instale el bot primero!";
	exit;

}

$textoMsg=json_decode (file_get_contents('textos.json'));
$iniParse=parse_ini_file('dadosBot.ini');

$ip=$iniParse ['ip'];
$token=$iniParse ['token'];
$limite=$iniParse ['limite'];

define ('TOKEN', $token); // token do bot criado no @botfather

// Instancia das classes
$tlg=new Telegram (TOKEN);
$redis=new Redis ();
$redis->connect ('localhost', 6379); //redis usando porta padrão

// BLOCO USADO EM LONG POLLING

while (true){

$updates=$tlg->getUpdates();

for ($i=0; $i < $tlg->UpdateCount(); $i++){

$tlg->serveUpdate($i);

switch ($tlg->Text ()){

	case '/start':

	$tlg->sendMessage ([
		'chat_id' => $tlg->ChatID (),
		'text' => $textoMsg->start,
		'parse_mode' => 'html',
		'reply_markup' => $tlg->buildInlineKeyBoard ([
			[$tlg->buildInlineKeyboardButton ('🇦🇷 SSH Gratis AR 🇦🇷', null, '/sshgratis')]
		])
	]);

	break;
	case '/sobre':

	$tlg->sendMessage ([
		'chat_id' => $tlg->ChatID (),
		'text' => 'Bot SSH Powered by; @Nanx_NF'
	]);

	break;
	case '/total':

	$tlg->sendMessage ([
		'chat_id' => $tlg->ChatID (),
		'text' => 'Fueron creadas <b>'.$redis->dbSize ().'</b> Cuentas en las ultimas 24hs',
		'parse_mode' => 'html'
	]);

	break;
	case '/sshgratis':

	$tlg->answerCallbackQuery ([
	'callback_query_id' => $tlg->Callback_ID()
	]);

	if ($redis->dbSize () == $limite){

		$textoSSH=$textoMsg->sshgratis->limite;

	} elseif ($redis->exists ($tlg->UserID ())){

		$textoSSH=$textoMsg->sshgratis->nao_criado;

	} else {

		$usuario=substr (str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);
		$senha=mt_rand(11111, 999999);

		exec ('./gerarusuario.sh '.$usuario.' '.$senha.' 1 1');

		$textoSSH="🇦🇷 Cuenta SSH creada!\r\n\r\n<b>Servidor:</b> <code>".$ip."</code>\r\n<b>Usuario:</b> <code>".$usuario."</code>\r\n<b>Contraseña:</b> <code>".$senha."</code>\r\n<b>Limite:</b> 1\r\n<b>Expira:</b> ".date ('d/m', strtotime('+1 day'))."\r\n\r\nSSH: 22| Dropbear: 443, 80| Socks: 8080\r\nSSL: 444| Proxy Publico: 442| Squid: 3128\r\n\r\n By; @Nanx_NF";

		$redis->setex ($tlg->UserID (), 43200, 'true'); //define registro para ser guardado por 12h

	}

	$tlg->sendMessage ([
		'chat_id' => $tlg->ChatID (),
		'text' => $textoSSH,
		'parse_mode' => 'html'
	]);

	break;

}

}}
