(function () {
  document.documentElement.classList.add("has-js");

  var config = window.atInteract || {};
  var reduceMotion = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function endpoint(url, params) {
    var target = new URL(url, window.location.origin);
    Object.keys(params).forEach(function (key) {
      if (params[key] === "" || params[key] == null) return;
      target.searchParams.set(key, params[key]);
    });
    return target.toString();
  }

  function restHeaders() {
    return {
      "Accept": "application/json",
      "X-WP-Nonce": config.nonce || ""
    };
  }

  function reveal(nodes) {
    if (reduceMotion || !("IntersectionObserver" in window)) return;
    var list = Array.prototype.slice.call(nodes || []);
    if (!list.length) return;
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (!entry.isIntersecting) return;
        entry.target.classList.add("is-in");
        observer.unobserve(entry.target);
      });
    }, { threshold: 0, rootMargin: "0px 0px -40px 0px" });
    list.forEach(function (node) {
      if (node.hasAttribute("data-reveal")) return;
      node.setAttribute("data-reveal", "");
      observer.observe(node);
    });
  }

  reveal(document.querySelectorAll(".lead, .section, .bpla-section, .vehi-home, .band, .contest, .page-hero, .notice, .home-shelf, .rail__block"));

  document.querySelectorAll("[data-tabs]").forEach(function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll("[role='tab']"));
    var panels = Array.prototype.slice.call(root.querySelectorAll("[role='tabpanel']"));
    if (!tabs.length || !panels.length) return;

    function activate(tab) {
      tabs.forEach(function (item) {
        var on = item === tab;
        item.classList.toggle("is-on", on);
        item.setAttribute("aria-selected", on ? "true" : "false");
        item.tabIndex = on ? 0 : -1;
      });
      panels.forEach(function (panel) {
        var on = panel.id === tab.getAttribute("aria-controls");
        panel.classList.toggle("is-on", on);
        if (on) panel.removeAttribute("hidden");
        else panel.setAttribute("hidden", "");
      });
    }

    tabs.forEach(function (tab, index) {
      tab.addEventListener("click", function () { activate(tab); });
      tab.addEventListener("keydown", function (event) {
        var next = null;
        if (event.key === "ArrowRight" || event.key === "ArrowDown") next = tabs[(index + 1) % tabs.length];
        if (event.key === "ArrowLeft" || event.key === "ArrowUp") next = tabs[(index - 1 + tabs.length) % tabs.length];
        if (event.key === "Home") next = tabs[0];
        if (event.key === "End") next = tabs[tabs.length - 1];
        if (!next) return;
        event.preventDefault();
        activate(next);
        next.focus();
      });
    });
  });

  var searchLayer = document.querySelector("[data-search]");
  var searchInput = searchLayer ? searchLayer.querySelector("input[type='search']") : null;
  var searchLive = document.querySelector("[data-search-live]");
  var searchTimer = 0;
  var searchRequest = null;
  var searchSeq = 0;

  function hideLive() {
    if (!searchLive) return;
    searchLive.hidden = true;
    searchLive.innerHTML = "";
  }

  function renderLive(items, query) {
    if (!searchLive) return;
    searchLive.innerHTML = "";
    if (!items.length) {
      var empty = document.createElement("p");
      empty.className = "search-live__empty";
      empty.textContent = "Ничего не нашли. Откройте полный поиск или проверьте написание.";
      searchLive.appendChild(empty);
    } else {
      var list = document.createElement("ul");
      list.className = "search-live__list";
      items.forEach(function (item) {
        var li = document.createElement("li");
        var link = document.createElement("a");
        link.href = item.url || "#";
        var kicker = document.createElement("span");
        kicker.className = "kicker";
        kicker.textContent = item.kicker || "";
        var title = document.createElement("span");
        title.className = "search-live__title";
        title.textContent = item.title || "";
        var meta = document.createElement("span");
        meta.className = "search-live__meta";
        meta.textContent = item.date || "";
        link.appendChild(kicker);
        link.appendChild(title);
        link.appendChild(meta);
        li.appendChild(link);
        list.appendChild(li);
      });
      searchLive.appendChild(list);
    }
    if (config.home && query) {
      var all = document.createElement("a");
      all.className = "section__link search-live__all";
      all.href = config.home.replace(/\/?$/, "/") + "?s=" + encodeURIComponent(query);
      all.textContent = "Все результаты";
      searchLive.appendChild(all);
    }
    searchLive.hidden = false;
  }

  function runSearch(query) {
    if (!config.search || !searchLive) return;
    if (searchRequest) searchRequest.abort();
    var controller = window.AbortController ? new AbortController() : null;
    searchRequest = controller;
    var seq = ++searchSeq;
    fetch(endpoint(config.search, { q: query }), {
      method: "GET",
      credentials: "same-origin",
      headers: restHeaders(),
      signal: controller ? controller.signal : undefined
    }).then(function (response) {
      if (!response.ok) throw new Error("search");
      return response.json();
    }).then(function (payload) {
      if (seq !== searchSeq) return;
      renderLive((payload && payload.items) || [], query);
    }).catch(function (error) {
      if (error && error.name === "AbortError") return;
      if (!searchLive) return;
      searchLive.hidden = false;
      searchLive.innerHTML = "";
      var note = document.createElement("p");
      note.className = "search-live__empty";
      note.textContent = "Живой поиск сейчас недоступен. Нажмите «Найти».";
      searchLive.appendChild(note);
    });
  }

  if (searchInput && searchLive && config.search) {
    searchInput.setAttribute("autocomplete", "off");
    searchInput.setAttribute("aria-autocomplete", "list");
    searchInput.setAttribute("aria-controls", "at-search-live");
    searchLive.id = "at-search-live";
    searchInput.addEventListener("input", function () {
      var query = searchInput.value.trim();
      window.clearTimeout(searchTimer);
      if (query.length < 2) {
        if (searchRequest) searchRequest.abort();
        hideLive();
        return;
      }
      searchTimer = window.setTimeout(function () { runSearch(query); }, 200);
    });
    searchInput.addEventListener("keydown", function (event) {
      if (event.key !== "ArrowDown" || searchLive.hidden) return;
      var first = searchLive.querySelector("a");
      if (!first) return;
      event.preventDefault();
      first.focus();
    });
  }
  if (searchLayer) {
    var searchObserver = new MutationObserver(function () {
      if (searchLayer.hidden) hideLive();
    });
    searchObserver.observe(searchLayer, { attributes: true, attributeFilter: ["hidden"] });
  }

  document.querySelectorAll("[data-feed]").forEach(function (root) {
    var list = root.querySelector("[data-feed-list]");
    var more = root.querySelector("[data-feed-more]");
    var empty = root.querySelector("[data-feed-empty]");
    var status = root.querySelector("[data-feed-status]");
    var tabs = Array.prototype.slice.call(root.querySelectorAll("[data-feed-category]"));
    var feedSeq = 0;
    var feedRequest = null;
    if (!list || !config.feed) return;

    root.querySelectorAll(".pagination, .nav-links").forEach(function (pager) {
      pager.hidden = true;
    });

    function setStatus(text, bad) {
      if (!status) return;
      status.hidden = !text;
      status.textContent = text || "";
      status.classList.toggle("note--bad", !!bad);
    }

    function paramsFor(page) {
      var params = {
        context: root.getAttribute("data-feed-context") || "journal",
        page: page
      };
      if (params.context === "journal") params.category = root.getAttribute("data-feed-category") || "";
      if (params.context === "archive") {
        params.taxonomy = root.getAttribute("data-feed-taxonomy") || "";
        params.term = root.getAttribute("data-feed-term") || "";
        params.year = root.getAttribute("data-feed-year") || "";
        params.month = root.getAttribute("data-feed-month") || "";
        params.day = root.getAttribute("data-feed-day") || "";
        params.author = root.getAttribute("data-feed-author") || "";
      }
      if (params.context === "search") params.s = root.getAttribute("data-feed-search") || "";
      return params;
    }

    function paintMore(page, pages) {
      root.setAttribute("data-feed-page", String(page));
      root.setAttribute("data-feed-pages", String(pages));
      if (more) more.hidden = page >= pages;
    }

    function load(page, mode) {
      var ticket = ++feedSeq;
      if (feedRequest) feedRequest.abort();
      var controller = window.AbortController ? new AbortController() : null;
      feedRequest = controller;
      root.classList.add("is-loading");
      root.setAttribute("aria-busy", "true");
      setStatus(mode === "replace" ? "Обновляем ленту…" : "Загружаем ещё…", false);
      return fetch(endpoint(config.feed, paramsFor(page)), {
        method: "GET",
        credentials: "same-origin",
        headers: restHeaders(),
        signal: controller ? controller.signal : undefined
      }).then(function (response) {
        if (!response.ok) throw new Error("feed");
        return response.json();
      }).then(function (payload) {
        if (ticket !== feedSeq) return;
        var html = payload && typeof payload.html === "string" ? payload.html : "";
        if (mode === "replace") list.innerHTML = html;
        else if (html) list.insertAdjacentHTML("beforeend", html);
        if (empty) empty.hidden = list.children.length !== 0;
        paintMore(payload.page || page, payload.pages || 0);
        setStatus("", false);
        reveal(list.querySelectorAll(".row"));
      }).catch(function (error) {
        if (error && error.name === "AbortError") return;
        if (ticket !== feedSeq) return;
        setStatus("Не удалось обновить ленту. Обновите страницу.", true);
      }).then(function () {
        if (ticket !== feedSeq) return;
        root.classList.remove("is-loading");
        root.removeAttribute("aria-busy");
      });
    }

    if (more) {
      more.addEventListener("click", function () {
        var page = parseInt(root.getAttribute("data-feed-page") || "1", 10);
        var pages = parseInt(root.getAttribute("data-feed-pages") || "1", 10);
        if (page >= pages) return;
        load(page + 1, "append");
      });
    }

    function selectTab(tab) {
      var slug = tab.getAttribute("data-feed-category") || "";
      root.setAttribute("data-feed-category", slug);
      tabs.forEach(function (item, index) {
        var on = item === tab;
        item.classList.toggle("is-on", on);
        item.setAttribute("aria-selected", on ? "true" : "false");
        item.tabIndex = on ? 0 : -1;
        item.dataset.index = String(index);
      });
      load(1, "replace");
    }

    tabs.forEach(function (tab, index) {
      tab.dataset.index = String(index);
      tab.addEventListener("click", function () { selectTab(tab); });
      tab.addEventListener("keydown", function (event) {
        var current = parseInt(tab.dataset.index || "0", 10);
        var next = null;
        if (event.key === "ArrowRight" || event.key === "ArrowDown") next = tabs[(current + 1) % tabs.length];
        if (event.key === "ArrowLeft" || event.key === "ArrowUp") next = tabs[(current - 1 + tabs.length) % tabs.length];
        if (event.key === "Home") next = tabs[0];
        if (event.key === "End") next = tabs[tabs.length - 1];
        if (!next) return;
        event.preventDefault();
        selectTab(next);
        next.focus();
      });
    });
  });

  var bpla = document.querySelector(".bpla-filters");
  if (bpla) {
    var filters = Array.prototype.slice.call(bpla.querySelectorAll(".bpla-filter"));
    filters.forEach(function (button, index) {
      button.addEventListener("keydown", function (event) {
        var next = null;
        if (event.key === "ArrowRight" || event.key === "ArrowDown") next = filters[(index + 1) % filters.length];
        if (event.key === "ArrowLeft" || event.key === "ArrowUp") next = filters[(index - 1 + filters.length) % filters.length];
        if (!next) return;
        event.preventDefault();
        next.focus();
        next.click();
      });
    });
  }

  var dialog = document.querySelector("[data-contact-dialog]");
  var dialogTopic = dialog ? dialog.querySelector("input[name='at_topic']") : null;
  var lastTrigger = null;

  function openContact(topic, trigger) {
    if (!dialog || typeof dialog.showModal !== "function") {
      window.location.href = (config.home || "/").replace(/\/?$/, "/") + "contacts/";
      return;
    }
    lastTrigger = trigger || null;
    if (dialogTopic && topic) dialogTopic.value = topic;
    var search = document.querySelector("[data-search]");
    if (search) search.hidden = true;
    document.body.classList.remove("is-locked");
    if (!dialog.open) dialog.showModal();
    var field = dialog.querySelector("input[name='at_name']");
    if (field) field.focus();
  }

  function closeContact() {
    if (!dialog || !dialog.open) return;
    dialog.close();
    if (lastTrigger) lastTrigger.focus();
  }

  document.querySelectorAll("[data-contact-open]").forEach(function (button) {
    button.addEventListener("click", function () {
      openContact(button.getAttribute("data-contact-topic") || "", button);
    });
  });
  if (dialog) {
    dialog.querySelectorAll("[data-contact-close]").forEach(function (button) {
      button.addEventListener("click", closeContact);
    });
    dialog.addEventListener("click", function (event) {
      if (event.target === dialog) closeContact();
    });
  }

  document.querySelectorAll("[data-contact-form]").forEach(function (form) {
    var status = form.querySelector("[data-contact-status]");
    form.addEventListener("submit", function (event) {
      if (!config.contact || !window.fetch) return;
      event.preventDefault();
      var data = new FormData(form);
      var button = form.querySelector("[type='submit']");
      if (button) button.disabled = true;
      if (status) {
        status.hidden = false;
        status.classList.remove("note--ok", "note--bad");
        status.textContent = "Отправляем…";
      }
      fetch(config.contact, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-WP-Nonce": config.nonce || ""
        },
        body: JSON.stringify({
          name: data.get("at_name") || "",
          email: data.get("at_email") || "",
          message: data.get("at_message") || "",
          topic: data.get("at_topic") || "",
          consent: !!data.get("at_consent"),
          company: data.get("at_company") || ""
        })
      }).then(function (response) {
        return response.json().then(function (payload) {
          return { ok: response.ok, payload: payload };
        });
      }).then(function (result) {
        if (!status) return;
        status.hidden = false;
        if (result.ok && result.payload && result.payload.ok) {
          status.classList.add("note--ok");
          status.classList.remove("note--bad");
          status.textContent = result.payload.message || "Письмо отправлено.";
          form.reset();
          if (dialogTopic && form.contains(dialogTopic) && dialogTopic.defaultValue) {
            dialogTopic.value = dialogTopic.defaultValue;
          }
          return;
        }
        status.classList.add("note--bad");
        status.classList.remove("note--ok");
        status.textContent = "Не удалось отправить. Проверьте имя, почту, текст и согласие.";
      }).catch(function () {
        if (!status) return;
        status.hidden = false;
        status.classList.add("note--bad");
        status.textContent = "Сеть недоступна. Отправьте форму ещё раз.";
      }).then(function () {
        if (button) button.disabled = false;
      });
    });
  });
})();
