<?php
/**
 * autoloader components to instantiate slidingBanner contribution
 */
$autoLoadConfig[220][] = array('autoType'=>'class', 'loadFile'=> 'observers/class.fulltextSearch.php');
$autoLoadConfig[230][] = array('autoType'=>'classInstantiate', 'className'=> 'fullTextSearch', 'objectName'=> 'fullTextSearch');

?>