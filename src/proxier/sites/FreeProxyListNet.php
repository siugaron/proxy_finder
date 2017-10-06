<?php
/**
 * Created by PhpStorm.
 * User: Bond
 * Date: 06.10.2017
 * Time: 11:01
 */

namespace proxier\sites;

use proxier\BaseSiteCom;

class FreeProxyListNet extends BaseSiteCom
{
    protected $config = array('baseUrl' => 'https://free-proxy-list.net/');

    function parse() {
        $idTable = "proxylisttable";
        $this->parseTable($idTable);
    }
}