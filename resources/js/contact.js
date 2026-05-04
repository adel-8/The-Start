
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value;
            
            // Simple validation
            if (!name || !email || !subject || !message) {
                showAlert('Please fill in all required fields.', 'error');
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                showAlert('Please enter a valid email address.', 'error');
                return;
            }
            
            // Phone validation (optional, only if provided)
            if (phone && !/^[\d\s\+\(\)\-]+$/.test(phone)) {
                showAlert('Please enter a valid phone number.', 'error');
                return;
            }
            
            // Simulate form submission (in real scenario, you'd send to backend)
            console.log('Form submitted:', { name, email, phone, subject, message });
            
            // Show success message
            showAlert('Thank you for contacting us! We will get back to you within 24 hours.', 'success');
            
            // Reset form
            document.getElementById('contactForm').reset();
        });
        
        function showAlert(message, type) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.textContent = message;
            alertDiv.className = `alert alert-${type} show`;
            
            // Scroll to alert
            alertDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                alertDiv.classList.remove('show');
            }, 5000);
        }
   