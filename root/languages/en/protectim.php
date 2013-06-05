<?php

if (!defined('IN_PHPBB')) {
	exit;
}

if (empty($lang) || !is_array($lang)) {
	$lang = array();
}

$lang = array_merge($lang, array(
	'CAPTCHA_PROTECTIM'                 => 'Login by SMS',

	'PROTECTIM_SESSION'                 => 'Session',
	'PROTECTIM_CODE'                    => 'Code',
	'PROTECTIM_CODE_EXPLAIN'            => 'Sent to your phone',

	'PROTECTIM_ERROR_UNKNOWN'           => 'Authentication service internal error',

	'PROTECTIM_ERROR_CONNECTION'        => 'Can\'t connect to SMS authentication service',
	'PROTECTIM_ERROR_SESSION_INCORRECT' => 'Incorrect session',
	'PROTECTIM_ERROR_CODE_EMPTY'        => 'You should fill the code field',
	'PROTECTIM_ERROR_CODE_INCORRECT'    => 'Incorrect code',
));

