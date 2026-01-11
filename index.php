<?php
// Include the secure database connection file
require_once 'config/configdb.php';

// Start or resume session
session_start();

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize username
$username   = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
$contact    = trim(filter_input(INPUT_POST, 'contact', FILTER_SANITIZE_STRING));
$pc_number  = (int) filter_input(INPUT_POST, 'pc_number', FILTER_SANITIZE_NUMBER_INT);

    if (!empty($username)) {
        try {
            // Set timezone to Indian Standard Time
            date_default_timezone_set('Asia/Kolkata');
            $created_at = date("Y-m-d H:i:s");

            // Begin transaction for atomic operations
            $pdo->beginTransaction();

            // Insert into tbl_user_detail
           $stmt = $pdo->prepare("INSERT INTO tbl_user_detail (user_name, contact, pc_number, created_at) 
                       VALUES (:username, :contact, :pc_number, :created_at)");
$stmt->execute([
    'username'   => $username,
    'contact'    => $contact,
    'pc_number'  => $pc_number,
    'created_at' => $created_at
]);

            $user_id = $pdo->lastInsertId();

            // Insert into tbl_user_score with default NULL values for challenges
            $stmt = $pdo->prepare("INSERT INTO tbl_user_score (user_id) VALUES (:user_id)");
            $stmt->execute(['user_id' => $user_id]);

            // Commit transaction
            $pdo->commit();

            // Store user data in session
           $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $username;
            $_SESSION['user_contact'] = $contact;
            $_SESSION['user_pc_number'] = $pc_number;



            // Redirect to the first challenge
            header("Location: ./pages/challenge1.php");
            exit();
        } catch (PDOException $e) {
            // Rollback on error
            $pdo->rollBack();
            error_log("Registration failed: " . $e->getMessage());
            $error = "Error: Could not register user.";
        }
    } else {
        $error = "Please enter a username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CYBERFLAG :: Anonymous CTF Challenge</title>
    <!-- <script src="https://cdn.tailwindcss.com"></script>
      -->
    <link href="tailwind.min.css" rel="stylesheet">
    <style>
        /* @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono:wght@400&family=Rajdhani:wght@300;400;500;600;700&family=Orbitron:wght@400;700;900&display=swap'); */
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Rajdhani', sans-serif;
            background: #000000;
            color: #00ff00;
            overflow-x: hidden;
            cursor: crosshair;
        }
        
        .hacker-bg {
            background: 
                radial-gradient(circle at 20% 50%, rgba(0, 255, 0, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 0, 0, 0.03) 0%, transparent 50%),
                radial-gradient(circle at 40% 80%, rgba(0, 255, 255, 0.02) 0%, transparent 50%),
                linear-gradient(180deg, #000000 0%, #001100 50%, #000000 100%);
            background-size: 100% 100%, 100% 100%, 100% 100%, 100% 100%;
            animation: backgroundShift 15s ease-in-out infinite;
        }
        
        @keyframes backgroundShift {
            0%, 100% { background-position: 0% 0%, 0% 0%, 0% 0%, 0% 0%; }
            50% { background-position: 20% 20%, 80% 80%, 60% 20%, 0% 0%; }
        }
        
        .matrix-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
            opacity: 0.15;
            background: repeating-linear-gradient(
                0deg,
                transparent,
                transparent 2px,
                rgba(0, 255, 0, 0.03) 2px,
                rgba(0, 255, 0, 0.03) 4px
            );
            animation: matrixScroll 20s linear infinite;
        }
        
        @keyframes matrixScroll {
            0% { transform: translateY(0); }
            100% { transform: translateY(100px); }
        }
        
        .binary-rain {
            position: fixed;
            top: -100vh;
            left: 0;
            width: 100vw;
            height: 200vh;
            pointer-events: none;
            z-index: 1;
            opacity: 0.1;
        }
        
        .binary-char {
            position: absolute;
            color: #00ff00;
            font-family: 'Share Tech Mono', monospace;
            font-size: 14px;
            animation: binaryFall linear infinite;
        }
        
        @keyframes binaryFall {
            0% { 
                transform: translateY(-100vh);
                opacity: 1;
            }
            100% { 
                transform: translateY(100vh);
                opacity: 0;
            }
        }
        
        .skull-cursor {
            position: fixed;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle, #ff0000 30%, transparent 30%);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transition: all 0.1s ease;
            box-shadow: 0 0 10px #ff0000;
        }
        
        .terminal-glow {
            box-shadow: 
                0 0 20px rgba(0, 255, 0, 0.5),
                0 0 40px rgba(0, 255, 0, 0.3),
                0 0 80px rgba(0, 255, 0, 0.1),
                inset 0 0 20px rgba(0, 255, 0, 0.05);
            border: 2px solid #00ff00;
            background: rgba(0, 0, 0, 0.9);
        }
        
        .hack-button {
            background: linear-gradient(45deg, #000000, #001100, #000000);
            border: 2px solid #00ff00;
            box-shadow: 
                0 0 10px #00ff00,
                inset 0 0 10px rgba(0, 255, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .hack-button::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(transparent, rgba(0, 255, 0, 0.3), transparent);
            animation: rotate 2s linear infinite;
            z-index: -1;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .hack-button:hover {
            box-shadow: 
                0 0 30px #00ff00,
                inset 0 0 20px rgba(0, 255, 0, 0.2);
            transform: scale(1.05);
            background: linear-gradient(45deg, #001100, #002200, #001100);
        }
        
        .glitch-text {
            position: relative;
            animation: glitch 1s infinite;
        }
        
        .glitch-text::before,
        .glitch-text::after {
            content: attr(data-text);
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        .glitch-text::before {
            animation: glitch-red 0.3s infinite;
            color: #ff0000;
            z-index: -1;
        }
        
        .glitch-text::after {
            animation: glitch-blue 0.3s infinite;
            color: #0000ff;
            z-index: -2;
        }
        
        @keyframes glitch {
            0%, 100% { transform: translate(0); }
            10% { transform: translate(-2px, -2px); }
            20% { transform: translate(2px, 2px); }
            30% { transform: translate(-2px, 2px); }
            40% { transform: translate(2px, -2px); }
            50% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            70% { transform: translate(-2px, 2px); }
            80% { transform: translate(2px, -2px); }
            90% { transform: translate(-2px, -2px); }
        }
        
        @keyframes glitch-red {
            0%, 100% { transform: translate(0); }
            10% { transform: translate(-2px, -2px); }
            20% { transform: translate(2px, 2px); }
            30% { transform: translate(-2px, 2px); }
            40% { transform: translate(2px, -2px); }
            50% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            70% { transform: translate(-2px, 2px); }
            80% { transform: translate(2px, -2px); }
            90% { transform: translate(-2px, -2px); }
        }
        
        @keyframes glitch-blue {
            0%, 100% { transform: translate(0); }
            10% { transform: translate(2px, 2px); }
            20% { transform: translate(-2px, -2px); }
            30% { transform: translate(2px, -2px); }
            40% { transform: translate(-2px, 2px); }
            50% { transform: translate(2px, 2px); }
            60% { transform: translate(-2px, -2px); }
            70% { transform: translate(2px, -2px); }
            80% { transform: translate(-2px, 2px); }
            90% { transform: translate(2px, 2px); }
        }
        
        .terminal-scan {
            position: relative;
            overflow: hidden;
        }
        
        .terminal-scan::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #00ff00, transparent);
            animation: terminalScan 3s infinite;
        }
        
        @keyframes terminalScan {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        .hack-input {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid #003300;
            color: #00ff00;
            font-family: 'Share Tech Mono', monospace;
            transition: all 0.3s ease;
        }
        
        .hack-input:focus {
            border-color: #00ff00;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.3);
            background: rgba(0, 20, 0, 0.9);
        }
        
        .skull-icon {
            width: 40px;
            height: 40px;
            background: #ff0000;
            clip-path: polygon(50% 0%, 100% 38%, 82% 100%, 18% 100%, 0% 38%);
            animation: skullPulse 2s infinite;
        }
        
        @keyframes skullPulse {
            0%, 100% { opacity: 0.8; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.1); }
        }
        
        .warning-flash {
            animation: warningFlash 1s infinite;
        }
        
        @keyframes warningFlash {
            0%, 100% { background: rgba(255, 0, 0, 0.1); }
            50% { background: rgba(255, 0, 0, 0.3); }
        }
        
        .ascii-skull {
            /* font-family: 'Share Tech Mono', monospace; */
            font-size: 8px;
            line-height: 8px;
            color: #ff0000;
            text-shadow: 0 0 10px #ff0000;
        }
        
        .network-lines {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 1;
        }
        
        .network-line {
            position: absolute;
            background: linear-gradient(90deg, transparent, #00ff00, transparent);
            height: 1px;
            animation: networkPulse 4s ease-in-out infinite;
        }
        
        @keyframes networkPulse {
            0%, 100% { opacity: 0; width: 0; }
            50% { opacity: 0.5; width: 200px; }
        }
        
        .virus-warning {
            border: 2px solid #ff0000;
            background: rgba(255, 0, 0, 0.1);
            box-shadow: 0 0 20px rgba(255, 0, 0, 0.5);
            animation: virusAlert 0.5s infinite;
        }
        
        @keyframes virusAlert {
            0%, 100% { border-color: #ff0000; }
            50% { border-color: #ff6600; }
        }
        
        .hack-progress {
            background: rgba(0, 255, 0, 0.1);
            height: 4px;
            position: relative;
            overflow: hidden;
        }
        
        .hack-progress::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #00ff00, #ffff00, #00ff00);
            animation: hackProgress 5s ease-in-out infinite;
        }
        
        @keyframes hackProgress {
            0% { width: 0%; }
            50% { width: 100%; }
            100% { width: 0%; }
        }
    </style>
</head>
<body class="hacker-bg min-h-screen">
    <!-- Custom Cursor -->
    <div class="skull-cursor" id="customCursor"></div>
    
    <!-- Matrix Overlay -->
    <div class="matrix-overlay"></div>
    
    <!-- Binary Rain -->
    <div class="binary-rain" id="binaryRain"></div>
    
    <!-- Network Lines -->
    <div class="network-lines" id="networkLines"></div>
    
    <!-- Main Container - Full Width -->
    <div class="min-h-screen w-full flex relative z-10">
        
        <!-- Left Side - Hacker Info Panel -->
        <div class="w-1/3 p-8 flex flex-col justify-center space-y-6">
            <!-- ASCII Skull Art -->
            <div class="text-center mb-8">
                <pre class="ascii-skull text-red-500">
    ░░░░░░░░░▄░░░░░░░░░░░░░░▄░░░░░░░░░
    ░░░░░░░░▌▒█░░░░░░░░░░░▄▀▒▌░░░░░░░
    ░░░░░░░░▌▒▒█░░░░░░░░▄▀▒▒▒▐░░░░░░░
    ░░░░░░░▐▄▀▒▒▀▀▀▀▄▄▄▀▒▒▒▒▒▐░░░░░░░
    ░░░░░▄▄▀▒░▒▒▒▒▒▒▒▒▒█▒▒▄█▒▐░░░░░░░
    ░░░▄▀▒▒▒░░░▒▒▒░░░▒▒▒▀██▀▒▌░░░░░░░
    ░░▐▒▒▒▄▄▒▒▒▒░░░▒▒▒▒▒▒▒▀▄▒▒▌░░░░░░
    ░░▌░░▌█▀▒▒▒▒▒▄▀█▄▒▒▒▒▒▒▒█▒▐░░░░░░
    ░▐░░░▒▒▒▒▒▒▒▒▌██▀▒▒░░░▒▒▒▀▄▌░░░░░
    ░▌░▒▄██▄▒▒▒▒▒▒▒▒▒░░░░░░▒▒▒▒▌░░░░░
    ▀▒▀▐▄█▄█▌▄░▀▒▒░░░░░░░░░░▒▒▒▐░░░░░
    ▐▒▒▐▀▐▀▒░▄▄▒▄▒▒▒▒▒▒░▒░▒░▒▒▒▒▌░░░░
    ▐▒▒▒▀▀▄▄▒▒▒▄▒▒▒▒▒▒▒▒░▒░▒░▒▒▐░░░░░
    ░▌▒▒▒▒▒▒▀▀▀▒▒▒▒▒▒░▒░▒░▒▒▄▒▒▐░░░░░
    ░▐▒▒▒▒▒▒▒▒▒▒▒▒▒▒░▒░▒░▒▄▒▒▒▒▌░░░░░
    ░░▀▄▒▒▒▒▒▒▒▒▒▒▒░▒░▒░▒▄▒▒▒▒▐░░░░░░
                </pre>
            </div>
            
            <!-- Threat Level Display -->
            <div class="virus-warning rounded-lg p-6">
                <div class="text-center mb-4">
                    <h3 class="text-2xl font-bold text-red-400 mb-2">⚠️ THREAT DETECTED ⚠️</h3>
                    <div class="text-red-300 font-mono text-sm">SECURITY LEVEL: MAXIMUM</div>
                </div>
                
                <!-- Live Stats -->
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-red-400" id="hackersCount">666</div>
                        <div class="text-red-300 text-xs">ACTIVE HACKERS</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-400" id="systemsHacked">1337</div>
                        <div class="text-yellow-300 text-xs">SYSTEMS BREACHED</div>
                    </div>
                </div>
                
                <!-- Hack Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-xs text-red-300 mb-1">
                        <span>SYSTEM INFILTRATION</span>
                        <span>∞%</span>
                    </div>
                    <div class="hack-progress rounded"></div>
                </div>
            </div>
            
            <!-- Warning Messages -->
            <div class="space-y-3">
                <div class="warning-flash rounded p-3 border border-red-500">
                    <div class="text-red-400 text-sm font-mono">
                        ⚡ FIREWALL BREACH DETECTED
                    </div>
                </div>
                <div class="warning-flash rounded p-3 border border-yellow-500" style="animation-delay: 0.5s;">
                    <div class="text-yellow-400 text-sm font-mono">
                        🔥 INTRUSION PROTOCOL ACTIVE
                    </div>
                </div>
                <div class="warning-flash rounded p-3 border border-purple-500" style="animation-delay: 1s;">
                    <div class="text-purple-400 text-sm font-mono">
                        💀 ANONYMOUS MODE ENABLED
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Center - Main Terminal -->
        <div class="flex-1 flex items-center justify-center p-8">
            <div class="terminal-glow rounded-lg w-full max-w-2xl p-12 terminal-scan">
                <!-- Terminal Header -->
                <div class="flex justify-between items-center mb-8 pb-4 border-b border-green-500">
                    <div class="flex items-center space-x-4">
                        <div class="skull-icon"></div>
                        <div>
                            <h1 class="glitch-text text-4xl font-bold text-green-400" data-text="CYBERFLAG" style="font-family: 'Orbitron', monospace;">
                            CYBERFLAG
                            </h1>
                            <div class="text-green-300 font-mono">ANONYMOUS CTF CHALLENGE</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-red-400 font-mono text-sm" id="systemTime">00:00:00</div>
                        <div class="text-green-400 text-xs">BREACH TIME</div>
                    </div>
                </div>
                
                <!-- Status Console -->
                <div class="bg-black bg-opacity-60 rounded-lg p-6 mb-8 font-mono text-sm border border-green-700">
                    <div class="text-green-400 mb-2">[SYSTEM] Initializing anonymous connection...</div>
                    <div class="text-yellow-400 mb-2">[WARNING] Entering restricted zone</div>
                    <div class="text-red-400 mb-2">[ALERT] All activities monitored</div>
                    <div class="text-blue-400 mb-2">[INFO] Quantum encryption enabled</div>
                    <div class="text-green-400">[READY] Awaiting hacker credentials...</div>
                </div>
                
                <!-- Main Form -->
               <form action="index.php" method="POST" class="space-y-8">
    <!-- Username Input -->
    <div>
        <label class="block text-green-400 font-mono text-sm mb-4">
            <span class="flex items-center space-x-2">
                <span>💀</span>
                <span>ENTER HACKER ALIAS:</span>
                <span class="text-red-400 animate-pulse">[REQUIRED]</span>
            </span>
        </label>
        <input 
            type="text" 
            id="username" 
            name="username" 
            placeholder="anonymous_h4ck3r" 
            required
            class="hack-input w-full px-6 py-4 rounded-lg text-lg focus:outline-none"
            autocomplete="off"
        >
    </div>

    <!-- Contact Number (10 digits) -->
    <div>
        <label class="block text-green-400 font-mono text-sm mb-4">
            <span class="flex items-center space-x-2">
                <span>📞</span>
                <span>CONTACT NUMBER:</span>
                <span class="text-red-400 animate-pulse">[REQUIRED]</span>
            </span>
        </label>
        <input
            type="text"
            id="contact"
            name="contact"
            placeholder="Enter Contact number (10 digits)"
            required
            pattern="\d{10}"
            inputmode="numeric"
            maxlength="10"
            class="hack-input w-full px-6 py-4 rounded-lg text-lg focus:outline-none"
            title="Enter exactly 10 digits"
            autocomplete="off"
        >
    </div>

    <!-- PC Number Select (1 - 70) -->
    <div>
        <label class="block text-green-400 font-mono text-sm mb-4">
            <span class="flex items-center space-x-2">
                <span>🖥️</span>
                <span>SELECT PC NUMBER:</span>
                <span class="text-red-400 animate-pulse">[REQUIRED]</span>
            </span>
        </label>
        <select
            id="pc_number"
            name="pc_number"
            required
            class="hack-input w-full px-6 py-4 rounded-lg text-lg focus:outline-none"
        >
        <option value="">----Select PC Number---</option>
            <?php for ($i = 1; $i <= 70; $i++): ?>
    <option value="<?= $i ?>">PC <?= $i ?></option>
<?php endfor; ?>

        </select>
    </div>

              <!-- Security Bypass -->
                    <div class="bg-black bg-opacity-40 rounded-lg p-4 border border-red-500">
                        <div class="text-red-400 text-sm font-mono mb-2">
                            🚨 BYPASSING SECURITY PROTOCOLS...
                        </div>
                        <div class="text-xs text-green-400 font-mono">
                            ✓ Firewall: BYPASSED<br>
                            ✓ Antivirus: DISABLED<br>
                            ✓ Intrusion Detection: OFFLINE<br>
                            ✓ Government Backdoors: BLOCKED
                        </div>
                    </div>
                    
                    <!-- Hack Button -->
                    <button 
                        type="submit" 
                        class="hack-button w-full py-6 rounded-lg font-bold text-xl text-green-400 font-mono relative z-10"
                    >
                        <span class="flex items-center justify-center space-x-4">
                            <span>💀</span>
                            <span>INITIATE HACK SEQUENCE</span>
                            <span>💀</span>
                        </span>
                    </button>
</form>

                <?php if (isset($error)): ?>
                <div class="mt-6 virus-warning rounded-lg p-4">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl">💀</span>
                        <span class="text-red-400 font-bold">HACK FAILED</span>
                    </div>
                    <p class="text-red-300 mt-2 font-mono">
                        ERROR: <?php echo htmlspecialchars($error); ?>
                    </p>
                </div>
                <?php endif; ?>
                <!-- <a href="users.php" class="text-green-400 font-mono text-sm mt-4 inline-block">View All Users</a> -->
            </div>
        </div>
        
        <!-- Right Side - System Monitor -->
        <div class="w-1/3 p-8 flex flex-col justify-center space-y-6">
            <!-- Live System Monitor -->
            <div class="terminal-glow rounded-lg p-6">
                <h3 class="text-green-400 font-mono text-lg mb-4">🖥️ SYSTEM MONITOR</h3>
                
                <!-- System Stats -->
                <div class="space-y-3 text-sm font-mono">
                    <div class="flex justify-between">
                        <span class="text-gray-400">CPU USAGE:</span>
                        <span class="text-red-400" id="cpuUsage">98.7%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">RAM:</span>
                        <span class="text-yellow-400" id="ramUsage">15.8GB</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">NETWORK:</span>
                        <span class="text-green-400">COMPROMISED</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">LOCATION:</span>
                        <span class="text-purple-400">HIDDEN</span>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activities -->
            <div class="terminal-glow rounded-lg p-6">
                <h3 class="text-green-400 font-mono text-lg mb-4">📡 BREACH LOG</h3>
                
                <div class="space-y-2 text-xs font-mono">
                    <div class="text-green-400">[12:34:56] Pentagon database accessed</div>
                    <div class="text-yellow-400">[12:33:21] CIA files downloaded</div>
                    <div class="text-red-400">[12:32:45] FBI watchlist updated</div>
                    <div class="text-blue-400">[12:31:12] Anonymous proxy activated</div>
                    <div class="text-purple-400">[12:30:33] Satellite connection hijacked</div>
                </div>
            </div>
            
            <!-- Challenge Timer -->
            <div class="terminal-glow rounded-lg p-6 text-center">
                <h3 class="text-red-400 font-mono text-lg mb-2">⏰ ESCAPE TIME</h3>
                <div class="text-4xl font-bold text-red-400 font-mono" id="challengeTimer">
                    01:00:00
                </div>
                <div class="text-red-300 text-sm mt-2">Before system traces you</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-black bg-opacity-80 border-t border-green-500 p-4 z-20">
        <div class="flex justify-between items-center text-green-400 font-mono text-sm">
            <div>© 2025 ANONYMOUS COLLECTIVE | CYBERFLAG DIVISION</div>
            <div class="flex space-x-4">
                <span>STATUS: <span class="text-red-400">INFILTRATING</span></span>
                <span>SECURITY: <span class="text-yellow-400">BYPASSED</span></span>
                <span>TRACE: <span class="text-green-400">BLOCKED</span></span>
            </div>
        </div>
    </div>
    
    <script>
        // Custom cursor
        const cursor = document.getElementById('customCursor');
        document.addEventListener('mousemove', (e) => {
            cursor.style.left = `${e.clientX}px`;
            cursor.style.top = `${e.clientY}px`;
        });

        // Binary rain effect
        function createBinaryRain() {
            const container = document.getElementById('binaryRain');
            const chars = '01abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
            
            setInterval(() => {
                const char = document.createElement('div');
                char.className = 'binary-char';
                char.textContent = chars[Math.floor(Math.random() * chars.length)];
                char.style.left = `${Math.random() * 100}vw`;
                char.style.animationDuration = `${Math.random() * 3 + 2}s`;
                char.style.fontSize = `${Math.random() * 10 + 12}px`;
                
                container.appendChild(char);
                
                setTimeout(() => char.remove(), 5000);
            }, 100);
        }

        // Network lines
        function createNetworkLines() {
            const container = document.getElementById('networkLines');
            
            setInterval(() => {
                const line = document.createElement('div');
                line.className = 'network-line';
                line.style.top = `${Math.random() * 100}vh`;
                line.style.left = `${Math.random() * 100}vw`;
                line.style.animationDelay = `${Math.random() * 2}s`;
                
                container.appendChild(line);
                
                setTimeout(() => line.remove(), 4000);
            }, 1500);
        }

        // Live stats update
        function updateStats() {
            document.getElementById('hackersCount').textContent = Math.floor(Math.random() * 100 + 600);
            document.getElementById('systemsHacked').textContent = Math.floor(Math.random() * 500 + 1300);
            document.getElementById('cpuUsage').textContent = `${(Math.random() * 10 + 90).toFixed(1)}%`;
            document.getElementById('ramUsage').textContent = `${(Math.random() * 5 + 14).toFixed(1)}GB`;
        }

        // System time
        function updateTime() {
            const now = new Date();
            document.getElementById('systemTime').textContent = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        // Challenge timer
        let challengeTime = 3600; // 60 minutes
        function updateTimer() {
            try {
                const hours = Math.floor(challengeTime / 3600);
                const minutes = Math.floor((challengeTime % 3600) / 60);
                const seconds = challengeTime % 60;
                
                document.getElementById('challengeTimer').textContent = 
                    `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                
                if (challengeTime > 0) {
                    challengeTime--;
                } else {
                    document.getElementById('challengeTimer').classList.add('warning-flash');
                    document.getElementById('challengeTimer').textContent = 'TIMEOUT';
                }
            } catch (error) {
                console.error('Timer update failed:', error);
            }
        }

        // Initialize all effects
        function initialize() {
            try {
                createBinaryRain();
                createNetworkLines();
                setInterval(updateStats, 2000);
                setInterval(updateTime, 1000);
                setInterval(updateTimer, 1000);
                
                // Add keyboard effects
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        document.querySelector('.hack-button').classList.add('animate-pulse');
                        setTimeout(() => {
                            document.querySelector('.hack-button').classList.remove('animate-pulse');
                        }, 200);
                    }
                });
            } catch (error) {
                console.error('Initialization failed:', error);
            }
        }

        // Start everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', initialize);

        // Optimize performance for animations
        window.addEventListener('resize', () => {
            // Recalculate positions for network lines if needed
            const lines = document.querySelectorAll('.network-line');
            lines.forEach(line => {
                line.style.left = `${Math.random() * window.innerWidth}px`;
                line.style.top = `${Math.random() * window.innerHeight}px`;
            });
        });

        // Clean up intervals on page unload
        window.addEventListener('unload', () => {
            clearInterval(updateStats);
            clearInterval(updateTime);
            clearInterval(updateTimer);
            clearInterval(createBinaryRain);
            clearInterval(createNetworkLines);
        });
    </script>
</body>
</html>