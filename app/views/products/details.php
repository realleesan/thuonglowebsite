<?php
// Load Models
require_once __DIR__ . '/../../models/ProductsModel.php';
require_once __DIR__ . '/../../models/CategoriesModel.php';

$productsModel = new ProductsModel();
$categoriesModel = new CategoriesModel();

// Get product ID from URL
$productId = $_GET['id'] ?? null;
$product = null;
$category = null;

if ($productId) {
    $product = $productsModel->getById($productId);
    if ($product && isset($product['category_id'])) {
        $category = $categoriesModel->getById($product['category_id']);
    }
}

// Fallback demo data if no product found
if (!$product) {
    $product = [
        'id' => 1,
        'name' => 'Data nguồn hàng chất lượng cao',
        'title' => 'Data nguồn hàng chất lượng cao',
        'description' => 'Cơ sở dữ liệu 10,000+ sản phẩm hot trend từ Trung Quốc với thông tin chi tiết nhà cung cấp uy tín và giá gốc.',
        'price' => 2500000,
        'image' => 'home/home-banner-top.png',
        'status' => 'active',
        'created_at' => date('Y-m-d H:i:s'),
        'category_id' => 1
    ];
    $category = [
        'id' => 1,
        'name' => 'Data nguồn hàng',
        'description' => 'Dữ liệu sản phẩm và nhà cung cấp'
    ];
}

// Product features/objectives
$productFeatures = [
    'Cơ sở dữ liệu 10,000+ sản phẩm hot trend từ Trung Quốc',
    'Thông tin chi tiết nhà cung cấp uy tín và giá gốc',
    'Hướng dẫn tìm kiếm và đánh giá sản phẩm tiềm năng',
    'Công cụ phân tích thị trường và xu hướng tiêu dùng',
    'Hỗ trợ kết nối trực tiếp với nhà cung cấp',
    'Tư vấn chiến lược kinh doanh và marketing',
    'Dịch vụ thanh toán quốc tế an toàn và nhanh chóng',
    'Cập nhật dữ liệu thường xuyên theo thời gian thực'
];

// Package contents
$packageContents = [
    [
        'title' => 'Database sản phẩm hot trend',
        'items' => ['10,000+ sản phẩm được cập nhật hàng tuần', 'Phân loại theo danh mục và độ hot', 'Thông tin giá gốc và margin lợi nhuận']
    ],
    [
        'title' => 'Thông tin nhà cung cấp',
        'items' => ['Danh sách 500+ nhà cung cấp uy tín', 'Thông tin liên hệ và đánh giá', 'Hướng dẫn đàm phán và đặt hàng']
    ],
    [
        'title' => 'Công cụ phân tích',
        'items' => ['Tool phân tích xu hướng thị trường', 'Báo cáo doanh số và lợi nhuận', 'Dự đoán sản phẩm tiềm năng']
    ],
    [
        'title' => 'Hỗ trợ và tư vấn',
        'items' => ['Tư vấn 1-1 với chuyên gia', 'Group hỗ trợ 24/7', 'Khóa học marketing online']
    ]
];

// Provider info
$providerInfo = [
    'name' => 'ThuongLo.com',
    'description' => 'Chuyên gia hàng đầu về thương mại điện tử và nhập khẩu từ Trung Quốc',
    'experience' => '5+ năm kinh nghiệm',
    'customers' => '10,000+ khách hàng tin tưởng',
    'rating' => 4.8,
    'specialties' => ['Nguồn hàng Trung Quốc', 'Thương mại điện tử', 'Marketing online', 'Logistics quốc tế']
];
?>

<!-- Main Content -->
<div id="wrapper-container" class="wrapper-container">
    <div class="content-pusher">
        <div id="main-content">
            <!-- Course Details Section -->
            <section class="course-details-section">
                <div class="container">
                    <div class="course-details-layout">
                        <!-- Left Column - Course Content -->
                        <div class="course-details-main">
                            <!-- Course Header -->
                            <div class="course-header">
                                <h1 class="course-title"><?php echo htmlspecialchars($product['name'] ?? $product['title']); ?></h1>
                                <div class="course-instructor">
                                    <span class="instructor-label">Được cung cấp bởi</span>
                                    <a href="#" class="instructor-name"><?php echo htmlspecialchars($providerInfo['name']); ?></a>
                                </div>
                                <div class="course-meta">
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 1V8L12 12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="1.5"/>
                                        </svg>
                                        <span>Cập nhật <?php echo date('m/Y', strtotime($product['created_at'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>10,000+ sản phẩm</span>
                                    </div>
                                    <div class="meta-item">
                                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Tiếng Việt</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Course Tabs -->
                            <div class="course-tabs">
                                <div class="tabs-nav">
                                    <button class="tab-item active" data-tab="description">Mô tả</button>
                                    <button class="tab-item" data-tab="curriculum">Nội dung gói</button>
                                    <button class="tab-item" data-tab="instructor">Nhà cung cấp</button>
                                </div>

                                <div class="tabs-content">
                                    <!-- Description Tab -->
                                    <div class="tab-panel active" id="description">
                                        <div class="course-description">
                                            <h4>Bạn sẽ nhận được gì</h4>
                                            <div class="learning-objectives">
                                                <div class="objectives-grid">
                                                    <?php foreach ($productFeatures as $feature): ?>
                                                    <div class="objective-item">
                                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M16.6667 5L7.50004 14.1667L3.33337 10" stroke="#356DF1" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                        <span><?php echo htmlspecialchars($feature); ?></span>
                                                    </div>
                                                    <?php endforeach; ?>
                                                        </svg>
                                                        <span>Hệ thống quản lý đơn hàng và theo dõi vận chuyển</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="course-content-description">
                                                <h4>Mô tả gói dịch vụ</h4>
                                                <p>Gói Data Nguồn Hàng Premium của ThuongLo là giải pháp toàn diện cho các doanh nghiệp muốn khởi nghiệp hoặc mở rộng kinh doanh dropshipping từ Trung Quốc. Chúng tôi cung cấp cơ sở dữ liệu sản phẩm được cập nhật liên tục với hơn 10,000 mặt hàng hot trend, kèm theo thông tin chi tiết về nhà cung cấp uy tín và giá cả cạnh tranh.</p>
                                                
                                                <p>Với kinh nghiệm nhiều năm trong lĩnh vực thương mại điện tử xuyên biên giới, ThuongLo không chỉ cung cấp dữ liệu mà còn đồng hành cùng bạn trong suốt quá trình kinh doanh. Từ việc tìm kiếm sản phẩm, đàm phán với nhà cung cấp, đến quản lý đơn hàng và vận chuyển - tất cả đều được hỗ trợ một cách chuyên nghiệp.</p>
                                                
                                                <h5>Yêu cầu</h5>
                                                <ul>
                                                    <li>Có kinh nghiệm cơ bản về thương mại điện tử</li>
                                                    <li>Đã đăng ký tài khoản kinh doanh hợp pháp</li>
                                                    <li>Có khả năng đầu tư vốn ban đầu cho hàng hóa</li>
                                                    <li>Kết nối internet ổn định để sử dụng hệ thống quản lý</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Curriculum Tab -->
                                    <div class="tab-panel" id="curriculum">
                                        <div class="course-curriculum">
                                            <div class="curriculum-section">
                                                <div class="section-header">
                                                    <h5>Phần 1: Cơ sở dữ liệu sản phẩm</h5>
                                                    <span class="section-info">5 danh mục • 3,500 sản phẩm</span>
                                                </div>
                                                <div class="section-lessons">
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M3 8L6 11L13 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Thời trang & Phụ kiện</span>
                                                        <span class="lesson-duration">1,200 SP</span>
                                                    </div>
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 12L3 7L13 7L8 12Z" fill="currentColor"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Điện tử & Công nghệ</span>
                                                        <span class="lesson-duration">800 SP</span>
                                                    </div>
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 12L3 7L13 7L8 12Z" fill="currentColor"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Gia dụng & Nội thất</span>
                                                        <span class="lesson-duration">900 SP</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="curriculum-section">
                                                <div class="section-header">
                                                    <h5>Phần 2: Dịch vụ hỗ trợ</h5>
                                                    <span class="section-info">6 dịch vụ • Hỗ trợ 24/7</span>
                                                </div>
                                                <div class="section-lessons">
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 12L3 7L13 7L8 12Z" fill="currentColor"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Vận chuyển chính ngạch</span>
                                                        <span class="lesson-duration">Toàn quốc</span>
                                                    </div>
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 12L3 7L13 7L8 12Z" fill="currentColor"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Thanh toán quốc tế</span>
                                                        <span class="lesson-duration">An toàn</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="curriculum-section">
                                                <div class="section-header">
                                                    <h5>Phần 3: Dịch vụ cao cấp</h5>
                                                    <span class="section-info">3 dịch vụ • Premium</span>
                                                </div>
                                                <div class="section-lessons">
                                                    <div class="lesson-item">
                                                        <div class="lesson-icon">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 12L3 7L13 7L8 12Z" fill="currentColor"/>
                                                            </svg>
                                                        </div>
                                                        <span class="lesson-title">Đánh hàng & Phiên dịch</span>
                                                        <span class="lesson-duration">Chuyên nghiệp</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Instructor Tab -->
                                    <div class="tab-panel" id="instructor">
                                        <div class="instructor-info">
                                            <div class="instructor-profile">
                                                <div class="instructor-avatar">
                                                    <img src="https://bombyx.live/wp-content/uploads/2022/09/KennyWhite-sq-1024x1024.jpg" alt="ThuongLo.com" />
                                                </div>
                                                <div class="instructor-details">
                                                    <h4>ThuongLo.com</h4>
                                                    <div class="instructor-stats">
                                                        <div class="stat-item">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M8 1L10.09 5.26L15 6L11 9.74L12.18 14.74L8 12.27L3.82 14.74L5 9.74L1 6L5.91 5.26L8 1Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            <span>2,500+ Khách hàng</span>
                                                        </div>
                                                        <div class="stat-item">
                                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                                <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
                                                            </svg>
                                                            <span>5 năm kinh nghiệm</span>
                                                        </div>
                                                    </div>
                                                    <div class="instructor-social">
                                                        <a href="#" class="social-link" aria-label="Facebook">
                                                            <i aria-hidden="true" class="fab fa-facebook"></i>
                                                        </a>
                                                        <a href="#" class="social-link" aria-label="X (Twitter)">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                                            </svg>
                                                        </a>
                                                        <a href="#" class="social-link" aria-label="Linkedin">
                                                            <i aria-hidden="true" class="fab fa-linkedin"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="instructor-bio">
                                                <p>ThuongLo là đội ngũ chuyên gia có hơn 5 năm kinh nghiệm trong lĩnh vực thương mại điện tử xuyên biên giới, chuyên cung cấp các giải pháp dropshipping từ Trung Quốc về Việt Nam.</p>
                                                <p>Chúng tôi đã hỗ trợ hơn 2,500 khách hàng thành công trong việc xây dựng và phát triển cửa hàng online, với tổng doanh thu đạt hàng trăm tỷ đồng. Đội ngũ của chúng tôi bao gồm các chuyên gia về sourcing, logistics, marketing và customer service.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reviews Section -->
                            <div class="reviews-section">
                                <h4>Đánh giá khách hàng</h4>
                                <div class="reviews-summary">
                                    <div class="rating-overview">
                                        <div class="rating-score">
                                            <span class="score">4.8</span>
                                            <div class="stars">
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <span class="rating-count">156 đánh giá</span>
                                        </div>
                                        <div class="rating-breakdown">
                                            <div class="rating-bar">
                                                <span>5</span>
                                                <div class="bar">
                                                    <div class="fill" style="width: 85%"></div>
                                                </div>
                                                <span>132</span>
                                            </div>
                                            <div class="rating-bar">
                                                <span>4</span>
                                                <div class="bar">
                                                    <div class="fill" style="width: 10%"></div>
                                                </div>
                                                <span>16</span>
                                            </div>
                                            <div class="rating-bar">
                                                <span>3</span>
                                                <div class="bar">
                                                    <div class="fill" style="width: 3%"></div>
                                                </div>
                                                <span>5</span>
                                            </div>
                                            <div class="rating-bar">
                                                <span>2</span>
                                                <div class="bar">
                                                    <div class="fill" style="width: 1%"></div>
                                                </div>
                                                <span>2</span>
                                            </div>
                                            <div class="rating-bar">
                                                <span>1</span>
                                                <div class="bar">
                                                    <div class="fill" style="width: 1%"></div>
                                                </div>
                                                <span>1</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Course Sidebar -->
                        <div class="course-sidebar">
                            <div class="course-card">
                                <div class="course-badge">
                                    <span>Premium</span>
                                </div>
                                <div class="course-image">
                                    <img src="https://eduma.thimpress.com/demo-marketplace/wp-content/uploads/sites/99/2022/11/create-an-lms-website-with-learnpress-4-675x450.png" alt="Gói Data Nguồn Hàng Premium" />
                                </div>
                                <div class="course-card-content">
                                    <div class="course-price">
                                        <span class="price">2,500,000₫</span>
                                    </div>
                                    <button class="btn-enroll">Đăng ký ngay</button>
                                    
                                    <div class="course-includes">
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M16 8C16 12.4183 12.4183 16 8 16C3.58172 16 0 12.4183 0 8C0 3.58172 3.58172 0 8 0C12.4183 0 16 3.58172 16 8Z" fill="#356DF1"/>
                                                <path d="M11.3333 6L7 10.3333L4.66667 8" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>98% khách hàng hài lòng</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 1L10.09 5.26L15 6L11 9.74L12.18 14.74L8 12.27L3.82 14.74L5 9.74L1 6L5.91 5.26L8 1Z" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>2,500+ khách hàng</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M5.33333 6.49992H8M5.33333 9.16659H10.6667M5.33333 11.8333H10.6667M10.6663 1.83325V3.83325M5.33301 1.83325V3.83325M4.66667 2.83325H11.3333C12.8061 2.83325 14 4.02716 14 5.49992V12.4999C14 13.9727 12.8061 15.1666 11.3333 15.1666H4.66667C3.19391 15.1666 2 13.9727 2 12.4999V5.49992C2 4.02716 3.19391 2.83325 4.66667 2.83325Z" stroke="#356DF1" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>10,000+ sản phẩm</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 2L10.09 6.26L15 7L11 10.74L12.18 15.74L8 13.27L3.82 15.74L5 10.74L1 7L5.91 6.26L8 2Z" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>Ngôn ngữ: Tiếng Việt</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 8L6 11L13 4" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>Hỗ trợ: 24/7</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 1V8L12 12" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="8" cy="8" r="7" stroke="#356DF1" stroke-width="1.5"/>
                                            </svg>
                                            <span>Có app mobile</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8 1V8L12 12" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                <circle cx="8" cy="8" r="7" stroke="#356DF1" stroke-width="1.5"/>
                                            </svg>
                                            <span>Truy cập không giới hạn</span>
                                        </div>
                                        <div class="include-item">
                                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M3 8L6 11L13 4" stroke="#356DF1" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                            <span>Phù hợp</span>
                                            <span class="skill-level">Mọi cấp độ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Include Related Courses Section -->
            <?php include __DIR__ . '/../_layout/related.php'; ?>
        </div>
    </div>
</div>