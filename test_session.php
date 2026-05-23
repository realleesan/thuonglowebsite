<?php
session_start();

if (isset($_GET['set'])) {
    $_SESSION['test_val'] = 'Hello Session Persistence!';
    session_write_close();
    header('Location: test_session.php');
    exit;
}

echo "<h1>Session Diagnostic Tool</h1>";
if (isset($_SESSION['test_val'])) {
    echo "<p style='color:green;font-size:18px;'>Session works! Value: <strong>" . htmlspecialchars($_SESSION['test_val']) . "</strong></p>";
    unset($_SESSION['test_val']);
} else {
    echo "<p style='color:red;font-size:18px;'>No session value found.</p>";
    echo "<p><a href='test_session.php?set=1'>Click here to set session value and redirect</a></p>";
}
echo "<p>Session ID: " . session_id() . "</p>";
echo "<pre>Full $_SESSION content:\n";
print_r($_SESSION);
echo "</pre>";
