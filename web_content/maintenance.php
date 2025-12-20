<?php
    include "../src/cdn/cdn_links.php";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>System Sync | Stock Focus</title>
        <style>
            :root {
                --bg-dark: #060709;
                --card-dark: #111418;
                --accent-orange: #f59e0b;
                --danger-red: #ef4444;
                --text-gray: #6b7280;
            }

            body { 
                background-color: var(--bg-dark);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Inter', sans-serif;
                margin: 0;
                color: #ffffff;
            }

            /* Dark Tech Grid */
            body::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: linear-gradient(#12151a 1px, transparent 1px), linear-gradient(90deg, #12151a 1px, transparent 1px);
                background-size: 40px 40px;
                opacity: 0.4;
                z-index: -1;
            }

            .maintenance-card {
                max-width: 480px;
                width: 90%;
                background: var(--card-dark);
                border: 1px solid #23282e;
                border-radius: 16px;
                padding: 45px;
                box-shadow: 0 25px 50px rgba(0,0,0,0.8);
                position: relative;
            }

            /* Pulse for the "Live" feel */
            .status-indicator {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                background: rgba(245, 158, 11, 0.05);
                padding: 4px 12px;
                border-radius: 100px;
                border: 1px solid rgba(245, 158, 11, 0.2);
                margin-bottom: 25px;
            }

            .dot {
                width: 8px;
                height: 8px;
                background: var(--accent-orange);
                border-radius: 50%;
            }
            .dot.lagging { background: var(--danger-red); animation: blink 0.5s infinite; }

            @keyframes blink { 50% { opacity: 0; } }

            .progress-label {
                display: flex;
                justify-content: space-between;
                font-family: 'JetBrains Mono', monospace;
                font-size: 11px;
                color: var(--text-gray);
                margin-bottom: 10px;
            }

            .progress-wrapper {
                height: 8px;
                background: #000;
                border-radius: 10px;
                overflow: hidden;
                border: 1px solid #23282e;
            }

            .progress-fill {
                height: 100%;
                width: 15%;
                background: linear-gradient(90deg, #f59e0b, #fbbf24);
                transition: width 0.8s ease; /* Slightly jittery transition */
                position: relative;
            }

            .progress-fill::after {
                content: "";
                position: absolute;
                inset: 0;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                animation: shimmer 1.5s infinite;
            }

            @keyframes shimmer { 0% { transform: translateX(-100%); } 100% { transform: translateX(100%); } }

            .log-window {
                margin-top: 30px;
                background: #000;
                border: 1px solid #23282e;
                border-radius: 8px;
                padding: 15px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 10px;
                text-align: left;
                color: #4ade80;
                height: 60px;
                overflow: hidden;
            }

            .lag-warning {
                color: var(--danger-red);
                font-size: 11px;
                margin-top: 10px;
                font-weight: 700;
                display: none; /* Shown via JS */
            }

            .btn-retry {
                margin-top: 30px;
                color: #4b5563;
                text-decoration: none;
                font-size: 12px;
                transition: 0.3s;
            }
            .btn-retry:hover { color: #fff; }
        </style>
    </head>
    
    <body>

        <div class="maintenance-card">
            <div class="status-indicator" id="status-pill">
                <span class="dot" id="status-dot"></span>
                <span id="status-text" style="font-size: 10px; font-weight: 700; color: var(--accent-orange); text-transform: uppercase;">System Updating</span>
            </div>

            <h4 class="fw-bold mb-3">Resource Optimization</h4>
            <p style="color: var(--text-gray); font-size: 14px; margin-bottom: 30px;">Refactoring audit database clusters. This may take longer due to network instability.</p>

            <div class="progress-label">
                <span id="percent-text">15% Complete</span>
                <span id="speed-text">142 KB/s</span>
            </div>
            
            <div class="progress-wrapper">
                <div class="progress-fill" id="p-bar"></div>
            </div>

            <div id="lag-msg" class="lag-warning">
                <i class="fa-solid fa-triangle-exclamation"></i> CONNECTION UNSTABLE - RETRYING...
            </div>

            <div class="log-window" id="logs">
                > Initializing sync...<br>
                > Validating package headers...
            </div>

            <div class="mt-4 pt-2">
                <span style="font-size: 11px; color: #4b5563;">Est. Time: <span id="timer" style="color: #9ca3af;">Calculating...</span></span>
            </div>

            <a href="javascript:location.reload();" class="btn-retry">
                <i class="fa-solid fa-rotate-right me-1"></i> Force Restart Sync
            </a>
        </div>

        <script>
            let progress = 15;
            let timeLeft = 240;
            let isLagging = false;
            
            const bar = document.getElementById('p-bar');
            const percentLabel = document.getElementById('percent-text');
            const speedLabel = document.getElementById('speed-text');
            const timerLabel = document.getElementById('timer');
            const lagMsg = document.getElementById('lag-msg');
            const dot = document.getElementById('status-dot');
            const logs = document.getElementById('logs');

            const logMessages = [
                "> Checking integrity...", "> Port 8080: Busy", "> Database indexing...", 
                "> Syncing log_id_7741...", "> Cache flush: 100%", "> Resolving headers..."
            ];

            function runUpdate() {
                // Random chance to "Lag" (1 in 10 chance per second)
                if (!isLagging && Math.random() < 0.1) {
                    startLag();
                }

                if (!isLagging) {
                    // Normal slow progress
                    progress += Math.random() * 0.4;
                    if (progress > 99) progress = 99.2;
                    
                    let speed = Math.floor(Math.random() * 80) + 20;
                    speedLabel.innerText = speed + " KB/s";
                    timeLeft -= 0.5;

                    // Update UI
                    bar.style.width = progress + "%";
                    percentLabel.innerText = Math.floor(progress) + "% Complete";
                    
                    // Add random logs
                    if (Math.random() < 0.2) {
                        logs.innerHTML += "<br>" + logMessages[Math.floor(Math.random() * logMessages.length)];
                        logs.scrollTop = logs.scrollHeight;
                    }
                } else {
                    // While lagging
                    speedLabel.innerText = "0.0 KB/s";
                    timeLeft += 1.5; // Timer goes UP while lagging
                }

                let mins = Math.floor(timeLeft / 60);
                let secs = Math.floor(timeLeft % 60);
                timerLabel.innerText = `${mins}m ${secs < 10 ? '0' : ''}${secs}s`;
            }

            function startLag() {
                isLagging = true;
                lagMsg.style.display = "block";
                dot.classList.add('lagging');
                
                // Stay lagged for 3 to 6 seconds
                setTimeout(() => {
                    isLagging = false;
                    lagMsg.style.display = "none";
                    dot.classList.remove('lagging');
                }, Math.random() * 3000 + 3000);
            }

            setInterval(runUpdate, 1000);
        </script>
    </body>
</html>