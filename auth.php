<?php
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Authenticate with FaceIO</title>
    <script src="https://cdn.faceio.net/fio.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Login with FaceIO</h1>
    <div id="authMessage"></div>
    <button id="authButton">Scan Face to Login</button>

    <script>
        const faceio = new faceIO("<?php echo $_ENV['FACEIO_PUBLIC_ID']; ?>");

        document.getElementById('authButton').addEventListener('click', async () => {
            try {
                let response = await faceio.authenticate({
                    "locale": "auto"
                });

                const result = await fetch('verify_auth', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        facialId: response.facialId
                    }),
                });

                const data = await result.json();

                if (data.success) {
                    displayMessage("Authentication successful! Redirecting...", "success", "authMessage");
                    setTimeout(() => {
                        window.location.href = '/';
                    }, 2000);
                } else {
                    displayMessage("Authentication failed: " + data.message, 'error', "authMessage");
                }
            } catch (error) {
                console.error(error);
                displayMessage("FaceIO authentication failed: " + error, 'error', "authMessage");
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