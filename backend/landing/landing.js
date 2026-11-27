(function () {
  'use strict';
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // Loader percentage
  var loaderPct = document.querySelector('.loader span');
  var pct = 0;
  var loaderTimer = setInterval(function () {
    pct = Math.min(pct + 9, 99);
    if (loaderPct) loaderPct.textContent = pct + '%';
  }, 40);
  function done() {
    clearInterval(loaderTimer);
    if (loaderPct) loaderPct.textContent = '100%';
    setTimeout(function () { document.documentElement.classList.add('loaded'); }, 150);
  }
  if (document.readyState === 'complete') done(); else window.addEventListener('load', done);
  setTimeout(done, 2500);

  // Split headings into words for blur reveal
  document.querySelectorAll('.blur-words').forEach(function (el) {
    var i = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (child) {
        if (child.nodeType === 3) {
          var frag = document.createDocumentFragment();
          child.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
            var s = document.createElement('span');
            s.className = 'w';
            s.style.transitionDelay = (i++ * 0.06) + 's';
            s.textContent = part;
            frag.appendChild(s);
          });
          node.replaceChild(frag, child);
        } else if (child.nodeType === 1) {
          walk(child);
        }
      });
    })(el);
  });

  // Typewriter eyebrows
  function typeOut(el) {
    var tw = el.querySelector('.tw');
    if (!tw || el.dataset.typed) return;
    el.dataset.typed = '1';
    var text = tw.getAttribute('data-text') || '';
    if (reduce) { tw.textContent = text; el.classList.add('done'); return; }
    var n = 0;
    var t = setInterval(function () {
      tw.textContent = text.slice(0, ++n);
      if (n >= text.length) { clearInterval(t); setTimeout(function () { el.classList.add('done'); }, 1200); }
    }, 45);
  }

  // Count-up numbers
  function countUp(el) {
    if (el.dataset.counted) return;
    el.dataset.counted = '1';
    var target = parseFloat(el.getAttribute('data-count'));
    if (reduce) { el.textContent = target; return; }
    var start = null, dur = 1400;
    function step(ts) {
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3)));
      if (p < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
  }

  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (e) {
      if (!e.isIntersecting) return;
      var el = e.target;
      el.classList.add('in');
      if (el.classList.contains('eyebrow')) typeOut(el);
      el.querySelectorAll('[data-count]').forEach(countUp);
      if (el.hasAttribute('data-count')) countUp(el);
      io.unobserve(el);
    });
  }, { threshold: 0.2, rootMargin: '0px 0px -40px 0px' });

  var animated = Array.prototype.slice.call(document.querySelectorAll('.reveal, .blur-words, .eyebrow, .mock, .stats, [data-anim]'));
  animated.forEach(function (el) { io.observe(el); });

  // Fallback: after a fast scroll or anchor jump, show anything already scrolled past
  var sweepQueued = false;
  function sweep() {
    sweepQueued = false;
    animated = animated.filter(function (el) {
      if (el.classList.contains('in')) return false;
      if (el.getBoundingClientRect().top < window.innerHeight) {
        el.classList.add('in');
        if (el.classList.contains('eyebrow')) typeOut(el);
        el.querySelectorAll('[data-count]').forEach(countUp);
        io.unobserve(el);
        return false;
      }
      return true;
    });
  }
  window.addEventListener('scroll', function () {
    if (!sweepQueued) { sweepQueued = true; setTimeout(sweep, 200); }
  }, { passive: true });

  // Rotating hero word
  var rot = document.querySelector('[data-rotate]');
  if (rot && !reduce) {
    var words = rot.getAttribute('data-rotate').split('|'), wi = 0;
    setInterval(function () {
      wi = (wi + 1) % words.length;
      rot.style.opacity = 0;
      setTimeout(function () { rot.textContent = words[wi]; rot.style.opacity = 1; }, 250);
    }, 2600);
  }

  // Feature tabs
  var tabs = document.querySelectorAll('.tab');
  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.tabIndex = on ? 0 : -1;
        document.getElementById(t.getAttribute('aria-controls')).hidden = !on;
      });
    });
    tab.addEventListener('keydown', function (e) {
      var list = Array.prototype.slice.call(tabs), i = list.indexOf(tab);
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        var next = list[(i + (e.key === 'ArrowRight' ? 1 : list.length - 1)) % list.length];
        next.focus(); next.click();
      }
    });
  });

  // Mobile menu
  var header = document.querySelector('.site-header');
  var toggle = document.querySelector('.menu-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = header.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    header.querySelectorAll('.nav-links a').forEach(function (a) {
      a.addEventListener('click', function () { header.classList.remove('open'); toggle.setAttribute('aria-expanded', 'false'); });
    });
  }

  // Active nav link on scroll
  var links = document.querySelectorAll('.nav-links a[href^="#"]');
  var sections = Array.prototype.map.call(links, function (a) { return document.querySelector(a.getAttribute('href')); });
  function onScroll() {
    var y = window.scrollY + 140, current = links[0];
    sections.forEach(function (s, i) { if (s && s.offsetTop <= y) current = links[i]; });
    links.forEach(function (a) { a.classList.toggle('active', a === current); });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Contact form: open the visitor's email app with the message filled in
  var form = document.getElementById('contact-form');
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (form.website && form.website.value) return; // honeypot
      var to = form.getAttribute('data-to');
      var body = 'Name: ' + form.name.value + '\nSchool: ' + form.school.value + '\nEmail: ' + form.email.value +
        '\nLearners: ' + form.learners.value + '\n\n' + form.message.value;
      window.location.href = 'mailto:' + to + '?subject=' + encodeURIComponent(form.subject.value || 'ShuleTech enquiry') +
        '&body=' + encodeURIComponent(body);
      var note = form.querySelector('.form-note');
      if (note) note.textContent = 'Your email app should now open with your message. If it does not, email us at ' + to + '.';
    });
  }

  var y = document.getElementById('year');
  if (y) y.textContent = new Date().getFullYear();
})();
