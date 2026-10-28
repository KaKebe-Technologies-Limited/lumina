/* Bono AI chat window (docs/BONO.md). No dependencies, CSP-safe (no inline code). Answers are inserted as text, never as HTML. */
(function () {
  'use strict';
  var root = document.querySelector('[data-bono]');
  if (!root) return;

  var launch = root.querySelector('[data-bono-open]');
  var panel = root.querySelector('.bono__panel');
  var closeBtn = root.querySelector('[data-bono-close]');
  var log = root.querySelector('[data-bono-log]');
  var form = root.querySelector('[data-bono-form]');
  var input = form.querySelector('input');
  var tokenEl = document.querySelector('meta[name="csrf-token"]');
  var KEY = 'bono-chat-v1';
  var history = [];
  var busy = false;
  var greeted = false;

  var save = function () {
    try { sessionStorage.setItem(KEY, JSON.stringify(history.slice(-20))); } catch (e) { /* storage may be blocked */ }
  };
  var restore = function () {
    try { var raw = sessionStorage.getItem(KEY); if (raw) history = JSON.parse(raw) || []; } catch (e) { history = []; }
  };
  var scroll = function () { log.scrollTop = log.scrollHeight; };

  var el = function (tag, cls, text) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text) n.textContent = text;
    return n;
  };

  var addMessage = function (role, text, extra) {
    var wrap = el('div', 'bono__msg bono__msg--' + role);
    wrap.appendChild(el('p', 'bono__bubble', text));
    if (extra && extra.sources && extra.sources.length) {
      var src = el('ul', 'bono__sources');
      extra.sources.forEach(function (s) {
        var li = document.createElement('li');
        var a = el('a', '', s.title || s.url);
        a.href = s.url;
        a.rel = 'noopener';
        if (/^https?:\/\/(wa\.me|api\.whatsapp)/.test(s.url)) a.target = '_blank';
        li.appendChild(a);
        src.appendChild(li);
      });
      wrap.appendChild(src);
    }
    if (extra && extra.id) {
      var fb = el('div', 'bono__feedback');
      fb.appendChild(el('span', '', 'Was this helpful?'));
      [['Yes', 1], ['No', 0]].forEach(function (b) {
        var btn = el('button', 'bono__fbtn', b[0]);
        btn.type = 'button';
        btn.addEventListener('click', function () {
          send('feedback', { id: extra.id, helpful: b[1] });
          fb.textContent = 'Thank you for telling me.';
        });
        fb.appendChild(btn);
      });
      wrap.appendChild(fb);
    }
    if (extra && extra.suggestions && extra.suggestions.length) {
      var chips = el('div', 'bono__chips');
      extra.suggestions.forEach(function (q) {
        var c = el('button', 'bono__chip', q);
        c.type = 'button';
        c.addEventListener('click', function () { ask(q); });
        chips.appendChild(c);
      });
      wrap.appendChild(chips);
    }
    log.appendChild(wrap);
    scroll();
    return wrap;
  };

  var post = function (url, data) {
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': tokenEl ? tokenEl.getAttribute('content') : '' },
      body: JSON.stringify(data)
    }).then(function (r) { return r.json().catch(function () { return { ok: false }; }).then(function (j) { j.status = r.status; return j; }); });
  };
  var send = function (what, data) { post(root.getAttribute('data-feedback'), data).catch(function () {}); };

  var ask = function (q) {
    q = (q || '').trim();
    if (!q || busy) return;
    busy = true;
    addMessage('user', q);
    var past = history.slice(-6);
    history.push({ role: 'user', text: q });
    input.value = '';
    var typing = addMessage('bot', 'Bono is thinking...');
    typing.classList.add('is-typing');
    post(root.getAttribute('data-ask'), { question: q, history: past }).then(function (j) {
      typing.remove();
      if (j && j.ok) {
        addMessage('bot', j.answer, { sources: j.sources, id: j.id, suggestions: j.suggestions });
        history.push({ role: 'bot', text: j.answer });
      } else {
        var m = (j && j.error) || 'Sorry, I could not answer just now. Please try again, or contact the team.';
        addMessage('bot', m);
      }
    }).catch(function () {
      typing.remove();
      addMessage('bot', 'I could not reach the website just now. Please check your connection and try again.');
    }).then(function () { busy = false; save(); input.focus(); });
  };

  var show = function () {
    panel.hidden = false;
    root.classList.add('is-open');
    launch.setAttribute('aria-expanded', 'true');
    if (!greeted) {
      greeted = true;
      var chips = [];
      try { chips = JSON.parse(root.getAttribute('data-suggest') || '[]'); } catch (e) { chips = []; }
      restore();
      if (history.length) {
        history.forEach(function (m) { addMessage(m.role === 'user' ? 'user' : 'bot', m.text); });
      } else {
        addMessage('bot', root.getAttribute('data-greeting') || 'Hello! How can I help?', { suggestions: chips });
      }
    }
    setTimeout(function () { input.focus(); }, 30);
  };
  var hide = function () {
    panel.hidden = true;
    root.classList.remove('is-open');
    launch.setAttribute('aria-expanded', 'false');
    launch.focus();
  };

  launch.addEventListener('click', function () { if (panel.hidden) show(); else hide(); });
  closeBtn.addEventListener('click', hide);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !panel.hidden) hide(); });
  form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
})();
