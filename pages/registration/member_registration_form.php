<?php
/**
 * Member registration wizard form partial.
 * Included by pages/registration/register_member.php
 * Expects: $form (assoc), $formErrors (array), $household (session row)
 */

function mf_val(array $form, string $key): string
{
    return htmlspecialchars((string) ($form[$key] ?? ''));
}

function mf_selected(array $form, string $key, string $value): string
{
    return ((string) ($form[$key] ?? '') === $value) ? 'selected' : '';
}

/** @param list<string>|list<array{0:string,1:string}> $options */
function mf_select_options(array $options, array $form, string $key, bool $withBlank = true): void
{
    if ($withBlank) {
        $sel = ($form[$key] ?? '') === '' ? 'selected' : '';
        echo '<option value="" disabled ' . $sel . '>Select…</option>';
    }
    foreach ($options as $opt) {
        if (is_array($opt)) {
            [$val, $label] = $opt;
        } else {
            $val = $label = $opt;
        }
        echo '<option value="' . htmlspecialchars($val) . '" ' . mf_selected($form, $key, (string) $val) . '>'
            . htmlspecialchars($label) . '</option>';
    }
}

$mfRelationships = [
    'Head', 'Spouse', 'Son', 'Daughter', 'Stepson', 'Stepdaughter',
    'Son-in-law', 'Daughter-in-law', 'Grandson', 'Granddaughter',
    'Father', 'Mother', 'Brother', 'Sister', 'Uncle', 'Aunt',
    'Nephew', 'Niece', 'Other relative', 'Non-relative', 'Boarder', 'Domestic helper',
];
$mfMaritalStatuses = ['Single', 'Married', 'Living-in', 'Widowed', 'Separated', 'Annulled', 'Unknown'];
$mfEducationLevels = [
    'No education', 'Pre-school', 'Elementary level', 'Elementary graduate',
    'High school level', 'High school graduate', 'Junior HS level', 'Junior HS graduate',
    'Senior HS level', 'Senior HS graduate', 'Vocational/Tech', 'College level',
    'College graduate', 'Post-graduate',
];
$mfEnrolledOptions = ['Yes, public', 'Yes, private', 'No'];
$mfSchoolLevels = [
    'Pre-school', 'Elementary', 'Junior High School', 'Senior High School',
    'Vocational/Technical', 'College/University',
];
$mfIncomeRanges = [
    'Below 5,000', '5,000 - 10,000', '10,001 - 15,000', '15,001 - 20,000',
    '20,001 - 30,000', '30,001 - 40,000', '40,001 - 50,000', 'Above 50,000',
];
$mfIncomeSources = ['Employment', 'Business', 'Remittance', 'Investments', 'Others'];
$mfWorkStatuses = [
    'Permanent Work', 'Casual Work', 'Contractual Work',
    'Individually Owned Business', 'Shared/Partnership Business', 'Corporate Business',
];
$mfHealthInsurance = [
    'PhilHealth paying member',
    'PhilHealth dependent of paying member',
    'PhilHealth indigent member',
    'PhilHealth dependent of indigent member',
    'Others / None',
];
$mfHealthFacilities = [
    'Government hospital', 'RHU/Health center', 'Brgy. Health Station',
    'Private hospital', 'Private clinic', 'Others',
];
$mfVisitReasons = ['Sick/Injured', 'Prenatal/Postnatal', 'Gave birth', 'Dental', 'Medical check-up', 'Others'];
$mfDisabilities = ['None', 'Sensory', 'Physical', 'Mental', 'Others'];
$mfPlaceOfDelivery = ['Public hospital', 'Private hospital', 'Brgy. Health Station', 'Home', 'Others'];
$mfBirthAttendants = ['Doctor', 'Nurse', 'Midwife', 'Hilot', 'Others'];
$mfFpMethods = [
    'Female sterilization/Ligation', 'Male sterilization/Vasectomy', 'IUD',
    'Injectables', 'Pills', 'Condom', 'Natural / Rhythm', 'Withdrawal', 'None', 'Others',
];
$mfFpSources = [
    'Government hospital', 'RHU/Health center', 'Brgy. Health Station',
    'Private hospital/clinic', 'Pharmacy', 'Others',
];
$mfFpIntention = ['Yes', 'No', 'Already using', 'Not applicable'];
$mfYesNo = ['Yes', 'No'];
$mfYesNoNa = ['Yes', 'No', 'Not applicable'];
$mfResidentTypes = [
    'Non-Migrant (Length of Stay is over 5 years)',
    'Migrant (Length of Stay is between 6 months and 1 day to 5 years)',
];
$mfHousingTenure = [
    'Owned/being amortized', 'Rented',
    'Rent-free with consent of owner', 'Rent-free without consent of owner',
];
$mfDrinkingWater = [
    'Lake, river, rain, others', 'Dug well', 'Unprotected spring', 'Protected spring',
    'Peddler', 'Tubed/Piped shallow well', 'Shared, tubed/piped deep well',
    'Own use, tubed/piped deep well', 'Shared, faucet community water system',
    'Own use, faucet community water system', 'Bottled water', 'Others, please specify',
];
$mfToiletFacilities = [
    'None', 'Open pit', 'Closed pit',
    'Water-sealed, other depository, shared',
    'Water-sealed, other depository, exclusive',
    'Water-sealed, sewer septic tank, shared',
    'Water-sealed, sewer septic tank, exclusive',
    'Others, specify',
];
$mfBuildingTypes = [
    'Single house', 'Duplex', 'Multi-unit residential (three units or more)',
    'Commercial/industrial/agricultural',
    'Institutional living quarter (hotel, hospital)',
    'Other housing units (boat, cave, others)',
];
$mfWallMaterials = [
    'No walls', 'Makeshift/salvaged/improvised materials', 'Glass', 'Asbestos',
    'Bamboo/Sawali/Cogon/Nipa', 'Galvanized iron/aluminum',
    'Half concrete/brick/stone and half wood', 'Wood',
    'Concrete/brick/stone', 'Others, please specify',
];

$hhNumber = htmlspecialchars((string) ($household['household_number'] ?? ''));
$hhAddress = htmlspecialchars((string) ($household['address'] ?? ''));
$hhHead = htmlspecialchars((string) ($household['household_head'] ?? ''));
$hhSessionId = (string) ($household['session_id'] ?? '');
$hasHouseholdHead = !empty($hasHouseholdHead);
$existingHeadName = (string) ($existingHeadName ?? '');
if ($hasHouseholdHead) {
    $mfRelationships = array_values(array_filter(
        $mfRelationships,
        static fn(string $rel): bool => strcasecmp($rel, 'Head') !== 0
    ));
    if (strcasecmp((string) ($form['relationship'] ?? ''), 'Head') === 0) {
        $form['relationship'] = '';
    }
}
?>
<style>
.mf-wizard { display: flex; flex-direction: column; gap: 0.85rem; }
.mf-household-bar {
  display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 0.6rem;
}
.mf-household-item {
  padding: 0.7rem 0.85rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px;
}
.mf-household-label {
  display: block; font-size: 0.65rem; font-weight: 700; letter-spacing: 0.05em;
  text-transform: uppercase; color: #94a3b8; margin-bottom: 0.2rem;
}
.mf-household-value {
  display: block; font-size: 0.88rem; font-weight: 700; color: var(--ink); line-height: 1.35;
}
.mf-progress {
  display: flex; flex-wrap: wrap; gap: 0.35rem; padding: 0.65rem;
  background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;
}
.mf-step {
  flex: 1 1 auto; min-width: 4.5rem; padding: 0.45rem 0.55rem; border: 1px solid #e2e8f0;
  border-radius: 8px; background: #f8fafc; color: #64748b; font-size: 0.72rem;
  font-weight: 700; text-align: center; cursor: pointer; transition: background 0.2s, border-color 0.2s, color 0.2s;
}
.mf-step:hover:not(:disabled) { background: #fff; border-color: #94a3b8; color: var(--ink); }
.mf-step.is-active {
  background: rgba(0, 56, 168, 0.08); border-color: rgba(0, 56, 168, 0.35); color: var(--bp-blue);
}
.mf-step.is-hidden { display: none; }
.mf-step:disabled { opacity: 0.45; cursor: not-allowed; }
.mf-step-panel { display: none; }
.mf-step-panel.is-active { display: block; }
.mf-step-panel.is-step-hidden { display: none !important; }
.mf-field[data-visibility-hidden="1"] { display: none !important; }
.member-reg-form .mf-nav {
  display: flex; justify-content: space-between; align-items: center; gap: 0.55rem;
  margin-top: 0.5rem; padding: 1rem 0 0; border-top: 1px solid #e2e8f0;
}
.member-reg-form .mf-nav-actions { display: flex; gap: 0.55rem; margin-left: auto; }
@media (max-width: 720px) {
  .mf-household-bar { grid-template-columns: 1fr; }
  .mf-progress { gap: 0.25rem; }
  .mf-step { min-width: calc(50% - 0.2rem); font-size: 0.68rem; }
  .member-reg-form .mf-nav { flex-direction: column-reverse; align-items: stretch; }
  .member-reg-form .mf-nav-actions { margin-left: 0; flex-direction: column-reverse; }
  .member-reg-form .mf-nav .hh-btn { width: 100%; justify-content: center; }
}
</style>

<form method="post" id="member-registration-form" class="member-reg-form mf-wizard" novalidate>
  <input type="hidden" name="register_member" value="1">
  <?php if ($hhSessionId !== ''): ?>
    <input type="hidden" name="session_id" value="<?php echo htmlspecialchars($hhSessionId); ?>">
  <?php endif; ?>

  <?php if (!empty($formErrors)): ?>
    <div class="admin-alert err hh-form-alert mf-form-alert" role="alert">
      <strong>Please fix the following:</strong>
      <ul>
        <?php foreach ($formErrors as $field => $error): ?>
          <?php if (is_int($field)): ?>
            <li><?php echo htmlspecialchars((string) $error); ?></li>
          <?php else: ?>
            <li><?php echo htmlspecialchars((string) $error); ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="hh-form-toolbar mf-toolbar">
    <p class="hh-form-toolbar-text">Complete all required fields marked with <span class="req">*</span></p>
    <button type="button" class="hh-autofill-btn" id="mf-btn-autofill">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l3 7 7 1-5 5 1 7-6-3-6 3 1-7-5-5 7-1z"/></svg>
      Auto Fill (Testing)
    </button>
  </div>
  <div class="admin-alert ok hh-form-toast mf-form-toast" id="mf-autofill-toast" hidden role="status">
    Form auto-filled with test data. Review before saving.
  </div>

  <div class="mf-household-bar" aria-label="Household context">
    <div class="mf-household-item">
      <span class="mf-household-label">Household</span>
      <span class="mf-household-value">H<?php echo $hhNumber; ?></span>
    </div>
    <div class="mf-household-item">
      <span class="mf-household-label">Address</span>
      <span class="mf-household-value"><?php echo $hhAddress !== '' ? $hhAddress : '—'; ?></span>
    </div>
    <div class="mf-household-item">
      <span class="mf-household-label">Household Head</span>
      <span class="mf-household-value"><?php echo $hhHead !== '' ? $hhHead : '—'; ?></span>
    </div>
  </div>

  <nav class="mf-progress" id="mf-progress" aria-label="Registration steps">
    <button type="button" class="mf-step is-active" data-step-target="basic">Basic</button>
    <button type="button" class="mf-step" data-step-target="education">Education</button>
    <button type="button" class="mf-step" data-step-target="employment">Work</button>
    <button type="button" class="mf-step" data-step-target="health">Health</button>
    <button type="button" class="mf-step" data-step-target="maternal">Maternal</button>
    <button type="button" class="mf-step" data-step-target="migration">Migration</button>
    <button type="button" class="mf-step" data-step-target="housing">Housing</button>
    <button type="button" class="mf-step" data-step-target="needs">Needs</button>
  </nav>

  <!-- Step 1: Basic Q1–Q10 -->
  <section class="hh-form-card mf-step-panel is-active" data-step="basic">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--household" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      </span>
      <div>
        <h2>Basic information</h2>
        <p>Q1–Q10 demographic identity details.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field span-2 mf-field">
        <label for="name">Q1 Full Name <span class="req">*</span></label>
        <input type="text" id="name" name="name" required placeholder="Full name" value="<?php echo mf_val($form, 'name'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="relationship">Q2 Relationship to Household Head <span class="req">*</span></label>
        <select id="relationship" name="relationship" required>
          <?php mf_select_options($mfRelationships, $form, 'relationship'); ?>
        </select>
        <?php if ($hasHouseholdHead): ?>
          <p class="admin-field-hint">
            This household already has a head<?php echo $existingHeadName !== '' ? ' (' . htmlspecialchars($existingHeadName) . ')' : ''; ?>.
            Only one head is allowed — choose another relationship.
          </p>
        <?php endif; ?>
      </div>
      <div class="admin-field mf-field">
        <label for="sex">Q3 Sex <span class="req">*</span></label>
        <select id="sex" name="sex" required>
          <option value="" disabled <?php echo ($form['sex'] ?? '') === '' ? 'selected' : ''; ?>>Select sex</option>
          <option value="1" <?php echo mf_selected($form, 'sex', '1'); ?>>Male</option>
          <option value="2" <?php echo mf_selected($form, 'sex', '2'); ?>>Female</option>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="date_of_birth">Q5 Date of Birth</label>
        <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo mf_val($form, 'date_of_birth'); ?>">
        <p class="admin-field-hint">YYYY-MM-DD. Age is auto-calculated.</p>
      </div>
      <div class="admin-field mf-field">
        <label for="age">Q4 Age <span class="req">*</span></label>
        <input type="number" id="age" name="age" min="0" max="120" required value="<?php echo mf_val($form, 'age'); ?>">
        <p class="admin-field-hint">Auto-calculated from date of birth.</p>
      </div>
      <div class="admin-field mf-field">
        <label for="place_of_birth">Q6 Place of Birth</label>
        <input type="text" id="place_of_birth" name="place_of_birth" value="<?php echo mf_val($form, 'place_of_birth'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="nationality">Q7 Nationality</label>
        <input type="text" id="nationality" name="nationality" value="<?php echo mf_val($form, 'nationality') !== '' ? mf_val($form, 'nationality') : 'Filipino'; ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="marital_status">Q8 Marital Status</label>
        <select id="marital_status" name="marital_status">
          <?php mf_select_options($mfMaritalStatuses, $form, 'marital_status'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="religion">Q9 Religion</label>
        <input type="text" id="religion" name="religion" value="<?php echo mf_val($form, 'religion'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="ethnicity">Q10 Ethnicity</label>
        <input type="text" id="ethnicity" name="ethnicity" value="<?php echo mf_val($form, 'ethnicity'); ?>">
      </div>
    </div>
  </section>

  <!-- Step 2: Education Q11–Q14 -->
  <section class="hh-form-card mf-step-panel" data-step="education">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--personnel" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
      </span>
      <div>
        <h2>Education</h2>
        <p>Q11–Q14 schooling and enrollment.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field span-2 mf-field" data-show="q11">
        <label for="highest_education">Q11 Highest Level of Education Completed</label>
        <select id="highest_education" name="highest_education">
          <?php mf_select_options($mfEducationLevels, $form, 'highest_education'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q12to14">
        <label for="currently_enrolled">Q12 Currently Enrolled</label>
        <select id="currently_enrolled" name="currently_enrolled">
          <?php mf_select_options($mfEnrolledOptions, $form, 'currently_enrolled'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q12to14">
        <label for="school_level">Q13 School Level</label>
        <select id="school_level" name="school_level">
          <?php mf_select_options($mfSchoolLevels, $form, 'school_level'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field" data-show="q12to14">
        <label for="place_of_school">Q14 Place of School</label>
        <input type="text" id="place_of_school" name="place_of_school" value="<?php echo mf_val($form, 'place_of_school'); ?>">
      </div>
    </div>
  </section>

  <!-- Step 3: Employment Q15–Q18 -->
  <section class="hh-form-card mf-step-panel" data-step="employment">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--visit" aria-hidden="true">
        <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
      </span>
      <div>
        <h2>Employment</h2>
        <p>Q15–Q18 income and work status.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field mf-field" data-show="q15to18">
        <label for="monthly_income">Q15 Monthly Income</label>
        <select id="monthly_income" name="monthly_income">
          <?php mf_select_options($mfIncomeRanges, $form, 'monthly_income'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q15to18">
        <label for="source_of_income">Q16a Source of Income</label>
        <select id="source_of_income" name="source_of_income">
          <?php mf_select_options($mfIncomeSources, $form, 'source_of_income'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q15to18">
        <label for="occupation">Q16b Occupation / Position</label>
        <input type="text" id="occupation" name="occupation" value="<?php echo mf_val($form, 'occupation'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="q15to18">
        <label for="work_status">Q17 Status of Work</label>
        <select id="work_status" name="work_status">
          <?php mf_select_options($mfWorkStatuses, $form, 'work_status'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field" data-show="q15to18">
        <label for="place_of_work">Q18 Place of Work</label>
        <input type="text" id="place_of_work" name="place_of_work" value="<?php echo mf_val($form, 'place_of_work'); ?>">
      </div>
    </div>
  </section>

  <!-- Step 4: Health Q26–Q29 + nutritional status -->
  <section class="hh-form-card mf-step-panel" data-step="health">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--location" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
      </span>
      <div>
        <h2>Health</h2>
        <p>Q26–Q29 health coverage and disability.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field mf-field">
        <label for="health_insurance">Q26 Health Insurance</label>
        <select id="health_insurance" name="health_insurance">
          <?php mf_select_options($mfHealthInsurance, $form, 'health_insurance'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="nutritional_status">Q26a Nutritional Status</label>
        <input type="text" id="nutritional_status" name="nutritional_status" placeholder="e.g. Normal, Underweight" value="<?php echo mf_val($form, 'nutritional_status'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="facility_visited">Q27 Facility Visited in Past 12 Months</label>
        <select id="facility_visited" name="facility_visited">
          <?php mf_select_options($mfHealthFacilities, $form, 'facility_visited'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="reason_for_visit">Q28 Reason for Visit in Health Facility</label>
        <select id="reason_for_visit" name="reason_for_visit">
          <?php mf_select_options($mfVisitReasons, $form, 'reason_for_visit'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="disability">Q29 Disability</label>
        <select id="disability" name="disability">
          <?php mf_select_options($mfDisabilities, $form, 'disability'); ?>
        </select>
      </div>
    </div>
  </section>

  <!-- Step 5: Maternal Q19–Q25 -->
  <section class="hh-form-card mf-step-panel" data-step="maternal">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--household" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/></svg>
      </span>
      <div>
        <h2>Maternal &amp; family planning</h2>
        <p>Q19–Q25 maternal health and FP questions.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field mf-field" data-show="q19to21">
        <label for="place_of_delivery">Q19 Place of Delivery</label>
        <select id="place_of_delivery" name="place_of_delivery">
          <?php mf_select_options($mfPlaceOfDelivery, $form, 'place_of_delivery'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q19to21">
        <label for="birth_attendant">Q20 Person Who Assisted in Delivery</label>
        <select id="birth_attendant" name="birth_attendant">
          <?php mf_select_options($mfBirthAttendants, $form, 'birth_attendant'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field" data-show="q19to21">
        <label for="immunization">Q21 Immunization (Last Vaccine Received)</label>
        <input type="text" id="immunization" name="immunization" value="<?php echo mf_val($form, 'immunization'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="q22to25">
        <label for="living_children">Q22 Living Children</label>
        <input type="number" id="living_children" name="living_children" min="0" value="<?php echo mf_val($form, 'living_children'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="q22to25">
        <label for="fp_method">Q23 Family Planning (FP) Use</label>
        <select id="fp_method" name="fp_method">
          <?php mf_select_options($mfFpMethods, $form, 'fp_method'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q22to25">
        <label for="source_of_fp">Q24 Source of FP Method</label>
        <select id="source_of_fp" name="source_of_fp">
          <?php mf_select_options($mfFpSources, $form, 'source_of_fp'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q22to25">
        <label for="intention_to_use_fp">Q25 Intention to Use FP</label>
        <select id="intention_to_use_fp" name="intention_to_use_fp">
          <?php mf_select_options($mfFpIntention, $form, 'intention_to_use_fp'); ?>
        </select>
      </div>
    </div>
  </section>

  <!-- Step 6: Migration Q30–Q44 -->
  <section class="hh-form-card mf-step-panel" data-step="migration">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--visit" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
      </span>
      <div>
        <h2>Migration &amp; socio-civic</h2>
        <p>Q30–Q44 residency, CTC, and skills.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field mf-field" data-show="q30">
        <label for="solo_parent">Q30 Solo Parent</label>
        <select id="solo_parent" name="solo_parent">
          <?php mf_select_options($mfYesNo, $form, 'solo_parent'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q31">
        <label for="registered_senior">Q31 Registered Senior Citizen</label>
        <select id="registered_senior" name="registered_senior">
          <?php mf_select_options($mfYesNoNa, $form, 'registered_senior'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q32">
        <label for="registered_voter">Q32 Registered Voter</label>
        <select id="registered_voter" name="registered_voter">
          <?php mf_select_options($mfYesNoNa, $form, 'registered_voter'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="previous_residence_5yrs">Q33 Previous Residence (5 years ago)</label>
        <input type="text" id="previous_residence_5yrs" name="previous_residence_5yrs" value="<?php echo mf_val($form, 'previous_residence_5yrs'); ?>">
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="previous_residence">Q34 Previous Residence</label>
        <input type="text" id="previous_residence" name="previous_residence" value="<?php echo mf_val($form, 'previous_residence'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="length_of_stay">Q35 Length of Stay in the Barangay</label>
        <input type="text" id="length_of_stay" name="length_of_stay" value="<?php echo mf_val($form, 'length_of_stay'); ?>">
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="type_of_resident">Q36 Type of Resident</label>
        <select id="type_of_resident" name="type_of_resident">
          <?php mf_select_options($mfResidentTypes, $form, 'type_of_resident'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="date_of_transfer">Q37 Date of Transfer</label>
        <input type="text" id="date_of_transfer" name="date_of_transfer" placeholder="MM/YYYY" value="<?php echo mf_val($form, 'date_of_transfer'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_leaving_1">Q38A Reason for Leaving Previous Residence (1)</label>
        <input type="text" id="reason_leaving_1" name="reason_leaving_1" value="<?php echo mf_val($form, 'reason_leaving_1'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_leaving_2">Q38B Reason for Leaving Previous Residence (2)</label>
        <input type="text" id="reason_leaving_2" name="reason_leaving_2" value="<?php echo mf_val($form, 'reason_leaving_2'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_leaving_3">Q38C Reason for Leaving Previous Residence (3)</label>
        <input type="text" id="reason_leaving_3" name="reason_leaving_3" value="<?php echo mf_val($form, 'reason_leaving_3'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="return_previous">Q39 Return to Previous Residence</label>
        <select id="return_previous" name="return_previous">
          <?php mf_select_options($mfYesNo, $form, 'return_previous'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_transferring_1">Q40A Reason for Transferring in this Barangay (1)</label>
        <input type="text" id="reason_transferring_1" name="reason_transferring_1" value="<?php echo mf_val($form, 'reason_transferring_1'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_transferring_2">Q40B Reason for Transferring in this Barangay (2)</label>
        <input type="text" id="reason_transferring_2" name="reason_transferring_2" value="<?php echo mf_val($form, 'reason_transferring_2'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="reason_transferring_3">Q40C Reason for Transferring in this Barangay (3)</label>
        <input type="text" id="reason_transferring_3" name="reason_transferring_3" value="<?php echo mf_val($form, 'reason_transferring_3'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="migration-details">
        <label for="duration_of_stay">Q41 Duration of Stay in Current Barangay</label>
        <input type="text" id="duration_of_stay" name="duration_of_stay" value="<?php echo mf_val($form, 'duration_of_stay'); ?>">
      </div>
      <div class="admin-field mf-field" data-show="q42">
        <label for="ctc_possession">Q42A CTC Possession</label>
        <select id="ctc_possession" name="ctc_possession">
          <?php mf_select_options($mfYesNoNa, $form, 'ctc_possession'); ?>
        </select>
      </div>
      <div class="admin-field mf-field" data-show="q42">
        <label for="ctc_issued_in_barangay">Q42B CTC Issued in Barangay</label>
        <select id="ctc_issued_in_barangay" name="ctc_issued_in_barangay">
          <?php mf_select_options($mfYesNoNa, $form, 'ctc_issued_in_barangay'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field" data-show="q43q44">
        <label for="skills_training">Q43 Skills Development Training Interest</label>
        <textarea id="skills_training" name="skills_training" rows="2"><?php echo mf_val($form, 'skills_training'); ?></textarea>
      </div>
      <div class="admin-field span-2 mf-field" data-show="q43q44">
        <label for="skills">Q44 Skills</label>
        <textarea id="skills" name="skills" rows="2"><?php echo mf_val($form, 'skills'); ?></textarea>
      </div>
    </div>
  </section>

  <!-- Step 7: Housing Q45–Q53 -->
  <section class="hh-form-card mf-step-panel" data-step="housing">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--location" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
      </span>
      <div>
        <h2>Housing</h2>
        <p>Q45–Q53 dwelling and sanitation.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field mf-field">
        <label for="housing_tenure">Q45 Housing Unit Tenure</label>
        <select id="housing_tenure" name="housing_tenure">
          <?php mf_select_options($mfHousingTenure, $form, 'housing_tenure'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="lot_tenure">Q46 Lot Tenure</label>
        <select id="lot_tenure" name="lot_tenure">
          <?php mf_select_options($mfHousingTenure, $form, 'lot_tenure'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="fuel_for_lighting">Q47 Fuel for Lighting</label>
        <input type="text" id="fuel_for_lighting" name="fuel_for_lighting" value="<?php echo mf_val($form, 'fuel_for_lighting'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="fuel_for_cooking">Q48 Fuel for Cooking</label>
        <input type="text" id="fuel_for_cooking" name="fuel_for_cooking" value="<?php echo mf_val($form, 'fuel_for_cooking'); ?>">
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="drinking_water_source">Q49 Main Source of Drinking Water</label>
        <select id="drinking_water_source" name="drinking_water_source">
          <?php mf_select_options($mfDrinkingWater, $form, 'drinking_water_source'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="garbage_disposal">Q50a Kitchen Garbage Disposal Method</label>
        <input type="text" id="garbage_disposal" name="garbage_disposal" value="<?php echo mf_val($form, 'garbage_disposal'); ?>">
      </div>
      <div class="admin-field mf-field">
        <label for="garbage_segregation">Q50b Garbage Segregation</label>
        <select id="garbage_segregation" name="garbage_segregation">
          <?php mf_select_options($mfYesNo, $form, 'garbage_segregation'); ?>
        </select>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="toilet_facility">Q51 Toilet Facility</label>
        <select id="toilet_facility" name="toilet_facility">
          <?php mf_select_options($mfToiletFacilities, $form, 'toilet_facility'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="building_type">Q52 Type of Building/House</label>
        <select id="building_type" name="building_type">
          <?php mf_select_options($mfBuildingTypes, $form, 'building_type'); ?>
        </select>
        <p class="admin-field-hint">Do not ask, observation only.</p>
      </div>
      <div class="admin-field mf-field">
        <label for="outer_wall_material">Q53 Outer Wall Material</label>
        <select id="outer_wall_material" name="outer_wall_material">
          <?php mf_select_options($mfWallMaterials, $form, 'outer_wall_material'); ?>
        </select>
        <p class="admin-field-hint">Do not ask, observation only.</p>
      </div>
    </div>
  </section>

  <!-- Step 8: Needs Q54–Q62 -->
  <section class="hh-form-card mf-step-panel" data-step="needs">
    <header class="hh-form-card-head">
      <span class="hh-form-card-icon hh-form-card-icon--personnel" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
      </span>
      <div>
        <h2>Community needs</h2>
        <p>Q54–Q62 mortality and community needs.</p>
      </div>
    </header>
    <div class="admin-form-grid hh-form-grid">
      <div class="admin-field span-2 mf-field">
        <label for="female_death_details">Q54 Household member death in past 12 months (age &amp; cause)</label>
        <textarea id="female_death_details" name="female_death_details" rows="2"><?php echo mf_val($form, 'female_death_details'); ?></textarea>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="child_death_details">Q55 Child under 5 death in past 12 months (age &amp; cause)</label>
        <textarea id="child_death_details" name="child_death_details" rows="2"><?php echo mf_val($form, 'child_death_details'); ?></textarea>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="common_diseases">Q56 Common diseases that cause death in this barangay</label>
        <textarea id="common_diseases" name="common_diseases" rows="2"><?php echo mf_val($form, 'common_diseases'); ?></textarea>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="primary_needs">Q57 Primary needs of this barangay</label>
        <textarea id="primary_needs" name="primary_needs" rows="2"><?php echo mf_val($form, 'primary_needs'); ?></textarea>
      </div>
      <div class="admin-field span-2 mf-field">
        <label for="future_residence">Q58 Household intended residence in 5 years</label>
        <textarea id="future_residence" name="future_residence" rows="2"><?php echo mf_val($form, 'future_residence'); ?></textarea>
      </div>
      <div class="admin-field mf-field">
        <label for="hunger_experience">Q59 Did anyone in your family ever experience hunger?</label>
        <select id="hunger_experience" name="hunger_experience">
          <?php mf_select_options($mfYesNo, $form, 'hunger_experience'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="cannot_read">Q60 Family member ages 8+ who can't read?</label>
        <select id="cannot_read" name="cannot_read">
          <?php mf_select_options($mfYesNo, $form, 'cannot_read'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="evacuation_center">Q61 Do you know where the evacuation center is?</label>
        <select id="evacuation_center" name="evacuation_center">
          <?php mf_select_options($mfYesNo, $form, 'evacuation_center'); ?>
        </select>
      </div>
      <div class="admin-field mf-field">
        <label for="emergency_kit">Q62 Do you have an emergency kit/bag?</label>
        <select id="emergency_kit" name="emergency_kit">
          <?php mf_select_options($mfYesNo, $form, 'emergency_kit'); ?>
        </select>
      </div>
    </div>
  </section>

  <footer class="hh-form-footer mf-nav">
    <button type="button" class="hh-btn hh-btn-secondary mf-nav-prev" id="mf-nav-prev">Previous</button>
    <div class="mf-nav-actions">
      <button type="button" class="hh-btn hh-btn-secondary mf-nav-next" id="mf-nav-next">Next</button>
      <button type="submit" class="hh-btn hh-btn-primary mf-nav-submit" id="mf-nav-submit" hidden>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8"/><path d="M7 3v5h8"/></svg>
        Save Member
      </button>
    </div>
  </footer>
</form>

<script>
(function () {
  'use strict';

  var form = document.getElementById('member-registration-form');
  if (!form) return;

  var progress = document.getElementById('mf-progress');
  var stepBtns = progress ? progress.querySelectorAll('.mf-step') : [];
  var panels = form.querySelectorAll('.mf-step-panel');
  var btnPrev = document.getElementById('mf-nav-prev');
  var btnNext = document.getElementById('mf-nav-next');
  var btnSubmit = document.getElementById('mf-nav-submit');
  var ageEl = document.getElementById('age');
  var sexEl = document.getElementById('sex');
  var dobEl = document.getElementById('date_of_birth');
  var residentEl = document.getElementById('type_of_resident');
  var autofillBtn = document.getElementById('mf-btn-autofill');
  var autofillToast = document.getElementById('mf-autofill-toast');

  var STEP_DEFS = [
    { id: 'basic', always: true },
    { id: 'education', visible: function (v) { return v.showQ11 || v.showQ12to14; } },
    { id: 'employment', visible: function (v) { return v.showQ15to18; } },
    { id: 'health', always: true },
    { id: 'maternal', visible: function (v) { return v.showQ19to21 || v.showQ22to25; } },
    { id: 'migration', always: true },
    { id: 'housing', always: true },
    { id: 'needs', always: true }
  ];

  var currentStepId = 'basic';

  function parseAge() {
    if (!ageEl || ageEl.value === '') return null;
    var n = parseInt(ageEl.value, 10);
    return isNaN(n) ? null : n;
  }

  function parseSex() {
    return sexEl ? sexEl.value : '';
  }

  function parseResident() {
    return residentEl ? residentEl.value : '';
  }

  function isFemale(sex) { return sex === '2'; }

  function showQ11(age) { return age === null || age >= 5; }
  function showQ12to14(age) { return age === null || age >= 3; }
  function showQ15to18(age) { return age === null || age >= 15; }
  function showQ19to21(age) { return age === null || age < 1; }
  function showQ22to25(sex, age) {
    if (age === null) return true;
    if (age >= 0 && age <= 9) return false;
    if (isFemale(sex) && age >= 55) return false;
    return true;
  }
  function showQ30(age) { return age === null || age >= 10; }
  function showQ31(age) { return age === null || age >= 60; }
  function showQ32(age) { return age === null || age >= 15; }
  function showMigrationDetails(type) {
    if (!type) return true;
    return type.indexOf('Migrant') === 0;
  }
  function showQ42(age) { return age === null || age >= 18; }
  function showQ43Q44(age) { return age === null || age >= 15; }

  function getVisibility() {
    var age = parseAge();
    var sex = parseSex();
    var resident = parseResident();
    return {
      showQ11: showQ11(age),
      showQ12to14: showQ12to14(age),
      showQ15to18: showQ15to18(age),
      showQ19to21: showQ19to21(age),
      showQ22to25: showQ22to25(sex, age),
      showQ30: showQ30(age),
      showQ31: showQ31(age),
      showQ32: showQ32(age),
      showMigrationDetails: showMigrationDetails(resident),
      showQ42: showQ42(age),
      showQ43Q44: showQ43Q44(age)
    };
  }

  function visibilityMap(v) {
    return {
      q11: v.showQ11,
      q12to14: v.showQ12to14,
      q15to18: v.showQ15to18,
      q19to21: v.showQ19to21,
      q22to25: v.showQ22to25,
      q30: v.showQ30,
      q31: v.showQ31,
      q32: v.showQ32,
      'migration-details': v.showMigrationDetails,
      q42: v.showQ42,
      q43q44: v.showQ43Q44
    };
  }

  function clearFieldValue(el) {
    if (!el || el === ageEl) return;
    if (el.tagName === 'SELECT') el.selectedIndex = 0;
    else el.value = '';
  }

  function clearHiddenFields(v) {
    var map = visibilityMap(v);
    form.querySelectorAll('.mf-field[data-show]').forEach(function (field) {
      var key = field.getAttribute('data-show');
      if (map[key] === false) {
        field.querySelectorAll('input, select, textarea').forEach(clearFieldValue);
      }
    });
  }

  function applyFieldVisibility(v) {
    var map = visibilityMap(v);
    form.querySelectorAll('.mf-field[data-show]').forEach(function (field) {
      var key = field.getAttribute('data-show');
      var show = map[key] !== false;
      field.setAttribute('data-visibility-hidden', show ? '0' : '1');
    });
  }

  function activeSteps(v) {
    return STEP_DEFS.filter(function (def) {
      return def.always || (def.visible && def.visible(v));
    });
  }

  function syncStepChrome(v) {
    var steps = activeSteps(v);
    var ids = steps.map(function (s) { return s.id; });

    stepBtns.forEach(function (btn) {
      var id = btn.getAttribute('data-step-target');
      var visible = ids.indexOf(id) !== -1;
      btn.classList.toggle('is-hidden', !visible);
      btn.disabled = !visible;
    });

    panels.forEach(function (panel) {
      var id = panel.getAttribute('data-step');
      var visible = ids.indexOf(id) !== -1;
      panel.classList.toggle('is-step-hidden', !visible);
    });

    if (ids.indexOf(currentStepId) === -1) {
      currentStepId = ids[0] || 'basic';
    }

    var idx = ids.indexOf(currentStepId);
    var isFirst = idx <= 0;
    var isLast = idx >= ids.length - 1;

    panels.forEach(function (panel) {
      panel.classList.toggle('is-active', panel.getAttribute('data-step') === currentStepId);
    });
    stepBtns.forEach(function (btn) {
      btn.classList.toggle('is-active', btn.getAttribute('data-step-target') === currentStepId);
    });

    if (btnPrev) btnPrev.hidden = isFirst;
    if (btnNext) btnNext.hidden = isLast;
    if (btnSubmit) btnSubmit.hidden = !isLast;
  }

  function goToStep(stepId) {
    currentStepId = stepId;
    syncStepChrome(getVisibility());
    var panel = form.querySelector('.mf-step-panel[data-step="' + stepId + '"]');
    if (panel) panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function nextStep() {
    var v = getVisibility();
    var steps = activeSteps(v);
    var idx = steps.findIndex(function (s) { return s.id === currentStepId; });
    if (idx >= 0 && idx < steps.length - 1) goToStep(steps[idx + 1].id);
  }

  function prevStep() {
    var v = getVisibility();
    var steps = activeSteps(v);
    var idx = steps.findIndex(function (s) { return s.id === currentStepId; });
    if (idx > 0) goToStep(steps[idx - 1].id);
  }

  function validateBasic() {
    var ok = true;
    var name = document.getElementById('name');
    var relationship = document.getElementById('relationship');
    if (name && !name.value.trim()) { ok = false; name.focus(); }
    else if (relationship && !relationship.value) { ok = false; relationship.focus(); }
    else if (sexEl && !sexEl.value) { ok = false; sexEl.focus(); }
    else if (ageEl && (ageEl.value === '' || parseAge() === null)) { ok = false; dobEl && dobEl.focus(); }
    return ok;
  }

  function updateAgeFromDob() {
    if (!dobEl || !ageEl) return;
    var dob = dobEl.value;
    if (!dob) {
      ageEl.value = '';
      refreshVisibility(true);
      return;
    }
    var birth = new Date(dob + 'T00:00:00');
    if (isNaN(birth.getTime())) {
      ageEl.value = '';
      refreshVisibility(true);
      return;
    }
    var today = new Date();
    if (birth > today) {
      ageEl.value = '';
      refreshVisibility(true);
      return;
    }
    var years = today.getFullYear() - birth.getFullYear();
    var m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) years--;
    ageEl.value = (years >= 0 && years <= 120) ? String(years) : '';
    refreshVisibility(true);
  }

  function refreshVisibility(clearHidden) {
    var v = getVisibility();
    applyFieldVisibility(v);
    if (clearHidden) clearHiddenFields(v);
    syncStepChrome(v);
  }

  stepBtns.forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (btn.disabled || btn.classList.contains('is-hidden')) return;
      var target = btn.getAttribute('data-step-target');
      if (target === currentStepId) return;
      if (currentStepId === 'basic' && target !== 'basic' && !validateBasic()) return;
      goToStep(target);
    });
  });

  if (btnPrev) btnPrev.addEventListener('click', prevStep);
  if (btnNext) btnNext.addEventListener('click', function () {
    if (currentStepId === 'basic' && !validateBasic()) return;
    nextStep();
  });

  if (dobEl) dobEl.addEventListener('change', updateAgeFromDob);
  if (dobEl) dobEl.addEventListener('input', updateAgeFromDob);
  if (sexEl) sexEl.addEventListener('change', function () { refreshVisibility(true); });
  if (residentEl) residentEl.addEventListener('change', function () { refreshVisibility(true); });

  form.addEventListener('submit', function (e) {
    if (!validateBasic()) {
      e.preventDefault();
      goToStep('basic');
    }
  });

  /* Autofill (Testing) — mirrors Android TestDataFaker.memberSample */
  if (autofillBtn) {
    function randInt(min, max) { return Math.floor(Math.random() * (max - min + 1)) + min; }
    function pick(arr) { return arr[randInt(0, arr.length - 1)]; }
    function randomName() {
      var first = ['Juan', 'Maria', 'Pedro', 'Ana', 'Jose', 'Rosa', 'Carlos', 'Sofia', 'Liza', 'Mark'];
      var last = ['Santos', 'Reyes', 'Cruz', 'Garcia', 'Mendoza', 'Torres', 'Ramos', 'Flores'];
      return pick(first) + ' ' + pick(last);
    }
    function formatDate(d) {
      return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    }
    function setVal(id, value) {
      var el = document.getElementById(id);
      if (!el) return;
      el.value = value == null ? '' : value;
      el.dispatchEvent(new Event('change', { bubbles: true }));
      el.dispatchEvent(new Event('input', { bubbles: true }));
    }

    var relationships = <?php echo json_encode($mfRelationships, JSON_UNESCAPED_UNICODE); ?>;
    var maritalStatuses = <?php echo json_encode($mfMaritalStatuses, JSON_UNESCAPED_UNICODE); ?>;
    var educationLevels = <?php echo json_encode($mfEducationLevels, JSON_UNESCAPED_UNICODE); ?>;
    var enrolledOptions = <?php echo json_encode($mfEnrolledOptions, JSON_UNESCAPED_UNICODE); ?>;
    var schoolLevels = <?php echo json_encode($mfSchoolLevels, JSON_UNESCAPED_UNICODE); ?>;
    var incomeRanges = <?php echo json_encode($mfIncomeRanges, JSON_UNESCAPED_UNICODE); ?>;
    var incomeSources = <?php echo json_encode($mfIncomeSources, JSON_UNESCAPED_UNICODE); ?>;
    var workStatuses = <?php echo json_encode($mfWorkStatuses, JSON_UNESCAPED_UNICODE); ?>;
    var placeOfDelivery = <?php echo json_encode($mfPlaceOfDelivery, JSON_UNESCAPED_UNICODE); ?>;
    var birthAttendants = <?php echo json_encode($mfBirthAttendants, JSON_UNESCAPED_UNICODE); ?>;
    var fpMethods = <?php echo json_encode($mfFpMethods, JSON_UNESCAPED_UNICODE); ?>;
    var fpSources = <?php echo json_encode($mfFpSources, JSON_UNESCAPED_UNICODE); ?>;
    var fpIntention = <?php echo json_encode($mfFpIntention, JSON_UNESCAPED_UNICODE); ?>;
    var healthInsurance = <?php echo json_encode($mfHealthInsurance, JSON_UNESCAPED_UNICODE); ?>;
    var healthFacilities = <?php echo json_encode($mfHealthFacilities, JSON_UNESCAPED_UNICODE); ?>;
    var visitReasons = <?php echo json_encode($mfVisitReasons, JSON_UNESCAPED_UNICODE); ?>;
    var disabilities = <?php echo json_encode($mfDisabilities, JSON_UNESCAPED_UNICODE); ?>;
    var yesNo = <?php echo json_encode($mfYesNo, JSON_UNESCAPED_UNICODE); ?>;
    var yesNoNa = <?php echo json_encode($mfYesNoNa, JSON_UNESCAPED_UNICODE); ?>;
    var residentTypes = <?php echo json_encode($mfResidentTypes, JSON_UNESCAPED_UNICODE); ?>;
    var housingTenure = <?php echo json_encode($mfHousingTenure, JSON_UNESCAPED_UNICODE); ?>;
    var drinkingWater = <?php echo json_encode($mfDrinkingWater, JSON_UNESCAPED_UNICODE); ?>;
    var toiletFacilities = <?php echo json_encode($mfToiletFacilities, JSON_UNESCAPED_UNICODE); ?>;
    var buildingTypes = <?php echo json_encode($mfBuildingTypes, JSON_UNESCAPED_UNICODE); ?>;
    var wallMaterials = <?php echo json_encode($mfWallMaterials, JSON_UNESCAPED_UNICODE); ?>;

    var cities = ['Manila', 'Quezon City', 'Cebu City', 'Davao City', 'Iloilo City', 'Baguio City'];
    var barangays = ['Poblacion', 'San Roque', 'San Jose', 'Santa Cruz', 'San Miguel'];
    var religions = ['Roman Catholic', 'Iglesia ni Cristo', 'Born Again', 'Islam', 'Others'];
    var ethnicities = ['Tagalog', 'Cebuano', 'Ilocano', 'Bicolano', 'Others'];
    var schools = ['Public Elementary School', 'Public High School', 'State University', 'Private Academy'];
    var occupations = ['Farmer', 'Teacher', 'Driver', 'Vendor', 'Office Clerk', 'Nurse'];

    autofillBtn.addEventListener('click', function () {
      var ageYears = randInt(1, 79);
      var birth = new Date();
      birth.setFullYear(birth.getFullYear() - ageYears);
      birth.setMonth(randInt(0, 11));
      birth.setDate(randInt(1, 28));
      var isMale = Math.random() > 0.5;
      var enrolled = ageYears < 5 ? 'No'
        : (ageYears < 22 && Math.random() > 0.35 ? pick(['Yes, public', 'Yes, private']) : 'No');
      var maternal = !isMale && ageYears >= 15 && ageYears <= 49;

      setVal('name', randomName());
      setVal('relationship', pick(relationships));
      setVal('sex', isMale ? '1' : '2');
      setVal('date_of_birth', formatDate(birth));
      updateAgeFromDob();
      setVal('place_of_birth', pick(cities));
      setVal('nationality', 'Filipino');
      setVal('marital_status', ageYears < 18 ? 'Single' : pick(maritalStatuses));
      setVal('religion', pick(religions));
      setVal('ethnicity', pick(ethnicities));
      setVal('highest_education', pick(educationLevels));
      setVal('currently_enrolled', enrolled);
      setVal('school_level', enrolled === 'No' ? '' : pick(schoolLevels));
      setVal('place_of_school', enrolled === 'No' ? '' : pick(schools));
      setVal('monthly_income', ageYears < 15 ? '' : pick(incomeRanges));
      setVal('source_of_income', ageYears < 15 ? '' : pick(incomeSources));
      setVal('occupation', ageYears < 15 ? '' : pick(occupations));
      setVal('work_status', ageYears < 15 ? '' : pick(workStatuses));
      setVal('place_of_work', ageYears < 15 ? '' : pick(cities));
      setVal('place_of_delivery', maternal ? pick(placeOfDelivery) : '');
      setVal('birth_attendant', maternal ? pick(birthAttendants) : '');
      setVal('immunization', pick(['Complete', 'Incomplete', 'None']));
      setVal('living_children', maternal ? String(randInt(0, 4)) : '');
      setVal('fp_method', maternal ? pick(fpMethods) : '');
      setVal('source_of_fp', maternal ? pick(fpSources) : '');
      setVal('intention_to_use_fp', maternal ? pick(fpIntention) : '');
      setVal('health_insurance', pick(healthInsurance));
      setVal('nutritional_status', pick(['Normal', 'Underweight', 'Overweight', 'Not assessed']));
      setVal('facility_visited', pick(healthFacilities));
      setVal('reason_for_visit', pick(visitReasons));
      setVal('disability', pick(disabilities));
      setVal('solo_parent', isMale ? 'No' : pick(yesNo));
      setVal('registered_senior', ageYears >= 60 ? pick(yesNo) : 'Not applicable');
      setVal('registered_voter', ageYears >= 15 ? pick(yesNo) : 'Not applicable');
      setVal('previous_residence_5yrs', pick(barangays) + ', ' + pick(cities));
      setVal('previous_residence', pick(barangays) + ', ' + pick(cities));
      setVal('length_of_stay', randInt(1, 20) + ' years');
      setVal('type_of_resident', pick(residentTypes));
      setVal('date_of_transfer', ageYears >= 18 ? '01/' + (2010 + randInt(0, 14)) : '');
      setVal('reason_leaving_1', pick(['Employment', 'Marriage', 'Education', 'Family']));
      setVal('reason_leaving_2', '');
      setVal('reason_leaving_3', '');
      setVal('return_previous', pick(yesNo));
      setVal('reason_transferring_1', pick(['Availability of jobs', 'Presence of schools', 'Family']));
      setVal('reason_transferring_2', '');
      setVal('reason_transferring_3', '');
      setVal('duration_of_stay', randInt(6, 60) + ' months');
      setVal('ctc_possession', ageYears >= 18 ? pick(yesNo) : 'Not applicable');
      setVal('ctc_issued_in_barangay', ageYears >= 18 ? pick(yesNo) : 'Not applicable');
      setVal('skills_training', pick(['Carpentry', 'Baking', 'Computer Graphics', 'None']));
      setVal('skills', pick(['Farming', 'Cooking', 'Driving', 'Sewing']));
      setVal('housing_tenure', pick(housingTenure));
      setVal('lot_tenure', pick(housingTenure));
      setVal('fuel_for_lighting', pick(['Electricity', 'Kerosene', 'Solar']));
      setVal('fuel_for_cooking', pick(['LPG', 'Wood', 'Charcoal', 'Electricity']));
      setVal('drinking_water_source', pick(drinkingWater));
      setVal('garbage_disposal', pick(['Collected by Garbage Truck', 'Burning', 'Composting', 'Burying']));
      setVal('garbage_segregation', pick(yesNo));
      setVal('toilet_facility', pick(toiletFacilities));
      setVal('building_type', pick(buildingTypes));
      setVal('outer_wall_material', pick(wallMaterials));
      setVal('female_death_details', 'None');
      setVal('child_death_details', 'None');
      setVal('common_diseases', pick(['None', 'Common cold, flu', 'Hypertension', 'Diabetes']));
      setVal('primary_needs', pick(['Stable water supply', 'Health services', 'Livelihood', 'Road improvement']));
      setVal('future_residence', pick(['Stay in current residence', 'Move within the barangay', 'Move to another city/municipality']));
      setVal('hunger_experience', pick(yesNo));
      setVal('cannot_read', pick(yesNo));
      setVal('evacuation_center', pick(yesNo));
      setVal('emergency_kit', pick(yesNo));

      refreshVisibility(true);

      if (autofillToast) {
        autofillToast.hidden = false;
        clearTimeout(autofillToast._t);
        autofillToast._t = setTimeout(function () { autofillToast.hidden = true; }, 3200);
      }
    });
  }

  updateAgeFromDob();
  refreshVisibility(false);
})();
</script>
