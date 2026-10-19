(function () {
  "use strict";

  document.documentElement.classList.add("has-js");

  var interact = window.atInteract || {};
  var i18n = interact.i18n || {};
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function qs(sel, root) {
    return (root || document).querySelector(sel);
  }

  function qsa(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function on(el, ev, fn, opts) {
    if (el) el.addEventListener(ev, fn, opts);
  }

  function debounce(fn, wait) {
    var t;
    return function () {
      var ctx = this;
      var args = arguments;
      clearTimeout(t);
      t = setTimeout(function () { fn.apply(ctx, args); }, wait);
    };
  }

  function rest(path, options) {
    options = options || {};
    var base = interact.restUrl || "";
    var url = base + path.replace(/^\//, "");
    if (options.params) {
      var query = Object.keys(options.params)
        .filter(function (key) {
          return options.params[key] !== undefined && options.params[key] !== null && options.params[key] !== "";
        })
        .map(function (key) {
          return encodeURIComponent(key) + "=" + encodeURIComponent(options.params[key]);
        })
        .join("&");
      if (query) url += (url.indexOf("?") >= 0 ? "&" : "?") + query;
    }
    var headers = {
      "X-WP-Nonce": interact.nonce || "",
      Accept: "application/json"
    };
    var body = options.body;
    if (body && typeof body === "object") {
      headers["Content-Type"] = "application/json";
      body = JSON.stringify(body);
    }
    return fetch(url, {
      method: options.method || "GET",
      credentials: "same-origin",
      headers: headers,
      body: body || undefined
    }).then(function (res) {
      return res.json().then(function (data) {
        if (!res.ok) {
          var err = data || {};
          err.status = res.status;
          throw err;
        }
        return data;
      });
    });
  }

  /* ---------- theme toggle ---------- */

  var themeToggle = qs("[data-theme-toggle]");

  function paintTheme(theme) {
    document.documentElement.setAttribute("data-theme", theme);
    var meta = qs("[data-theme-color]");
    if (meta) meta.setAttribute("content", theme === "dark" ? "#141311" : "#f3efe6");
    if (!themeToggle) return;
    var dark = theme === "dark";
    themeToggle.setAttribute("aria-pressed", dark ? "true" : "false");
    var label = themeToggle.querySelector("[data-theme-label]");
    if (label) label.textContent = dark ? "Светлая тема" : "Тёмная тема";
  }

  paintTheme(document.documentElement.getAttribute("data-theme") === "dark" ? "dark" : "light");
  on(themeToggle, "click", function () {
    var next = document.documentElement.getAttribute("data-theme") === "dark" ? "light" : "dark";
    try { localStorage.setItem("at-theme", next); } catch (e) {}
    paintTheme(next);
  });

  /* ---------- mast / nav / search overlay ---------- */

  var mast = qs("[data-mast]");
  var navToggle = qs("[data-nav-toggle]");
  var nav = qs("[data-nav]");
  var searchOpen = qs("[data-search-open]");
  var searchLayer = qs("[data-search]");
  var searchClose = qs("[data-search-close]");
  var searchInput = searchLayer ? searchLayer.querySelector("input[type='search']") : null;
  var liveBox = qs("[data-search-live]");
  var liveStatus = qs("[data-search-status]");
  var liveList = qs("[data-search-results]");
  var liveTimer = 0;
  var liveSeq = 0;

  function setNav(open) {
    if (!nav || !navToggle) return;
    nav.classList.toggle("is-open", open);
    navToggle.setAttribute("aria-expanded", open ? "true" : "false");
    document.body.classList.toggle("is-nav", open);
    if (open) setSearch(false);
  }

  on(navToggle, "click", function () {
    setNav(!nav.classList.contains("is-open"));
  });
  on(nav, "click", function (event) {
    if (event.target.closest("a")) setNav(false);
  });
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
    if (open && searchInput) {
      searchInput.focus();
      if (searchInput.value.trim().length >= 2) runLiveSearch(searchInput.value);
    }
    if (!open && wasOpen && searchOpen) searchOpen.focus();
  }

  on(searchOpen, "click", function () { setSearch(true); });
  on(searchClose, "click", function () { setSearch(false); });
  on(searchLayer, "click", function (event) {
    if (event.target === searchLayer) setSearch(false);
  });

  function renderLive(items, total, query) {
    if (!liveBox || !liveList || !liveStatus) return;
    liveList.innerHTML = "";
    if (!items || !items.length) {
      liveStatus.textContent = query.length < 2 ? (i18n.searchTyping || "") : (i18n.searchEmpty || "");
      liveBox.hidden = query.length < 1;
      return;
    }
    liveStatus.textContent = total > items.length ? items.length + " из " + total : (i18n.searchNone && !items.length ? i18n.searchNone : "");
    items.forEach(function (item) {
      var li = document.createElement("li");
      var a = document.createElement("a");
      a.href = item.url;
      a.className = "search-live__item";
      var kicker = document.createElement("span");
      kicker.className = "kicker";
      kicker.textContent = item.kicker || "";
      var title = document.createElement("strong");
      title.textContent = item.title || "";
      var meta = document.createElement("span");
      meta.className = "search-live__meta";
      meta.textContent = item.date || "";
      a.appendChild(kicker);
      a.appendChild(title);
      if (item.excerpt) {
        var excerpt = document.createElement("em");
        excerpt.textContent = item.excerpt;
        a.appendChild(excerpt);
      }
      a.appendChild(meta);
      li.appendChild(a);
      liveList.appendChild(li);
    });
    liveBox.hidden = false;
  }

  function runLiveSearch(value) {
    var query = String(value || "").trim();
    if (!interact.restUrl) return;
    if (query.length < 2) {
      renderLive([], 0, query);
      return;
    }
    var seq = ++liveSeq;
    liveBox.hidden = false;
    liveStatus.textContent = i18n.loading || "…";
    rest("search", { params: { q: query } })
      .then(function (data) {
        if (seq !== liveSeq) return;
        renderLive(data.items || [], data.total || 0, query);
      })
      .catch(function () {
        if (seq !== liveSeq) return;
        liveStatus.textContent = i18n.error || "";
      });
  }

  if (searchInput) {
    on(searchInput, "input", function () {
      var value = searchInput.value;
      clearTimeout(liveTimer);
      liveTimer = setTimeout(function () { runLiveSearch(value); }, 220);
    });
  }

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape") {
      setSearch(false);
      setNav(false);
      setContact(false);
    }
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
      event.preventDefault();
      setSearch(true);
    }
  });

  /* ---------- reading progress / sticky mast / back to top ---------- */

  var bar = qs("[data-progress]");
  var article = qs("[data-article]");
  var totop = qs("[data-totop]");
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
    if (totop) totop.hidden = window.scrollY < 480;
    paintProgress();
  }

  document.addEventListener("scroll", function () {
    if (scrollTicking) return;
    scrollTicking = true;
    requestAnimationFrame(onScrollFrame);
  }, { passive: true });
  window.addEventListener("resize", paintProgress);
  onScrollFrame();

  on(totop, "click", function () {
    window.scrollTo({ top: 0, behavior: reduceMotion ? "auto" : "smooth" });
  });

  /* ---------- fighter directory ---------- */

  var input = qs("[data-fighter-search]");
  if (input) {
    var cards = qsa("[data-fighter]");
    var groups = qsa("[data-letter]");
    var count = qs("[data-fighter-count]");
    var empty = qs("[data-fighter-empty]");

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

    on(input, "input", filter);
    filter();
  }

  /* ---------- lazy videos ---------- */

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
      headers: { "Content-Type": "application/json" },
      credentials: "same-origin",
      keepalive: true,
      body: JSON.stringify({ id: window.atView.id })
    }).catch(function () {});
  }

  /* ---------- media archive lightbox ---------- */

  var archive = qs("[data-archive]");
  var lightbox = qs("[data-lightbox]");
  var tiles = qsa("[data-vault-item]");
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
      var countEl = archive.querySelector("[data-vault-count]");
      var emptyEl = archive.querySelector("[data-vault-empty]");
      if (countEl) countEl.textContent = plural(shown);
      if (emptyEl) emptyEl.hidden = shown !== 0;
    };

    var bindGroup = function (selector, read) {
      if (!archive) return;
      var buttons = qsa(selector, archive);
      buttons.forEach(function (button) {
        button.addEventListener("click", function () {
          var value = read(button);
          if (selector.indexOf("scope") !== -1) scope = value;
          else kind = value;
          buttons.forEach(function (item) {
            var onBtn = item === button;
            item.classList.toggle("is-on", onBtn);
            item.setAttribute("aria-pressed", onBtn ? "true" : "false");
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

  /* ---------- BPLA events: filters + expand ---------- */

  var bplaSection = qs(".bpla-section");
  if (bplaSection) {
    bplaSection.addEventListener("click", function (e) {
      var filterBtn = e.target.closest(".bpla-filter");
      if (filterBtn) {
        var filter = filterBtn.getAttribute("data-filter");
        bplaSection.querySelectorAll(".bpla-filter").forEach(function (b) {
          var onBtn = b === filterBtn;
          b.classList.toggle("is-active", onBtn);
          b.setAttribute("aria-selected", onBtn ? "true" : "false");
        });
        bplaSection.querySelectorAll(".bpla-item").forEach(function (item) {
          var status = item.getAttribute("data-status") || "";
          var show = filter === "all" || status === filter;
          item.classList.toggle("is-hidden", !show);
        });
        return;
      }

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

  /* ---------- Hero album lightbox ---------- */

  var albumItems = qsa(".album-item");
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

    var albumStage = lb.querySelector("[data-stage]");
    var counter = lb.querySelector("[data-counter]");
    var closeBtn = lb.querySelector(".album-lightbox__close");
    var prevBtn = lb.querySelector(".album-lightbox__prev");
    var nextBtn = lb.querySelector(".album-lightbox__next");
    var albumCurrent = 0;

    function renderAlbum(index) {
      albumCurrent = (index + albumItems.length) % albumItems.length;
      var item = albumItems[albumCurrent];
      var full = item.getAttribute("data-full");
      var type = item.getAttribute("data-type");
      albumStage.innerHTML = "";
      if (type === "video") {
        var v = document.createElement("video");
        v.src = full;
        v.controls = true;
        v.autoplay = true;
        v.setAttribute("playsinline", "");
        albumStage.appendChild(v);
      } else {
        var img = document.createElement("img");
        img.src = full;
        img.alt = item.getAttribute("aria-label") || "";
        albumStage.appendChild(img);
      }
      counter.textContent = (albumCurrent + 1) + " / " + albumItems.length;
      prevBtn.style.visibility = albumItems.length > 1 ? "visible" : "hidden";
      nextBtn.style.visibility = albumItems.length > 1 ? "visible" : "hidden";
    }

    function openAlbum(index) {
      renderAlbum(index);
      lb.classList.add("is-open");
      document.body.classList.add("is-locked");
      closeBtn.focus();
    }
    function closeAlbum() {
      lb.classList.remove("is-open");
      document.body.classList.remove("is-locked");
      albumStage.innerHTML = "";
    }

    albumItems.forEach(function (item, i) {
      item.addEventListener("click", function () { openAlbum(i); });
    });
    on(closeBtn, "click", closeAlbum);
    on(prevBtn, "click", function () { renderAlbum(albumCurrent - 1); });
    on(nextBtn, "click", function () { renderAlbum(albumCurrent + 1); });
    on(lb, "click", function (e) { if (e.target === lb) closeAlbum(); });
    document.addEventListener("keydown", function (e) {
      if (!lb.classList.contains("is-open")) return;
      if (e.key === "Escape") closeAlbum();
      if (e.key === "ArrowLeft") renderAlbum(albumCurrent - 1);
      if (e.key === "ArrowRight") renderAlbum(albumCurrent + 1);
    });
  }

  /* ---------- Вехи истории: local index search ---------- */

  var vehi = qs("[data-vehi]");
  if (vehi) {
    var vehiInput = vehi.querySelector("[data-vehi-search]");
    var vehiEmpty = vehi.querySelector("[data-vehi-empty]");
    var vehiCount = vehi.querySelector("[data-vehi-count]");
    var vehiCards = qsa("[data-vehi-card]", vehi);
    var vehiGroups = qsa("[data-vehi-group]", vehi);
    var vehiLetters = qsa("[data-vehi-letter]", vehi);

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

  /* ---------- generic tabs ---------- */

  qsa("[data-tabs]").forEach(function (root) {
    var tabs = qsa("[data-tab]", root);
    var panels = qsa("[data-tab-panel]", root);
    if (!tabs.length || !panels.length) return;

    function activate(tab) {
      tabs.forEach(function (btn) {
        var onBtn = btn === tab;
        btn.classList.toggle("is-on", onBtn);
        btn.setAttribute("aria-selected", onBtn ? "true" : "false");
      });
      var id = tab.getAttribute("aria-controls");
      panels.forEach(function (panel) {
        var match = panel.id && panel.id === id;
        panel.hidden = !match;
      });
    }

    tabs.forEach(function (tab) {
      tab.addEventListener("click", function () { activate(tab); });
      tab.addEventListener("keydown", function (event) {
        var idx = tabs.indexOf(tab);
        if (event.key === "ArrowRight") {
          event.preventDefault();
          activate(tabs[(idx + 1) % tabs.length]);
          tabs[(idx + 1) % tabs.length].focus();
        }
        if (event.key === "ArrowLeft") {
          event.preventDefault();
          activate(tabs[(idx - 1 + tabs.length) % tabs.length]);
          tabs[(idx - 1 + tabs.length) % tabs.length].focus();
        }
      });
    });
  });

  /* ---------- contact popup + AJAX forms ---------- */

  var contactDialog = qs("[data-contact-dialog]");
  var contactTopic = contactDialog ? contactDialog.querySelector("[data-topic-field]") : null;

  function setContact(open, topic) {
    if (!contactDialog) return;
    if (topic && contactTopic) contactTopic.value = topic;
    if (typeof contactDialog.showModal === "function") {
      if (open && !contactDialog.open) contactDialog.showModal();
      if (!open && contactDialog.open) contactDialog.close();
    } else {
      contactDialog.hidden = !open;
    }
  }

  qsa("[data-contact-open]").forEach(function (btn) {
    btn.addEventListener("click", function (event) {
      if (!contactDialog) return;
      event.preventDefault();
      setContact(true, btn.getAttribute("data-topic") || "");
    });
  });
  on(qs("[data-contact-close]"), "click", function () { setContact(false); });
  if (contactDialog) {
    contactDialog.addEventListener("click", function (event) {
      if (event.target === contactDialog) setContact(false);
    });
  }

  qsa("[data-ajax-form]").forEach(function (form) {
    form.addEventListener("submit", function (event) {
      if (!interact.restUrl || !window.fetch) return;
      event.preventDefault();
      var note = form.parentNode.querySelector("[data-form-note]") || qs("[data-form-note]", form.parentNode);
      var submit = form.querySelector("[data-submit]");
      var payload = {};
      Array.prototype.forEach.call(form.elements, function (field) {
        if (!field.name || field.disabled) return;
        if (field.type === "checkbox") {
          if (field.checked) payload[field.name] = field.value || "1";
          return;
        }
        payload[field.name] = field.value;
      });
      if (submit) {
        submit.disabled = true;
        submit.dataset.label = submit.textContent;
        submit.textContent = i18n.loading || "…";
      }
      rest("contact", { method: "POST", body: payload })
        .then(function (data) {
          if (note) {
            note.hidden = false;
            note.textContent = data.message || i18n.sent || "";
            note.classList.remove("note--bad");
            note.classList.add("note--ok");
          }
          form.reset();
          if (contactTopic && form.contains(contactTopic)) {
            contactTopic.value = contactTopic.defaultValue || "Письмо в редакцию";
          }
        })
        .catch(function (err) {
          if (note) {
            note.hidden = false;
            note.textContent = (err && err.message) || i18n.error || "";
            note.classList.remove("note--ok");
            note.classList.add("note--bad");
          }
        })
        .then(function () {
          if (submit) {
            submit.disabled = false;
            submit.textContent = submit.dataset.label || "Отправить";
          }
        });
    });
  });

  /* ---------- AJAX journal feed ---------- */

  var feed = qs("[data-feed]");
  if (feed && interact.restUrl) {
    var feedList = qs("[data-feed-list]", feed);
    var feedMore = qs("[data-feed-more]", feed);
    var feedPages = qs("[data-feed-pages]", feed);
    var feedEmpty = qs("[data-feed-empty]", feed);
    var feedCount = qs("[data-feed-count]", feed);
    var feedSearch = qs("[data-feed-search]", feed);
    var feedTabs = qsa("[data-feed-cat]", feed);
    var state = {
      category: 0,
      page: parseInt(feed.getAttribute("data-feed-page") || "1", 10) || 1,
      max: parseInt(feed.getAttribute("data-feed-max") || "1", 10) || 1,
      s: ""
    };

    function word(n) {
      var n10 = n % 10;
      var n100 = n % 100;
      if (n10 === 1 && n100 !== 11) return n + " материал";
      if (n10 >= 2 && n10 <= 4 && (n100 < 12 || n100 > 14)) return n + " материала";
      return n + " материалов";
    }

    function paintFeedButtons() {
      if (feedMore) {
        feedMore.hidden = state.page >= state.max;
        feedMore.disabled = false;
        feedMore.textContent = i18n.loadMore || "Ещё материалы";
      }
      if (feedPages) feedPages.hidden = true;
    }

    function loadFeed(reset) {
      if (!feedList) return;
      if (reset) {
        state.page = 1;
        feedList.classList.add("is-loading");
      } else if (feedMore) {
        feedMore.disabled = true;
        feedMore.textContent = i18n.loading || "…";
      }
      rest("feed", {
        params: {
          category: state.category,
          page: state.page,
          s: state.s
        }
      })
        .then(function (data) {
          state.max = data.max || 1;
          if (reset) feedList.innerHTML = data.html || "";
          else feedList.insertAdjacentHTML("beforeend", data.html || "");
          if (feedCount) feedCount.textContent = word(data.total || 0);
          if (feedEmpty) feedEmpty.hidden = !!(data.total);
          paintFeedButtons();
          observeReveal(feedList);
        })
        .catch(function () {
          if (feedMore) {
            feedMore.disabled = false;
            feedMore.textContent = i18n.loadMore || "Ещё материалы";
          }
        })
        .then(function () {
          feedList.classList.remove("is-loading");
        });
    }

    feedTabs.forEach(function (tab) {
      tab.addEventListener("click", function () {
        feedTabs.forEach(function (btn) {
          var onBtn = btn === tab;
          btn.classList.toggle("is-on", onBtn);
          btn.setAttribute("aria-selected", onBtn ? "true" : "false");
        });
        state.category = parseInt(tab.getAttribute("data-feed-cat") || "0", 10) || 0;
        loadFeed(true);
      });
    });

    if (feedSearch) {
      on(feedSearch, "input", debounce(function () {
        state.s = feedSearch.value.trim();
        loadFeed(true);
      }, 280));
    }

    on(feedMore, "click", function () {
      if (state.page >= state.max) return;
      state.page += 1;
      loadFeed(false);
    });

    paintFeedButtons();
  }

  /* ---------- share / copy ---------- */

  qsa("[data-share-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var url = btn.getAttribute("data-share-copy") || window.location.href;
      var note = btn.closest("[data-share]");
      note = note ? note.querySelector("[data-share-note]") : null;
      var done = function (ok) {
        if (!note) return;
        note.hidden = false;
        note.textContent = ok ? (i18n.copied || "") : (i18n.copyFail || "");
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () { done(true); }).catch(function () { done(false); });
      } else {
        done(false);
      }
    });
  });

  /* ---------- TOC scroll spy ---------- */

  var toc = qs("[data-toc]");
  if (toc) {
    var links = qsa("a[href^='#']", toc);
    var map = links.map(function (link) {
      return {
        link: link,
        el: document.getElementById(decodeURIComponent(link.getAttribute("href").slice(1)))
      };
    }).filter(function (row) { return row.el; });

    if (map.length && "IntersectionObserver" in window) {
      var spy = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          map.forEach(function (row) {
            row.link.classList.toggle("is-current", row.el === entry.target);
          });
        });
      }, { rootMargin: "-20% 0px -70% 0px", threshold: 0 });
      map.forEach(function (row) { spy.observe(row.el); });
    }
  }

  /* ---------- scroll reveal (akadem-style, theme tokens) ---------- */

  function observeReveal(root) {
    if (reduceMotion) return;
    var sel = ".story, .row, .person-card, .person, .vehi-rail__item, .vehi-card, .bpla-item, .nom, .feature, .side-story, .related .story, [data-reveal]";
    var nodes = qsa(sel, root || document).filter(function (el) {
      return !el.classList.contains("is-visible") && !el.hasAttribute("data-reveal-bound");
    });
    if (!nodes.length) return;
    nodes.forEach(function (el) { el.setAttribute("data-reveal", ""); });
    if (!("IntersectionObserver" in window)) {
      nodes.forEach(function (el) { el.classList.add("is-visible"); });
      return;
    }
    if (!observeReveal.observer) {
      observeReveal.observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add("is-visible");
          observeReveal.observer.unobserve(entry.target);
        });
      }, { threshold: 0.12, rootMargin: "0px 0px -8% 0px" });
    }
    nodes.forEach(function (el, index) {
      el.setAttribute("data-reveal-bound", "1");
      el.style.transitionDelay = Math.min(index % 6, 5) * 60 + "ms";
      observeReveal.observer.observe(el);
    });
  }

  observeReveal(document);
})();
