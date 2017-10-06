<?php
/**
 * Created by PhpStorm.
 * User: Bond
 * Date: 06.10.2017
 * Time: 10:56
 */
include_once "../ProxySearcher.php";
include_once "../BaseSiteCom.php";

include_once "../sites/ProxyListeDe.php";

include_once "../sites/UsProxyOrg.php";
include_once "../sites/SslProxiesOrg.php";
include_once "../sites/FreeProxyListNet.php";
$test = new \proxier\ProxySearcher();
$proxy = $test->run();
var_dump($proxy);