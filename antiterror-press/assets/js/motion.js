/**
 * Front-end motion for WordPress.
 *
 * Enqueued as `at-motion` with defer, before `at-interact`.
 * interact.js calls atMotion.enter() for search rows and
 * atMotion.swap() when the REST feed replaces a list.
 * The first screen only translates. Opacity fades are reserved
 * for blocks that enter later, so the lead story can paint.
 */
(function () {
  var doc = document.documentElement;
  var media = window.matchMedia ? window.matchMedia("(prefers-reduced-motion: reduce)") : null;

  function reduced() {
    return !!(media && media.matches);
  }

  function sync() {
    if (reduced()) doc.removeAttribute("data-motion");
    else doc.setAttribute("data-motion", "on");
  }

  sync();
  if (media && media.addEventListener) media.addEventListener("change", sync);

  function listOf(nodes) {
    if (!nodes) return [];
    if (nodes.length === undefined || nodes.nodeType === 1) return [nodes];
    return Array.prototype.slice.call(nodes);
  }

  function enter(nodes, options) {
    var items = listOf(nodes);
    var mode = options && options.mode === "settle" ? "settle" : "rise";
    var step = options && options.step ? options.step : 42;
    if (!items.length) return;
    if (reduced() || mode === "settle") {
      items.forEach(function (node, index) {
        if (node.classList.contains("at-settle") || node.classList.contains("is-in")) return;
        if (!reduced() && mode === "settle") {
          node.style.setProperty("--at-delay", Math.min(index, 8) * step + "ms");
          node.classList.add("at-settle");
        }
        node.classList.add("is-in");
      });
      return;
    }
    items.forEach(function (node, index) {
      if (node.classList.contains("at-enter") || node.classList.contains("is-in")) return;
      node.style.setProperty("--at-delay", Math.min(index, 8) * step + "ms");
      node.classList.add("at-enter");
      window.requestAnimationFrame(function () {
        window.requestAnimationFrame(function () {
          node.classList.add("is-in");
        });
      });
    });
  }

  function watch(selector) {
    var nodes = Array.prototype.slice.call(document.querySelectorAll(selector));
    if (!nodes.length) return;
    if (reduced() || !("IntersectionObserver" in window)) {
      nodes.forEach(function (node) { node.classList.add("is-in"); });
      return;
    }
    var waiting = [];
    var seen = [];
    var observer = new IntersectionObserver(function (entries) {
      var batch = [];
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        observer.unobserve(entry.target);
        batch.push(entry.target);
      });
      if (batch.length) enter(batch, { mode: "rise" });
    }, { threshold: 0.2, rootMargin: "0px 0px -10% 0px" });
    nodes.forEach(function (node) {
      var rect = node.getBoundingClientRect();
      var onScreen = rect.top < window.innerHeight * 0.92 && rect.bottom > 0;
      if (onScreen) seen.push(node);
      else waiting.push(node);
    });
    if (seen.length) enter(seen, { mode: "settle" });
    waiting.forEach(function (node) { observer.observe(node); });
  }

  function swap(update) {
    if (reduced() || typeof document.startViewTransition !== "function") {
      update();
      return;
    }
    document.startViewTransition(function () { update(); });
  }

  window.atMotion = {
    enter: enter,
    watch: watch,
    swap: swap,
    reduced: reduced
  };

  watch(".page-hero, .feature, .lead, .story, .person-card, .side-story, .row, .siblings__link, .vehi-rail__item, .share, .fund-lead, .fund-row, .fund-line, .issue-cell, .rubric, .section-card");

  window.setTimeout(function () {
    document.querySelectorAll(".at-enter:not(.is-in)").forEach(function (node) {
      node.classList.add("is-in");
    });
  }, 1500);
})();
