<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$current_user_id = $_SESSION['user_id'];
$other_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';

// Create messages table if it doesn't exist
$create_table_query = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read INT DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX(sender_id),
    INDEX(receiver_id),
    INDEX(created_at)
)";
$conn->query($create_table_query);

// Get list of conversations
$conversations_query = "
    SELECT DISTINCT 
        CASE 
            WHEN sender_id = ? THEN receiver_id 
            ELSE sender_id 
        END as other_user_id,
        u.firstname,
        u.lastname,
        u.email,
        (SELECT message FROM messages WHERE 
            (sender_id = ? AND receiver_id = u.id) OR 
            (sender_id = u.id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message,
        (SELECT created_at FROM messages WHERE 
            (sender_id = ? AND receiver_id = u.id) OR 
            (sender_id = u.id AND receiver_id = ?)
         ORDER BY created_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0) as unread_count
    FROM messages m
    JOIN users u ON (u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END)
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY last_message_time DESC
";

$stmt = $conn->prepare($conversations_query);
$stmt->bind_param("iiiiiiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
$stmt->execute();
$conversations_result = $stmt->get_result();
$conversations = [];
while ($row = $conversations_result->fetch_assoc()) {
    $conversations[] = $row;
}
$stmt->close();

// If user_id is provided, get their details and load messages
$other_user = null;
$messages = [];

if ($other_user_id) {
    $user_stmt = $conn->prepare("SELECT id, firstname, lastname, email FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $other_user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $other_user = $user_result->fetch_assoc();
    $user_stmt->close();

    // Get messages between current user and other user
    $msg_stmt = $conn->prepare("
        SELECT * FROM messages 
        WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC
    ");
    $msg_stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
    $msg_stmt->execute();
    $msg_result = $msg_stmt->get_result();
    while ($row = $msg_result->fetch_assoc()) {
        $messages[] = $row;
    }
    $msg_stmt->close();

    // Mark messages as read
    $read_stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ? AND is_read = 0");
    $read_stmt->bind_param("ii", $other_user_id, $current_user_id);
    $read_stmt->execute();
    $read_stmt->close();
}

// Handle new message submission
$response = ['success' => false];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $message_text = trim($_POST['message']);
    $receiver_id = intval($_POST['receiver_id']);

    if (!empty($message_text) && $receiver_id > 0) {
        $insert_stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        $insert_stmt->bind_param("iis", $current_user_id, $receiver_id, $message_text);
        
        if ($insert_stmt->execute()) {
            $response['success'] = true;
            $response['message'] = htmlspecialchars($message_text);
            $response['time'] = date('H:i');
        }
        $insert_stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e1b4b 100%);
            color: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }

        .messages-container {
            display: flex;
            height: 100vh;
            background: var(--dark-bg);
        }

        /* Sidebar */
        .sidebar {
            width: 350px;
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(16px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .conversation-item {
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s;
            border: 1px solid transparent;
            position: relative;
        }

        .conversation-item:hover {
            background: rgba(99, 102, 241, 0.1);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .conversation-item.active {
            background: rgba(99, 102, 241, 0.2);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .conv-name {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }

        .conv-preview {
            font-size: 0.85rem;
            color: #94a3b8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #ef4444;
            color: white;
            padding: 4px 8px;
            border-radius: 50px;
            font-size: 0.7rem;
            font-weight: 700;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
        }

        .empty-state {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #64748b;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Chat Header */
        .chat-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-user-info h3 {
            font-weight: 700;
            margin-bottom: 0.2rem;
        }

        .chat-user-info small {
            color: #94a3b8;
        }

        .btn-close-chat {
            background: transparent;
            border: none;
            color: #f8fafc;
            cursor: pointer;
            font-size: 1.5rem;
        }

        /* Messages */
        .messages-box {
            flex: 1;
            overflow-y: auto;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .message {
            display: flex;
            margin-bottom: 1rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .message.own {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 60%;
            padding: 0.8rem 1.2rem;
            border-radius: 16px;
            word-wrap: break-word;
        }

        .message.own .message-content {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.other .message-content {
            background: rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.3rem;
        }

        /* Input Area */
        .chat-input-area {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 0.75rem;
        }

        .chat-input-area input {
            flex: 1;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            outline: none;
            font-family: 'Outfit', sans-serif;
        }

        .chat-input-area input:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(99, 102, 241, 0.5);
        }

        .chat-input-area input::placeholder {
            color: #64748b;
        }

        .btn-send {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-send:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.4);
        }

        .btn-send:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .sidebar { width: 100%; max-height: 40%; border-right: none; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
            .messages-container { flex-direction: column; }
            .message-content { max-width: 85%; }
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99, 102, 241, 0.3); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99, 102, 241, 0.5); }
    </style>
</head>
<body>

<div class="messages-container">
    <!-- Sidebar with conversations -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2><i class="fas fa-comments me-2"></i>Messages</h2>
            <a href="<?php echo $hide_nav ? 'job_board.php?nomdi=1' : 'job_board.php'; ?>" class="btn btn-close-chat" title="Back">
                <i class="fas fa-times"></i>
            </a>
        </div>
        
        <div class="conversations-list">
            <?php if(empty($conversations)): ?>
                <div style="padding: 2rem 1rem; text-align: center; color: #64748b;">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                    <p>No conversations yet</p>
                </div>
            <?php else: ?>
                <?php foreach($conversations as $conv): ?>
                    <a href="messages.php?user_id=<?php echo $conv['other_user_id']; ?><?php echo $hide_nav ? '&nomdi=1' : ''; ?>" style="text-decoration: none; color: inherit;">
                        <div class="conversation-item <?php echo ($other_user_id == $conv['other_user_id']) ? 'active' : ''; ?>">
                            <div class="conv-name"><?php echo htmlspecialchars($conv['firstname'] . ' ' . $conv['lastname']); ?></div>
                            <div class="conv-preview"><?php echo htmlspecialchars(substr($conv['last_message'], 0, 50)); ?></div>
                            <?php if($conv['unread_count'] > 0): ?>
                                <div class="unread-badge"><?php echo $conv['unread_count']; ?></div>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area">
        <?php if($other_user): ?>
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-user-info">
                    <h3><?php echo htmlspecialchars($other_user['firstname'] . ' ' . $other_user['lastname']); ?></h3>
                    <small><i class="fas fa-circle text-success me-2" style="font-size: 0.5rem;"></i>Active</small>
                </div>
                <a href="<?php echo $hide_nav ? 'job_board.php?nomdi=1' : 'job_board.php'; ?>" class="btn btn-close-chat">
                    <i class="fas fa-times"></i>
                </a>
            </div>

            <!-- Messages -->
            <div class="messages-box" id="messagesBox">
                <?php foreach($messages as $msg): ?>
                    <div class="message <?php echo ($msg['sender_id'] == $current_user_id) ? 'own' : 'other'; ?>">
                        <div>
                            <div class="message-content">
                                <?php echo htmlspecialchars($msg['message']); ?>
                            </div>
                            <div class="message-time">
                                <?php echo date('H:i', strtotime($msg['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Input Area -->
            <div class="chat-input-area">
                <input type="text" id="messageInput" placeholder="Type your message..." />
                <button class="btn-send" id="sendBtn"><i class="fas fa-paper-plane me-2"></i>Send</button>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>Select a conversation</h3>
                <p>Choose someone from the list to start messaging</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    const messagesBox = document.getElementById('messagesBox');
    const messageInput = document.getElementById('messageInput');
    const sendBtn = document.getElementById('sendBtn');

    function scrollToBottom() {
        if(messagesBox) {
            messagesBox.scrollTop = messagesBox.scrollHeight;
        }
    }

    function sendMessage() {
        const message = messageInput.value.trim();
        const receiverId = <?php echo $other_user_id ?? 'null'; ?>;

        if (!message || !receiverId) return;

        const formData = new FormData();
        formData.append('message', message);
        formData.append('receiver_id', receiverId);

        fetch('messages.php<?php echo $hide_nav ? "?nomdi=1" : ""; ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Add message to chat
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message own';
                messageDiv.innerHTML = `
                    <div>
                        <div class="message-content">${data.message}</div>
                        <div class="message-time">${data.time}</div>
                    </div>
                `;
                messagesBox.appendChild(messageDiv);
                messageInput.value = '';
                scrollToBottom();
            }
        });
    }

    if(sendBtn) {
        sendBtn.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', (e) => {
            if(e.key === 'Enter') sendMessage();
        });
    }

    window.addEventListener('load', scrollToBottom);
</script>

</body>
</html>
