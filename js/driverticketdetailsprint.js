 // Function to trigger print dialog for ticket modal
 function printTicket() {
    // Get the modal content to be printed
    var modalContent = document.querySelector('#ticketModal .modal-content').innerHTML;

    // Remove the print and close buttons from the modal content
    modalContent = modalContent.replace(/<button[^>]*print-btn[^>]*>.*?<\/button>/g, ''); // Remove print button
    modalContent = modalContent.replace(/<button[^>]*close[^>]*>.*?<\/button>/g, ''); // Remove close button

    // Create a new window for displaying the content
    var printWindow = window.open('', '', 'height=600,width=800');

    // Write the content into the new window
    printWindow.document.write('<html><head><title>Driver\'s Ticket Details</title>');
    printWindow.document.write('<style>');
    printWindow.document.write(`
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        .print-container {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .modal-header h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .modal-detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .modal-detail-table th, .modal-detail-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
            color: #333;
        }
        .modal-footer {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }
        @media print {
            body {
                padding: 0;
            }
            .print-btn, .close-btn {
                display: none;
            }
        }
    `);
    printWindow.document.write('</style></head><body>');
    printWindow.document.write('<div class="print-container">');
    printWindow.document.write(modalContent); // Dynamically include modal content without buttons
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');

    // Trigger the print dialog (PDF option should be available in most browsers)
    printWindow.document.close(); // Ensure the document is fully written
    printWindow.print(); // Trigger the print dialog
}