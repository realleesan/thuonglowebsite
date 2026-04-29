<?php
// Test update parent_id - bypass auth
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('DB_HOST', 'localhost');
define('DB_NAME', 'thuonglowebsite');
define('DB_USER', 'root');
define('DB_PASS', '');

require __DIR__ . '/core/database.php';
require __DIR__ . '/app/models/BaseModel.php';
require __DIR__ . '/app/models/CategoriesModel.php';

$model = new CategoriesModel();

if ($_POST) {
    $id = (int)$_POST['id'];
    $parent_id = $_POST['parent_id'] === '' ? null : (int)$_POST['parent_id'];
    
    echo "ID: $id, New parent_id: " . var_export($parent_id, true) . "<hr>";
    
    $result = $model->update($id, ['parent_id' => $parent_id]);
    
    echo "Update result: " . ($result ? 'OK' : 'FAIL') . "<br>";
    
    $cat = $model->find($id);
    echo "Current parent_id in DB: " . var_export($cat['parent_id'], true);
} else {
    $cats = $model->getActive();
    echo '<form method="post">';
    echo 'Danh muc: <select name="id">';
    foreach ($cats as $c) {
        echo '<option value="'.$c['id'].'">'.$c['name'].' (current parent:'.($c['parent_id']??'NULL').')</option>';
    }
    echo '</select><br><br>';
    echo 'Parent moi: <select name="parent_id"><option value="">-- NULL (khong co cha) --</option>';
    foreach ($cats as $c) {
        echo '<option value="'.$c['id'].'">'.$c['name'].'</option>';
    }
    echo '</select><br><br>';
    echo '<button>Update</button></form>';
}
