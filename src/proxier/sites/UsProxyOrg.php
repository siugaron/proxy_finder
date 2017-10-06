<?php

namespace proxier\sites;


use proxier\BaseSiteCom;

/**
 * Все прокси выдаются сразу, без пейджинга
 * Затем через js они преобрауются на странице, но в курле имеют отличный от браузерного вид
 * Class UsProxyOrg
 * @package proxier\sites
 */
class UsProxyOrg extends BaseSiteCom
{

    protected $config = array('baseUrl' => 'https://www.us-proxy.org/');

    public function parse()
    {
        $idTable = "proxylisttable";
        $this->parseTable($idTable);
    }
}