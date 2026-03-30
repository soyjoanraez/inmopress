(function () {
    function parseDateTime(value) {
        if (!value) return null;
        var parts = value.split(' ');
        if (parts.length < 2) return null;
        return { date: parts[0], time: parts[1] };
    }

    function toMinutes(timeStr) {
        var parts = timeStr.split(':');
        return parseInt(parts[0], 10) * 60 + parseInt(parts[1], 10);
    }

    function pad(num) {
        return num < 10 ? '0' + num : '' + num;
    }

    function minutesToTime(mins) {
        var h = Math.floor(mins / 60);
        var m = mins % 60;
        return pad(h) + ':' + pad(m) + ':00';
    }

    function getWeekDates(startDateStr) {
        var start = new Date(startDateStr + 'T00:00:00');
        var dates = [];
        for (var i = 0; i < 7; i++) {
            var d = new Date(start.getTime() + i * 86400000);
            dates.push(d.toISOString().slice(0, 10));
        }
        return dates;
    }

    function fetchEvents(calendar) {
        var data = new FormData();
        data.append('action', 'inmopress_get_week_events');
        data.append('nonce', window.inmopressCalendar.nonce);
        data.append('start', calendar.weekStart);
        data.append('end', calendar.weekEnd);

        var type = document.getElementById('crm-calendar-type');
        var status = document.getElementById('crm-calendar-status');
        var priority = document.getElementById('crm-calendar-priority');
        var agent = document.getElementById('crm-calendar-agent');

        if (type && type.value) data.append('type', type.value);
        if (status && status.value) data.append('status', status.value);
        if (priority && priority.value) data.append('priority', priority.value);
        if (agent && agent.value) data.append('agent', agent.value);

        return fetch(window.inmopressCalendar.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        })
            .then(function (res) { return res.json(); })
            .then(function (json) {
                if (!json || !json.success) return [];
                return json.data || [];
            });
    }

    function clearEvents(calendar) {
        calendar.dayBodies.forEach(function (body) {
            body.innerHTML = '';
        });
    }

    function renderEvents(calendar, events) {
        clearEvents(calendar);
        var hourStart = calendar.hourStart;
        var hourEnd = calendar.hourEnd;
        var hourHeight = calendar.hourHeight;
        var maxMinutes = (hourEnd - hourStart) * 60;

        events.forEach(function (evt) {
            var start = parseDateTime(evt.start);
            if (!start) return;
            var end = parseDateTime(evt.end);
            var date = start.date;

            var dayIndex = calendar.weekDates.indexOf(date);
            if (dayIndex === -1) return;

            var startMinutes = toMinutes(start.time);
            var duration = 30;
            if (end && end.time) {
                var endMinutes = toMinutes(end.time);
                duration = Math.max(15, endMinutes - startMinutes);
            }

            var offsetMinutes = Math.max(0, startMinutes - (hourStart * 60));
            if (offsetMinutes > maxMinutes) return;

            var top = (offsetMinutes / 60) * hourHeight;
            var height = Math.max(18, (duration / 60) * hourHeight);

            var el = document.createElement('div');
            el.className = 'crm-calendar-event';
            el.setAttribute('draggable', 'true');
            el.dataset.eventId = evt.id;
            el.dataset.duration = duration;
            el.style.top = top + 'px';
            el.style.height = height + 'px';
            el.style.background = evt.color || '#3b82f6';
            el.innerHTML = '<div class="crm-calendar-event-title">' + evt.title + '</div>' +
                '<div class="crm-calendar-event-time">' + start.time.slice(0,5) + '</div>';

            el.addEventListener('dragstart', function (e) {
                e.dataTransfer.setData('text/plain', evt.id);
                e.dataTransfer.effectAllowed = 'move';
            });

            el.addEventListener('click', function (e) {
                e.stopPropagation();
                window.location.href = window.inmopressCalendar.editBaseUrl + '&edit=' + evt.id;
            });

            calendar.dayBodies[dayIndex].appendChild(el);
        });
    }

    function updateEventTime(eventId, start, end) {
        var data = new FormData();
        data.append('action', 'inmopress_update_event_time');
        data.append('nonce', window.inmopressCalendar.nonce);
        data.append('event_id', eventId);
        data.append('start', start);
        data.append('end', end);

        return fetch(window.inmopressCalendar.ajaxUrl, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        }).then(function (res) { return res.json(); });
    }

    function attachInteractions(calendar) {
        calendar.dayBodies.forEach(function (body, index) {
            body.addEventListener('dragover', function (e) {
                e.preventDefault();
                body.classList.add('drag-over');
            });
            body.addEventListener('dragleave', function () {
                body.classList.remove('drag-over');
            });
            body.addEventListener('drop', function (e) {
                e.preventDefault();
                body.classList.remove('drag-over');
                var eventId = e.dataTransfer.getData('text/plain');
                if (!eventId) return;

                var rect = body.getBoundingClientRect();
                var offsetY = e.clientY - rect.top;
                var rawMinutes = Math.max(0, (offsetY / calendar.hourHeight) * 60);
                var snap = 15;
                var snappedMinutes = Math.round(rawMinutes / snap) * snap;
                var totalMinutes = calendar.hourStart * 60 + snappedMinutes;
                var startTime = minutesToTime(totalMinutes);
                var date = calendar.weekDates[index];
                var start = date + ' ' + startTime;

                var duration = 30;
                var draggedEl = body.querySelector('[data-event-id="' + eventId + '"]');
                if (draggedEl && draggedEl.dataset.duration) {
                    duration = parseInt(draggedEl.dataset.duration, 10) || duration;
                }

                var endMinutes = totalMinutes + duration;
                var end = date + ' ' + minutesToTime(endMinutes);

                updateEventTime(eventId, start, end).then(function (json) {
                    if (json && json.success) {
                        loadCalendar(calendar);
                    }
                });
            });

            body.addEventListener('click', function (e) {
                if (e.target.closest('.crm-calendar-event')) {
                    return;
                }
                var rect = body.getBoundingClientRect();
                var offsetY = e.clientY - rect.top;
                var rawMinutes = Math.max(0, (offsetY / calendar.hourHeight) * 60);
                var snap = 15;
                var snappedMinutes = Math.round(rawMinutes / snap) * snap;
                var totalMinutes = calendar.hourStart * 60 + snappedMinutes;
                var startTime = minutesToTime(totalMinutes).slice(0,5);
                var date = calendar.weekDates[index];
                var url = window.inmopressCalendar.newBaseUrl + '&start=' + encodeURIComponent(date + ' ' + startTime + ':00');
                window.location.href = url;
            });
        });
    }

    function loadCalendar(calendar) {
        fetchEvents(calendar).then(function (events) {
            renderEvents(calendar, events);
        });
    }

    function init() {
        var calendarEl = document.querySelector('.crm-calendar');
        if (!calendarEl || !window.inmopressCalendar) return;

        var weekStart = calendarEl.dataset.weekStart;
        var weekDates = getWeekDates(weekStart);
        var weekEnd = weekDates[6];

        var calendar = {
            weekStart: weekStart,
            weekEnd: weekEnd,
            weekDates: weekDates,
            hourStart: parseInt(calendarEl.dataset.hourStart, 10) || 8,
            hourEnd: parseInt(calendarEl.dataset.hourEnd, 10) || 20,
            hourHeight: 60,
            dayBodies: Array.prototype.slice.call(calendarEl.querySelectorAll('.crm-calendar-day-body'))
        };

        attachInteractions(calendar);
        loadCalendar(calendar);

        ['crm-calendar-type', 'crm-calendar-status', 'crm-calendar-priority', 'crm-calendar-agent'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('change', function () {
                    loadCalendar(calendar);
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();
