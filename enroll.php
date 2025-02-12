<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

if (isset($_SESSION['user_id'])) {
    $loggedInMessage = "You are logged in as: " . $_SESSION['user_id'];
} else {
    $loggedInMessage = "You are not logged in.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enroll with FaceIO</title>
    <script src="https://cdn.faceio.net/fio.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Enroll New User</h1>
    <?php echo "<p>$loggedInMessage</p>"; ?>

    <form id="enrollForm">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <div id="enrollMessage"></div>
        <button type="button" id="enrollButton">Scan Face to Enroll</button>
    </form>

    <script>
        const faceio = new faceIO("<?php echo $_ENV['FACEIO_PUBLIC_ID']; ?>");

        document.getElementById('enrollButton').addEventListener('click', async () => {
            const username = document.getElementById('username').value;
            if (!username) {
                displayMessage("Please enter a username.", "error", "enrollMessage");
                return;
            }

            try {
                let response = await faceio.enroll({
                    "locale": "auto",
                    "payload": {
                        "username": username
                    }
                });

                console.log(response);
                const result = await fetch('verify_enroll', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        facialId: response.facialId,
                        username: username
                    }),
                });

                const data = await result.json();

                if (data.success) {
                    displayMessage("Enrollment successful!", "success", "enrollMessage");
                } else {
                    displayMessage("Enrollment failed on server: " + data.message, "error", "enrollMessage");
                }
            } catch (error) {
                console.error(error);
                displayMessage("FaceIO enrollment failed: " + error, "error", "enrollMessage");
            }
        });

        function displayMessage(message, type, containerId) {
            const container = document.getElementById(containerId);
            container.textContent = message;
            container.className = type;
        }
    </script>

    <a href="/">Home</a>
</body>
</html>
