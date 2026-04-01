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

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <div class="dash-layout">

            ' . $this->sidebar($user, $avatar, 'messages') . '

            <div class="dash-main dash-main--messages">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">MESSAGES</h1>
                    </div>
                </div>

                <div class="msg-layout">
                    <!-- Liste des conversations -->
                    <div class="msg-sidebar">
                        <div class="msg-search-wrap">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M10.5 3.75a6.75 6.75 0 100 13.5 6.75 6.75 0 000-13.5zM2.25 10.5a8.25 8.25 0 1114.59 5.28l4.69 4.69a.75.75 0 11-1.06 1.06l-4.69-4.69A8.25 8.25 0 012.25 10.5z" clip-rule="evenodd"/></svg>
                            <input type="text" placeholder="Search conversations..." class="msg-search" id="msgSearch">
                        </div>
                        <div class="msg-conversations" id="msgConversations">
                            <!-- Conversations vides pour l\'instant -->
                            <div class="msg-empty-list">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="32" height="32"><path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z" clip-rule="evenodd"/></svg>
                                <p>No conversations yet.</p>
                                <p class="msg-empty-hint">Connect with travelers to start chatting!</p>
                            </div>
                        </div>
                    </div>

                    <!-- Zone de chat -->
                    <div class="msg-chat" id="msgChat">
                        <div class="msg-chat-empty">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="48" height="48"><path fill-rule="evenodd" d="M4.848 2.771A49.144 49.144 0 0112 2.25c2.43 0 4.817.178 7.152.52 1.978.292 3.348 2.024 3.348 3.97v6.02c0 1.946-1.37 3.678-3.348 3.97a48.901 48.901 0 01-3.476.383.39.39 0 00-.297.17l-2.755 4.133a.75.75 0 01-1.248 0l-2.755-4.133a.39.39 0 00-.297-.17 48.9 48.9 0 01-3.476-.384c-1.978-.29-3.348-2.024-3.348-3.97V6.741c0-1.946 1.37-3.68 3.348-3.97z" clip-rule="evenodd"/></svg>
                            <p>SELECT A CONVERSATION</p>
                            <small>Choose a contact or start a new conversation to begin sharing.</small>
                        </div>
                    </div>
                </div>

            </div>
        </div>';
    }
}