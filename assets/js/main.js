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
