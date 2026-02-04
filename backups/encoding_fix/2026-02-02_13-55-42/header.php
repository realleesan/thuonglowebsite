    <!-- Top Banner -->
    <div class="top-banner">
        <div class="container">
            <p>ChÃ o má»«ng Ä‘áº¿n vá»›i ThuongLo! Ná»n táº£ng data nguá»“n hÃ ng vÃ  dá»‹ch vá»¥ thÆ°Æ¡ng máº¡i xuyÃªn biÃªn giá»›i hÃ ng Ä‘áº§u. <a href="?page=products">KhÃ¡m phÃ¡ ngay!</a></p>
        </div>
    </div>

    <!-- Main Header -->
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <a href="<?php echo base_url(); ?>">
                        <img src="<?php echo icon_url('logo/logo.svg'); ?>" alt="Thuonglo" width="160" height="36">
                    </a>
                </div>

                <!-- Categories Dropdown -->
                <div class="categories-dropdown <?php echo ($currentPage == 'categories') ? 'active' : ''; ?>">
                    <a href="<?php echo nav_url('categories'); ?>" class="categories-btn">
                        <span>Danh má»¥c </span>
                        <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </a>
                    <div class="categories-menu">
                        <a href="<?php echo page_url('categories', ['category' => 'data-nguon-hang']); ?>">Data nguá»“n hÃ ng</a>
                        <a href="<?php echo page_url('categories', ['category' => 'van-chuyen-chinh-ngach']); ?>">Váº­n chuyá»ƒn chÃ­nh ngáº¡ch</a>
                        <a href="<?php echo page_url('categories', ['category' => 'mua-hang-tron-goi']); ?>">Mua hÃ ng trá»n gÃ³i</a>
                        <a href="<?php echo page_url('categories', ['category' => 'thanh-toan-quoc-te']); ?>">Thanh toÃ¡n quá»‘c táº¿</a>
                        <a href="<?php echo page_url('categories', ['category' => 'dich-vu-danh-hang']); ?>">Dá»‹ch vá»¥ Ä‘Ã¡nh hÃ ng</a>
                        <a href="<?php echo page_url('categories', ['category' => 'phien-dich']); ?>">PhiÃªn dá»‹ch</a>
                        <a href="<?php echo page_url('categories', ['category' => 'ho-tro-di-lai']); ?>">Há»— trá»£ Ä‘i láº¡i</a>
                        <a href="<?php echo page_url('categories', ['category' => 'dich-vu-khac']); ?>">Dá»‹ch vá»¥ khÃ¡c</a>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="search-bar">
                    <form method="get" action="<?php echo base_url(); ?>">
                        <button type="submit" class="search-btn">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.5 11H11.71L11.43 10.73C12.41 9.59 13 8.11 13 6.5C13 2.91 10.09 0 6.5 0C2.91 0 0 2.91 0 6.5C0 10.09 2.91 13 6.5 13C8.11 13 9.59 12.41 10.73 11.43L11 11.71V12.5L16 17.49L17.49 16L12.5 11ZM6.5 11C4.01 11 2 8.99 2 6.5C2 4.01 4.01 2 6.5 2C8.99 2 11 4.01 11 6.5C11 8.99 8.99 11 6.5 11Z" fill="#6B7280"/>
                            </svg>
                        </button>
                        <input type="text" name="search" placeholder="TÃ¬m kiáº¿m dá»‹ch vá»¥, data nguá»“n hÃ ng..." class="search-input">
                        <input type="hidden" value="products" name="page">
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <!-- Main Menu -->
                <?php
                // Get current page
                $currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
                
                // Define page groups for dropdown menus
                $guidePages = ['about', 'guide', 'contact', 'faq'];
                $newsPages = ['news'];
                $productPages = ['products', 'details', 'course-details']; // Removed 'categories' from here
                ?>
                <ul class="main-menu">
                    <li class="<?php echo ($currentPage == 'home') ? 'active' : ''; ?>"><a href="<?php echo base_url(); ?>">Trang chá»§</a></li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $productPages)) ? 'active' : ''; ?>">
                        <a href="<?php echo nav_url('products'); ?>">Sáº£n pháº©m <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <div class="dropdown-menu">
                            <a href="<?php echo page_url('products', ['category' => 'data-nguon-hang']); ?>">Data nguá»“n hÃ ng</a>
                            <a href="<?php echo page_url('products', ['category' => 'van-chuyen-chinh-ngach']); ?>">Váº­n chuyá»ƒn chÃ­nh ngáº¡ch</a>
                            <a href="<?php echo page_url('products', ['category' => 'mua-hang-tron-goi']); ?>">Mua hÃ ng trá»n gÃ³i</a>
                            <a href="<?php echo page_url('products', ['category' => 'thanh-toan-quoc-te']); ?>">Thanh toÃ¡n quá»‘c táº¿</a>
                            <a href="<?php echo page_url('products', ['category' => 'dich-vu-danh-hang']); ?>">Dá»‹ch vá»¥ Ä‘Ã¡nh hÃ ng <span class="new-badge">Hot</span></a>
                            <a href="<?php echo page_url('products', ['category' => 'phien-dich']); ?>">PhiÃªn dá»‹ch</a>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $guidePages)) ? 'active' : ''; ?>">
                        <button type="button" class="dropdown-btn">HÆ°á»›ng dáº«n <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <div class="dropdown-menu">
                            <a href="<?php echo nav_url('about'); ?>">Giá»›i thiá»‡u</a>
                            <a href="<?php echo page_url('guide', ['type' => 'how-to-order']); ?>">CÃ¡ch Ä‘áº·t hÃ ng</a>
                            <a href="<?php echo page_url('guide', ['type' => 'payment']); ?>">HÆ°á»›ng dáº«n thanh toÃ¡n</a>
                            <a href="<?php echo page_url('guide', ['type' => 'shipping']); ?>">Quy trÃ¬nh váº­n chuyá»ƒn</a>
                            <a href="<?php echo nav_url('contact'); ?>">LiÃªn há»‡ há»— trá»£</a>
                            <a href="<?php echo nav_url('faq'); ?>">CÃ¢u há»i thÆ°á»ng gáº·p</a>
                        </div>
                    </li>
                    <li class="has-dropdown <?php echo (in_array($currentPage, $newsPages)) ? 'active' : ''; ?>">
                        <a href="<?php echo nav_url('news'); ?>">Tin tá»©c <svg width="10" height="6" viewBox="0 0 10 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 1L5 5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                        <div class="dropdown-menu">
                            <a href="<?php echo page_url('news', ['category' => 'thuong-mai-xb']); ?>">ThÆ°Æ¡ng máº¡i XB</a>
                            <a href="<?php echo page_url('news', ['category' => 'chinh-sach-hai-quan']); ?>">ChÃ­nh sÃ¡ch háº£i quan</a>
                            <a href="<?php echo page_url('news', ['category' => 'thi-truong-trung-quoc']); ?>">Thá»‹ trÆ°á»ng Trung Quá»‘c</a>
                            <a href="<?php echo page_url('news', ['category' => 'kinh-nghiem-kinh-doanh']); ?>">Kinh nghiá»‡m kinh doanh</a>
                        </div>
                    </li>
                    <li class="<?php echo ($currentPage == 'affiliate') ? 'active' : ''; ?>"><a href="<?php echo nav_url('affiliate'); ?>">Äáº¡i lÃ½</a></li>
                </ul>

                <!-- Right Side Buttons -->
                <div class="header-buttons">
                    <a href="<?php echo nav_url('register'); ?>" class="btn-get-started">ÄÄƒng kÃ½</a>
                    <a href="<?php echo nav_url('login'); ?>" class="btn-login">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M8 8C10.21 8 12 6.21 12 4C12 1.79 10.21 0 8 0C5.79 0 4 1.79 4 4C4 6.21 5.79 8 8 8ZM8 10C5.33 10 0 11.34 0 14V16H16V14C16 11.34 10.67 10 8 10Z" stroke="currentColor" stroke-width="1" fill="none"/>
                        </svg>
                        ÄÄƒng nháº­p
                    </a>
                </div>
            </div>
        </div>
    </nav>