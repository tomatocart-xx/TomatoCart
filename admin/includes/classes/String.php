<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: String.class.inc.php 2452 2009-05-01 08:59:02Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */

/**
 * This class contains functions for string operations
 *
 * @copyright Copyright Intermesh
 * @version $Id: String.class.inc.php 2452 2009-05-01 08:59:02Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.utils
 * @since Group-Office 3.0
 */

class osC_String_Admin {

	function replace_once($search, $replace, $subject) {
		$firstChar = strpos($subject, $search);
		if($firstChar !== false) {
			$beforeStr = substr($subject,0,$firstChar);
			$afterStr = substr($subject, $firstChar + strlen($search));
			return $beforeStr.$replace.$afterStr;
		} else {
			return $subject;
		}
	}

	/**
	 * Reverse strpos. couldn't get PHP strrpos to work with offset
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @param int $offset
	 * @return int
	 */
	function rstrpos ($haystack, $needle, $offset=0)
	{
		$size = strlen ($haystack);
		$pos = strpos (strrev($haystack), strrev($needle), $size - $offset);
		 
		if ($pos === false)
		return false;
		 
		return $size - $pos - strlen($needle);
	}

	function trim_lines($text)
	{
		//go_log(LOG_DEBUG, $text);
		str_replace("\r\n","\n", $text);

		$trimmed='';

		$lines = explode("\n", $text);
		foreach($lines as $line)
		{
			if($trimmed=='')
			{
				$trimmed .= $line."\n";
			}elseif(empty($line))
			{
				$trimmed .= "\n";
			}elseif($line[0]!=' ')
			{
				return $text;
			}else{
				$trimmed .= substr($line,1)."\n";
			}
		}

		return $trimmed;
	}



	/**
	 * Grab an e-mail address out of a string
	 *
	 * @param	int $level The log level. See sys_log() of the PHP docs
	 * @param	string $message The log message
	 * @access public
	 * @return void
	 */
	function get_email_from_string($email) {
		if (preg_match("/(\b)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", $email, $matches)) {
			return $matches[0];
		} else {
			return false;
		}
	}

	/*
	 function get_name_from_string($string) {
		if (preg_match('/([\D]*|[\D]*[\040][\D]*)/i', $string, $matches)) {
		$matches[0] = str_replace('"', '', $matches[0]);
		return $matches[0];
		} else {
		return $string;
		}
		}*/

	/**
	 * Adds paramaters to an URL
	 *
	 * @param	string $url
	 * @param	string $params
	 * @access public
	 * @return string
	 */

	function add_params_to_url($url, $params) {
		if (strpos($url, '?') === false) {
			$url .= '?'.$params;
		} else {
			$url .= '&amp;'.$params;
		}
		return $url;
	}



	/**
	 * Get's all queries from an SQL dump file in an array
	 *
	 * @param	string $file The absolute path to the SQL file
	 * @access public
	 * @return array An array of SQL strings
	 */

	function get_sql_queries($file) {
		$sql = '';
		$queries = array ();
		if ($handle = fopen($file, "r")) {
			while (!feof($handle)) {
				$buffer = trim(fgets($handle, 4096));
				if ($buffer != '' && substr($buffer, 0, 1) != '#' && substr($buffer, 0, 1) != '-') {
					$sql .= $buffer;
				}
			}
			fclose($handle);
		} else {
			die("Could not read SQL dump file $file!");
		}
		$length = strlen($sql);
		$in_string = false;
		$start = 0;
		$escaped = false;
		for ($i = 0; $i < $length; $i ++) {
			$char = $sql[$i];
			if ($char == '\'' && !$escaped) {
				$in_string = !$in_string;
			}
			if ($char == ';' && !$in_string) {
				$offset = $i - $start;
				$queries[] = substr($sql, $start, $offset);

				$start = $i +1;
			}
			if ($char == '\\') {
				$escaped = true;
			} else {
				$escaped = false;
			}
		}
		return $queries;
	}


	/**
	 * Return only the contents of the body tag from a HTML page
	 *
	 * @param	string $html A HTML formatted string
	 * @access public
	 * @return string HTML formated string
	 */

	function get_html_body($html) {
		$to_removed_array = array ("'<html[^>]*>'si", "'</html>'si", "'<body[^>]*>'si", "'</body>'si", "'<head[^>]*>.*?</head>'si", "'<style[^>]*>.*?</style>'si", "'<object[^>]*>.*?</object>'si",);

		//$html = str_replace("\r", "", $html);
		//$html = str_replace("\n", "", $html);

		$html = preg_replace($to_removed_array, '', $html);
		return $html;

	}


	/**
	 * Give it a full name and it tries to determine the First, Middle and Lastname
	 *
	 * @param	string $full_name A full name
	 * @access public
	 * @return array array with keys first, middle and last
	 */

	function split_name($full_name) {
		$name_arr = explode(' ', $full_name);

		$name['first'] = $full_name;
		$name['middle'] = '';
		$name['last'] = '';
		$count = count($name_arr);
		$last_index = $count -1;
		for ($i = 0; $i < $count; $i ++) {
			switch ($i) {
				case 0 :
					$name['first'] = $name_arr[$i];
					break;

				case $last_index :
					$name['last'] = $name_arr[$i];
					break;

				default :
					$name['middle'] .= $name_arr[$i].' ';
					break;
			}
		}
		$name['middle'] = trim($name['middle']);
		return $name;
	}

	/**
	 * Get the regex used for validating an email address
	 * Requires the Top Level Domain to be between 2 and 6 alphanumeric chars
	 *
	 * @param	none
	 * @access	public
	 * @return	string
	 */
	function get_email_validation_regex() {
		return "/^[a-z0-9\._\-]+@[a-z0-9\.\-_]+\.[a-z]{2,4}$/i";
	}


	/**
	 * Check if an email adress is in a valid format
	 *
	 * @param	string $email E-mail address
	 * @access public
	 * @return bool
	 */
	function validate_email($email) {
		return preg_match(String::get_email_validation_regex(), $email);
	}

	/**
	 * Checks for empty string and returns stripe when empty
	 *
	 * @param	string $input Any string
	 * @access public
	 * @return string
	 */
	function empty_to_stripe($input) {
		if ($input == "") {
			return "-";
		} else {
			return $input;
		}
	}

	/**
	 * Return a formatted address string
	 *
	 * @param	array $object User or contact
	 * @access public
	 * @return string Address formatted
	 */
	function address_format($object, $linebreak = '<br />') {
		if (isset ($object['name'])) {
			$name = $object['name'];
		} else {
			$middle_name = $object['middle_name'] == '' ? '' : $object['middle_name'].' ';

			if ($object['title'] != '' && $object['initials'] != '') {
				$name = $object['title'].' '.$object['initials'].' '.$middle_name.$object['last_name'];
			} else {
				$name = $object['first_name'].' '.$middle_name.$object['last_name'];
			}
		}

		$address = $name.$linebreak;

		if ($object['address'] != '') {
			$address .= $object['address'];
			if (isset ($object['address_no'])) {
				$address .= ' '.$object['address_no'];
			}
			$address .= $linebreak;
		}
		if ($object['zip'] != '') {
			$address .= $object['zip'].' ';
		}
		if ($object['city'] != '') {
			$address .= $object['city'].$linebreak;
		}
		if ($object['country'] != '') {
			global $lang;
			require_once($GLOBALS['GO_LANGUAGE']->get_base_language_file('countries'));
			
			$address .= $countries[$object['country']].$linebreak;
		}
		return $address;

	}


	/**
	 * Formats a name in Group-Office
	 *
	 * @param string $sort_name string Vlaue can be last_name or first_name
	 * @return string base64 encoded string
	 */
	function format_name($last, $first = '', $middle = '', $sort_name='') {

		if(is_array($last))
		{
			$first = isset($last['first_name']) ? $last['first_name'] : '';
			$middle = isset($last['middle_name']) ? $last['middle_name'] : '';
			$last = isset($last['last_name']) ? $last['last_name'] : '';
		}

		$sort_name = $sort_name == '' ? $_SESSION['GO_SESSION']['sort_name'] : $sort_name;

		if ($sort_name== 'last_name') {
			$name = 	!empty ($last) ? $last : '';
			if(!empty($last) && !empty($first))
			{
				$name .= ', ';
			}
			$name .= !empty ($first) ? $first : '';
			$name .= !empty ($middle) ? ' '.$middle : '';
		} else {
			$name = !empty ($first) ? $first : ' ';
			$name .= !empty ($middle) ? ' '.$middle.' ' : ' ';
			$name .= $last;
		}

		return $name;
	}


	/**
	 * Chop long strings with 3 dots
	 *
	 * Chops of the string after a given length and puts three dots behind it
	 * function editted by Tyler Gee to make it chop at whole words
	 *
	 * @param	string $string The string to chop
	 * @param	int $maxlength The maximum number of characters in the string
	 * @access public
	 * @return string
	 */

	function cut_string($string, $maxlength, $cut_whole_words = true) {
		if (strlen($string) > $maxlength) {
			$temp = substr($string, 0, $maxlength -3);
			if ($cut_whole_words) {
				if ($pos = strrpos($temp, ' ')) {
					return substr($temp, 0, $pos).'...';
				} else {
					return $temp = substr($string, 0, $maxlength -3).'...';
				}
			} else {
				return $temp.'...';
			}

		} else {
			return $string;
		}
	}

	/**
	 * Trim plain text to a maximum number of lines
	 * 
	 * @param $string
	 * @param $maxlines
	 * @return String
	 */
	function limit_lines($string,$maxlines)
	{
		$string = str_replace("\r", '', $string);
		$lines = explode("\n", $string, $maxlines);
		$new_string =  implode("\n", $lines);
		
		if(strlen($new_string)<strlen($string))
		{
			$new_string .= "\n...";
		}
		return $new_string;
	}





	/**
	 * Convert an enriched formated string to HTML format
	 *
	 * @param	string $enriched Enriched formatted string
	 * @access public
	 * @return string HTML formated string
	 */
	function enriched_to_html($enriched, $convert_links=true) {
		global $GO_CONFIG, $GO_MODULES;

		// We add space at the beginning and end of the string as it will
		// make some regular expression checks later much easier (so we
		// don't have to worry about start/end of line characters)
		$enriched = ' '.$enriched.' ';

		// Get color parameters into a more useable format.
		$enriched = preg_replace('/<color><param>([\da-fA-F]+),([\da-fA-F]+),([\da-fA-F]+)<\/param>/Uis', '<color r=\1 g=\2 b=\3>', $enriched);
		$enriched = preg_replace('/<color><param>(red|blue|green|yellow|cyan|magenta|black|white)<\/param>/Uis', '<color n=\1>', $enriched);

		// Get font family parameters into a more useable format.
		$enriched = preg_replace('/<fontfamily><param>(\w+)<\/param>/Uis', '<fontfamily f=\1>', $enriched);

		// Single line breaks become spaces, double line breaks are a
		// real break. This needs to do <nofill> tracking to be
		// compliant but we don't want to deal with state at this
		// time, so we fake it some day we should rewrite this to
		// handle <nofill> correctly.
		$enriched = preg_replace('/([^\n])\r\n([^\r])/', '\1 \2', $enriched);
		$enriched = preg_replace('/(\r\n)\r\n/', '\1', $enriched);

		// We try to protect against bad stuff here.
		$enriched = @ htmlspecialchars($enriched, ENT_QUOTES);

		// Now convert the known tags to html. Try to remove any tag
		// parameters to stop people from trying to pull a fast one
		$enriched = preg_replace('/(?<!&lt;)&lt;bold.*&gt;(.*)&lt;\/bold&gt;/Uis', '<span style="font-weight: bold">\1</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;italic.*&gt;(.*)&lt;\/italic&gt;/Uis', '<span style="font-style: italic">\1</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;underline.*&gt;(.*)&lt;\/underline&gt;/Uis', '<span style="text-decoration: underline">\1</span>', $enriched);
		$enriched = preg_replace_callback('/(?<!&lt;)&lt;color r=([\da-fA-F]+) g=([\da-fA-F]+) b=([\da-fA-F]+)&gt;(.*)&lt;\/color&gt;/Uis', create_function('$colors',
		'for ($i = 1; $i < 4; $i ++) {
			$colors[$i] = sprintf(\'%02X\', round(hexdec($colors[$i]) / 255));
		}
		return \'<span style="color: #\'.$colors[1].$colors[2].$colors[3].\'">\'.$colors[4].\'</span>\';'), $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;color n=(red|blue|green|yellow|cyan|magenta|black|white)&gt;(.*)&lt;\/color&gt;/Uis', '<span style="color: \1">\2</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fontfamily&gt;(.*)&lt;\/fontfamily&gt;/Uis', '\1', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fontfamily f=(\w+)&gt;(.*)&lt;\/fontfamily&gt;/Uis', '<span style="font-family: \1">\2</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;smaller.*&gt;/Uis', '<span style="font-size: smaller">', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;\/smaller&gt;/Uis', '</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;bigger.*&gt;/Uis', '<span style="font-size: larger">', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;\/bigger&gt;/Uis', '</span>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;fixed.*&gt;(.*)&lt;\/fixed&gt;/Uis', '<font face="fixed">\1</font>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;center.*&gt;(.*)&lt;\/center&gt;/Uis', '<div align="center">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushleft.*&gt;(.*)&lt;\/flushleft&gt;/Uis', '<div align="left">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushright.*&gt;(.*)&lt;\/flushright&gt;/Uis', '<div align="right">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;flushboth.*&gt;(.*)&lt;\/flushboth&gt;/Uis', '<div align="justify">\1</div>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;paraindent.*&gt;(.*)&lt;\/paraindent&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);
		$enriched = preg_replace('/(?<!&lt;)&lt;excerpt.*&gt;(.*)&lt;\/excerpt&gt;/Uis', '<blockquote>\1</blockquote>', $enriched);

		// Now we remove the leading/trailing space we added at the
		// start.
		$enriched = preg_replace('/^ (.*) $/s', '\1', $enriched);

		$module = $GO_MODULES->modules['email'];

		if($convert_links)
		{
			$enriched = preg_replace("/(?:^|\b)(((http(s?):\/\/)|(www\.-))([\w\.-]+)([,:;%#&\/?=\w+\.\-@]+))(?:\b|$)/is", "<a href=\"http$4://$5$6$7\" target=\"_blank\" class=\"blue\">$1</a>", $enriched);
			$enriched = preg_replace("/(\A|\s)([\w\.\-]+)(@)([\w\.-]+)([A-Za-z]{2,4})\b/i", "\\1<a href=\"mailto:\\2\\3\\4\\5\" class=\"blue\">\\2\\3\\4\\5</a>", $enriched);
		}

		$enriched = nl2br($enriched);
		$enriched = str_replace("\r", "", $enriched);
		$enriched = str_replace("\n", "", $enriched);

		return $enriched;

	}


	/**
	 * Convert plain text to HTML
	 *
	 * @param	string $text Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */
	function text_to_html($text, $convert_links=true) {
		global $GO_CONFIG, $GO_MODULES;

		if($convert_links)
		{
			$text = preg_replace("/\b(https?:\/\/[\pL0-9\.&\-\/@#;`~=%?:_\+]+)\b/ui", '{lt}a href={quot}$1{quot} target={quot}_blank{quot} class={quot}normal-link{quot}{gt}$1{lt}/a{gt}', $text."\n");
			$text = preg_replace("/\b([\pL0-9\._\-]+@[\pL0-9\.\-_]+\.[a-z]{2,4})(\s)/ui", "{lt}a class={quot}normal-link{quot} href={quot}mailto:$1{quot}{gt}$1{lt}/a{gt}$2", $text);
		}

		$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
		$text = nl2br(trim($text));
		//$text = str_replace("\r", "", $text);
		//$text = str_replace("\n", "", $text);
		
		//we dont use < and > directly with the preg functions because htmlspecialchars will screw it up. We don't want to use
		//htmlspecialchars before the pcre functions because email address like <mschering@intermesh.nl> will fail.
		
		$text = str_replace("{quot}", '"', $text);
		$text = str_replace("{lt}", "<", $text);
		$text = str_replace("{gt}", ">", $text);

		return ($text);
	}
	
	function html_to_text($text, $link_list=true){
		global $GO_CONFIG;
		require_once($GO_CONFIG->class_path.'html2text.class.inc');
		
		$htmlToText = new Html2Text ($text);
		return $htmlToText->get_text($link_list);	
	}

	/**
	 * Convert Dangerous HTML to safe HTML for display inside of Group-Office
	 *
	 * This also removes everything outside the body and replaces mailto links
	 *
	 * @param	string $text Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */
	function convert_html($html, $block_external_images=false, $replace_count=0) {

		$html = str_replace("\r", '', $html);
		$html = str_replace("\n",' ', $html);		

		//remove strange white spaces in tags first
		//sometimes things like this happen <style> </ style >
		$html = preg_replace("'</[\s]*([\w]*)[\s]*>'","</$1>", $html);

		$to_removed_array = array (
		"'<html[^>]*>'si",
		"'</html>'si",
		"'<body[^>]*>'si",
		"'</body>'si",
		"'<meta[^>]*>'si",
		"'<head[^>]*>.*?</head>'si",
		"'<style[^>]*>.*?</style>'si",
		"'<script[^>]*>.*?</script>'si",
		"'<iframe[^>]*>.*?</iframe>'si",
		"'<object[^>]*>.*?</object>'si",
		"'<embed[^>]*>.*?</embed>'si",
		"'<applet[^>]*>.*?</applet>'si",
		"'<form[^>]*>'si",
		"'<input[^>]*>'si",
		"'<select[^>]*>.*?</select>'si",
		"'<textarea[^>]*>.*?</textarea>'si",
		"'</form>'si"
		);
	
		$html = preg_replace($to_removed_array, '', $html);
		$html = preg_replace("/([\"']?)javascript:/i", "$1removed_script:", $html);
	
		if($block_external_images)
		{
			//$html = preg_replace("/<img(.*)src=([\"']?)http([^>])/", "<img$1src=$2blocked:http$3", $html);
			//$html = preg_replace("/<([^=]*)=[\"']?http[^\"'\s>]*/", "<$1=\"blocked\"", $html);
			$html = preg_replace("/<([^aA]{1})([^>]*)https?:([^>]*)/", "<$1$2blocked:$3", $html, -1, $replace_count);
		}
	
		return $html;
	}

	/**
	 * Change HTML links to Group-Office links. For example mailto: links will call
	 * the Group-Office e-mail module if installed.
	 *
	 *
	 * @param	string $text Plain text string
	 * @access public
	 * @return string HTML formatted string
	 */

	function convert_links($html)
	{
		global $GO_CONFIG, $GO_MODULES;

		$html = str_replace("\r", '', $html);
		$html = str_replace("\n",' ', $html);

		$regexp="/<a[^>]*href=([\"']?)(http|https|ftp|bf2)(:\/\/)(.+?)>/i";
		$html = preg_replace($regexp, "<a target=$1_blank$1 class=$1blue$1 href=$1$2$3$4>", $html);

		//$regexp="/<a.+?href=([\"']?)".str_replace('/','\\/', $GO_CONFIG->full_url)."(.+?)>/i";
		//$html = preg_replace($regexp, "<a target=$1main$1 class=$1blue$1 href=$1".$GO_CONFIG->host."$2$3>", $html);

		$html =str_replace($GO_CONFIG->full_url, $GO_CONFIG->host, $html);

		if ($GO_MODULES->modules['email'] && $GO_MODULES->modules['email']['read_permission']) {
			$html = preg_replace("/(href=([\"']?)mailto:)([\w\.\-]+)(@)([\w\.\-\"]+)\b/i",
			"href=\"javascript:this.showComposer({values: {to : '$3$4$5'}});", $html);
		}
	
		

		return $html;
	}


	/**
	 * Quotes a string with >
	 *
	 * @param	string $text
	 * @access public
	 * @return string A string quoted with >
	 */
	function quote($text) {
		$text = "> ".ereg_replace("\n", "\n> ", trim($text));
		return ($text);
	}


	/**
	 * Used by icalendar convertor
	 *
	 * @param unknown_type $sText
	 * @param unknown_type $bEmulate_imap_8bit
	 * @return unknown
	 */

	function quoted_printable_encode($sText,$bEmulate_imap_8bit=true) {
		// split text into lines
		
		$sText = str_replace("\r", '', $sText);
		
		$aLines=explode("\n",$sText);
		
		//var_dump($aLines);

		for ($i=0;$i<count($aLines);$i++) {
			$sLine =& $aLines[$i];
			if (strlen($sLine)===0) continue; // do nothing, if empty

			$sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

			// imap_8bit encodes x09 everywhere, not only at lineends,
			// for EBCDIC safeness encode !"#$@[\]^`{|}~,
			// for complete safeness encode every character :)
			if ($bEmulate_imap_8bit)
			$sRegExp = '/[^\x21-\x3C\x3E-\x7E]/e';

			$sReplmt = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
			$sLine = preg_replace( $sRegExp, $sReplmt, $sLine );

			// encode x09,x20 at lineends
			{
				$iLength = strlen($sLine);
				$iLastChar = ord($sLine{$iLength-1});

				//              !!!!!!!!
				// imap_8_bit does not encode x20 at the very end of a text,
				// here is, where I don't agree with imap_8_bit,
				// please correct me, if I'm wrong,
				// or comment next line for RFC2045 conformance, if you like
				if (!($bEmulate_imap_8bit && ($i==count($aLines)-1)))

				if (($iLastChar==0x09)||($iLastChar==0x20)) {
					$sLine{$iLength-1}='=';
					$sLine .= ($iLastChar==0x09)?'09':'20';
				}
			}    // imap_8bit encodes x20 before chr(13), too
			// although IMHO not requested by RFC2045, why not do it safer :)
			// and why not encode any x20 around chr(10) or chr(13)
			if ($bEmulate_imap_8bit) {
				$sLine=str_replace(' =0D','=20=0D',$sLine);
				$sLine=str_replace(' =0A','=20=0A',$sLine);
				$sLine=str_replace('=0D ','=0D=20',$sLine);
				$sLine=str_replace('=0A ','=0A=20',$sLine);
			}

			//merijn$sLine  = str_replace(' ','=20',$sLine);

			// finally split into softlines no longer than 76 chars,
			// for even more safeness one could encode x09,x20
			// at the very first character of the line
			// and after soft linebreaks, as well,
			// but this wouldn't be caught by such an easy RegExp
			
			//preg_match_all( '/.{1,73}([^=]{0,2})?/', $sLine, $aMatch );
			//$sLine = implode( '=' . chr(13).chr(10), $aMatch[0] ); // add soft crlf's
		}

		// join lines into text
		return implode('=0D=0A',$aLines);
	}


}