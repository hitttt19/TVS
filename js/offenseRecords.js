function showRecordForm() {
    // Show record creation form logic
    document.getElementById('formTitle').innerText = 'Create New Record';
    document.getElementById('record_id').value = '';
    document.getElementById('datetime').value = '';
    document.getElementById('ticket_no').value = '';
    document.getElementById('license_id').value = '';
    
    // Reset enforcer dropdown (ensure it's visible and the first option is selected)
    const enforcerDropdown = document.getElementById('traffic_enforcer');
    enforcerDropdown.selectedIndex = 0; // Reset to first option (usually a default option like "Select Enforcer")
    
    document.getElementById('status').value = 'Pending';
    document.getElementById('offense_name').value = '';
    document.getElementById('offense_rate').value = '';
    document.getElementById('formAction').value = 'create';
    document.getElementById('recordFormModal').style.display = 'block';
}




function editRecord(id, datetime, ticket_no, license_id, traffic_enforcer, status, offense_name, offense_rate) {
    console.log("Traffic Enforcer:", traffic_enforcer);  // Debugging line
    
    // Show record editing form logic
    document.getElementById('formTitle').innerText = 'Edit Record';
    document.getElementById('record_id').value = id;
    document.getElementById('datetime').value = datetime;
    document.getElementById('ticket_no').value = ticket_no;
    document.getElementById('license_id').value = license_id;

     // Hide the name field in the edit form
     document.getElementById('temp_name').closest('.form-group').style.display = 'none';
    
    // Set the traffic enforcer dropdown
    const enforcerDropdown = document.getElementById('traffic_enforcer');
    
    // Loop through options to find the one that matches the selected enforcer
    for (let i = 0; i < enforcerDropdown.options.length; i++) {
        if (enforcerDropdown.options[i].value === traffic_enforcer) {
            enforcerDropdown.selectedIndex = i;  // Select the matched option
            break;
        }
    }

    document.getElementById('status').value = status;
    document.getElementById('offense_name').value = offense_name;
    document.getElementById('offense_rate').value = offense_rate;
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordFormModal').style.display = 'block';
}



function closeRecordModal() {
    document.getElementById('recordFormModal').style.display = 'none';
}

function deleteRecord(id) {
    const confirmationModal = document.getElementById('confirmationModal');
    const confirmButton = document.getElementById('confirmDeleteButton');
    
    // Show confirmation modal
    confirmationModal.style.display = 'block';
    
    // Handle deletion confirmation
    confirmButton.onclick = function() {
        fetch('offense_records.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'delete', id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success Toastify
                Toastify({
                    text: data.message,
                    duration: 3000,  // Show for 3 seconds
                    close: true,
                    gravity: "top",  // Position it on top
                    position: "right",  // Position on the right
                    backgroundColor: "linear-gradient(to right, #00b09b, #96c93d)" // Green color
                }).showToast();
                
                // Optionally, you can refresh the page or update the table dynamically
                location.reload(); // Or call updateTable() if you have dynamic updates
            } else {
                // Show error Toastify
                Toastify({
                    text: data.message,
                    duration: 3000,  // Show for 3 seconds
                    close: true,
                    gravity: "top",  // Position it on top
                    position: "right",  // Position on the right
                    backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)" // Red color
                }).showToast();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error Toastify if something goes wrong with the request
            Toastify({
                text: "An error occurred while deleting the record.",
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "linear-gradient(to right, #ff5f6d, #ffc371)"
            }).showToast();
        });

        // Close the confirmation modal
        confirmationModal.style.display = 'none';
    };

    // Close the modal on cancel
    document.getElementById('cancelDeleteButton').onclick = function() {
        confirmationModal.style.display = 'none';
    };
}



function sendLetter() {
    const ticketNo = document.querySelector('.ticket-no').textContent;
    const driverName = document.querySelector('.driver-name').textContent;
    const offenseName = document.querySelector('.offense-name').textContent;
    const violationDate = document.querySelector('.violation-date').textContent;
    const offenseRate = document.querySelector('.offense-rate').textContent;
    const driverAddress = document.querySelector('.driver-address').textContent;

    fetch('send_notification.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            ticket_no: ticketNo,
            offense_name: offenseName,
            offense_rate: offenseRate,
        }),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Letter sent successfully!");
            fetchNotifications(); // Fetch notifications to show the new one
            closeLetterModal(); // Close the modal
        } else {
            alert(data.message || "Failed to send the letter.");
        }
    });
}

function openLetterModal(ticketNo, driverName, offenseName, violationDate, offenseRate, driverAddress, driverId) {
    // Display the ticket details
    document.querySelector('.ticket-no').innerText = ticketNo;
    document.querySelector('.driver-name').innerText = driverName;
    document.querySelector('.offense-name').innerText = offenseName;
    document.querySelector('.violation-date').innerText = violationDate;
    
    // Format the offense rate with the peso sign (₱) and two decimal places
    const formattedOffenseRate = '₱' + parseFloat(offenseRate).toFixed(2); // Ensures two decimal places
    document.querySelector('.offense-rate').innerText = formattedOffenseRate;
    
    document.querySelector('.driver-address').innerText = driverAddress;

    // Set the current date in a readable format (e.g., "October 8, 2024")
    const currentDate = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.querySelector('.date').innerText = currentDate.toLocaleDateString('en-US', options);

    // Store the driver ID globally for later use
    window.currentDriverId = driverId;

    // Show the modal
    document.getElementById('myModal').style.display = 'block';
}

function closeLetterModal() {
    document.getElementById('myModal').style.display = 'none';
}

// Close the modal if the background is clicked
document.getElementById('myModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeLetterModal();
    }
});

function updateOffenseRate() {
    const offenseSelect = document.getElementById('offense_name');
    const offenseRateInput = document.getElementById('offense_rate');

    const selectedOption = offenseSelect.options[offenseSelect.selectedIndex];
    const rate = selectedOption.getAttribute('data-rate');

    offenseRateInput.value = rate;
}

function searchRecords() {
    const searchInput = document.getElementById('search').value;

    const xhr = new XMLHttpRequest();
    xhr.open('GET', `offense_records.php?search=${encodeURIComponent(searchInput)}`, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            const records = JSON.parse(xhr.responseText);
            updateTable(records);
        } else {
            console.error('Error fetching records:', xhr.statusText);
        }
    };
    xhr.onerror = function() {
        console.error('Request failed');
    };
    xhr.send();
}

function updateTable(records) {
    const tbody = document.querySelector('.offenseR-table-container tbody');
    tbody.innerHTML = ''; // Clear the table before updating

    records.forEach(record => {
        console.log(record.traffic_enforcer); // Check if traffic_enforcer exists

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${new Date(record.datetime).toLocaleString()}</td>
            <td>${record.ticket_no}</td>
            <td>${record.license_id}</td>
            <td>${record.driver_name}</td>
            <td>${record.offense_name}</td>
            <td>${record.offense_rate}</td>
            <td>${record.traffic_enforcer}</td>  <!-- Traffic enforcer column -->
            <td><span class="status ${record.status}">${record.status}</span></td>
            <td>
                <button class="edit-btn" 
                    onclick="editRecord(${record.id}, '${record.datetime}', '${record.ticket_no}', '${record.license_id}', '${record.traffic_enforcer}', '${record.status}', '${record.offense_name}', '${record.offense_rate}')">
                    <img src="../icons/Edit.png" alt="Edit Icon" class="icon">
                </button>
                <form action="offense_records.php" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="${record.id}">
                    <input type="hidden" name="action" value="delete">
                    <button type="submit" class="delete-btn">
                        <img src="../icons/Delete.png" alt="Delete Icon" class="icon">
                    </button>
                    ${record.status !== 'Resolved' ? ` 
                    <button type="button" class="send-btn" 
                        data-datetime="${record.datetime}" 
                        style="display: none;" 
                        onclick="openLetterModal('${record.ticket_no}', '${record.driver_name}', '${record.offense_name}', '${record.datetime}', '${record.offense_rate}', '${record.driver_address}')">
                            <img src="../icons/send.png" alt="Send Icon" class="icon">
                    </button>` : ''}
                </form>
            </td>
        `;
        tbody.appendChild(row);
    });
}


document.addEventListener('DOMContentLoaded', function() {
    const sendButtons = document.querySelectorAll('.send-btn');

    sendButtons.forEach(button => {
        const recordDatetime = new Date(button.getAttribute('data-datetime')).getTime();
        const currentTime = Date.now();
        const elapsedTime = currentTime - recordDatetime;

        const row = button.closest('tr'); // Get the closest row to the send button

        if (elapsedTime >= 60000) {
            // Show the send button and add row-alert class
            button.style.display = 'inline-block'; 
            row.classList.add('row-alert'); // Add the class for alert rows
        } else {
            setTimeout(() => {
                button.style.display = 'inline-block';
                row.classList.add('row-alert'); // Add the class for alert rows
            }, 60000 - elapsedTime);
        }

        // Ensure that the enforcer is visible even in the alert row
        const enforcerColumn = row.querySelector('td:nth-child(7)'); // This assumes the enforcer is in the 7th column
        if (enforcerColumn) {
            enforcerColumn.style.display = 'inline-block';
        }
    });

    // Attach close button event listener
    document.querySelector('.close-button').addEventListener('click', closeRecordModal);
});

