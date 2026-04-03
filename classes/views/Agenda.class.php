<?php
class Agenda extends Dashboard
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

        // Calendrier — mois courant
        $today     = new DateTime();
        $year      = (int)($_GET['year']  ?? $today->format('Y'));
        $month     = (int)($_GET['month'] ?? $today->format('m'));
        if ($month < 1)  { $month = 12; $year--; }
        if ($month > 12) { $month = 1;  $year++; }

        $firstDay    = new DateTime("$year-$month-01");
        $daysInMonth = (int)$firstDay->format('t');
        $startDow    = (int)$firstDay->format('N'); // 1=Mon … 7=Sun
        $monthName   = strtoupper($firstDay->format('F Y'));
        $todayNum    = ($today->format('Y-m') === "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT))
                       ? (int)$today->format('j') : 0;

        // Navigation prev/next
        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1)  { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1;  $nextYear++; }

        // Grille du calendrier
        $cells      = '';
        $dayHeaders = '';
        foreach (['MON','TUE','WED','THU','FRI','SAT','SUN'] as $d) {
            $dayHeaders .= '<div class="cal-head">' . $d . '</div>';
        }

        // Cases vides avant le 1er
        for ($i = 1; $i < $startDow; $i++) {
            $cells .= '<div class="cal-cell cal-cell--empty"></div>';
        }

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $isToday = ($d === $todayNum) ? ' cal-cell--today' : '';
            $cells .= '
                <div class="cal-cell' . $isToday . '" data-day="' . $d . '">
                    <span class="cal-day-num">' . $d . '</span>
                </div>';
        }

        // Jour sélectionné (today par défaut)
        $selectedDay  = $todayNum ?: 1;
        $selectedDate = strtoupper((new DateTime("$year-$month-$selectedDay"))->format('l, F j'));
        $todayDate    = $today->format('Y-m-d');

        // Sidebar
        $sidebar = $this->sidebar($user, $avatar, 'agenda');

        return <<<HTML
        <link rel="stylesheet" href="public/css/dashboard.css">
        <div class="dash-layout">

            {$sidebar}

            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">MY AGENDA</h1>
                        <p class="dash-subtitle">Plan your cultural exchanges, meetings, and travel events.</p>
                    </div>
                    <button class="dash-new-event-btn" id="btnNewEventMain">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd"/></svg>
                        New Event
                    </button>
                </div>

                <div class="agenda-grid">
                    <!-- Calendrier -->
                    <div class="agenda-calendar-wrap">
                        <!-- Hidden field to pass userId (for viewing other users' public events) -->
                        <input type="hidden" id="viewedUserId" value="{$user['id']}">
                        <div class="cal-nav">
                            <a href="?page=agenda&month={$prevMonth}&year={$prevYear}" class="cal-nav-btn">&#8249;</a>
                            <span class="cal-month-label">{$monthName}</span>
                            <a href="?page=agenda&month={$nextMonth}&year={$nextYear}" class="cal-nav-btn">&#8250;</a>
                        </div>
                        <div class="cal-grid">
                            {$dayHeaders}
                            {$cells}
                        </div>
                        <div class="cal-legend">
                            <span class="cal-legend-dot cal-legend-dot--private"></span> Private
                            <span class="cal-legend-dot cal-legend-dot--shared"></span> Cultural exchange
                            <span class="cal-legend-dot cal-legend-dot--public"></span> Particular
                            <span class="cal-legend-dot cal-legend-dot--public2"></span> Public Event
                        </div>
                    </div>

                    <!-- Panel jour sélectionné -->
                    <div class="agenda-day-panel" id="agendaDayPanel">
                        <div class="agenda-day-header" id="agendaDayTitle">
                            {$selectedDate}
                            <button class="dash-new-event-btn dash-new-event-btn--sm" id="btnAddEvent">+ ADD</button>
                        </div>
                        <div class="agenda-events" id="agendaEventsList">
                            <div class="agenda-no-events">No events for this day.<br>Click a day or "+ New Event" to add one.</div>
                        </div>
                    </div>
                </div>

                <!-- ── INVITATIONS REÇUES ── -->
                <div class="dash-section" style="margin-top:28px;">
                    <div class="dash-section-header">
                        <h2 class="dash-section-title">EVENT INVITATIONS</h2>
                        <span class="dash-subtitle" style="font-size:12px;margin:0;">Pending invitations to events from other users</span>
                    </div>
                    <div id="invitationsList">
                        <div class="agenda-no-events" style="padding:24px 0;">Loading invitations…</div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Créer un événement -->
        <div class="modal-overlay" id="eventModal" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <h2 class="modal-title">NEW EVENT</h2>
                    <button class="modal-close" id="btnCloseModal">&#x2715;</button>
                </div>
                <form class="modal-form" onsubmit="return false;">
                    <input type="hidden" id="evtId" value="">
                    <div class="modal-field">
                        <label>EVENT TITLE</label>
                        <input type="text" placeholder="e.g. French Cooking Class" id="evtTitle">
                    </div>
                    <div class="modal-field">
                        <label>DATE</label>
                        <input type="date" id="evtDate" value="{$todayDate}">
                    </div>
                    <div class="modal-row">
                        <div class="modal-field">
                            <label>START</label>
                            <input type="time" id="evtStart" value="10:00">
                        </div>
                        <div class="modal-field">
                            <label>END</label>
                            <input type="time" id="evtEnd" value="11:00">
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>LOCATION</label>
                        <input type="text" id="evtLocation" placeholder="e.g. Lyon, France">
                    </div>
                    <div class="modal-field">
                        <label>TYPE</label>
                        <div class="modal-type-btns">
                            <button type="button" class="type-btn type-btn--private active" data-type="private">&#x1F512; Private</button>
                            <button type="button" class="type-btn type-btn--shared" data-type="shared">&#x1F91D; Shared</button>
                            <button type="button" class="type-btn type-btn--public" data-type="public">&#x1F30D; Public</button>
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>NOTES</label>
                        <textarea placeholder="Optional notes..." id="evtNotes" rows="2"></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="prof-btn prof-btn--outline" id="btnCancel">Cancel</button>
                        <button type="button" class="prof-btn prof-btn--primary" id="btnSave">Save Event</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Inviter à un événement -->
        <div class="modal-overlay" id="inviteModal" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <h2 class="modal-title">INVITE TO EVENT</h2>
                    <button type="button" class="modal-close" onclick="closeInviteModal()">&#x2715;</button>
                </div>
                <div class="modal-form">
                    <input type="hidden" id="inviteEventId" value="">
                    <input type="hidden" id="inviteUsername" value="">
                    <div class="modal-field">
                        <label>SEARCH USER</label>
                        <div class="invite-search-wrap">
                            <input type="text" class="invite-search-input" id="inviteSearchInput"
                                   placeholder="Type a username…" autocomplete="off">
                        </div>
                        <div class="invite-search-results" id="inviteSearchResults"></div>
                        <div id="inviteSelectedUser" style="display:none;" class="invite-selected-user">
                            <span id="inviteSelectedName"></span>
                            <button type="button" class="invite-clear-btn" onclick="clearInviteSelection()">&#x2715;</button>
                        </div>
                    </div>
                    <div id="inviteMessage" style="margin: 10px 0; font-weight: bold;"></div>
                    <div class="modal-actions">
                        <button type="button" class="prof-btn prof-btn--outline" onclick="closeInviteModal()">Cancel</button>
                        <button type="button" class="prof-btn prof-btn--primary" id="btnSendInvite" onclick="inviteToEvent()" disabled>Send Invite</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
const currentYear = {$year};
const currentMonth = {$month};
let selectedDay = {$selectedDay};
let events = {};

// Attach all listeners AFTER functions are defined
function attachListeners() {
    document.getElementById('btnNewEventMain').addEventListener('click', openEventModal);
    document.getElementById('btnAddEvent').addEventListener('click', openEventModal);
    document.getElementById('btnCloseModal').addEventListener('click', closeEventModal);
    document.getElementById('btnCancel').addEventListener('click', closeEventModal);
    document.getElementById('btnSave').addEventListener('click', saveEvent);

    document.querySelectorAll('.type-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            selectType(this);
        });
    });

    document.querySelectorAll('.cal-cell:not(.cal-cell--empty)').forEach(function(cell) {
        cell.addEventListener('click', function() {
            document.querySelectorAll('.cal-cell--selected').forEach(function(c) {
                c.classList.remove('cal-cell--selected');
            });
            this.classList.add('cal-cell--selected');
            selectedDay = parseInt(this.dataset.day, 10);
            updateDayTitle(selectedDay);
            renderEvents(selectedDay);
        });
    });
}

function updateDayTitle(day) {
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    const days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const dt = new Date(currentYear, currentMonth - 1, day);
    const title = days[dt.getDay()].toUpperCase() + ', ' + months[dt.getMonth()].toUpperCase() + ' ' + day;
    document.getElementById('agendaDayTitle').textContent = title;
}

async function loadEvents() {
    try {
        const viewedUserId = document.getElementById('viewedUserId')?.value || '';
        let url = '?page=agenda&action=getAgendaEvents&year=' + currentYear + '&month=' + currentMonth;
        if (viewedUserId) {
            url += '&user_id=' + viewedUserId;
        }
        const resp = await fetch(url);
        const data = await resp.json();

        if (!data.success) {
            console.error('Erreur chargement:', data.error);
            document.getElementById('agendaEventsList').innerHTML = '<div class="agenda-no-events">Erreur chargement</div>';
            return;
        }

        events = {};
        data.events.forEach(function(e) {
            const day = new Date(e.event_date).getDate();
            if (!events[day]) events[day] = [];
            const starts = e.event_date.substr(11, 5);
            const endMatch = e.description ? e.description.match(/\[end:(\d{1,2}:\d{2})\]/) : null;
            const endTime = endMatch ? endMatch[1] : '';
            const notes = e.description ? e.description.replace(/\[end:\d{1,2}:\d{2}\]\s*/, '').trim() : '';

            events[day].push({
                id: e.id,
                title: e.title,
                location: e.location || '',
                notes: notes,
                start: starts,
                end: endTime,
                type: e.type,
                date: e.event_date.substr(0, 10)
            });
        });

        markDayDots();
        renderEvents(selectedDay);
    } catch (err) {
        console.error('Erreur fetch:', err);
    }
}

function markDayDots() {
    document.querySelectorAll('.cal-cell').forEach(function(cell) {
        const day = cell.dataset.day;
        if (!day || cell.classList.contains('cal-cell--empty')) return;
        const exists = events[day] && events[day].length > 0;
        let dot = cell.querySelector('.cal-dot');
        if (exists) {
            if (!dot) {
                dot = document.createElement('span');
                dot.className = 'cal-dot cal-dot--shared';
                cell.appendChild(dot);
            }
        } else {
            if (dot) dot.remove();
        }
    });
}

function renderEvents(day) {
    const list = document.getElementById('agendaEventsList');
    const evts = events[day] || [];

    if (evts.length === 0) {
        list.innerHTML = '<div class="agenda-no-events">No events for this day.<br>Click &quot;+ ADD&quot; to create one.</div>';
        return;
    }

    let html = '';
    evts.forEach(function(e) {
        const typeLabel = e.type === 'shared' ? 'Cultural Exchange' : e.type === 'public' ? 'Public Event' : 'Private';
        html += '<div class="agenda-event agenda-event--' + e.type + '">';
        html += '<div class="agenda-event-type">' + typeLabel + '</div>';
        html += '<div class="agenda-event-title">' + e.title + '</div>';
        html += '<div class="agenda-event-meta">&#x23F0; ' + e.start;
        if (e.end) html += ' &mdash; ' + e.end;
        html += '</div>';
        if (e.location) html += '<div class="agenda-event-location">&#x1F4CD; ' + e.location + '</div>';
        if (e.notes) html += '<div class="agenda-event-notes">' + e.notes + '</div>';
        html += '<div class="agenda-event-actions">';
        html += '<button type="button" class="event-edit" data-id="' + e.id + '" data-day="' + day + '">&#x270F;&#xFE0F; Edit</button>';
        html += '<button type="button" class="event-invite" data-id="' + e.id + '">&#x1F4C4; Invite</button>';
        html += '<button type="button" class="event-delete" data-id="' + e.id + '" data-day="' + day + '">&#x1F5D1;&#xFE0F; Delete</button>';
        html += '</div>';
        html += '</div>';
    });
    list.innerHTML = html;

    // Attach listeners to dynamically created buttons
    list.querySelectorAll('.event-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editEvent(parseInt(this.dataset.id, 10), parseInt(this.dataset.day, 10));
        });
    });
    list.querySelectorAll('.event-invite').forEach(function(btn) {
        btn.addEventListener('click', function() {
            showInviteModal(parseInt(this.dataset.id, 10));
        });
    });
    list.querySelectorAll('.event-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            deleteEvent(parseInt(this.dataset.id, 10), parseInt(this.dataset.day, 10));
        });
    });
}

function openEventModal() {
    document.getElementById('evtId').value = '';
    document.getElementById('evtTitle').value = '';
    document.getElementById('evtLocation').value = '';
    const dateStr = currentYear + '-' + String(currentMonth).padStart(2, '0') + '-' + String(selectedDay).padStart(2, '0');
    document.getElementById('evtDate').value = dateStr;
    document.getElementById('evtStart').value = '10:00';
    document.getElementById('evtEnd').value = '11:00';
    document.getElementById('evtNotes').value = '';
    document.querySelectorAll('.type-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    document.querySelector('.type-btn--private').classList.add('active');
    document.getElementById('eventModal').style.display = 'flex';
}

function closeEventModal() {
    document.getElementById('eventModal').style.display = 'none';
}

function selectType(btn) {
    document.querySelectorAll('.type-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    btn.classList.add('active');
}

async function saveEvent() {
    const title    = document.getElementById('evtTitle').value.trim();
    const date     = document.getElementById('evtDate').value;
    const start    = document.getElementById('evtStart').value;
    const end      = document.getElementById('evtEnd').value;
    const location = document.getElementById('evtLocation').value.trim();
    const notes    = document.getElementById('evtNotes').value.trim();
    const typeBtn  = document.querySelector('.type-btn.active');
    const type     = typeBtn ? typeBtn.dataset.type : 'private';
    const id       = document.getElementById('evtId').value;

    if (!title || !date || !start) {
        alert('Titre, date et heure requis');
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('date', date);
    formData.append('start', start);
    formData.append('end', end);
    formData.append('location', location);
    formData.append('notes', notes);
    formData.append('type', type);
    if (id) formData.append('event_id', id);

    const action = id ? 'updateAgendaEvent' : 'createAgendaEvent';

    try {
        const resp = await fetch('?page=agenda&action=' + action, {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();

        if (!data.success) {
            alert('Erreur: ' + (data.error || 'Erreur inconnue'));
            console.error('Erreur POST:', data);
            return;
        }

        closeEventModal();
        await loadEvents();
    } catch (err) {
        alert('Erreur POST: ' + err);
        console.error(err);
    }
}

function editEvent(eventId, day) {
    const evt = (events[day] || []).find(function(e) {
        return e.id === eventId;
    });
    if (!evt) return;

    document.getElementById('evtId').value       = eventId;
    document.getElementById('evtTitle').value    = evt.title;
    document.getElementById('evtLocation').value = evt.location || '';
    document.getElementById('evtDate').value     = evt.date;
    document.getElementById('evtStart').value    = evt.start;
    document.getElementById('evtEnd').value      = evt.end || '';
    document.getElementById('evtNotes').value    = evt.notes || '';

    document.querySelectorAll('.type-btn').forEach(function(b) {
        b.classList.remove('active');
    });
    const btn = document.querySelector('.type-btn--' + evt.type) || document.querySelector('.type-btn--private');
    btn.classList.add('active');

    openEventModal();
}

// ── INVITE MODAL — predictive search ───────────────────────────

let inviteSearchTimer = null;

function showInviteModal(eventId) {
    document.getElementById('inviteEventId').value = eventId;
    document.getElementById('inviteUsername').value = '';
    document.getElementById('inviteSearchInput').value = '';
    document.getElementById('inviteSearchResults').innerHTML = '';
    document.getElementById('inviteSearchResults').classList.remove('active');
    document.getElementById('inviteMessage').textContent = '';
    document.getElementById('inviteSelectedUser').style.display = 'none';
    document.getElementById('btnSendInvite').disabled = true;
    document.getElementById('inviteModal').style.display = 'flex';

    // Attach search listener each time modal opens (safe — input is recreated)
    const input = document.getElementById('inviteSearchInput');
    input.oninput = function() {
        clearTimeout(inviteSearchTimer);
        const q = this.value.trim();
        const results = document.getElementById('inviteSearchResults');
        if (q.length < 2) {
            results.innerHTML = '';
            results.classList.remove('active');
            return;
        }
        inviteSearchTimer = setTimeout(function() { doInviteSearch(q); }, 300);
    };
}

function closeInviteModal() {
    document.getElementById('inviteModal').style.display = 'none';
    clearTimeout(inviteSearchTimer);
}

function doInviteSearch(q) {
    const results = document.getElementById('inviteSearchResults');
    results.innerHTML = '<div class="invite-search-loading">Searching…</div>';
    results.classList.add('active');
    fetch('?action=searchUsers&q=' + encodeURIComponent(q))
        .then(function(r) { return r.json(); })
        .then(function(users) { renderInviteResults(users); })
        .catch(function() { results.innerHTML = '<div class="invite-search-loading">Error searching.</div>'; });
}

function renderInviteResults(users) {
    const results = document.getElementById('inviteSearchResults');
    if (!users || users.length === 0) {
        results.innerHTML = '<div class="invite-search-loading">No users found.</div>';
        return;
    }
    results.innerHTML = users.map(function(u) {
        const avatarHtml = u.avatar
            ? '<img src="' + u.avatar + '" alt="" class="invite-sr-avatar-img">'
            : '<div class="invite-sr-avatar-ph">' + u.username.substring(0, 2).toUpperCase() + '</div>';
        return '<div class="invite-sr-item" onclick="selectInviteUser(\'' + u.username.replace(/'/g, "\\'") + '\')">'
             +   '<div class="invite-sr-avatar">' + avatarHtml + '</div>'
             +   '<div class="invite-sr-info">'
             +     '<span class="invite-sr-name">' + u.username + '</span>'
             +     (u.country ? '<span class="invite-sr-country">' + u.country + '</span>' : '')
             +   '</div>'
             + '</div>';
    }).join('');
}

function selectInviteUser(username) {
    document.getElementById('inviteUsername').value = username;
    document.getElementById('inviteSelectedName').textContent = '👤 ' + username;
    document.getElementById('inviteSelectedUser').style.display = 'flex';
    document.getElementById('inviteSearchInput').value = '';
    document.getElementById('inviteSearchResults').innerHTML = '';
    document.getElementById('inviteSearchResults').classList.remove('active');
    document.getElementById('btnSendInvite').disabled = false;
    document.getElementById('inviteMessage').textContent = '';
}

function clearInviteSelection() {
    document.getElementById('inviteUsername').value = '';
    document.getElementById('inviteSelectedUser').style.display = 'none';
    document.getElementById('btnSendInvite').disabled = true;
    document.getElementById('inviteSearchInput').focus();
}

async function inviteToEvent() {
    const eventId  = document.getElementById('inviteEventId').value;
    const username = document.getElementById('inviteUsername').value.trim();
    const msgDiv   = document.getElementById('inviteMessage');

    if (!username) {
        msgDiv.textContent = 'Please select a user first.';
        msgDiv.style.color = 'red';
        return;
    }

    const formData = new FormData();
    formData.append('event_id', eventId);
    formData.append('username', username);

    try {
        const resp = await fetch('?page=agenda&action=inviteToEvent', { method: 'POST', body: formData });
        const data = await resp.json();
        if (data.success) {
            msgDiv.textContent = 'Invitation sent to ' + username + '!';
            msgDiv.style.color = 'green';
            setTimeout(function() { closeInviteModal(); }, 1500);
        } else {
            msgDiv.textContent = data.error || 'Error sending invitation';
            msgDiv.style.color = 'red';
        }
    } catch (err) {
        msgDiv.textContent = 'Network error: ' + err;
        msgDiv.style.color = 'red';
    }
}

// Close invite search results when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#inviteModal')) return;
    if (!e.target.closest('.invite-search-wrap') && !e.target.closest('.invite-search-results')) {
        const results = document.getElementById('inviteSearchResults');
        if (results) { results.innerHTML = ''; results.classList.remove('active'); }
    }
});

async function deleteEvent(eventId, day) {
    if (!confirm('Supprimer cet événement ?')) return;

    const formData = new FormData();
    formData.append('event_id', eventId);

    try {
        const resp = await fetch('?page=agenda&action=deleteAgendaEvent', {
            method: 'POST',
            body: formData
        });
        const data = await resp.json();

        if (!data.success) {
            alert('Impossible de supprimer');
            return;
        }

        await loadEvents();
    } catch (err) {
        alert('Erreur DELETE: ' + err);
    }
}

// ── INVITATIONS ────────────────────────────────────────────────

async function loadInvitations() {
    const container = document.getElementById('invitationsList');
    if (!container) return;
    try {
        const resp = await fetch('?page=agenda&action=getInvitations');
        const data = await resp.json();
        if (!data.success || data.invitations.length === 0) {
            container.innerHTML = '<div class="agenda-no-events" style="padding:24px 0;">No pending invitations.</div>';
            return;
        }
        let html = '<div class="invitations-grid">';
        data.invitations.forEach(function(inv) {
            const typeLabel = inv.type === 'shared' ? '🤝 Cultural Exchange' : inv.type === 'public' ? '🌍 Public Event' : '🔒 Private';
            const dateStr = inv.event_date ? new Date(inv.event_date).toLocaleDateString('en-GB', {weekday:'short', day:'numeric', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit'}) : '—';
            html += '<div class="invitation-card" id="inv-' + inv.id + '">';
            html += '<div class="inv-type-badge">' + typeLabel + '</div>';
            html += '<div class="inv-title">' + escapeHtml(inv.title) + '</div>';
            html += '<div class="inv-meta">📅 ' + dateStr + '</div>';
            if (inv.location) html += '<div class="inv-meta">📍 ' + escapeHtml(inv.location) + '</div>';
            html += '<div class="inv-meta inv-from">From: <strong>' + escapeHtml(inv.organizer_username) + '</strong></div>';
            html += '<div class="inv-actions">';
            html += '<button class="inv-btn inv-btn--accept" onclick="respondInvitation(' + inv.id + ', ' + inv.event_id + ', \'accepted\')">✅ Accept</button>';
            html += '<button class="inv-btn inv-btn--decline" onclick="respondInvitation(' + inv.id + ', ' + inv.event_id + ', \'declined\')">❌ Decline</button>';
            html += '</div></div>';
        });
        html += '</div>';
        container.innerHTML = html;
    } catch(e) {
        container.innerHTML = '<div class="agenda-no-events" style="padding:24px 0;">Error loading invitations.</div>';
    }
}

async function respondInvitation(participantId, eventId, status) {
    const card = document.getElementById('inv-' + participantId);
    if (card) card.style.opacity = '0.5';
    try {
        const fd = new FormData();
        fd.append('participant_id', participantId);
        fd.append('event_id', eventId);
        fd.append('status', status);
        const resp = await fetch('?page=agenda&action=respondInvitation', {method:'POST', body:fd});
        const data = await resp.json();
        if (data.success) {
            if (card) {
                card.style.transition = 'all 0.3s';
                card.style.transform  = 'scale(0.95)';
                card.style.opacity    = '0';
                setTimeout(function() { card.remove(); loadInvitations(); }, 300);
            }
            if (status === 'accepted') {
                await loadEvents(); // Refresh calendar
            }
        } else {
            if (card) card.style.opacity = '1';
            alert(data.error || 'Error responding to invitation');
        }
    } catch(e) {
        if (card) card.style.opacity = '1';
        alert('Network error');
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    attachListeners();
    updateDayTitle(selectedDay);
    loadEvents();
    loadInvitations();
});
        </script>

        <style>
        .invitations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 14px;
            margin: 8px;
        }
        .invitation-card {
            background: var(--card-bg, #1a1a2e);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: border-color 0.2s;
        }
        .invitation-card:hover { border-color: rgba(99,102,241,0.4); }
        .inv-type-badge {
            font-size: 11px; font-weight: 700; letter-spacing: .05em;
            color: #a78bfa; text-transform: uppercase;
        }
        .inv-title {
            font-size: 16px; font-weight: 700; color: #fff;
            line-height: 1.3;
        }
        .inv-meta { font-size: 13px; color: rgba(255,255,255,0.5); }
        .inv-from strong { color: rgba(255,255,255,0.8); }
        .inv-actions { display: flex; gap: 8px; margin-top: 4px; }
        .inv-btn {
            flex: 1; padding: 8px 12px; border-radius: 8px;
            border: none; cursor: pointer; font-size: 13px; font-weight: 600;
            transition: opacity 0.15s, transform 0.1s;
        }
        .inv-btn:hover { opacity: 0.85; transform: translateY(-1px); }
        .inv-btn--accept  { background: #22c55e; color: #fff; }
        .inv-btn--decline { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }

        .sidebar-nav-badge {
            margin-left: auto;
            background: #ef4444;
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 99px;
            min-width: 18px;
            text-align: center;
            line-height: 16px;
        }

        /* ── Invite predictive search ── */
        .invite-search-wrap {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.15);
            border-radius: 10px;
            padding: 0;
            gap: 8px;
            transition: border-color 0.2s;
        }
        .invite-search-wrap:focus-within {
            border-color: rgba(99,102,241,0.6);
        }
        .invite-search-icon { color: rgba(255,255,255,0.4); flex-shrink: 0; }
        .invite-search-input {
            flex: 1;
            background: none;
            border: none;
            outline: none;
            color: #fff;
            font-size: 14px;
            padding: 10px 0;
        }
        .invite-search-input::placeholder { color: rgba(255,255,255,0.35); }

        .invite-search-results {
            display: none;
            flex-direction: column;
            background: #1e1e2e;
            border: 1.5px solid rgba(255,255,255,0.12);
            border-radius: 10px;
            margin-top: 4px;
            overflow: hidden;
            box-shadow: 0 8px 24px rgba(0,0,0,0.4);
            z-index: 10001;
            max-height: 220px;
            overflow-y: auto;
        }
        .invite-search-results.active { display: flex; }
        .invite-search-loading {
            padding: 12px 16px;
            color: rgba(255,255,255,0.4);
            font-size: 13px;
        }
        .invite-sr-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .invite-sr-item:hover { background: rgba(99,102,241,0.15); }
        .invite-sr-avatar { flex-shrink: 0; }
        .invite-sr-avatar-img {
            width: 32px; height: 32px; border-radius: 50%; object-fit: cover;
        }
        .invite-sr-avatar-ph {
            width: 32px; height: 32px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #a78bfa);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-size: 11px; font-weight: 700;
        }
        .invite-sr-info { display: flex; flex-direction: column; gap: 1px; }
        .invite-sr-name { color: #fff; font-weight: 600; font-size: 14px; }
        .invite-sr-country { color: rgba(255,255,255,0.4); font-size: 12px; }

        .invite-selected-user {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(99,102,241,0.15);
            border: 1.5px solid rgba(99,102,241,0.4);
            border-radius: 8px;
            padding: 8px 12px;
            margin-top: 8px;
            color: #a78bfa;
            font-weight: 600;
            font-size: 14px;
        }
        .invite-clear-btn {
            background: none;
            border: none;
            color: rgba(255,255,255,0.4);
            cursor: pointer;
            font-size: 14px;
            padding: 0 2px;
            line-height: 1;
            transition: color 0.15s;
        }
        .invite-clear-btn:hover { color: #f87171; }
        </style>
HTML;
    }
}