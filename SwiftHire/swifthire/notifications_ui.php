<?php
$notif_btn_class = isset($notif_btn_class) ? $notif_btn_class : 'btn-outline-light';
$is_ringing = isset($ring_bell) && $ring_bell ? 'bell-ringing' : '';
?>
<style>
@keyframes ring {
    0% { transform: rotate(0); }
    10% { transform: rotate(15deg); }
    20% { transform: rotate(-10deg); }
    30% { transform: rotate(5deg); }
    40% { transform: rotate(-5deg); }
    50% { transform: rotate(0); }
    100% { transform: rotate(0); }
}
.bell-ringing {
    animation: ring 2s infinite ease-in-out;
    transform-origin: top center;
    display: inline-block;
}
</style>
<div class="dropdown">
    <button class="btn <?php echo $notif_btn_class; ?> rounded-pill px-3 position-relative border-0" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell <?php echo $is_ringing; ?>" id="notifBellIcon"></i>
        <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none; font-size:0.65rem;">
            0
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="notifDropdown" id="notifList" style="width: 300px; max-height: 400px; overflow-y: auto;">
        <li><div class="dropdown-item text-center text-muted py-2">Loading...</div></li>
    </ul>
</div>

<script>
function fetchNotifications() {
    fetch('fetch_notifications.php')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('notifBadge');
                const list = document.getElementById('notifList');
                const bellIcon = document.getElementById('notifBellIcon');
                
                if (data.notifications.length > 0) {
                    badge.style.display = 'inline-block';
                    badge.textContent = data.notifications.length;
                    
                    list.innerHTML = '';
                    data.notifications.forEach(n => {
                        let icon = n.type === 'message' ? 'fa-comment-dots' : 'fa-info-circle';
                        let color = n.type === 'message' ? 'text-primary' : 'text-success';
                        
                        let li = document.createElement('li');
                        li.innerHTML = `
                            <a class="dropdown-item py-2" href="#" onclick="handleNotifClick(event, ${n.id}, '${n.type}', ${n.related_id})">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="fas ${icon} ${color} mt-1"></i>
                                    <div>
                                        <p class="mb-0 text-wrap" style="font-size:0.85rem; line-height:1.2;">${n.message_body}</p>
                                        <small class="text-muted" style="font-size:0.7rem;">${new Date(n.created_at).toLocaleString()}</small>
                                    </div>
                                </div>
                            </a>
                        `;
                        list.appendChild(li);
                    });
                } else {
                    badge.style.display = 'none';
                    if (bellIcon) bellIcon.classList.remove('bell-ringing'); // Stop ringing if no unread
                    list.innerHTML = '<li><div class="dropdown-item text-center text-muted py-2">No new notifications</div></li>';
                }
            }
        });
}

function handleNotifClick(e, id, type, relatedId) {
    e.preventDefault();
    fetch('mark_notifications_read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id=' + id
    }).then(() => {
        let targetUrl = '';
        if (type === 'application_received') {
            targetUrl = 'owner_inbox.php#app-' + relatedId;
        } else if (type === 'application_accepted' || type === 'request') {
            targetUrl = 'user_dashboard.php#app-' + relatedId;
        } else if (type === 'message') {
            targetUrl = 'messages.php?application_id=' + relatedId;
        } else {
            fetchNotifications();
            return;
        }

        let iframe = document.querySelector('iframe[name="contentFrame"]');
        if (iframe) {
            // We are in mdi_main.php, so add nomdi=1
            if (targetUrl.indexOf('?') === -1) {
                targetUrl = targetUrl.replace('#', '?nomdi=1#');
            } else {
                targetUrl = targetUrl.replace('?', '?nomdi=1&');
            }
            iframe.src = targetUrl;
            
            // Re-fetch to update badge
            fetchNotifications();
        } else {
            window.location.href = targetUrl;
        }
    });
}

// Initial fetch and poll
fetchNotifications();
setInterval(fetchNotifications, 5000);
</script>
