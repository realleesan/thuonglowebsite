<?php
/**
 * Test form for image URL submission
 * 
 * Chạy: http://test1.web3b.com/test_image_form.php?id=2
 */

require_once __DIR__ . '/core/view_init.php';
require_once __DIR__ . '/app/models/NewsModel.php';

$service = isset($currentService) ? $currentService : ($adminService ?? null);

if (!$service) {
    die("ERROR: Service not available");
}

$news_id = (int)($_GET['id'] ?? 0);

// Get current news data
$newsModel = new NewsModel();
$currentNews = $newsModel->find($news_id);

$current_image = $currentNews['image'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Image URL Form</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        .current-image { margin-top: 10px; }
        .current-image img { max-width: 200px; }
        .debug { background: #f8f9fa; padding: 10px; margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Test Image URL Submission</h1>
    
    <p>News ID: <?= $news_id ?></p>
    
    <div class="current-image">
        <strong>Current Image:</strong><br>
        <?php if ($current_image): ?>
            <img src="<?= htmlspecialchars($current_image) ?>" alt="Current">
            <br>URL: <?= htmlspecialchars($current_image) ?>
        <?php else: ?>
            <em>No image</em>
        <?php endif; ?>
    </div>
    
    <form method="POST" action="?page=admin&module=news&action=edit&id=<?= $news_id ?>">
        <div class="form-group">
            <label for="title">Title:</label>
            <input type="text" id="title" name="title" value="<?= htmlspecialchars($currentNews['title'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="slug">Slug:</label>
            <input type="text" id="slug" name="slug" value="<?= htmlspecialchars($currentNews['slug'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="5"><?= htmlspecialchars($currentNews['content'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="excerpt">Excerpt:</label>
            <textarea id="excerpt" name="excerpt" rows="3"><?= htmlspecialchars($currentNews['excerpt'] ?? '') ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="author">Author:</label>
            <input type="text" id="author" name="author" value="<?= htmlspecialchars($currentNews['author_name'] ?? '') ?>">
        </div>
        
        <div class="form-group">
            <label for="image_url">Image URL (NEW):</label>
            <input type="text" id="image_url" name="image_url" value="" placeholder="Enter new image URL here...">
        </div>
        
        <button type="submit">Submit</button>
    </form>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="debug">
            <strong>POST data received:</strong>
            <?php print_r($_POST); ?>
        </div>
    <?php endif; ?>
</body>
</html>
