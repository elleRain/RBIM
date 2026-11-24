<script>
  (function () {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var menuBtn = document.getElementById('menu-btn');

    function closeSidebar() {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
      overlay.hidden = true;
    }

    function openSidebar() {
      sidebar.classList.add('open');
      overlay.classList.add('show');
      overlay.hidden = false;
    }

    if (menuBtn) {
      menuBtn.addEventListener('click', function () {
        if (sidebar.classList.contains('open')) closeSidebar();
        else openSidebar();
      });
    }
    if (overlay) overlay.addEventListener('click', closeSidebar);

    var userMenu = document.getElementById('user-menu');
    var userMenuToggle = document.getElementById('user-menu-toggle');

    function closeUserMenu() {
      if (!userMenu || !userMenuToggle) return;
      userMenu.classList.remove('open');
      userMenuToggle.setAttribute('aria-expanded', 'false');
    }

    function openUserMenu() {
      if (!userMenu || !userMenuToggle) return;
      userMenu.classList.add('open');
      userMenuToggle.setAttribute('aria-expanded', 'true');
    }

    if (userMenuToggle) {
      userMenuToggle.addEventListener('click', function (e) {
        e.stopPropagation();
        if (userMenu.classList.contains('open')) closeUserMenu();
        else openUserMenu();
      });
    }

    document.addEventListener('click', function (e) {
      if (userMenu && !userMenu.contains(e.target)) closeUserMenu();
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeUserMenu();
    });
  })();
</script>
