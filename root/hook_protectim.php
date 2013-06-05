<?php

if (!defined('IN_PHPBB'))
{
	exit;
}

define('PROTECTIM_ENABLED', true);
define('PROTECTIM_SERVER', 'http://protect.im/auth.php');
define('PROTECTIM_PRKEY', '{{PRKEY}}');

define('PROTECTIM_FAIL',		0x0000);
define('PROTECTIM_OK',			0x0001);
define('PROTECTIM_RESET',		0x0002);
define('PROTECTIM_UNAVAILABLE',	0x0003);

class protectim
{
	private static $_session, $_code;

	public static function init()
	{
		global $user, $auth, $config;

		if (!PROTECTIM_ENABLED || !defined('ADMIN_START') || defined('IN_INSTALL'))
		{
			return;
		}

		if (!$auth->acl_get('a_') || !empty($user->data['session_admin']))
		{
			return;
		}

		$user->add_lang('mods/protectim');

		$config['max_login_attempts'] = 0;
		$config['ip_login_limit_max'] = 0;

		if (!isset($_POST['login']))
		{
			self::auth_get_session();
		}
		else
		{
			self::$_session	= request_var('protectim_session', '');
			self::$_code	= request_var('protectim_code', '');

			switch (true)
			{
				case !preg_match('/^\d{4}-\d{4}$/', self::$_session):
					self::auth_get_session();
					self::error('PROTECTIM_ERROR_SESSION_INCORRECT');

					break;

				case !self::$_code:
					self::error('PROTECTIM_ERROR_CODE_EMPTY');

					break;

				case !preg_match('/^\d{6}$/', self::$_code):
					self::error('PROTECTIM_ERROR_CODE_INCORRECT');

					break;

				default:
					self::auth_check_code();
			}
		}

		self::assign_vars();
	}

	private static function auth_get_session()
	{
		global $user;

		self::$_session	= '';
		self::$_code	= '';

		$vars = self::send(array(
			'mode'		=> 'init',
			'prkey'		=> PROTECTIM_PRKEY,
			'uid'		=> $user->data['user_id'],
		));

		if ($vars)
		{
			switch ($vars['status'])
			{
				case PROTECTIM_FAIL:
				default:
					self::error($vars['error'], !empty($vars['fatal']));
					break;

				case PROTECTIM_OK:
					self::$_session = $vars['session'];
					break;
			}
		}
		else
		{
			self::error('PROTECTIM_ERROR_CONNECTION', true);
		}
	}
	private static function auth_check_code()
	{
		global $user;

		$vars = self::send(array(
			'mode'		=> 'check',
			'prkey'		=> PROTECTIM_PRKEY,
			'uid'		=> $user->data['user_id'],
			'session'	=> self::$_session,
			'code'		=> self::$_code,
		));

		if ($vars)
		{
			switch ($vars['status'])
			{
				case PROTECTIM_FAIL:
				default:
					self::error($vars['error'], !empty($vars['fatal']));

					break;

				case PROTECTIM_OK:

					break;

				case PROTECTIM_RESET:
					self::$_session	= $vars['session'];
					self::$_code	= '';

					break;
			}
		}
		else
		{
			self::error('PROTECTIM_ERROR_CONNECTION', true);
		}
	}

	private static function assign_vars()
	{
		global $template;

		$template->assign_vars(array(
			'PROTECTIM_ENABLED'		=> PROTECTIM_ENABLED,

			'CAPTCHA_TEMPLATE'		=> 'protectim_form.html',
			'S_CONFIRM_CODE'		=> true,

			'PROTECTIM_SESSION'		=> self::$_session,
			'PROTECTIM_CODE'		=> self::$_code,
		));
	}
	private static function error($message, $fatal = false)
	{
		global $template, $user;

		if ($fatal)
		{
			trigger_error((isset($user->lang[$message]) ? $user->lang[$message] : $message));
		}

		if (isset($_POST['login']))
		{
			unset($_POST['login']);
		}

		$template->assign_vars(array(
			'PROTECTIM_ERROR'		=> isset($user->lang[$message]) ? $user->lang[$message] : $message,
		));
	}

	private static function request($url)
	{
		$data = false;

		if (in_array(strtolower(ini_get('allow_url_fopen')), array('1', 'on')))
		{
			$data = file_get_contents($url);
		}

		if (($data === false) && function_exists('curl_init'))
		{
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_FAILONERROR, true);
			curl_setopt($ch, CURLOPT_AUTOREFERER, true);
			curl_setopt($ch, CURLOPT_NOBODY, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

			$response = curl_exec($ch);

			if(curl_errno($ch) !== 0)
			{
				echo(curl_error($ch) . "<br />\n");
			}

			$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
			$data = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

			curl_close($ch);
		}

		if ($data === false)
		{
			$_url = parse_url($url);

			$errno = $errstr = '';

			if ($fp = fsockopen($_url['host'], 80, $errno, $errstr, 1))
			{
				stream_set_timeout($fp, 25);

				$path = $_url['path'];
				if (!strlen($path))
				{
					$path = '/';
				}
				if (strlen($_url['query']))
				{
					$path .= '?' . $_url['query'];
				}

				fwrite($fp, "GET {$path} HTTP/1.0\r\n");
				fwrite($fp, "Host: {$_url['host']}\r\n");
				fwrite($fp, "Connection: Close\r\n\r\n");

				$data = '';
				while (!feof($fp))
				{
					$chunk = fgets($fp, 1024);
					$data .= $chunk;
				}
				fclose ($fp);
			}
		}

		return $data;
	}
	private static function send($vars)
	{
		$response = self::request(PROTECTIM_SERVER . '?data=' . base64_encode(serialize($vars)));
echo $response;
		if ($response && $data = @unserialize(@base64_decode($response)))
		{
var_dump($data);
			return $data;
		}

		return false;
	}
}

$phpbb_hook->register('phpbb_user_session_handler', array('protectim', 'init'));

