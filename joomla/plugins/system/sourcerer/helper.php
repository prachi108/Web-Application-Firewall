<?php
/**
 * @package         Sourcerer
 * @version         6.3.6
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2016 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

require_once JPATH_LIBRARIES . '/regularlabs/helpers/helper.php';
require_once JPATH_LIBRARIES . '/regularlabs/helpers/functions.php';
require_once JPATH_LIBRARIES . '/regularlabs/helpers/tags.php';
require_once JPATH_LIBRARIES . '/regularlabs/helpers/text.php';
require_once JPATH_LIBRARIES . '/regularlabs/helpers/protect.php';

RLFunctions::loadLanguage('plg_system_sourcerer');

/**
 * Plugin that replaces Sourcerer code with its HTML / CSS / JavaScript / PHP equivalent
 */
class PlgSystemSourcererHelper
{
	var $option     = '';
	var $src_params = null;

	public function __construct(&$params)
	{
		$this->option = JFactory::getApplication()->input->get('option');

		// Set plugin parameters
		$this->src_params              = new stdClass;
		$this->src_params->syntax_word = trim($params->syntax_word);

		// Tag character start and end
		$this->src_params->tag_characters = $params->tag_characters;
		list($tag_start, $tag_end) = $this->getTagCharacters(true);

		// Break/paragraph start and end tags
		$this->src_params->breaks_start = RLTags::getRegexSurroundingTagPre();
		$this->src_params->breaks_end   = RLTags::getRegexSurroundingTagPost();
		$breaks_start                   = $this->src_params->breaks_start;
		$breaks_end                     = $this->src_params->breaks_end;
		$spaces                         = RLTags::getRegexSpaces('*');
		$inside_tag                     = RLTags::getRegexInsideTag();

		$this->src_params->regex = '#('
			. '(?P<start_pre>' . $breaks_start . ')'
			. $tag_start . preg_quote($this->src_params->syntax_word, '#') . $spaces . '(?P<data>' . $inside_tag . ')' . $tag_end
			. '(?P<start_post>' . $breaks_end . ')'

			. '(?P<content>.*?)'

			. '(?P<end_pre>' . $breaks_start . ')'
			. $tag_start . '\/' . preg_quote($this->src_params->syntax_word, '#') . $tag_end
			. '(?P<end_post>' . $breaks_end . ')'
			. ')#s';

		$this->src_params->tags_syntax = array(array('<', '>'), array('\[\[', '\]\]'));
		$this->src_params->splitter    = '<!-- START: SRC_SPLIT -->';

		$this->src_params->protected_tags = array(
			$this->src_params->tag_character_start . $this->src_params->syntax_word,
		);

		$this->src_params->trim           = (bool) $params->trim;
		$this->src_params->enable_in_head = (bool) $params->enable_in_head;
		$this->src_params->include_path   = str_replace('//', '/', ('/' . trim($params->include_path, ' /\\') . '/'));

		$user                            = JFactory::getUser();
		$this->src_params->user_is_admin = $user->authorise('core.admin', 1);

		// Initialise the different enables
		$this->src_params->areas                              = array();
		$this->src_params->areas['default']                   = array();
		$this->src_params->areas['default']['enable_css']     = $params->enable_css;
		$this->src_params->areas['default']['enable_js']      = $params->enable_js;
		$this->src_params->areas['default']['enable_php']     = $params->enable_php;
		$this->src_params->areas['default']['forbidden_php']  = $params->forbidden_php;
		$this->src_params->areas['default']['forbidden_tags'] = $params->forbidden_tags;


		$this->src_params->currentarea        = 'default';
		$this->src_params->remove_from_search = $params->remove_from_search;
	}

	/**
	 * onContentPrepare
	 */
	public function onContentPrepare(&$article, &$context)
	{
		$area   = isset($article->created_by) ? 'articles' : 'other';
		$remove = $this->src_params->remove_from_search
			&& in_array($context, array('com_search.search', 'com_search.search.article', 'com_finder.indexer'));


		if (!RLHelper::isCategoryList($context))
		{
			switch (true)
			{
				case (isset($article->text)):
					$this->replace($article->text, $area, $article, $remove);
					break;

				case (isset($article->introtext)):
					$this->replace($article->introtext, $area, $article, $remove);

				case (isset($article->fulltext)) :
					$this->replace($article->fulltext, $area, $article, $remove);
					break;
			}
		}

		if (isset($article->description))
		{
			$this->replace($article->description, $area, $article, $remove);
		}

		if (isset($article->title))
		{
			$this->replace($article->title, $area, $article, $remove);
		}
	}

	/**
	 * onAfterDispatch
	 */
	public function onAfterDispatch()
	{
		// only in html, pdfs, ajax/raw and feeds
		if (!in_array(JFactory::getDocument()->getType(), array('html', 'pdf', 'ajax', 'raw')) && !RLFunctions::isFeed())
		{
			return;
		}

		if (!$buffer = RLFunctions::getComponentBuffer())
		{
			return;
		}

		$this->tagArea($buffer, 'SRC', 'component');

		JFactory::getDocument()->setBuffer($buffer, 'component');
	}

	/**
	 * onAfterRender
	 */
	public function onAfterRender()
	{
		// only in html, pdfs, ajax/raw and feeds
		if (!in_array(JFactory::getDocument()->getType(), array('html', 'pdf', 'ajax', 'raw')) && !RLFunctions::isFeed())
		{
			return;
		}

		// Grab the body (but be gentle)
		$html = JFactory::getApplication()->getBody();

		if ($html == '')
		{
			return;
		}

		list($pre, $body, $post) = RLText::getBody($html);

		$this->protect($body);
		$this->replaceInTheRest($body);

		$this->cleanLeftoverJunk($body);
		RLProtect::unprotect($body);

		$this->src_params->enable_in_head
			? $this->replace($pre, 'head')
			: $this->cleanTagsFromHead($pre);

		$html = $pre . $body . $post;

		// Throw the body back (less gentle)
		JFactory::getApplication()->setBody($html);
	}

	function replaceInTheRest(&$string)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		list($pre_string, $string, $post_string) = RLText::getContentContainingSearches(
			$string,
			array(
				$this->src_params->tag_character_start . $this->src_params->syntax_word,
			),
			array(
				$this->src_params->tag_character_start . '/' . $this->src_params->syntax_word . $this->src_params->tag_character_end,
			)
		);

		if ($string == '')
		{
			$string = $pre_string . $string . $post_string;

			return;
		}

		// COMPONENT
		if (RLFunctions::isFeed())
		{
			$s      = '#(<item[^>]*>)#s';
			$string = preg_replace($s, '\1<!-- START: SRC_COMPONENT -->', $string);
			$string = str_replace('</item>', '<!-- END: SRC_COMPONENT --></item>', $string);
		}
		if (strpos($string, '<!-- START: SRC_COMPONENT -->') === false)
		{
			$this->tagArea($string, 'SRC', 'component');
		}


		$components = $this->getTagArea($string, 'SRC', 'component');
		foreach ($components as $component)
		{
			$this->replace($component['1'], 'components', '');
			$string = str_replace($component['0'], $component['1'], $string);
		}

		// EVERYWHERE
		$this->replace($string, 'other');

		$string = $pre_string . $string . $post_string;
	}

	function tagArea(&$string, $ext = 'EXT', $area = '')
	{
		if ($string && $area)
		{
			$string = '<!-- START: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->' . $string . '<!-- END: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->';
			if ($area == 'article_text')
			{
				$string = preg_replace('#(<hr class="system-pagebreak".*?>)#si', '<!-- END: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->\1<!-- START: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->', $string);
			}
		}
	}

	function getTagArea(&$string, $ext = 'EXT', $area = '')
	{
		$matches = array();
		if ($string && $area)
		{
			$start   = '<!-- START: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->';
			$end     = '<!-- END: ' . strtoupper($ext) . '_' . strtoupper($area) . ' -->';
			$matches = explode($start, $string);
			array_shift($matches);
			foreach ($matches as $i => $match)
			{
				list($text) = explode($end, $match, 2);
				$matches[$i] = array(
					$start . $text . $end,
					$text,
				);
			}
		}

		return $matches;
	}

	function replace(&$string, $area = 'articles', $article = '', $remove = false)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		$array       = $this->stringToSplitArray($string, $this->src_params->regex);
		$array_count = count($array);

		if ($array_count <= 1)
		{
			return;
		}

		for ($i = 1; $i < $array_count - 1; $i++)
		{
			if (!fmod($i, 2) || !preg_match($this->src_params->regex, $array[$i], $match))
			{
				continue;
			}

			$content = $this->handleMatch($match, $area, $article, $remove);

			$array[$i] = $match['start_pre'] . $match['start_post'] . $content . $match['end_pre'] . $match['end_post'];
		}

		$string = implode('', $array);
	}

	function handleMatch(&$match, $area = 'articles', $article = '', $remove = false)
	{
		if ($remove)
		{
			return '';
		}

		$data    = RLTags::getTagValues(trim($match['data']), array());
		$content = trim($match['content']);

		$remove_html = !in_array('0', $data->params);

		// Remove html tags if code is placed via the WYSIWYG editor
		if ($remove_html)
		{
			$this->cleanText($content);
		}

		$this->replacePhpShortCodes($content);

		// Add the include file if file=... or include=... is used in the {source} tag
		$file = !empty($data->file) ? $data->file : (!empty($data->include) ? $data->include : '');
		if (!empty($file) && JFile::exists(JPATH_SITE . $this->src_params->include_path . $file))
		{
			$content = '<?php include JPATH_SITE . \'' . $this->src_params->include_path . $file . '\'; ?>' . $content;
		}

		$this->replaceTags($content, $area, $article);

		if (!$remove_html)
		{
			return $content;
		}

		$trim = isset($data->trim) ? $data->trim : $this->src_params->trim;

		if ($trim)
		{
			$tags = RLTags::cleanSurroundingTags(array(
				'start_pre'  => $match['start_pre'],
				'start_post' => $match['start_post'],
			));

			$match = array_merge($match, $tags);

			$tags = RLTags::cleanSurroundingTags(array(
				'end_pre'  => $match['end_pre'],
				'end_post' => $match['end_post'],
			));

			$match = array_merge($match, $tags);

			$tags = RLTags::cleanSurroundingTags(array(
				'start_pre' => $match['start_pre'],
				'end_post'  => $match['end_post'],
			));

			$match = array_merge($match, $tags);
		}

		return $content;
	}

	function replacePhpShortCodes(&$string)
	{
		// Replace <? with <?php
		$string = preg_replace('#<\?(\s.*?)\?>#', '<?php\1?>', $string);
		// Replace <?= with <?php echo
		$string = preg_replace('#<\?=\s*(.*?)\?>#', '<?php echo \1?>', $string);
	}

	function replaceTags(&$string, $area = 'articles', $article = '')
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		// allow in component?
		if (RLProtect::isRestrictedComponent(isset($this->params->components) ? $this->params->components : array(), $area))
		{
			$this->protectTags($string);

			return;
		}

		$this->replaceTagsByType($string, $area, 'php', $article);
		$this->replaceTagsByType($string, $area, 'all');
		$this->replaceTagsByType($string, $area, 'js');
		$this->replaceTagsByType($string, $area, 'css');
	}

	function replaceTagsByType(&$string, $area = 'articles', $type = 'all', $article = '')
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		$type_ext = '_' . $type;
		if ($type == 'all')
		{
			$type_ext = '';
		}

		$a             = $this->src_params->areas['default'];
		$security_pass = 1;
		$enable = isset($a['enable' . $type_ext]) ? $a['enable' . $type_ext] : 1;

		switch ($type)
		{
			case 'php':
				$this->replaceTagsPHP($string, $enable, $security_pass, $article);
				break;
			case 'js':
				$this->replaceTagsJS($string, $enable, $security_pass);
				break;
			case 'css':
				$this->replaceTagsCSS($string, $enable, $security_pass);
				break;
			default:
				$this->replaceTagsAll($string, $enable, $security_pass);
				break;
		}
	}

	/**
	 * Replace any html style tags by a comment tag if not permitted
	 * Match: <...>
	 */
	function replaceTagsAll(&$string, $enabled = 1, $security_pass = 1)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::_('SRC_CODE_REMOVED_NOT_ENABLED') . ' -->';

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_SECURITY', '') . ' -->';

			return;
		}

		$this->cleanTags($string);

		$a = $this->src_params->areas['default'];
		$forbidden_tags_array = explode(',', $a['forbidden_tags']);
		$this->cleanArray($forbidden_tags_array);
		// remove the comment tag syntax from the array - they cannot be disabled
		$forbidden_tags_array = array_diff($forbidden_tags_array, array('!--'));
		// reindex the array
		$forbidden_tags_array = array_merge($forbidden_tags_array);

		$has_forbidden_tags = 0;
		foreach ($forbidden_tags_array as $forbidden_tag)
		{
			if (!(strpos($string, '<' . $forbidden_tag) == false))
			{
				$has_forbidden_tags = 1;
				break;
			}
		}

		if (!$has_forbidden_tags)
		{
			return;
		}

		// double tags
		$tag_regex = '#<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>.*?</\1>#si';
		preg_match_all($tag_regex, $string, $matches, PREG_SET_ORDER);

		if (!empty($matches))
		{
			foreach ($matches as $match)
			{
				if (!in_array($match['1'], $forbidden_tags_array))
				{
					continue;
				}

				$tag    = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match['1']) . ' -->';
				$string = str_replace($match['0'], $tag, $string);
			}
		}

		// single tags
		$tag_regex = '#<\s*([a-z\!][^>\s]*?)(?:\s+.*?)?>#si';
		preg_match_all($tag_regex, $string, $matches, PREG_SET_ORDER);

		if (!empty($matches))
		{
			foreach ($matches as $match)
			{
				if (!in_array($match['1'], $forbidden_tags_array))
				{
					continue;
				}

				$tag    = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_TAG_REMOVED_FORBIDDEN', $match['1']) . ' -->';
				$string = str_replace($match['0'], $tag, $string);
			}
		}
	}

	/**
	 * Replace the PHP tags with the evaluated PHP scripts
	 * Or replace by a comment tag the PHP tags if not permitted
	 */
	function replaceTagsPHP(&$src_str, $src_enabled = 1, $src_security_pass = 1, $article = '')
	{
		if (!is_string($src_str) || $src_str == '')
		{
			return;
		}

		if ((strpos($src_str, '<?') === false) && (strpos($src_str, '[[?') === false))
		{
			return;
		}

		global $src_vars;

		// Match ( read {} as <> ):
		// {?php ... ?}
		// {? ... ?}
		$src_string_array       = $this->stringToSplitArray($src_str, '-start-' . '\?(?:php)?[\s<](.*?)\?' . '-end-', 1);
		$src_string_array_count = count($src_string_array);

		if ($src_string_array_count < 1)
		{
			$src_str = implode('', $src_string_array);

			return;
		}

		if (!$src_enabled)
		{
			// replace source block content with HTML comment
			$src_string_array      = array();
			$src_string_array['0'] = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_PHP'), JText::_('SRC_PHP')) . ' -->';

			$src_str = implode('', $src_string_array);

			return;
		}
		if (!$src_security_pass)
		{
			// replace source block content with HTML comment
			$src_string_array      = array();
			$src_string_array['0'] = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_PHP')) . ' -->';

			$src_str = implode('', $src_string_array);

			return;
		}

		// if source block content has more than 1 php block, combine them
		if ($src_string_array_count > 3)
		{
			for ($i = 2; $i < $src_string_array_count - 1; $i++)
			{
				if (fmod($i, 2) == 0)
				{
					$src_string_array['1'] .= "<!-- SRC_SEMICOLON --> ?>" . $src_string_array[$i] . "<?php ";
					unset($src_string_array[$i]);
					continue;
				}

				$src_string_array['1'] .= $src_string_array[$i];
				unset($src_string_array[$i]);
			}
		}

		// fixes problem with _REQUEST being stripped if there is an error in the code
		$src_backup_REQUEST = $_REQUEST;
		$src_backup_vars    = array_keys(get_defined_vars());

		$src_script = trim($src_string_array['1']) . '<!-- SRC_SEMICOLON -->';
		$src_script = preg_replace('#(;\s*)?<\!-- SRC_SEMICOLON -->#s', ';', $src_script);

		$a = $this->src_params->areas['default'];
		$src_forbidden_php_array = explode(',', $a['forbidden_php']);
		$this->cleanArray($src_forbidden_php_array);
		$src_forbidden_php_regex = '#[^a-z_](' . implode('|', $src_forbidden_php_array) . ')(\s*\(|\s+[\'"])#si';

		preg_match_all($src_forbidden_php_regex, ' ' . $src_script, $src_functions, PREG_SET_ORDER);

		if (!empty($src_functions))
		{
			$src_functionsArray = array();
			foreach ($src_functions as $src_function)
			{
				$src_functionsArray[] = $src_function['1'] . ')';
			}

			$src_comment = JText::_('SRC_PHP_CODE_REMOVED_FORBIDDEN') . ': ( ' . implode(', ', $src_functionsArray) . ' )';

			$src_string_array['1'] = JFactory::getDocument()->getType() == 'html'
				? '<!-- ' . JText::_('SRC_COMMENT') . ': ' . $src_comment . ' -->'
				: $src_string_array['1'] = '';

			$src_str = implode('', $src_string_array);

			return;
		}

		if (!isset($Itemid))
		{
			$Itemid = JFactory::getApplication()->input->getInt('Itemid');
		}
		if (!isset($mainframe) || !isset($app))
		{
			$mainframe = $app = JFactory::getApplication();
		}
		if (!isset($document) || !isset($doc))
		{
			$document = $doc = JFactory::getDocument();
		}
		if (!isset($database) || !isset($db))
		{
			$database = $db = JFactory::getDbo();
		}
		if (!isset($user))
		{
			$user = JFactory::getUser();
		}

		$src_script = '
			if (is_array($src_vars)) {
				foreach ($src_vars as $src_key => $src_value) {
					${$src_key} = $src_value;
				}
			}
			' . $src_script . ';
			return get_defined_vars();
			';

		$temp_PHP_func = create_function('&$src_vars, &$article, &$Itemid, &$mainframe, &$app, &$document, &$doc, &$database, &$db, &$user', $src_script);

		// evaluate the script
		// but without using the the evil eval
		ob_start();
		$src_new_vars = $temp_PHP_func($src_vars, $article, $Itemid, $mainframe, $app, $document, $doc, $database, $db, $user);
		unset($temp_PHP_func);
		$src_string_array['1'] = ob_get_contents();
		ob_end_clean();

		$src_diff_vars = array_diff(array_keys($src_new_vars), $src_backup_vars);
		foreach ($src_diff_vars as $src_diff_key)
		{
			if (!in_array($src_diff_key, array('src_vars', 'article', 'Itemid', 'mainframe', 'app', 'document', 'doc', 'database', 'db', 'user'))
				&& substr($src_diff_key, 0, 4) != 'src_'
			)
			{
				$src_vars[$src_diff_key] = $src_new_vars[$src_diff_key];
			}
		}

		$src_str = implode('', $src_string_array);
	}

	/**
	 * Replace the JavaScript tags by a comment tag if not permitted
	 */
	function replaceTagsJS(&$string, $enabled = 1, $security_pass = 1)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		// quick check to see if i is necessary to do anything
		if ((strpos($string, 'script') === false))
		{
			return;
		}

		// Match:
		// <script ...>...</script>
		$tag_regex =
			'(-start-' . '\s*script\s[^' . '-end-' . ']*?[^/]\s*' . '-end-'
			. '(.*?)'
			. '-start-' . '\s*\/\s*script\s*' . '-end-)';
		$arr       = $this->stringToSplitArray($string, $tag_regex, 1);
		$arr_count = count($arr);

		// Match:
		// <script ...>
		// single script tags are not xhtml compliant and should not occur, but just incase they do...
		if ($arr_count == 1)
		{
			$tag_regex = '(-start-' . '\s*script\s.*?' . '-end-)';
			$arr       = $this->stringToSplitArray($string, $tag_regex, 1);
			$arr_count = count($arr);
		}

		if ($arr_count <= 1)
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_JAVASCRIPT'), JText::_('SRC_JAVASCRIPT')) . ' -->';

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_JAVASCRIPT')) . ' -->';

			return;
		}
	}

	/**
	 * Replace the CSS tags by a comment tag if not permitted
	 */
	function replaceTagsCSS(&$string, $enabled = 1, $security_pass = 1)
	{
		if (!is_string($string) || $string == '')
		{
			return;
		}

		// quick check to see if i is necessary to do anything
		if ((strpos($string, 'style') === false) && (strpos($string, 'link') === false))
		{
			return;
		}

		// Match:
		// <script ...>...</script>
		$tag_regex =
			'(-start-' . '\s*style\s[^' . '-end-' . ']*?[^/]\s*' . '-end-'
			. '(.*?)'
			. '-start-' . '\s*\/\s*style\s*' . '-end-)';
		$arr       = $this->stringToSplitArray($string, $tag_regex, 1);
		$arr_count = count($arr);

		// Match:
		// <script ...>
		// single script tags are not xhtml compliant and should not occur, but just in case they do...
		if ($arr_count == 1)
		{
			$tag_regex = '(-start-' . '\s*link\s[^' . '-end-' . ']*?(rel="stylesheet"|type="text/css").*?' . '-end-)';
			$arr       = $this->stringToSplitArray($string, $tag_regex, 1);
			$arr_count = count($arr);
		}

		if ($arr_count <= 1)
		{
			return;
		}

		if (!$enabled)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_NOT_ALLOWED', JText::_('SRC_CSS'), JText::_('SRC_CSS')) . ' -->';

			return;
		}

		if (!$security_pass)
		{
			// replace source block content with HTML comment
			$string = '<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::sprintf('SRC_CODE_REMOVED_SECURITY', JText::_('SRC_CSS')) . ' -->';

			return;
		}
	}

	function stringToSplitArray($string, $search, $tags = 0)
	{
		if (!$tags)
		{
			$string = preg_replace($search, $this->src_params->splitter . '\1' . $this->src_params->splitter, $string);

			return explode($this->src_params->splitter, $string);
		}

		foreach ($this->src_params->tags_syntax as $src_tag_syntax)
		{
			$tag_search = str_replace('-start-', $src_tag_syntax['0'], $search);
			$tag_search = str_replace('-end-', $src_tag_syntax['1'], $tag_search);
			$tag_search = '#' . $tag_search . '#si';
			$string     = preg_replace($tag_search, $this->src_params->splitter . '\1' . $this->src_params->splitter, $string);
		}

		return explode($this->src_params->splitter, $string);
	}

	function cleanTags(&$string)
	{
		foreach ($this->src_params->tags_syntax as $src_tag_syntax)
		{
			$tag_regex = '#' . $src_tag_syntax['0'] . '\s*(\/?\s*[a-z\!][^' . $src_tag_syntax['1'] . ']*?(?:\s+.*?)?)' . $src_tag_syntax['1'] . '#si';
			$string    = preg_replace($tag_regex, '<\1\2>', $string);
		}
	}

	function cleanArray(&$array)
	{
		// trim all values
		$array = array_map('trim', $array);
		// remove duplicates
		$array = array_unique($array);
		// remove empty (or false) values
		$array = array_filter($array);
	}

	function cleanText(&$string)
	{
		require_once JPATH_LIBRARIES . '/regularlabs/helpers/text.php';

		$string = RLText::convertWysiwygToPlainText($string);
	}

	/**
	 * Protect input and text area's
	 */
	function protect(&$string)
	{
		RLProtect::protectForm($string, $this->src_params->protected_tags);
	}

	function protectTags(&$string)
	{
		RLProtect::protectTags($string, $this->src_params->protected_tags);
	}

	function unprotectTags(&$string)
	{
		RLProtect::unprotectTags($string, $this->src_params->protected_tags);
	}

	function cleanTagsFromHead(&$string)
	{
		if (
			strpos($string, $this->src_params->tag_character_start . $this->src_params->syntax_word) === false
			&& strpos($string, $this->src_params->tag_character_start . '/' . $this->src_params->syntax_word) === false
		)
		{
			return;
		}

		list($tag_start, $tag_end) = $this->getTagCharacters(true);
		$spaces     = RLTags::getRegexSpaces('*');
		$inside_tag = RLTags::getRegexInsideTag();

		// Remove start tag to end tag
		$string = preg_replace(
			$this->src_params->regex,
			'',
			$string
		);

		// Remove start tag with optional php stuff after it
		$string = preg_replace(
			'#'
			. $tag_start . preg_quote($this->src_params->syntax_word, '#') . $spaces . '(' . $inside_tag . ')' . $tag_end
			. '(\s*<\?php(.*?)\?>)?'
			. '#s',
			'',
			$string
		);

		// Remove left over end tags
		$string = preg_replace(
			'#'
			. $tag_start . '\/' . preg_quote($this->src_params->syntax_word, '#') . $tag_end
			. '#s',
			'',
			$string
		);
	}

	public function getTagCharacters($quote = false)
	{
		if (!isset($this->src_params->tag_character_start))
		{
			list($this->src_params->tag_character_start, $this->src_params->tag_character_end) = explode('.', $this->src_params->tag_characters);
		}

		$start = $this->src_params->tag_character_start;
		$end   = $this->src_params->tag_character_end;

		if ($quote)
		{
			$start = preg_quote($start, '#');
			$end   = preg_quote($end, '#');
		}

		return array($start, $end);
	}

	/**
	 * Just in case you can't figure the method name out: this cleans the left-over junk
	 */
	function cleanLeftoverJunk(&$string)
	{
		$string = preg_replace('#<\!-- (START|END): SRC_[^>]* -->#', '', $string);

		if (strpos($string, $this->src_params->tag_character_start . '/' . $this->src_params->syntax_word) === false)
		{
			$this->unprotectTags($string);

			return;
		}

		$regex = $this->src_params->regex;
		if (@preg_match($regex . 'u', $string))
		{
			$regex .= 'u';
		}

		$string = preg_replace(
			$regex,
			'<!-- ' . JText::_('SRC_COMMENT') . ': ' . JText::_('SRC_CODE_REMOVED_NOT_ENABLED') . ' -->',
			$string
		);

		$this->unprotectTags($string);
	}
}
