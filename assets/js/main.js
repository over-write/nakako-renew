(function () {
  "use strict";

  /** 粉体ページのタブ同期（存在する場合のみ代入） */
  var syncPowderTabsFromHash = null;

  var menuToggle = document.getElementById("menu-toggle");
  var menuClose = document.getElementById("menu-close");
  var navWrap = document.getElementById("global-nav");

  function setNavOpen(open) {
    document.body.classList.toggle("nav-open", open);
    if (menuToggle) {
      menuToggle.setAttribute("aria-expanded", open ? "true" : "false");
      menuToggle.setAttribute("aria-label", open ? "メニューを閉じる" : "メニューを開く");
    }
    document.body.style.overflow = open ? "hidden" : "";
  }

  if (menuToggle && navWrap) {
    menuToggle.addEventListener("click", function () {
      var open = !document.body.classList.contains("nav-open");
      setNavOpen(open);
    });
  }

  if (menuClose) {
    menuClose.addEventListener("click", function () {
      setNavOpen(false);
    });
  }

  /* SP：gnav 内のリンク（相対パス ../... や # 付き同一ページ含む）でメニューを閉じる */
  if (navWrap) {
    navWrap.addEventListener("click", function (e) {
      var link = e.target.closest("a[href]");
      if (!link || !navWrap.contains(link)) return;
      if (window.matchMedia("(max-width: 767px)").matches) {
        setNavOpen(false);
      }
    });
  }

  var pagetop = document.getElementById("pagetop");
  if (pagetop) {
    var PAGETOP_SCROLL_THRESHOLD = 400;

    function updatePagetopVisibility() {
      var y =
        window.scrollY ||
        window.pageYOffset ||
        document.documentElement.scrollTop ||
        document.body.scrollTop ||
        0;
      var show = y > PAGETOP_SCROLL_THRESHOLD;
      pagetop.classList.toggle("pagetop--visible", show);
      pagetop.setAttribute("aria-hidden", show ? "false" : "true");
      if (show) {
        pagetop.removeAttribute("tabindex");
      } else {
        pagetop.setAttribute("tabindex", "-1");
      }
    }

    pagetop.addEventListener("click", function () {
      if (location.hash) {
        history.replaceState(
          null,
          document.title,
          window.location.pathname + window.location.search
        );
      }
      if (typeof syncPowderTabsFromHash === "function") {
        syncPowderTabsFromHash();
      }
      window.scrollTo({ top: 0, behavior: "smooth" });
    });
    window.addEventListener("scroll", updatePagetopVisibility, { passive: true });
    window.addEventListener("pageshow", updatePagetopVisibility);
    updatePagetopVisibility();
  }

  /* 粉体ページ：powder-tabs の current（ハッシュ／スクロール位置） */
  var powderTabs = document.querySelector(".powder-tabs");
  if (powderTabs) {
    var powderTabLinks = powderTabs.querySelectorAll(".powder-tabs__tab[href^='#']");
    var powderSections = document.querySelectorAll(".powder-main > section.powder-section[id]");
    /** スクロール連動で前回付けたハッシュ（タブバー横スクロールは変化時のみ） */
    var powderScrollSpyLastHash = null;

    function powderSpActivationLinePx() {
      var root = document.documentElement;
      var hh = parseFloat(getComputedStyle(root).getPropertyValue("--header-h")) || 72;
      var tabH = powderTabs.offsetHeight || 0;
      return hh + tabH + 12;
    }

    function setPowderTabsActiveByHash(hash, scrollTabBar) {
      var activeLink = null;
      powderTabLinks.forEach(function (a) {
        var on = hash !== "" && a.getAttribute("href") === hash;
        a.classList.toggle("powder-tabs__tab--current", on);
        if (on) {
          a.setAttribute("aria-current", "true");
          activeLink = a;
        } else {
          a.removeAttribute("aria-current");
        }
      });

      if (!scrollTabBar) return;
      if (!window.matchMedia("(max-width: 768px)").matches) return;
      var scroller = powderTabs.querySelector(".powder-tabs__scroll");
      if (!scroller) return;

      if (!activeLink) {
        scroller.scrollTo({ left: 0, behavior: "smooth" });
        return;
      }

      var tabLeft = activeLink.offsetLeft;
      var tabWidth = activeLink.offsetWidth;
      var viewport = scroller.clientWidth;
      var targetCentered = tabLeft - viewport / 2 + tabWidth / 2;
      var maxScroll = Math.max(0, scroller.scrollWidth - viewport);
      scroller.scrollTo({
        left: Math.max(0, Math.min(maxScroll, targetCentered)),
        behavior: "smooth",
      });
    }

    function updatePowderTabsFromScroll() {
      if (!window.matchMedia("(max-width: 768px)").matches) return;
      if (!powderSections.length) return;

      var line = powderSpActivationLinePx();
      var activeHash = "";
      for (var i = powderSections.length - 1; i >= 0; i--) {
        if (powderSections[i].getBoundingClientRect().top <= line) {
          activeHash = "#" + powderSections[i].id;
          break;
        }
      }

      if (activeHash !== location.hash) {
        history.replaceState(
          null,
          document.title,
          window.location.pathname + window.location.search + activeHash
        );
      }

      var scrollTabBar = activeHash !== powderScrollSpyLastHash;
      powderScrollSpyLastHash = activeHash;
      setPowderTabsActiveByHash(activeHash, scrollTabBar);
    }

    var powderScrollSpyScheduled = false;
    function schedulePowderScrollSpy() {
      if (powderScrollSpyScheduled) return;
      powderScrollSpyScheduled = true;
      requestAnimationFrame(function () {
        powderScrollSpyScheduled = false;
        updatePowderTabsFromScroll();
      });
    }

    syncPowderTabsFromHash = function () {
      setPowderTabsActiveByHash(location.hash, true);
      powderScrollSpyLastHash = location.hash;
    };

    window.addEventListener("hashchange", syncPowderTabsFromHash);
    window.addEventListener("pageshow", syncPowderTabsFromHash);
    window.addEventListener("scroll", schedulePowderScrollSpy, { passive: true });
    window.addEventListener("resize", schedulePowderScrollSpy);

    syncPowderTabsFromHash();
    schedulePowderScrollSpy();
  }

  /* 粉体ページ：設備一覧テーブルの横スクロール（端検知・ボタン） */
  document.querySelectorAll("[data-table-scroll-shell]").forEach(function (shell) {
    var viewport =
      shell.querySelector(".table-scroll-shell__viewport") ||
      shell.querySelector(".table-wrap");
    var prevBtn = shell.querySelector(".table-scroll-shell__nav--prev");
    var nextBtn = shell.querySelector(".table-scroll-shell__nav--next");
    if (!viewport || !prevBtn || !nextBtn) return;

    var SNAP = 2;
    var NAV_INSET = 8;
    var NAV_SIZE = 40;

    function positionNavWithinTableBand() {
      var rect = viewport.getBoundingClientRect();
      prevBtn.style.left = rect.left + NAV_INSET + "px";
      prevBtn.style.right = "auto";
      nextBtn.style.left = rect.right - NAV_SIZE - NAV_INSET + "px";
      nextBtn.style.right = "auto";
    }

    var navPosRaf = 0;
    function scheduleNavPosition() {
      if (navPosRaf) return;
      navPosRaf = requestAnimationFrame(function () {
        navPosRaf = 0;
        positionNavWithinTableBand();
      });
    }

    function applyScrollHints() {
      var maxScroll = viewport.scrollWidth - viewport.clientWidth;
      var scrollable = maxScroll > SNAP;
      var left = viewport.scrollLeft;
      var atStart = !scrollable || left <= SNAP;
      var atEnd = !scrollable || left >= maxScroll - SNAP;

      shell.classList.toggle("table-scroll-shell--scrollable", scrollable);
      shell.classList.toggle("table-scroll-shell--at-start", atStart);
      shell.classList.toggle("table-scroll-shell--at-end", atEnd);

      prevBtn.tabIndex = scrollable && !atStart ? 0 : -1;
      nextBtn.tabIndex = scrollable && !atEnd ? 0 : -1;
      prevBtn.setAttribute("aria-hidden", scrollable && !atStart ? "false" : "true");
      nextBtn.setAttribute("aria-hidden", scrollable && !atEnd ? "false" : "true");

      scheduleNavPosition();
    }

    function scrollByStep(dir) {
      var step = Math.min(280, viewport.clientWidth * 0.65);
      viewport.scrollBy({ left: dir * step, behavior: "smooth" });
    }

    prevBtn.addEventListener("click", function () {
      scrollByStep(-1);
    });
    nextBtn.addEventListener("click", function () {
      scrollByStep(1);
    });

    viewport.addEventListener("scroll", applyScrollHints, { passive: true });
    window.addEventListener("resize", applyScrollHints);
    window.addEventListener("pageshow", applyScrollHints);
    window.addEventListener("scroll", scheduleNavPosition, { passive: true });
    applyScrollHints();

    var tbl = viewport.querySelector("table");

    if ("ResizeObserver" in window) {
      var ro = new ResizeObserver(function () {
        applyScrollHints();
      });
      ro.observe(viewport);
      if (tbl) {
        ro.observe(tbl);
      }
    }

    /* 設備一覧 thead をサイトヘッダー直下に見せる（PC・SP。.table-wrap の overflow-x がスクロールコンテナになり CSS の sticky が効かないため複製＋fixed） */
    if (tbl && tbl.classList.contains("setsubi-table")) {
      var theadEl = tbl.querySelector("thead");
      if (theadEl) {
        var powderTabsEl = document.querySelector(".powder-tabs");
        var stickyRoot = document.createElement("div");
        stickyRoot.className = "setsubi-sticky-thead";
        stickyRoot.setAttribute("aria-hidden", "true");
        var stickyPan = document.createElement("div");
        stickyPan.className = "setsubi-sticky-thead__pan";
        var stickyTable = document.createElement("table");
        stickyTable.className = tbl.className + " setsubi-table--sticky-clone";
        stickyTable.appendChild(theadEl.cloneNode(true));
        stickyPan.appendChild(stickyTable);
        stickyRoot.appendChild(stickyPan);
        document.body.appendChild(stickyRoot);

        var stickyRaf = 0;

        function headerStickyInsetPx() {
          var headerEl = document.querySelector(".site-header");
          var y = headerEl ? headerEl.getBoundingClientRect().bottom : NaN;
          if (!isFinite(y)) {
            var hh = parseFloat(
              getComputedStyle(document.documentElement).getPropertyValue("--header-h")
            );
            y = isFinite(hh) ? hh : 72;
          }

          /* SP 粉体：.powder-tabs がヘッダー直下に sticky のときは、その下から複製 thead を出す */
          var tabs = powderTabsEl;
          if (tabs && getComputedStyle(tabs).display !== "none") {
            var tr = tabs.getBoundingClientRect();
            if (tr.top <= y + 1) {
              y = Math.max(y, tr.bottom);
            }
          }

          return y;
        }

        function syncStickyCloneWidths() {
          var origThs = tbl.querySelectorAll("thead th");
          var cloneThs = stickyTable.querySelectorAll("thead th");
          if (!origThs.length || origThs.length !== cloneThs.length) {
            return;
          }
          stickyTable.style.width = tbl.offsetWidth + "px";
          for (var ti = 0; ti < origThs.length; ti++) {
            var cw = origThs[ti].getBoundingClientRect().width;
            cloneThs[ti].style.width = cw + "px";
            cloneThs[ti].style.boxSizing = "border-box";
          }
        }

        function updateStickyThead() {
          var theadRow = tbl.querySelector("thead tr");
          if (!theadRow) {
            stickyRoot.classList.remove("setsubi-sticky-thead--visible");
            return;
          }

          var insetTop = headerStickyInsetPx();
          var theadTop = theadRow.getBoundingClientRect().top;
          var tableBottom = tbl.getBoundingClientRect().bottom;
          var show = theadTop < insetTop && tableBottom > insetTop + 2;

          if (!show) {
            stickyRoot.classList.remove("setsubi-sticky-thead--visible");
            return;
          }

          var vr = viewport.getBoundingClientRect();
          stickyRoot.style.top = insetTop + "px";
          stickyRoot.style.left = vr.left + "px";
          stickyRoot.style.width = vr.width + "px";

          stickyPan.style.transform = "translateX(" + -viewport.scrollLeft + "px)";

          syncStickyCloneWidths();
          stickyRoot.classList.add("setsubi-sticky-thead--visible");
        }

        function scheduleStickyThead() {
          if (stickyRaf) {
            return;
          }
          stickyRaf = requestAnimationFrame(function () {
            stickyRaf = 0;
            updateStickyThead();
          });
        }

        viewport.addEventListener("scroll", scheduleStickyThead, { passive: true });
        window.addEventListener("scroll", scheduleStickyThead, { passive: true });
        window.addEventListener("resize", scheduleStickyThead);
        window.addEventListener("pageshow", scheduleStickyThead);

        if ("ResizeObserver" in window) {
          var stickyRo = new ResizeObserver(scheduleStickyThead);
          stickyRo.observe(viewport);
          stickyRo.observe(tbl);
          if (powderTabsEl) {
            stickyRo.observe(powderTabsEl);
          }
        }

        scheduleStickyThead();
      }
    }
  });

  /* お問い合わせフォーム：郵便番号 → 住所自動入力 */
  var zipInput = document.getElementById("zip");
  var zipBtn = document.querySelector(".form-field__btn");
  var prefInput = document.getElementById("pref");
  var addressInput = document.getElementById("address");

  function lookupZip() {
    var zip = zipInput.value.replace(/[^\d]/g, "");
    if (zip.length !== 7) {
      alert("郵便番号を7桁で入力してください（ハイフンなし）");
      return;
    }
    zipBtn.disabled = true;
    zipBtn.textContent = "検索中…";
    fetch("https://zipcloud.ibsnet.co.jp/api/search?zipcode=" + zip)
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (!data.results || !data.results.length) {
          alert("該当する住所が見つかりませんでした。");
          return;
        }
        var r = data.results[0];
        prefInput.value = r.address1;
        addressInput.value = r.address2 + r.address3;
        addressInput.focus();
      })
      .catch(function () {
        alert("住所の取得に失敗しました。手動でご入力ください。");
      })
      .finally(function () {
        zipBtn.disabled = false;
        zipBtn.textContent = "住所検索";
      });
  }

  if (zipBtn && zipInput && prefInput && addressInput) {
    zipBtn.addEventListener("click", lookupZip);
    zipInput.addEventListener("keydown", function (e) {
      if (e.key === "Enter") { e.preventDefault(); lookupZip(); }
    });
  }

  var galleryRow = document.querySelector(".home-gallery__row");
  if (galleryRow) {
    var galleryImgs = galleryRow.querySelectorAll("img");
    galleryImgs.forEach(function (img) {
      var clone = img.cloneNode(true);
      clone.setAttribute("aria-hidden", "true");
      clone.setAttribute("alt", "");
      galleryRow.appendChild(clone);
    });
  }
})();
