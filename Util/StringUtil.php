<?php
namespace Oka\ApiBundle\Util;

/**
 * 
 * @author cedrick
 * 
 */
final class StringUtil
{
	/**
	 * @param mixed $value
	 * @return string
	 */
	public static function stringCast($value)
	{
		if (is_array($value)) {
			$cast = '';
			foreach ($value as $content) {
				$cast .= is_array($content) ? self::stringCast($value) : ' ' . (string) $content;
			}
		} else {
			$cast = (string) $value;
		}
		
		return $cast;
	}
}