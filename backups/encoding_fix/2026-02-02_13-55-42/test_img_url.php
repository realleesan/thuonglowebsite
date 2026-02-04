<?php
// Test script to verify img_url function works correctly
require_once 'config.php';
require_once 'core/functions.php';

// Initialize URL builder
init_url_builder();

echo "Testing img_url() function:\n";
echo "img_url('home/home-banner-final.png'): " . img_url('home/home-banner-final.png') . "\n";
echo "img_url('about/about_founder.jpg'): " . img_url('about/about_founder.jpg') . "\n";
echo "img_url('home/cta-final-1.png'): " . img_url('home/cta-final-1.png') . "\n";

echo "\nTesting asset_url() function:\n";
echo "asset_url('css/main.css'): " . asset_url('css/main.css') . "\n";
echo "asset_url('js/main.js'): " . asset_url('js/main.js') . "\n";

echo "\nEnvironment: " . get_environment() . "\n";
echo "Base URL: " . base_url() . "\n";
?>