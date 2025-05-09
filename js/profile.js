
  // Fetch Session Data
  fetch('http://localhost:8001/api/service/usermanagement/sessiontest', {
    method: 'GET',
    credentials: 'include'
  })
  .then(response => response.json())
  .then(data => {
    document.getElementById('session-id').textContent = data.id;
    document.getElementById('session-fullname').textContent = data.fullname;
    document.getElementById('session-email').textContent = data.email;
    document.getElementById('session-profile').textContent = data.profile;
    document.getElementById('session-role').textContent = data.barangayRole;

    const statusBadge = document.getElementById('session-status-badge');
    statusBadge.textContent = data.status;
    statusBadge.classList.add(data.status.toLowerCase() === 'active' ? 'bg-success' : 'bg-danger');

    document.getElementById('session-picture').src = data.picture || 'assets/images/anonymous.svg';

    // Set session ID in the form hidden input fields
    document.getElementById('session-id-input').value = data.id;
    document.getElementById('session-id-input-password').value = data.id;
  })
  .catch(() => {
    document.getElementById('session-email').textContent = 'Unavailable';
  });

  // Modal Controls
  function openModal(modalId) {
    const myModal = new bootstrap.Modal(document.getElementById(modalId));
    myModal.show();
  }

  // Update Email
  document.getElementById('updateEmailForm').addEventListener('submit', function(e) {
  e.preventDefault();
  const editEmail = document.getElementById('newEmail').value;
  const sessionId = document.getElementById('session-id-input').value;
  const messageDiv = document.getElementById('emailMessage');

  if(editEmail === ''){
    messageDiv.textContent = 'Enter email.';
    messageDiv.className = 'message error';
    return;
  } else {
    // Email Validation
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(editEmail)) {
      messageDiv.textContent = 'Please enter a valid email address.';
      messageDiv.className = 'message error';
      return;
    }
  }

        fetch(`http://localhost:8001/api/service/usermanagement/updateEmail/${sessionId}`, {
            method: 'PUT',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ editEmail })
        })
        .then(response => response.json())
        .then(result => {
            if (result.message === "User updated successfully.") {
            messageDiv.textContent = 'Email updated successfully! Page will refresh.';
            messageDiv.className = 'message success';

            // Swal success notice with timer then redirect
            Swal.fire({
            icon: 'success',
            title: 'Email Updated!',
            text: 'You need to log in again.',
            timer: 3000, // 3 seconds
            timerProgressBar: true,
            showConfirmButton: false,
            willClose: () => {
                window.location.href = 'logout.php';  // redirect to your login page
            }          
            });      

            } else {
            messageDiv.textContent = result.message || 'Failed to update email.';
            messageDiv.className = 'message error';
            }
        })
        .catch(() => {
            messageDiv.textContent = 'An error occurred while updating.';
            messageDiv.className = 'message error';
        });
    });

    document.getElementById('updatePasswordForm').addEventListener('submit', function(e) {
  e.preventDefault();

  const messageDiv = document.getElementById('passwordMessage');
  messageDiv.textContent = '';
  messageDiv.className = 'message';

  const editPassword = document.getElementById('newPassword').value;
  const confirmPassword = document.getElementById('confirmPassword').value;
  const sessionId = document.getElementById('session-id-input-password').value;

  if (editPassword.length < 8) {
    messageDiv.textContent = 'Password must be at least 8 characters long.';
    messageDiv.className = 'message error';
    return;
  }
  if (!/[A-Z]/.test(editPassword)) {
    messageDiv.textContent = 'Password must contain at least one uppercase letter.';
    messageDiv.className = 'message error';
    return;
  }
  if (!/[a-z]/.test(editPassword)) {
    messageDiv.textContent = 'Password must contain at least one lowercase letter.';
    messageDiv.className = 'message error';
    return;
  }
  if (!/\d/.test(editPassword)) {
    messageDiv.textContent = 'Password must contain at least one number.';
    messageDiv.className = 'message error';
    return;
  }
  if (!/[!@#$%^&*]/.test(editPassword)) {
    messageDiv.textContent = 'Password must contain at least one special character (!@#$%^&*).';
    messageDiv.className = 'message error';
    return;
  }
  if (confirmPassword === '') {
    messageDiv.textContent = 'Re-type password.';
    messageDiv.className = 'message error';
    return;
  }
  if (editPassword !== confirmPassword) {
    messageDiv.textContent = 'Passwords do not match.';
    messageDiv.className = 'message error';
    return;
  }

    fetch(`http://localhost:8001/api/service/usermanagement/updatePassword/${sessionId}`, {
        method: 'PUT',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ editPassword })
    })
    .then(response => response.json())
    .then(result => {
        if (result.message === "User updated successfully.") {
        messageDiv.textContent = 'Password updated successfully! Page will refresh.';
        messageDiv.className = 'message success';

        // Swal success notice with timer then redirect
        Swal.fire({
        icon: 'success',
        title: 'Password Updated!',
        text: 'You need to log in again.',
        timer: 3000, // 3 seconds
        timerProgressBar: true,
        showConfirmButton: false,
        willClose: () => {
            window.location.href = 'logout.php';  // redirect to your login page
        }          
        });      

        } else {
        messageDiv.textContent = result.message || 'Failed to update password.';
        messageDiv.className = 'message error';
        }
    })
    .catch(() => {
        messageDiv.textContent = 'An error occurred while updating.';
        messageDiv.className = 'message error';
    });
});