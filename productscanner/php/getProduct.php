<?php
/**
 * Load product's data from sku (barcode)
 * Return name and final price
 */
use Magento\Framework\App\Bootstrap;

try{
    $sku = trim($_POST['sku']);
    if(!preg_match('/[0-9]+/', $sku)) exit('Invalid sku '.$sku);

    $envFilePath = __DIR__ . '/../../app/etc/env.php';

    require __DIR__ . '/../../app/bootstrap.php';

    $params = $_SERVER;

    $bootstrap = Bootstrap::create(BP, $params);
    $objectManager = $bootstrap->getObjectManager();

    $objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

    $product = $objectManager
        ->get('Magento\Catalog\Model\ProductRepository')
        ->get($sku);

    $result = "<p>".$product->getName()."</p>";
    $result .= "<p>".$product->getFinalPrice()."</p>";

    echo $result;
}catch (Exception $e){
    echo $e->getMessage();
}
