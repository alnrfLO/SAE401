<?php
class Messages extends Dashboard
{
    public function content()
    {
        $user = $this->data['profileUser'] ?? [];
        if (!$user) {
            return '<div style="text-align:center;padding:200px 20px;"><h2>User not found.</h2></div>';
        }

        $initials = strtoupper(substr($user['username'] ?? '??', 0, 2));
        $avatar = !empty($user['avatar'])
            ? '<img src="' . htmlspecialchars($user['avatar'], ENT_QUOTES) . '" alt="Avatar" class="sidebar-avatar-img">'
            : '<div class="sidebar-avatar-placeholder">' . $initials . '</div>';

        $currentUserId = $user['id'];

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <link rel="stylesheet" href="public/css/messages.css">
        <div class="dash-layout">

            ' . $this->sidebar($user, $avatar, 'messages') . '

            <div class="dash-main dash-main--messages">
                <div class="msg-layout">

                    <!-- ── SIDEBAR CONVERSATIONS ── -->
                    <div class="msg-sidebar" id="msgSidebar">
                        <div class="msg-sidebar-header">
                            <h2 class="msg-sidebar-title">Messages</h2>
                            <div class="msg-header-actions">
                                <button class="msg-icon-btn" id="btnNewDirect" title="New message">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M1.5 8.67v8.58a3 3 0 003 3h15a3 3 0 003-3V8.67l-8.928 5.493a3 3 0 01-3.144 0L1.5 8.67z"/><path d="M22.5 6.908V6.75a3 3 0 00-3-3h-15a3 3 0 00-3 3v.158l9.714 5.978a1.5 1.5 0 001.572 0L22.5 6.908z"/></svg>
                                </button>
                                <button class="msg-icon-btn" id="btnNewGroup" title="New group">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M4.5 6.375a4.125 4.125 0 118.25 0 4.125 4.125 0 01-8.25 0zM14.25 8.625a3.375 3.375 0 116.75 0 3.375 3.375 0 01-6.75 0zM1.5 19.125a7.125 7.125 0 0114.25 0v.003l-.001.119a.75.75 0 01-.363.63 13.067 13.067 0 01-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 01-.364-.63l-.001-.122zM17.25 19.128l-.001.144a2.25 2.25 0 01-.233.96 10.088 10.088 0 005.06-1.01.75.75 0 00.42-.643 4.875 4.875 0 00-6.957-4.611 8.586 8.586 0 011.71 5.157v.003z"/></svg>
                                </button>
                            </div>
                        </div>
                        <div class="msg-search-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="15" height="15"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd"/></svg>
                            <input type="text" placeholder="Search…" class="msg-search" id="msgSearch">
                        </div>
                        <div class="msg-conv-list" id="msgConvList">
                            <div class="msg-loading">
                                <div class="msg-spinner"></div>
                            </div>
                        </div>
                    </div>

                    <!-- ── ZONE CHAT ── -->
                    <div class="msg-chat" id="msgChat">
                        <div class="msg-chat-empty" id="msgChatEmpty">
                            <div class="msg-chat-empty-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="40" height="40"><path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z" clip-rule="evenodd"/></svg>
                            </div>
                            <p class="msg-chat-empty-title">Select a conversation</p>
                            <p class="msg-chat-empty-hint">Choose a contact or start a new conversation</p>
                        </div>

                        <!-- Header du chat (caché jusqu\'à ouverture) -->
                        <div class="msg-chat-header" id="msgChatHeader" style="display:none">
                            <button class="msg-back-btn" id="msgBackBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="20" height="20"><path fill-rule="evenodd" d="M11.03 3.97a.75.75 0 010 1.06l-6.22 6.22H21a.75.75 0 010 1.5H4.81l6.22 6.22a.75.75 0 11-1.06 1.06l-7.5-7.5a.75.75 0 010-1.06l7.5-7.5a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                            </button>
                            <div class="msg-chat-header-avatar" id="chatHeaderAvatar"></div>
                            <div class="msg-chat-header-info">
                                <div class="msg-chat-header-name" id="chatHeaderName"></div>
                                <div class="msg-chat-header-sub" id="chatHeaderSub"></div>
                            </div>
                            <button class="msg-icon-btn" id="btnLeaveConv" title="Leave conversation" style="margin-left:auto">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm5.03 4.72a.75.75 0 010 1.06l-1.72 1.72h10.94a.75.75 0 010 1.5H10.81l1.72 1.72a.75.75 0 11-1.06 1.06l-3-3a.75.75 0 010-1.06l3-3a.75.75 0 011.06 0z" clip-rule="evenodd"/></svg>
                            </button>
                        </div>

                        <!-- Messages -->
                        <div class="msg-messages" id="msgMessages"></div>

                        <!-- Input (caché jusqu\'à ouverture) -->
                        <div class="msg-input-wrap" id="msgInputWrap" style="display:none">
                            <textarea class="msg-input" id="msgInput" placeholder="Write a message…" rows="1"></textarea>
                            <button class="msg-send-btn" id="msgSendBtn">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="18" height="18"><path d="M3.478 2.405a.75.75 0 00-.926.94l2.432 7.905H13.5a.75.75 0 010 1.5H4.984l-2.432 7.905a.75.75 0 00.926.94 60.519 60.519 0 0018.445-8.986.75.75 0 000-1.218A60.517 60.517 0 003.478 2.405z"/></svg>
                            </button>
                        </div>
                    </div>

                </div><!-- /.msg-layout -->
            </div><!-- /.dash-main -->
        </div><!-- /.dash-layout -->

        <!-- ── MODAL : Nouveau message direct ── -->
        <div class="msg-modal-overlay" id="modalDirect" style="display:none">
            <div class="msg-modal">
                <div class="msg-modal-header">
                    <h3>New Message</h3>
                    <button class="msg-modal-close" data-modal="modalDirect">✕</button>
                </div>
                <input type="text" class="msg-modal-search" id="directSearch" placeholder="Search a friend…" autocomplete="off">
                <div class="msg-modal-list" id="directResults">
                    <p class="msg-modal-hint">Type at least 2 characters to search</p>
                </div>
            </div>
        </div>

        <!-- ── MODAL : Nouveau groupe ── -->
        <div class="msg-modal-overlay" id="modalGroup" style="display:none">
            <div class="msg-modal">
                <div class="msg-modal-header">
                    <h3>New Group</h3>
                    <button class="msg-modal-close" data-modal="modalGroup">✕</button>
                </div>
                <input type="text" class="msg-modal-input" id="groupName" placeholder="Group name…" maxlength="100">
                <input type="text" class="msg-modal-search" id="groupSearch" placeholder="Add members…" autocomplete="off">
                <div class="msg-modal-list" id="groupResults">
                    <p class="msg-modal-hint">Type to search friends</p>
                </div>
                <div class="msg-selected-members" id="selectedMembers"></div>
                <button class="msg-modal-submit" id="btnCreateGroup">Create Group</button>
            </div>
        </div>

        <script>
        (function() {
            const ME = ' . (int)$currentUserId . ';
            let activeConvId = null;
            let lastMsgId = 0;
            let pollTimer = null;
            let selectedGroupMembers = {};  // { userId: username }

            // ── UTILS ─────────────────────────────────────────────────────
            function avatar(a, name, size = 36) {
                const initials = (name || "?").substring(0, 2).toUpperCase();
                if (a) return `<img src="${escHtml(a)}" class="msg-avatar" style="width:${size}px;height:${size}px" alt="${escHtml(name)}">`;
                const colors = ["#3b82f6","#10b981","#f59e0b","#ef4444","#8b5cf6","#06b6d4"];
                const color = colors[name.charCodeAt(0) % colors.length];
                return `<div class="msg-avatar msg-avatar-letter" style="width:${size}px;height:${size}px;background:${color}">${initials}</div>`;
            }

            function escHtml(s) {
                return String(s ?? "").replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
            }

            function timeAgo(dateStr) {
                const d = new Date(dateStr);
                const now = new Date();
                const diff = Math.floor((now - d) / 1000);
                if (diff < 60) return "now";
                if (diff < 3600) return Math.floor(diff/60) + "m";
                if (diff < 86400) return Math.floor(diff/3600) + "h";
                return d.toLocaleDateString("en-US", {month:"short", day:"numeric"});
            }

            function formatTime(dateStr) {
                const d = new Date(dateStr);
                return d.toLocaleTimeString("en-US", {hour:"2-digit", minute:"2-digit"});
            }

            function ajax(params, method = "GET") {
                const base = "?action=" + params.action;
                if (method === "GET") {
                    const qs = Object.entries(params).map(([k,v]) => k+"="+encodeURIComponent(v)).join("&");
                    return fetch("?" + qs).then(r => r.json());
                } else {
                    const fd = new FormData();
                    Object.entries(params).forEach(([k,v]) => {
                        if (k !== "action") fd.append(k, v);
                    });
                    return fetch("?action=" + params.action, {method:"POST", body: fd}).then(r => r.json());
                }
            }

            // ── CHARGER LES CONVERSATIONS ────────────────────────────────
            function loadConversations(filter = "") {
                fetch("?action=getConversations")
                    .then(r => r.json())
                    .then(data => renderConvList(data, filter));
            }

            function renderConvList(convs, filter = "") {
                const list = document.getElementById("msgConvList");
                const filtered = filter
                    ? convs.filter(c => {
                        const name = getConvName(c).toLowerCase();
                        return name.includes(filter.toLowerCase());
                    })
                    : convs;

                if (!filtered.length) {
                    list.innerHTML = `<div class="msg-empty-list">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="28" height="28"><path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z" clip-rule="evenodd"/></svg>
                        <p>No conversations yet</p>
                        <p class="msg-empty-hint">Start chatting with your friends!</p>
                    </div>`;
                    return;
                }

                list.innerHTML = filtered.map(c => {
                    const name = getConvName(c);
                    const av = getConvAvatar(c, name);
                    const lastMsg = c.last_message
                        ? `<span class="${c.last_sender_id == ME ? "msg-conv-me" : ""}">${c.last_sender_id == ME ? "You: " : ""}${escHtml(c.last_message.substring(0,40))}${c.last_message.length > 40 ? "…" : ""}</span>`
                        : `<span class="msg-conv-empty">No messages yet</span>`;
                    const badge = c.unread_count > 0
                        ? `<span class="msg-unread-badge">${c.unread_count}</span>` : "";
                    const active = activeConvId === c.id ? "msg-conv-item--active" : "";
                    const isGroup = c.type === "group"
                        ? `<span class="msg-group-badge">group</span>` : "";
                    return `<div class="msg-conv-item ${active}" data-id="${c.id}" onclick="window.openConv(${c.id})">
                        ${av}
                        <div class="msg-conv-info">
                            <div class="msg-conv-top">
                                <span class="msg-conv-name">${escHtml(name)}${isGroup}</span>
                                <span class="msg-conv-time">${c.last_message_at ? timeAgo(c.last_message_at) : ""}</span>
                            </div>
                            <div class="msg-conv-preview">${lastMsg}${badge}</div>
                        </div>
                    </div>`;
                }).join("");
            }

            function getConvName(c) {
                if (c.type === "group") return c.name || "Group";
                const other = (c.members || []).find(m => m.id != ME);
                return other ? other.username : "Unknown";
            }

            function getConvAvatar(c, name) {
                if (c.type === "group") {
                    return `<div class="msg-avatar msg-avatar-letter msg-avatar-group" style="width:42px;height:42px">${escHtml(name.substring(0,2).toUpperCase())}</div>`;
                }
                const other = (c.members || []).find(m => m.id != ME);
                return avatar(other?.avatar, name, 42);
            }

            // ── OUVRIR UNE CONVERSATION ──────────────────────────────────
            window.openConv = function(convId) {
                activeConvId = convId;
                lastMsgId = 0;
                clearInterval(pollTimer);

                document.getElementById("msgChatEmpty").style.display = "none";
                document.getElementById("msgChatHeader").style.display = "flex";
                document.getElementById("msgInputWrap").style.display = "flex";
                document.getElementById("msgMessages").innerHTML = `<div class="msg-loading"><div class="msg-spinner"></div></div>`;

                // Mobile: masque sidebar
                document.getElementById("msgSidebar").classList.add("msg-sidebar--hidden");
                document.getElementById("msgChat").classList.add("msg-chat--active");

                // Charger messages + info conv
                fetch("?action=getConvInfo&conv_id=" + convId)
                    .then(r => r.json())
                    .then(conv => {
                        if (!conv) return;
                        updateChatHeader(conv);
                    });

                fetch("?action=getMessages&conv_id=" + convId)
                    .then(r => r.json())
                    .then(msgs => {
                        renderMessages(msgs);
                        if (msgs.length) lastMsgId = msgs[msgs.length-1].id;
                        scrollBottom();
                        startPolling();
                    });

                // Highlight dans la liste
                document.querySelectorAll(".msg-conv-item").forEach(el => {
                    el.classList.toggle("msg-conv-item--active", parseInt(el.dataset.id) === convId);
                });
            };

            function updateChatHeader(conv) {
                const other = conv.type === "group"
                    ? null
                    : (conv.members || []).find(m => m.id != ME);
                const name = conv.type === "group"
                    ? (conv.name || "Group")
                    : (other?.username || "Unknown");
                const sub = conv.type === "group"
                    ? (conv.members || []).map(m => m.username).join(", ")
                    : (other?.country || "");

                document.getElementById("chatHeaderAvatar").innerHTML = conv.type === "group"
                    ? `<div class="msg-avatar msg-avatar-letter msg-avatar-group" style="width:38px;height:38px">${name.substring(0,2).toUpperCase()}</div>`
                    : avatar(other?.avatar, name, 38);
                document.getElementById("chatHeaderName").textContent = name;
                document.getElementById("chatHeaderSub").textContent = sub;
            }

            function renderMessages(msgs) {
                const container = document.getElementById("msgMessages");
                if (!msgs.length) {
                    container.innerHTML = `<div class="msg-no-messages">No messages yet — say hello! 👋</div>`;
                    return;
                }
                container.innerHTML = msgs.map(m => renderMsg(m)).join("");
            }

            function renderMsg(m) {
                const isMine = m.sender_id == ME;
                const content = m.is_deleted
                    ? `<em class="msg-deleted">Message deleted</em>`
                    : escHtml(m.content).replace(/\n/g, "<br>");
                return `<div class="msg-bubble-wrap ${isMine ? "msg-bubble-wrap--mine" : ""}">
                    ${!isMine ? `<div class="msg-bubble-sender">${avatar(m.sender_avatar, m.sender_username, 26)}</div>` : ""}
                    <div class="msg-bubble ${isMine ? "msg-bubble--mine" : "msg-bubble--other"}">
                        ${!isMine ? `<div class="msg-bubble-name">${escHtml(m.sender_username)}</div>` : ""}
                        <div class="msg-bubble-text">${content}</div>
                        <div class="msg-bubble-time">${formatTime(m.created_at)}</div>
                    </div>
                </div>`;
            }

            function appendMessages(msgs) {
                if (!msgs.length) return;
                const container = document.getElementById("msgMessages");
                // Retire le "no messages yet" si présent
                const noMsg = container.querySelector(".msg-no-messages");
                if (noMsg) noMsg.remove();

                const atBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 80;
                msgs.forEach(m => {
                    container.insertAdjacentHTML("beforeend", renderMsg(m));
                    lastMsgId = Math.max(lastMsgId, parseInt(m.id));
                });
                if (atBottom) scrollBottom();
            }

            function scrollBottom() {
                const c = document.getElementById("msgMessages");
                c.scrollTop = c.scrollHeight;
            }

            // ── POLLING ──────────────────────────────────────────────────
            function startPolling() {
                pollTimer = setInterval(() => {
                    if (!activeConvId) return;
                    fetch(`?action=pollMessages&conv_id=${activeConvId}&after_id=${lastMsgId}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.messages && data.messages.length) {
                                appendMessages(data.messages);
                            }
                            // Refresh conv list pour badges
                            loadConversations(document.getElementById("msgSearch").value);
                        });
                }, 3000);
            }

            // ── ENVOYER ──────────────────────────────────────────────────
            function sendMessage() {
                const input = document.getElementById("msgInput");
                const content = input.value.trim();
                if (!content || !activeConvId) return;

                input.value = "";
                input.style.height = "auto";

                const fd = new FormData();
                fd.append("conv_id", activeConvId);
                fd.append("content", content);

                fetch("?action=sendMessage", {method:"POST", body: fd})
                    .then(r => r.json())
                    .then(data => {
                        if (data.message) {
                            appendMessages([data.message]);
                            loadConversations(document.getElementById("msgSearch").value);
                        }
                    });
            }

            document.getElementById("msgSendBtn").onclick = sendMessage;
            document.getElementById("msgInput").addEventListener("keydown", e => {
                if (e.key === "Enter" && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
            document.getElementById("msgInput").addEventListener("input", function() {
                this.style.height = "auto";
                this.style.height = Math.min(this.scrollHeight, 120) + "px";
            });

            // ── BACK BUTTON (MOBILE) ─────────────────────────────────────
            document.getElementById("msgBackBtn").onclick = function() {
                clearInterval(pollTimer);
                activeConvId = null;
                document.getElementById("msgSidebar").classList.remove("msg-sidebar--hidden");
                document.getElementById("msgChat").classList.remove("msg-chat--active");
                document.getElementById("msgChatHeader").style.display = "none";
                document.getElementById("msgInputWrap").style.display = "none";
                document.getElementById("msgChatEmpty").style.display = "flex";
                document.getElementById("msgMessages").innerHTML = "";
            };

            // ── QUITTER CONVERSATION ─────────────────────────────────────
            document.getElementById("btnLeaveConv").onclick = function() {
                if (!activeConvId) return;
                if (!confirm("Leave this conversation?")) return;
                const fd = new FormData();
                fd.append("conv_id", activeConvId);
                fetch("?action=leaveConversation", {method:"POST", body:fd})
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById("msgBackBtn").click();
                            loadConversations();
                        }
                    });
            };

            // ── RECHERCHE CONVERSATION ───────────────────────────────────
            let allConvs = [];
            fetch("?action=getConversations").then(r => r.json()).then(d => {
                allConvs = d;
                renderConvList(d);
            });

            document.getElementById("msgSearch").addEventListener("input", function() {
                renderConvList(allConvs, this.value);
            });

            // ── MODAL UTILITAIRES ────────────────────────────────────────
            function openModal(id) { document.getElementById(id).style.display = "flex"; }
            function closeModal(id) { document.getElementById(id).style.display = "none"; }

            document.querySelectorAll(".msg-modal-close").forEach(btn => {
                btn.onclick = () => closeModal(btn.dataset.modal);
            });
            document.querySelectorAll(".msg-modal-overlay").forEach(overlay => {
                overlay.onclick = e => { if (e.target === overlay) closeModal(overlay.id); };
            });

            // ── MODAL NOUVEAU MESSAGE DIRECT ────────────────────────────
            document.getElementById("btnNewDirect").onclick = () => {
                document.getElementById("directSearch").value = "";
                document.getElementById("directResults").innerHTML = `<p class="msg-modal-hint">Type at least 2 characters</p>`;
                openModal("modalDirect");
            };

            let directTimer;
            document.getElementById("directSearch").addEventListener("input", function() {
                clearTimeout(directTimer);
                const q = this.value.trim();
                if (q.length < 2) {
                    document.getElementById("directResults").innerHTML = `<p class="msg-modal-hint">Type at least 2 characters</p>`;
                    return;
                }
                directTimer = setTimeout(() => {
                    fetch("?action=searchUsers&q=" + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(users => {
                            const res = document.getElementById("directResults");
                            if (!users.length) { res.innerHTML = `<p class="msg-modal-hint">No users found</p>`; return; }
                            res.innerHTML = users.map(u => `
                                <div class="msg-modal-user" onclick="window.startDirect(${u.id})">
                                    ${avatar(u.avatar, u.username, 36)}
                                    <div class="msg-modal-user-info">
                                        <strong>${escHtml(u.username)}</strong>
                                        <small>${escHtml(u.country || "")}</small>
                                    </div>
                                </div>`).join("");
                        });
                }, 300);
            });

            window.startDirect = function(userId) {
                closeModal("modalDirect");
                const fd = new FormData();
                fd.append("target_id", userId);
                fetch("?action=openDirect", {method:"POST", body:fd})
                    .then(r => r.json())
                    .then(data => {
                        if (data.conv_id) {
                            loadConversations();
                            setTimeout(() => window.openConv(data.conv_id), 300);
                        }
                    });
            };

            // ── MODAL NOUVEAU GROUPE ─────────────────────────────────────
            document.getElementById("btnNewGroup").onclick = () => {
                document.getElementById("groupName").value = "";
                document.getElementById("groupSearch").value = "";
                document.getElementById("groupResults").innerHTML = `<p class="msg-modal-hint">Type to search friends</p>`;
                selectedGroupMembers = {};
                renderSelectedMembers();
                openModal("modalGroup");
            };

            let groupTimer;
            document.getElementById("groupSearch").addEventListener("input", function() {
                clearTimeout(groupTimer);
                const q = this.value.trim();
                if (q.length < 2) {
                    document.getElementById("groupResults").innerHTML = `<p class="msg-modal-hint">Type to search friends</p>`;
                    return;
                }
                groupTimer = setTimeout(() => {
                    fetch("?action=searchUsers&q=" + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(users => {
                            const res = document.getElementById("groupResults");
                            if (!users.length) { res.innerHTML = `<p class="msg-modal-hint">No users found</p>`; return; }
                            res.innerHTML = users.map(u => {
                                const added = selectedGroupMembers[u.id] ? "msg-modal-user--added" : "";
                                return `<div class="msg-modal-user ${added}" onclick="window.toggleGroupMember(${u.id}, \'${escHtml(u.username)}\', \'${escHtml(u.avatar || \'\')}\')">
                                    ${avatar(u.avatar, u.username, 36)}
                                    <div class="msg-modal-user-info">
                                        <strong>${escHtml(u.username)}</strong>
                                        <small>${escHtml(u.country || "")}</small>
                                    </div>
                                    ${selectedGroupMembers[u.id] ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#10b981" width="18" height="18"><path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd"/></svg>` : ""}
                                </div>`;
                            }).join("");
                        });
                }, 300);
            });

            window.toggleGroupMember = function(id, username, av) {
                if (selectedGroupMembers[id]) {
                    delete selectedGroupMembers[id];
                } else {
                    selectedGroupMembers[id] = {username, avatar: av};
                }
                renderSelectedMembers();
                // Refresh liste
                document.getElementById("groupSearch").dispatchEvent(new Event("input"));
            };

            function renderSelectedMembers() {
                const container = document.getElementById("selectedMembers");
                const members = Object.entries(selectedGroupMembers);
                if (!members.length) { container.innerHTML = ""; return; }
                container.innerHTML = members.map(([id, m]) =>
                    `<div class="msg-selected-chip">
                        ${avatar(m.avatar, m.username, 22)}
                        <span>${escHtml(m.username)}</span>
                        <button onclick="window.toggleGroupMember(${id}, \'${escHtml(m.username)}\', \'${escHtml(m.avatar || "")}\')" class="msg-chip-remove">✕</button>
                    </div>`
                ).join("");
            }

            document.getElementById("btnCreateGroup").onclick = function() {
                const name = document.getElementById("groupName").value.trim();
                if (!name) { alert("Please enter a group name"); return; }
                const memberIds = Object.keys(selectedGroupMembers);
                if (memberIds.length < 1) { alert("Please add at least one member"); return; }

                const fd = new FormData();
                fd.append("group_name", name);
                memberIds.forEach(id => fd.append("member_ids[]", id));

                fetch("?action=createGroup", {method:"POST", body:fd})
                    .then(r => r.json())
                    .then(data => {
                        if (data.conv_id) {
                            closeModal("modalGroup");
                            loadConversations();
                            setTimeout(() => window.openConv(data.conv_id), 300);
                        }
                    });
            };

            // ── INIT ─────────────────────────────────────────────────────
            loadConversations();

        })();
        </script>
        ';
    }
}