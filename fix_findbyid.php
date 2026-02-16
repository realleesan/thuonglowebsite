<?php
$file = 'app/services/AgentRegistrationService.php';
$content = file_get_contents($file);

// Replace all findById with find
$content = str_replace('findById', 'find', $content);

file_put_contents($file, $content);
echo "Fixed findById -> find in AgentRegistrationService.php\n";
?>