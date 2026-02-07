# Đối Soát Yêu Cầu Nghiệp Vụ với Spec

## Tóm Tắt

Đã đối soát yêu cầu nghiệp vụ từ tài liệu "JOBS THUONGLO A SINH.docx" với spec hiện tại và thực hiện các điều chỉnh cần thiết để đảm bảo bao phủ đầy đủ.

## Yêu Cầu Nghiệp Vụ Chính

### 1. Hành Trình Đại Lý (Agent Journey)

#### ✅ Đã Bao Phủ

1. **Đăng ký/Đăng nhập đại lý**
   - Thuộc hệ thống auth hiện có (không thuộc affiliate-building spec)
   - Đã có AuthController.php xử lý đăng ký với mã giới thiệu

2. **Dashboard hiển thị thông tin quan trọng**
   - ✅ DS khách hàng đã sale thành công (Requirement 1.5)
   - ✅ Doanh số tổng (Requirement 1.6)
   - ✅ Doanh số theo tuần (Requirement 1.7 - MỚI BỔ SUNG)
   - ✅ Doanh số theo tháng (Requirement 1.8 - MỚI BỔ SUNG)
   - ✅ Tình trạng thanh toán hoa hồng (Requirement 1.9 - MỚI BỔ SUNG)
   - ✅ Link AFF duy nhất (Requirement 1.10 - MỚI BỔ SUNG)
   - ✅ Mã giới thiệu (Requirement 1.11 - MỚI BỔ SUNG)

3. **Link AFF và Chia sẻ**
   - ✅ Link AFF duy nhất gắn mã đại lý (Requirement 5.1)
   - ✅ QR code cho link (Requirement 5.3)
   - ✅ Share buttons cho social media (trong marketing tools)

4. **Ghi nhận tự động**
   - ⚠️ Backend logic (không thuộc frontend spec)
   - Frontend chỉ hiển thị dữ liệu đã được ghi nhận

5. **Xem lịch sử và theo dõi hoa hồng**
   - ✅ Lịch sử bán hàng (Requirement 2.1, 2.2)
   - ✅ Theo dõi hoa hồng (Requirement 4.1, 4.2, 4.3)
   - ✅ Trạng thái thanh toán (chờ/đã thanh toán) (Requirement 2.2)

### 2. Sản Phẩm và Dịch Vụ

#### ✅ Đã Bao Phủ

1. **Bán gói SP theo gói**
   - ✅ Hiển thị trong reports/orders.php (by_product)
   - ✅ Tracking theo từng sản phẩm

2. **Giỏ hàng & Thanh toán**
   - ⚠️ Thuộc phần customer-facing (không thuộc affiliate spec)

3. **Mở khóa SP tự động**
   - ⚠️ Backend logic (không thuộc affiliate spec)

### 3. Quản Lý Đại Lý

#### ✅ Đã Bao Phủ

1. **Mã ID riêng - Mã giới thiệu**
   - ✅ Hiển thị trên dashboard (Requirement 1.11)
   - ✅ Hiển thị trong profile (Requirement 6.2)
   - ✅ Gắn vào affiliate link (Requirement 5.1)

2. **Quản lý DS đại lý**
   - ⚠️ Thuộc admin system (không thuộc affiliate spec)

## Thay Đổi Đã Thực Hiện

### 1. Requirements.md

**Requirement 1 - Dashboard Overview:**
- ✅ Thêm AC 1.7: Hiển thị doanh số theo tuần riêng biệt
- ✅ Thêm AC 1.8: Hiển thị doanh số theo tháng riêng biệt
- ✅ Thêm AC 1.9: Hiển thị tình trạng thanh toán hoa hồng chi tiết
- ✅ Thêm AC 1.10: Hiển thị Link AFF duy nhất
- ✅ Thêm AC 1.11: Hiển thị mã giới thiệu (Referral Code)

**Requirement 6 - Profile Management:**
- ✅ Thêm AC 6.2: Hiển thị mã giới thiệu trong profile
- ✅ Thêm AC 6.3: Hiển thị Link AFF trong profile

### 2. Design.md

**Data Models - Dashboard:**
```json
{
  "stats": {
    "total_revenue": 125000000,
    "weekly_revenue": 18500000,      // MỚI
    "monthly_revenue": 45000000,     // MỚI
    "pending_commission": 8500000,
    "paid_commission": 36500000      // MỚI
  },
  "affiliate_info": {                // MỚI
    "affiliate_id": "AFF123",
    "affiliate_link": "https://thuonglo.com/ref/AFF123",
    "referral_code": "AFF123"
  },
  "commission_status": {             // MỚI
    "pending": 8500000,
    "paid": 36500000,
    "pending_count": 12,
    "paid_count": 156
  }
}
```

**Data Models - Profile:**
```json
{
  "profile": {
    "affiliate_id": "AFF123",        // MỚI
    "referral_code": "AFF123",       // MỚI
    "affiliate_link": "https://..."  // MỚI
  }
}
```

**PHP Classes:**
- ✅ Thêm class `AffiliateInfo`
- ✅ Thêm class `CommissionStatus`
- ✅ Cập nhật class `DashboardStats` với weekly/monthly revenue
- ✅ Cập nhật class `Profile` với affiliate info

### 3. demo_data.json

**Dashboard Section:**
- ✅ Thêm `weekly_revenue`: 18500000
- ✅ Thêm `monthly_revenue`: 45000000
- ✅ Thêm `paid_commission`: 36500000
- ✅ Thêm object `affiliate_info` với affiliate_id, affiliate_link, referral_code
- ✅ Thêm object `commission_status` với pending/paid breakdown

**Profile Section:**
- ✅ Thêm `affiliate_id`: "AFF123"
- ✅ Thêm `referral_code`: "AFF123"
- ✅ Thêm `affiliate_link`: "https://thuonglo.com/ref/AFF123"

## Ma Trận Bao Phủ Yêu Cầu

| Yêu Cầu Nghiệp Vụ | Requirement | Status | Ghi Chú |
|-------------------|-------------|--------|---------|
| Dashboard - DS khách hàng | 1.5 | ✅ | Đã có |
| Dashboard - Doanh số tổng | 1.6 | ✅ | Đã có |
| Dashboard - Doanh số tuần | 1.7 | ✅ | Mới thêm |
| Dashboard - Doanh số tháng | 1.8 | ✅ | Mới thêm |
| Dashboard - Tình trạng thanh toán | 1.9 | ✅ | Mới thêm |
| Dashboard - Link AFF | 1.10 | ✅ | Mới thêm |
| Dashboard - Mã giới thiệu | 1.11 | ✅ | Mới thêm |
| Link AFF duy nhất | 5.1 | ✅ | Đã có |
| QR code cho link | 5.3 | ✅ | Đã có |
| Lịch sử hoa hồng | 2.1, 2.2 | ✅ | Đã có |
| Theo dõi tài chính | 4.1-4.5 | ✅ | Đã có |
| Profile - Mã giới thiệu | 6.2 | ✅ | Mới thêm |
| Profile - Link AFF | 6.3 | ✅ | Mới thêm |
| Báo cáo chi tiết | 7.1-7.4 | ✅ | Đã có |

## Các Yêu Cầu Ngoài Phạm Vi

Các yêu cầu sau **KHÔNG** thuộc affiliate-building spec (frontend):

1. **Backend Logic:**
   - Ghi nhận tự động khi KH click link và mua hàng
   - Tính toán hoa hồng tự động
   - Xử lý thanh toán

2. **Customer-Facing Features:**
   - Giỏ hàng
   - Thanh toán trực tuyến
   - Mở khóa sản phẩm

3. **Admin Features:**
   - Quản lý danh sách đại lý
   - Phê duyệt đại lý
   - Quản lý mã giới thiệu

## Kết Luận

✅ **Spec hiện tại đã bao phủ đầy đủ yêu cầu nghiệp vụ cho phần frontend affiliate**

**Các điều chỉnh đã thực hiện:**
1. Bổ sung 5 acceptance criteria mới vào Requirements
2. Cập nhật data models trong Design document
3. Cập nhật demo_data.json với dữ liệu mới

**Sẵn sàng triển khai:**
- Phase 1 đã hoàn thành (Foundation & Demo Data)
- Có thể bắt đầu Phase 2 (Layout Components)

**Lưu ý:**
- Backend logic (ghi nhận tự động, tính hoa hồng) cần được xử lý riêng
- Admin features (quản lý đại lý) thuộc admin-building spec
- Customer features (giỏ hàng, thanh toán) thuộc customer-facing spec
