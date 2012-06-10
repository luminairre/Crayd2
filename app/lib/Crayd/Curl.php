<?

/**
 * Crayd Curl
 * 
 * Curl Helper
 * 
 * @author vee@gate00.net
 */
class Crayd_Curl {
    // Useragent list
    const USERAGENT_FIREFOX = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:13.0) Gecko/20100101 Firefox/13.0';
    const USERAGENT_SAFARI = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/534.57.2 (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2';
    const USERAGENT_CHROME = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_4) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7';
    const USERAGENT_IPHONE = 'Mozilla/5.0 (iPhone; CPU iPhone OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9B206 Safari/7534.48.3';
    const USERAGENT_IPAD = 'Mozilla/5.0 (iPad; CPU OS 5_1_1 like Mac OS X) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Mobile/9A334 Safari/7534.48.3';
    const USERAGENT_ANDROID = 'Mozilla/5.0 (Linux; U; Android 4.0.1; en-us; sdk Build/ICS_MR0) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';

    var $ch;
    public $timeout = 10;
    public $params = array();
    public $method = 'get';
    public $useragent = '';
    public $referer;
    public $status = '';
    public $return = '';

    /**
     * Init
     */
    public function __construct() {
        
    }

    public function post($url, $params = array()) {
        $this->method = 'post';
        return $this->exec($url, $params);
    }

    public function get($url, $params = array()) {
        return $this->exec($url, $params);
    }

    public function delete($url, $params = array()) {
        $this->method = 'delete';
        return $this->exec($url, $params);
    }

    public function put($url, $params = array()) {
        $this->method = 'put';
        return $this->exec($url, $params);
    }

    /**
     * OVERWRITES parameter
     * @param array $params 
     */
    public function setParam($params = array()) {
        $this->params = $params;
    }

    /**
     * Add parameter
     */
    public function addParam($key, $value) {
        $this->params[$key] = $value;
    }

    /**
     * Upload a file, use fullpath
     * @param string $path 
     */
    public function file($key, $path) {
        if (substr($path, 0, 1) != '@') {
            $this->params[$key] = '@' . $path;
        }
    }

    public function exec($url, $params) {
        // init
        $ch = curl_init();

        // merge parameters
        if (count($params) > 0) {
            $this->params = array_merge($this->params, $params);
        }

        // set timeout
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);

        // set expect
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));

        // always return transfer
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // set useragent
        if (!empty($this->useragent)) {
            curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);
        }

        // set referer
        if (!empty($this->referer)) {
            curl_setopt($ch, CURLOPT_REFERER, $this->referer);
        }
        
        // set ignore ssl
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        switch ($this->method) {
            case 'post':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
                break;
            case 'get':
                if (count($this->params) > 0) {
                    if (strpos($url, '?') !== false) {
                        $url .= '&' . http_build_query($this->params);
                    } else {
                        $url .= '?' . http_build_query($this->params);
                    }
                }
                break;
            case 'delete':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'put':
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->params);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                break;
        }
        
        // set URL
        curl_setopt($ch, CURLOPT_URL, $url);
        
        $this->return = curl_exec($ch);
        $this->status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return $this->return;
    }

}