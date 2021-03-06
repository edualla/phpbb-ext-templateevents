<?php
/**
* phpBB Extension - marttiphpbb templateevents
* @copyright (c) 2014 marttiphpbb <info@martti.be>
* @license GNU General Public License, version 2 (GPL-2.0)
*/

namespace marttiphpbb\templateevents\event;

use phpbb\auth\auth;
use phpbb\request\request;
use phpbb\template\twig\twig as template;
use phpbb\user;

/**
* @ignore
*/
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/* @var auth */
	protected $auth;

	/* @var request */
	protected $request;

	/* @var template */
	protected $template;

	/* @var user */
	protected $user;

	/* @var string */
	protected $phpbb_root_path;

	/* @var string */
	protected $php_ext;

	/**
	 * @param auth $auth
	 * @param request $request
	 * @param template $template
	 * @param user $user
	 * @param string $phpbb_root_path
	 * @param string $php_ext
	*/
	public function __construct(
		auth $auth,
		request $request,
		template $template,
		user $user,
		$phpbb_root_path,
		$php_ext
	)
	{
		$this->auth = $auth;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->phpbb_root_path = $phpbb_root_path;
		$this->php_ext = $php_ext;
	}

	static public function getSubscribedEvents()
	{
		return array(
			'core.user_setup'		=> 'core_user_setup',
			'core.page_footer'		=> 'core_page_footer',
			'core.append_sid'		=> 'core_append_sid',
			'core.adm_page_footer'	=> 'core_page_footer', // same function for board and acp
		);
	}

	public function core_user_setup($event)
	{
		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = array(
			'ext_name' => 'marttiphpbb/templateevents',
			'lang_set' => 'common',
		);
		$event['lang_set_ext'] = $lang_set_ext;
	}

	public function core_page_footer($event)
	{
		if ($this->auth->acl_get('a_'))
		{

			$page_name = $this->user->page['page_name'];
			$query_string = $this->user->page['query_string'];

			$query_string = str_replace(array('&templateevents=1', '&templateevents=0'), '', $query_string);
			$query_string = str_replace(array('templateevents=1', 'templateevents=0'), '', $query_string);
			$query_string = trim($query_string, '&');

			$templateevents = ($this->request->variable('templateevents', 0)) ? true : false;

			if ($templateevents)
			{
				$query_string .= ($query_string) ? '&' : '';
				$query_string .= 'templateevents=0';

				$this->template->assign_var('U_TEMPLATEEVENTS_HIDE', append_sid($page_name, $query_string));
				$this->template->assign_var('S_TEMPLATEEVENTS', 1);
			}
			else
			{
				$query_string .= ($query_string) ? '&' : '';
				$query_string .= 'templateevents=1';

				$this->template->assign_var('U_TEMPLATEEVENTS_SHOW', append_sid($page_name, $query_string));
			}
		}
	}

	public function core_append_sid($event)
	{
		$params = $event['params'];

		if (is_string($params))
		{
			if (strpos($params, 'templateevents=0') !== false)
			{
				return;
			}
		}

		if ($this->request->variable('templateevents', 0)
			&& $this->auth->acl_get('a_'))
		{
			if (is_string($params))
			{
				if ($params !== '')
				{
					$params .= '&';
				}
				$params .= 'templateevents=1';
			}
			else
			{
				if ($params === false)
				{
					$params = array();
				}
				$params['templateevents'] = 1;
			}
			$event['params'] = $params;
		}
	}
}
