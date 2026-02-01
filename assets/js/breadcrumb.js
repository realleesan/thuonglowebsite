/**
 * Breadcrumb Component JavaScript
 * Xử lý các tương tác và tính năng động cho breadcrumb
 */

class BreadcrumbManager {
    constructor() {
        this.breadcrumbElement = null;
        this.init();
    }

    /**
     * Khởi tạo breadcrumb manager
     */
    init() {
        this.breadcrumbElement = document.querySelector('.breadcrumb');
        if (this.breadcrumbElement) {
            this.setupEventListeners();
            this.handleResponsiveBreadcrumb();
            this.addKeyboardNavigation();
        }
    }

    /**
     * Thiết lập event listeners
     */
    setupEventListeners() {
        // Xử lý click tracking cho analytics
        this.breadcrumbElement.addEventListener('click', (e) => {
            if (e.target.classList.contains('breadcrumb-link')) {
                this.trackBreadcrumbClick(e.target);
            }
        });

        // Xử lý responsive khi resize window
        window.addEventListener('resize', () => {
            this.handleResponsiveBreadcrumb();
        });
    }

    /**
     * Xử lý breadcrumb responsive
     */
    handleResponsiveBreadcrumb() {
        if (!this.breadcrumbElement) return;

        const breadcrumbItems = this.breadcrumbElement.querySelectorAll('.breadcrumb-link, .breadcrumb-current');
        const isMobile = window.innerWidth <= 480;

        if (isMobile && breadcrumbItems.length > 3) {
            this.collapseBreadcrumb(breadcrumbItems);
        } else {
            this.expandBreadcrumb(breadcrumbItems);
        }
    }

    /**
     * Thu gọn breadcrumb trên mobile
     */
    collapseBreadcrumb(items) {
        items.forEach((item, index) => {
            const delimiter = item.nextElementSibling;
            
            // Hiển thị item đầu và cuối, ẩn các item ở giữa
            if (index === 0 || index === items.length - 1) {
                item.style.display = '';
                if (delimiter && delimiter.classList.contains('delimiter')) {
                    delimiter.style.display = '';
                }
            } else if (index === 1) {
                // Thay thế item thứ 2 bằng dấu ...
                item.style.display = 'none';
                if (delimiter && delimiter.classList.contains('delimiter')) {
                    delimiter.innerHTML = '<span class="breadcrumb-ellipsis">...</span>';
                    delimiter.style.display = '';
                }
            } else {
                item.style.display = 'none';
                if (delimiter && delimiter.classList.contains('delimiter')) {
                    delimiter.style.display = 'none';
                }
            }
        });
    }

    /**
     * Mở rộng breadcrumb (desktop)
     */
    expandBreadcrumb(items) {
        items.forEach((item) => {
            item.style.display = '';
            const delimiter = item.nextElementSibling;
            if (delimiter && delimiter.classList.contains('delimiter')) {
                delimiter.style.display = '';
                // Khôi phục delimiter gốc nếu đã bị thay đổi
                if (delimiter.innerHTML.includes('breadcrumb-ellipsis')) {
                    delimiter.innerHTML = `
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    `;
                }
            }
        });
    }

    /**
     * Thêm keyboard navigation
     */
    addKeyboardNavigation() {
        const breadcrumbLinks = this.breadcrumbElement.querySelectorAll('.breadcrumb-link');
        
        breadcrumbLinks.forEach((link, index) => {
            link.addEventListener('keydown', (e) => {
                switch (e.key) {
                    case 'ArrowRight':
                        e.preventDefault();
                        this.focusNextLink(breadcrumbLinks, index);
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.focusPrevLink(breadcrumbLinks, index);
                        break;
                    case 'Home':
                        e.preventDefault();
                        breadcrumbLinks[0].focus();
                        break;
                    case 'End':
                        e.preventDefault();
                        breadcrumbLinks[breadcrumbLinks.length - 1].focus();
                        break;
                }
            });
        });
    }

    /**
     * Focus vào link tiếp theo
     */
    focusNextLink(links, currentIndex) {
        const nextIndex = currentIndex + 1;
        if (nextIndex < links.length) {
            links[nextIndex].focus();
        }
    }

    /**
     * Focus vào link trước đó
     */
    focusPrevLink(links, currentIndex) {
        const prevIndex = currentIndex - 1;
        if (prevIndex >= 0) {
            links[prevIndex].focus();
        }
    }

    /**
     * Track breadcrumb click cho analytics
     */
    trackBreadcrumbClick(linkElement) {
        const breadcrumbText = linkElement.textContent.trim();
        const breadcrumbUrl = linkElement.href;
        
        // Gửi event tracking (có thể tích hợp với Google Analytics, etc.)
        if (typeof gtag !== 'undefined') {
            gtag('event', 'breadcrumb_click', {
                'breadcrumb_text': breadcrumbText,
                'breadcrumb_url': breadcrumbUrl
            });
        }
        
        // Console log cho development
        console.log('Breadcrumb clicked:', {
            text: breadcrumbText,
            url: breadcrumbUrl
        });
    }

    /**
     * Thêm breadcrumb item động
     */
    addBreadcrumbItem(title, url = null) {
        if (!this.breadcrumbElement) return;

        // Tạo delimiter
        const delimiter = document.createElement('span');
        delimiter.className = 'delimiter';
        delimiter.setAttribute('aria-hidden', 'true');
        delimiter.innerHTML = `
            <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1L5 5L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        `;

        // Tạo breadcrumb item
        const item = document.createElement(url ? 'a' : 'span');
        if (url) {
            item.href = url;
            item.className = 'breadcrumb-link';
        } else {
            item.className = 'breadcrumb-current';
            item.setAttribute('aria-current', 'page');
        }
        item.textContent = title;

        // Thêm vào breadcrumb
        this.breadcrumbElement.appendChild(delimiter);
        this.breadcrumbElement.appendChild(item);

        // Cập nhật responsive
        this.handleResponsiveBreadcrumb();
    }

    /**
     * Cập nhật breadcrumb item cuối cùng
     */
    updateCurrentPage(title) {
        const currentItem = this.breadcrumbElement.querySelector('.breadcrumb-current');
        if (currentItem) {
            currentItem.textContent = title;
        }
    }
}

/**
 * Utility functions cho breadcrumb
 */
const BreadcrumbUtils = {
    /**
     * Tạo breadcrumb từ URL hiện tại
     */
    generateFromUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const page = urlParams.get('page');
        const breadcrumbs = [{ title: 'Trang chủ', url: './' }];

        // Mapping các page với title
        const pageMapping = {
            'products': 'Sản phẩm',
            'details': 'Chi tiết sản phẩm',
            'categories': 'Danh mục',
            'about': 'Giới thiệu',
            'contact': 'Liên hệ',
            'news': 'Tin tức',
            'auth': 'Đăng nhập',
            'register': 'Đăng ký'
        };

        if (page && pageMapping[page]) {
            breadcrumbs.push({ title: pageMapping[page] });
        }

        return breadcrumbs;
    },

    /**
     * Render breadcrumb từ mảng dữ liệu
     */
    render(breadcrumbs, containerId = 'breadcrumb-container') {
        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '<nav class="breadcrumb" aria-label="Breadcrumb navigation">';
        
        breadcrumbs.forEach((item, index) => {
            if (index > 0) {
                html += `
                    <span class="delimiter" aria-hidden="true">
                        <svg width="6" height="10" viewBox="0 0 6 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L1 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                `;
            }

            if (item.url) {
                html += `<a href="${item.url}" class="breadcrumb-link">${item.title}</a>`;
            } else {
                html += `<span class="breadcrumb-current" aria-current="page">${item.title}</span>`;
            }
        });

        html += '</nav>';
        container.innerHTML = html;
    }
};

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', () => {
    new BreadcrumbManager();
});

// Export cho sử dụng global
window.BreadcrumbManager = BreadcrumbManager;
window.BreadcrumbUtils = BreadcrumbUtils;