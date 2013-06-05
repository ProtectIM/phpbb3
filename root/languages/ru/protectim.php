<?php

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = array();
}

$lang = array_merge($lang, array(
	'CAPTCHA_PROTECTIM'                 => 'Авторизация по СМС',

	'PROTECTIM_SESSION'                 => 'Номер сессии',
	'PROTECTIM_CODE'                    => 'Код авторизации',
	'PROTECTIM_CODE_EXPLAIN'            => 'Выслан Вам на мобильный номер',

	'PROTECTIM_ERROR_UNKNOWN'           => 'Ошибка службы СМС-авторизации',

	'PROTECTIM_ERROR_CONNECTION'        => 'Ошибка связи с сервисом СМС-авторизации',
	'PROTECTIM_ERROR_SESSION_INCORRECT' => 'Неверный номер сессии',
	'PROTECTIM_ERROR_CODE_EMPTY'        => 'Вы должны указать код авторизации',
	'PROTECTIM_ERROR_CODE_INCORRECT'    => 'Неверный код авторизации',
));

