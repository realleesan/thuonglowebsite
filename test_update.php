<?php
// Test update parent_id
require 'app/config/config.php';
require 'app/models/CategoriesModel.php';

$model = new CategoriesModel();

if ($_POST) {
    $id = (int)$_POST['id'];
    $parent_id = $_POST['parent_id'] === '' ? null : (int)$_POST['parent_id'];
    
    $result = $model->update($id, ['parent_id' => $parent_id]);
    
    echo "Update result: " . ($result ? 'OK' : 'FAIL') . "<br>";
    
    $cat = $model->find($id);
    echo "Current parent_id: " . var_export($cat['parent_id'], true);
} else {
    $cats = $model->getActive();
    echo '<form method="post">';
    echo 'Danh muc: <select name="id">';
    foreach ($cats as $c) {
        echo '<option value="'.$c['id'].'">'.$c['name'].' (parent:'.($c['parent_id']??'NULL').')</option>';
    }
    echo '</select><br>';
    echo 'Parent moi: <select name="parent_id"><option value="">NULL</option>';
    foreach ($cats as $c) {
        echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
    }
    echo '</select><br>';
    echo '<button>Update</button></form>';
}
