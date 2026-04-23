<div class="dropdown">
    <button class="btn btn-outline-light rounded-pill px-3 position-relative" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
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
        if (type === 'message' || type === 'application_accepted') {
            window.location.href = 'messages.php';
        } else {
            fetchNotifications();
        }
    });
}

// Initial fetch and poll
fetchNotifications();
setInterval(fetchNotifications, 5000);
</script>
