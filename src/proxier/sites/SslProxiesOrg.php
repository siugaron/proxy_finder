<?php
/**
 * Created by PhpStorm.
 * User: Bond
 * Date: 06.10.2017
 * Time: 12:09
 */

namespace proxier\sites;

use proxier\BaseSiteCom;

class SslProxiesOrg extends BaseSiteCom
{
    protected $config = array('baseUrl' => 'https://www.sslproxies.org/');

    function parse()
    {
        $idTable = "proxylisttable";
        $this->parseTable($idTable);
    }
}