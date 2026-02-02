<?php
/**
 * Script to update all footer links to use new URL functions
 */

// Read footer content
$footerPath = 'app/views/_layout/footer.php';
$content = file_get_contents($footerPath);

// Define replacements for footer links
$replacements = [
    // Guide section
    '?page=guide&type=how-to-order' => '<?php echo page_url(\'guide\', [\'type\' => \'how-to-order\']); ?>',
    '?page=guide&type=payment' => '<?php echo page_url(\'guide\', [\'type\' => \'payment\']); ?>',
    '?page=guide&type=shipping' => '<?php echo page_url(\'guide\', [\'type\' => \'shipping\']); ?>',
    '?page=guide&type=customs' => '<?php echo page_url(\'guide\', [\'type\' => \'customs\']); ?>',
    '?page=guide&type=pricing' => '<?php echo page_url(\'guide\', [\'type\' => \'pricing\']); ?>',
    
    // News section
    '?page=news' => '<?php echo nav_url(\'news\'); ?>',
    
    // FAQ section
    '?page=faq' => '<?php echo nav_url(\'faq\'); ?>',
    
    // Company section
    '?page=about' => '<?php echo nav_url(\'about\'); ?>',
    '?page=security' => '<?php echo nav_url(\'security\'); ?>',
    '?page=contact' => '<?php echo nav_url(\'contact\'); ?>',
    '?page=careers' => '<?php echo nav_url(\'careers\'); ?>',
    '?page=affiliate' => '<?php echo nav_url(\'affiliate\'); ?>',
    '?page=terms' => '<?php echo nav_url(\'terms\'); ?>',
    '?page=privacy' => '<?php echo nav_url(\'privacy\'); ?>',
    '?page=accessibility' => '<?php echo nav_url(\'accessibility\'); ?>',
    
    // Features section
    '?page=features&type=user-management' => '<?php echo page_url(\'features\', [\'type\' => \'user-management\']); ?>',
    '?page=features&type=order-tracking' => '<?php echo page_url(\'features\', [\'type\' => \'order-tracking\']); ?>',
    '?page=features&type=reporting' => '<?php echo page_url(\'features\', [\'type\' => \'reporting\']); ?>',
    '?page=features&type=support' => '<?php echo page_url(\'features\', [\'type\' => \'support\']); ?>',
    '?page=features&type=multi-language' => '<?php echo page_url(\'features\', [\'type\' => \'multi-language\']); ?>',
    
    // Copyright section
    'href="./"' => 'href="<?php echo base_url(); ?>"',
];

// Apply replacements
foreach ($replacements as $old => $new) {
    $content = str_replace('href="' . $old . '"', 'href="' . $new . '"', $content);
}

// Write updated content
file_put_contents($footerPath, $content);

echo "Footer links updated successfully!\n";
echo "Updated " . count($replacements) . " link patterns.\n";
?>