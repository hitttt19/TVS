        // Function to show the modal for creating a new enforcer
        function showEnforcerForm() {
            document.getElementById("formTitle").innerText = "Create New Enforcer";
            document.getElementById("entryId").value = ''; // Reset form fields
            document.getElementById("formAction").value = 'create'; // Set action to create
            clearFormFields(); // Clear fields
            document.getElementById("enforcerFormModal").style.display = "block"; // Show modal
        }

        // Function to show the modal for editing an enforcer
        function editEnforcer(id, badge_id, firstname, middlename, lastname, gender, date_of_birth, present_address, permanent_address, contact_number, username, email) {
            document.getElementById("entryId").value = id;
            document.getElementById("badge_id").value = badge_id;
            document.getElementById("firstname").value = firstname;
            document.getElementById("middlename").value = middlename;
            document.getElementById("lastname").value = lastname;
            document.getElementById("gender").value = gender;
            document.getElementById("date_of_birth").value = date_of_birth;
            document.getElementById("present_address").value = present_address;
            document.getElementById("permanent_address").value = permanent_address;
            document.getElementById("contact_number").value = contact_number;
            document.getElementById("username").value = username;
            document.getElementById("email").value = email;

            document.getElementById("formTitle").innerText = "Edit Enforcer";
            document.getElementById("formAction").value = 'edit'; // Set action to edit
            document.getElementById("enforcerFormModal").style.display = "block"; // Show modal
        }

        // Function to clear form fields
        function clearFormFields() {
            document.getElementById("badge_id").value = '';
            document.getElementById("firstname").value = '';
            document.getElementById("middlename").value = '';
            document.getElementById("lastname").value = '';
            document.getElementById("gender").value = '';
            document.getElementById("date_of_birth").value = '';
            document.getElementById("present_address").value = '';
            document.getElementById("permanent_address").value = '';
            document.getElementById("contact_number").value = '';
            document.getElementById("username").value = '';
            document.getElementById("email").value = '';
            document.getElementById("password").value = ''; // Optionally clear password
        }

        // Function to delete an enforcer
        function deleteEnforcer(id) {
            if (confirm("Are you sure you want to delete this enforcer?")) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'enforcers_list.php';
                
                // Create hidden input for the action and ID
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'delete';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;

                form.appendChild(actionInput);
                form.appendChild(idInput);
                
                // Append form to body and submit
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Close modal when clicking on the close button
        document.getElementById("closeEnforcerForm").onclick = function() {
            document.getElementById("enforcerFormModal").style.display = "none"; // Hide modal
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == document.getElementById("enforcerFormModal")) {
                document.getElementById("enforcerFormModal").style.display = "none"; // Hide modal
            }
        }

        function viewEnforcer(id) {
        // Redirect to the current page with view_id parameter
        window.location.href = 'enforcers_list.php?view_id=' + id;
        }

        document.getElementById('closeViewEnforcer').onclick = function() {
            document.getElementById('viewEnforcerModal').style.display = 'none';
        }

        // Check if the view_id parameter is present and show the modal
        window.onload = function() {
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('view_id')) {
                document.getElementById('viewEnforcerModal').style.display = 'block';
            }
        }

        // Live search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const enforcerTable = document.getElementById('enforcerTable');

            searchInput.addEventListener('keyup', function() {
                const filter = searchInput.value.toLowerCase();
                const rows = enforcerTable.getElementsByTagName('tr');

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
