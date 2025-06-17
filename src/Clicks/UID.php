<?php namespace LeadMax\TrackYourStats\Clicks;

use Exception;
use Nassiry\Encoder\Facades\Encoder;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 8/29/2017
 * Time: 2:30 PM
 */
class UID
{

	static function encode(int $clickId, int $targetLength = 12): string
	{
		$coreEncoded = Encoder::encodeId($clickId, $targetLength);
		$charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		// Add random padding before and after
		$padLength = $targetLength - strlen($coreEncoded) - 1;
		//$prefix = '';
		$suffix = '';
		if ($padLength > 0) {
			$suffix = substr(str_shuffle(str_repeat($charset, $padLength)), 0, $padLength);
		}

		return $coreEncoded . '|' . $suffix;
	}

	static function decode(string $str): string
	{
		$coreEncoded = explode("|", $str);

		return Encoder::decodeId($coreEncoded[0]);
	}
}