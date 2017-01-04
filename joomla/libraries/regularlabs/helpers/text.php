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

require_once __DIR__ . '/string.php';

class RLText
{
	public static function fixDate(&$date)
	{
		if (!$date)
		{
			$date = null;

			return;
		}

		$date = trim($date);

		// Check if date has correct syntax: 00-00-00 00:00:00
		if (preg_match('#^[0-9]+-[0-9]+-[0-9]+( [0-9][0-9]:[0-9][0-9]:[0-9][0-9])?$#', $date))
		{
			return;
		}

		// Check if date has syntax: 00-00-00 00:00
		// If so, add :00 (seconds)
		if (preg_match('#^[0-9]+-[0-9]+-[0-9]+ [0-9][0-9]:[0-9][0-9]$#', $date))
		{
			$date .= ':00';

			return;
		}

		// Check if date has a prepending date syntax: 00-00-00 ...
		// If so, add :00 (seconds)
		if (preg_match('#^([0-9]+-[0-9]+-[0-9]+)#', $date, $match))
		{
			$date = $match['1'] . ' 00:00:00';

			return;
		}

		// Date format is not correct, so return null
		$date = null;
	}

	public static function fixDateOffset(&$date)
	{
		if ($date <= 0)
		{
			$date = 0;

			return;
		}

		$date = JFactory::getDate($date, JFactory::getUser()->getParam('timezone', JFactory::getConfig()->get('offset')));
		$date->setTimezone(new DateTimeZone('UTC'));

		$date = $date->format('Y-m-d H:i:s', true, false);
	}

	public static function dateToDateFormat($dateFormat)
	{
		$caracs = array(
			// Day
			'%d'  => 'd',
			'%a'  => 'D',
			'%#d' => 'j',
			'%A'  => 'l',
			'%u'  => 'N',
			'%w'  => 'w',
			'%j'  => 'z',
			// Week
			'%V'  => 'W',
			// Month
			'%B'  => 'F',
			'%m'  => 'm',
			'%b'  => 'M',
			// Year
			'%G'  => 'o',
			'%Y'  => 'Y',
			'%y'  => 'y',
			// Time
			'%P'  => 'a',
			'%p'  => 'A',
			'%l'  => 'g',
			'%I'  => 'h',
			'%H'  => 'H',
			'%M'  => 'i',
			'%S'  => 's',
			// Timezone
			'%z'  => 'O',
			'%Z'  => 'T',
			// Full Date / Time
			'%s'  => 'U',
		);

		return strtr((string) $dateFormat, $caracs);
	}

	public static function dateToStrftimeFormat($dateFormat)
	{
		$caracs = array(
			// Day - no strf eq : S
			'd'  => '%d',
			'D'  => '%a',
			'jS' => '%#d[TH]',
			'j'  => '%#d',
			'l'  => '%A',
			'N'  => '%u',
			'w'  => '%w',
			'z'  => '%j',
			// Week - no date eq : %U, %W
			'W'  => '%V',
			// Month - no strf eq : n, t
			'F'  => '%B',
			'm'  => '%m',
			'M'  => '%b',
			// Year - no strf eq : L; no date eq : %C, %g
			'o'  => '%G',
			'Y'  => '%Y',
			'y'  => '%y',
			// Time - no strf eq : B, G, u; no date eq : %r, %R, %T, %X
			'a'  => '%P',
			'A'  => '%p',
			'g'  => '%l',
			'h'  => '%I',
			'H'  => '%H',
			'i'  => '%M',
			's'  => '%S',
			// Timezone - no strf eq : e, I, P, Z
			'O'  => '%z',
			'T'  => '%Z',
			// Full Date / Time - no strf eq : c, r; no date eq : %c, %D, %F, %x
			'U'  => '%s',
		);

		return strtr((string) $dateFormat, $caracs);
	}

	public static function html_entity_decoder($given_html, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
	{
		if (is_array($given_html))
		{
			foreach ($given_html as $i => $html)
			{
				$given_html[$i] = self::html_entity_decoder($html);
			}

			return $given_html;
		}

		return html_entity_decode($given_html, $quote_style, $charset);
	}

	public static function toArray($string, $separator = '')
	{
		if (is_array($string))
		{
			return $string;
		}

		if (is_object($string))
		{
			return (array) $string;
		}

		if ($separator == '')
		{
			return array($string);
		}

		return explode($separator, $string);
	}

	public static function cleanTitle($string, $strip_tags = false, $strip_spaces = true)
	{
		if (empty($string))
		{
			return '';
		}

		// remove comment tags
		$string = preg_replace('#<\!--.*?-->#s', '', $string);

		// replace weird whitespace
		$string = str_replace(chr(194) . chr(160), ' ', $string);

		if ($strip_tags)
		{
			// remove html tags
			$string = preg_replace('#</?[a-z][^>]*>#usi', '', $string);
			// remove comments tags
			$string = preg_replace('#<\!--.*?-->#us', '', $string);
		}

		if ($strip_spaces)
		{
			// Replace html spaces
			$string = str_replace(array('&nbsp;', '&#160;'), ' ', $string);

			// Remove duplicate whitespace
			$string = preg_replace('#[ \n\r\t]+#', ' ', $string);
		}

		return trim($string);
	}

	public static function prepareSelectItem($string, $published = 1, $type = '', $remove_first = 0)
	{
		if (empty($string))
		{
			return '';
		}

		$string = str_replace(array('&nbsp;', '&#160;'), ' ', $string);
		$string = preg_replace('#- #', '  ', $string);

		for ($i = 0; $remove_first > $i; $i++)
		{
			$string = preg_replace('#^  #', '', $string);
		}

		if (preg_match('#^( *)(.*)$#', $string, $match))
		{
			list($string, $pre, $name) = $match;

			$pre = preg_replace('#  #', ' ·  ', $pre);
			$pre = preg_replace('#(( ·  )*) ·  #', '\1 »  ', $pre);
			$pre = str_replace('  ', ' &nbsp; ', $pre);

			$string = $pre . $name;
		}

		switch (true)
		{
			case ($type == 'separator'):
				$string = '[[:font-weight:normal;font-style:italic;color:grey;:]]' . $string;
				break;

			case (!$published):
				$string = '[[:font-style:italic;color:grey;:]]' . $string . ' [' . JText::_('JUNPUBLISHED') . ']';
				break;

			case ($published == 2):
				$string = '[[:font-style:italic;:]]' . $string . ' [' . JText::_('JARCHIVED') . ']';
				break;
		}

		return $string;
	}

	public static function strReplaceOnce($search, $replace, $string)
	{
		$pos = strpos($string, $search);

		if ($pos === false)
		{
			return $string;
		}

		return substr_replace($string, $replace, $pos, strlen($search));
	}

	/**
	 * Gets the full uri and optionally adds/replaces the hash
	 */
	public static function getURI($hash = '')
	{
		$url = JUri::getInstance()->toString();

		if ($hash == '')
		{
			return $url;
		}

		if (strpos($url, '#') !== false)
		{
			$url = substr($url, 0, strpos($url, '#'));
		}

		return $url . '#' . $hash;
	}

	/**
	 * gets attribute from a tag string
	 */
	public static function fixHtmlTagStructure(&$string, $remove_surrounding_p_tags = 1)
	{
		if (empty($string))
		{
			return;
		}

		// Combine duplicate <p> tags
		RLText::combinePTags($string);

		// Move div nested inside <p> tags outside of it
		RLText::moveDivBlocksOutsidePBlocks($string);

		// Remove duplicate ending </p> tags
		RLText::removeDuplicateTags($string, '/p');

		if ($remove_surrounding_p_tags)
		{
			// Remove surrounding <p></p> blocks
			RLText::removeSurroundingPBlocks($string);
		}
	}

	/**
	 * Move div nested inside <p> tags outside of it
	 * input: <p><div>...</div></p>
	 *  output: </p><div>...</div><p>
	 */
	public static function moveDivBlocksOutsidePBlocks(&$string)
	{
		if (empty($string))
		{
			return;
		}

		$p_start_tag   = '<p(?: [^>]*)?>';
		$p_end_tag     = '</p>';
		$optional_tags = '\s*(?:<br ?/?>|<\!-- [^>]*-->|&nbsp;|&\#160;)*\s*';

		$string = trim(preg_replace('#(' . $p_start_tag . ')(' . $optional_tags . '<div(?: [^>]*)?>.*?</div>' . $optional_tags . ')(' . $p_end_tag . ')#si', '\2', $string));
	}

	/**
	 * Combine duplicate <p> tags
	 * input: <p class="aaa" a="1"><!-- ... --><p class="bbb" b="2">
	 * output: <p class="aaa bbb" a="1" b="2"><!-- ... -->
	 */
	public static function combinePTags(&$string)
	{
		if (empty($string))
		{
			return;
		}

		$p_start_tag   = '<p(?: [^>]*)?>';
		$optional_tags = '\s*(?:<\!-- [^>]*-->|&nbsp;|&\#160;)*\s*';
		preg_match_all('#(' . $p_start_tag . ')(' . $optional_tags . ')(' . $p_start_tag . ')#si', $string, $tags, PREG_SET_ORDER);

		if (empty($tags))
		{
			return;
		}

		foreach ($tags as $tag)
		{
			$string = str_replace($tag['0'], $tag['2'] . RLText::combineTags($tag['1'], $tag['3']), $string);
		}
	}

	/**
	 * Remove surrounding <p></p> blocks
	 * input: <p ...><!-- ... --></p>...<p ...><!-- ... --></p>
	 * output: <!-- ... -->...<!-- ... -->
	 */
	public static function removeSurroundingPBlocks(&$string)
	{
		if (empty($string))
		{
			return;
		}

		RLText::removeStartingPTag($string);
		RLText::removeEndingPTag($string);
	}

	public static function removeStartingPTag(&$string)
	{
		if (empty($string))
		{
			return;
		}

		$p_start_tag = '<p(?: [^>]*)?>';

		if (strpos($string, '</p>') === false || !preg_match('#^\s*' . $p_start_tag . '#si', $string))
		{
			return;
		}

		$test = preg_replace('#^(\s*)' . $p_start_tag . '#si', '\1', $string);
		if (stripos($test, '<p') > stripos($test, '</p'))
		{
			return;
		}

		$string = $test;
	}

	public static function removeEndingPTag(&$string)
	{
		if (empty($string))
		{
			return;
		}

		$p_end_tag = '</p>';

		if (!preg_match('#' . $p_end_tag . '\s*$#si', $string))
		{
			return;
		}

		$test = preg_replace('#' . $p_end_tag . '(\s*)$#si', '\1', $string);
		if (strrpos($test, '<p') > strrpos($test, '</p'))
		{
			return;
		}

		$string = $test;
	}

	/**
	 * Combine tags
	 */
	public static function combineTags($tag1, $tag2)
	{
		// Return if tags are the same
		if ($tag1 == $tag2)
		{
			return $tag1;
		}

		if (!preg_match('#<([a-z][a-z0-9]*)#si', $tag1, $tag_type))
		{
			return $tag2;
		}

		$tag_type = $tag_type[1];

		if (!$attribs = RLText::combineAttributes($tag1, $tag2))
		{
			return '<' . $tag_type . '>';
		}

		return '<' . $tag_type . ' ' . $attribs . '>';
	}

	/**
	 * gets attribute from a tag string
	 */
	public static function getAttribute($attributes, $string)
	{
		// get attribute from string
		if (preg_match('#' . preg_quote($attributes, '#') . '="([^"]*)"#si', $string, $match))
		{
			return $match['1'];
		}

		return '';
	}

	/**
	 * gets attributes from a tag string
	 */
	public static function getAttributes($string)
	{
		if (empty($string))
		{
			return array();
		}

		preg_match_all('#([a-z0-9-_]+)="([^"]*)"#si', $string, $matches, PREG_SET_ORDER);

		if (empty($matches))
		{
			return array();
		}

		$attribs = array();

		foreach ($matches as $match)
		{
			$attribs[$match['1']] = $match['2'];
		}

		return $attribs;
	}

	/**
	 * combine attribute values in a tag string
	 */
	public static function combineAttributes($string1, $string2)
	{
		$attribs1 = is_array($string1) ? $string1 : RLText::getAttributes($string1);
		$attribs2 = is_array($string2) ? $string2 : RLText::getAttributes($string2);

		$dublicate_attribs = array_intersect_key($attribs1, $attribs2);

		// Fill $attribs with the unique ids
		$attribs = array_diff_key($attribs1, $attribs2) + array_diff_key($attribs2, $attribs1);

		// Add/combine the duplicate ids
		$single_value_attributes = array('id', 'href');
		foreach ($dublicate_attribs as $key => $val)
		{
			if (in_array($key, $single_value_attributes))
			{
				$attribs[$key] = $attribs2[$key];
				continue;
			}
			// Combine strings, but remove duplicates
			// "aaa bbb" + "aaa ccc" = "aaa bbb ccc"

			// use a ';' as a concatenated for javascript values (keys beginning with 'on')
			$glue          = substr($key, 0, 2) == 'on' ? ';' : ' ';
			$attribs[$key] = implode($glue, array_merge(explode($glue, $attribs1[$key]), explode($glue, $attribs2[$key])));
		}

		foreach ($attribs as $key => &$val)
		{
			$val = $key . '="' . $val . '"';
		}

		return implode(' ', $attribs);
	}

	/**
	 * remove duplicate </p> tags
	 * input: </p><!-- ... --></p>
	 * output: </p><!-- ... -->
	 */
	public static function removeDuplicateTags(&$string, $tag_type = 'p')
	{
		if (empty($string))
		{
			return;
		}

		$string = preg_replace('#(<' . $tag_type . '(?: [^>]*)?>\s*(<!--.*?-->\s*)?)<' . $tag_type . '(?: [^>]*)?>#si', '\1', $string);
	}

	/**
	 * Creates an alias from a string
	 * Based on stringURLUnicodeSlug method from the unicode slug plugin by infograf768
	 */
	public static function createAlias($string)
	{
		if (empty($string))
		{
			return '';
		}

		// Remove < > html entities
		$string = str_replace(array('&lt;', '&gt;'), '', $string);

		// Convert html entities
		$string = RLText::html_entity_decoder($string);

		// Convert to lowercase
		$string = RLString::strtolower($string);

		// remove html tags
		$string = preg_replace('#</?[a-z][^>]*>#usi', '', $string);
		// remove comments tags
		$string = preg_replace('#<\!--.*?-->#us', '', $string);

		// Replace weird whitespace characters like (Â) with spaces
		//$string = str_replace(array(chr(160), chr(194)), ' ', $string);
		$string = str_replace("\xC2\xA0", ' ', $string);
		$string = str_replace("\xE2\x80\xA8", ' ', $string); // ascii only

		// Replace double byte whitespaces by single byte (East Asian languages)
		$string = str_replace("\xE3\x80\x80", ' ', $string);

		// Remove any '-' from the string as they will be used as concatenator.
		// Would be great to let the spaces in but only Firefox is friendly with this
		$string = str_replace('-', ' ', $string);

		// Replace forbidden characters by whitespaces
		$string = self::regexReplace('[,:\#\$\*"@+=;&\.%\(\)\[\]\{\}\/\'\\\\|]', "\x20", $string);

		// Delete all characters that should not take up any space, like: ?
		$string = self::regexReplace('[\?\!¿¡]', '', $string);

		// Trim white spaces at beginning and end of alias and make lowercase
		$string = trim($string);

		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$string = self::regexReplace('\x20+', '-', $string);

		// Remove leading and trailing hyphens
		$string = trim($string, '-');

		return $string;
	}

	public static function regexReplace($pattern, $replacement, $string)
	{
		return function_exists('mb_ereg_replace')
			? mb_ereg_replace($pattern, $replacement, $string)
			: preg_replace('#' . $pattern . '#', $replacement, $string);
	}

	/**
	 * Creates an array of different syntaxes of titles to match against a url variable
	 */
	public static function createUrlMatches($titles = array())
	{
		$matches = array();
		foreach ($titles as $title)
		{
			$matches[] = $title;
			$matches[] = RLString::strtolower($title);
		}

		$matches = array_unique($matches);

		foreach ($matches as $title)
		{
			$matches[] = htmlspecialchars(RLText::html_entity_decoder($title));
		}

		$matches = array_unique($matches);

		foreach ($matches as $title)
		{
			$matches[] = urlencode($title);
			$matches[] = utf8_decode($title);
			$matches[] = str_replace(' ', '', $title);
			$matches[] = trim(preg_replace('#[^a-z0-9]#i', '', $title));
			$matches[] = trim(preg_replace('#[^a-z]#i', '', $title));
		}

		$matches = array_unique($matches);

		foreach ($matches as $i => $title)
		{
			$matches[$i] = trim(str_replace('?', '', $title));
		}

		$matches = array_diff(array_unique($matches), array('', '-'));

		return $matches;
	}

	static function getBody($html)
	{
		if (strpos($html, '<body') === false || strpos($html, '</body>') === false)
		{
			return array('', $html, '');
		}

		$html_split = explode('<body', $html, 2);
		$pre        = $html_split['0'];
		$body       = '<body' . $html_split['1'];
		$body_split = explode('</body>', $body);
		$post       = array_pop($body_split);
		$body       = implode('</body>', $body_split) . '</body>';

		return array($pre, $body, $post);
	}

	static function getContentContainingSearches($string, $start_searches = array(), $end_searches = array(), $start_offset = 200, $end_offset = null)
	{
		// String is too short to split and search through
		if (strlen($string) < 100)
		{
			return array('', $string, '');
		}

		$end_offset = is_null($end_offset) ? $start_offset : $end_offset;

		$found       = 0;
		$start_split = strlen($string);

		foreach ($start_searches as $search)
		{
			$pos = strpos($string, $search);

			if ($pos === false)
			{
				continue;
			}

			$start_split = min($start_split, $pos);
			$found       = 1;
		}

		// No searches are found
		if (!$found)
		{
			return array($string, '', '');
		}

		// String is too short to split
		if (strlen($string) < ($start_offset + $end_offset + 1000))
		{
			return array('', $string, '');
		}

		$start_split = max($start_split - $start_offset, 0);

		$pre    = substr($string, 0, $start_split);
		$string = substr($string, $start_split);

		self::fixBrokenTagsByPreString($pre, $string);

		if (empty($end_searches))
		{
			$end_searches = $start_searches;
		}

		$found     = 0;
		$end_split = 0;
		foreach ($end_searches as $search)
		{
			$pos = strrpos($string, $search);

			if ($pos === false)
			{
				continue;
			}

			$end_split = max($end_split, $pos + strlen($search));
			$found     = 1;
		}

		// No end split is found, so don't split remainder
		if (!$found)
		{
			return array($pre, $string, '');
		}

		$end_split = min($end_split + $end_offset, strlen($string));

		$post   = substr($string, $end_split);
		$string = substr($string, 0, $end_split);

		self::fixBrokenTagsByPostString($post, $string);

		return array($pre, $string, $post);
	}

	protected static function fixBrokenTagsByPreString(&$pre, &$string)
	{
		if (!preg_match('#</?[a-z][^>]*(="[^"]*)?$#s', $pre, $match))
		{
			return;
		}

		$pre    = substr($pre, 0, strlen($pre) - strlen($match['0']));
		$string = $match['0'] . $string;
	}

	protected static function fixBrokenTagsByPostString(&$post, &$string)
	{
		if (!preg_match('#</?[a-z][^>]*(="[^"]*)?$#s', $string, $match))
		{
			return;
		}

		if (!preg_match('#^[^>]*>#s', $post, $match))
		{
			return;
		}

		$post = substr($post, strlen($match['0']));
		$string .= $match['0'];
	}

	static function createArray($string, $separator = ',')
	{
		return array_filter(explode($separator, trim($string)));
	}

	static function pregQuote($string = '', $delimiter = '#')
	{
		if (is_array($string))
		{
			return '(' . implode('|', self::pregQuoteArray($string, $delimiter)) . ')';
		}

		return preg_quote($string, $delimiter);
	}

	static function pregQuoteArray($array = array(), $delimiter = '#')
	{
		array_walk($array, function (&$part, $key, $delimiter)
		{
			$part = preg_quote($part, $delimiter);
		}, $delimiter);

		return $array;
	}

	static function stringContains($haystacks, $needles)
	{
		$haystacks = (array) $haystacks;
		$needles   = (array) $needles;

		foreach ($haystacks as $haystack)
		{
			foreach ($needles as $needle)
			{
				if (strpos($haystack, $needle) !== false)
				{
					return true;
				}
			}
		}

		return false;
	}

	public static function getTagRegex($tags, $include_no_attributes = true, $include_ending = true, $required_attributes = array())
	{
		require_once __DIR__ . '/tags.php';

		return RLTags::getRegexTags($tags, $include_no_attributes, $include_ending, $required_attributes);
	}

	public static function is_alphanumeric($string)
	{
		if (function_exists('ctype_alnum'))
		{
			return ctype_alnum($string);
		}

		return preg_match('#^[a-zA-Z0-9]+$#', $string);
	}

	public static function convertWysiwygToPlainText($string)
	{
		// replace chr style enters with normal enters
		$string = str_replace(array(chr(194) . chr(160), '&#160;', '&nbsp;'), ' ', $string);

		// replace linbreak tags with normal linebreaks (paragraphs, enters, etc).
		$enter_tags = array('p', 'br');
		$regex      = '#</?((' . implode(')|(', $enter_tags) . '))+[^>]*?>\n?#si';
		$string     = preg_replace($regex, " \n", $string);

		// replace indent characters with spaces
		$string = preg_replace('#<' . 'img [^>]*/sourcerer/images/tab\.png[^>]*>#si', '    ', $string);

		// strip all other tags
		$regex  = '#<(/?\w+((\s+\w+(\s*=\s*(?:".*?"|\'.*?\'|[^\'">\s]+))?)+\s*|\s*)/?)>#si';
		$string = preg_replace($regex, "", $string);

		// reset htmlentities
		$string = RLText::html_entity_decoder($string);

		// convert protected html entities &_...; -> &...;
		$string = preg_replace('#&_([a-z0-9\#]+?);#i', '&\1;', $string);

		return $string;
	}

	public static function splitString($string, $delimiters = array(), $max_length = 10000, $maximize_parts = true)
	{
		// String is too short to split
		if (strlen($string) < $max_length)
		{
			return array($string);
		}

		// No delimiters given or found
		if (empty($delimiters) || !RLText::stringContains($string, $delimiters))
		{
			return array($string);
		}

		// preg_quote all delimiters
		$array = preg_split('#' . RLText::pregQuote($delimiters) . '#s', $string, null, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		if (!$maximize_parts)
		{
			return $array;
		}

		$new_array = array();
		foreach ($array as $part)
		{
			// First element, add to new array
			if (!count($new_array))
			{
				$new_array[] = $part;
				continue;
			}

			$last_part = end($new_array);
			$last_key  = key($new_array);

			// If last and current parts are longer than max_length, then simply add as new value
			if (strlen($last_part) + strlen($part) > $max_length)
			{
				$new_array[] = $part;
				continue;
			}

			// Concatenate part to previous part
			$new_array[$last_key] .= $part;
		}

		return $new_array;
	}
}
