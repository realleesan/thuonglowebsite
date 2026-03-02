-- Update news data only (columns already exist)
-- This file only updates existing data, does not create columns

-- First, run create_news_categories.sql to create categories

-- Reset views to 0 for all news
UPDATE `news` SET `views` = 0;

-- Get category IDs
SET @cat_thuong_mai_xb = (SELECT id FROM categories WHERE slug = 'thuong-mai-xb' AND type = 'news' LIMIT 1);
SET @cat_chinh_sach = (SELECT id FROM categories WHERE slug = 'chinh-sach-hai-quan' AND type = 'news' LIMIT 1);
SET @cat_thi_truong = (SELECT id FROM categories WHERE slug = 'thi-truong-trung-quoc' AND type = 'news' LIMIT 1);
SET @cat_kinh_nghiem = (SELECT id FROM categories WHERE slug = 'kinh-nghiem-kinh-doanh' AND type = 'news' LIMIT 1);

-- Update news #1 - Thương mại XB
UPDATE `news` SET 
    `category_id` = @cat_thuong_mai_xb,
    `author_name` = 'Admin',
    `tags` = 'data-nguon-hang,van-chuyen,trung-quoc',
    `views` = 0,
    `content` = '<h2>Giới thiệu về Data Nguồn Hàng Trung Quốc</h2>
<p>Trong bối cảnh thương mại điện tử phát triển mạnh mẽ, việc tìm kiếm nguồn hàng chất lượng từ Trung Quốc đang trở thành nhu cầu thiết yếu của nhiều doanh nghiệp Việt Nam. Data nguồn hàng không chỉ giúp bạn tiết kiệm thời gian mà còn tối ưu hóa chi phí kinh doanh.</p>

<h3>Tại sao cần Data Nguồn Hàng?</h3>
<p>Data nguồn hàng cung cấp thông tin chi tiết về:</p>
<ul>
    <li>Thông tin nhà cung cấp uy tín</li>
    <li>Giá cả cạnh tranh từ nhiều nguồn khác nhau</li>
    <li>Sản phẩm hot trend trên thị trường</li>
    <li>Đánh giá chất lượng từ người mua thực tế</li>
</ul>

<h3>Cách Tìm Kiếm Nguồn Hàng Hiệu Quả</h3>
<p>Để tìm được nguồn hàng chất lượng, bạn cần:</p>
<ol>
    <li>Xác định rõ sản phẩm muốn kinh doanh</li>
    <li>Nghiên cứu thị trường và đối thủ cạnh tranh</li>
    <li>Sử dụng các nền tảng uy tín như 1688, Taobao, Alibaba</li>
    <li>Kiểm tra độ tin cậy của nhà cung cấp</li>
    <li>So sánh giá cả và chất lượng</li>
</ol>

<p>Với kinh nghiệm nhiều năm trong lĩnh vực thương mại xuyên biên giới, ThuongLo.com cam kết cung cấp data nguồn hàng chất lượng cao, giúp bạn tiết kiệm thời gian và chi phí trong quá trình kinh doanh.</p>'
WHERE `id` = 1;

-- Update news #2 - Chính sách hải quan
UPDATE `news` SET 
    `category_id` = @cat_chinh_sach,
    `author_name` = 'Admin',
    `tags` = 'hai-quan,chinh-sach,nhap-khau',
    `views` = 0,
    `content` = '<h2>Chính Sách Hải Quan Mới Nhất 2024</h2>
<p>Năm 2024 đánh dấu nhiều thay đổi quan trọng trong chính sách hải quan Việt Nam, ảnh hưởng trực tiếp đến hoạt động nhập khẩu hàng hóa từ Trung Quốc.</p>

<h3>Những Thay Đổi Chính</h3>
<p>Các điểm mới trong chính sách hải quan:</p>
<ul>
    <li>Điều chỉnh mức thuế suất với một số mặt hàng</li>
    <li>Đơn giản hóa thủ tục hải quan điện tử</li>
    <li>Tăng cường kiểm tra chất lượng hàng hóa</li>
    <li>Quy định mới về hàng hóa cấm nhập khẩu</li>
</ul>

<h3>Thủ Tục Hải Quan Cần Biết</h3>
<p>Để thông quan hàng hóa nhanh chóng, bạn cần chuẩn bị:</p>
<ol>
    <li>Hợp đồng mua bán hàng hóa</li>
    <li>Hóa đơn thương mại (Commercial Invoice)</li>
    <li>Vận đơn (Bill of Lading)</li>
    <li>Giấy chứng nhận xuất xứ (C/O)</li>
    <li>Các giấy tờ chuyên ngành (nếu có)</li>
</ol>

<p>ThuongLo.com cung cấp dịch vụ tư vấn hải quan chuyên nghiệp, giúp bạn hiểu rõ quy định và tiết kiệm chi phí nhập khẩu.</p>'
WHERE `id` = 2;

-- Update news #3 - Thị trường Trung Quốc
UPDATE `news` SET 
    `category_id` = @cat_thi_truong,
    `author_name` = 'Admin',
    `tags` = 'trung-quoc,thi-truong,kinh-doanh',
    `views` = 0,
    `content` = '<h2>Thị Trường Trung Quốc - Cơ Hội Và Thách Thức</h2>
<p>Thị trường Trung Quốc với quy mô hơn 1.4 tỷ dân đang là điểm đến hấp dẫn cho các doanh nghiệp Việt Nam muốn mở rộng kinh doanh.</p>

<h3>Tổng Quan Thị Trường</h3>
<p>Những đặc điểm nổi bật của thị trường Trung Quốc:</p>
<ul>
    <li>Quy mô thị trường khổng lồ với sức mua cao</li>
    <li>Hệ thống logistics phát triển</li>
    <li>Nền tảng thương mại điện tử tiên tiến</li>
    <li>Cạnh tranh khốc liệt</li>
</ul>

<h3>Cơ Hội Kinh Doanh</h3>
<p>Các ngành hàng tiềm năng:</p>
<ul>
    <li>Nông sản, thực phẩm sạch</li>
    <li>Mỹ phẩm, chăm sóc sức khỏe</li>
    <li>Thời trang, phụ kiện</li>
    <li>Điện tử, công nghệ</li>
</ul>

<p>ThuongLo.com hỗ trợ doanh nghiệp Việt tiếp cận thị trường Trung Quốc với các giải pháp toàn diện từ tìm nguồn hàng đến logistics.</p>'
WHERE `id` = 3;

-- Update news #4 - Kinh nghiệm kinh doanh
UPDATE `news` SET 
    `category_id` = @cat_kinh_nghiem,
    `author_name` = 'Admin',
    `tags` = 'kinh-nghiem,kinh-doanh,xuat-khau',
    `views` = 0,
    `content` = '<h2>Kinh Nghiệm Kinh Doanh Xuyên Biên Giới</h2>
<p>Kinh doanh xuyên biên giới đang trở thành xu hướng phổ biến, mang lại cơ hội lớn cho các doanh nghiệp vừa và nhỏ.</p>

<h3>Bắt Đầu Như Thế Nào?</h3>
<p>Các bước cơ bản để khởi nghiệp:</p>
<ol>
    <li>Nghiên cứu thị trường và sản phẩm</li>
    <li>Xây dựng kế hoạch kinh doanh chi tiết</li>
    <li>Tìm kiếm nguồn vốn khởi nghiệp</li>
    <li>Thiết lập mối quan hệ với nhà cung cấp</li>
    <li>Xây dựng kênh bán hàng</li>
</ol>

<h3>Quản Lý Nguồn Hàng</h3>
<p>Bí quyết quản lý hiệu quả:</p>
<ul>
    <li>Đa dạng hóa nhà cung cấp</li>
    <li>Kiểm soát chất lượng nghiêm ngặt</li>
    <li>Tối ưu hóa tồn kho</li>
    <li>Xây dựng mối quan hệ lâu dài</li>
</ul>

<p>Với hơn 10 năm kinh nghiệm, ThuongLo.com chia sẻ những bài học quý báu giúp bạn tránh được những sai lầm phổ biến và thành công trong kinh doanh xuyên biên giới.</p>'
WHERE `id` = 4;
