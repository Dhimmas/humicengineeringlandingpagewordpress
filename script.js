(function () {
  var scrollTicking = false;

  function initHumicHeader() {
    var header = document.getElementById('site-header');
    if (!header) return false;
    if (header.dataset.humicInit === '1') {
      if (typeof window.humicSyncHeader === 'function') {
        window.humicSyncHeader();
      }
      return true;
    }

    header.dataset.humicInit = '1';
    document.documentElement.classList.add('humic-custom-layout');
    document.body.classList.add('humic-custom-layout');
    var navbar = document.getElementById('navbar');
    var spacer = document.getElementById('humic-header-spacer');
    var backdrop = null;
    var syncMobileMenuPosition = null;

    function zeroTopGap() {
      document.documentElement.classList.add('humic-custom-layout');
      document.body.classList.add('humic-custom-layout');
      if (!document.body.classList.contains('humic-header-pinned')) {
        document.body.style.paddingTop = '0';
      }
      document.body.style.marginTop = '0';

      var blocks = document.querySelector('.wp-site-blocks');
      if (blocks) {
        blocks.style.paddingTop = '0';
        blocks.style.marginTop = '0';
        blocks.style.gap = '0';
      }

      document.querySelectorAll('.has-global-padding, .is-root-container, #page, .entry-content, .wp-block-post-content, main').forEach(function (el) {
        el.style.paddingTop = '0';
        el.style.marginTop = '0';
      });

      document.querySelectorAll('.elementor-section, .e-con, .elementor-element, .elementor-widget-wrap, .elementor-widget-container, .elementor-widget-shortcode, .elementor-widget-html').forEach(function (el) {
        if (!el.querySelector || (!el.querySelector('main') && !el.querySelector('.hero'))) {
          return;
        }
        el.style.paddingTop = '0';
        el.style.marginTop = '0';
      });
    }

    function isHeaderInsideElementor() {
      return !!header.closest('.elementor, .elementor-section, .e-con, .elementor-element, .elementor-widget-wrap, .elementor-widget-container, .elementor-widget-shortcode');
    }

    function getBodyInsertPoint() {
      var el = document.getElementById('page')
        || document.querySelector('.wp-site-blocks')
        || document.querySelector('.site-content')
        || document.querySelector('.site')
        || document.querySelector('main');

      if (!el) {
        return null;
      }

      while (el.parentElement && el.parentElement !== document.body) {
        el = el.parentElement;
      }

      return el.parentElement === document.body ? el : null;
    }

    function isMobileLayout() {
      return window.matchMedia('(max-width: 768px)').matches;
    }

    function ensureHeaderSpacer() {
      spacer = document.getElementById('humic-header-spacer');
      if (spacer || !header.parentNode || !isHeaderInsideElementor()) {
        return spacer;
      }

      spacer = document.createElement('div');
      spacer.id = 'humic-header-spacer';
      spacer.setAttribute('aria-hidden', 'true');
      header.parentNode.insertBefore(spacer, header);
      return spacer;
    }

    function stripHeaderInlineStyles() {
      var mobile = isMobileLayout();
      header.removeAttribute('style');
      header.querySelectorAll('.topbar, .navbar, .navbar-inner, .container, .logo, .logo a, .logo img, .nav-links, .desktop-cta, .burger, .burger span, .topbar-inner, .topbar-left, .topbar-social, .topbar-left a, .topbar-left span, .topbar-social a').forEach(function (el) {
        if (!mobile && el.classList.contains('topbar')) {
          return;
        }
        el.removeAttribute('style');
      });
    }

    var CONTENT_SLOT_SEL = 'main, footer, .footer, .hero, .stats-bar, section[id], .elementor-location-footer';

    function slotHasPageContent(el) {
      return !!(el && el.querySelector && el.querySelector(CONTENT_SLOT_SEL));
    }

    function clearHeaderSlotHide(el) {
      if (!el || el === document.body) {
        return;
      }
      delete el.dataset.humicHeaderSlot;
      delete el.dataset.humicHeaderOrigin;
      ['display', 'height', 'min-height', 'max-height', 'margin', 'padding', 'overflow', 'visibility'].forEach(function (prop) {
        el.style.removeProperty(prop);
      });
    }

    function restoreContentSlots() {
      document.querySelectorAll('.elementor-widget-shortcode, .elementor-widget-html, .elementor-element[data-widget_type], .wp-block-shortcode, .entry-content, .wp-block-post-content').forEach(function (el) {
        if (slotHasPageContent(el) || el.querySelector('#site-header')) {
          clearHeaderSlotHide(el);
        }
      });
    }

    function restoreHeaderContainers() {
      restoreContentSlots();
      document.querySelectorAll('[data-humic-header-slot], [data-humic-header-origin]').forEach(function (el) {
        if (slotHasPageContent(el) || el.querySelector('#site-header') || el === header) {
          clearHeaderSlotHide(el);
        }
      });

      var node = header.parentElement;
      while (node && node !== document.body) {
        clearHeaderSlotHide(node);
        node = node.parentElement;
      }
    }

    function getBodyInsertRef() {
      var adminBar = document.getElementById('wpadminbar');
      var node = document.body.firstChild;

      if (adminBar && adminBar.parentNode === document.body) {
        node = adminBar.nextSibling;
      }

      while (node && node.nodeType !== 1) {
        node = node.nextSibling;
      }

      if (node && node.id === 'wpadminbar') {
        node = node.nextSibling;
        while (node && node.nodeType !== 1) {
          node = node.nextSibling;
        }
      }

      return node;
    }

    function finalizeHeaderSlots() {
      restoreHeaderContainers();
    }

    function canManageHeaderSlots() {
      return document.readyState !== 'loading';
    }

    function mountHeaderToBody() {
      var insertRef = getBodyInsertRef();

      if (header.parentElement !== document.body) {
        var parent = header.parentElement;
        while (parent && parent !== document.body) {
          if (parent.classList && (
            parent.classList.contains('elementor-section') ||
            parent.classList.contains('e-con') ||
            parent.classList.contains('elementor-column') ||
            parent.classList.contains('elementor-widget') ||
            parent.classList.contains('elementor-widget-container') ||
            parent.classList.contains('elementor-widget-shortcode') ||
            parent.classList.contains('elementor-widget-html')
          )) {
            parent.setAttribute('data-humic-header-slot', '1');
          }
          parent = parent.parentElement;
        }
        document.body.insertBefore(header, insertRef);
      }

      header.dataset.humicMounted = '1';
      document.body.classList.add('humic-header-mounted');
      restoreHeaderContainers();

      if (!canManageHeaderSlots()) {
        return;
      }

      finalizeHeaderSlots();
    }

    function portalHeader() {
      if (isMobileLayout()) {
        return;
      }
      mountHeaderToBody();
      header.dataset.portaled = '1';
      document.body.classList.add('humic-header-portaled');
      document.documentElement.classList.add('humic-header-portaled');
    }

    function resetTopbarInlineStyles() {
      stripHeaderInlineStyles();
    }

    function resetMobileHeaderLayout() {
      header.classList.remove('humic-header-fixed');
      document.body.classList.remove('humic-header-pinned');
      document.body.classList.remove('humic-header-portaled');
      document.documentElement.classList.remove('humic-header-portaled');
      document.body.style.removeProperty('padding-top');
      header.style.removeProperty('top');
      header.style.removeProperty('position');
      restoreHeaderContainers();
    }

    function clearDesktopPinStyles() {
      resetMobileHeaderLayout();
    }

    function applyHeaderLayout() {
      stripHeaderInlineStyles();
      mountHeaderToBody();

      var mobile = isMobileLayout();
      if (mobile) {
        document.body.classList.add('humic-layout-mobile');
        document.body.classList.remove('humic-layout-desktop', 'humic-header-static-mobile');
        document.documentElement.classList.add('humic-layout-mobile');
        document.documentElement.classList.remove('humic-layout-desktop');
        header.classList.remove('humic-header-static-mobile');
        header.dataset.portaled = 'mobile-fixed';
        forceMobileTopbar();
      } else {
        document.body.classList.remove('humic-header-static-mobile', 'humic-layout-mobile');
        document.body.classList.add('humic-layout-desktop');
        document.documentElement.classList.remove('humic-layout-mobile');
        document.documentElement.classList.add('humic-layout-desktop');
        header.classList.remove('humic-header-static-mobile');
        header.dataset.portaled = '1';
      }

      pinHeader();

      if (!mobile) {
        forceDesktopTopbar();
      }

      spacer = document.getElementById('humic-header-spacer');
      if (spacer) {
        spacer.style.display = 'none';
        spacer.style.height = '0';
        spacer.style.margin = '0';
        spacer.style.padding = '0';
      }
    }

    function pinHeader() {
      // Replaced by native position: sticky CSS
    }

    function getAdminBarOffset() {
      var adminBar = document.getElementById('wpadminbar');
      if (!adminBar) {
        return 0;
      }

      var style = window.getComputedStyle(adminBar);
      if (style.display === 'none' || style.visibility === 'hidden') {
        return 0;
      }

      var rect = adminBar.getBoundingClientRect();
      if (rect.height > 0) {
        return Math.ceil(rect.bottom);
      }

      return window.matchMedia('(max-width: 782px)').matches ? 46 : 32;
    }

    function getVisibleHeaderHeight() {
      var mobile = isMobileLayout();
      var minHeight = mobile ? 96 : 106;
      var topbar = header.querySelector('.topbar');
      var navbar = header.querySelector('.navbar');
      var topbarHeight = 0;
      var navbarHeight = 0;

      if (!mobile && topbar) {
        topbarHeight = topbar.offsetHeight || 36;
        if (topbarHeight < 20) {
          topbarHeight = 36;
        }
      }

      if (navbar) {
        navbarHeight = navbar.offsetHeight || (mobile ? 60 : 70);
      }

      var menu = document.getElementById('mobile-menu');
      var menuHeight = 0;
      if (!mobile && menu && menu.classList.contains('open')) {
        menuHeight = menu.getBoundingClientRect().height;
      }
      var total = topbarHeight + navbarHeight + menuHeight;
      var rect = header.getBoundingClientRect();

      return Math.max(minHeight, total, Math.round(rect.height));
    }

    function forceMobileTopbar() {
      var topbar = header.querySelector('.topbar');
      if (!topbar) {
        return;
      }

      topbar.style.setProperty('display', 'flex', 'important');
      topbar.style.setProperty('height', '36px', 'important');
      topbar.style.setProperty('min-height', '36px', 'important');
      topbar.style.setProperty('max-height', '36px', 'important');
      topbar.style.setProperty('visibility', 'visible', 'important');
      topbar.style.setProperty('opacity', '1', 'important');
      topbar.style.setProperty('overflow', 'visible', 'important');
      topbar.style.setProperty('margin', '0px', 'important');
      topbar.style.setProperty('padding', '0px', 'important');
      topbar.style.setProperty('background', '#CC0000', 'important');
    }

    function forceDesktopTopbar() {
      if (isMobileLayout()) return;
      var topbar = header.querySelector('.topbar');
      if (!topbar) {
        return;
      }

      topbar.style.setProperty('display', 'flex', 'important');
      topbar.style.setProperty('height', '36px', 'important');
      topbar.style.setProperty('min-height', '36px', 'important');
      topbar.style.setProperty('max-height', 'none', 'important');
      topbar.style.setProperty('visibility', 'visible', 'important');
      topbar.style.setProperty('opacity', '1', 'important');
      topbar.style.setProperty('overflow', 'visible', 'important');
      topbar.style.setProperty('background', '#CC0000', 'important');
    }

    function applyPinnedBodyOffset(adminOffset, height) {
      document.documentElement.style.setProperty('--humic-sticky-top', adminOffset + 'px');
      forceDesktopTopbar();
    }

    function updateHeaderHeight() {
      applyHeaderLayout();
    }

    function bindMobileTouchFeedback(root) {
      if (!root) return;
      root.querySelectorAll('.mob-link, .mob-sublink').forEach(function (el) {
        if (el.dataset.humicTouchInit === '1') return;
        el.dataset.humicTouchInit = '1';

        function clearPressed() {
          el.classList.remove('is-pressed');
        }

        el.addEventListener('pointerdown', function () {
          el.classList.add('is-pressed');
        });
        el.addEventListener('pointerup', clearPressed);
        el.addEventListener('pointercancel', clearPressed);
        el.addEventListener('pointerleave', clearPressed);
      });
    }

    function onHeaderScroll() {
      if (scrollTicking) return;
      scrollTicking = true;
      requestAnimationFrame(function () {
        var adminOffset = Math.max(0, getAdminBarOffset());
        document.documentElement.style.setProperty('--humic-sticky-top', adminOffset + 'px');
        if (header.classList.contains('humic-header-fixed') || document.body.classList.contains('humic-header-pinned')) {
          header.style.setProperty('top', adminOffset + 'px', 'important');
        }

        if (!isMobileLayout() && (header.parentElement !== document.body || isHeaderInsideElementor())) {
          updateHeaderHeight();
        }
        if (!isMobileLayout()) {
          forceDesktopTopbar();
        } else if (typeof syncMobileMenuPosition === 'function' && document.body.classList.contains('humic-mobile-nav-open')) {
          syncMobileMenuPosition();
        }
        var scrolled = window.scrollY > 10;
        if (navbar) navbar.classList.toggle('scrolled', scrolled);
        header.classList.toggle('scrolled', scrolled);
        scrollTicking = false;
      });
    }

    function initMobileDropdowns() {
      document.querySelectorAll('.mob-dropdown-toggle').forEach(function (btn) {
        if (btn.dataset.humicDropdownInit === '1') return;
        btn.dataset.humicDropdownInit = '1';
        btn.addEventListener('click', function (event) {
          event.preventDefault();
          var group = btn.closest('.mob-nav-group');
          if (!group) return;
          var willOpen = !group.classList.contains('open');

          document.querySelectorAll('.mob-nav-group.open').forEach(function (other) {
            if (other === group) return;
            other.classList.remove('open');
            var otherToggle = other.querySelector('.mob-dropdown-toggle');
            if (otherToggle) otherToggle.setAttribute('aria-expanded', 'false');
          });

          group.classList.toggle('open', willOpen);
          btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

          if (typeof window.humicSyncMobileMenuPanel === 'function') {
            window.requestAnimationFrame(window.humicSyncMobileMenuPanel);
          }
        });
      });

      bindMobileTouchFeedback(document.getElementById('mobile-menu'));

      document.querySelectorAll('.mob-nav-group').forEach(function (group) {
        var hasActive = group.querySelector('.mob-sublink.active, .mob-link.active');
        if (hasActive) {
          group.classList.add('open');
          var toggle = group.querySelector('.mob-dropdown-toggle');
          if (toggle) toggle.setAttribute('aria-expanded', 'true');
        }
      });
    }

    function measureMobileMenuHeight(menu) {
      var previousMaxHeight = menu.style.maxHeight;
      menu.style.maxHeight = 'none';
      var height = menu.scrollHeight;
      menu.style.maxHeight = previousMaxHeight;
      return height;
    }

    function syncMobileMenuHeight() {
      var mobileMenu = document.getElementById('mobile-menu');
      if (!mobileMenu || isMobileLayout()) return;

      if (!mobileMenu.classList.contains('open')) {
        mobileMenu.style.maxHeight = '0px';
        return;
      }

      var cap = Math.max(window.innerHeight - 80, 320);
      mobileMenu.style.maxHeight = Math.min(measureMobileMenuHeight(mobileMenu), cap) + 'px';
    }

    function initMobileNav() {
      var burger = document.getElementById('burger');
      var mobileMenu = document.getElementById('mobile-menu');
      if (!burger || !mobileMenu || burger.dataset.humicNavInit === '1') return;
      burger.dataset.humicNavInit = '1';

      backdrop = document.getElementById('mobile-menu-backdrop');
      if (!backdrop) {
        backdrop = document.createElement('div');
        backdrop.id = 'mobile-menu-backdrop';
        backdrop.className = 'mobile-menu-backdrop';
        backdrop.setAttribute('aria-hidden', 'true');
        document.body.appendChild(backdrop);
      }

      var navbar = header.querySelector('.navbar');
      if (navbar && mobileMenu.parentElement !== document.body) {
        document.body.appendChild(mobileMenu);
      }

      var savedScrollY = 0;
      var mobileMenuFocusHandler = null;

      burger.setAttribute('aria-controls', 'mobile-menu');

      function getMobileMenuFocusables() {
        return Array.prototype.slice.call(
          mobileMenu.querySelectorAll(
            'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
          )
        ).filter(function (el) {
          return el.offsetWidth > 0 || el.offsetHeight > 0 || el.getClientRects().length > 0;
        });
      }

      function focusFirstMobileMenuItem() {
        var items = getMobileMenuFocusables();
        if (items.length) {
          items[0].focus();
        }
      }

      function syncMobileMenuPanelHeight() {
        var topVal = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--humic-mobile-menu-top'), 10);
        if (isNaN(topVal)) {
          topVal = 96;
        }

        var adminBar = document.getElementById('wpadminbar');
        if (adminBar) {
          var adminStyle = window.getComputedStyle(adminBar);
          if (adminStyle.display !== 'none' && adminStyle.visibility !== 'hidden') {
            var adminBottom = Math.ceil(adminBar.getBoundingClientRect().bottom);
            if (adminBottom > topVal) {
              topVal = adminBottom;
            }
          }
        }

        var available = Math.max(window.innerHeight - topVal - 8, 220);
        document.documentElement.style.setProperty('--humic-mobile-menu-max-height', available + 'px');
      }

      syncMobileMenuPosition = function () {
        var headerRect = header.getBoundingClientRect();
        var topbar = header.querySelector('.topbar');
        var navbar = header.querySelector('.navbar');
        var navbarInner = header.querySelector('.navbar-inner');
        var top = Math.max(Math.round(headerRect.bottom), 0);

        [topbar, navbar, navbarInner].forEach(function (el) {
          if (!el) return;
          var style = window.getComputedStyle(el);
          if (style.display === 'none' || style.visibility === 'hidden') return;
          var rect = el.getBoundingClientRect();
          top = Math.max(top, Math.round(rect.bottom));
        });

        if (isMobileLayout()) {
          var adminOffset = getAdminBarOffset();
          var topbarHeight = topbar ? Math.round(topbar.getBoundingClientRect().height) : 0;
          var navbarHeight = navbar ? Math.round(navbar.getBoundingClientRect().height) : 0;
          var navbarInnerHeight = navbarInner ? Math.round(navbarInner.getBoundingClientRect().height) : 0;
          if (topbarHeight < 20) topbarHeight = 36;
          if (Math.max(navbarHeight, navbarInnerHeight) < 40) navbarHeight = 60;
          top = Math.max(top, adminOffset + topbarHeight + Math.max(navbarHeight, navbarInnerHeight, 60));
        }

        var adminBar = document.getElementById('wpadminbar');

        if (adminBar) {
          var adminStyle = window.getComputedStyle(adminBar);
          if (adminStyle.display !== 'none' && adminStyle.visibility !== 'hidden') {
            var adminBottom = Math.ceil(adminBar.getBoundingClientRect().bottom);
            if (adminBottom > top) {
              top = adminBottom;
            }
          }
        }

        document.documentElement.style.setProperty('--humic-mobile-menu-top', top + 'px');
        document.documentElement.style.setProperty('--humic-adminbar-height', getAdminBarOffset() + 'px');
        backdrop.style.top = top + 'px';
        syncMobileMenuPanelHeight();
      };

      function bindMobileMenuFocusTrap() {
        if (mobileMenuFocusHandler) return;

        mobileMenuFocusHandler = function (event) {
          if (!document.body.classList.contains('humic-mobile-nav-open')) return;

          if (event.key === 'Escape') {
            event.preventDefault();
            setMenuOpen(false);
            return;
          }

          if (event.key !== 'Tab') return;

          var items = getMobileMenuFocusables();
          if (!items.length) return;

          var first = items[0];
          var last = items[items.length - 1];
          var activeInMenu = mobileMenu.contains(document.activeElement);

          if (!activeInMenu) {
            event.preventDefault();
            first.focus();
            return;
          }

          if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
          } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
          }
        };

        document.addEventListener('keydown', mobileMenuFocusHandler);
      }

      function unbindMobileMenuFocusTrap() {
        if (!mobileMenuFocusHandler) return;
        document.removeEventListener('keydown', mobileMenuFocusHandler);
        mobileMenuFocusHandler = null;
      }

      window.humicSyncMobileMenuPanel = function () {
        syncMobileMenuPosition();
      };

      function lockBodyScroll() {
        // Just rely on CSS overflow: hidden on body.humic-mobile-nav-open
      }

      function unlockBodyScroll() {
        // Just rely on CSS overflow: hidden removal
      }

      function resetMobileSubmenus() {
        document.querySelectorAll('.mob-nav-group.open').forEach(function (group) {
          group.classList.remove('open');
          var toggle = group.querySelector('.mob-dropdown-toggle');
          if (toggle) toggle.setAttribute('aria-expanded', 'false');
          var submenu = group.querySelector('.mob-submenu');
          if (submenu) submenu.removeAttribute('style');
        });
      }

      function setMenuOpen(isOpen) {
        if (isOpen) {
          if (!isMobileLayout()) return;
          if (document.body.classList.contains('humic-mobile-nav-open')) return;

          delete mobileMenu.dataset.closingHandled;
          document.body.classList.remove('humic-mobile-nav-closing');
          syncMobileMenuPosition();
          lockBodyScroll();

          mobileMenu.classList.remove('is-closing', 'is-visible');
          mobileMenu.removeAttribute('style');
          document.body.classList.remove('humic-mobile-nav-closing');
          document.body.classList.add('humic-mobile-nav-open');
          mobileMenu.classList.add('open');
          mobileMenu.setAttribute('aria-hidden', 'false');
          burger.setAttribute('aria-expanded', 'true');
          bindMobileMenuFocusTrap();

          syncMobileMenuPosition();

          window.requestAnimationFrame(function () {
            focusFirstMobileMenuItem();
          });
        } else {
          if (!document.body.classList.contains('humic-mobile-nav-open') || mobileMenu.classList.contains('is-closing')) {
            return;
          }

          syncMobileMenuPosition();
          document.body.classList.remove('humic-mobile-nav-open');
          document.body.classList.add('humic-mobile-nav-closing');
          mobileMenu.classList.remove('open');
          mobileMenu.classList.add('is-closing');

          window.setTimeout(function () {
            if (mobileMenu.classList.contains('is-closing')) {
              finishMobileMenuClose();
            }
          }, 500);
        }
      }

      function finishMobileMenuClose() {
        if (mobileMenu.dataset.closingHandled === '1') return;
        mobileMenu.dataset.closingHandled = '1';

        mobileMenu.classList.remove('open', 'is-closing', 'is-visible');
        mobileMenu.removeAttribute('style');
        unbindMobileMenuFocusTrap();
        burger.setAttribute('aria-expanded', 'false');
        mobileMenu.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('humic-mobile-nav-open', 'humic-mobile-nav-closing');
        unlockBodyScroll();
        resetMobileSubmenus();

        window.requestAnimationFrame(function () {
          burger.focus();
          delete mobileMenu.dataset.closingHandled;
        });
      }

      burger.addEventListener('click', function () {
        setMenuOpen(!document.body.classList.contains('humic-mobile-nav-open'));
      });

      mobileMenu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          setMenuOpen(false);
        });
      });

      backdrop.addEventListener('click', function () {
        setMenuOpen(false);
      });

      mobileMenu.addEventListener('transitionend', function (event) {
        if (event.target !== mobileMenu) return;
        if (event.propertyName !== 'max-height') return;

        if (mobileMenu.classList.contains('is-closing')) {
          finishMobileMenuClose();
        }
      });

      bindMobileTouchFeedback(mobileMenu);

      window.addEventListener('resize', function () {
        if (window.innerWidth > 768) {
          if (document.body.classList.contains('humic-mobile-nav-open') || mobileMenu.classList.contains('is-closing')) {
            finishMobileMenuClose();
          }
          return;
        }

        forceMobileTopbar();
        syncMobileMenuPosition();

        if (document.body.classList.contains('humic-mobile-nav-open')) {
          window.requestAnimationFrame(syncMobileMenuPosition);
        }
      });

      var adminBarEl = document.getElementById('wpadminbar');
      if (adminBarEl && window.ResizeObserver) {
        new ResizeObserver(function () {
          if (document.body.classList.contains('humic-mobile-nav-open')) {
            syncMobileMenuPosition();
          }
        }).observe(adminBarEl);
      }

      if (isMobileLayout()) {
        syncMobileMenuPosition();
      }
    }

    zeroTopGap();
    updateHeaderHeight();
    zeroTopGap();
    initMobileNav();
    initMobileDropdowns();

    window.addEventListener('load', function () {
      updateHeaderHeight();
      window.setTimeout(updateHeaderHeight, 200);
    });

    header.querySelectorAll('img').forEach(function (img) {
      if (img.complete) return;
      img.addEventListener('load', updateHeaderHeight, { once: true });
    });

    var adminBar = document.getElementById('wpadminbar');
    if (adminBar && window.ResizeObserver) {
      new ResizeObserver(updateHeaderHeight).observe(adminBar);
    }

    window.addEventListener('resize', updateHeaderHeight);
    window.addEventListener('orientationchange', function () {
      window.setTimeout(updateHeaderHeight, 150);
    });
    window.addEventListener('scroll', onHeaderScroll, { passive: true });
    onHeaderScroll();

    if (window.ResizeObserver) {
      new ResizeObserver(updateHeaderHeight).observe(header);
    }

    window.humicGetHeaderOffset = function () {
      var height = getVisibleHeaderHeight();
      var buffer = isMobileLayout() ? 16 : 32;
      if (isMobileLayout()) {
        return height > 0 ? height + buffer : 76;
      }
      return height > 0 ? height + getAdminBarOffset() + buffer : 140;
    };
    window.humicSyncHeader = updateHeaderHeight;

    return true;
  }

  function boot() {
    if (!initHumicHeader()) {
      window.setTimeout(initHumicHeader, 100);
      window.setTimeout(initHumicHeader, 500);
      window.setTimeout(initHumicHeader, 1200);
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  window.addEventListener('load', function () {
    initHumicHeader();
    if (typeof window.humicSyncHeader === 'function') {
      window.humicSyncHeader();
      window.setTimeout(window.humicSyncHeader, 100);
      window.setTimeout(window.humicSyncHeader, 500);
    }
  });
  window.addEventListener('resize', function () {
    if (typeof window.humicSyncHeader === 'function') {
      window.humicSyncHeader();
    }
  });

  if (window.elementorFrontend && window.elementorFrontend.hooks) {
    window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function () {
      initHumicHeader();
    });
  }
})();

(function () {
  var sections = document.querySelectorAll('section[id]');
  var navLinks = document.querySelectorAll('#site-header a[data-humic-nav], #site-header a.nav-link, #site-header a.mob-link');

  function normalizePath(path) {
    var value = (path || '/').replace(/\/+$/, '') || '/';
    return value.toLowerCase();
  }

  function getLinkHash(href) {
    if (!href) return '';
    var hashIndex = href.indexOf('#');
    return hashIndex >= 0 ? href.slice(hashIndex) : '';
  }

  function linkPath(href) {
    try {
      return normalizePath(new URL(href, window.location.href).pathname);
    } catch (e) {
      return '';
    }
  }

  function isFrontPage() {
    return document.body.classList.contains('home');
  }

  function isHomepageHashLink(href) {
    var hash = getLinkHash(href);
    if (!hash || hash === '#') return false;

    try {
      var linkUrl = new URL(href, window.location.href);
      var pagePath = normalizePath(window.location.pathname);
      var linkPathname = normalizePath(linkUrl.pathname);
      return linkPathname === pagePath;
    } catch (e) {
      return href.charAt(0) === '#';
    }
  }

  function pathMatchesNavItem(path, navItem) {
    if (navItem === 'news') {
      return /(^|\/)news$/i.test(path) || /humic_news/i.test(path);
    }
    if (navItem === 'events') {
      return /(^|\/)events$/i.test(path) || /humic_event/i.test(path);
    }
    return false;
  }

  function markCurrentPageLinks() {
    var currentPath = normalizePath(window.location.pathname);

    navLinks.forEach(function (link) {
      var navItem = link.getAttribute('data-humic-nav');
      var href = link.getAttribute('href') || '';
      var hash = getLinkHash(href);

      if (hash && hash !== '#') {
        return;
      }

      if (navItem && pathMatchesNavItem(currentPath, navItem)) {
        link.classList.add('active');
        return;
      }

      var targetPath = linkPath(href);
      if (targetPath && targetPath === currentPath) {
        link.classList.add('active');
      }
    });
  }

  function syncHomepageSectionActive(current) {
    if (!isFrontPage() || !current) return;

    navLinks.forEach(function (link) {
      var href = link.getAttribute('href') || '';
      var navItem = link.getAttribute('data-humic-nav');

      if (isHomepageHashLink(href)) {
        link.classList.toggle('active', getLinkHash(href) === '#' + current);
        return;
      }

      if (navItem === 'news' || navItem === 'events') {
        link.classList.toggle('active', navItem === current);
      }
    });
  }

  function onScroll() {
    if (!sections.length) return;

    var scrollY = window.scrollY + (window.humicGetHeaderOffset ? window.humicGetHeaderOffset() : 106);
    var current = '';

    sections.forEach(function (sec) {
      if (sec.offsetTop <= scrollY) {
        current = sec.getAttribute('id');
      }
    });

    if (!current) return;

    if (isFrontPage()) {
      syncHomepageSectionActive(current);
      return;
    }

    navLinks.forEach(function (link) {
      var href = link.getAttribute('href');
      if (!isHomepageHashLink(href)) return;

      link.classList.remove('active');
      if (getLinkHash(href) === '#' + current) {
        link.classList.add('active');
      }
    });
  }

  markCurrentPageLinks();
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();

(function () {
  document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
    anchor.addEventListener('click', function (e) {
      var href = this.getAttribute('href');
      if (!href || href === '#') return;
      var target = document.querySelector(href);
      if (!target) return;
      e.preventDefault();
      var offset = window.humicGetHeaderOffset ? window.humicGetHeaderOffset() : 106;
      var top = target.getBoundingClientRect().top + window.scrollY - offset;
      window.scrollTo({ top: top, behavior: 'smooth' });
    });
  });
})();

(function () {
  var reveals = document.querySelectorAll('.rcard, .news-feat, .news-item, .about-img-col, .pcard');
  if (!reveals.length || !('IntersectionObserver' in window)) return;

  reveals.forEach(function (el) { el.classList.add('reveal'); });

  var observer = new IntersectionObserver(
    function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.12 }
  );

  reveals.forEach(function (el) { observer.observe(el); });
})();

(function () {
  document.querySelectorAll('.news-feat, .news-item').forEach(function (card) {
    card.addEventListener('click', function (e) {
      if (e.target.closest('a')) return;
      var link = card.querySelector('.nmore');
      if (!link) return;
      var href = link.getAttribute('href');
      if (!href || href === '#' || href === '') return;
      if (link.getAttribute('target') === '_blank') {
        window.open(href, '_blank', 'noopener,noreferrer');
      } else {
        window.location.href = href;
      }
    });
    card.style.cursor = 'pointer';
  });
})();

(function () {
  var btn = document.getElementById('back-to-top');
  if (!btn) return;

  function toggleBtn() {
    if (window.scrollY > 400) {
      btn.removeAttribute('hidden');
      btn.classList.add('visible');
    } else {
      btn.setAttribute('hidden', '');
      btn.classList.remove('visible');
    }
  }

  window.addEventListener('scroll', toggleBtn, { passive: true });
  toggleBtn();

  btn.addEventListener('click', function () {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();

(function () {
  function parseStatValue(str) {
    var s = String(str || '').trim();
    var match = s.match(/^(\D*?)(\d+(?:\.\d+)?)(\D*)$/);
    if (!match) return null;
    return {
      prefix: match[1],
      number: parseFloat(match[2]),
      suffix: match[3],
      raw: s
    };
  }

  function formatStatValue(parsed, value) {
    var n = parsed.number % 1 === 0 ? Math.round(value) : Math.round(value * 10) / 10;
    return parsed.prefix + n + parsed.suffix;
  }

  function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
  }

  function animateStatValue(el, parsed, duration) {
    if (el.dataset.statAnimated === '1') return;
    el.dataset.statAnimated = '1';

    var start = performance.now();
    var target = parsed.number;

    function tick(now) {
      var progress = Math.min((now - start) / duration, 1);
      var current = easeOutQuart(progress) * target;
      el.textContent = formatStatValue(parsed, current);
      if (progress < 1) {
        requestAnimationFrame(tick);
      } else {
        el.textContent = parsed.raw;
      }
    }

    requestAnimationFrame(tick);
  }

  function initStatsCounter() {
    var bar = document.querySelector('.stats-bar');
    if (!bar || bar.dataset.humicStatsInit === '1') return;

    var vals = bar.querySelectorAll('.stat-val[data-stat-value]');
    if (!vals.length) return;

    var parsedList = [];
    vals.forEach(function (el) {
      var parsed = parseStatValue(el.getAttribute('data-stat-value'));
      if (parsed) {
        parsedList.push({ el: el, parsed: parsed });
        el.textContent = formatStatValue(parsed, 0);
      }
    });

    if (!parsedList.length) return;

    bar.dataset.humicStatsInit = '1';

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      parsedList.forEach(function (item) {
        item.el.textContent = item.parsed.raw;
      });
      return;
    }

    function runAnimation() {
      parsedList.forEach(function (item) {
        animateStatValue(item.el, item.parsed, 2000);
      });
    }

    if (!('IntersectionObserver' in window)) {
      runAnimation();
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            runAnimation();
            observer.disconnect();
          }
        });
      },
      { threshold: 0.35 }
    );

    observer.observe(bar);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initStatsCounter);
  } else {
    initStatsCounter();
  }
})();

(function () {
  function closeAccordion(card) {
    if (!card) return;
    var head = card.querySelector('.rcard-accordion-head');
    var body = card.querySelector('.rcard-accordion-body');
    card.classList.remove('is-expanded');
    if (head) head.setAttribute('aria-expanded', 'false');
    if (body) {
      body.style.maxHeight = body.scrollHeight + 'px';
      body.offsetHeight;
      body.style.maxHeight = '0';
      body.style.opacity = '0';
    }
  }

  function scrollToResearchCard(card) {
    window.setTimeout(function () {
      var offset = window.humicGetHeaderOffset ? window.humicGetHeaderOffset() : 106;
      var top = card.getBoundingClientRect().top + window.scrollY - offset - 16;
      window.scrollTo({ top: Math.max(top, 0), behavior: 'smooth' });
    }, 480);
  }

  function openAccordion(card, grid, scrollIntoView) {
    var head = card.querySelector('.rcard-accordion-head');
    var body = card.querySelector('.rcard-accordion-body');
    if (!body) return;

    grid.querySelectorAll('.rcard-accordion.is-expanded').forEach(function (other) {
      if (other !== card) closeAccordion(other);
    });

    card.classList.add('is-expanded');
    if (head) head.setAttribute('aria-expanded', 'true');

    body.style.maxHeight = 'none';
    var fullHeight = body.scrollHeight;
    body.style.maxHeight = '0';
    body.offsetHeight;
    body.style.maxHeight = fullHeight + 'px';
    body.style.opacity = '1';

    if (scrollIntoView) {
      scrollToResearchCard(card);
    }
  }

  function initResearchAccordions() {
    var grid = document.querySelector('.humic-research-page-grid');
    if (!grid || grid.dataset.humicAccordionInit === '1') return;
    grid.dataset.humicAccordionInit = '1';

    grid.querySelectorAll('.rcard-accordion-body').forEach(function (body) {
      body.style.maxHeight = '0';
      body.style.opacity = '0';
    });

    grid.addEventListener('click', function (e) {
      var head = e.target.closest('.rcard-accordion-head');
      if (!head) return;

      var card = head.closest('.rcard-accordion');
      if (!card || !card.querySelector('.rcard-accordion-body')) return;

      if (card.classList.contains('is-expanded')) {
        closeAccordion(card);
      } else {
        openAccordion(card, grid);
      }
    });

    function openFromHash() {
      if (!window.location.hash) return;
      var target = document.getElementById(window.location.hash.slice(1));
      if (target && target.classList.contains('rcard-accordion')) {
        openAccordion(target, grid, true);
      }
    }

    openFromHash();
    window.addEventListener('hashchange', openFromHash);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initResearchAccordions);
  } else {
    initResearchAccordions();
  }
})();

(function () {
  function initMembersFilter() {
    var filterNav = document.querySelector('.humic-members-filter');
    var grid = document.getElementById('humic-researcher-grid');
    var emptyMsg = document.getElementById('humic-members-filter-empty');
    if (!filterNav || !grid || filterNav.dataset.humicFilterInit === '1') {
      return;
    }
    filterNav.dataset.humicFilterInit = '1';

    var cards = grid.querySelectorAll('[data-member-letter]');

    filterNav.addEventListener('click', function (e) {
      var btn = e.target.closest('.humic-members-filter-btn');
      if (!btn) return;

      var filter = btn.getAttribute('data-filter');
      filterNav.querySelectorAll('.humic-members-filter-btn').forEach(function (b) {
        b.classList.toggle('active', b === btn);
      });

      var visible = 0;
      cards.forEach(function (card) {
        var show = filter === 'all' || card.getAttribute('data-member-letter') === filter;
        card.style.display = show ? '' : 'none';
        if (show) visible++;
      });

      if (emptyMsg) {
        emptyMsg.hidden = visible > 0;
      }
    });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMembersFilter);
  } else {
    initMembersFilter();
  }
})();
