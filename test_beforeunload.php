<?php
/**
 * Test file cho vấn đề JavaScript beforeunload warning
 * 
 * Chạy: http://test1.web3b.com/test_beforeunload.php
 * 
 * Test 1: Load trang và click nút Submit (không thay đổi gì)
 * Test 2: Load trang, thay đổi giá trị field, click nút Submit  
 * Test 3: Load trang, thay đổi giá trị field, click link "Quay lại danh sách"
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Beforeunload Warning</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
        .test-section { border: 1px solid #ccc; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .test-section h3 { margin-top: 0; }
        button { padding: 10px 20px; margin: 5px; cursor: pointer; }
        .btn-primary { background: #007bff; color: white; border: none; }
        .btn-outline { background: #fff; border: 1px solid #ccc; }
        input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        textarea { height: 100px; }
        .result { background: #f8f9fa; padding: 10px; margin-top: 20px; white-space: pre-wrap; }
    </style>
</head>
<body>
    <h1>Test: beforeunload Warning Issue</h1>
    
    <div class="test-section">
        <h3>Test 1: Submit form KHÔNG thay đổi gì</h3>
        <form id="testForm1" method="POST" action="?page=admin&module=news&action=edit&id=2">
            <input type="hidden" name="test" value="1">
            <input type="text" name="title" value="Test Title" readonly>
            <button type="submit" class="btn-primary">Submit (không thay đổi)</button>
        </form>
    </div>

    <div class="test-section">
        <h3>Test 2: Submit form SAU KHI thay đổi giá trị</h3>
        <form id="testForm2" method="POST" action="?page=admin&module=news&action=edit&id=2">
            <input type="text" name="title" id="title2" value="Test Title">
            <button type="button" onclick="document.getElementById('title2').value = 'Changed!'">Click để thay đổi</button>
            <button type="submit" class="btn-primary">Submit (có thay đổi)</button>
        </form>
    </div>

    <div class="test-section">
        <h3>Test 3: Test JavaScript Logic</h3>
        <button type="button" onclick="runTest()">Chạy Test JavaScript</button>
        <div id="testResult" class="result"></div>
    </div>

    <div class="test-section">
        <h3>Current Implementation (từ edit.php)</h3>
        <pre id="currentCode"></pre>
    </div>

<script>
// Test 1: Current implementation
let formChanged1 = false;
document.querySelectorAll('#testForm1 input, #testForm1 textarea, #testForm1 select').forEach(element => {
    element.addEventListener('change', () => formChanged1 = true);
});

document.querySelector('#testForm1').addEventListener('submit', function(e) {
    console.log('Form 1: Submit clicked, formChanged =', formChanged1);
});

window.addEventListener('beforeunload', function(e) {
    if (formChanged1) {
        console.log('Form 1: beforeunload triggered!');
        e.preventDefault();
        e.returnValue = '';
    }
});

// Test 2: With isSubmitting flag
let formChanged2 = false;
let isSubmitting2 = false;

document.querySelectorAll('#testForm2 input, #testForm2 textarea, #testForm2 select').forEach(element => {
    element.addEventListener('change', () => formChanged2 = true);
});

document.querySelector('#testForm2').addEventListener('submit', function(e) {
    isSubmitting2 = true;
    formChanged2 = false;
    console.log('Form 2: Submit clicked, isSubmitting =', isSubmitting2, ', formChanged =', formChanged2);
});

window.addEventListener('beforeunload', function(e) {
    console.log('Form 2: beforeunload check - formChanged =', formChanged2, ', isSubmitting =', isSubmitting2);
    if (formChanged2 && !isSubmitting2) {
        console.log('Form 2: beforeunload would show warning');
        e.preventDefault();
        e.returnValue = '';
    }
});

// Test 3: Manual test function
function runTest() {
    let results = [];
    
    // Test case 1: formChanged=false, isSubmitting=false
    let test1 = !formChanged1 && !isSubmitting2;
    results.push('Test 1 (no change, not submitting): should NOT warn = ' + test1);
    
    // Test case 2: formChanged=true, isSubmitting=false (before submit clicked)
    // Simulate user changes value
    formChanged2 = true;
    isSubmitting2 = false;
    let test2 = formChanged2 && !isSubmitting2;
    results.push('Test 2 (changed, not submitting): should warn = ' + test2);
    
    // Test case 3: formChanged=true, isSubmitting=true (after submit clicked)
    isSubmitting2 = true;
    let test3 = formChanged2 && !isSubmitting2;
    results.push('Test 3 (changed, submitting): should NOT warn = ' + test3);
    
    document.getElementById('testResult').textContent = results.join('\n');
}

// Show current implementation code
fetch('app/views/admin/news/edit.php')
    .then(r => r.text())
    .then(text => {
        let match = text.match(/\/\/ Warn before[\s\S]*?<\/script>/);
        if (match) {
            document.getElementById('currentCode').textContent = match[0];
        }
    });
</script>

<p><a href="?page=admin&module=news&action=edit&id=2">Quay lại trang edit</a></p>
</body>
</html>
