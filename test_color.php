<?php
require 'app/services/FilterConfigService.php'; 
$f = new FilterConfigService(); 
$cats = $f->getCategoriesForFilter(); 
print_r($cats[0]);
