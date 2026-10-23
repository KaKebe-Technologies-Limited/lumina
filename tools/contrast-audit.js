// Dev helper (not loaded by the site): paste into the browser console on localhost, or run through the browser tool.
// window.__audit(['/lumina/', '/lumina/about'], 1200) -> loads each page in a same-origin iframe of the given width and lists text whose
// colour contrast against the nearest solid background is below WCAG AA (4.5:1, 3:1 for large text). Text over images/gradients is reported as "photo".
(function () {
  const parse = c => { const m = c.match(/rgba?\(([^)]+)\)/); if (!m) return null; const p = m[1].split(/[ ,\/]+/).filter(Boolean).map(Number); return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 }; };
  const lum = c => { const f = v => { v /= 255; return v <= .03928 ? v / 12.92 : Math.pow((v + .055) / 1.055, 2.4); }; return .2126 * f(c.r) + .7152 * f(c.g) + .0722 * f(c.b); };
  const ratio = (a, b) => { const x = lum(a), y = lum(b); return (Math.max(x, y) + .05) / (Math.min(x, y) + .05); };
  const over = (f, b) => ({ r: f.r * f.a + b.r * (1 - f.a), g: f.g * f.a + b.g * (1 - f.a), b: f.b * f.a + b.b * (1 - f.a), a: 1 });
  function bgOf(el, win) {
    let layers = [], photo = false;
    for (let e = el; e; e = e.parentElement) {
      const cs = win.getComputedStyle(e);
      if (cs.backgroundImage && cs.backgroundImage !== 'none') { photo = true; break; }
      const c = parse(cs.backgroundColor);
      if (c && c.a > 0) { layers.push(c); if (c.a >= 1) break; }
    }
    let base = { r: 255, g: 255, b: 255, a: 1 };
    for (let i = layers.length - 1; i >= 0; i--) base = over(layers[i], base);
    return { c: base, photo };
  }
  function auditDoc(doc) {
    const win = doc.defaultView, out = [], seen = new Set();
    const w = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);
    let n;
    while ((n = w.nextNode())) {
      const t = n.nodeValue.trim(); if (!t) continue;
      const el = n.parentElement; if (!el || /^(SCRIPT|STYLE|NOSCRIPT)$/.test(el.tagName)) continue;
      const cs = win.getComputedStyle(el);
      if (cs.visibility === 'hidden' || cs.display === 'none' || +cs.opacity === 0) continue;
      const r = el.getBoundingClientRect(); if (!r.width || !r.height) continue;
      let op = 1; for (let e = el; e; e = e.parentElement) op *= +win.getComputedStyle(e).opacity;
      if (op < .05) continue;
      const fg = parse(cs.color); if (!fg) continue;
      const { c: bg, photo } = bgOf(el, win);
      const f = over({ ...fg, a: fg.a * Math.min(1, op) }, bg);
      const size = parseFloat(cs.fontSize), bold = parseInt(cs.fontWeight) >= 700;
      const large = size >= 24 || (size >= 18.66 && bold);
      const need = large ? 3 : 4.5, ra = ratio(f, bg);
      if (ra < need) {
        const key = el.className + '|' + t.slice(0, 20); if (seen.has(key)) continue; seen.add(key);
        out.push({ cls: (el.tagName + '.' + String(el.className).split(' ').join('.')).slice(0, 70), text: t.slice(0, 30), ratio: +ra.toFixed(2), need, photo, fg: cs.color, bg: `rgb(${bg.r | 0},${bg.g | 0},${bg.b | 0})` });
      }
    }
    return { out, hscroll: doc.documentElement.scrollWidth > doc.documentElement.clientWidth + 1 };
  }
  window.__audit = async function (urls, width) {
    const res = {};
    for (const u of urls) {
      const fr = document.createElement('iframe');
      fr.style.cssText = `position:fixed;left:-9999px;top:0;width:${width}px;height:900px;border:0`;
      fr.src = u; document.body.appendChild(fr);
      await new Promise(ok => { fr.onload = ok; setTimeout(ok, 8000); });
      await new Promise(ok => setTimeout(ok, 500));
      try { const d = fr.contentDocument; d.querySelectorAll('[data-reveal],[class*=reveal]').forEach(e => { e.style.opacity = 1; e.style.transform = 'none'; }); res[u] = auditDoc(d); }
      catch (e) { res[u] = { error: String(e) }; }
      fr.remove();
    }
    return res;
  };
})();
