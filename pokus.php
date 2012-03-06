<?php
require_once('./PSWebServiceLibrary.php');
$webService = new PrestaShopWebservice('http://localhost/prestashop','JP8A7MJWKUD26WZNETUL5JIOF9CP7E1V', false);

$xml = $webService->get(array('resource' => 'categories'));

$resources = $xml->categories->children();
foreach ($resources as $key => $resource)
echo 'Name of field : '.$key.' - Value : '.$resource.'<br />';

print_r($xml);



?>

