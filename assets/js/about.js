// About page JavaScript functionality

// Modal functions
function openModal() {
    document.getElementById('agentModal').style.display = 'block';
}

function closeModal() {
    document.getElementById('agentModal').style.display = 'none';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Close modal when clicking outside of it
    window.onclick = function(event) {
        var modal = document.getElementById('agentModal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    // Handle form submission
    document.getElementById('agentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        var formData = new FormData(this);
        var data = {};
        for (var pair of formData.entries()) {
            data[pair[0]] = pair[1];
        }
        
        // Here you would typically send the data to your server
        console.log('Agent registration data:', data);
        
        // Show success message
        alert('Đăng ký thành công! Chúng tôi sẽ liên hệ với bạn sớm nhất có thể.');
        
        // Close modal and reset form
        closeModal();
        this.reset();
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
});