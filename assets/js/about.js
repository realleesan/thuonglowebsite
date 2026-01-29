// about.js - Phiên bản đầy đủ (Modal + Counter Animation)

// --- 1. CÁC HÀM XỬ LÝ MODAL (Mở/Đóng Form) ---
// Phải để ở ngoài cùng để HTML (onclick) có thể gọi được
function openModal() {
    const modal = document.getElementById('agentModal');
    if (modal) {
        modal.style.display = 'block';
        // Thêm class 'show' để kích hoạt hiệu ứng fade-in CSS
        setTimeout(() => modal.classList.add('show'), 10);
    } else {
        console.error("Không tìm thấy modal có ID 'agentModal'");
    }
}

function closeModal() {
    const modal = document.getElementById('agentModal');
    if (modal) {
        modal.classList.remove('show');
        // Đợi 0.3s cho hiệu ứng biến mất rồi mới ẩn hẳn
        setTimeout(() => {
            modal.style.display = 'none';
        }, 300);
    }
}

// --- 2. CÁC HÀM XỬ LÝ SỐ CHẠY (Counter) ---
function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Hiệu ứng chạy số mượt (nhanh đầu, chậm đuôi)
        const easeProgress = 1 - Math.pow(1 - progress, 3);
        
        const currentVal = Math.floor(easeProgress * (end - start) + start);
        obj.innerHTML = currentVal;
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            obj.innerHTML = end;
        }
    };
    window.requestAnimationFrame(step);
}

function initCounter() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    if (statNumbers.length === 0) return;

    statNumbers.forEach(stat => {
        // Lấy số từ nội dung text
        const rawText = stat.innerText.replace(/[^0-9]/g, '');
        const finalValue = parseInt(rawText, 10);
        
        if (!isNaN(finalValue) && finalValue > 0) {
            stat.innerText = "0"; // Reset về 0
            animateValue(stat, 0, finalValue, 2000); // Chạy trong 2 giây
        }
    });
}

// --- 3. KHỞI CHẠY KHI TRANG LOAD XONG ---
document.addEventListener('DOMContentLoaded', function() {
    // A. Chạy hiệu ứng số
    initCounter();

    // B. Xử lý đóng modal khi click ra vùng tối bên ngoài
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('agentModal');
        if (modal && event.target === modal) {
            closeModal();
        }
    });

    // C. Xử lý đóng modal bằng phím ESC
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    // D. Xử lý khi nhấn nút "Gửi Phê Duyệt" (Submit Form)
    const agentForm = document.getElementById('agentForm');
    if (agentForm) {
        agentForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Chặn load lại trang
            
            // Lấy dữ liệu
            const formData = new FormData(this);
            const data = {};
            for (const pair of formData.entries()) {
                data[pair[0]] = pair[1];
            }
            
            console.log('Dữ liệu đăng ký:', data);
            alert('Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất có thể.');
            
            closeModal(); // Đóng form
            this.reset(); // Xóa dữ liệu đã nhập
        });
    }
});