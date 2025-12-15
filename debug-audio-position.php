<?php
/**
 * Debug Audio Position Tracking
 * Check if position is being saved correctly
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    die('Please login first');
}

// Get database connection
$db = Database::getInstance()->getConnection();

// Get recent audio history with positions
$stmt = $db->prepare("
    SELECT id, text, voice, position, created_at, updated_at
    FROM audio_history
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$stmt->execute([$userId]);
$audios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Audio Position</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #667eea;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #f9f9f9;
        }
        .position {
            font-weight: bold;
            color: #667eea;
        }
        .position.zero {
            color: #999;
        }
        .text-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .info-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2196F3;
        }
        .success-box {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #4CAF50;
        }
        .error-box {
            background: #ffebee;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #f44336;
        }
        button {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #5568d3;
        }
        .test-section {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <h1>🔍 Debug Audio Position Tracking</h1>
    
    <div class="info-box">
        <strong>User ID:</strong> <?= $userId ?><br>
        <strong>Total Audio Records:</strong> <?= count($audios) ?><br>
        <strong>Records with Position > 0:</strong> <?= count(array_filter($audios, fn($a) => $a['position'] > 0)) ?>
    </div>
    
    <?php if (count($audios) === 0): ?>
        <div class="error-box">
            ⚠️ No audio records found. Please create some audio first in the dashboard.
        </div>
    <?php else: ?>
        <div class="success-box">
            ✅ Found <?= count($audios) ?> audio records. Check the table below for position data.
        </div>
    <?php endif; ?>
    
    <div class="test-section">
        <h2>Test Position Update</h2>
        <p>Test if the API endpoint is working:</p>
        <button onclick="testUpdatePosition()">Test Update Position API</button>
        <div id="test-result" style="margin-top: 10px;"></div>
    </div>
    
    <h2>Recent Audio History (Last 10)</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Text Preview</th>
                <th>Voice</th>
                <th>Position (seconds)</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($audios as $audio): ?>
                <tr>
                    <td><?= $audio['id'] ?></td>
                    <td class="text-preview" title="<?= htmlspecialchars($audio['text']) ?>">
                        <?= htmlspecialchars(substr($audio['text'], 0, 50)) ?>...
                    </td>
                    <td><?= htmlspecialchars($audio['voice']) ?></td>
                    <td class="position <?= $audio['position'] == 0 ? 'zero' : '' ?>">
                        <?= $audio['position'] ?>s
                        <?php if ($audio['position'] > 0): ?>
                            (<?= gmdate("i:s", $audio['position']) ?>)
                        <?php endif; ?>
                    </td>
                    <td><?= date('Y-m-d H:i:s', strtotime($audio['created_at'])) ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($audio['updated_at'])) ?></td>
                    <td>
                        <button onclick="updatePosition(<?= $audio['id'] ?>, 30)">Set 30s</button>
                        <button onclick="updatePosition(<?= $audio['id'] ?>, 0)">Reset</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div style="margin-top: 20px;">
        <button onclick="location.reload()">🔄 Refresh Page</button>
        <button onclick="location.href='<?= BASE_URL ?>/dashboard'">← Back to Dashboard</button>
    </div>
    
    <script>
        const API_BASE = '<?= BASE_URL ?>/api';
        
        async function updatePosition(audioId, position) {
            try {
                const response = await fetch(`${API_BASE}/update_position.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: audioId,
                        position: position
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert(`✅ Position updated to ${position}s for audio ID ${audioId}`);
                    location.reload();
                } else {
                    alert(`❌ Failed: ${data.error}`);
                }
            } catch (error) {
                alert(`❌ Error: ${error.message}`);
            }
        }
        
        async function testUpdatePosition() {
            const resultDiv = document.getElementById('test-result');
            resultDiv.innerHTML = '<em>Testing...</em>';
            
            // Get first audio ID
            const firstAudioId = <?= count($audios) > 0 ? $audios[0]['id'] : 'null' ?>;
            
            if (!firstAudioId) {
                resultDiv.innerHTML = '<span style="color: red;">❌ No audio records to test with</span>';
                return;
            }
            
            try {
                const testPosition = Math.floor(Math.random() * 100) + 10; // Random 10-110
                
                const response = await fetch(`${API_BASE}/update_position.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id: firstAudioId,
                        position: testPosition
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `<span style="color: green;">✅ Success! Updated audio ID ${firstAudioId} to ${testPosition}s</span>`;
                    setTimeout(() => location.reload(), 1500);
                } else {
                    resultDiv.innerHTML = `<span style="color: red;">❌ Failed: ${data.error}</span>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<span style="color: red;">❌ Error: ${error.message}</span>`;
            }
        }
    </script>
</body>
</html>
