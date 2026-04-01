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

        $firstDay  = new DateTime("$year-$month-01");
        $daysInMonth = (int)$firstDay->format('t');
        $startDow  = (int)$firstDay->format('N'); // 1=Mon … 7=Sun
        $monthName = $firstDay->format('F Y');
        $todayNum  = ($today->format('Y-m') === "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT))
                     ? (int)$today->format('j') : 0;

        // Navigation prev/next
        $prevMonth = $month - 1; $prevYear = $year;
        if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
        $nextMonth = $month + 1; $nextYear = $year;
        if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

        // Grille du calendrier
        $cells = '';
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
        $selectedDay = $todayNum ?: 1;
        $selectedDate = (new DateTime("$year-$month-$selectedDay"))->format('l, F j');

        return '
        <link rel="stylesheet" href="public/css/dashboard.css">
        <div class="dash-layout">

            ' . $this->sidebar($user, $avatar, 'agenda') . '

            <div class="dash-main">
                <div class="dash-topbar">
                    <div>
                        <h1 class="dash-title">MY AGENDA</h1>
                        <p class="dash-subtitle">Plan your cultural exchanges, meetings, and travel events.</p>
                    </div>
                    <button class="dash-new-event-btn" onclick="openEventModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="16" height="16"><path fill-rule="evenodd" d="M12 3.75a.75.75 0 01.75.75v6.75h6.75a.75.75 0 010 1.5h-6.75v6.75a.75.75 0 01-1.5 0v-6.75H4.5a.75.75 0 010-1.5h6.75V4.5a.75.75 0 01.75-.75z" clip-rule="evenodd"/></svg>
                        New Event
                    </button>
                </div>

                <div class="agenda-grid">
                    <!-- Calendrier -->
                    <div class="agenda-calendar-wrap">
                        <div class="cal-nav">
                            <a href="?page=agenda&month=' . $prevMonth . '&year=' . $prevYear . '" class="cal-nav-btn">&#8249;</a>
                            <span class="cal-month-label">' . strtoupper($monthName) . '</span>
                            <a href="?page=agenda&month=' . $nextMonth . '&year=' . $nextYear . '" class="cal-nav-btn">&#8250;</a>
                        </div>
                        <div class="cal-grid">
                            ' . $dayHeaders . '
                            ' . $cells . '
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
                            ' . strtoupper($selectedDate) . '
                            <button class="dash-new-event-btn dash-new-event-btn--sm" onclick="openEventModal()">+ ADD</button>
                        </div>
                        <div class="agenda-events" id="agendaEventsList">
                            <div class="agenda-no-events">No events for this day.<br>Click a day or "+ New Event" to add one.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Modal Créer un événement -->
        <div class="modal-overlay" id="eventModal" style="display:none">
            <div class="modal-box">
                <div class="modal-header">
                    <h2 class="modal-title">NEW EVENT</h2>
                    <button class="modal-close" onclick="closeEventModal()">✕</button>
                </div>
                <form class="modal-form" onsubmit="return false;">
                    <div class="modal-field">
                        <label>EVENT TITLE</label>
                        <input type="text" placeholder="e.g. French Cooking Class" id="evtTitle">
                    </div>
                    <div class="modal-field">
                        <label>DATE</label>
                        <input type="date" id="evtDate" value="' . $today->format('Y-m-d') . '">
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
                        <label>TYPE</label>
                        <div class="modal-type-btns">
                            <button type="button" class="type-btn type-btn--private active" data-type="private" onclick="selectType(this)">🔒 Private</button>
                            <button type="button" class="type-btn type-btn--shared" data-type="shared" onclick="selectType(this)">🤝 Shared</button>
                            <button type="button" class="type-btn type-btn--public" data-type="public" onclick="selectType(this)">🌍 Public</button>
                        </div>
                    </div>
                    <div class="modal-field">
                        <label>NOTES</label>
                        <textarea placeholder="Optional notes…" id="evtNotes" rows="2"></textarea>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="prof-btn prof-btn--outline" onclick="closeEventModal()">Cancel</button>
                        <button type="button" class="prof-btn prof-btn--primary" onclick="saveEvent()">Save Event</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        // ── Sélection de jour ──
        document.querySelectorAll(".cal-cell:not(.cal-cell--empty)").forEach(cell => {
            cell.addEventListener("click", function() {
                document.querySelectorAll(".cal-cell--selected").forEach(c => c.classList.remove("cal-cell--selected"));
                this.classList.add("cal-cell--selected");
                const day = this.dataset.day;
                const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
                const date = new Date(' . $year . ', ' . ($month - 1) . ', day);
                const days = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];
                document.getElementById("agendaDayTitle").innerHTML =
                    days[date.getDay()].toUpperCase() + ", " + months[date.getMonth()].toUpperCase() + " " + day +
                    \'<button class="dash-new-event-btn dash-new-event-btn--sm" onclick="openEventModal()">+ ADD</button>\';
                renderEvents(day);
            });
        });

        // Données locales (à remplacer par fetch BDD plus tard)
        let events = {};

        function renderEvents(day) {
            const list = document.getElementById("agendaEventsList");
            const evts = events[day] || [];
            if (evts.length === 0) {
                list.innerHTML = \'<div class="agenda-no-events">No events for this day.<br>Click "+ ADD" to create one.</div>\';
                return;
            }
            list.innerHTML = evts.map(e => `
                <div class="agenda-event agenda-event--\${e.type}">
                    <div class="agenda-event-type">\${e.type === "shared" ? "Cultural Exchange" : e.type === "public" ? "Public Event" : "Private"}</div>
                    <div class="agenda-event-title">\${e.title}</div>
                    <div class="agenda-event-meta">⏰ \${e.start} — \${e.end}</div>
                    ${e.notes ? `<div class="agenda-event-notes">${String(e.notes)}</div>` : ""}
                </div>
            `).join("");
        }

        function openEventModal() {
            document.getElementById("eventModal").style.display = "flex";
        }
        function closeEventModal() {
            document.getElementById("eventModal").style.display = "none";
        }
        function selectType(btn) {
            document.querySelectorAll(".type-btn").forEach(b => b.classList.remove("active"));
            btn.classList.add("active");
        }
        function saveEvent() {
            const title = document.getElementById("evtTitle").value.trim();
            if (!title) { alert("Please enter a title."); return; }
            const date  = document.getElementById("evtDate").value;
            const day   = parseInt(date.split("-")[2]);
            const start = document.getElementById("evtStart").value;
            const end   = document.getElementById("evtEnd").value;
            const type  = document.querySelector(".type-btn.active")?.dataset.type || "private";
            const notes = document.getElementById("evtNotes").value.trim();

            if (!events[day]) events[day] = [];
            events[day].push({ title, start, end, type, notes });

            // Marquer le jour dans le calendrier
            const cell = document.querySelector(`.cal-cell[data-day="${day}"]`);
            if (cell) {
                let dot = cell.querySelector(".cal-dot");
                if (!dot) { dot = document.createElement("span"); dot.className = "cal-dot cal-dot--" + type; cell.appendChild(dot); }
            }

            closeEventModal();
            renderEvents(day);
        }
        </script>';
    }
}