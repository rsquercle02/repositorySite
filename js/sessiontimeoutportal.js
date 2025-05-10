let inactivityTimeout;
    let warningTimeout;

    // Function to start session timeout (30 minutes for warning, 5 more minutes for logout)
    function startSessionTimeout() {
      // Set timeout for showing warning after 30 minutes of inactivity (30 * 60 * 1000 ms)
      warningTimeout = setTimeout(() => {
        Swal.fire({
          title: 'Inactivity Warning',
          text: 'You have been inactive for 30 minutes. You will be logged out in 5 more minutes if there is no activity.',
          icon: 'warning',
          showConfirmButton: false,  // No confirm button
          timer: 300000,  // Automatically close the warning in 5 minutes
        });
      }, 1800000);  // 1800000 milliseconds = 30 minutes

      // Set timeout for logging out after 35 minutes of inactivity (30 min warning + 5 min grace period)
      inactivityTimeout = setTimeout(() => {
        // Perform logout action (e.g., redirect or clear session data)
        logout();
      }, 2100000); // 2100000 milliseconds = 35 minutes
    }

    // Function to log out user
    function logout() {
      // Example: Redirect to a logout URL (You can customize this)
      window.location.href = "logout";  // Replace with your actual logout endpoint

      // Alternatively, clear session data or cookies
      // localStorage.clear(); // Clears local storage (if you're using it)
      // sessionStorage.clear(); // Clears session storage (if you're using it)
      // document.cookie = "session=;expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;"; // Clear session cookie
    }

    // Reset timeout on user interaction (mouse movement or keypress)
    function resetInactivityTimer() {
      clearTimeout(warningTimeout);  // Clear the warning timeout
      clearTimeout(inactivityTimeout);  // Clear the logout timeout
      startSessionTimeout();  // Restart the timeouts
    }

    // Attach event listeners for user activity
    window.onload = () => {
      // Start the session timeout when the page is loaded
      startSessionTimeout();

      // Listen for mouse movement or keypress to reset inactivity timer
      document.addEventListener('mousemove', resetInactivityTimer);
      document.addEventListener('keypress', resetInactivityTimer);
    };