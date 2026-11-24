<?php
/**
 * Statistics Reports filter bar (rendered in page content).
 * Expects: $isSuperAdmin, $isMuniAdmin, $filtersActive, $municipalities, $barangays,
 *          $filterMuniId, $filterBrgyId, $filterSex, $filterAge, $ageBandOptions
 */
?>
<form class="rpt-filters" method="get" action="" id="rpt-cross-filter">
  <?php if ($filtersActive && !$isSuperAdmin && !$isMuniAdmin): ?>
    <div class="rpt-filters-head">
      <a class="rpt-filter-reset" href="statistics.php">Clear filters</a>
    </div>
  <?php endif; ?>
  <div class="rpt-filters-grid">
    <?php if ($isSuperAdmin): ?>
      <label class="rpt-filter-field">
        <span>Municipality</span>
        <select name="muni" id="filter-muni">
          <option value="0">All municipalities</option>
          <?php foreach ($municipalities as $mun): ?>
            <option value="<?php echo (int) $mun['city_municipality_id']; ?>" <?php echo $filterMuniId === (int) $mun['city_municipality_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars((string) $mun['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="rpt-filter-field">
        <span>Barangay</span>
        <select name="brgy" id="filter-brgy">
          <option value="0">All barangays</option>
          <?php foreach ($barangays as $brgy): ?>
            <option value="<?php echo (int) $brgy['barangay_id']; ?>" <?php echo $filterBrgyId === (int) $brgy['barangay_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars((string) $brgy['barangay']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    <?php elseif (!empty($isMuniAdmin)): ?>
      <label class="rpt-filter-field">
        <span>Barangay</span>
        <select name="brgy" id="filter-brgy">
          <option value="0">All barangays</option>
          <?php foreach ($barangays as $brgy): ?>
            <option value="<?php echo (int) $brgy['barangay_id']; ?>" <?php echo $filterBrgyId === (int) $brgy['barangay_id'] ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars((string) $brgy['barangay']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
    <?php endif; ?>
    <label class="rpt-filter-field">
      <span>Sex</span>
      <select name="sex" id="filter-sex">
        <option value="">All</option>
        <option value="1" <?php echo $filterSex === '1' ? 'selected' : ''; ?>>Male</option>
        <option value="2" <?php echo $filterSex === '2' ? 'selected' : ''; ?>>Female</option>
      </select>
    </label>
    <label class="rpt-filter-field">
      <span>Age group</span>
      <select name="age" id="filter-age">
        <option value="">All ages</option>
        <?php foreach ($ageBandOptions as $band): ?>
          <option value="<?php echo htmlspecialchars($band); ?>" <?php echo $filterAge === $band ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($band); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <div class="rpt-filter-field rpt-filter-action">
      <span>Cross graph</span>
      <button type="button" class="btn btn-primary rpt-cross-open-btn" id="cross-graph-open" aria-haspopup="dialog">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 20V10"/><path d="M12 20V4"/><path d="M6 20v-6"/></svg>
        Open
      </button>
    </div>
  </div>
</form>
