<?php
/**
 * @package         Regular Labs Library
 * @version         16.11.9943
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/cache.php';
require_once __DIR__ . '/text.php';

class RLProtect
{
	static $protect_start        = '<!-- ___RL_PROTECTED___';
	static $protect_end          = '___RL_PROTECTED___ -->';
	static $protect_tags_start   = '<!-- ___RL_PROTECTED_TAGS___';
	static $protect_tags_end     = '___RL_PROTECTED_TAGS___ -->';
	static $html_safe_start      = '___RL_PROTECTED___';
	static $html_safe_end        = '___/RL_PROTECTED___';
	static $html_safe_tags_start = '___RL_PROTECTED_TAGS___';
	static $html_safe_tags_end   = '___/RL_PROTECTED_TAGS___';
	static $sourcerer_tag        = null;

	/**
	 * check if page should be protected for certain extensions
	 */
	public static function isProtectedPage($extension_alias = '', $hastags = false, $exclude_formats = array('pdf'))
	{
		// return if disabled via url
		if (($extension_alias && JFactory::getApplication()->input->get('disable_' . $extension_alias)))
		{
			return true;
		}

		$hash = md5('isProtectedPage_' . $hastags . '_' . json_encode($exclude_formats));

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		// return if current page is pdf format
		// return if current page is an image
		// return if current page is Regular Labs QuickPage
		// return if current page is a JoomFish or Josetta page
		$isprotected = (
			in_array(JFactory::getApplication()->input->get('format'), $exclude_formats)
			|| in_array(JFactory::getApplication()->input->get('view'), array('image', 'img'))
			|| in_array(JFactory::getApplication()->input->get('type'), array('image', 'img'))
			|| ($hastags
				&& (
					JFactory::getApplication()->input->getInt('rl_qp', 0)
					|| in_array(JFactory::getApplication()->input->get('option'), array('com_joomfishplus', 'com_josetta'))
				)
			)
			|| (JFactory::getApplication()->isAdmin()
				&& in_array(JFactory::getApplication()->input->get('option'), array('com_jdownloads'))
			)
		);

		return RLCache::set(
			$hash,
			$isprotected
		);
	}

	/**
	 * check if page is an admin page
	 */
	public static function isAdmin($block_login = 0)
	{
		$app = JFactory::getApplication();

		return (
			$app->isAdmin()
			&& (!$block_login || !JFactory::getUser()->get('guest'))
			&& $app->input->get('task') != 'preview'
			&& !(
				$app->input->get('option') == 'com_finder'
				&& $app->input->get('format') == 'json'
			)
		);
	}

	/**
	 * check if page is an edit page
	 */
	public static function isEditPage()
	{
		$hash = md5('isEditPage');

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		$app = JFactory::getApplication();

		$option = $app->input->get('option');
		// always return false for these components
		if (in_array($option, array('com_rsevents', 'com_rseventspro')))
		{
			return RLCache::set(
				$hash,
				false
			);
		}

		$task = $app->input->get('task');
		if (strpos($task, '.') !== false)
		{
			$task = explode('.', $task);
			$task = array_pop($task);
		}

		$view = $app->input->get('view');
		if (strpos($view, '.') !== false)
		{
			$view = explode('.', $view);
			$view = array_pop($view);
		}

		$isedit = (
			in_array($task, array('edit', 'form', 'submission'))
			|| in_array($view, array('edit', 'form'))
			|| in_array($app->input->get('do'), array('edit', 'form'))
			|| in_array($app->input->get('layout'), array('edit', 'form', 'write'))
			|| in_array($app->input->get('option'), array('com_contentsubmit', 'com_cckjseblod'))
			|| ($app->input->get('option') == 'com_comprofiler' && in_array($task, array('', 'userdetails')))
			|| RLProtect::isAdmin()
		);

		return RLCache::set(
			$hash,
			$isedit
		);
	}

	/**
	 * Check if the page is a restricted component
	 *
	 * @return bool
	 */
	public static function isRestrictedComponent($restricted_components, $area = 'component')
	{
		if ($area != 'component' && !($area == 'article' && JFactory::getApplication()->input->get('option') == 'com_content'))
		{
			return false;
		}

		$restricted_components =
			is_array($restricted_components)
				? $restricted_components
				: explode(',', str_replace('|', ',', $restricted_components));

		if (in_array(JFactory::getApplication()->input->get('option'), $restricted_components))
		{
			return true;
		}

		if (JFactory::getApplication()->input->get('option') == 'com_acymailing'
			&& JFactory::getApplication()->input->get('ctrl') != 'user'
		)
		{
			return true;
		}

		return false;
	}

	/**
	 * Check if the component is installed
	 *
	 * @return bool
	 */
	public static function isComponentInstalled($extension_alias)
	{
		jimport('joomla.filesystem.file');

		return JFile::exists(JPATH_ADMINISTRATOR . '/components/com_' . $extension_alias . '/' . $extension_alias . '.php');
	}

	/**
	 * Check if the component is installed
	 *
	 * @return bool
	 */
	public static function isSystemPluginInstalled($extension_alias)
	{
		jimport('joomla.filesystem.file');

		return JFile::exists(JPATH_PLUGINS . '/system/' . $extension_alias . '/' . $extension_alias . '.php');
	}

	/**
	 * the regular expression to mach the edit form
	 */
	public static function getFormRegex($regex_format = 0)
	{
		$regex = '(<' . 'form\s[^>]*('
			. '(id|name)="(adminForm|postform|submissionForm|default_action_user|seblod_form)"'
			. '|action="[^"]*option=com_myjspace&(amp;)?view=see"'
			. '))';

		if (!$regex_format)
		{
			return $regex;
		}

		return '#' . $regex . '#si';
	}

	/**
	 * protect all text based form fields
	 */
	public static function protectFields(&$string, $search_strings = array())
	{
		// No specified strings tags found in the string
		if (!self::containsStringsToProtect($string, $search_strings))
		{
			return;
		}

		$parts = RLText::splitString($string, array('</label>', '</select>'));

		foreach ($parts as &$part)
		{
			if (!self::containsStringsToProtect($part, $search_strings))
			{
				continue;
			}

			self::protectFieldsPart($part);
		}

		$string = implode('', $parts);
	}

	private static function containsStringsToProtect(&$string, $search_strings = array())
	{
		if (
			empty($string)
			|| (
				strpos($string, '<input') === false
				&& strpos($string, '<textarea') === false
				&& strpos($string, '<select') === false
			)
		)
		{
			return false;
		}

		// No specified strings tags found in the string
		if (!empty($search_strings) && !RLText::stringContains($string, $search_strings))
		{
			return false;
		}

		return true;
	}

	private static function protectFieldsPart(&$string)
	{
		self::protectFieldsTextAreas($string);
		self::protectFieldsInputFields($string);
	}

	public static function protectFieldsTextAreas(&$string)
	{
		if (strpos($string, '<textarea') === false)
		{
			return;
		}

		// Only replace non-empty textareas
		// Todo: maybe also prevent empty textareas but with a non-empty placeholder attribute

		// Temporarily replace empty textareas
		$temp_tag = '___TEMP_TEXTAREA___';
		$string   = preg_replace(
			'#<textarea((?:\s[^>]*)?)>(\s*)</textarea>#si',
			'<' . $temp_tag . '\1>\2</' . $temp_tag . '>',
			$string
		);

		self::protectByRegex(
			$string,
			'#(?:'
			. '<' . 'textarea.*?<' . '/textarea>'
			. '\s*)+#si'
		);

		// Replace back the temporarily replaced empty textareas
		$string = str_replace($temp_tag, 'textarea', $string);
	}

	public static function protectFieldsInputFields(&$string)
	{
		if (strpos($string, '<input') === false)
		{
			return;
		}

		$type_values = '(?:text|email|hidden)';
		// must be of certain type
		$param_type = '\s+type\s*=\s*(?:"' . $type_values . '"|\'' . $type_values . '\'])';
		// must have a non-empty value or placeholder attribute
		$param_value = '\s+(?:value|placeholder)\s*=\s*(?:"[^"]+"|\'[^\']+\'])';
		// Regex to match any other parameter
		$params = '(?:\s+[a-z][a-z0-9-_]*(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[0-9]+))?)*';

		self::protectByRegex(
			$string,
			'#(?:(?:'
			. '<' . 'input' . $params . $param_type . $params . $param_value . $params . '\s*/?>'
			. '|<' . 'input' . $params . $param_value . $params . $param_type . $params . '\s*/?>'
			. ')\s*)+#si'
		);
	}

	/**
	 * protect all text based form fields
	 */
	public static function protectScripts(&$string)
	{
		if (strpos($string, '</script>') === false)
		{
			return;
		}

		self::protectByRegex(
			$string,
			'#<script[\s>].*?</script>#si'
		);
	}

	/**
	 * protect all html tags with some type of attributes/content
	 */
	public static function protectHtmlTags(&$string)
	{
		// protect comment tags
		self::protectByRegex($string, '#<!--\s[^>].*?(?:\s-->|$)#s');
		// protect html tags
		self::protectByRegex($string, '#<[a-z][^>]*(?:="[^"]*"|=\'[^\']*\')+[^>]*>#si');
	}

	/**
	 * protect text by given regex
	 */
	public static function protectByRegex(&$string, $regex)
	{
		preg_match_all($regex, $string, $matches);

		if (empty($matches))
		{
			return;
		}

		$matches      = array_unique($matches['0']);
		$replacements = array();

		foreach ($matches as $match)
		{
			$replacements[] = self::protectString($match);
		}

		$string = str_replace($matches, $replacements, $string);
	}

	/**
	 * protect given plugin style tags
	 */
	public static function protectTags(&$string, $tags = array(), $include_closing_tags = true)
	{
		list($tags, $protected) = self::prepareTags($tags, $include_closing_tags);

		$string = str_replace($tags, $protected, $string);
	}

	/**
	 * replace any protected tags to original
	 */
	public static function unprotectTags(&$string, $tags = array(), $include_closing_tags = true)
	{
		list($tags, $protected) = self::prepareTags($tags, $include_closing_tags);

		$string = str_replace($protected, $tags, $string);
	}

	/**
	 * protect array of strings
	 */
	public static function protectInString(&$string, $unprotected = array(), $protected = array())
	{
		$protected = empty($protected) ? self::protectArray($unprotected) : $protected;

		$string = str_replace($unprotected, $protected, $string);
	}

	/**
	 * replace any protected tags to original
	 */
	public static function unprotectInString(&$string, $unprotected = array(), $protected = array())
	{
		$protected = empty($protected) ? self::protectArray($unprotected) : $protected;

		$string = str_replace($protected, $unprotected, $string);
	}

	private static function getSourcererTag()
	{
		if (isset(self::$sourcerer_tag))
		{
			return self::$sourcerer_tag;
		}

		require_once __DIR__ . '/parameters.php';
		$params              = RLParameters::getInstance()->getPluginParams('sourcerer');
		self::$sourcerer_tag = isset($params->syntax_word) ? $params->syntax_word : '';

		return self::$sourcerer_tag;
	}

	/**
	 * protect all Sourcerer blocks
	 */
	public static function protectSourcerer(&$string)
	{
		if (self::getSourcererTag())
		{
			return;
		}

		if (strpos($string, '{/' . self::$sourcerer_tag . '}') === false)
		{
			return;
		}

		$regex = '#' . preg_quote('{' . self::$sourcerer_tag, '#') . '[\s\}].*?' . preg_quote('{/' . self::$sourcerer_tag . '}', '#') . '#si';

		preg_match_all($regex, $string, $matches);

		if (empty($matches))
		{
			return;
		}

		$matches = array_unique($matches['0']);

		foreach ($matches as $match)
		{
			$string = str_replace($match, self::protectString($match), $string);
		}
	}

	/**
	 * protect complete adminForm
	 */
	public static function protectForm(&$string, $tags = array(), $include_closing_tags = true)
	{
		if (!self::isEditPage())
		{
			return;
		}

		list($tags, $protected_tags) = self::prepareTags($tags, $include_closing_tags);

		$string = preg_replace(self::getFormRegex(1), '<!-- TMP_START_EDITOR -->\1', $string);
		$string = explode('<!-- TMP_START_EDITOR -->', $string);

		foreach ($string as $i => &$string_part)
		{
			if (empty($string_part) || !fmod($i, 2))
			{
				continue;
			}

			self::protectFormPart($string_part, $tags, $protected_tags);
		}

		$string = implode('', $string);
	}

	private static function protectFormPart(&$string, $tags = array(), $protected_tags = array())
	{
		if (strpos($string, '</form>') === false)
		{
			return;
		}

		// Protect entire form
		if (empty($tags))
		{
			$form_parts      = explode('</form>', $string, 2);
			$form_parts['0'] = self::protectString($form_parts['0'] . '</form>');
			$string          = implode('', $form_parts);

			return;
		}

		if (!preg_match('#(?:' . implode('|', $tags) . ')#si', $string))
		{
			return;
		}

		$form_parts = explode('</form>', $string, 2);
		// protect tags only inside form fields
		preg_match_all('#(?:<textarea[^>]*>.*?<\/textarea>|<input[^>]*>)#si', $form_parts['0'], $matches);

		if (empty($matches))
		{
			return;
		}

		$matches = array_unique($matches['0']);

		foreach ($matches as $match)
		{
			$field           = str_replace($tags, $protected_tags, $match);
			$form_parts['0'] = str_replace($match, $field, $form_parts['0']);
		}

		$string = implode('</form>', $form_parts);
	}

	/**
	 * replace any protected text to original
	 */
	public static function unprotect(&$string)
	{
		self::unprotectByRegex(
			$string,
			'#' . preg_quote(self::$protect_tags_start, '#') . '(.*?)' . preg_quote(self::$protect_tags_end, '#') . '#si'
		);

		self::unprotectByRegex(
			$string,
			'#' . preg_quote(self::$protect_start, '#') . '(.*?)' . preg_quote(self::$protect_end, '#') . '#si'
		);
	}

	private static function unprotectByRegex(&$string, $regex)
	{
		while (preg_match_all($regex, $string, $matches, PREG_SET_ORDER))
		{
			foreach ($matches as $match)
			{
				$string = str_replace($match['0'], base64_decode($match['1']), $string);
			}
		}
	}

	/**
	 * replace any protected text to original
	 */
	public static function convertProtectionToHtmlSafe(&$string)
	{
		$string = str_replace(
			array(
				self::$protect_start,
				self::$protect_end,
				self::$protect_tags_start,
				self::$protect_tags_end,
			),
			array(
				self::$html_safe_start,
				self::$html_safe_end,
				self::$html_safe_tags_start,
				self::$html_safe_tags_end,
			),
			$string
		);
	}

	/**
	 * replace any protected text to original
	 */
	public static function unprotectHtmlSafe(&$string)
	{
		$string = str_replace(
			array(
				self::$html_safe_start,
				self::$html_safe_end,
				self::$html_safe_tags_start,
				self::$html_safe_tags_end,
			),
			array(
				self::$protect_start,
				self::$protect_end,
				self::$protect_tags_start,
				self::$protect_tags_end,
			),
			$string
		);

		self::unprotect($string);
	}

	/**
	 * prepare the tags and protected tags array
	 */
	private static function prepareTags($tags, $include_closing_tags = true)
	{
		if (!is_array($tags))
		{
			$tags = array($tags);
		}

		$hash = md5('prepareTags_' . json_encode($tags) . '_' . $include_closing_tags);

		if (RLCache::has($hash))
		{
			return RLCache::get($hash);
		}

		foreach ($tags as $i => $tag)
		{
			if (RLText::is_alphanumeric($tag['0']))
			{
				$tag = '{' . $tag;
			}

			$tags[$i] = $tag;

			if ($include_closing_tags)
			{
				$tags[] = preg_replace('#^([^a-z0-9]+)#', '\1/', $tag);
			}
		}

		return RLCache::set(
			$hash,
			array($tags, self::protectArray($tags, 1))
		);
	}

	/**
	 * encode string
	 */
	public static function protectString($string, $is_tag = 0)
	{
		if ($is_tag)
		{
			return self::$protect_tags_start . base64_encode($string) . self::$protect_tags_end;
		}

		return self::$protect_start . base64_encode($string) . self::$protect_end;
	}

	/**
	 * decode string
	 */
	public static function unprotectString($string, $is_tag = 0)
	{
		if ($is_tag)
		{
			return self::$protect_tags_start . base64_decode($string) . self::$protect_tags_end;
		}

		return self::$protect_start . base64_decode($string) . self::$protect_end;
	}

	/**
	 * encode tag string
	 */
	public static function protectTag($string)
	{
		return self::protectString($string, 1);
	}

	/**
	 * encode array of strings
	 */
	public static function protectArray($array, $is_tag = 0)
	{
		foreach ($array as &$string)
		{
			$string = self::protectString($string, $is_tag);
		}

		return $array;
	}

	/**
	 * decode array of strings
	 */
	public static function unprotectArray($array, $is_tag = 0)
	{
		foreach ($array as &$string)
		{
			$string = self::unprotectString($string, $is_tag);
		}

		return $array;
	}

	/**
	 * replace any protected tags to original
	 */
	public static function unprotectForm(&$string, $tags = array())
	{
		// Protect entire form
		if (empty($tags))
		{
			RLProtect::unprotect($string);

			return;
		}

		RLProtect::unprotectTags($string, $tags);
	}

	/**
	 * remove inline comments in scrips and styles
	 */
	public static function removeInlineComments(&$string, $name)
	{
		$string = preg_replace('#\s*/\* (START|END): ' . $name . ' [a-z]* \*/\s*#s', "\n", $string);
	}

	/**
	 * remove tags from title tags
	 */
	public static function removeFromHtmlTagContent(&$string, $tags, $include_closing_tags = true, $html_tags = array('title'))
	{
		list($tags, $protected) = self::prepareTags($tags, $include_closing_tags);

		if (!is_array($html_tags))
		{
			$html_tags = array($html_tags);
		}

		preg_match_all('#(<(' . implode('|', $html_tags) . ')(?:\s[^>]*?)>)(.*?)(</\2>)#si', $string, $matches, PREG_SET_ORDER);

		if (empty($matches))
		{
			return;
		}

		foreach ($matches as $match)
		{
			$content = $match['3'];
			foreach ($tags as $tag)
			{
				$content = preg_replace('#' . preg_quote($tag, '#') . '.*?\}#si', '', $content);
			}
			$string = str_replace($match['0'], $match['1'] . $content . $match['4'], $string);
		}
	}

	/**
	 * remove tags from tag attributes
	 */
	public static function removeFromHtmlTagAttributes(&$string, $tags, $attributes = 'ALL', $include_closing_tags = true)
	{
		list($tags, $protected) = self::prepareTags($tags, $include_closing_tags);

		if ($attributes == 'ALL')
		{
			$attributes = array('[a-z][a-z0-9-_]*');
		}

		if (!is_array($attributes))
		{
			$attributes = array($attributes);
		}

		preg_match_all('#\s(?:' . implode('|', $attributes) . ')\s*=\s*".*?"#si', $string, $matches);

		if (empty($matches) || empty($matches['0']))
		{
			return;
		}

		$matches = array_unique($matches['0']);

		// preg_quote all tags
		$tags_regex = '#' . RLText::pregQuote($tags) . '.*?\}#si';

		foreach ($matches as $match)
		{
			if (!RLText::stringContains($match, $tags))
			{
				continue;
			}

			$title = $match;

			$title = preg_replace($tags_regex, '', $title);

			$string = RLText::strReplaceOnce($match, $title, $string);
		}
	}

	/**
	 * Check if article passes security levels
	 */
	static function articlePassesSecurity(&$article, $securtiy_levels = array())
	{
		if (!isset($article->created_by))
		{
			return true;
		}

		if (empty($securtiy_levels))
		{
			return true;
		}

		if (is_string($securtiy_levels))
		{
			$securtiy_levels = array($securtiy_levels);
		}

		if (
			!is_array($securtiy_levels)
			|| in_array('-1', $securtiy_levels)
		)
		{
			return true;
		}

		// Lookup group level of creator
		$user_groups = new JAccess;
		$user_groups = $user_groups->getGroupsByUser($article->created_by);

		// Return true if any of the security levels are found in the users groups
		return count(array_intersect($user_groups, $securtiy_levels));
	}

	/**
	 * Place an error in the message queue
	 */
	static function throwError($error)
	{
		// Return if page is not an admin page or the admin login page
		if (
			!JFactory::getApplication()->isAdmin()
			|| JFactory::getUser()->get('guest')
		)
		{
			return;
		}

		// Check if message is not already in queue
		$messagequeue = JFactory::getApplication()->getMessageQueue();
		foreach ($messagequeue as $message)
		{
			if ($message['message'] == $error)
			{
				return;
			}
		}

		JFactory::getApplication()->enqueueMessage($error, 'error');
	}

	public static function isJoomla3()
	{
		return true;
	}
}
