<?php

namespace proxier;


abstract class BaseSiteCom
{

    protected $errors = array();
    protected $parsedProxies = array();
    protected $config = array();
    protected $lastPage = 1;

    protected $curlObject = null;
    protected $curlError = null;
    protected $curlResult = null;
    protected $curlOptions = array();
    protected $curlInfo = array();
    protected $options = array();

    public function __construct($options = array())
    {
        $this->options = $options;
    }

    abstract public function parse();

    /**
     * @param $error
     * @return $this
     */
    public function setError($error)
    {
        $this->errors[] = $error;
        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getLastError()
    {
        if ($this->errors) {
            return end($this->errors);
        }
        return null;
    }

    /**
     * @return $this
     */
    protected function setCurlTor()
    {
        $this->curlOptions[CURLOPT_AUTOREFERER] = 1;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;
        $this->curlOptions[CURLOPT_PROXY] = '127.0.0.1:' . ($this->config['curlTorPort'] ? (int)$this->config['curlTorPort'] : 9050);
        $this->curlOptions[CURLOPT_PROXYTYPE] = 7;
        $this->curlOptions[CURLOPT_TIMEOUT] = 120;
        $this->curlOptions[CURLOPT_VERBOSE] = 0;
        $this->curlOptions[CURLOPT_HEADER] = 0;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param $url
     * @return $this
     */
    protected function curlInit($url)
    {
        $this->resetCurl();
        $this->setCurlDefaultOptions();
        $this->curlOptions[CURLOPT_URL] = $url;
        if (strpos($url, 'https:') === 0) {
            //ssl
            $this->setCurlOption(CURLOPT_SSL_VERIFYHOST, 0);
            $this->setCurlOption(CURLOPT_SSL_VERIFYPEER, 0);
        }
        if (isset($this->options['tor']) && $this->options['tor']) {
            $this->setCurlTor();
        }
        if (!$this->curlObject) {
            $this->curlObject = curl_init($url);
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function runCurl()
    {
        try {
            curl_setopt_array($this->curlObject, $this->curlOptions);
            $this->curlResult = curl_exec($this->curlObject);
            $this->curlError = curl_error($this->curlObject);
            $this->curlInfo = curl_getinfo($this->curlObject);
            curl_close($this->curlObject);
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            $this->curlError = $e->getMessage();
        }

        return $this;
    }

    /**
     * @param $key
     * @param $val
     * @return $this
     */
    protected function setCurlOption($key, $val)
    {
        $this->curlOptions[$key] = $val;
        return $this;
    }

    /**
     * @return $this
     */
    protected function setCurlDefaultOptions()
    {
        $this->curlOptions[CURLOPT_USERAGENT] = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.106 Safari/537.36";
        $this->curlOptions[CURLOPT_TIMEOUT] = 60;
        $cookie = "proxy_cookie.txt";
        $this->curlOptions[CURLOPT_COOKIEJAR] = $cookie;
        $this->curlOptions[CURLOPT_COOKIE] = $cookie;
        $this->curlOptions[CURLOPT_FOLLOWLOCATION] = 1;
        $this->curlOptions[CURLOPT_RETURNTRANSFER] = 1;

        return $this;
    }

    /**
     * @return $this
     */
    protected function resetCurl()
    {
        $this->curlObject = null;
        $this->curlOptions = array();
        $this->curlInfo = array();
        $this->curlError = null;
        $this->curlResult = null;
        return $this;
    }

    /**
     * @return array
     */
    public function getParsedProxies()
    {
        return $this->parsedProxies;
    }

    /**
     * @param $idTable
     * @param int $numberIpTd
     * @param int $numberPortTd
     * @param bool $class
     * @param bool $noBody
     * @return bool
     */
    public function parseTable($idTable, $numberIpTd = 0, $numberPortTd = 1, $class = false, $noBody = false)
    {
        $this->curlInit($this->config['baseUrl']);
        $this->runCurl();
        if ($this->curlError) {
            return false;
        }

        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->validateOnParse = true;
        @$dom->loadHTML($this->curlResult);

        if ($class) {
            $a = new \DOMXPath($dom);
            $spans = $a->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $idTable ')]");
            for ($i = $spans->length - 1; $i > -1; $i--) {
                $table = $spans->item($i);

            }
        } else {
            $table = $dom->getElementById($idTable);
        }

        if (!isset($table) || !$table) {
            return false;
        }
        if ($noBody)
            $rows = $table->getElementsByTagName('tr');
        else {
            $tbody = $table->getElementsByTagName('tbody');

            if (!$tbody || !$tbody->length) {
                return false;
            }
            $body = $tbody->item(0);
            $rows = $body->getElementsByTagName('tr');
        }
        if (!$rows || !$rows->length) {
            return false;
        }
        foreach ($rows as $row) {
            $cells = $row->getElementsByTagName('td');
            if (!$cells || !$cells->length) {
                continue;
            }
            $rowData = array();
            foreach ($cells as $i => $td) {
                $rowData[(string)$i] = $td->textContent;
            }
            $this->parsedProxies[] = $rowData[$numberIpTd] . ":" . $rowData[$numberPortTd];
        }
    }

    /**
     * @param $proxy
     * @return bool
     */
    function testProxy($proxy)
    {
        $splited = explode(':', $proxy); // Separate IP and port
        if ($con = @fsockopen($splited[0], $splited[1], $eroare, $eroare_str, 3)) {
            fclose($con); // Close the socket handle
            return true;
        }

        return false;
    }
}