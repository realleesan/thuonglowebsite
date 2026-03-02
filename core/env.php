<?php
/**
 * Environment Variables Loader
 * Simple .env file parser for configuration
 */

class Env {
    private static $loaded = false;
    private static $variables = [];
    
    /**
     * Load environment variables from .env file
     */
    public static function load($path = null) {
        if (self::$loaded) {
            return;
        }
        
        if ($path === null) {
            $path = __DIR__ . '/../.env';
        }
        
        if (!file_exists($path)) {
            // .env file is optional, use defaults from config.php
            self::$loaded = true;
            return;
        }
        
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            
            // Parse KEY=VALUE
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
                    $value = $matches[2];
                }
                
                // Store in static array
                self::$variables[$key] = $value;
                
                // Set as environment variable
                if (!getenv($key)) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
        
        self::$loaded = true;
    }
    
    /**
     * Get environment variable
     */
    public static function get($key, $default = null) {
        self::load();
        
        // Check in order: static array, $_ENV, $_SERVER, getenv()
        if (isset(self::$variables[$key])) {
            return self::$variables[$key];
        }
        
        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }
        
        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }
        
        return $default;
    }
    
    /**
     * Check if environment variable exists
     */
    public static function has($key) {
        self::load();
        return self::get($key) !== null;
    }
    
    /**
     * Get all environment variables
     */
    public static function all() {
        self::load();
        return self::$variables;
    }
}

// Auto-load .env file when this file is included
Env::load();
