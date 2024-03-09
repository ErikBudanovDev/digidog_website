document.getElementById('gpt-submit').addEventListener('click', function() {
    console.log('ping')
    var inputData = document.getElementById('gpt-input').value;
    fetch(gptAjax.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=gpt_request&inputData=' + encodeURIComponent(inputData),
    })
    .then(response => response.json()) // Convert the response to JSON
    .then(data => {
        console.log(data)
        if (data.success && data.data) {
            document.getElementById('gpt-response').innerHTML = data.data; // Display the 'data' value
        } else {
            console.error('Error: ', data);
            document.getElementById('gpt-response').innerHTML = 'Error: Something went wrong!';
        }
    })
    .catch(error => console.error('Error:', error));
});
