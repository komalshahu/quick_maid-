<?php
session_start();
require 'db.php';
require 'schema_bootstrap.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header("Location: login.php");
    exit;
}

$owner_id = (int)$_SESSION['user_id'];
$selected_job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$selected_maid_id = isset($_GET['maid_id']) ? (int)$_GET['maid_id'] : 0;

$threads = [];
$threads_stmt = $conn->prepare("
    SELECT
        m.job_id,
        v.job_title,
        CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END AS maid_id,
        u.firstname,
        u.lastname,
        u.user_type,
        MAX(m.id) AS last_message_id,
        MAX(m.`timestamp`) AS last_message_time,
        SUM(CASE WHEN m.receiver_id = ? AND m.is_read = 0 THEN 1 ELSE 0 END) AS unread_count
    FROM messages m
    JOIN vacancies v ON m.job_id = v.id
    JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
    WHERE v.user_id = ?
      AND (m.sender_id = ? OR m.receiver_id = ?)
    GROUP BY m.job_id, maid_id, v.job_title, u.firstname, u.lastname, u.user_type
    ORDER BY last_message_time DESC, last_message_id DESC
");
$threads_stmt->bind_param("iiiiii", $owner_id, $owner_id, $owner_id, $owner_id, $owner_id, $owner_id);
$threads_stmt->execute();
$threads_res = $threads_stmt->get_result();
while ($row = $threads_res->fetch_assoc()) {
    $threads[] = $row;
}
$threads_stmt->close();

$selected_thread = null;
foreach ($threads as $thread) {
    if ($selected_job_id > 0 && $selected_maid_id > 0
        && (int)$thread['job_id'] === $selected_job_id
        && (int)$thread['maid_id'] === $selected_maid_id) {
        $selected_thread = $thread;
        break;
    }
}

if (!$selected_thread && !empty($threads)) {
    $selected_thread = $threads[0];
    $selected_job_id = (int)$selected_thread['job_id'];
    $selected_maid_id = (int)$selected_thread['maid_id'];
}

if (!$selected_thread && $selected_job_id > 0 && $selected_maid_id > 0) {
    $job_stmt = $conn->prepare("
        SELECT v.id AS job_id, v.job_title, u.firstname, u.lastname, u.user_type
        FROM vacancies v
        JOIN users u ON u.id = ?
        WHERE v.id = ? AND v.user_id = ?
        LIMIT 1
    ");
    $job_stmt->bind_param("iii", $selected_maid_id, $selected_job_id, $owner_id);
    $job_stmt->execute();
    $job_res = $job_stmt->get_result();
    $row = $job_res ? $job_res->fetch_assoc() : null;
    $job_stmt->close();

    if ($row) {
        $selected_thread = [
            'job_id' => $selected_job_id,
            'maid_id' => $selected_maid_id,
            'job_title' => $row['job_title'],
            'firstname' => $row['firstname'],
            'lastname' => $row['lastname'],
            'user_type' => $row['user_type'],
            'unread_count' => 0,
        ];
    }
}

$selected_name = $selected_thread
    ? trim((string)$selected_thread['firstname'] . ' ' . (string)$selected_thread['lastname'])
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Dashboard - QuickMaid</title>
    <link rel="icon" href="images/logo.png" type="image/png">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ink: #0f172a;
            --paper: #f8fafc;
            --line: #e2e8f0;
            --accent: #4f46e5;
            --accent-soft: #eef2ff;
            --success: #10b981;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(180deg, #e2e8f0 0%, #f8fafc 220px);
            color: var(--ink);
            min-height: 100vh;
        }
        .navbar-custom { background: rgba(15, 23, 42, 0.96); }
        .workspace {
            display: grid;
            grid-template-columns: 360px 1fr;
            gap: 24px;
            padding: 28px 0 32px;
        }
        .panel {
            background: rgba(255,255,255,0.92);
            border: 1px solid rgba(148, 163, 184, 0.22);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
            overflow: hidden;
            backdrop-filter: blur(12px);
        }
        .panel-header {
            padding: 20px 22px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        }
        .thread-list {
            max-height: calc(100vh - 210px);
            overflow-y: auto;
            padding: 12px;
        }
        .thread-link {
            display: block;
            padding: 14px 16px;
            border-radius: 18px;
            text-decoration: none;
            color: inherit;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            margin-bottom: 10px;
            background: #fff;
        }
        .thread-link:hover,
        .thread-link.active {
            border-color: rgba(79, 70, 229, 0.18);
            background: var(--accent-soft);
            transform: translateY(-1px);
        }
        .thread-meta {
            font-size: 0.85rem;
            color: #64748b;
        }
        .thread-unread {
            min-width: 26px;
            height: 26px;
            padding: 0 8px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: var(--success);
            color: #fff;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .chat-shell {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 160px);
        }
        .chat-header {
            padding: 22px 24px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #fff;
        }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background:
                radial-gradient(circle at top right, rgba(79, 70, 229, 0.08), transparent 30%),
                linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        }
        .message-row {
            display: flex;
            margin-bottom: 14px;
        }
        .message-row.sent { justify-content: flex-end; }
        .bubble-wrap { max-width: 74%; }
        .bubble {
            border-radius: 18px;
            padding: 10px 14px 12px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            word-wrap: break-word;
            white-space: pre-wrap;
        }
        .bubble.sent {
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            color: #fff;
            border-bottom-right-radius: 6px;
        }
        .bubble.received {
            background: #fff;
            color: var(--ink);
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 6px;
        }
        .bubble-time {
            display: block;
            text-align: right;
            margin-top: 8px;
            font-size: 0.72rem;
            opacity: 0.72;
        }
        .chat-composer {
            padding: 18px 20px;
            border-top: 1px solid var(--line);
            background: #fff;
        }
        .empty-state {
            min-height: calc(100vh - 160px);
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 32px;
            color: #64748b;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
        }
        @media (max-width: 992px) {
            .workspace { grid-template-columns: 1fr; }
            .thread-list { max-height: 360px; }
            .chat-shell, .empty-state { min-height: 560px; }
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark navbar-custom">
    <div class="container-fluid px-4">
        <a class="navbar-brand fw-bold" href="owner_dashboard.php"><i class="fas fa-comments me-2"></i>QuickMaid Chat</a>
        <div class="d-flex gap-2 align-items-center">
            <?php include 'notifications_ui.php'; ?>
            <a href="owner_inbox.php" class="btn btn-outline-light rounded-pill px-4">Inbox</a>
            <a href="owner_dashboard.php" class="btn btn-light text-dark rounded-pill px-4">Dashboard</a>
        </div>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="workspace">
        <aside class="panel">
            <div class="panel-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold fs-5">Master Chat Dashboard</div>
                        <div class="text-muted small">Applicant conversations across your jobs</div>
                    </div>
                    <span class="badge rounded-pill text-bg-dark"><?php echo count($threads); ?></span>
                </div>
            </div>
            <div class="thread-list">
                <?php if (empty($threads)): ?>
                    <div class="text-center py-5 px-3 text-muted">
                        <i class="fas fa-inbox fa-2x mb-3"></i>
                        <div class="fw-semibold mb-1">No inquiries yet</div>
                        <div class="small">Maid messages will appear here as soon as a conversation starts.</div>
                    </div>
                <?php else: ?>
                    <?php foreach ($threads as $thread): ?>
                        <?php
                            $is_active = $selected_thread
                                && (int)$thread['job_id'] === (int)$selected_thread['job_id']
                                && (int)$thread['maid_id'] === (int)$selected_thread['maid_id'];
                            $thread_name = trim((string)$thread['firstname'] . ' ' . (string)$thread['lastname']);
                        ?>
                        <a
                            href="owner_chat_dashboard.php?job_id=<?php echo (int)$thread['job_id']; ?>&maid_id=<?php echo (int)$thread['maid_id']; ?>"
                            class="thread-link <?php echo $is_active ? 'active' : ''; ?>"
                        >
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($thread_name); ?></div>
                                    <div class="thread-meta">
                                        <i class="fas fa-briefcase me-1"></i><?php echo htmlspecialchars($thread['job_title']); ?>
                                    </div>
                                    <div class="thread-meta mt-1">
                                        <i class="fas fa-clock me-1"></i><?php echo date('M d, h:i A', strtotime($thread['last_message_time'])); ?>
                                    </div>
                                </div>
                                <?php if ((int)$thread['unread_count'] > 0): ?>
                                    <span class="thread-unread"><?php echo (int)$thread['unread_count']; ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </aside>

        <section class="panel">
            <?php if ($selected_thread): ?>
                <div class="chat-shell">
                    <div class="chat-header">
                        <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                            <div>
                                <div class="fw-bold fs-4"><?php echo htmlspecialchars($selected_name); ?></div>
                                <div class="small opacity-75">
                                    Job: <?php echo htmlspecialchars($selected_thread['job_title']); ?> | Thread ID:
                                    <?php echo htmlspecialchars(implode('-', [(int)$selected_thread['job_id'], min($owner_id, $selected_maid_id), max($owner_id, $selected_maid_id)])); ?>
                                </div>
                            </div>
                            <a href="owner_inbox.php" class="btn btn-light btn-sm rounded-pill px-3">
                                <i class="fas fa-users me-1"></i>Applicant Inbox
                            </a>
                        </div>
                    </div>

                    <div id="chatMessages" class="chat-messages"></div>

                    <div class="chat-composer">
                        <div class="input-group input-group-lg">
                            <input id="chatInput" type="text" class="form-control rounded-start-pill" placeholder="Reply to <?php echo htmlspecialchars($selected_name); ?>...">
                            <button id="chatSendBtn" class="btn btn-primary rounded-end-pill px-4" type="button">
                                <i class="fas fa-paper-plane me-1"></i>Send
                            </button>
                        </div>
                        <div id="chatStatus" class="small text-muted mt-2" style="min-height:18px;"></div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div>
                        <i class="fas fa-comments fa-3x mb-3"></i>
                        <h3 class="fw-bold text-dark">Select a conversation</h3>
                        <p class="mb-0">Your maid inquiries will open here with live unread tracking.</p>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php if ($selected_thread): ?>
<script>
const CURRENT_USER_ID = <?php echo $owner_id; ?>;
const OTHER_USER_ID = <?php echo $selected_maid_id; ?>;
const JOB_ID = <?php echo $selected_job_id; ?>;
let sinceId = 0;
let pollRef = null;

const chatMessagesEl = document.getElementById('chatMessages');
const chatInputEl = document.getElementById('chatInput');
const chatSendBtnEl = document.getElementById('chatSendBtn');
const chatStatusEl = document.getElementById('chatStatus');

function setStatus(text) {
    chatStatusEl.textContent = text || '';
}

function scrollBottom() {
    chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
}

function renderMessage(msg) {
    const isMine = parseInt(msg.sender_id, 10) === CURRENT_USER_ID;
    const row = document.createElement('div');
    row.className = 'message-row ' + (isMine ? 'sent' : 'received');

    const wrap = document.createElement('div');
    wrap.className = 'bubble-wrap';

    const bubble = document.createElement('div');
    bubble.className = 'bubble ' + (isMine ? 'sent' : 'received');
    bubble.textContent = msg.message_text || '';

    const time = document.createElement('span');
    time.className = 'bubble-time';
    const ts = msg.timestamp ? new Date(msg.timestamp.replace(' ', 'T')) : null;
    time.textContent = ts && !isNaN(ts.getTime())
        ? ts.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})
        : '';

    bubble.appendChild(time);
    wrap.appendChild(bubble);
    row.appendChild(wrap);
    chatMessagesEl.appendChild(row);
}

async function fetchMessages() {
    try {
        const url = new URL('fetch_messages.php', window.location.href);
        url.searchParams.set('job_id', JOB_ID);
        url.searchParams.set('other_user_id', OTHER_USER_ID);
        if (sinceId > 0) {
            url.searchParams.set('since_id', sinceId);
        }

        const res = await fetch(url.toString(), { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) {
            setStatus(data.error || 'Unable to load messages');
            return;
        }

        if (Array.isArray(data.messages) && data.messages.length) {
            data.messages.forEach((msg) => {
                renderMessage(msg);
                sinceId = Math.max(sinceId, parseInt(msg.id, 10) || sinceId);
            });
            scrollBottom();
        }
    } catch (error) {
        setStatus('Network error while loading messages');
    }
}

async function sendMessage() {
    const text = (chatInputEl.value || '').trim();
    if (!text) {
        return;
    }

    chatSendBtnEl.disabled = true;
    setStatus('Sending...');
    try {
        const form = new FormData();
        form.append('receiver_id', OTHER_USER_ID);
        form.append('job_id', JOB_ID);
        form.append('message_text', text);

        const res = await fetch('send_message.php', {
            method: 'POST',
            body: form,
            credentials: 'same-origin'
        });
        const data = await res.json();

        if (data.success && data.message) {
            chatInputEl.value = '';
            setStatus('');
            renderMessage(data.message);
            sinceId = Math.max(sinceId, parseInt(data.message.id, 10) || sinceId);
            scrollBottom();
        } else {
            setStatus(data.error || 'Failed to send message');
        }
    } catch (error) {
        setStatus('Network error while sending');
    } finally {
        chatSendBtnEl.disabled = false;
    }
}

chatSendBtnEl.addEventListener('click', sendMessage);
chatInputEl.addEventListener('keypress', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});

window.addEventListener('load', async () => {
    await fetchMessages();
    scrollBottom();
    chatInputEl.focus();
    pollRef = setInterval(fetchMessages, 2500);
});

window.addEventListener('beforeunload', () => {
    if (pollRef) {
        clearInterval(pollRef);
    }
});
</script>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
