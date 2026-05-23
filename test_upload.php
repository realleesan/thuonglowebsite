<?php
/**
 * Diagnostic tool for testing image upload issues.
 * Open this in your browser at: http://localhost/thuonglowebsite/test_upload.php
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$upload_dir = 'assets/images/products/';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Diagnostic Upload Test</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; max-width: 800px; margin: 40px auto; padding: 20px; background: #f9fafb; color: #111827; }
        h1, h2 { color: #1f2937; margin-top: 1.5em; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; }
        pre { background: #f3f4f6; padding: 16px; border-radius: 8px; overflow-x: auto; border: 1px solid #e5e7eb; font-size: 14px; }
        .success { color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 12px; border-radius: 6px; font-weight: bold; }
        .error { color: #b91c1c; background: #fef2f2; border: 1px solid #fecaca; padding: 12px; border-radius: 6px; font-weight: bold; }
        .info { background: #eff6ff; border: 1px solid #bfdbfe; padding: 12px; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        td, th { padding: 10px; border: 1px solid #e5e7eb; text-align: left; }
        th { background: #f3f4f6; }
        form { background: white; padding: 24px; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        button { background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-size: 16px; cursor: pointer; }
        button:hover { background: #1d4ed8; }
    </style>
</head>
<body>
<h1>Diagnostic File Upload Utility</h1>";

// 1. Check Directory and Permissions
echo "<h2>1. Environment and Permissions Check</h2>";
echo "<table>
    <thead>
        <tr>
            <th>Parameter</th>
            <th>Value / Status</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Current Working Directory</td>
            <td><code>" . htmlspecialchars(getcwd()) . "</code></td>
        </tr>
        <tr>
            <td>Upload Target Path</td>
            <td><code>" . htmlspecialchars($upload_dir) . "</code></td>
        </tr>
        <tr>
            <td>Is <code>assets/</code> writable?</td>
            <td>" . (is_writable('assets') ? "<span style='color:green;'>YES</span>" : "<span style='color:red;'>NO</span>") . "</td>
        </tr>
        <tr>
            <td>Is <code>assets/images/</code> writable?</td>
            <td>" . (is_writable('assets/images') ? "<span style='color:green;'>YES</span>" : "<span style='color:red;'>NO</span>") . "</td>
        </tr>
        <tr>
            <td>Does <code>assets/images/products/</code> exist?</td>
            <td>" . (is_dir($upload_dir) ? "<span style='color:green;'>YES</span>" : "<span style='color:orange;'>NO (Will try to create)</span>") . "</td>
        </tr>";

if (!is_dir($upload_dir)) {
    // Try to create the directory
    $created = @mkdir($upload_dir, 0755, true);
    echo "<tr>
            <td>Directory Creation Attempt</td>
            <td>" . ($created ? "<span style='color:green;'>SUCCESS</span>" : "<span style='color:red;'>FAILED</span>") . "</td>
        </tr>";
}

echo "<tr>
            <td>Is <code>assets/images/products/</code> writable?</td>
            <td>" . (is_writable($upload_dir) ? "<span style='color:green;'>YES</span>" : "<span style='color:red;'>NO</span>") . "</td>
        </tr>
        <tr>
            <td>PHP Upload Max Filesize</td>
            <td><code>" . ini_get('upload_max_filesize') . "</code></td>
        </tr>
        <tr>
            <td>PHP Post Max Size</td>
            <td><code>" . ini_get('post_max_size') . "</code></td>
        </tr>
    </tbody>
</table>";

// 2. Process Upload if submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>2. Upload Attempt Diagnostics</h2>";
    echo "<div class='info'>Form submitted. Raw <code>\$_FILES</code> array:</div>";
    echo "<pre>" . htmlspecialchars(print_r($_FILES, true)) . "</pre>";
    
    if (isset($_FILES['image_file'])) {
        $error_code = $_FILES['image_file']['error'];
        echo "<p>PHP Upload Status Code: <strong>$error_code</strong> — " . getUploadErrorString($error_code) . "</p>";
        
        if ($error_code === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image_file']['tmp_name'];
            $file_name = $_FILES['image_file']['name'];
            $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            
            echo "<table>
                <tr>
                    <th>Detail</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Original Filename</td>
                    <td><code>" . htmlspecialchars($file_name) . "</code></td>
                </tr>
                <tr>
                    <td>Detected Extension</td>
                    <td><code>" . htmlspecialchars($ext) . "</code></td>
                </tr>
                <tr>
                    <td>Temp File Location</td>
                    <td><code>" . htmlspecialchars($tmp_name) . "</code></td>
                </tr>
                <tr>
                    <td>File Size</td>
                    <td><code>" . number_format($_FILES['image_file']['size'] / 1024, 2) . " KB</code></td>
                </tr>
            </table>";
            
            if (in_array($ext, $allowed)) {
                $new_filename = 'test_' . time() . '.' . $ext;
                $dest = $upload_dir . $new_filename;
                
                echo "<p>Attempting to move temp file to target destination: <code>" . htmlspecialchars($dest) . "</code></p>";
                
                if (move_uploaded_file($tmp_name, $dest)) {
                    echo "<div class='success'>
                        SUCCESS! Image was successfully uploaded and moved to: <code>$dest</code><br>
                        URL: <a href='$dest' target='_blank'>$dest</a>
                    </div>";
                } else {
                    echo "<div class='error'>
                        FAILED! move_uploaded_file returned FALSE.<br>
                        This is usually caused by incorrect destination folder permissions or directory path structure.
                    </div>";
                    $last_error = error_get_last();
                    if ($last_error) {
                        echo "<p>Last PHP Error Message:</p>";
                        echo "<pre>" . htmlspecialchars(print_r($last_error, true)) . "</pre>";
                    }
                }
            } else {
                echo "<div class='error'>FAILED! Extension '.$ext' is not in the allowed list (jpg, jpeg, png, gif, webp).</div>";
            }
        }
    } else {
        echo "<div class='error'>Error: 'image_file' was not found in post request. Make sure form has enctype='multipart/form-data'.</div>";
    }
}

function getUploadErrorString($code) {
    switch ($code) {
        case UPLOAD_ERR_INI_SIZE: return "<span style='color:red;'>UPLOAD_ERR_INI_SIZE: The uploaded file exceeds the upload_max_filesize directive in php.ini</span>";
        case UPLOAD_ERR_FORM_SIZE: return "<span style='color:red;'>UPLOAD_ERR_FORM_SIZE: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form</span>";
        case UPLOAD_ERR_PARTIAL: return "<span style='color:red;'>UPLOAD_ERR_PARTIAL: The uploaded file was only partially uploaded</span>";
        case UPLOAD_ERR_NO_FILE: return "<span style='color:red;'>UPLOAD_ERR_NO_FILE: No file was uploaded</span>";
        case UPLOAD_ERR_NO_TMP_DIR: return "<span style='color:red;'>UPLOAD_ERR_NO_TMP_DIR: Missing a temporary folder on the server</span>";
        case UPLOAD_ERR_CANT_WRITE: return "<span style='color:red;'>UPLOAD_ERR_CANT_WRITE: Failed to write file to disk</span>";
        case UPLOAD_ERR_EXTENSION: return "<span style='color:red;'>UPLOAD_ERR_EXTENSION: A PHP extension stopped the file upload</span>";
        case UPLOAD_ERR_OK: return "<span style='color:green;'>UPLOAD_ERR_OK: No error, file uploaded to temp successfully</span>";
        default: return "Unknown upload error";
    }
}
?>

<h2>3. Test Upload Form</h2>
<p>Upload a small image to test if uploading is fully functional:</p>
<form method="POST" enctype="multipart/form-data">
    <div style="margin-bottom: 20px;">
        <label for="image_file" style="display:block;margin-bottom:8px;font-weight:bold;">Select Image File:</label>
        <input type="file" id="image_file" name="image_file" accept="image/*" required>
    </div>
    <button type="submit">Upload Test Image</button>
</form>

</body>
</html>
