<?php
/**
 * Household registration form fields (used inside modal).
 * Expects: $form, $province, $municipality, $barangayName
 */
?>
<form method="post" id="household-form" class="admin-modal-form hh-register-form" novalidate>
  <input type="hidden" name="register_household" value="1">

  <?php if (!empty($formErrors)): ?>
    <div class="admin-alert err hh-form-alert" role="alert">
      <strong>Please fix the following:</strong>
      <ul>
        <?php foreach ($formErrors as $error): ?>
          <li><?php echo htmlspecialchars($error); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="hh-form-toolbar">
    <p class="hh-form-toolbar-text">Complete all required fields marked with <span class="req">*</span></p>
    <button type="button" class="hh-autofill-btn" id="btn-autofill">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3 7 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg>
      Auto Fill (Testing)
    </button>
  </div>
  <div class="admin-alert ok hh-form-toast" id="autofill-toast" hidden role="status">Form auto-filled with test data. Review before saving.</div>

  <div class="hh-loc-bar">
    <div class="hh-loc-item">
      <span class="hh-loc-label">Province</span>
      <span class="hh-loc-value" id="province"><?php echo htmlspecialchars($province); ?></span>
    </div>
    <div class="hh-loc-item">
      <span class="hh-loc-label">Municipality</span>
      <span class="hh-loc-value" id="municipality"><?php echo htmlspecialchars($municipality); ?></span>
    </div>
    <div class="hh-loc-item">
      <span class="hh-loc-label">Barangay</span>
      <span class="hh-loc-value" id="barangay"><?php echo htmlspecialchars($barangayName); ?></span>
    </div>
  </div>

  <section class="hh-form-card">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--location" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 1 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      </span>
      <div>
        <h2>Location</h2>
        <p>Household address within the assigned barangay.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field span-2">
        <label for="address">Address <span class="req">*</span></label>
        <textarea id="address" name="address" required placeholder="House no., street, purok/sitio"><?php echo htmlspecialchars($form['address']); ?></textarea>
      </div>
    </div>
  </section>

  <section class="hh-form-card">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--household" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
      <div>
        <h2>Household details</h2>
        <p>Respondent information and household composition.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field">
        <label for="respondent_name">Respondent name <span class="req">*</span></label>
        <input type="text" id="respondent_name" name="respondent_name" required placeholder="Full name" value="<?php echo htmlspecialchars($form['respondent_name']); ?>">
      </div>
      <div class="admin-field">
        <label for="household_head">Household head <span class="req">*</span></label>
        <input type="text" id="household_head" name="household_head" required placeholder="Full name" value="<?php echo htmlspecialchars($form['household_head']); ?>">
      </div>
      <div class="admin-field">
        <label for="total_members_display">Total members</label>
        <div class="hh-member-count" id="total_members_display" aria-live="polite">0</div>
        <input type="hidden" name="total_members" value="0">
        <p class="admin-field-hint">Starts at 0 and increases automatically as members are registered.</p>
      </div>
    </div>
  </section>

  <section class="hh-form-card">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--visit" aria-hidden="true">
        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h18"/></svg>
      </span>
      <div>
        <h2>Visit information</h2>
        <p>Survey visit schedule and interview outcome.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field">
        <label for="visit_number">Visit number <span class="req">*</span></label>
        <input type="number" id="visit_number" name="visit_number" min="1" required value="<?php echo htmlspecialchars($form['visit_number']); ?>">
      </div>
      <div class="admin-field">
        <label for="visit_date">Visit date <span class="req">*</span></label>
        <input type="date" id="visit_date" name="visit_date" required value="<?php echo htmlspecialchars($form['visit_date']); ?>">
      </div>
      <div class="admin-field">
        <label for="start_time">Start time</label>
        <input type="time" id="start_time" name="start_time" value="<?php echo htmlspecialchars($form['start_time']); ?>">
      </div>
      <div class="admin-field">
        <label for="end_time">End time</label>
        <input type="time" id="end_time" name="end_time" value="<?php echo htmlspecialchars($form['end_time']); ?>">
      </div>
      <div class="admin-field">
        <label for="result">Result <span class="req">*</span></label>
        <select id="result" name="result" required>
          <option value="" disabled <?php echo $form['result'] === '' ? 'selected' : ''; ?>>Select result</option>
          <option value="Complete" <?php echo $form['result'] === 'Complete' ? 'selected' : ''; ?>>Complete</option>
          <option value="Incomplete" <?php echo $form['result'] === 'Incomplete' ? 'selected' : ''; ?>>Incomplete</option>
          <option value="Refused" <?php echo $form['result'] === 'Refused' ? 'selected' : ''; ?>>Refused</option>
          <option value="No Response" <?php echo $form['result'] === 'No Response' ? 'selected' : ''; ?>>No Response</option>
        </select>
      </div>
      <div class="admin-field" id="next-visit-wrap">
        <label for="next_visit_date">Next visit date</label>
        <input type="date" id="next_visit_date" name="next_visit_date" value="<?php echo htmlspecialchars($form['next_visit_date']); ?>">
        <p class="admin-field-hint">Required when result is Incomplete.</p>
      </div>
    </div>
  </section>

  <section class="hh-form-card">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--personnel" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </span>
      <div>
        <h2>Personnel</h2>
        <p>Interviewer and encoding staff details.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field">
        <label for="interviewer_name">Interviewer name <span class="req">*</span></label>
        <input type="text" id="interviewer_name" name="interviewer_name" required value="<?php echo htmlspecialchars($form['interviewer_name']); ?>">
      </div>
      <div class="admin-field">
        <label for="supervisor_name">Supervisor name</label>
        <input type="text" id="supervisor_name" name="supervisor_name" placeholder="Optional" value="<?php echo htmlspecialchars($form['supervisor_name']); ?>">
      </div>
      <div class="admin-field">
        <label for="encoding_supervisor">Encoding supervisor</label>
        <input type="text" id="encoding_supervisor" name="encoding_supervisor" placeholder="Optional" value="<?php echo htmlspecialchars($form['encoding_supervisor']); ?>">
      </div>
    </div>
  </section>

  <footer class="hh-form-footer">
    <button type="button" class="hh-btn hh-btn-secondary" id="modal-cancel">Cancel</button>
    <button type="submit" class="hh-btn hh-btn-primary">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
      Save Household
    </button>
  </footer>
</form>
