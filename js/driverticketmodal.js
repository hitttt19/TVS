// Open the modal and populate it with the ticket details
function openTicketModal(ticketNo, driverName, offenseName, ticketDate, offenseRate) {
    document.getElementById('modalTicketNo').innerText = ticketNo;
    document.getElementById('modalDriverName').innerText = driverName;
    document.getElementById('modalOffenseName').innerText = offenseName;
    document.getElementById('modalTicketDate').innerText = ticketDate;
    document.getElementById('modalOffenseRate').innerText = '₱' + parseFloat(offenseRate).toFixed(2); // Format the rate as currency

    // Show the modal
    document.getElementById('ticketModal').style.display = 'block';
}

// Close the modal
function closeTicketModal() {
    document.getElementById('ticketModal').style.display = 'none';
}

// Close the modal if the user clicks outside of it
window.onclick = function(event) {
    if (event.target == document.getElementById('ticketModal')) {
        closeTicketModal();
    }
}
