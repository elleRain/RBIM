<?php
/**
 * Shared topbar + user menu.
 * Expects: $pageHeading, $pageSubheading, $user_name, $user_initials, $role_label, $user, $logout
 * Optional: $backUrl, $backLabel, $showFullscreenBtn, $showCrossGraphBtn, $topbarWithFilters, $topbarFiltersPartial
 */
$pageHeading = $pageHeading ?? 'Dashboard';
$pageSubheading = $pageSubheading ?? '';
$backUrl = $backUrl ?? null;
$backLabel = $backLabel ?? 'Back';
$showFullscreenBtn = $showFullscreenBtn ?? false;
$showCrossGraphBtn = $showCrossGraphBtn ?? false;
$topbarWithFilters = $topbarWithFilters ?? false;
$topbarFiltersPartial = $topbarFiltersPartial ?? null;
?>
<header class="topbar<?php echo $topbarWithFilters ? ' topbar--with-filters' : ''; ?>">
  <div class="topbar-left">
    <button type="button" class="menu-btn" id="menu-btn" aria-label="Open menu">
      <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <?php if (!empty($backUrl)): ?>
      <a href="<?php echo htmlspecialchars($backUrl); ?>" class="topbar-back" title="<?php echo htmlspecialchars($backLabel); ?>">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        <span><?php echo htmlspecialchars($backLabel); ?></span>
      </a>
    <?php endif; ?>
    <div class="page-heading"<?php echo $pageHeading === '' ? ' hidden' : ''; ?>>
      <h1><?php echo htmlspecialchars($pageHeading); ?></h1>
      <?php if ($pageSubheading !== ''): ?>
        <p class="page-subheading"><?php echo htmlspecialchars($pageSubheading); ?></p>
      <?php endif; ?>
      <?php if ($topbarWithFilters && $topbarFiltersPartial && is_file($topbarFiltersPartial)): ?>
        <?php require $topbarFiltersPartial; ?>
      <?php endif; ?>
    </div>
  </div>
  <div class="topbar-right">
    <?php if ($showCrossGraphBtn): ?>
      <button type="button" class="topbar-cross-graph-btn" id="cross-graph-open" aria-haspopup="dialog" title="Cross graph">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
        <span>Cross graph</span>
      </button>
    <?php endif; ?>
    <?php if ($showFullscreenBtn): ?>
      <button type="button" class="topbar-fullscreen-btn" id="rpt-fullscreen-toggle" aria-pressed="false" aria-label="Enter fullscreen" title="Fullscreen">
        <span class="topbar-fs-icon topbar-fs-icon-enter" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M21 8V5a2 2 0 0 0-2-2h-3"/><path d="M3 16v3a2 2 0 0 0 2 2h3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
        </span>
        <span class="topbar-fs-icon topbar-fs-icon-exit" aria-hidden="true" hidden>
          <svg viewBox="0 0 24 24"><path d="M8 3v3a2 2 0 0 1-2 2H3"/><path d="M21 8h-3a2 2 0 0 1-2-2V3"/><path d="M3 16h3a2 2 0 0 1 2 2v3"/><path d="M16 21v-3a2 2 0 0 1 2-2h3"/></svg>
        </span>
        <span class="topbar-fs-label">Fullscreen</span>
      </button>
    <?php endif; ?>
    <div class="user-menu" id="user-menu">
      <button type="button" class="user-menu-toggle" id="user-menu-toggle" aria-expanded="false" aria-haspopup="true" aria-controls="user-menu-dropdown">
        <div class="user-avatar"><?php echo htmlspecialchars($user_initials); ?></div>
        <div class="user-meta">
          <strong><?php echo htmlspecialchars($user_name); ?></strong>
          <span><?php echo htmlspecialchars($role_label); ?></span>
        </div>
        <span class="user-menu-chevron" aria-hidden="true">
          <svg viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
        </span>
      </button>
      <div class="user-menu-dropdown" id="user-menu-dropdown" role="menu">
        <div class="user-menu-header">
          <strong><?php echo htmlspecialchars($user['username']); ?></strong>
          <span><?php echo htmlspecialchars($role_label); ?></span>
        </div>
        <a class="user-menu-item user-menu-logout" href="<?php echo htmlspecialchars($logout); ?>">
          <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
          Sign out
        </a>
      </div>
    </div>
  </div>
</header>
