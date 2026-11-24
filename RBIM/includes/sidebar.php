<?php
/**
 * Shared sidebar navigation (super admin + barangay admin).
 * Expects: $activeNav, $logo (optional), $user (optional; falls back to current_user())
 */
$activeNav = $activeNav ?? 'dashboard';
$logo = $logo ?? app_url('assets/img/system-logo.jpg');
$navUser = $user ?? (function_exists('current_user') ? current_user() : null);
$navRole = (string) ($navUser['role'] ?? '');
$isSuperAdmin = $navRole === 'super_admin';
$isMuniAdmin = $navRole === 'muni_admin';
?>
<aside class="sidebar" id="sidebar" aria-label="<?php echo $isSuperAdmin ? 'Super admin navigation' : 'Admin navigation'; ?>">
  <div class="sidebar-brand">
    <img src="<?php echo htmlspecialchars($logo); ?>" alt="CPD logo" width="40" height="40">
    <div class="sidebar-brand-text">
      <strong>RBIM</strong>
      <span><?php echo $isSuperAdmin ? 'Super Admin Portal' : ($isMuniAdmin ? 'Municipality Admin' : 'Barangay Admin'); ?></span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($isSuperAdmin): ?>
      <p class="nav-label">Overview</p>
      <a class="nav-link<?php echo $activeNav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/dashboard.php')); ?>">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        Dashboard
      </a>
      <a class="nav-link<?php echo $activeNav === 'statistics_reports' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/reports/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg>
        Statistics Reports
      </a>
      <a class="nav-link<?php echo $activeNav === 'city_statistics' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/city-municipality/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
        City/Municipality
      </a>
      <a class="nav-link<?php echo $activeNav === 'barangay_statistics' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/barangay/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
        Barangay Statistics
      </a>

      <p class="nav-label">Tools</p>
      <a class="nav-link<?php echo $activeNav === 'question_assessment' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('question_assessment_tool/question_assessment_tool.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/><path d="M9 9h1"/></svg>
        Question Assessment Tool
      </a>

      <div class="sidebar-nav-bottom">
        <p class="nav-label">Administration</p>
        <a class="nav-link<?php echo $activeNav === 'user_management' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/users/index.php')); ?>">
          <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          User Management
        </a>
      </div>
    <?php elseif ($isMuniAdmin): ?>
      <p class="nav-label">Overview</p>
      <a class="nav-link<?php echo $activeNav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/dashboard.php')); ?>">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        Dashboard
      </a>
      <a class="nav-link<?php echo $activeNav === 'statistics_reports' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/reports/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg>
        Statistics Reports
      </a>
      <a class="nav-link<?php echo $activeNav === 'barangay_statistics' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/barangay/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg>
        Barangay Statistics
      </a>

      <p class="nav-label">Tools</p>
      <a class="nav-link<?php echo $activeNav === 'question_assessment' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('question_assessment_tool/question_assessment_tool.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M9 13h6"/><path d="M9 17h6"/><path d="M9 9h1"/></svg>
        Question Assessment Tool
      </a>
    <?php else: ?>
      <p class="nav-label">Overview</p>
      <a class="nav-link<?php echo $activeNav === 'dashboard' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/dashboard.php')); ?>">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
        Dashboard
      </a>
      <a class="nav-link<?php echo $activeNav === 'statistics_reports' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/reports/statistics.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M7 16V9"/><path d="M12 16V5"/><path d="M17 16v-6"/></svg>
        Statistics Reports
      </a>

      <p class="nav-label">Households</p>
      <a class="nav-link<?php echo $activeNav === 'household_list' ? ' active' : ''; ?>" href="<?php echo htmlspecialchars(app_url('pages/household_list.php')); ?>">
        <svg viewBox="0 0 24 24"><path d="M8 6h13"/><path d="M8 12h13"/><path d="M8 18h13"/><path d="M3 6h.01"/><path d="M3 12h.01"/><path d="M3 18h.01"/></svg>
        Household List
      </a>
    <?php endif; ?>
  </nav>
</aside>
