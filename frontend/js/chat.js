const userListContainer = document.getElementById('chat-room-list'); // 好友列表
const chatBox = document.getElementById('chat-box');
const messageInput = document.getElementById('message-input');
const sendBtn = document.getElementById('send-btn');

let currentRoomId = null;
let unreadCounts = {};
let myId = null;

// 初始化 Echo
window.Echo = new Echo({
    broadcaster: 'socket.io',
    host: 'http://127.0.0.1:6001'
});

// =====================
// 取得好友列表
// =====================
async function loadUsers() {
    if (!window.token) return;

    try {
        // 1️⃣ 取得好友列表
        const resUsers = await fetch(`${API_BASE}/chat/users`, {
            headers: { Authorization: `Bearer ${window.token}` }
        });
        const users = await resUsers.json();
        userListContainer.innerHTML = '<h6 class="text-center">好友列表</h6>';

        // 2️⃣ 取得未讀數量
        const resUnread = await fetch(`${API_BASE}/chat/unread-counts`, {
            headers: { Authorization: `Bearer ${window.token}` }
        });
        const unreadData = await resUnread.json();
        unreadCounts = unreadData; // 初始化未讀數量
        
        // 3️⃣ 渲染好友列表
        users.forEach(u => {
            const btn = document.createElement('button');
            btn.className = 'btn btn-outline-primary w-100 mb-1 text-start';
            btn.textContent = u.name;
            btn.dataset.userId = u.id;   // 先用 userId
            btn.dataset.roomId = '';      // 目前聊天室還沒建立

            const badge = document.createElement('span');
            badge.className = 'badge bg-danger ms-1';
            badge.style.display = 'none';
            btn.appendChild(badge);

            // 先顯示未讀數量
            const unread = unreadCounts[u.id] || 0;
            if (unread > 0) {
                badge.textContent = unread;
                badge.style.display = 'inline-block';
            }

            btn.onclick = async (e) => {
                e.preventDefault();
                userListContainer.querySelectorAll('button').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');

                try {
                    const resRoom = await fetch(`${API_BASE}/chat/room`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Authorization: `Bearer ${window.token}`
                        },
                        body: JSON.stringify({ user_id: u.id })
                    });
                    const data = await resRoom.json();
                    btn.dataset.roomId = data.room_id;

                    // 把 userId key 換成 roomId key
                    if (unreadCounts[u.id]) {
                        unreadCounts[data.room_id] = unreadCounts[u.id];
                        delete unreadCounts[u.id];
                    }

                    switchRoom(data.room_id);
                } catch (err) {
                    console.error('建立聊天室失敗', err);
                }
            };

            userListContainer.appendChild(btn);
        });

        // 4️⃣ 最後再一次更新徽章
        updateBadges();

    } catch (err) {
        console.error('載入好友列表或未讀數失敗', err);
    }
}

// =====================
// 顯示訊息
// =====================
function displayMessage(msg) {
    const div = document.createElement('div');

    if (!myId) {
        console.warn('myId 未定義');
        return;
    }

    const isMe = msg.user.id === myId;
    div.className = 'chat-message ' + (isMe ? 'me' : 'other');
    div.style.display = 'flex';
    div.style.flexDirection = 'column';
    div.style.alignItems = isMe ? 'flex-end' : 'flex-start';
    div.style.margin = '5px 0';

    // 訊息氣泡
    const bubble = document.createElement('div');
    bubble.style.backgroundColor = isMe ? '#d1e7dd' : '#f8d7da';
    bubble.style.padding = '8px 12px';
    bubble.style.borderRadius = '12px';
    bubble.style.whiteSpace = 'nowrap'; // ❗ 強制單行
    bubble.style.display = 'inline-block'; // ❗ 讓訊息依長度撐開
    bubble.style.overflowX = 'visible'; // ❗ 避免被截斷
    bubble.style.maxWidth = 'none';

    bubble.innerHTML = `<strong>${isMe ? '我' : msg.user.name}:</strong> ${msg.message}`;

    // 狀態（已讀 / 未讀）
    const status = document.createElement('div');
    status.style.fontSize = '0.75em';
    status.style.color = '#6c757d';
    status.style.marginTop = '2px';
    status.style.alignSelf = isMe ? 'flex-start' : 'flex-end';

    if (isMe) {
        status.innerText = msg.is_read ? '已讀' : '未讀';
    } else {
        status.innerText = '';
    }

    // 時間
    const time = document.createElement('div');
    time.style.fontSize = '0.7em';
    time.style.color = '#999';
    time.style.marginTop = '1px';
    time.style.alignSelf = isMe ? 'flex-start' : 'flex-end';

    let createdAt = null;
console.log(msg);
    if (msg.created_at) {
        // 把 "2025-08-15 06:23:58" 轉成 "2025-08-15T06:23:58"
        const formatted = msg.created_at.replace(" ", "T");

        createdAt = new Date(formatted);
        console.log(formatted,createdAt);
        // 防止無效日期
        if (isNaN(createdAt.getTime())) {
            createdAt = new Date(); // fallback 變成現在時間
        }
    } else {
        createdAt = new Date(); // 如果沒傳就顯示現在時間
    }

    time.innerText = createdAt.toLocaleString('zh-TW', {
        hour: '2-digit',
        minute: '2-digit'
    });

    div.appendChild(bubble);
    div.appendChild(status);
    div.appendChild(time);

    chatBox.appendChild(div);
    chatBox.scrollTop = chatBox.scrollHeight;

    // 如果是別人發的訊息，標記為已讀
    if (!isMe && msg.room_id === currentRoomId) {
        markAsRead(currentRoomId);
    }
}

// =====================
// 切換聊天室
// =====================
async function switchRoom(roomId) {
    if (!roomId) return;
    
    if (currentRoomId) window.Echo.leave(`chat.${currentRoomId}`);
    currentRoomId = roomId;
    chatBox.innerHTML = '';
    unreadCounts[roomId] = 1;
    updateBadges();

    // 加入聊天室
    window.Echo.join(`chat.${roomId}`)
        .listen('NewChatMessage', e => {
            displayMessage(e);

            // 非當前聊天室增加未讀
            if (currentRoomId !== roomId) {
                unreadCounts[roomId] = (unreadCounts[roomId] || 0) + 1;
                updateBadges();
            }
        });
        
    // 載入歷史訊息 
    try {
        const res = await fetch(`${API_BASE}/chat/messages/${roomId}`, {
            headers: { Authorization: `Bearer ${window.token}` }
        });
        const messages = await res.json();
        chatBox.innerHTML = '';
        messages.forEach(displayMessage);

        markAsRead(roomId);
    } catch (err) {
        console.error('載入歷史訊息失敗', err);
    }
}

// =====================
// 發送訊息
// =====================
async function sendMessage() {
    if (!currentRoomId) return;
    const msg = messageInput.value.trim();
    if (!msg) return;

    try {
        const res = await fetch(`${API_BASE}/chat/send`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', Authorization: `Bearer ${window.token}` },
            body: JSON.stringify({ room_id: currentRoomId, message: msg })
        });

        if (res.ok) {
            const newMsg = await res.json();
            displayMessage(newMsg);
            messageInput.value = '';
        }
    } catch (err) {
        console.error('發送訊息失敗', err);
    }
}

sendBtn.addEventListener('click', sendMessage);
messageInput.addEventListener('keypress', e => {
    if (e.key === 'Enter') {
        e.preventDefault();
        sendMessage();
    }
});

// =====================
// 更新未讀徽章
// =====================
function updateBadges() {
    userListContainer.querySelectorAll('button').forEach(btn => {
        const roomId = btn.dataset.roomId;
        const userId = btn.dataset.userId;
        const badge = btn.querySelector('span');

        let count = 0;

        // 優先用 roomId，如果沒有再用 userId
        if (roomId && unreadCounts[roomId] !== undefined) {
            count = unreadCounts[roomId];
        } else if (userId && unreadCounts[userId] !== undefined) {
            count = unreadCounts[userId];
        }

        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
        }
    });
}

// =====================
// 標記已讀
// =====================
async function markAsRead(roomId) {
    try {
        await fetch(`${API_BASE}/chat/read/${roomId}`, {
            method: 'POST',
            headers: { Authorization: `Bearer ${window.token}` }
        });
        unreadCounts[roomId] = 0;
        updateBadges();
    } catch (err) {
        console.error('標記已讀失敗', err);
    }
}

// =====================
// 初始化
// =====================
window.addEventListener('user-logged-in', () => {
    myId = window.authUserId; // 必須在登入後有 authUserId
    loadUsers();
});
