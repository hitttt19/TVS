function showDriverForm() {
    document.getElementById('formTitle').innerText = 'Create New Driver';
    document.getElementById('driverId').value = '';
    document.getElementById('license_id').value = '';
    document.getElementById('license_type').value = '';
    document.getElementById('firstname').value = '';
    document.getElementById('middlename').value = '';
    document.getElementById('lastname').value = '';
    document.getElementById('gender').value = '';
    document.getElementById('date_of_birth').value = '';
    document.getElementById('present_address').value = '';
    document.getElementById('permanent_address').value = '';
    document.getElementById('nationality').value = '';
    document.getElementById('contact_number').value = '';
    document.getElementById('username').value = '';
    document.getElementById('email').value = '';
    document.getElementById('formAction').value = 'create';
    document.getElementById('driverFormModal').style.display = 'block';
}

function editDriver(id, license_id, license_type, firstname, middlename, lastname, gender, date_of_birth, present_address, permanent_address, nationality, contact_number, username, email) {
    document.getElementById('formTitle').innerText = 'Edit Driver';
    document.getElementById('driverId').value = id;
    document.getElementById('license_id').value = license_id;
    document.getElementById('license_type').value = license_type;
    document.getElementById('firstname').value = firstname;
    document.getElementById('middlename').value = middlename;
    document.getElementById('lastname').value = lastname;
    document.getElementById('gender').value = gender;
    document.getElementById('date_of_birth').value = date_of_birth;
    document.getElementById('present_address').value = present_address;
    document.getElementById('permanent_address').value = permanent_address;
    document.getElementById('nationality').value = nationality;
    document.getElementById('contact_number').value = contact_number;
    document.getElementById('username').value = username;
    document.getElementById('email').value = email;
    document.getElementById('formAction').value = 'update';
    document.getElementById('driverFormModal').style.display = 'block';
}

document.getElementById('closeDriverForm').onclick = function() {
    document.getElementById('driverFormModal').style.display = 'none';
}

function viewDriver(id) {
// Redirect to the current page with view_id parameter
window.location.href = 'drivers_list.php?view_id=' + id;
}

document.getElementById('closeViewDriver').onclick = function() {
    document.getElementById('viewDriverModal').style.display = 'none';
}

// Check if the view_id parameter is present and show the modal
window.onload = function() {
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('view_id')) {
        document.getElementById('viewDriverModal').style.display = 'block';
    }
}

function updateTable(drivers) {
    const tbody = document.querySelector('.table tbody');
    tbody.innerHTML = ''; // Clear existing rows

    drivers.forEach(driver => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${htmlspecialchars(driver.license_id)}</td>
            <td>${htmlspecialchars(driver.firstname + " " + driver.middlename + " " + driver.lastname)}</td>
            <td>${htmlspecialchars(driver.license_type)}</td>
            <td>
                <button class="view-btn" onclick="viewDriver(${driver.id})"><img src="../icons/view.png" alt="View" /></button>
                <button class="edit-btn" onclick="editDriver(${driver.id}, '${driver.license_id}', '${driver.license_type}', '${driver.firstname}', '${driver.middlename}', '${driver.lastname}', '${driver.gender}', '${driver.date_of_birth}', '${driver.present_address}', '${driver.permanent_address}', '${driver.nationality}', '${driver.contact_number}', '${driver.username}', '${driver.email}')"><img src="../icons/edit.png" alt="Edit" /></button>
                <form action="drivers_list.php" method="post" style="display:inline;">
                    <input type="hidden" name="id" value="${driver.id}">
                    <button type="submit" name="action" value="delete" class="delete-btn"><img src="../icons/delete.png" alt="Delete" /></button>
                </form>
            </td>
        `;
        tbody.appendChild(row);
    });
}

// Live search functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const driverTable = document.getElementById('driverTable');

    searchInput.addEventListener('keyup', function() {
        const filter = searchInput.value.toLowerCase();
        const rows = driverTable.getElementsByTagName('tr');

        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let match = false;

            for (let j = 0; j < cells.length; j++) {
                if (cells[j]) {
                    if (cells[j].textContent.toLowerCase().includes(filter)) {
                        match = true;
                        break;
                    }
                }
            }
            rows[i].style.display = match ? '' : 'none';
        }
    });
});
