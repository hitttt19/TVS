// Function to show the record creation form
function showRecordForm() {
    document.getElementById('formTitle').innerText = 'Create New Record';
    document.getElementById('record_id').value = '';
    document.getElementById('datetime').value = '';
    document.getElementById('ticket_no').value = '';
    document.getElementById('license_id').value = '';
    document.getElementById('traffic_enforcer').value = '';
    document.getElementById('status').value = 'Pending';
    document.getElementById('offense_name').value = '';
    document.getElementById('offense_rate').value = '';
    document.getElementById('formAction').value = 'create';
    document.getElementById('recordFormModal').style.display = 'block';
}

// Function to populate the form for editing an existing record
function editRecord(id, datetime, ticket_no, license_id, traffic_enforcer, status, offense_name, offense_rate) {
    document.getElementById('formTitle').innerText = 'Edit Record';
    document.getElementById('record_id').value = id;
    document.getElementById('datetime').value = datetime;
    document.getElementById('ticket_no').value = ticket_no;
    document.getElementById('license_id').value = license_id;
    document.getElementById('traffic_enforcer').value = traffic_enforcer;
    document.getElementById('status').value = status;
    document.getElementById('offense_name').value = offense_name;
    document.getElementById('offense_rate').value = offense_rate;
    document.getElementById('formAction').value = 'update';
    document.getElementById('recordFormModal').style.display = 'block';
}

// Function to delete a record after confirmation
function deleteRecord(id) {
    if (confirm("Are you sure you want to delete this record?")) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'action';
        input.value = 'delete';
        form.appendChild(input);

        const recordIdInput = document.createElement('input');
        recordIdInput.type = 'hidden';
        recordIdInput.name = 'record_id';
        recordIdInput.value = id;
        form.appendChild(recordIdInput);

        document.body.appendChild(form);
        form.submit();
    }
}

// Function to open the letter modal for an offense record
function openLetterModal(ticketNo, driverName, offenseName, violationDate, offenseRate) {
    const modal = document.getElementById("myModal");
    modal.querySelector('.date').innerText = new Date(violationDate).toLocaleDateString();
    modal.querySelector('.driver-name').innerText = driverName;
    modal.querySelector('.ticket-no').innerText = ticketNo;
    modal.querySelector('.offense-name').innerText = offenseName;
    modal.querySelector('.violation-date').innerText = new Date(violationDate).toLocaleDateString();
    modal.querySelector('.offense-rate').innerText = offenseRate;
    modal.style.display = "block";
}

// Close modal when clicking on the close button
const closeButtons = document.querySelectorAll('.modal .close');
closeButtons.forEach(closeButton => {
    closeButton.onclick = function() {
        this.closest('.modal').style.display = "none";
    };
});

// Close modal when clicking outside the modal
window.onclick = function(event) {
    const modal = document.getElementById("myModal");
    if (event.target === modal) {
        modal.style.display = "none";
    }
};

// Function to update the offense rate when selecting an offense
function updateOffenseRate() {
    const offenseSelect = document.getElementById('offense_name');
    const offenseRateInput = document.getElementById('offense_rate');
    const selectedOption = offenseSelect.options[offenseSelect.selectedIndex];
    const rate = selectedOption.getAttribute('data-rate');
    offenseRateInput.value = rate;
}

// Function to handle searching of records with AJAX
function searchRecords() {
    const searchInput = document.getElementById('search').value;
    const xhr = new XMLHttpRequest();
    xhr.open('GET', `enforcer_offenseRecords.php?search=${encodeURIComponent(searchInput)}`, true);
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
    tbody.innerHTML = ''; // Clear existing rows

    // Filter records based on the current user's enforcer_id
    const filteredRecords = records.filter(record => record.enforcer_id == currentUserId);

    filteredRecords.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${new Date(record.datetime).toLocaleString()}</td>
            <td>${record.ticket_no}</td>
            <td>${record.license_id}</td>
            <td>${record.driver_name}</td>
            <td>${record.offense_name}</td>
            <td>${record.offense_rate}</td>
            <td>${record.traffic_enforcer}</td>
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
                        onclick="openLetterModal('${record.ticket_no}', '${record.driver_name}', '${record.offense_name}', '${record.datetime}', '${record.offense_rate}')">
                            <img src="../icons/send.png" alt="Send Icon" class="icon">
                    </button>` : ''}
                </form>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateTable(records) {
    const tbody = document.querySelector('.offenseR-table-container tbody');
    tbody.innerHTML = ''; // Clear existing rows

    // Filter records based on the current user's enforcer_id
    const filteredRecords = records.filter(record => record.enforcer_id == currentUserId);

    filteredRecords.forEach(record => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${new Date(record.datetime).toLocaleString()}</td>
            <td>${record.ticket_no}</td>
            <td>${record.license_id}</td>
            <td>${record.driver_name}</td>
            <td>${record.offense_name}</td>
            <td>${record.offense_rate}</td>
            <td>${record.traffic_enforcer}</td>
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
                            style="display:inline-block;" 
                            onclick="openLetterModal('${record.ticket_no}', '${record.driver_name}', '${record.offense_name}', '${record.datetime}', '${record.offense_rate}')">
                            <img src="../icons/send.png" alt="Send Icon" class="icon">
                        </button>` : ''}
                </form>
            </td>
        `;
        tbody.appendChild(row);
    });
}


