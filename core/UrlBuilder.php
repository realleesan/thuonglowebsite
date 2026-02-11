<?php
/**
 * URL Builder Class
 * Handles URL generation for all contexts (local and hosting)
 */

class UrlBuilder {
    private $config;
    private $baseUrl;
    private $environment;
    
    /**
     * Constructor
     * @param array $config Configuration array
     */
    public function __construct($config) {
        $this->config = $config;
        $this->environment = $config['app']['environment'];
        $this->baseUrl = $this->generateBaseUrl();
    }
    
    /**
     * Generate base URL based on environment and configuration
     * @return string Base URL
     */
    private function generateBaseUrl() {
        // Determine protocol
        $protocol = 'http';
        
        // Check multiple conditions for HTTPS
        if ($this->config['url']['force_https']) {
            $protocol = 'https';
        } elseif (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $protocol = 'https';
        } elseif (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
            $protocol = 'https';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            // Check for proxy/load balancer forwarded protocol
            $protocol = 'https';
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on') {
            // Alternative proxy header
            $protocol = 'https';
        }
        
        // Get host
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Handle www redirect preference
        if ($this->config['url']['www_redirect'] === 'non-www') {
            $host = preg_replace('/^www\./', '', $host);
        } elseif ($this->config['url']['www_redirect'] === 'www') {
            if (!preg_match('/^www\./', $host)) {
                $host = 'www.' . $host;
            }
        }
        
        // Build base URL
        $baseUrl = $protocol . '://' . $host;
        
        // Add port if not standard
        if (isset($_SERVER['SERVER_PORT'])) {
            $port = $_SERVER['SERVER_PORT'];
            if (($protocol === 'http' && $port != 80) || 
                ($protocol === 'https' && $port != 443)) {
                $baseUrl .= ':' . $port;
            }
        }
        
        // Add trailing slash
        $baseUrl .= '/';
        
        return $baseUrl;
    }
    
    /**
     * Get base URL
     * @return string
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }
    
    /**
     * Generate asset URL
     * @param string $path Asset path
     * @return string Full asset URL
     */
    public function asset($path) {
        $assetPath = $this->config['paths']['assets'] . ltrim($path, '/');
        return $this->baseUrl . $assetPath;
    }
    
    /**
     * Generate general URL
     * @param string $path URL path
     * @return string Full URL
     */
    public function url($path = '') {
        return $this->baseUrl . ltrim($path, '/');
    }
    
    /**
     * Generate page URL with query parameters
     * @param string $page Page name
     * @param array $params Additional parameters
     * @return string Full URL
     */
    public function page($page, $params = []) {
        $url = $this->baseUrl;
        
        // Add index.php if not using clean URLs
        if (!$this->config['url']['remove_index_php']) {
            $url .= 'index.php';
        }
        
        // Add page parameter
        $params['page'] = $page;
        
        // Build query string
        $queryString = http_build_query($params);
        
        return $url . ($queryString ? '?' . $queryString : '');
    }
    
    /**
     * Generate navigation URL
     * @param string $page Page name
     * @return string Navigation URL
     */
    public function nav($page) {
        return $this->page($page);
    }
    
    /**
     * Check if current request is HTTPS
     * @return bool
     */
    public function isHttps() {
        return strpos($this->baseUrl, 'https://') === 0;
    }
    
    /**
     * Get current environment
     * @return string
     */
    public function getEnvironment() {
        return $this->environment;
    }
    
    /**
     * Check if running in local environment
     * @return bool
     */
    public function isLocal() {
        return $this->environment === 'local';
    }
    
    /**
     * Check if running in hosting environment
     * @return bool
     */
    public function isHosting() {
        return $this->environment === 'hosting';
    }
}