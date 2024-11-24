<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renewable Notifications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>

<div class="container mb-3 bg-white text-dark rounded-3 shadow p-3">
    <h1>Notifications</h1>

    <div class="d-flex justify-content-end col">
        <button class="btn btn-success lg-m-3 rounded-3" data-bs-toggle="modal" data-bs-target="#exampleModal">Create Notification</button>
    </div>

    <div id="alert-container" class="mb-3"></div> <!-- Alert container -->

    <div class="m-3 border rounded-2" style="height: 70vh; overflow: auto;">
        <div id="applications-container" class="applications-container"></div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-body">

                    <div class="container bg-white text-dark rounded-3 px-0">
                        <div class="d-flex justify-content-center">
                            <h1>Renewal Notification</h1>
                        </div>

                        <form id="notification-form">
                            <label class="form-label" for="market-name">Add Market:</label>
                            <input class="form-control mb-3 rounded-3" type="text" id="market-name" placeholder="Enter market name" required>

                            <label class="form-label" for="notification-message">Notification Message:</label>
                            <textarea class="form-control mb-3 rounded-3" id="notification-message" placeholder="Enter notification message..." required></textarea>

                            <label class="form-label" for="status-select">Status:</label>
                            <select class="form-select mb-3 rounded-3" id="status-select" required>
                                <option value="" disabled selected>Select status</option>
                                <option value="Processing">Processing</option>
                                <option value="Complete">Complete</option>
                                <option value="Failed">Failed</option>
                            </select>

                            <div class="d-flex justify-content-end">
                                <button class="btn btn-secondary m-3" data-bs-dismiss="modal" type="button">Close</button>
                                <button class="btn btn-success my-3 rounded-3" type="submit">Send Notification</button>
                            </div>
                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

<script>
// JavaScript functionality to handle notifications
document.getElementById('notification-form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form submission

    // Get input values
    const marketName = document.getElementById('market-name').value;
    const notificationMessage = document.getElementById('notification-message').value;
    const status = document.getElementById('status-select').value;

    // Create new notification card
    const newNotification = document.createElement('div');
    newNotification.className = 'card card-body m-3 rounded-5';
    newNotification.innerHTML = `
        <h3>Permit Status: ${status}</h3>
        <div class='applications-container'>
            <label class='col-form-label'>${marketName}: ${notificationMessage}</label>
        </div>
    `;

    // Append to applications container
    document.getElementById('applications-container').appendChild(newNotification);

    // Clear form fields
    document.getElementById('market-name').value = '';
    document.getElementById('notification-message').value = '';
    document.getElementById('status-select').selectedIndex = 0; // Reset to default

    // Show success alert
    showAlert('Notification sent successfully!', 'success');

    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('exampleModal'));
    modal.hide();
});

// Function to display alerts
function showAlert(message, type) {
    const alertContainer = document.getElementById('alert-container');
    
    // Create alert element
    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible fade show`;
    alert.role = 'alert';
    alert.innerHTML = `
        ${message}
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    `;

    // Append alert to the container
    alertContainer.appendChild(alert);
}
</script>

</body>
</html>