<?php

use Magento\Framework\App\Bootstrap;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Filesystem;

$envFilePath = __DIR__ . '/../app/etc/env.php';

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;

$bootstrap = Bootstrap::create(BP, $params);
$objectManager = $bootstrap->getObjectManager();

$fileSystem = $objectManager->create('\Magento\Framework\Filesystem');
$rootPath = $fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::ROOT)->getAbsolutePath();
//echo $rootPath;
//exit;
//$directoryList = $objectManager->get('Magento\Framework\App\Filesystem\DirectoryList');
$path = str_replace($rootPath, '', str_replace('\\', '/', __DIR__) . '/../_files/design');
//exit;
//$directoryList->addDirectory(\Magento\Framework\App\Filesystem::THEMES_DIR, array('path' => ltrim($path, '/')));
$_design = $objectManager->get('Magento\\Framework\\View\\DesignInterface');
$objectManager->get('Magento\\Framework\\App\\State')->setAreaCode(\Magento\Framework\View\DesignInterface::DEFAULT_AREA);
$_design->setDesignTheme('Magento/luma', 'frontend');
/** @var \Magento\Framework\View\Asset\Repository $assetRepo */
//$assetRepo = $objectManager->get('Magento\\Framework\\View\\Asset\\Repository');
//$quickStylesPath = $assetRepo->createAsset('Magento_DesignEditor::controls/quick_styles.xml')->getSourceFile();
//$this->assertFileExists($quickStylesPath);
//$this->_model = $objectManager->create('Magento\\DesignEditor\\Model\\Config\\Control\\QuickStyles', array('configFiles' => array(file_get_contents($quickStylesPath))));

?>
<html>
<head>
<body>

</body>
</html>

