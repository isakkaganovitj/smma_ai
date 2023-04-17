function processWorkbook(workbook) {
    // Convert the first worksheet to JSON
    const sheetName = workbook.SheetNames[0];
    const worksheet = workbook.Sheets[sheetName];
    const data = XLSX.utils.sheet_to_json(worksheet);

    // Validate the data
    if (!validateData(data)) {
        alert('Invalid data. Please check your Excel file.');
        return;
    }

    // If the data is valid, send it to the server
    sendDataToServer(data);
}
function validateData(data) {
    // Add your validation rules here
    // For example, check if the data array is not empty
    if (data.length === 0) {
        return false;
    }

    return true;
}
function sendDataToServer(data) {
    // Show the loading spinner
    document.getElementById("loading-spinner").style.display = "block";

    // Send the data to the server using the Fetch API
    fetch("server.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    })
        .then((response) => response.json())
        .then((result) => {
            // Hide the loading spinner
            document.getElementById("loading-spinner").style.display = "none";

            console.log(result);
            alert("Data processed successfully. Check the server for the generated_emails.xlsx file.");
        })
        .catch((error) => {
            // Hide the loading spinner
            document.getElementById("loading-spinner").style.display = "none";

            console.error("Error:", error);
        });
}
document.getElementById("email-generation-form").addEventListener("submit", function (event) {
    event.preventDefault();

    // Call the processWorkbook() function here when the form is submitted
});
