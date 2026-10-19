(function () {
  var themeToggle = document.querySelector("[data-theme-toggle]");

  function paintTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    var meta = document.querySelector("[data-theme-color]");
    if (meta) meta.setAttribute("content", theme === "dark" ? "#141311" : "#f3efe6");
    if (!themeToggle) return;
    var dark = theme === "dark";
    themeToggle.setAttribute("aria-pressed", dark ? "true" : "false");
    var label = themeToggle.querySelector("[data-theme-label]");
    if (label) label.textContent = dark ? "Светлая тема" : "Тёмная тема";
  }

  paintTheme(document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light");
  if (themeToggle) {
    themeToggle.addEventListener("click", function () {
      var next = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
      try { localStorage.setItem("at-theme", next); } catch (e) {}
      paintTheme(next);
    });
  }

  var mast = document.querySelector("[data-mast]");
  var navToggle = document.querySelector("[data-nav-toggle]");
  var nav = document.querySelector("[data-nav]");
  var searchOpen = document.querySelector("[data-search-open]");
  var searchLayer = document.querySelector("[data-search]");
  var searchClose = document.querySelector("[data-search-close]");
  var searchInput = searchLayer ? searchLayer.querySelector("input[type='search']") : null;

  function setNav(open) {
    if (!nav || !navToggle) return;
    nav.classList.toggle("is-open", open);
    navToggle.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.classList.toggle("is-nav", open);
    if (open) setSearch(false);
  }

  if (navToggle) {
    navToggle.addEventListener("click", function () {
      setNav(!nav.classList.contains("is-open"));
    });
  }
  if (nav) {
    nav.addEventListener("click", function (event) {
      if (event.target.closest("a")) setNav(false);
    });
  }
  document.addEventListener("click", function (event) {
    if (!nav || !nav.classList.contains("is-open")) return;
    if (nav.contains(event.target) || (navToggle && navToggle.contains(event.target))) return;
    setNav(false);
  });

  function setSearch(open) {
    if (!searchLayer) return;
    if (open) setNav(false);
    var wasOpen = !searchLayer.hidden;
    searchLayer.hidden = !open;
    document.body.classList.toggle("is-locked", open);
    if (open && searchInput) searchInput.focus();
    if (!open && wasOpen && searchOpen) searchOpen.focus();
  }

  if (searchOpen) searchOpen.addEventListener("click", function () { setSearch(true); });
  if (searchClose) searchClose.addEventListener("click", function () { setSearch(false); });
  if (searchLayer) {
    searchLayer.addEventListener("click", function (event) {
      if (event.target === searchLayer) setSearch(false);
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      setSearch(false);
      setNav(false);
    }
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      setSearch(true);
    }
  });

  var bar = document.querySelector("[data-progress]");
  var article = document.querySelector("[data-article]");
  var scrollTicking = false;
  function paintProgress() {
    if (!bar || !article) return;
    var rect = article.getBoundingClientRect();
    var total = article.offsetHeight - window.innerHeight;
    var passed = Math.min(Math.max(-rect.top, 0), Math.max(total, 0));
    var progress = total > 0 ? passed / total : 0;
    bar.style.transform = "scaleX(" + progress + ")";
  }
  function onScrollFrame() {
    scrollTicking = false;
    if (mast) mast.classList.toggle("is-stuck", window.scrollY > 8);
    paintProgress();
  }
  document.addEventListener("scroll", function () {
    if (scrollTicking) return;
    scrollTicking = true;
    requestAnimationFrame(onScrollFrame);
  }, { passive: true });
  window.addEventListener("resize", paintProgress);
  onScrollFrame();

  var input = document.querySelector("[data-fighter-search]");
  if (input) {
    var cards = Array.prototype.slice.call(document.querySelectorAll("[data-fighter]"));
    var groups = Array.prototype.slice.call(document.querySelectorAll("[data-letter]"));
    var count = document.querySelector("[data-fighter-count]");
    var empty = document.querySelector("[data-fighter-empty]");

    var plural = function (n) {
      var n10 = n % 10;
      var n100 = n % 100;
      if (n10 === 1 && n100 !== 11) return n + " боец";
      if (n10 >= 2 && n10 <= 4 && (n100 < 12 || n100 > 14)) return n + " бойца";
      return n + " бойцов";
    };

    var filter = function () {
      var query = input.value.trim().toLowerCase();
      var visible = 0;
      cards.forEach(function (card) {
        var hay = (card.getAttribute("data-hay") || card.textContent || "").toLowerCase();
        var show = !query || hay.indexOf(query) !== -1;
        card.hidden = !show;
        if (show) visible += 1;
      });
      groups.forEach(function (group) {
        var any = Array.prototype.some.call(group.querySelectorAll("[data-fighter]"), function (card) {
          return !card.hidden;
        });
        group.hidden = !any;
      });
      if (count) count.textContent = plural(visible);
      if (empty) empty.hidden = visible !== 0;
    };

    input.addEventListener("input", filter);
    filter();
  }

  var lazyVideos = document.querySelectorAll("video[data-src]");
  if (lazyVideos.length && "IntersectionObserver" in window) {
    var videoObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        var video = entry.target;
        if (!video.getAttribute("src")) video.src = video.getAttribute("data-src");
        videoObserver.unobserve(video);
      });
    }, { rootMargin: "240px" });
    Array.prototype.forEach.call(lazyVideos, function (video) { videoObserver.observe(video); });
  } else {
    Array.prototype.forEach.call(lazyVideos, function (video) {
      video.src = video.getAttribute("data-src");
    });
  }

  if (window.atView && window.atView.id && window.atView.endpoint) {
    fetch(window.atView.endpoint, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": window.atView.nonce || ""
      },
      credentials: "same-origin",
      keepalive: true,
      body: JSON.stringify({ id: window.atView.id })
    }).catch(function () {});
  }

  var archive = document.querySelector("[data-archive]");
  var lightbox = document.querySelector("[data-lightbox]");
  var tiles = Array.prototype.slice.call(document.querySelectorAll("[data-vault-item]"));
  if (lightbox && tiles.length && typeof lightbox.showModal === "function") {
    var stage = lightbox.querySelector("[data-lightbox-stage]");
    var current = 0;
    var scope = "all";
    var kind = "all";

    var plural = function (n) {
      var n10 = n % 10;
      var n100 = n % 100;
      if (n10 === 1 && n100 !== 11) return n + " материал";
      if (n10 >= 2 && n10 <= 4 && (n100 < 12 || n100 > 14)) return n + " материала";
      return n + " материалов";
    };

    var visibleTiles = function () {
      return tiles.filter(function (tile) { return !tile.hidden; });
    };

    var applyVault = function () {
      if (!archive) return;
      var search = archive.querySelector("[data-vault-search]");
      var query = search ? search.value.trim().toLowerCase() : "";
      var shown = 0;
      tiles.forEach(function (tile) {
        if (!archive.contains(tile)) return;
        var scopeOk = scope === "all" || tile.getAttribute("data-shelf") === scope;
        var typeOk = kind === "all" || tile.getAttribute("data-type") === kind;
        var hay = (tile.getAttribute("data-hay") || "").toLowerCase();
        var show = scopeOk && typeOk && (!query || hay.indexOf(query) !== -1);
        tile.hidden = !show;
        if (show) shown += 1;
      });
      Array.prototype.forEach.call(archive.querySelectorAll("[data-chapter], section[data-shelf]"), function (block) {
        block.hidden = !block.querySelector("[data-vault-item]:not([hidden])");
      });
      var count = archive.querySelector("[data-vault-count]");
      var empty = archive.querySelector("[data-vault-empty]");
      if (count) count.textContent = plural(shown);
      if (empty) empty.hidden = shown !== 0;
    };

    var bindGroup = function (selector, read) {
      if (!archive) return;
      var buttons = Array.prototype.slice.call(archive.querySelectorAll(selector));
      buttons.forEach(function (button) {
        button.addEventListener("click", function () {
          var value = read(button);
          if (selector.indexOf("scope") !== -1) scope = value;
          else kind = value;
          buttons.forEach(function (item) {
            var on = item === button;
            item.classList.toggle("is-on", on);
            item.setAttribute("aria-pressed", on ? "true" : "false");
          });
          applyVault();
        });
      });
    };

    bindGroup("[data-vault-scope]", function (button) { return button.getAttribute("data-vault-scope") || "all"; });
    bindGroup("[data-vault-type]", function (button) { return button.getAttribute("data-vault-type") || "all"; });
    if (archive) {
      var search = archive.querySelector("[data-vault-search]");
      if (search) search.addEventListener("input", applyVault);
      applyVault();
    }

    var render = function (tile) {
      if (!tile) return;
      stage.innerHTML = "";
      var src = tile.getAttribute("data-src");
      var type = tile.getAttribute("data-type");
      if (type === "video") {
        var video = document.createElement("video");
        video.controls = true;
        video.autoplay = true;
        video.playsInline = true;
        video.src = src;
        stage.appendChild(video);
      } else {
        var image = document.createElement("img");
        image.src = src;
        image.alt = tile.getAttribute("data-title") || "";
        stage.appendChild(image);
      }
      lightbox.querySelector("[data-lightbox-kicker]").textContent = tile.getAttribute("data-kicker") || "";
      lightbox.querySelector("[data-lightbox-title]").textContent = tile.getAttribute("data-title") || "";
      lightbox.querySelector("[data-lightbox-link]").href = tile.getAttribute("data-href") || "#";
    };

    var openAt = function (tile) {
      var list = visibleTiles();
      current = Math.max(0, list.indexOf(tile));
      render(list[current] || tile);
      if (!lightbox.open) lightbox.showModal();
    };

    var step = function (dir) {
      var list = visibleTiles();
      if (!list.length) return;
      current = (current + dir + list.length) % list.length;
      render(list[current]);
    };

    tiles.forEach(function (tile) {
      tile.addEventListener("click", function () { openAt(tile); });
    });
    lightbox.querySelector("[data-lightbox-close]").addEventListener("click", function () {
      lightbox.close();
    });
    lightbox.querySelector("[data-lightbox-prev]").addEventListener("click", function () { step(-1); });
    lightbox.querySelector("[data-lightbox-next]").addEventListener("click", function () { step(1); });
    lightbox.addEventListener("close", function () {
      stage.innerHTML = "";
    });
    lightbox.addEventListener("keydown", function (event) {
      if (event.key === "ArrowRight") step(1);
      if (event.key === "ArrowLeft") step(-1);
    });
  }
})();

  // ===== BPLA events: filters + expand =====
  var bplaSection = document.querySelector(".bpla-section");
  if (bplaSection) {
    // Filters
    bplaSection.addEventListener("click", function (e) {
      var filterBtn = e.target.closest(".bpla-filter");
      if (filterBtn) {
        var filter = filterBtn.getAttribute("data-filter");
        bplaSection.querySelectorAll(".bpla-filter").forEach(function (b) {
          var on = b === filterBtn;
          b.classList.toggle("is-active", on);
          b.setAttribute("aria-pressed", on ? "true" : "false");
        });
        bplaSection.querySelectorAll(".bpla-item").forEach(function (item) {
          var status = item.getAttribute("data-status") || "";
          var show = filter === "all" || status === filter;
          item.classList.toggle("is-hidden", !show);
        });
        return;
      }

      // Expand / collapse
      var toggle = e.target.closest(".bpla-item__toggle");
      if (toggle) {
        var item = toggle.closest(".bpla-item");
        var details = item.querySelector(".bpla-item__details");
        var isOpen = item.classList.contains("is-open");
        item.classList.toggle("is-open", !isOpen);
        toggle.setAttribute("aria-expanded", !isOpen ? "true" : "false");
        if (details) details.hidden = isOpen;
      }
    });
  }

  // ===== Hero album lightbox with navigation =====
  var albumItems = Array.prototype.slice.call(document.querySelectorAll(".album-item"));
  if (albumItems.length) {
    var lb = document.createElement("div");
    lb.className = "album-lightbox";
    lb.setAttribute("role", "dialog");
    lb.setAttribute("aria-modal", "true");
    lb.innerHTML = [
      '<button type="button" class="album-lightbox__close" aria-label="Закрыть">×</button>',
      '<button type="button" class="album-lightbox__prev" aria-label="Предыдущее">‹</button>',
      '<button type="button" class="album-lightbox__next" aria-label="Следующее">›</button>',
      '<div class="album-lightbox__stage"><div data-stage></div></div>',
      '<div class="album-lightbox__counter" data-counter></div>'
    ].join("");
    document.body.appendChild(lb);

    var stage = lb.querySelector("[data-stage]");
    var counter = lb.querySelector("[data-counter]");
    var closeBtn = lb.querySelector(".album-lightbox__close");
    var prevBtn = lb.querySelector(".album-lightbox__prev");
    var nextBtn = lb.querySelector(".album-lightbox__next");
    var current = 0;

    function render(index) {
      current = (index + albumItems.length) % albumItems.length;
      var item = albumItems[current];
      var full = item.getAttribute("data-full");
      var type = item.getAttribute("data-type");
      stage.innerHTML = "";
      if (type === "video") {
        var v = document.createElement("video");
        v.src = full;
        v.controls = true;
        v.autoplay = true;
        v.setAttribute("playsinline", "");
        stage.appendChild(v);
      } else {
        var img = document.createElement("img");
        img.src = full;
        img.alt = item.getAttribute("aria-label") || "";
        stage.appendChild(img);
      }
      counter.textContent = (current + 1) + " / " + albumItems.length;
      prevBtn.style.visibility = albumItems.length > 1 ? "visible" : "hidden";
      nextBtn.style.visibility = albumItems.length > 1 ? "visible" : "hidden";
    }

    function openAt(index) {
      render(index);
      lb.classList.add("is-open");
      document.body.classList.add("is-locked");
      closeBtn.focus();
    }
    function close() {
      lb.classList.remove("is-open");
      document.body.classList.remove("is-locked");
      stage.innerHTML = "";
    }

    albumItems.forEach(function (item, i) {
      item.addEventListener("click", function () { openAt(i); });
    });
    closeBtn.addEventListener("click", close);
    prevBtn.addEventListener("click", function () { render(current - 1); });
    nextBtn.addEventListener("click", function () { render(current + 1); });
    lb.addEventListener("click", function (e) { if (e.target === lb) close(); });
    document.addEventListener("keydown", function (e) {
      if (!lb.classList.contains("is-open")) return;
      if (e.key === "Escape") close();
      if (e.key === "ArrowLeft") render(current - 1);
      if (e.key === "ArrowRight") render(current + 1);
    });
  }

  // ===== Вехи истории: поиск в своём указателе (плагин ищет сам) =====
  var vehi = document.querySelector("[data-vehi]");
  if (vehi) {
    var vehiInput = vehi.querySelector("[data-vehi-search]");
    var vehiEmpty = vehi.querySelector("[data-vehi-empty]");
    var vehiCount = vehi.querySelector("[data-vehi-count]");
    var vehiCards = Array.prototype.slice.call(vehi.querySelectorAll("[data-vehi-card]"));
    var vehiGroups = Array.prototype.slice.call(vehi.querySelectorAll("[data-vehi-group]"));
    var vehiLetters = Array.prototype.slice.call(vehi.querySelectorAll("[data-vehi-letter]"));

    function vehiFold(value) {
      return String(value || "")
        .toLocaleLowerCase()
        .replace(/ё/g, "е")
        .replace(/[«»"'`]/g, "")
        .replace(/[–—−-]/g, " ")
        .replace(/\s+/g, " ")
        .trim();
    }

    function vehiWord(n) {
      var n10 = n % 10;
      var n100 = n % 100;
      if (n10 === 1 && n100 !== 11) return "событие";
      if (n10 >= 2 && n10 <= 4 && (n100 < 12 || n100 > 14)) return "события";
      return "событий";
    }

    function vehiFilter() {
      var query = vehiFold(vehiInput ? vehiInput.value : "");
      var visible = 0;
      vehiCards.forEach(function (card) {
        var hay = vehiFold(card.getAttribute("data-hay"));
        var show = !query || hay.indexOf(query) !== -1;
        card.hidden = !show;
        if (show) visible += 1;
      });
      vehiGroups.forEach(function (group) {
        var any = !!group.querySelector("[data-vehi-card]:not([hidden])");
        group.hidden = !any;
        var letter = group.getAttribute("data-vehi-group");
        vehiLetters.forEach(function (link) {
          if (link.getAttribute("data-vehi-letter") !== letter) return;
          link.classList.toggle("is-empty", !any);
        });
      });
      if (vehiEmpty) vehiEmpty.hidden = visible !== 0;
      if (vehiCount) vehiCount.textContent = visible ? (visible + " " + vehiWord(visible)) : "";
    }

    if (vehiInput) vehiInput.addEventListener("input", vehiFilter);
    vehiFilter();
  }
