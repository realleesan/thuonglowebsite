# Hosting Path Configuration - Implementation Tasks

## Phase 1: Core Configuration Setup ✅ COMPLETED

### 1. Create Configuration System ✅ COMPLETED

- [X] 1.1 Create config.php with environment detection and base configuration
- [X] 1.2 Implement environment detection function (detect_environment)
- [X] 1.3 Create UrlBuilder class for URL generation
- [X] 1.4 Add helper functions (asset_url, css_url, js_url, img_url)
- [X] 1.5 Write unit tests for environment detection
- [X] 1.6 Write unit tests for UrlBuilder class

### 2. Update Core Files Integration ✅ COMPLETED

- [X] 2.1 Update index.php to load config and initialize UrlBuilder
- [X] 2.2 Update core/functions.php to include new URL helper functions
- [X] 2.3 Test basic configuration loading works

## Phase 2: Asset Management ✅ COMPLETED

### 3. Update Master Layout ✅ COMPLETED

- [X] 3.1 Update master layout to use new asset URL functions (Validates: Requirements 2.1, 2.2, 2.3)
- [X] 3.2 Replace all hardcoded CSS paths with css_url() function (Validates: Requirements 2.1)
- [X] 3.3 Replace all hardcoded JS paths with js_url() function (Validates: Requirements 2.2)
- [X] 3.4 Replace all hardcoded image paths with img_url() function (Validates: Requirements 2.3, 2.4)
- [X] 3.5 Test homepage loads with correct asset paths (Validates: Requirements 2.1-2.5)

### 4. Implement Cache Busting ✅ COMPLETED

- [X] 4.1 Create versioned_asset() function for cache busting (already implemented in functions.php)
- [X] 4.2 Update asset includes to use versioned URLs where appropriate
- [X] 4.3 Test cache busting works correctly

### 5. Update Layout Templates ✅ COMPLETED

- [X] 5.1 Update header template with new asset functions (Validates: Requirements 2.4)
- [X] 5.2 Update footer template with new asset functions
- [X] 5.3 Update master layout template with versioned asset functions
- [X] 5.4 Test layout templates load correctly with assets (Validates: Requirements 2.1-2.5)

### 6. Update Page-Specific Templates

- [X] 6.1 Update home page template to use img_url() for images (Validates: Requirements 2.3, 2.4)
- [X] 6.2 Update about page template to use img_url() for images (Validates: Requirements 2.3, 2.4)
- [X] 6.3 Update payment/checkout template to use img_url() for images (Validates: Requirements 2.3, 2.4)
- [X] 6.4 Update CTA template to use img_url() for images (Validates: Requirements 2.3, 2.4)
- [X] 6.5 Test all page-specific templates load correctly with assets (Validates: Requirements 2.1-2.5)

## Phase 3: Navigation & Internal Links ✅ COMPLETED

### 7. Update Navigation System ✅ COMPLETED

- [X] 7.1 Update main navigation menu to use nav_url() (Validates: Requirements 3.1)
- [X] 7.2 Update footer navigation links (Validates: Requirements 3.1)
- [X] 7.3 Test all navigation links work correctly (Validates: Requirements 3.1)

### 8. Update Breadcrumb System ✅ COMPLETED

- [X] 8.1 Update breadcrumb generation functions to use new URL system (Validates: Requirements 3.2)
- [X] 8.2 Test breadcrumbs on all page types (Validates: Requirements 3.2)
- [X] 8.3 Verify breadcrumb links are correct (Validates: Requirements 3.2)

### 9. Update Form Actions

- [X] 9.1 Update all form action URLs to use new URL system (Validates: Requirements 3.5)
- [X] 9.2 Update authentication forms (login, register, forgot password) (Validates: Requirements 3.5)
- [X] 9.3 Update contact form and other forms (Validates: Requirements 3.5)
- [X] 9.4 Test form submissions work correctly (Validates: Requirements 3.5)

## Phase 4: URL Rewriting & Clean URLs

### 10. Create .htaccess Configuration

- [X] 10.1 Create .htaccess file with rewrite rules (Validates: Requirements 4.1, 4.2)
- [X] 10.2 Add HTTPS redirect rules (if SSL available) (Validates: Requirements 4.5)
- [X] 10.3 Add www redirect rules based on configuration (Validates: Requirements 4.4)
- [X] 10.4 Add index.php removal rules (Validates: Requirements 4.2)
- [X] 10.5 Add security rules to protect sensitive files

### 11. Test URL Rewriting

- [X] 11.1 Test clean URLs work without index.php (Validates: Requirements 4.2)
- [X] 11.2 Test 404 handling for invalid URLs (Validates: Requirements 4.3)
- [X] 11.3 Test HTTPS redirects (if applicable) (Validates: Requirements 4.5)
- [X] 11.4 Test www redirects work correctly (Validates: Requirements 4.4)

## Phase 5: Property-Based Testing

### 12. Write Property-Based Tests

- [x] 12.1 Write property test for environment detection consistency (Validates: Requirements 1.2, 5.1, 5.2)
- [ ] 12.2 Write property test for URL generation validity (Validates: Requirements 1.1, 1.3, 2.1-2.5)
- [ ] 12.3 Write property test for asset path resolution (Validates: Requirements 2.1, 2.2, 2.3, 2.4, 2.5)
- [ ] 12.4 Write property test for navigation link validity (Validates: Requirements 3.1, 3.2, 3.3, 3.4)
- [ ] 12.5 Write property test for environment-specific configuration (Validates: Requirements 5.3, 5.4, 5.5)

### 13. Integration Testing

- [x] 13.1 Test complete homepage rendering on hosting (Validates: All requirements)
- [x] 13.2 Test all major pages (products, news, contact, auth) (Validates: Requirements 2.1-2.5, 3.1-3.5)
- [x] 13.3 Test user flows (registration, login, product viewing) (Validates: Requirements 3.5)
- [x] 13.4 Test admin and affiliate dashboards (Validates: Requirements 2.1-2.5, 3.1-3.5)
- [x] 13.5 Performance testing on hosting environment

## Phase 6: Security & Optimization

### 14. Security Hardening

- [ ] 14.1 Test file access protection (.htaccess rules)
- [ ] 14.2 Validate path traversal prevention
- [ ] 14.3 Test input sanitization for URL parameters
- [ ] 14.4 Review and secure file upload paths

### 15. Performance Optimization

- [ ] 15.1 Implement asset caching headers
- [ ] 15.2 Optimize URL generation performance
- [ ] 15.3 Test page load times on hosting
- [ ] 15.4 Optimize asset loading order

## Phase 7: Final Testing & Documentation

### 16. Comprehensive Testing

- [ ] 16.1 Manual testing checklist on hosting environment (Validates: All requirements)
  - [ ] Homepage loads completely
  - [ ] All CSS styles applied
  - [ ] All JavaScript works
  - [ ] All images display
  - [ ] Navigation works
  - [ ] Forms submit correctly
  - [ ] Breadcrumbs work
  - [ ] 404 pages display
- [ ] 16.2 Cross-browser testing
- [ ] 16.3 Mobile responsiveness testing

### 17. Documentation & Cleanup

- [ ] 17.1 Update README with hosting deployment instructions
- [ ] 17.2 Document configuration options
- [ ] 17.3 Create troubleshooting guide
- [ ] 17.4 Clean up any temporary files or debug code

## Optional Enhancements

### 18. Advanced Features (Optional)

- [ ] 18.1* Implement CDN support for assets
- [ ] 18.2* Add asset minification and compression
- [ ] 18.3* Implement advanced caching strategies
- [ ] 18.4* Add monitoring and error reporting
- [ ] 18.5* Create deployment automation scripts

## Testing Framework Setup ✅ COMPLETED

### 19. Setup Testing Environment ✅ COMPLETED

- [X] 19.1 Choose and setup property-based testing framework (PHPUnit with Eris or similar)
- [X] 19.2 Create test configuration for different environments
- [X] 19.3 Setup test data and fixtures
- [X] 19.4 Create test runner scripts

## Success Criteria

The implementation is complete when:

- ✅ Website loads completely on https://test1.web3b.com/
- ✅ All assets (CSS, JS, images) load correctly
- ✅ All navigation and internal links work
- ✅ Forms submit to correct URLs
- ✅ Clean URLs work without index.php
- ✅ All property-based tests pass
- ✅ Performance meets requirements (< 3 second load time)
- ✅ Security measures are in place and tested
