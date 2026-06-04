<?php
require_once 'core/view_init.php';
require_once 'app/models/ProductDataModel.php';
$m = new ProductDataModel();
print_r($m->getByProduct(1006));
