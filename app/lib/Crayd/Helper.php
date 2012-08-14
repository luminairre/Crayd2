<?

/**
 * Helper class
 * 
 * Dunno where to put these D=
 * 
 * @author vee@gate00.net
 */
class Crayd_Helper {

    /**
     * Browser detector
     * Taken from SMF
     */
    static public function detectBrowser() {

        // The following determines the user agent (browser) as best it can.
        $return = array(
            'is_opera' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== false,
            'is_opera6' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 6') !== false,
            'is_opera7' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 7') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera/7') !== false,
            'is_opera8' => strpos($_SERVER['HTTP_USER_AGENT'], 'Opera 8') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera/8') !== false,
            'is_opera9' => preg_match('~Opera[ /]9(?!\\.[89])~', $_SERVER['HTTP_USER_AGENT']) === 1,
            'is_opera10' => preg_match('~Opera[ /]10\\.~', $_SERVER['HTTP_USER_AGENT']) === 1 || (preg_match('~Opera[ /]9\\.[89]~', $_SERVER['HTTP_USER_AGENT']) === 1 && preg_match('~Version/1[0-9]\\.~', $_SERVER['HTTP_USER_AGENT']) === 1),
            'is_ie4' => strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 4') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'WebTV') === false,
            'is_webkit' => strpos($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') !== false,
            'is_mac_ie' => strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.') !== false && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') !== false,
            'is_web_tv' => strpos($_SERVER['HTTP_USER_AGENT'], 'WebTV') !== false,
            'is_konqueror' => strpos($_SERVER['HTTP_USER_AGENT'], 'Konqueror') !== false,
            'is_firefox' => preg_match('~(?:Firefox|Ice[wW]easel|IceCat)/~', $_SERVER['HTTP_USER_AGENT']) === 1,
            'is_firefox1' => preg_match('~(?:Firefox|Ice[wW]easel|IceCat)/1\\.~', $_SERVER['HTTP_USER_AGENT']) === 1,
            'is_firefox2' => preg_match('~(?:Firefox|Ice[wW]easel|IceCat)/2\\.~', $_SERVER['HTTP_USER_AGENT']) === 1,
            'is_firefox3' => preg_match('~(?:Firefox|Ice[wW]easel|IceCat|Shiretoko|Minefield)/3\\.~', $_SERVER['HTTP_USER_AGENT']) === 1,
            'is_iphone' => strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== false,
            'is_ipad' => strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false,
            'is_android' => strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false,
            'is_midp' => strpos($_SERVER['HTTP_USER_AGENT'], 'MIDP') !== false,
            'is_symbian' => strpos($_SERVER['HTTP_USER_AGENT'], 'Symbian') !== false,
        );

        $return['is_chrome'] = $return['is_webkit'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome') !== false;
        $return['is_safari'] = !$return['is_chrome'] && strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') !== false;
        $return['is_gecko'] = strpos($_SERVER['HTTP_USER_AGENT'], 'Gecko') !== false && !$return['is_webkit'] && !$return['is_konqueror'];

        // Internet Explorer 5 and 6 are often "emulated".
        $return['is_ie8'] = !$return['is_opera'] && !$return['is_gecko'] && !$return['is_web_tv'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8') !== false;
        $return['is_ie7'] = !$return['is_opera'] && !$return['is_gecko'] && !$return['is_web_tv'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7') !== false && !$return['is_ie8'];
        $return['is_ie6'] = !$return['is_opera'] && !$return['is_gecko'] && !$return['is_web_tv'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') !== false && !$return['is_ie8'] && !$return['is_ie7'];
        $return['is_ie5.5'] = !$return['is_opera'] && !$return['is_gecko'] && !$return['is_web_tv'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.5') !== false;
        $return['is_ie5'] = !$return['is_opera'] && !$return['is_gecko'] && !$return['is_web_tv'] && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 5.0') !== false;

        $return['is_ie'] = $return['is_ie4'] || $return['is_ie5'] || $return['is_ie5.5'] || $return['is_ie6'] || $return['is_ie7'] || $return['is_ie8'];
        // Before IE8 we need to fix IE... lots!
        $return['ie_standards_fix'] = !$return['is_ie8'];

        $return['needs_size_fix'] = ($return['is_ie5'] || $return['is_ie5.5'] || $return['is_ie4'] || $return['is_opera6']) && strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') === false;

        // This isn't meant to be reliable, it's just meant to catch most bots to prevent PHPSESSID from showing up.
        $return['possibly_robot'] = !empty($user_info['possibly_robot']);

        // Robots shouldn't be logging in or registering.  So, they aren't a bot.  Better to be wrong than sorry (or people won't be able to log in!), anyway.
        if ((isset($_REQUEST['action']) && in_array($_REQUEST['action'], array('login', 'login2', 'register'))) || !$user_info['is_guest'])
            $return['possibly_robot'] = false;


        if (strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'Blackberry') !== false) {
            $return['is_blackberry'] = 1;
            if (strpos($_SERVER['HTTP_USER_AGENT'], '6.0') !== false) {
                $return['is_blackberry_os6'] = 1;
            } else if (strpos($_SERVER['HTTP_USER_AGENT'], '5.0') !== false) {
                $return['is_blackberry_os5'] = 1;
            } else {
                $return['is_blackberry_os4'] = 1;
            }
        }


        return $return;
    }

    /**
     * Generates random character
     * @param int $lengthchar
     * @param string $characters
     * @return string
     */
    static public function generateRandom($lengthchar, $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890') {
        srand((double) microtime() * 1000000);
        $i = 0;
        $pass = '';
        $length = strlen($characters) - 1;

        while ($i < $lengthchar) {
            $num = rand() % $length;
            $tmp = substr($characters, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return substr($pass, 0, $lengthchar);
    }

    /**
     * Filters a variable, spaces to dashes, other than alphanumeric will be stripped
     * @param string $string
     * @return string
     */
    public static function alnum($string) {
        $string = str_replace(' ', '-', htmlspecialchars_decode(strtolower(trim($string)), ENT_QUOTES));
        $new_string = preg_replace("/[^A-Za-z0-9\-]/i", "", $string);
        $new_string = str_replace('---', '-', $new_string);
        $new_string = str_replace('--', '-', $new_string);
        return $new_string;
    }

    /**
     * returns basic browser engine
     */
    public static function getBrowser() {
        $navigator_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($navigator_user_agent, 'trident') !== false)
            return 'trident';
        
        if (strpos($navigator_user_agent, "webkit") !== false)
            return 'webkit';
        
        if (strpos($navigator_user_agent, "presto") !== false)
            return 'presto';
        
        if (strpos($navigator_user_agent, "gecko") !== false)
            return 'gecko';
        
        if (strpos($navigator_user_agent, "robot") !== false)
            return 'robot';
        if (strpos($navigator_user_agent, "spider") !== false)
            return 'robot';
        if (strpos($navigator_user_agent, "bot") !== false)
            return 'robot';
        if (strpos($navigator_user_agent, "crawl") !== false)
            return 'robot';
        if (strpos($navigator_user_agent, "search") !== false)
            return 'robot';
        
        if (strpos($navigator_user_agent, "w3c_validator") !== false)
            return 'validator';
        if (strpos($navigator_user_agent, "jigsaw") !== false)
            return 'validator';
        
        return 'unknownengine';
    }

    /**
     * returns basic platform infomation
     */
    public static function getPlatform() {
        $navigator_user_agent = strtolower($_SERVER['HTTP_USER_AGENT']);

        if (strpos($navigator_user_agent, 'linux') !== false)
            return 'linux';

        if (strpos($navigator_user_agent, 'mac') !== false)
            return 'mac';

        if (strpos($navigator_user_agent, 'win') !== false)
            return 'windows';

        return 'unknownplatform';
    }

    /**
     * Validates $var
     * Methods are: required, alnum, array, email, max, min, url
     * Values are array, for now its
     *   max => integer
     *   min => integer
     * @param mixed $var
     * @param array $methods 
     * @param array $values
     * @return boolean
     */
    public static function validate($var, $methods, $values = array()) {

        if (in_array('required', $methods)) {
            if (empty($var))
                return false;
        }

        if (in_array('array', $methods)) {
            if (!is_array($var))
                return false;
            if (count($var) < 1)
                return false;
        }

        if (in_array('email', $methods) && isset($var)) {
            if (!filter_var($var, FILTER_VALIDATE_EMAIL))
                return false;
        }

        if (in_array('max', $methods) && isset($values['max']) && isset($var)) {
            if ($var > $values['max'])
                return false;
        }

        if (in_array('min', $methods) && isset($values['min']) && isset($var)) {
            if ($var < $values['min'])
                return false;
        }

        if (in_array('url', $methods) && isset($var)) {
            if (!filter_var($var, FILTER_VALIDATE_URL))
                return false;
        }

        if (in_array('alnum', $methods) && isset($var)) {
            if (preg_match("/^[A-Za-z0-9]+$/", $var) < 1)
                return false;
        }

        return true;
    }

    /**
     * Converts filesize
     * Kudos to Alix Axel @ stackoverflow
     */
    static public function filesize($bytes) {
        if ($bytes > 0) {
            $unit = intval(log($bytes, 1024));
            $units = array('B', 'KB', 'MB', 'GB');

            if (array_key_exists($unit, $units) === true) {
                return sprintf('%d %s', $bytes / pow(1024, $unit), $units[$unit]);
            }
        }

        return $bytes;
    }

}