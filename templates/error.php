<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Hotel Reservation System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .error-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #dc3545;
            margin-top: 0;
        }
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .technical-details {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops! Something went wrong</h1>
        
        <div class="error-message">
            <?php if (ini_get('display_errors') && isset($message)): ?>
                <p><strong>Technical Details:</strong></p>
                <div class="technical-details">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php else: ?>
                <p>An unexpected error occurred. Our team has been notified and we're working to fix it.</p>
            <?php endif; ?>
        </div>
        
        <p>Please try one of the following:</p>
        <ul>
            <li>Refresh the page</li>
            <li>Clear your browser cache</li>
            <li>Try again later</li>
        </ul>
        
        <a href="<?php echo BASE_URL; ?>" class="back-link">← Return to Homepage</a>
    </div>
</body>
</html>
