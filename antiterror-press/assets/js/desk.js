/**
 * Компактная шапка, оглавление материала, кольцо прокрутки, нижняя панель.
 */
(function () {
  var mast = document.querySelector("[data-mast]");
  var root = document.documentElement;
  var reduce = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function syncHeader() {
    if (!mast) return;
    root.style.setProperty("--header", mast.offsetHeight + "px");
  }

  var write = document.querySelector(".desk-write");

  function paintScroll() {
    var max = root.scrollHeight - window.innerHeight;
    var progress = max > 0 ? Math.min(1, window.scrollY / max) : 0;
    root.style.setProperty("--scroll", String(Math.round(progress * 100)));
    if (mast) mast.classList.toggle("is-compact", window.scrollY > 72);
    if (write) write.hidden = window.scrollY < 280;
    syncHeader();
  }

  var frame = false;
  function onScroll() {
    if (frame) return;
    frame = true;
    window.requestAnimationFrame(function () {
      frame = false;
      paintScroll();
    });
  }

  document.addEventListener("scroll", onScroll, { passive: true });
  window.addEventListener("resize", paintScroll);
  paintScroll();

  var dockMenu = document.querySelector("[data-dock-menu]");
  var navToggle = document.querySelector("[data-nav-toggle]");
  if (dockMenu && navToggle) {
    dockMenu.addEventListener("click", function () {
      navToggle.click();
    });
  }

  var dockSearch = document.querySelector("[data-dock-search]");
  var searchOpen = document.querySelector(".mast [data-search-open]");
  if (dockSearch && searchOpen) {
    dockSearch.addEventListener("click", function () {
      searchOpen.click();
    });
  }

  var article = document.querySelector("[data-article] .prose");
  var toc = document.querySelector("[data-toc]");
  var list = toc ? toc.querySelector("[data-toc-list]") : null;
  if (article && toc && list) {
    var heads = Array.prototype.slice.call(article.querySelectorAll("h2, h3"));
    if (heads.length >= 2) {
      var used = Object.create(null);
      heads.forEach(function (head, index) {
        var text = (head.textContent || "").replace(/\s+/g, " ").trim();
        if (!text) return;
        if (!head.id) {
          var base = text.toLowerCase().replace(/[^a-z0-9а-яё]+/gi, "-").replace(/^-+|-+$/g, "") || "part";
          var id = base;
          var n = 2;
          while (used[id] || document.getElementById(id)) {
            id = base + "-" + n;
            n += 1;
          }
          used[id] = true;
          head.id = id;
        }
        var item = document.createElement("li");
        if (head.tagName === "H3") item.className = "toc__sub";
        var link = document.createElement("a");
        link.href = "#" + head.id;
        link.textContent = text;
        link.setAttribute("data-toc-link", String(index));
        item.appendChild(link);
        list.appendChild(item);
      });
      if (list.children.length >= 2) {
        toc.hidden = false;
        var links = Array.prototype.slice.call(list.querySelectorAll("a"));
        if ("IntersectionObserver" in window) {
          var current = -1;
          var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
              if (!entry.isIntersecting) return;
              var next = heads.indexOf(entry.target);
              if (next < 0 || next === current) return;
              current = next;
              links.forEach(function (link, index) {
                if (index === current) link.setAttribute("aria-current", "true");
                else link.removeAttribute("aria-current");
              });
            });
          }, { rootMargin: "-20% 0px -70% 0px", threshold: 0 });
          heads.forEach(function (head) { observer.observe(head); });
        }
        if (!reduce) {
          list.addEventListener("click", function (event) {
            var link = event.target.closest ? event.target.closest("a") : null;
            if (!link) return;
            var target = document.getElementById((link.getAttribute("href") || "").slice(1));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: "smooth", block: "start" });
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, "", link.getAttribute("href"));
            }
          });
        }
      }
    }
  }
})();
