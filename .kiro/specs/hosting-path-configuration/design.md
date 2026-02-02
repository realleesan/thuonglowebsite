# Hosting Path Configuration - Design Document

## Architecture Overview

### System Components
1. **Configuration Manager** - Centralized config handling
2. **Environment Detector** - Auto-detect local vs hosting
3. **URL Builder** - Generate correct URLs for all contexts
4. **Asset Manager** - Handle static asset paths
5. **Route Handler** - Clean URL routing with .htaccess

### Design Principles
- **Environment Agnostic**: Code works seamlessly across environments
- **Configuration Driven**: All paths configurable via single config file
- **Backward Compatible**: Existing code continues to work
- **Performance Optimized**: Minimal overhead for path resolution

## Detailed Design

### 1. Configuration System

#### Config Structure
```php
// config.php
return [
    'app' => [
        'name' => 'Thuong Lo',
        'environment' => 'auto', // auto, local, hosting
        'debug' => false, // auto-set based on environment
    ],
    'url' => [
        'base' => 'auto', // auto-detect or manual override
        'force_https' => true,
        'www_redirect' => 'non-www', // www, non-www, none
    ],
    'paths' => [
        'assets' => 'assets/',
        'uploads' => 'uploads/',
        'cache' => 'cache/',
    ]
];
```

#### Environment Detection Logic
```php
function detect_environment() {
    // Check for hosting indicators
    if (isset($_SERVER['HTTP_HOST']) && 
        strpos($_SERVER['HTTP_HOST'], 'test1.web3b.com') !== false) {
        return 'hosting';
    }
    
    // Check for local indicators
    if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']) ||
        strpos($_SERVER['HTTP_HOST'], '.local') !== false) {
        return 'local';
    }
    
    return 'hosting'; // default to hosting for safety
}
```

### 2. URL Building System

#### Base URL Generation
```php
class UrlBuilder {
    private $config;
    private $baseUrl;
    
    public function __construct($config) {
        $this->config = $config;
        $this->baseUrl = $this->generateBaseUrl();
    }
    
    private function generateBaseUrl() {
        $protocol = $this->config['url']['force_https'] ? 'https' : 
                   (isset($_SERVER['HTTPS']) ? 'https' : 'http');
        $host = $_SERVER['HTTP_HOST'];
        
        // Handle www redirect preference
        if ($this->config['url']['www_redirect'] === 'non-www') {
            $host = preg_replace('/^www\./', '', $host);
        } elseif ($this->config['url']['www_redirect'] === 'www') {
            if (!preg_match('/^www\./', $host)) {
                $host = 'www.' . $host;
            }
        }
        
        return $protocol . '://' . $host . '/';
    }
    
    public function asset($path) {
        return $this->baseUrl . $this->config['paths']['assets'] . ltrim($path, '/');
    }
    
    public function url($path = '') {
        return $this->baseUrl . ltrim($path, '/');
    }
}
```

### 3. Asset Management

#### CSS/JS Helper Functions
```php
function asset_url($path) {
    global $urlBuilder;
    return $urlBuilder->asset($path);
}

function css_url($file) {
    return asset_url('css/' . $file);
}

function js_url($file) {
    return asset_url('js/' . $file);
}

function img_url($file) {
    return asset_url('images/' . $file);
}
```

#### Cache Busting
```php
function versioned_asset($path) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/assets/' . $path;
    $version = file_exists($fullPath) ? filemtime($fullPath) : time();
    return asset_url($path) . '?v=' . $version;
}
```

### 4. Routing System

#### .htaccess Configuration
```apache
RewriteEngine On

# Force HTTPS (if SSL available)
RewriteCond %{HTTPS} off
RewriteCond %{HTTP_HOST} ^test1\.web3b\.com$ [NC]
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]

# Remove www (or add www based on config)
RewriteCond %{HTTP_HOST} ^www\.test1\.web3b\.com$ [NC]
RewriteRule ^(.*)$ https://test1.web3b.com/$1 [R=301,L]

# Remove index.php from URLs
RewriteCond %{THE_REQUEST} /index\.php[?\s] [NC]
RewriteRule ^index\.php$ / [R=301,L]

# Route all requests through index.php
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Prevent access to sensitive files
<Files "config.php">
    Order allow,deny
    Deny from all
</Files>

<FilesMatch "\.(log|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 5. Integration Points

#### Master Layout Updates
```php
// In master.php
$config = require_once 'config.php';
$urlBuilder = new UrlBuilder($config);

// CSS includes
echo '<link rel="stylesheet" href="' . css_url('main.css') . '">';

// JS includes  
echo '<script src="' . js_url('main.js') . '"></script>';

// Logo
echo '<img src="' . img_url('logo/logo.png') . '" alt="Logo">';
```

#### Navigation Updates
```php
// Update all navigation links
function nav_url($page) {
    global $urlBuilder;
    return $urlBuilder->url('?page=' . $page);
}

// In navigation templates
echo '<a href="' . nav_url('products') . '">Sản phẩm</a>';
```

## Implementation Strategy

### Phase 1: Core Configuration
1. Create config.php with environment detection
2. Implement UrlBuilder class
3. Add helper functions for asset URLs
4. Update master layout to use new system

### Phase 2: Asset Management
1. Update all CSS/JS includes in layouts
2. Update image paths throughout templates
3. Implement cache busting for assets
4. Test all static resources load correctly

### Phase 3: Navigation & Links
1. Update navigation menus
2. Update breadcrumb generation
3. Update form action URLs
4. Update internal page links

### Phase 4: URL Rewriting
1. Create .htaccess with rewrite rules
2. Test clean URLs work
3. Implement 404 handling
4. Test redirects (www, HTTPS)

### Phase 5: Testing & Optimization
1. Test on hosting environment
2. Performance optimization
3. Security hardening
4. Documentation updates

## Correctness Properties

### Property 1: Environment Detection Consistency
**Validates: Requirements 1.2, 5.1, 5.2**
```php
// For any server environment variables, the detection should be consistent
property('environment_detection_is_consistent', function() {
    // Test that same environment variables always produce same result
    $env1 = detect_environment_with_vars(['HTTP_HOST' => 'test1.web3b.com']);
    $env2 = detect_environment_with_vars(['HTTP_HOST' => 'test1.web3b.com']);
    return $env1 === $env2;
});
```

### Property 2: URL Generation Consistency
**Validates: Requirements 1.1, 1.3, 2.1-2.5**
```php
// All generated URLs should be valid and consistent
property('url_generation_is_valid', function($path) {
    $url = $urlBuilder->url($path);
    // URL should always start with protocol
    return preg_match('/^https?:\/\//', $url) === 1;
});
```

### Property 3: Asset Path Resolution
**Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5**
```php
// Asset URLs should always resolve to accessible paths
property('asset_urls_are_accessible', function($assetPath) {
    $url = asset_url($assetPath);
    $parsedUrl = parse_url($url);
    // Should have valid scheme and host
    return isset($parsedUrl['scheme']) && isset($parsedUrl['host']);
});
```

### Property 4: Navigation Link Validity
**Validates: Requirements 3.1, 3.2, 3.3, 3.4**
```php
// All navigation links should be valid URLs
property('navigation_links_are_valid', function($page) {
    $url = nav_url($page);
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
});
```

### Property 5: Environment-Specific Configuration
**Validates: Requirements 5.3, 5.4, 5.5**
```php
// Configuration should vary appropriately by environment
property('config_varies_by_environment', function($environment) {
    $config = get_config_for_environment($environment);
    if ($environment === 'local') {
        return $config['app']['debug'] === true;
    } else {
        return $config['app']['debug'] === false;
    }
});
```

## Testing Strategy

### Unit Tests
- Test environment detection with various server variables
- Test URL building with different configurations
- Test asset path generation
- Test helper functions

### Integration Tests
- Test complete page rendering with correct asset paths
- Test navigation functionality
- Test form submissions
- Test breadcrumb generation

### Manual Testing Checklist
- [ ] Homepage loads completely on hosting
- [ ] All CSS styles applied correctly
- [ ] All JavaScript functionality works
- [ ] All images display properly
- [ ] Navigation links work
- [ ] Breadcrumbs display correctly
- [ ] Forms submit to correct URLs
- [ ] 404 pages display for invalid URLs
- [ ] HTTPS redirect works (if SSL available)

## Security Considerations

### File Access Protection
- Prevent direct access to config.php
- Block access to log files and documentation
- Validate all user inputs in URL parameters

### Path Traversal Prevention
- Sanitize all file paths
- Prevent directory traversal attacks
- Validate asset requests

### HTTPS Enforcement
- Force HTTPS in production if SSL available
- Secure cookie settings
- HSTS headers if supported

## Performance Optimizations

### Asset Optimization
- Implement cache busting for static assets
- Add appropriate cache headers
- Consider asset minification

### URL Generation Caching
- Cache base URL calculation
- Minimize repeated environment detection
- Optimize path resolution

## Monitoring & Maintenance

### Error Logging
- Log environment detection issues
- Log URL generation errors
- Monitor 404 errors

### Performance Monitoring
- Track page load times
- Monitor asset loading performance
- Alert on broken links

### Regular Maintenance
- Update cache busting versions
- Review and update .htaccess rules
- Test after hosting environment changes