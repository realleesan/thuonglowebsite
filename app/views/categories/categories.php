<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <div class="elementor elementor-15130">
                <?php 
                // Breadcrumb sẽ được hiển thị từ master layout
                ?>

                <!-- Main Categories Section -->
                <section class="categories-section">
                    <div class="container">
                        <div class="categories-layout">
                            <!-- Left Column - Categories -->
                            <div class="categories-main">
                                <!-- Header with Title and Filter Button -->
                                <div class="categories-header">
                                    <h1 class="page-title">Danh Mục</h1>
                                    <button class="filter-toggle-btn" id="filterToggle">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
                                            <path d="M8.37013 7.79006C8.42013 8.22006 8.73013 8.55006 9.17013 8.63006C9.24013 8.64006 9.31013 8.65006 9.38013 8.65006C9.74013 8.65006 10.0701 8.46006 10.2401 8.15006C10.2401 8.15006 10.3701 7.93006 10.3701 7.86006V6.83006H21.3701C21.4801 6.83006 21.8501 6.61006 21.9301 6.52006C22.1301 6.31006 22.2301 5.99006 22.1801 5.68006C22.1401 5.36006 21.9601 5.10006 21.7001 4.95006C21.6801 4.94006 21.3401 4.81006 21.2801 4.81006H10.3701V3.77006C10.3701 3.64006 10.1101 3.30006 10.0601 3.25006C9.80013 3.01006 9.39013 2.94006 9.03013 3.07006C8.68013 3.19006 8.44013 3.47006 8.39013 3.81006C8.34013 4.16006 8.36013 4.61006 8.37013 5.05006C8.37013 5.25006 8.39013 5.44006 8.39013 5.61006C8.39013 5.78006 8.39013 5.96006 8.37013 6.16006C8.35013 6.71006 8.33013 7.34006 8.37013 7.80006V7.79006Z" fill="#098CE9"></path>
                                            <path d="M21.3701 17.5401H10.3701V16.5101C10.3701 16.4501 10.2201 16.1701 10.2201 16.1601C9.99013 15.8101 9.57013 15.6601 9.14013 15.7501C8.72013 15.8501 8.42013 16.1701 8.37013 16.5801C8.34013 16.9301 8.35013 17.3401 8.37013 17.7401C8.37013 17.9501 8.38013 18.1501 8.38013 18.3401C8.38013 18.5001 8.38013 18.6801 8.36013 18.8801C8.34013 19.4601 8.31013 20.1201 8.38013 20.5701C8.42013 20.8601 8.61013 21.1101 8.89013 21.2601C9.05013 21.3401 9.22013 21.3801 9.39013 21.3801C9.56013 21.3801 9.71013 21.3401 9.85013 21.2701C10.0201 21.1901 10.3701 20.8201 10.3701 20.6101V19.5801H21.2801C21.3401 19.5801 21.6801 19.4501 21.7001 19.4401C21.9601 19.2901 22.1301 19.0201 22.1701 18.7101C22.2101 18.4001 22.1201 18.0801 21.9101 17.8601C21.8601 17.8101 21.4801 17.5501 21.3501 17.5501L21.3701 17.5401Z" fill="#098CE9"></path>
                                            <path d="M14.3401 9.45006C14.0301 9.31006 13.7001 9.32006 13.4301 9.49006C13.1301 9.67006 12.9201 10.0201 12.8901 10.4201C12.8101 11.4001 12.8301 13.0001 12.8901 13.9301C12.9301 14.3901 13.1701 14.7801 13.5201 14.9501C13.6401 15.0101 13.7701 15.0301 13.9001 15.0301C14.1101 15.0301 14.3101 14.9601 14.5101 14.8201C14.6601 14.7201 14.9201 14.3701 14.9201 14.1701V13.1901H21.4001C21.4001 13.1901 21.4501 13.1901 21.6901 13.0701C21.9901 12.9001 22.1801 12.5901 22.1901 12.2301C22.2001 11.8701 22.0301 11.5401 21.7401 11.3601C21.7201 11.3501 21.4601 11.2101 21.3901 11.2101H14.9101V10.2201C14.9101 9.96006 14.5501 9.56006 14.3301 9.46006L14.3401 9.45006Z" fill="#098CE9"></path>
                                            <path d="M2.84013 13.1801H11.3801C11.8701 13.0601 12.2001 12.6701 12.2101 12.2001C12.2101 11.7301 11.9201 11.3401 11.4301 11.2001H2.77013C2.23013 11.3501 2.00013 11.8201 2.00013 12.2201C2.01013 12.7001 2.33013 13.0801 2.83013 13.1901L2.84013 13.1801Z" fill="#098CE9"></path>
                                            <path d="M2.84013 6.82006H6.82013C7.40013 6.69006 7.66013 6.22006 7.65013 5.80006C7.65013 5.39006 7.38013 4.92006 6.77013 4.81006C6.25013 4.84006 5.66013 4.81006 5.09013 4.78006C4.35013 4.74006 3.58013 4.70006 2.92013 4.79006C2.31013 4.90006 2.02013 5.36006 2.00013 5.79006C1.98013 6.21006 2.23013 6.69006 2.83013 6.83006L2.84013 6.82006Z" fill="#098CE9"></path>
                                            <path d="M6.86013 17.5501H2.82013C2.23013 17.6901 1.98013 18.1801 2.00013 18.5901C2.02013 19.0101 2.31013 19.4701 2.92013 19.5601C3.22013 19.6001 3.54013 19.6201 3.87013 19.6201C4.23013 19.6201 4.60013 19.6001 4.96013 19.5901C5.55013 19.5601 6.15013 19.5401 6.69013 19.5901H6.71013C7.31013 19.5101 7.60013 19.0601 7.63013 18.6401C7.66013 18.2201 7.43013 17.7201 6.84013 17.5701L6.86013 17.5501Z" fill="#098CE9"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Top Bar with Results and Sort -->
                                <div class="categories-topbar">
                                    <div class="results-count">
                                        <span>Hiển thị 1-12 trong 15 danh mục</span>
                                    </div>
                                    <div class="sort-dropdown">
                                        <form method="get">
                                            <select name="order_by" class="sort-select">
                                                <option value="name" selected>Tên A-Z</option>
                                                <option value="name_desc">Tên Z-A</option>
                                                <option value="course_count">Nhiều sản phẩm nhất</option>
                                                <option value="course_count_desc">Ít sản phẩm nhất</option>
                                                <option value="popular">Phổ biến nhất</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Categories Grid -->
                                <div class="categories-grid">
                                    <!-- Category Item 1 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phổ biến</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Gói Data Nguồn Hàng" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Gói Data Nguồn Hàng</a>
                                            </h3>
                                            <div class="category-description">
                                                Cơ sở dữ liệu nhà cung cấp uy tín với hàng triệu sản phẩm chất lượng cao từ Trung Quốc và các nước châu Á.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>24 Gói dữ liệu</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 2 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Xu hướng</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Vận Chuyển Chính Ngạch" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Vận Chuyển Chính Ngạch</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ vận chuyển hàng hóa từ Trung Quốc về Việt Nam qua đường chính ngạch, đảm bảo an toàn và hợp pháp.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>18 Dịch vụ</span>
                                                </div>                                             
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 3 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Mới</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Mua Hàng Trọn Gói" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Mua Hàng Trọn Gói</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ mua hàng trọn gói từ A-Z: Tìm nguồn → Đặt hàng → Thanh toán NCC → Vận chuyển → Giao hàng tận nơi.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>15 Gói dịch vụ</span>
                                                </div>                                             
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 4 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phổ biến</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Thanh Toán Quốc Tế" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Thanh Toán Quốc Tế</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ thanh toán quốc tế an toàn, nhanh chóng với tỷ giá ưu đãi cho các giao dịch thương mại.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>21 Phương thức</span>
                                                </div>                                                
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 5 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Hot</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Dịch Vụ Đánh Hàng" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Dịch Vụ Đánh Hàng</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ đánh hàng chuyên nghiệp bao gồm: Phiên dịch, hỗ trợ đi lại, ăn ở tại Trung Quốc.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>32 Dịch vụ</span>
                                                </div>                                             
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 6 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phổ biến</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Phiên Dịch Thương Mại" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Phiên Dịch Thương Mại</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ phiên dịch chuyên nghiệp cho các cuộc đàm phán thương mại, hội nghị và giao dịch kinh doanh.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>12 Dịch vụ</span>
                                                </div>                                                
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 7 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phát triển</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Hỗ Trợ Đi Lại" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Hỗ Trợ Đi Lại</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ hỗ trợ đi lại, đưa đón sân bay, di chuyển trong thành phố và các chuyến công tác tại Trung Quốc.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>16 Dịch vụ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 8 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Hot</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Dịch Vụ Ăn Ở" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Dịch Vụ Ăn Ở</a>
                                            </h3>
                                            <div class="category-description">
                                                Hỗ trợ đặt khách sạn, nhà hàng và các dịch vụ ăn ở chất lượng cao trong các chuyến công tác.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>28 Dịch vụ</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 9 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phổ biến</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Tư Vấn Kinh Doanh" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Tư Vấn Kinh Doanh</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ tư vấn chiến lược kinh doanh, phát triển thị trường và mở rộng hoạt động thương mại.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>19 Dịch vụ</span>
                                                </div>                                            
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 10 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phát triển</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Kho Bãi & Logistics" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Kho Bãi & Logistics</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ kho bãi, lưu trữ hàng hóa và quản lý chuỗi cung ứng chuyên nghiệp tại Trung Quốc.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>14 Dịch vụ</span>
                                                </div>                                          
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 11 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Hot</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Kiểm Định Chất Lượng" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Kiểm Định Chất Lượng</a>
                                            </h3>
                                            <div class="category-description">
                                                Dịch vụ kiểm định chất lượng sản phẩm, giám sát sản xuất và đảm bảo tiêu chuẩn xuất khẩu.al finance, investment strategies, and wealth building techniques from experts.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>22 Dịch vụ</span>
                                                </div>                                              
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Category Item 12 -->
                                    <div class="category-item">
                                        <div class="category-tag-wrapper">
                                            <a href="#" class="category-tag">Phổ biến</a>
                                        </div>
                                        <div class="category-image">
                                            <a href="?page=products">
                                                <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" 
                                                     alt="Hỗ Trợ Pháp Lý" loading="lazy">
                                            </a>
                                        </div>
                                        <div class="category-content">
                                            <h3 class="category-title">
                                                <a href="?page=products">Hỗ Trợ Pháp Lý</a>
                                            </h3>
                                            <div class="category-description">
                                                Tư vấn pháp lý thương mại, hỗ trợ thủ tục xuất nhập khẩu và giải quyết các vấn đề pháp lý.
                                            </div>
                                            <div class="category-meta">
                                                <div class="course-count">
                                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M2 4H14M2 8H14M2 12H10" stroke="#6c757d" stroke-width="1.5" stroke-linecap="round"/>
                                                    </svg>
                                                    <span>26 Dịch vụ</span>
                                                </div>                                              
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Pagination -->
                                <div class="pagination-wrapper">
                                    <nav class="pagination">
                                        <a href="#" class="page-link prev">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M12.5 15L7.5 10L12.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                        <a href="#" class="page-link active">1</a>
                                        <a href="#" class="page-link">2</a>
                                        <a href="#" class="page-link next">
                                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M7.5 15L12.5 10L7.5 5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    </nav>
                                </div>
                            </div>

                            <!-- Right Column - Sidebar -->
                            <div class="categories-sidebar" id="categoriesSidebar">
                                <div class="sidebar-header">
                                    <h3>Bộ Lọc</h3>
                                    <button class="sidebar-close" id="sidebarClose">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="sidebar-content">
                                    <!-- Category Type Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Loại Danh Mục</h3>
                                        <div class="filter-content">
                                            <ul class="category-type-list">
                                                <li><a href="#">Tất Cả Danh Mục</a> <span class="count">(15)</span></li>
                                                <li><a href="#">Phổ Biến Nhất</a> <span class="count">(8)</span></li>
                                                <li><a href="#">Xu Hướng</a> <span class="count">(5)</span></li>
                                                <li><a href="#">Danh Mục Mới</a> <span class="count">(3)</span></li>
                                                <li><a href="#">Chủ Đề Hot</a> <span class="count">(4)</span></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Reset Button -->
                                    <div class="filter-section">
                                        <button class="reset-filters-btn">Đặt Lại</button>
                                    </div>

                                    <!-- Course Count Filter -->
                                    <div class="filter-section">
                                        <h3 class="filter-title">Số Lượng Sản Phẩm</h3>
                                        <div class="filter-content">
                                            <ul class="course-count-list">
                                                <li><a href="#">10+ Sản Phẩm</a></li>
                                                <li><a href="#">20+ Sản Phẩm</a></li>
                                                <li><a href="#">30+ Sản Phẩm</a></li>
                                            </ul>
                                        </div>
                                    </div>

                                    <!-- Apply Button -->
                                    <div class="filter-section">
                                        <button class="apply-filters-btn">Áp Dụng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>