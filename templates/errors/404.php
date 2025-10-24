<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Not Found - Wasteland Dominion</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="wasteland-bg">
    <div class="container">
        <div class="error-page">
            <h1 class="error-title">🔍 LOCATION NOT FOUND</h1>
            <div class="error-content">
                <div class="error-icon">🗺️</div>
                <h2>Lost in the Wasteland</h2>
                <p>The location you're looking for doesn't exist in our wasteland database. It may have been destroyed in the nuclear fallout or never existed at all.</p>
                
                <div class="error-actions">
                    <a href="/" class="btn btn-primary">🏠 Return Home</a>
                    <a href="/game/dashboard" class="btn btn-secondary">🎮 Game Dashboard</a>
                    <a href="/forum" class="btn btn-secondary">💬 Community Forum</a>
                </div>
                
                <div class="error-details">
                    <p><strong>Error Code:</strong> 404 - Page Not Found</p>
                    <p><strong>Requested:</strong> <?= htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown') ?></p>
                    <p><strong>Time:</strong> <?= date('Y-m-d H:i:s') ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <style>
        .error-page {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 600px;
            margin: 2rem auto;
            background: linear-gradient(135deg, rgba(45, 24, 16, 0.9), rgba(74, 74, 74, 0.7));
            border-radius: 15px;
            border: 2px solid var(--metal-blue, #1e3a5f);
            box-shadow: 0 0 30px rgba(30, 58, 95, 0.3);
        }
        
        .error-title {
            font-family: 'Orbitron', monospace;
            font-size: 2.5rem;
            color: var(--metal-blue, #1e3a5f);
            margin-bottom: 2rem;
            text-shadow: 0 0 10px rgba(30, 58, 95, 0.5);
        }
        
        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }
        
        .error-content h2 {
            font-family: 'Orbitron', monospace;
            color: var(--wasteland-yellow, #ffd23f);
            margin-bottom: 1rem;
        }
        
        .error-content p {
            margin-bottom: 2rem;
            line-height: 1.6;
            opacity: 0.9;
        }
        
        .error-actions {
            margin: 2rem 0;
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .error-details {
            margin-top: 3rem;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
        
        .error-details p {
            margin: 0.5rem 0;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        @media (max-width: 768px) {
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .error-title {
                font-size: 2rem;
            }
        }
    </style>
</body>
</html>