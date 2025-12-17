<?php
    include "../src/cdn/cdn_links.php"
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>System Unavailable</title>
        <style>
            .error-container {
                max-width: 600px;
            }
            .btn-primary {
                background-color: #004d99; /* Deep Professional Blue */
                border-color: #004d99;
            }
            .btn-primary:hover {
                background-color: #003366;
                border-color: #003366;
            }
        </style>
    </head>
    
    <body class="bg-white d-flex align-items-center justify-content-center vh-100">

        <div class="text-center error-container p-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="70" height="70" fill="#dc3545" class="bi bi-exclamation-triangle-fill mb-4" viewBox="0 0 16 16">
            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.035 1.767.98 1.767h13.71c.945 0 1.438-.99.98-1.767zM8 5c.535 0 .954.43.954.965v3.479c0 .535-.419.966-.954.966a.954.954 0 0 1-.954-.966V5.965C7.046 5.43 7.465 5 8 5m0 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
            </svg>
            <h1 class="display-4 fw-bold text-dark mb-3">System Unavailable</h1>
            <p class="lead text-secondary mb-4">
                We are experiencing unexpected downtime. The primary resource you are trying to reach is currently inaccessible.
            </p>
            <p class="text-muted small">
                Error Code: Resource Not Found / Internal Forwarding Failure
            </p>
            <a href="/" class="btn btn-lg btn-primary mt-4 fw-semibold">Return to Main Site</a>
        </div>
    </body>
</html>