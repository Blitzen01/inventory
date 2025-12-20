<?php
    include "../src/cdn/cdn_links.php";
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 - Terminal Error | Stock Focus</title>
        <style>
            :root {
                --bg-deep: #08090a;
                --card-bg: #111316;
                --accent-color: #ffffff;
                --border-muted: #22262b;
                --text-secondary: #636e7b;
            }

            body { 
                background-color: var(--bg-deep);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                font-family: 'Inter', -apple-system, sans-serif;
                margin: 0;
                color: var(--accent-color);
            }

            /* Professional Grid Background */
            body::before {
                content: "";
                position: absolute;
                inset: 0;
                background-image: linear-gradient(rgba(255,255,255,0.01) 1px, transparent 1px), 
                                  linear-gradient(90deg, rgba(255,255,255,0.01) 1px, transparent 1px);
                background-size: 60px 60px;
                z-index: -1;
            }

            .container-404 {
                max-width: 600px;
                width: 90%;
            }

            .error-code {
                font-size: 120px;
                font-weight: 900;
                letter-spacing: -8px;
                margin: 0;
                background: linear-gradient(180deg, #fff 30%, #222 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                line-height: 0.8;
            }

            .status-sub {
                color: var(--text-secondary);
                text-transform: uppercase;
                letter-spacing: 5px;
                font-size: 0.75rem;
                margin-bottom: 30px;
                display: block;
            }

            .terminal-window {
                background: var(--card-bg);
                border: 1px solid var(--border-muted);
                padding: 25px;
                font-family: 'JetBrains Mono', monospace;
                font-size: 13px;
                border-radius: 4px;
                box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            }

            .line { margin-bottom: 5px; display: none; } /* Hidden for typewriter effect */
            .line-meta { color: #3d444d; margin-right: 15px; }
            .line-error { color: #f87171; }
            .line-path { color: #fbbf24; }

            .btn-stealth {
                margin-top: 40px;
                padding: 12px 28px;
                border: 1px solid var(--border-muted);
                color: #fff;
                text-decoration: none;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 2px;
                transition: 0.3s;
                display: inline-flex;
                align-items: center;
                gap: 12px;
            }

            .btn-stealth:hover {
                background: #fff;
                color: #000;
            }

            .cursor {
                display: inline-block;
                width: 8px;
                height: 14px;
                background: #fff;
                animation: blink 1s infinite;
                vertical-align: middle;
            }
            @keyframes blink { 50% { opacity: 0; } }
        </style>
    </head>
    
    <body>

        <div class="container-404">
            <h1 class="error-code">404</h1>
            <span class="status-sub">Target Resource Missing</span>

            <div class="terminal-window" id="terminal">
                <div class="line" style="display: block;"><span class="line-meta">01</span> > INITIALIZING SYSTEM SCAN...</div>
                <div class="line"><span class="line-meta">02</span> > SEARCHING: <span class="line-path">/root/sys_core/restricted/</span></div>
                <div class="line"><span class="line-meta">03</span> > <span class="line-error">ERROR: 0x88404_VOID_DIR</span></div>
                <div class="line"><span class="line-meta">04</span> > Directory mapping failed. Resource is offline.</div>
                <div class="line"><span class="line-meta">05</span> > <span class="line-error">REDIRECTING TO NULL PATH...</span><span class="cursor"></span></div>
            </div>

            <a href="/sys_null/access_denied/void.php" class="btn-stealth">
                <i class="fa-solid fa-terminal"></i> Re-Route Connection
            </a>
        </div>

        <script>
            // Professional Typewriter Sequence
            const lines = document.querySelectorAll('.line');
            let index = 1;

            function showNextLine() {
                if (index < lines.length) {
                    lines[index].style.display = 'block';
                    index++;
                    setTimeout(showNextLine, 600); // 0.6s delay between lines
                }
            }
            
            // Start the sequence after a small initial delay
            setTimeout(showNextLine, 500);
        </script>
    </body>
</html>