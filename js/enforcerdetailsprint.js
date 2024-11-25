
    document.querySelector('.print-btn').addEventListener('click', function () {
    // Get the modal content to be printed
    var modalContent = document.querySelector('#viewEnforcerModal .modal-content').innerHTML;

    // Remove the print and close buttons from the modal content
    modalContent = modalContent.replace(/<button[^>]*print-btn[^>]*>.*?<\/button>/g, ''); // Remove print button
    modalContent = modalContent.replace(/<button[^>]*close[^>]*>.*?<\/button>/g, ''); // Remove close button

    // Create a new window for displaying the content
    var printWindow = window.open('', '', 'height=600,width=800');

    // Write the content into the new window
    printWindow.document.write('<html><head><title>Enforcer Details</title>');
    printWindow.document.write('<style>');
    printWindow.document.write('body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background-color: #f4f4f9; }');
    printWindow.document.write('.print-container { max-width: 800px; margin: auto; background: #ffffff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }');
    printWindow.document.write('.modal-header h2 { text-align: center; font-size: 20px; margin-bottom: 20px; }');
    printWindow.document.write('.enforcer-info { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border: 1px solid #ddd; padding: 10px; border-radius: 8px; }');
    printWindow.document.write('.info-left { flex: 3; font-size: 14px; line-height: 1.8; color: #333; }');
    printWindow.document.write('.info-left p { margin: 6px 0; }');
    printWindow.document.write('@media print { body { padding: 0; } .modal-header { display: none; } .print-btn { display: none; } }');
    printWindow.document.write('</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write('<div class="print-container">');
    printWindow.document.write(modalContent); // Dynamically include modal content without buttons
    printWindow.document.write('</div>');
    printWindow.document.write('</body></html>');

    // Trigger the print dialog (PDF option should be available in most browsers)
    printWindow.document.close(); // Ensure the document is fully written
    printWindow.print(); // Trigger the print dialog
});

