<?php
session_start();
require 'db.php';
require 'schema_bootstrap.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?tab=login");
    exit;
}

$current_user_id = (int)$_SESSION['user_id'];
$owner_id = isset($_GET['owner_id']) ? (int)$_GET['owner_id'] : 0;
$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;
$hide_nav = isset($_GET['nomdi']) && $_GET['nomdi'] == '1';

if ($job_id <= 0) {
    http_response_code(400);
    echo "Invalid chat request.";
    exit;
}

$has_user_id = false;
$col_check = $conn->query("SHOW COLUMNS FROM vacancies LIKE 'user_id'");
if ($col_check && $col_check->num_rows > 0) {
    $has_user_id = true;
}

if ($has_user_id) {
    $job_stmt = $conn->prepare("
        SELECT v.id, v.job_title, v.user_id AS owner_user_id, u.firstname, u.lastname
        FROM vacancies v
        LEFT JOIN users u ON v.user_id = u.id
        WHERE v.id = ?
        LIMIT 1
    ");
} else {
    $job_stmt = $conn->prepare("
        SELECT v.id, v.job_title, NULL AS owner_user_id, NULL AS firstname, NULL AS lastname
        FROM vacancies v
        WHERE v.id = ?
        LIMIT 1
    ");
}
$job_stmt->bind_param("i", $job_id);
$job_stmt->execute();
$job_res = $job_stmt->get_result();
$job = $job_res ? $job_res->fetch_assoc() : null;
$job_stmt->close();

$owner_available = $job && (int)$job['owner_user_id'] > 0;
if ($owner_available) {
    // Always trust DB owner mapping for the selected job.
    $owner_id = (int)$job['owner_user_id'];
}

$owner_name = trim((string)($job['firstname'] ?? '') . ' ' . (string)($job['lastname'] ?? ''));
if (!$owner_available || $owner_name === '') {
    $owner_name = 'Owner';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat with Owner - QuickMaid</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Outfit', sans-serif; background: #f8fafc; }
        .chat-wrap { max-width: 900px; margin: 24px auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; overflow: hidden; }
        .chat-header { background: #0f172a; color: #fff; padding: 14px 18px; }
        .chat-messages { height: 460px; overflow-y: auto; background: #f8fafc; padding: 16px; }
        .chat-bubble { max-width: 72%; padding: 10px 14px; border-radius: 14px; white-space: pre-wrap; word-wrap: break-word; }
        .chat-bubble.me { background: #4f46e5; color: #fff; border-bottom-right-radius: 6px; }
        .chat-bubble.them { background: #e2e8f0; color: #0f172a; border-bottom-left-radius: 6px; }
        .chat-time { font-size: 0.75rem; color: #64748b; margin-top: 4px; }
    </style>
</head>
<body>
<div class="container">
    <div class="chat-wrap shadow-sm">
        <div class="chat-header d-flex align-items-center justify-content-between">
            <div>
                <div class="fw-bold"><i class="fas fa-comments me-2"></i>Chat with <?php echo htmlspecialchars($owner_name); ?></div>
                <div class="small opacity-75">Job: <?php echo htmlspecialchars($job['job_title']); ?> (ID: <?php echo (int)$job_id; ?>)</div>
            </div>
            <a class="btn btn-sm btn-light" href="maid_vacancies.php<?php echo $hide_nav ? '?nomdi=1' : ''; ?>">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>

        <?php if (!$owner_available): ?>
            <div class="alert alert-warning m-3 mb-0">
                This job post does not have a linked owner account yet, so chat is not available right now.
            </div>
        <?php endif; ?>
        <div id="chatMessages" class="chat-messages"></div>
        <div class="p-3 border-top">
            <div class="d-flex gap-2">
                <input id="chatInput" type="text" class="form-control rounded-pill" placeholder="Type your message..." autocomplete="off" <?php echo !$owner_available ? 'disabled' : ''; ?>>
                <button id="chatSendBtn" class="btn btn-primary rounded-pill px-4" type="button" <?php echo !$owner_available ? 'disabled' : ''; ?>>
                    <i class="fas fa-paper-plane me-1"></i>Send
                </button>
            </div>
            <div id="chatStatus" class="small text-muted mt-2" style="min-height:18px;"></div>
        </div>
    </div>
</div>

<script>
const CURRENT_USER_ID = <?php echo $current_user_id; ?>;
const OWNER_ID = <?php echo (int)$owner_id; ?>;
const JOB_ID = <?php echo $job_id; ?>;
const OWNER_AVAILABLE = <?php echo $owner_available ? 'true' : 'false'; ?>;
let sinceId = 0;
let poll = null;

const chatMessagesEl = document.getElementById('chatMessages');
const chatInputEl = document.getElementById('chatInput');
const chatSendBtnEl = document.getElementById('chatSendBtn');
const chatStatusEl = document.getElementById('chatStatus');

function setStatus(text) { chatStatusEl.textContent = text || ''; }
function scrollBottom() { chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight; }

function renderMessage(msg) {
    const mine = parseInt(msg.sender_id, 10) === CURRENT_USER_ID;
    const row = document.createElement('div');
    row.className = 'd-flex mb-2 ' + (mine ? 'justify-content-end' : 'justify-content-start');

    const bubbleWrap = document.createElement('div');
    const bubble = document.createElement('div');
    bubble.className = 'chat-bubble ' + (mine ? 'me' : 'them');
    bubble.textContent = msg.message_text || '';
    bubbleWrap.appendChild(bubble);

    const time = document.createElement('div');
    time.className = 'chat-time';
    const ts = msg.timestamp ? new Date(msg.timestamp.replace(' ', 'T')) : null;
    time.textContent = ts && !isNaN(ts.getTime()) ? ts.toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'}) : '';
    bubbleWrap.appendChild(time);

    row.appendChild(bubbleWrap);
    chatMessagesEl.appendChild(row);
}

async function fetchMessages() {
    if (!OWNER_AVAILABLE) return;
    try {
        const u = new URL('fetch_messages.php', window.location.href);
        u.searchParams.set('owner_id', OWNER_ID);
        u.searchParams.set('job_id', JOB_ID);
        if (sinceId > 0) u.searchParams.set('since_id', sinceId);

        const res = await fetch(u.toString(), { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) {
            setStatus(data.error || 'Unable to load messages');
            return;
        }

        if (Array.isArray(data.messages) && data.messages.length) {
            data.messages.forEach((m) => {
                renderMessage(m);
                sinceId = Math.max(sinceId, parseInt(m.id, 10) || sinceId);
            });
            scrollBottom();
        }
    } catch (e) {
        setStatus('Network error while loading messages');
    }
}

async function sendMessage() {
    if (!OWNER_AVAILABLE) return;
    const text = (chatInputEl.value || '').trim();
    if (!text) return;

    chatSendBtnEl.disabled = true;
    setStatus('Sending...');
    try {
        const form = new FormData();
        form.append('receiver_id', OWNER_ID);
        form.append('job_id', JOB_ID);
        form.append('message_text', text);

        const res = await fetch('send_message.php', { method: 'POST', body: form, credentials: 'same-origin' });
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
    } catch (e) {
        setStatus('Network error while sending');
    } finally {
        chatSendBtnEl.disabled = false;
    }
}

chatSendBtnEl.addEventListener('click', sendMessage);
chatInputEl.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') sendMessage();
});

window.addEventListener('load', async () => {
    if (!OWNER_AVAILABLE) {
        setStatus('Chat unavailable: owner account not linked to this job post.');
        return;
    }
    await fetchMessages();
    chatInputEl.focus();
    poll = setInterval(fetchMessages, 2000);
});
window.addEventListener('beforeunload', () => {
    if (poll) clearInterval(poll);
});
</script>
</body>
</html>

