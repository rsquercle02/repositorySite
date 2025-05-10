let inactivityTimeout;
    let warningTimeout;

    // Function to start session timeout (20 minutes for warning, 2 more minutes for logout)
    function startSessionTimeout() {
      // Set timeout for showing warning after 20 minutes of inactivity (20 * 60 * 1000 ms)
      warningTimeout = setTimeout(() => {
        Swal.fire({
          title: 'Inactivity Warning',
          text: 'You have been inactive for 20 minutes. You will be logged out in 2 more minutes if there is no activity.',
          icon: 'warning',
          showConfirmButton: false,  // No confirm button
          timer: 120000,  // Automatically close the warning in 2 minutes
        });
      }, 1200000);  // 1200000 milliseconds = 20 minutes

      // Set timeout for logging out after 22 minutes of inactivity (20 min warning + 2 min grace period)
      inactivityTimeout = setTimeout(() => {
        // Perform logout action (e.g., redirect or clear session data)
        logout();
      }, 1440000); // 1440000 milliseconds = 22 minutes
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