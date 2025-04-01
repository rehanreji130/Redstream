// Function to Validate Registration Form Inputs
function validateForm(formId) {
    let form = document.getElementById(formId);
    let inputs = form.getElementsByTagName("input");

    for (let i = 0; i < inputs.length; i++) {
        if (inputs[i].value.trim() === "") {
            alert("All fields are required!");
            inputs[i].focus();
            return false;
        }
    }
    return true;
}

// Function to Show Notification
function showNotification(message, type = "success") {
    let notification = document.createElement("div");
    notification.className = `notification ${type}`;
    notification.innerText = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// Function to Fetch Nearby Hospitals Based on Geolocation
function getNearbyHospitals() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                let lat = position.coords.latitude;
                let lon = position.coords.longitude;

                // Make an AJAX call to fetch hospitals sorted by proximity
                fetch(`recipient_hospital_list.php?lat=${lat}&lon=${lon}`)
                    .then(response => response.json())
                    .then(data => {
                        let hospitalList = document.getElementById("hospital-list");
                        hospitalList.innerHTML = "";
                        data.forEach(hospital => {
                            let listItem = document.createElement("li");
                            listItem.innerText = `${hospital.name} - ${hospital.distance} km away`;
                            hospitalList.appendChild(listItem);
                        });
                    })
                    .catch(error => console.error("Error fetching hospitals:", error));
            },
            function (error) {
                alert("Geolocation failed. Please enable location services.");
            }
        );
    } else {
        alert("Geolocation is not supported by your browser.");
    }
}

// Function to Handle AJAX Requests
function sendAjaxRequest(url, data, callback) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", url, true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            callback(xhr.responseText);
        }
    };

    xhr.send(data);
}

// Function to Send Blood Request via AJAX
function requestBlood(bloodType, hospitalId) {
    let data = `blood_type=${bloodType}&hospital_id=${hospitalId}`;

    sendAjaxRequest("hospital_request_blood.php", data, function (response) {
        showNotification("Blood request sent successfully!", "success");
    });
}

// Function to Send Notifications via AJAX
function sendNotification(donorId, message) {
    let data = `donor_id=${donorId}&message=${encodeURIComponent(message)}`;

    sendAjaxRequest("hospital_send_notifications.php", data, function (response) {
        showNotification("Notification sent!", "success");
    });
}
