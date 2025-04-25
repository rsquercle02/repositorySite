// AI summary and suggestions for inspection data
function summaryandsuggestions(data){
    // Show loading
    document.getElementById('loading-indicator').style.display = 'block';

    fetch('https://bfmsi.smartbarangayconnect.com/api/service/approval/generatecontent', {
        method: 'GET',  // HTTP method
        headers: {
            'Content-Type': 'application/json'  // Set content type to JSON
        },
        })
        .then(response => response.json())  // Parse the response as JSON
        .then(data => {
            //const sasdata = data.candidates[0].content.parts[0].text;
            //console.log(sasdata);
            //cleanAndFormatInspectionSummary(sasdata);
            //document.getElementById('summaryandsuggestions').innerHTML = data.candidates[0].content.parts[0].text;
            findAndRenderText(data);
        })// Handle the response
        .catch(error => {
            console.error('Error:', error)
        })  // Handle errors
        .finally(() => {
            // Hide loading
            document.getElementById('loading-indicator').style.display = 'none';
        });

}

const outputDiv = document.getElementById("output");

  function findAndRenderText(obj) {
    if (typeof obj === 'object' && obj !== null) {
      for (let key in obj) {
        if (key === "text" && typeof obj[key] === "string") {
          const cleaned = obj[key]
            .replace(/\\n/g, '\n')
            .replace(/\\"/g, '"')
            .replace(/\\\\/g, '\\');

          // Use marked.js to render the markdown into HTML
          const html = marked.parse(cleaned);
          const div = document.createElement("div");
          div.className = "text-block";
          div.innerHTML = html;
          outputDiv.appendChild(div);
        } else {
          findAndRenderText(obj[key]);
        }
      }
    }
  }
  summaryandsuggestions("unhygienic conditions: 5, unsanitary storage: 3");