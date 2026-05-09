// review_popup.js - Handle review popup on user side

// API Base URL
const API_URL = '/Hotel-Booking-Web-System/ChongEeLynn/review_api.php';

let reviewModal = null;

function showReviewPopup(booking) {
    // Remove existing modal if any
    if (reviewModal) {
        reviewModal.remove();
    }
    
    // Create modal HTML
    reviewModal = document.createElement('div');
    reviewModal.id = 'reviewModal';
    reviewModal.className = 'review-modal';
    reviewModal.innerHTML = `
        <div class="review-modal-content">
            <div class="review-modal-header">
                <h3><i class="fas fa-star"></i> Leave a Review</h3>
                <button class="review-close-btn" onclick="closeReviewPopup()">&times;</button>
            </div>
            <div class="review-modal-body">
                <p class="review-hotel-name">Grand Hotel Melaka</p>
                <p class="review-room-info">Room: <strong>${escapeHtml(booking.room_name)}</strong></p>
                <p class="review-booking-ref">Booking: #${escapeHtml(booking.booking_ref)}</p>
                
                <div class="review-rating-section">
                    <label>Your Rating:</label>
                    <div class="star-rating" id="starRatingContainer">
                        <span class="star" data-rating="1">☆</span>
                        <span class="star" data-rating="2">☆</span>
                        <span class="star" data-rating="3">☆</span>
                        <span class="star" data-rating="4">☆</span>
                        <span class="star" data-rating="5">☆</span>
                    </div>
                    <input type="hidden" id="reviewRating" value="0">
                </div>
                
                <div class="review-comment-section">
                    <label>Your Review:</label>
                    <textarea id="reviewComment" rows="4" placeholder="Share your experience at Grand Hotel Melaka..."></textarea>
                </div>
                
                <div class="review-points-info">
                    <i class="fas fa-gift"></i> Earn <strong>10 points</strong> for leaving a review!
                </div>
            </div>
            <div class="review-modal-footer">
                <button class="review-later-btn" onclick="skipReview(${booking.booking_id})">Later</button>
                <button class="review-submit-btn" onclick="submitReview(${booking.booking_id}, ${booking.room_id})">Submit Review & Earn Points</button>
            </div>
        </div>
    `;
    
    document.body.appendChild(reviewModal);
    
    // Star rating functionality - SIMPLE VERSION USING TEXT STARS
    const stars = reviewModal.querySelectorAll('.star');
    const ratingInput = reviewModal.querySelector('#reviewRating');
    
    function updateStars(rating) {
        stars.forEach((star, index) => {
            if (index < rating) {
                star.textContent = '★';
                star.style.color = '#fbbf24';
            } else {
                star.textContent = '☆';
                star.style.color = '#ddd';
            }
        });
    }
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            ratingInput.value = rating;
            updateStars(rating);
            console.log('Rating selected:', rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, index) => {
                if (index < rating) {
                    s.textContent = '★';
                    s.style.color = '#fbbf24';
                } else {
                    s.textContent = '☆';
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    // Reset stars on mouse leave
    const container = reviewModal.querySelector('#starRatingContainer');
    container.addEventListener('mouseleave', function() {
        const currentRating = parseInt(ratingInput.value);
        updateStars(currentRating);
    });
    
    // Show modal
    setTimeout(() => {
        reviewModal.classList.add('show');
    }, 10);
    
    // Close on background click
    reviewModal.addEventListener('click', function(e) {
        if (e.target === reviewModal) {
            closeReviewPopup();
        }
    });
}

function closeReviewPopup() {
    if (reviewModal) {
        reviewModal.classList.remove('show');
        setTimeout(() => {
            reviewModal.remove();
            reviewModal = null;
        }, 300);
    }
}

function skipReview(bookingId) {
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'skip_review', booking_id: bookingId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeReviewPopup();
            showToast('You can review later from your bookings page.', 'info');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
    });
}

function submitReview(bookingId, roomId) {
    const rating = document.getElementById('reviewRating').value;
    const comment = document.getElementById('reviewComment').value;
    
    console.log('Rating:', rating, 'Comment:', comment);
    
    if (rating == 0) {
        showToast('Please select a rating!', 'error');
        return;
    }
    
    if (!comment.trim()) {
        showToast('Please write your review!', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = document.querySelector('.review-submit-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    submitBtn.disabled = true;
    
    fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'submit_review',
            booking_id: bookingId,
            room_id: roomId,
            rating: rating,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeReviewPopup();
            
            // Update points display if exists on page
            const pointsElement = document.querySelector('.stat-number');
            if (pointsElement) {
                fetch(`${API_URL}?action=get_points`)
                    .then(res => res.json())
                    .then(pointsData => {
                        if (pointsData.points) {
                            pointsElement.textContent = pointsData.points.toLocaleString();
                        }
                    });
            }
        } else {
            showToast(data.error, 'error');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error. Please try again.', 'error');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `review-toast review-toast-${type}`;
    toast.innerHTML = `<i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i> ${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Check for pending review on page load
function checkForPendingReview() {
    console.log('Checking for pending review...');
    fetch(`${API_URL}?action=check_pending`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            if (data.success && data.has_review) {
                console.log('Pending review found! Showing popup...');
                showReviewPopup(data.booking);
            } else {
                console.log('No pending review found');
            }
        })
        .catch(error => {
            console.error('Error checking review status:', error);
        });
}

// Auto-check when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(checkForPendingReview, 500);
});