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

class RLHtmlFix
{
	public static function _($string)
	{
		if (!preg_match('#</?(' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>#is', $string))
		{
			return $string;
		}

		// Convert utf8 characters to html entities
		if (function_exists('mb_convert_encoding'))
		{
			$string = mb_convert_encoding($string, 'html-entities', 'utf-8');
		}

		$string = self::protectSpecialCode($string);

		$string = self::convertDivsInsideInlineElementsToSpans($string);
		$string = self::removeParagraphsAroundBlockElements($string);
		$string = self::removeInlineElementsAroundBlockElements($string);

		$string = class_exists('DOMDocument')
			? self::DOMDocument($string)
			: self::custom($string);

		$string = self::unprotectSpecialCode($string);

		// Convert html entities back to utf8 characters
		if (function_exists('mb_convert_encoding'))
		{
			// Make sure &lt; and &gt; don't get converted
			$string = str_replace(array('&lt;', '&gt;'), array('&amp;lt;', '&amp;gt;'), $string);

			$string = mb_convert_encoding($string, 'utf-8', 'html-entities');
		}

		return $string;
	}

	public static function DOMDocument($string)
	{
		$doc = new DOMDocument();

		$doc->substituteEntities = false;

		@$doc->loadHTML($string);
		$string = $doc->saveHTML();

		$string = preg_replace('#^.*?(?:<head>(.*)</head>.*?)?<body>(.*)</body>.*?$#s', '\1\2', $string);

		// Remove leading/trailing empty paragraph
		$string = preg_replace('#(^\s*<p(?: [^>]*)?>\s*</p>|<p(?: [^>]*)?>\s*</p>\s*$)#s', '', $string);

		return $string;
	}

	public static function custom($string)
	{
		$block_regex = '<(' . implode('|', self::getBlockElementsNoDiv()) . ')[\s>]';

		$string = preg_replace('#(' . $block_regex . ')#s', '[:SPLIT-BLOCK:]\1', $string);
		$parts  = explode('[:SPLIT-BLOCK:]', $string);

		foreach ($parts as $i => &$part)
		{
			if (!preg_match('#^' . $block_regex . '#si', $part, $type))
			{
				continue;
			}

			$type = strtolower($type['1']);

			// remove endings of other block elements
			$part = preg_replace('#</(?:' . implode('|', self::getBlockElementsNoDiv($type)) . ')>#is', '', $part);

			if (strpos($part, '</' . $type . '>') !== false)
			{
				continue;
			}

			// Add ending tag once
			$part = preg_replace('#(\s*)$#s', '</' . $type . '>\1', $part, 1);

			// Remove empty block tags
			$part = preg_replace('#^<' . $type . '(?: [^>]*)?>\s*</' . $type . '>#is', '', $part);
		}

		return implode('', $parts);
	}

	public static function removeParagraphsAroundBlockElements($string)
	{
		if (strpos($string, '</p>') == false)
		{
			return $string;
		}

		$string = preg_replace(
			'#'
			. '(?:<p(?: [^>]*)?>\s*)'
			. '(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)'
			. '#s',
			'\1',
			$string
		);

		$string = preg_replace(
			'#'
			. '(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)'
			. '(?:\s*</p>)'
			. '#s',
			'\1',
			$string
		);

		return $string;
	}

	public static function convertDivsInsideInlineElementsToSpans($string)
	{
		if (strpos($string, '</div>') == false)
		{
			return $string;
		}

		// Ignore block elements inside anchors
		$regex = '#<(' . implode('|', self::getInlineElementsNoAnchor()) . ')(?: [^>]*)?>.*?</\1>#s';
		preg_match_all($regex, $string, $matches);

		if (empty($matches))
		{
			return $string;
		}

		$matches      = array_unique($matches['0']);
		$searches     = array();
		$replacements = array();

		foreach ($matches as $match)
		{
			if (strpos($match, '</div>') === false)
			{
				continue;
			}

			$searches[]     = $match;
			$replacements[] = str_replace(
				array('<div>', '<div ', '</div>'),
				array('<span>', '<span ', '</span>'),
				$match
			);
		}

		if (empty($searches))
		{
			return $string;
		}

		return str_replace($matches, $replacements, $string);
	}

	public static function removeInlineElementsAroundBlockElements($string)
	{
		$string = preg_replace(
			'#'
			. '(?:<(?:' . implode('|', self::getInlineElementsNoAnchor()) . ')(?: [^>]*)?>\s*)'
			. '(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)'
			. '#s',
			'\1',
			$string
		);

		$string = preg_replace(
			'#'
			. '(</?(?:' . implode('|', self::getBlockElements()) . ')(?: [^>]*)?>)'
			. '(?:\s*</(?:' . implode('|', self::getInlineElementsNoAnchor()) . ')>)'
			. '#s',
			'\1',
			$string
		);

		return $string;
	}

	private static function getBlockElements($exclude = array())
	{
		if (!is_array($exclude))
		{
			$exclude = array($exclude);
		}

		$elements = array(
			'div', 'p', 'pre',
			'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
		);

		return array_diff($elements, $exclude);
	}

	private static function getInlineElements($exclude = array())
	{
		if (!is_array($exclude))
		{
			$exclude = array($exclude);
		}

		$elements = array(
			'span', 'code', 'a',
			'strong', 'b', 'em', 'i', 'u', 'big', 'small', 'font',
		);

		return array_diff($elements, $exclude);
	}

	private static function getBlockElementsNoDiv($exclude = array())
	{
		return array_diff(self::getBlockElements($exclude), array('div'));
	}

	private static function getInlineElementsNoAnchor($exclude = array())
	{
		return array_diff(self::getInlineElements($exclude), array('a'));
	}

	private static function protectSpecialCode($string)
	{
		require_once __DIR__ . '/protect.php';

		// Protect PHP code
		RLProtect::protectByRegex($string, '#(<|&lt;)\?php\s.*?\?(>|&gt;)#s');

		// Protect {...} tags
		RLProtect::protectByRegex($string, '#\{.*?\}#s');

		// Protect [...] tags
		RLProtect::protectByRegex($string, '#\[.*?\]#s');

		RLProtect::convertProtectionToHtmlSafe($string);

		return $string;
	}

	private static function unprotectSpecialCode($string)
	{
		require_once __DIR__ . '/protect.php';

		RLProtect::unprotectHtmlSafe($string);

		return $string;
	}
}
