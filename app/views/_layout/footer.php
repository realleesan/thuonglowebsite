<footer id="colophon" class="site-footer">
    <div class="footer">
        <div class="container">
            <div class="row">
                <aside id="text-1210021" class="widget widget_text footer_widget">
                    <div class="textwidget">
                        <div data-elementor-type="wp-post" data-elementor-id="8920" class="elementor elementor-8920">
                            <section class="elementor-section elementor-top-section elementor-element elementor-element-3683d89 elementor-section-stretched elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="3683d89" data-element_type="section" data-settings="{&quot;background_background&quot;:&quot;classic&quot;,&quot;stretch_section&quot;:&quot;section-stretched&quot;}">
                                <div class="elementor-container elementor-column-gap-custom">
                                    <!-- Logo and Contact Sale Column -->
                                    <div class="elementor-column elementor-col-25 elementor-top-column elementor-element elementor-element-b0d2307" data-id="b0d2307" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-18fb26a elementor-widget-mobile__width-inherit elementor-widget elementor-widget-image" data-id="18fb26a" data-element_type="widget" data-widget_type="image.default">
                                                <div class="elementor-widget-container">
                                                    <a href="<?php echo base_url(); ?>">
                                                        <img loading="lazy" decoding="async" width="160" height="36" src="<?php echo icon_url('logo/logo.svg'); ?>" class="attachment-full size-full wp-image-14235" alt="ThuongLo">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-d6709c2 elementor-widget thim-widget-button" data-id="d6709c2" data-element_type="widget" data-widget_type="thim-button.default">
                                                <div class="elementor-widget-container">
                                                    <a href="<?php echo nav_url('contact'); ?>">Liên hệ tư vấn</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Categories Column -->
                                    <div class="elementor-column elementor-col-25 elementor-top-column elementor-element elementor-element-ed9e25f" data-id="ed9e25f" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-ceb6744 thim-ekits-heading--mobiletext-left elementor-widget thim-ekits-heading elementor-widget-thim-heading" data-id="ceb6744" data-element_type="widget" data-widget_type="thim-heading.default">
                                                <div class="elementor-widget-container">
                                                    <div class="sc_heading">
                                                        <h4 class="title">Danh mục</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-ff87c16 elementor-widget elementor-widget-thim-ekits-header-info" data-id="ff87c16" data-element_type="widget" data-widget_type="thim-ekits-header-info.default">
                                                <div class="elementor-widget-container">
                                                    <div class="header-info-swapper">
                                                        <ul class="thim-header-info">
                                                            <?php
                                                            // Get categories from database
                                                            require_once __DIR__ . '/../../../app/models/CategoriesModel.php';
                                                            $categoriesModel = new CategoriesModel();
                                                            $categories = $categoriesModel->getActiveForFilter();
                                                            
                                                            // Show first 5 categories
                                                            $displayCategories = array_slice($categories, 0, 5);
                                                            foreach ($displayCategories as $category):
                                                            ?>
                                                                <li><a href="?page=products&category=<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Brands Column -->
                                    <div class="elementor-column elementor-col-25 elementor-top-column elementor-element elementor-element-brands" data-id="brands" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-brands-heading thim-ekits-heading--mobiletext-left elementor-widget thim-ekits-heading elementor-widget-thim-heading" data-id="brands-heading" data-element_type="widget" data-widget_type="thim-heading.default">
                                                <div class="elementor-widget-container">
                                                    <div class="sc_heading">
                                                        <h4 class="title">Thương hiệu</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-brands-list elementor-widget elementor-widget-thim-ekits-header-info" data-id="brands-list" data-element_type="widget" data-widget_type="thim-ekits-header-info.default">
                                                <div class="elementor-widget-container">
                                                    <div class="header-info-swapper">
                                                        <ul class="thim-header-info">
                                                            <?php
                                                            // Get brands from database
                                                            require_once __DIR__ . '/../../../app/models/BrandsModel.php';
                                                            $brandsModel = new BrandsModel();
                                                            $brands = $brandsModel->getForFilter();
                                                            
                                                            // Show first 5 brands
                                                            $displayBrands = array_slice($brands, 0, 5);
                                                            foreach ($displayBrands as $brand):
                                                            ?>
                                                                <li><a href="?page=products&brand=<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></a></li>
                                                            <?php endforeach; ?>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Support Column -->
                                    <div class="elementor-column elementor-col-25 elementor-top-column elementor-element elementor-element-9fe5798" data-id="9fe5798" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-2388626 thim-ekits-heading--mobiletext-left elementor-widget thim-ekits-heading elementor-widget-thim-heading" data-id="2388626" data-element_type="widget" data-widget_type="thim-heading.default">
                                                <div class="elementor-widget-container">
                                                    <div class="sc_heading">
                                                        <h4 class="title">Hỗ trợ</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-ad93be3 elementor-widget elementor-widget-thim-ekits-header-info" data-id="ad93be3" data-element_type="widget" data-widget_type="thim-ekits-header-info.default">
                                                <div class="elementor-widget-container">
                                                    <div class="header-info-swapper">
                                                        <ul class="thim-header-info">
                                                            <li><a href="?page=contact">Liên hệ hỗ trợ</a></li>
                                                            <li><a href="?page=faq">Câu hỏi thường gặp</a></li>
                                                            <li><a href="?page=shopping-guide">Hướng dẫn mua hàng</a></li>
                                                            <li><a href="?page=users">Quản lý tài khoản</a></li>
                                                            <li><a href="?page=news">Tin tức thời trang</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Company Column -->
                                    <div class="elementor-column elementor-col-25 elementor-top-column elementor-element elementor-element-f095090" data-id="f095090" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-0e3565b thim-ekits-heading--mobiletext-left elementor-widget thim-ekits-heading elementor-widget-thim-heading" data-id="0e3565b" data-element_type="widget" data-widget_type="thim-heading.default">
                                                <div class="elementor-widget-container">
                                                    <div class="sc_heading">
                                                        <h4 class="title">Công ty</h4>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-c27122d elementor-widget elementor-widget-thim-ekits-header-info" data-id="c27122d" data-element_type="widget" data-widget_type="thim-ekits-header-info.default">
                                                <div class="elementor-widget-container">
                                                    <div class="header-info-swapper">
                                                        <ul class="thim-header-info">
                                                            <li><a href="?page=about">Giới thiệu</a></li>
                                                            <li><a href="?page=contact">Liên hệ</a></li>
                                                            <li><a href="?page=affiliate">Trở thành đại lý</a></li>
                                                            <li><a href="?page=terms">Điều khoản dịch vụ</a></li>
                                                            <li><a href="?page=privacy">Chính sách bảo mật</a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- Bottom Section with Copyright and Social -->
                            <section class="elementor-section elementor-top-section elementor-element elementor-element-8ffc780 elementor-section-content-middle elementor-section-stretched elementor-section-boxed elementor-section-height-default elementor-section-height-default" data-id="8ffc780" data-element_type="section" data-settings="{&quot;stretch_section&quot;:&quot;section-stretched&quot;,&quot;background_background&quot;:&quot;classic&quot;}">
                                <div class="elementor-container elementor-column-gap-custom">
                                    <div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-7964784" data-id="7964784" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-203b476 elementor-widget elementor-widget-text-editor" data-id="203b476" data-element_type="widget" data-widget_type="text-editor.default">
                                                <div class="elementor-widget-container">
                                                    <div>
                                                        <div>© 2025 <a href="<?php echo base_url(); ?>">ThuongLo</a>. Tất cả quyền được bảo lưu.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="elementor-column elementor-col-50 elementor-top-column elementor-element elementor-element-947903a" data-id="947903a" data-element_type="column">
                                        <div class="elementor-widget-wrap elementor-element-populated">
                                            <div class="elementor-element elementor-element-b66ba79 elementor-widget__width-initial elementor-widget elementor-widget-text-editor" data-id="b66ba79" data-element_type="widget" data-widget_type="text-editor.default">
                                                <div class="elementor-widget-container">
                                                    <p>Kết nối với chúng tôi</p>
                                                </div>
                                            </div>
                                            <div class="elementor-element elementor-element-35fbdc8 elementor-widget__width-initial elementor-widget elementor-widget-thim-ekits-social" data-id="35fbdc8" data-element_type="widget" data-widget_type="thim-ekits-social.default">
                                                <div class="elementor-widget-container">
                                                    <div class="social-swapper">
                                                        <ul class="thim-social-media">
                                                            <li class="elementor-repeater-item-4b2b659">
                                                                <a href="https://facebook.com" aria-label="Facebook">
                                                                    <i aria-hidden="true" class="fab fa-facebook"></i>
                                                                </a>
                                                            </li>
                                                            <li class="elementor-repeater-item-7fc7620">
                                                                <a href="https://youtube.com" aria-label="Youtube">
                                                                    <i aria-hidden="true" class="fab fa-youtube"></i>
                                                                </a>
                                                            </li>
                                                            <li class="elementor-repeater-item-4a46acf">
                                                                <a href="https://instagram.com" aria-label="Instagram">
                                                                    <i aria-hidden="true" class="fab fa-instagram"></i>
                                                                </a>
                                                            </li>
                                                            <li class="elementor-repeater-item-6795e2d">
                                                                <a href="https://twitter.com" aria-label="X (Twitter)">
                                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                                    </svg>
                                                                </a>
                                                            </li>
                                                            <li class="elementor-repeater-item-9063424">
                                                                <a href="https://tiktok.com" aria-label="Tiktok">
                                                                    <i aria-hidden="true" class="fab fa-tiktok"></i>
                                                                </a>
                                                            </li>
                                                            <li class="elementor-repeater-item-f783640">
                                                                <a href="https://linkedin.com" aria-label="Linkedin">
                                                                    <i aria-hidden="true" class="fab fa-linkedin"></i>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </div>


</footer>