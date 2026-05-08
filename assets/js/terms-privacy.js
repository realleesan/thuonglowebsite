// Terms & Privacy Pages JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Table of contents generation
    const content = document.querySelector('.terms-content, .privacy-content');
    if (!content) return;
    
    const sections = content.querySelectorAll('.terms-section-item h2, .privacy-section-item h2');
    const tocContainer = document.createElement('div');
    tocContainer.className = 'table-of-contents';
    
    const tocTitle = document.createElement('h3');
    tocTitle.textContent = 'Mục lục';
    tocContainer.appendChild(tocTitle);
    
    const tocList = document.createElement('ul');
    
    sections.forEach((section, index) => {
        const listItem = document.createElement('li');
        const link = document.createElement('a');
        
        // Add ID to section
        section.id = `section-${index + 1}`;
        
        link.href = `#section-${index + 1}`;
        link.textContent = section.textContent;
        
        listItem.appendChild(link);
        tocList.appendChild(listItem);
        
        // Smooth scroll
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById(this.getAttribute('href').substring(1));
            const rect = target.getBoundingClientRect();
            const offset = window.pageYOffset + rect.top - 100;
            
            window.scrollTo({
                top: offset,
                behavior: 'smooth'
            });
        });
    });
    
    tocContainer.appendChild(tocList);
    
    // Insert TOC after intro
    const intro = content.querySelector('.terms-intro, .privacy-intro');
    if (intro) {
        intro.parentNode.insertBefore(tocContainer, intro.nextSibling);
    }
    
    // Scroll to top button
    const scrollToTop = document.createElement('button');
    scrollToTop.className = 'scroll-to-top';
    scrollToTop.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollToTop.setAttribute('aria-label', 'Cuộn lên đầu trang');
    
    document.body.appendChild(scrollToTop);
    
    // Show/hide scroll to top button
    function toggleScrollToTop() {
        if (window.pageYOffset > 300) {
            scrollToTop.classList.add('visible');
        } else {
            scrollToTop.classList.remove('visible');
        }
    }
    
    window.addEventListener('scroll', toggleScrollToTop);
    
    scrollToTop.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    // Highlight current section in TOC
    function highlightCurrentSection() {
        const scrollPosition = window.pageYOffset + 150;
        
        sections.forEach((section, index) => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.offsetHeight;
            
            if (scrollPosition >= sectionTop && scrollPosition < sectionTop + sectionHeight) {
                // Remove active class from all links
                tocList.querySelectorAll('a').forEach(link => {
                    link.style.color = '#007bff';
                    link.style.fontWeight = 'normal';
                });
                
                // Add active class to current link
                const currentLink = tocList.querySelector(`a[href="#section-${index + 1}"]`);
                if (currentLink) {
                    currentLink.style.color = '#0056b3';
                    currentLink.style.fontWeight = 'bold';
                }
            }
        });
    }
    
    window.addEventListener('scroll', highlightCurrentSection);
    
    // Copy link functionality
    sections.forEach((section, index) => {
        section.style.position = 'relative';
        
        const copyLink = document.createElement('button');
        copyLink.className = 'copy-link';
        copyLink.innerHTML = '<i class="fas fa-link"></i>';
        copyLink.title = 'Sao chép liên kết';
        
        copyLink.style.cssText = `
            position: absolute;
            top: 0;
            right: 0;
            background: transparent;
            border: none;
            color: #007bff;
            cursor: pointer;
            padding: 5px;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        section.appendChild(copyLink);
        
        section.addEventListener('mouseenter', function() {
            copyLink.style.opacity = '1';
        });
        
        section.addEventListener('mouseleave', function() {
            copyLink.style.opacity = '0';
        });
        
        copyLink.addEventListener('click', function() {
            const url = window.location.origin + window.location.pathname + '#section-' + (index + 1);
            
            navigator.clipboard.writeText(url).then(() => {
                this.innerHTML = '<i class="fas fa-check"></i>';
                this.style.color = '#28a745';
                
                setTimeout(() => {
                    this.innerHTML = '<i class="fas fa-link"></i>';
                    this.style.color = '#007bff';
                }, 2000);
            });
        });
    });
    
    // Search functionality
    const searchContainer = document.createElement('div');
    searchContainer.className = 'search-container';
    searchContainer.innerHTML = `
        <input type="text" placeholder="Tìm kiếm nội dung..." class="content-search">
        <button class="search-clear" style="display: none;">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    searchContainer.style.cssText = `
        margin-bottom: 30px;
        position: relative;
    `;
    
    const searchInput = searchContainer.querySelector('.content-search');
    const searchClear = searchContainer.querySelector('.search-clear');
    
    searchInput.style.cssText = `
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #e9ecef;
        border-radius: 8px;
        font-size: 16px;
        outline: none;
        transition: border-color 0.3s ease;
    `;
    
    searchClear.style.cssText = `
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #6c757d;
        cursor: pointer;
        font-size: 18px;
    `;
    
    // Insert search after TOC
    if (tocContainer.nextSibling) {
        tocContainer.parentNode.insertBefore(searchContainer, tocContainer.nextSibling);
    }
    
    searchInput.addEventListener('focus', function() {
        this.style.borderColor = '#007bff';
    });
    
    searchInput.addEventListener('blur', function() {
        this.style.borderColor = '#e9ecef';
    });
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const sectionItems = document.querySelectorAll('.terms-section-item, .privacy-section-item');
        
        if (searchTerm === '') {
            searchClear.style.display = 'none';
            sectionItems.forEach(item => {
                item.style.display = 'block';
            });
            return;
        }
        
        searchClear.style.display = 'block';
        
        sectionItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            
            if (text.includes(searchTerm)) {
                item.style.display = 'block';
                
                // Highlight search term
                const content = item.innerHTML;
                const highlightedContent = content.replace(
                    new RegExp(searchTerm, 'gi'),
                    match => `<mark style="background: yellow; padding: 2px;">${match}</mark>`
                );
                item.innerHTML = highlightedContent;
            } else {
                item.style.display = 'none';
            }
        });
    });
    
    searchClear.addEventListener('click', function() {
        searchInput.value = '';
        searchInput.dispatchEvent(new Event('input'));
        searchInput.focus();
    });
    
    // Print functionality
    const printButton = document.createElement('button');
    printButton.className = 'print-button';
    printButton.innerHTML = '<i class="fas fa-print"></i> In trang';
    
    printButton.style.cssText = `
        position: fixed;
        bottom: 30px;
        left: 30px;
        background: #6c757d;
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 25px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 8px;
    `;
    
    document.body.appendChild(printButton);
    
    printButton.addEventListener('click', function() {
        window.print();
    });
    
    printButton.addEventListener('mouseenter', function() {
        this.style.background = '#5a6268';
        this.style.transform = 'translateY(-2px)';
    });
    
    printButton.addEventListener('mouseleave', function() {
        this.style.background = '#6c757d';
        this.style.transform = 'translateY(0)';
    });
    
    // Analytics tracking
    sections.forEach((section, index) => {
        section.addEventListener('click', function() {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'section_click', {
                    'section_title': section.textContent,
                    'section_position': index + 1
                });
            }
        });
    });
});
