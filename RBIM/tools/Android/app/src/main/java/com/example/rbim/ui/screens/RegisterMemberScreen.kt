package com.example.rbim.ui.screens

import androidx.compose.animation.AnimatedContent
import androidx.compose.animation.fadeIn
import androidx.compose.animation.fadeOut
import androidx.compose.animation.slideInHorizontally
import androidx.compose.animation.slideOutHorizontally
import androidx.compose.animation.togetherWith
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material.icons.outlined.PersonAdd
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.input.KeyboardType
import androidx.compose.ui.unit.dp
import com.example.rbim.data.TestDataFaker
import com.example.rbim.data.model.CensusOptions
import com.example.rbim.data.model.FieldVisibility
import com.example.rbim.data.model.Household
import com.example.rbim.data.model.HouseholdMember
import com.example.rbim.data.model.MemberFormProgress
import com.example.rbim.data.repository.HouseholdRepository
import com.example.rbim.ui.components.FormDropdown
import com.example.rbim.ui.components.FormLabeledDropdown
import com.example.rbim.ui.components.FormTextField
import com.example.rbim.ui.components.FormTopBar
import com.example.rbim.ui.components.WizardNavigationBar
import com.example.rbim.ui.components.WizardProgressHeader
import com.example.rbim.ui.components.WizardStep
import com.example.rbim.ui.components.WizardStepCard
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.WarmWhite
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale
import java.util.UUID
import kotlinx.coroutines.launch

private object MemberSteps {
    val Basic = WizardStep("basic", "Basic", "Q1–Q10 demographic identity details.")
    val Education = WizardStep("education", "Education", "Q11–Q14 schooling and enrollment.")
    val Employment = WizardStep("employment", "Work", "Q15–Q18 income and work status.")
    val Health = WizardStep("health", "Health", "Q26–Q29 health coverage and disability.")
    val Maternal = WizardStep("maternal", "Maternal", "Q19–Q25 maternal and family planning.")
    val Migration = WizardStep("migration", "Migration", "Q30–Q44 socio-civic, residency, CTC, skills.")
    val Housing = WizardStep("housing", "Housing", "Q45–Q53 dwelling and sanitation.")
    val Needs = WizardStep("needs", "Needs", "Q54–Q62 mortality and community needs.")
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RegisterMemberScreen(
    household: Household,
    onBack: () -> Unit,
    onDone: () -> Unit
) {
    val context = LocalContext.current
    val repository = remember { HouseholdRepository(context) }
    var registeredCount by remember {
        mutableStateOf(repository.getMembers(household.localId).size)
    }

    // Q1–Q10
    var name by remember { mutableStateOf("") }
    var relationship by remember { mutableStateOf("") }
    var sex by remember { mutableStateOf("") }
    var age by remember { mutableStateOf("") }
    var dateOfBirth by remember { mutableStateOf("") }
    var placeOfBirth by remember { mutableStateOf("") }
    var nationality by remember { mutableStateOf("Filipino") }
    var maritalStatus by remember { mutableStateOf("") }
    var religion by remember { mutableStateOf("") }
    var ethnicity by remember { mutableStateOf("") }
    // Q11–Q14
    var highestEducation by remember { mutableStateOf("") }
    var currentlyEnrolled by remember { mutableStateOf("") }
    var schoolLevel by remember { mutableStateOf("") }
    var placeOfSchool by remember { mutableStateOf("") }
    // Q15–Q18
    var monthlyIncome by remember { mutableStateOf("") }
    var sourceOfIncome by remember { mutableStateOf("") }
    var occupation by remember { mutableStateOf("") }
    var workStatus by remember { mutableStateOf("") }
    var placeOfWork by remember { mutableStateOf("") }
    // Q19–Q25
    var placeOfDelivery by remember { mutableStateOf("") }
    var birthAttendant by remember { mutableStateOf("") }
    var immunization by remember { mutableStateOf("") }
    var livingChildren by remember { mutableStateOf("") }
    var fpMethod by remember { mutableStateOf("") }
    var sourceOfFp by remember { mutableStateOf("") }
    var intentionToUseFp by remember { mutableStateOf("") }
    // Q26–Q29
    var healthInsurance by remember { mutableStateOf("") }
    var nutritionalStatus by remember { mutableStateOf("") }
    var facilityVisited by remember { mutableStateOf("") }
    var reasonForVisit by remember { mutableStateOf("") }
    var disability by remember { mutableStateOf("") }
    // Q30–Q44
    var soloParent by remember { mutableStateOf("") }
    var registeredSenior by remember { mutableStateOf("") }
    var registeredVoter by remember { mutableStateOf("") }
    var previousResidence5yrs by remember { mutableStateOf("") }
    var previousResidence by remember { mutableStateOf("") }
    var lengthOfStay by remember { mutableStateOf("") }
    var typeOfResident by remember { mutableStateOf("") }
    var dateOfTransfer by remember { mutableStateOf("") }
    var reasonLeaving1 by remember { mutableStateOf("") }
    var reasonLeaving2 by remember { mutableStateOf("") }
    var reasonLeaving3 by remember { mutableStateOf("") }
    var returnPreviousResidence by remember { mutableStateOf("") }
    var reasonTransferring1 by remember { mutableStateOf("") }
    var reasonTransferring2 by remember { mutableStateOf("") }
    var reasonTransferring3 by remember { mutableStateOf("") }
    var durationOfStay by remember { mutableStateOf("") }
    var ctcPossession by remember { mutableStateOf("") }
    var ctcIssuedInBarangay by remember { mutableStateOf("") }
    var skillsTrainingInterest by remember { mutableStateOf("") }
    var skills by remember { mutableStateOf("") }
    // Q45–Q53
    var housingTenure by remember { mutableStateOf("") }
    var lotTenure by remember { mutableStateOf("") }
    var fuelForLighting by remember { mutableStateOf("") }
    var fuelForCooking by remember { mutableStateOf("") }
    var drinkingWaterSource by remember { mutableStateOf("") }
    var garbageDisposal by remember { mutableStateOf("") }
    var garbageSegregation by remember { mutableStateOf("") }
    var toiletFacility by remember { mutableStateOf("") }
    var buildingType by remember { mutableStateOf("") }
    var outerWallMaterial by remember { mutableStateOf("") }
    // Q54–Q62
    var femaleDeathDetails by remember { mutableStateOf("") }
    var childDeathDetails by remember { mutableStateOf("") }
    var commonDiseases by remember { mutableStateOf("") }
    var primaryNeeds by remember { mutableStateOf("") }
    var futureResidence by remember { mutableStateOf("") }
    var hungerExperience by remember { mutableStateOf("") }
    var cannotRead by remember { mutableStateOf("") }
    var evacuationCenter by remember { mutableStateOf("") }
    var emergencyKit by remember { mutableStateOf("") }

    var errors by remember { mutableStateOf(mapOf<String, String>()) }
    var showSuccess by remember { mutableStateOf(false) }
    var lastSavedName by remember { mutableStateOf("") }
    var currentStepId by remember { mutableStateOf(MemberSteps.Basic.id) }
    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()
    val scrollState = rememberScrollState()

    fun draftMember(): HouseholdMember = HouseholdMember(
        localId = "",
        householdLocalId = household.localId,
        householdNumber = household.householdNumber,
        name = name,
        relationship = relationship,
        sex = sex.toIntOrNull() ?: 0,
        age = age.toIntOrNull() ?: 0,
        dateOfBirth = dateOfBirth,
        placeOfBirth = placeOfBirth,
        nationality = nationality,
        maritalStatus = maritalStatus,
        religion = religion,
        ethnicity = ethnicity,
        highestEducation = highestEducation,
        currentlyEnrolled = currentlyEnrolled,
        schoolLevel = schoolLevel,
        placeOfSchool = placeOfSchool,
        monthlyIncome = monthlyIncome,
        sourceOfIncome = sourceOfIncome,
        occupation = occupation,
        workStatus = workStatus,
        placeOfWork = placeOfWork,
        placeOfDelivery = placeOfDelivery,
        birthAttendant = birthAttendant,
        immunization = immunization,
        livingChildren = livingChildren,
        fpMethod = fpMethod,
        sourceOfFp = sourceOfFp,
        intentionToUseFp = intentionToUseFp,
        healthInsurance = healthInsurance,
        nutritionalStatus = nutritionalStatus,
        facilityVisited = facilityVisited,
        reasonForVisit = reasonForVisit,
        disability = disability,
        soloParent = soloParent,
        registeredSenior = registeredSenior,
        registeredVoter = registeredVoter,
        previousResidence5yrs = previousResidence5yrs,
        previousResidence = previousResidence,
        lengthOfStay = lengthOfStay,
        typeOfResident = typeOfResident,
        dateOfTransfer = dateOfTransfer,
        reasonLeaving1 = reasonLeaving1,
        reasonLeaving2 = reasonLeaving2,
        reasonLeaving3 = reasonLeaving3,
        returnPreviousResidence = returnPreviousResidence,
        reasonTransferring1 = reasonTransferring1,
        reasonTransferring2 = reasonTransferring2,
        reasonTransferring3 = reasonTransferring3,
        durationOfStay = durationOfStay,
        ctcPossession = ctcPossession,
        ctcIssuedInBarangay = ctcIssuedInBarangay,
        skillsTrainingInterest = skillsTrainingInterest,
        skills = skills,
        housingTenure = housingTenure,
        lotTenure = lotTenure,
        fuelForLighting = fuelForLighting,
        fuelForCooking = fuelForCooking,
        drinkingWaterSource = drinkingWaterSource,
        garbageDisposalMethod = garbageDisposal,
        garbageSegregation = garbageSegregation,
        toiletFacility = toiletFacility,
        buildingType = buildingType,
        outerWallMaterial = outerWallMaterial,
        femaleDeathDetails = femaleDeathDetails,
        childDeathDetails = childDeathDetails,
        commonDiseases = commonDiseases,
        primaryNeeds = primaryNeeds,
        futureResidence = futureResidence,
        hungerExperience = hungerExperience,
        cannotRead = cannotRead,
        evacuationCenter = evacuationCenter,
        emergencyKit = emergencyKit
    )

    fun clearForm() {
        name = ""; relationship = ""; sex = ""; age = ""; dateOfBirth = ""
        placeOfBirth = ""; nationality = "Filipino"; maritalStatus = ""
        religion = ""; ethnicity = ""; highestEducation = ""
        currentlyEnrolled = ""; schoolLevel = ""; placeOfSchool = ""
        monthlyIncome = ""; sourceOfIncome = ""; occupation = ""
        workStatus = ""; placeOfWork = ""
        placeOfDelivery = ""; birthAttendant = ""; livingChildren = ""
        fpMethod = ""; sourceOfFp = ""; intentionToUseFp = ""
        immunization = ""; healthInsurance = ""; nutritionalStatus = ""
        facilityVisited = ""; reasonForVisit = ""; disability = ""
        soloParent = ""; registeredSenior = ""; registeredVoter = ""
        previousResidence5yrs = ""; previousResidence = ""; lengthOfStay = ""
        typeOfResident = ""; dateOfTransfer = ""
        reasonLeaving1 = ""; reasonLeaving2 = ""; reasonLeaving3 = ""
        returnPreviousResidence = ""
        reasonTransferring1 = ""; reasonTransferring2 = ""; reasonTransferring3 = ""
        durationOfStay = ""; ctcPossession = ""; ctcIssuedInBarangay = ""
        skillsTrainingInterest = ""; skills = ""
        housingTenure = ""; lotTenure = ""; fuelForLighting = ""; fuelForCooking = ""
        drinkingWaterSource = ""; garbageDisposal = ""; garbageSegregation = ""
        toiletFacility = ""; buildingType = ""; outerWallMaterial = ""
        femaleDeathDetails = ""; childDeathDetails = ""; commonDiseases = ""
        primaryNeeds = ""; futureResidence = ""; hungerExperience = ""
        cannotRead = ""; evacuationCenter = ""; emergencyKit = ""
        errors = emptyMap()
        currentStepId = MemberSteps.Basic.id
    }

    fun clearHiddenFields() {
        val ageInt = age.toIntOrNull()
        if (!FieldVisibility.showQ11(ageInt)) highestEducation = ""
        if (!FieldVisibility.showQ12to14(ageInt)) {
            currentlyEnrolled = ""; schoolLevel = ""; placeOfSchool = ""
        }
        if (!FieldVisibility.showQ15to18(ageInt)) {
            monthlyIncome = ""; sourceOfIncome = ""; occupation = ""
            workStatus = ""; placeOfWork = ""
        }
        if (!FieldVisibility.showQ19to21(ageInt)) {
            placeOfDelivery = ""; birthAttendant = ""; immunization = ""
        }
        if (!FieldVisibility.showQ22to25(sex, ageInt)) {
            livingChildren = ""; fpMethod = ""; sourceOfFp = ""; intentionToUseFp = ""
        }
        if (!FieldVisibility.showQ30(ageInt)) soloParent = ""
        if (!FieldVisibility.showQ31(ageInt)) registeredSenior = ""
        if (!FieldVisibility.showQ32(ageInt)) registeredVoter = ""
        if (!FieldVisibility.showMigrationDetailFields(typeOfResident)) {
            dateOfTransfer = ""
            reasonLeaving1 = ""; reasonLeaving2 = ""; reasonLeaving3 = ""
            returnPreviousResidence = ""
            reasonTransferring1 = ""; reasonTransferring2 = ""; reasonTransferring3 = ""
            durationOfStay = ""
        }
        if (!FieldVisibility.showQ42(ageInt)) {
            ctcPossession = ""; ctcIssuedInBarangay = ""
        }
        if (!FieldVisibility.showQ43Q44(ageInt)) {
            skillsTrainingInterest = ""; skills = ""
        }
    }

    fun autoFill() {
        val sample = TestDataFaker.memberSample()
        name = sample.name
        relationship = sample.relationship
        sex = sample.sex
        age = sample.age
        dateOfBirth = sample.dateOfBirth
        placeOfBirth = sample.placeOfBirth
        nationality = sample.nationality
        maritalStatus = sample.maritalStatus
        religion = sample.religion
        ethnicity = sample.ethnicity
        highestEducation = sample.highestEducation
        currentlyEnrolled = sample.currentlyEnrolled
        schoolLevel = sample.schoolLevel
        placeOfSchool = sample.placeOfSchool
            monthlyIncome = sample.monthlyIncome
            sourceOfIncome = sample.sourceOfIncome
            occupation = sample.occupation
            workStatus = sample.workStatus
            placeOfWork = sample.placeOfWork
        immunization = sample.immunization
        healthInsurance = sample.healthInsurance
        nutritionalStatus = sample.nutritionalStatus
        facilityVisited = sample.facilityVisited
        reasonForVisit = sample.reasonForVisit
        disability = sample.disability
            placeOfDelivery = sample.placeOfDelivery
            birthAttendant = sample.birthAttendant
            livingChildren = sample.livingChildren
            fpMethod = sample.fpMethod
            sourceOfFp = sample.sourceOfFp
            intentionToUseFp = sample.intentionToUseFp
        soloParent = sample.soloParent
        registeredSenior = sample.registeredSenior
        registeredVoter = sample.registeredVoter
        previousResidence5yrs = sample.previousResidence5yrs
        previousResidence = sample.previousResidence
        lengthOfStay = sample.lengthOfStay
        typeOfResident = sample.typeOfResident
        dateOfTransfer = sample.dateOfTransfer
        reasonLeaving1 = sample.reasonLeaving1
        reasonLeaving2 = sample.reasonLeaving2
        reasonLeaving3 = sample.reasonLeaving3
        returnPreviousResidence = sample.returnPreviousResidence
        reasonTransferring1 = sample.reasonTransferring1
        reasonTransferring2 = sample.reasonTransferring2
        reasonTransferring3 = sample.reasonTransferring3
        durationOfStay = sample.durationOfStay
        ctcPossession = sample.ctcPossession
        ctcIssuedInBarangay = sample.ctcIssuedInBarangay
        skillsTrainingInterest = sample.skillsTrainingInterest
        skills = sample.skills
        housingTenure = sample.housingTenure
        lotTenure = sample.lotTenure
        fuelForLighting = sample.fuelForLighting
        fuelForCooking = sample.fuelForCooking
        drinkingWaterSource = sample.drinkingWaterSource
        garbageDisposal = sample.garbageDisposalMethod
        garbageSegregation = sample.garbageSegregation
        toiletFacility = sample.toiletFacility
        buildingType = sample.buildingType
        outerWallMaterial = sample.outerWallMaterial
        femaleDeathDetails = sample.femaleDeathDetails
        childDeathDetails = sample.childDeathDetails
        commonDiseases = sample.commonDiseases
        primaryNeeds = sample.primaryNeeds
        futureResidence = sample.futureResidence
        hungerExperience = sample.hungerExperience
        cannotRead = sample.cannotRead
        evacuationCenter = sample.evacuationCenter
        emergencyKit = sample.emergencyKit
        clearHiddenFields()
        errors = emptyMap()
        scope.launch { snackbarHostState.showSnackbar("Form auto-filled with test data") }
    }

    fun parseDob(dob: String): Calendar? {
        return try {
            val parsed = SimpleDateFormat("yyyy-MM-dd", Locale.US).apply { isLenient = false }.parse(dob) ?: return null
            Calendar.getInstance().apply { time = parsed }
        } catch (_: Exception) { null }
    }

    fun updateAgeFromDob(dob: String) {
        val birth = parseDob(dob)
        if (birth == null) {
            if (dob.isBlank()) age = ""
            clearHiddenFields()
            return
        }
        if (birth.after(Calendar.getInstance())) {
            age = ""
            clearHiddenFields()
            return
        }
        val today = Calendar.getInstance()
        var years = today.get(Calendar.YEAR) - birth.get(Calendar.YEAR)
        if (today.get(Calendar.MONTH) < birth.get(Calendar.MONTH) ||
            (today.get(Calendar.MONTH) == birth.get(Calendar.MONTH) &&
                today.get(Calendar.DAY_OF_MONTH) < birth.get(Calendar.DAY_OF_MONTH))
        ) years--
        age = if (years in 0..120) years.toString() else ""
        clearHiddenFields()
    }

    fun validateBasicStep(): Boolean {
        val next = mutableMapOf<String, String>()
        if (name.isBlank()) next["name"] = "Full name is required"
        if (relationship.isBlank()) next["relationship"] = "Relationship is required"
        if (sex.isBlank()) next["sex"] = "Sex is required"
        val ageInt = age.toIntOrNull()
        if (ageInt == null || ageInt < 0 || ageInt > 120) next["age"] = "Age must be between 0 and 120"
        if (dateOfBirth.isBlank()) next["date_of_birth"] = "Date of birth is required"
        else {
            val birth = parseDob(dateOfBirth)
            if (birth == null) next["date_of_birth"] = "Use YYYY-MM-DD format"
            else if (birth.after(Calendar.getInstance())) next["date_of_birth"] = "Date of birth cannot be in the future"
        }
        errors = next
        return next.isEmpty()
    }

    fun saveMember() {
        if (!validateBasicStep()) {
            currentStepId = MemberSteps.Basic.id
            return
        }
        clearHiddenFields()
        val member = draftMember().copy(localId = UUID.randomUUID().toString())
        repository.createMember(member)
        lastSavedName = member.name
        registeredCount = repository.getMembers(household.localId).size
        showSuccess = true
    }

    val ageInt = age.toIntOrNull()
    val showQ11 = FieldVisibility.showQ11(ageInt)
    val showQ12to14 = FieldVisibility.showQ12to14(ageInt)
    val showQ15to18 = FieldVisibility.showQ15to18(ageInt)
    val showQ19to21 = FieldVisibility.showQ19to21(ageInt)
    val showQ22to25 = FieldVisibility.showQ22to25(sex, ageInt)
    val showQ30 = FieldVisibility.showQ30(ageInt)
    val showQ31 = FieldVisibility.showQ31(ageInt)
    val showQ32 = FieldVisibility.showQ32(ageInt)
    val showMigrationDetails = FieldVisibility.showMigrationDetailFields(typeOfResident)
    val showQ42 = FieldVisibility.showQ42(ageInt)
    val showQ43Q44 = FieldVisibility.showQ43Q44(ageInt)

    val completionProgress = MemberFormProgress.calculate(draftMember(), sex)

    val steps = remember(showQ11, showQ12to14, showQ15to18, showQ19to21, showQ22to25) {
        buildList {
            add(MemberSteps.Basic)
            if (showQ11 || showQ12to14) add(MemberSteps.Education)
            if (showQ15to18) add(MemberSteps.Employment)
            add(MemberSteps.Health)
            if (showQ19to21 || showQ22to25) add(MemberSteps.Maternal)
            add(MemberSteps.Migration)
            add(MemberSteps.Housing)
            add(MemberSteps.Needs)
        }
    }

    val stepIndex = steps.indexOfFirst { it.id == currentStepId }.let { if (it >= 0) it else 0 }
    val currentStep = steps[stepIndex]
    val isLastStep = stepIndex >= steps.lastIndex

    LaunchedEffect(steps.map { it.id }.joinToString()) {
        if (steps.none { it.id == currentStepId }) {
            currentStepId = steps[stepIndex.coerceAtMost(steps.lastIndex)].id
        }
    }
    LaunchedEffect(currentStepId) { scrollState.animateScrollTo(0) }

    Scaffold(
        topBar = {
            FormTopBar(
                title = "Register Member",
                subtitle = "${household.displayNumber} · ${household.householdHead} · $registeredCount saved",
                onBack = onBack
            )
        },
        bottomBar = {
            WizardNavigationBar(
                isFirstStep = stepIndex == 0,
                isLastStep = isLastStep,
                onPrevious = { if (stepIndex > 0) currentStepId = steps[stepIndex - 1].id },
                onNext = {
                    when {
                        currentStep.id == MemberSteps.Basic.id && !validateBasicStep() -> Unit
                        isLastStep -> saveMember()
                        else -> currentStepId = steps[stepIndex + 1].id
                    }
                },
                onAutoFill = { autoFill() },
                nextLabel = if (isLastStep) "Save Member" else "Next",
                nextIcon = if (isLastStep) Icons.Outlined.PersonAdd else Icons.AutoMirrored.Filled.ArrowForward,
                modifier = Modifier.navigationBarsPadding()
            )
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
        containerColor = CreamSurface
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .imePadding()
        ) {
            WizardProgressHeader(
                steps = steps,
                currentIndex = stepIndex,
                completionProgress = completionProgress
            )
            Column(
                modifier = Modifier
                    .weight(1f)
                    .fillMaxWidth()
                    .verticalScroll(scrollState)
                    .padding(horizontal = 16.dp)
                    .padding(bottom = 12.dp),
                verticalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                AnimatedContent(
                    targetState = currentStep.id,
                    transitionSpec = {
                        (slideInHorizontally { it / 4 } + fadeIn()) togetherWith
                            (slideOutHorizontally { -it / 4 } + fadeOut())
                    },
                    label = "memberStep"
                ) { stepId ->
                    WizardStepCard {
                        when (stepId) {
                        MemberSteps.Basic.id -> {
                                FormTextField(value = name, onValueChange = { name = it }, label = "Q1 Full Name", required = true, isError = errors.containsKey("name"), errorText = errors["name"])
                                FormLabeledDropdown(value = relationship, onValueChange = { relationship = it }, label = "Q2 Relationship to Household Head", options = CensusOptions.relationships, required = true, isError = errors.containsKey("relationship"), errorText = errors["relationship"])
                                FormLabeledDropdown(value = sex, onValueChange = { sex = it; clearHiddenFields() }, label = "Q3 Sex", options = CensusOptions.sexes.map { it.first.toString() to it.second }, required = true, isError = errors.containsKey("sex"), errorText = errors["sex"])
                                FormTextField(value = dateOfBirth, onValueChange = { dateOfBirth = it; updateAgeFromDob(it) }, label = "Q5 Date of Birth", required = true, supportingText = "YYYY-MM-DD", isError = errors.containsKey("date_of_birth"), errorText = errors["date_of_birth"])
                                FormTextField(value = age, onValueChange = {}, label = "Q4 Age", required = true, readOnly = true, enabled = false, keyboardType = KeyboardType.Number, supportingText = "Auto-calculated from date of birth", isError = errors.containsKey("age"), errorText = errors["age"])
                                FormTextField(value = placeOfBirth, onValueChange = { placeOfBirth = it }, label = "Q6 Place of Birth")
                                FormTextField(value = nationality, onValueChange = { nationality = it }, label = "Q7 Nationality")
                                FormDropdown(value = maritalStatus, onValueChange = { maritalStatus = it }, label = "Q8 Marital Status", options = CensusOptions.maritalStatuses)
                                FormTextField(value = religion, onValueChange = { religion = it }, label = "Q9 Religion")
                                FormTextField(value = ethnicity, onValueChange = { ethnicity = it }, label = "Q10 Ethnicity")
                        }

                        MemberSteps.Education.id -> {
                                if (showQ11) {
                                    FormDropdown(value = highestEducation, onValueChange = { highestEducation = it }, label = "Q11 Highest Level of Education Completed", options = CensusOptions.educationLevels)
                                }
                                if (showQ12to14) {
                                    FormDropdown(value = currentlyEnrolled, onValueChange = { currentlyEnrolled = it }, label = "Q12 Currently Enrolled", options = CensusOptions.enrolledOptions)
                                    FormDropdown(value = schoolLevel, onValueChange = { schoolLevel = it }, label = "Q13 School Level", options = CensusOptions.schoolLevels)
                                    FormTextField(value = placeOfSchool, onValueChange = { placeOfSchool = it }, label = "Q14 Place of School")
                            }
                        }

                        MemberSteps.Employment.id -> {
                                FormDropdown(value = monthlyIncome, onValueChange = { monthlyIncome = it }, label = "Q15 Monthly Income", options = CensusOptions.incomeRanges)
                                FormDropdown(value = sourceOfIncome, onValueChange = { sourceOfIncome = it }, label = "Q16a Source of Income", options = CensusOptions.incomeSources)
                                FormTextField(value = occupation, onValueChange = { occupation = it }, label = "Q16b Occupation / Position")
                                FormDropdown(value = workStatus, onValueChange = { workStatus = it }, label = "Q17 Status of Work", options = CensusOptions.workStatuses)
                                FormTextField(value = placeOfWork, onValueChange = { placeOfWork = it }, label = "Q18 Place of Work")
                        }

                        MemberSteps.Health.id -> {
                                FormDropdown(value = healthInsurance, onValueChange = { healthInsurance = it }, label = "Q26 Health Insurance", options = CensusOptions.healthInsurance)
                                FormTextField(value = nutritionalStatus, onValueChange = { nutritionalStatus = it }, label = "Q26a Nutritional Status")
                                FormDropdown(value = facilityVisited, onValueChange = { facilityVisited = it }, label = "Q27 Facility Visited in Past 12 Months", options = CensusOptions.healthFacilities)
                                FormDropdown(value = reasonForVisit, onValueChange = { reasonForVisit = it }, label = "Q28 Reason for Visit in Health Facility", options = CensusOptions.visitReasons)
                                FormDropdown(value = disability, onValueChange = { disability = it }, label = "Q29 Disability", options = CensusOptions.disabilities)
                                if (showQ19to21) {
                                    FormTextField(value = immunization, onValueChange = { immunization = it }, label = "Q21 Immunization (Last Vaccine Received)")
                                }
                            }

                            MemberSteps.Maternal.id -> {
                                if (showQ19to21) {
                                    FormDropdown(value = placeOfDelivery, onValueChange = { placeOfDelivery = it }, label = "Q19 Place of Delivery", options = CensusOptions.placeOfDelivery)
                                    FormDropdown(value = birthAttendant, onValueChange = { birthAttendant = it }, label = "Q20 Person Who Assisted in Delivery", options = CensusOptions.birthAttendants)
                                }
                                if (showQ22to25) {
                                    FormTextField(value = livingChildren, onValueChange = { livingChildren = it }, label = "Q22 Living Children", keyboardType = KeyboardType.Number)
                                    FormDropdown(value = fpMethod, onValueChange = { fpMethod = it }, label = "Q23 Family Planning (FP) Use", options = CensusOptions.fpMethods)
                                    FormDropdown(value = sourceOfFp, onValueChange = { sourceOfFp = it }, label = "Q24 Source of FP Method", options = CensusOptions.fpSources)
                                    FormDropdown(value = intentionToUseFp, onValueChange = { intentionToUseFp = it }, label = "Q25 Intention to Use FP", options = CensusOptions.fpIntention)
                                }
                            }

                            MemberSteps.Migration.id -> {
                                if (showQ30) {
                                    FormDropdown(value = soloParent, onValueChange = { soloParent = it }, label = "Q30 Solo Parent", options = CensusOptions.yesNo)
                                }
                                if (showQ31) {
                                    FormDropdown(value = registeredSenior, onValueChange = { registeredSenior = it }, label = "Q31 Registered Senior Citizen", options = CensusOptions.yesNoNa)
                                }
                                if (showQ32) {
                                    FormDropdown(value = registeredVoter, onValueChange = { registeredVoter = it }, label = "Q32 Registered Voter", options = CensusOptions.yesNoNa)
                                }
                                FormTextField(value = previousResidence5yrs, onValueChange = { previousResidence5yrs = it }, label = "Q33 Previous Residence (5 years ago)")
                                FormTextField(value = previousResidence, onValueChange = { previousResidence = it }, label = "Q34 Previous Residence")
                                FormTextField(value = lengthOfStay, onValueChange = { lengthOfStay = it }, label = "Q35 Length of Stay in the Barangay")
                                FormDropdown(
                                    value = typeOfResident,
                                    onValueChange = { typeOfResident = it; clearHiddenFields() },
                                    label = "Q36 Type of Resident",
                                    options = CensusOptions.residentTypes
                                )
                                if (showMigrationDetails) {
                                    FormTextField(value = dateOfTransfer, onValueChange = { dateOfTransfer = it }, label = "Q37 Date of Transfer", supportingText = "MM/YYYY")
                                    FormTextField(value = reasonLeaving1, onValueChange = { reasonLeaving1 = it }, label = "Q38A Reason for Leaving Previous Residence (1)")
                                    FormTextField(value = reasonLeaving2, onValueChange = { reasonLeaving2 = it }, label = "Q38B Reason for Leaving Previous Residence (2)")
                                    FormTextField(value = reasonLeaving3, onValueChange = { reasonLeaving3 = it }, label = "Q38C Reason for Leaving Previous Residence (3)")
                                    FormTextField(value = returnPreviousResidence, onValueChange = { returnPreviousResidence = it }, label = "Q39 Return to Previous Residence")
                                    FormTextField(value = reasonTransferring1, onValueChange = { reasonTransferring1 = it }, label = "Q40A Reason for Transferring in this Barangay (1)")
                                    FormTextField(value = reasonTransferring2, onValueChange = { reasonTransferring2 = it }, label = "Q40B Reason for Transferring in this Barangay (2)")
                                    FormTextField(value = reasonTransferring3, onValueChange = { reasonTransferring3 = it }, label = "Q40C Reason for Transferring in this Barangay (3)")
                                    FormTextField(value = durationOfStay, onValueChange = { durationOfStay = it }, label = "Q41 Duration of Stay in Current Barangay")
                                }
                                if (showQ42) {
                                    FormDropdown(value = ctcPossession, onValueChange = { ctcPossession = it }, label = "Q42A CTC Possession", options = CensusOptions.yesNoNa)
                                    FormDropdown(value = ctcIssuedInBarangay, onValueChange = { ctcIssuedInBarangay = it }, label = "Q42B CTC Issued in Barangay", options = CensusOptions.yesNoNa)
                                }
                                if (showQ43Q44) {
                                    FormTextField(value = skillsTrainingInterest, onValueChange = { skillsTrainingInterest = it }, label = "Q43 Skills Development Training Interest", singleLine = false, minLines = 2)
                                    FormTextField(value = skills, onValueChange = { skills = it }, label = "Q44 Skills", singleLine = false, minLines = 2)
                                }
                        }

                        MemberSteps.Housing.id -> {
                                FormDropdown(value = housingTenure, onValueChange = { housingTenure = it }, label = "Q45 Housing Unit Tenure", options = CensusOptions.housingTenure)
                                FormDropdown(value = lotTenure, onValueChange = { lotTenure = it }, label = "Q46 Lot Tenure", options = CensusOptions.housingTenure)
                                FormTextField(value = fuelForLighting, onValueChange = { fuelForLighting = it }, label = "Q47 Fuel for Lighting")
                                FormTextField(value = fuelForCooking, onValueChange = { fuelForCooking = it }, label = "Q48 Fuel for Cooking")
                                FormDropdown(value = drinkingWaterSource, onValueChange = { drinkingWaterSource = it }, label = "Q49 Main Source of Drinking Water", options = CensusOptions.drinkingWater)
                                FormTextField(value = garbageDisposal, onValueChange = { garbageDisposal = it }, label = "Q50a Kitchen Garbage Disposal Method")
                                FormDropdown(value = garbageSegregation, onValueChange = { garbageSegregation = it }, label = "Q50b Garbage Segregation", options = CensusOptions.yesNo)
                                FormDropdown(value = toiletFacility, onValueChange = { toiletFacility = it }, label = "Q51 Toilet Facility", options = CensusOptions.toiletFacilities)
                            FormDropdown(
                                value = buildingType,
                                onValueChange = { buildingType = it },
                                    label = "Q52 Type of Building/House",
                                    options = CensusOptions.buildingTypes,
                                    supportingText = "Do not ask, observation only"
                            )
                                FormDropdown(
                                value = outerWallMaterial,
                                onValueChange = { outerWallMaterial = it },
                                    label = "Q53 Outer Wall Material",
                                    options = CensusOptions.wallMaterials,
                                    supportingText = "Do not ask, observation only"
                                )
                            }

                            MemberSteps.Needs.id -> {
                                FormTextField(value = femaleDeathDetails, onValueChange = { femaleDeathDetails = it }, label = "Q54 Household member death in past 12 months (age & cause)", singleLine = false, minLines = 2)
                                FormTextField(value = childDeathDetails, onValueChange = { childDeathDetails = it }, label = "Q55 Child under 5 death in past 12 months (age & cause)", singleLine = false, minLines = 2)
                                FormTextField(value = commonDiseases, onValueChange = { commonDiseases = it }, label = "Q56 Common diseases that cause death in this barangay", singleLine = false, minLines = 2)
                                FormTextField(value = primaryNeeds, onValueChange = { primaryNeeds = it }, label = "Q57 Primary needs of this barangay", singleLine = false, minLines = 2)
                                FormTextField(value = futureResidence, onValueChange = { futureResidence = it }, label = "Q58 Household intended residence in 5 years", singleLine = false, minLines = 2)
                                FormDropdown(value = hungerExperience, onValueChange = { hungerExperience = it }, label = "Q59 Did anyone in your family ever experience hunger?", options = CensusOptions.yesNo)
                                FormDropdown(value = cannotRead, onValueChange = { cannotRead = it }, label = "Q60 Family member ages 8+ who can't read?", options = CensusOptions.yesNo)
                                FormDropdown(value = evacuationCenter, onValueChange = { evacuationCenter = it }, label = "Q61 Do you know where the evacuation center is?", options = CensusOptions.yesNo)
                                FormDropdown(value = emergencyKit, onValueChange = { emergencyKit = it }, label = "Q62 Do you have an emergency kit/bag?", options = CensusOptions.yesNo)
                            }
                        }
                    }
                }
                Spacer(modifier = Modifier.height(8.dp))
            }
        }
    }

    if (showSuccess) {
        AlertDialog(
            onDismissRequest = { },
            title = { Text("Member Saved", fontWeight = FontWeight.SemiBold, color = TealDark) },
            text = {
                Column {
                    Text("$lastSavedName was registered for ${household.displayNumber}.", color = Slate700)
                    Spacer(modifier = Modifier.height(8.dp))
                    Text("Add another member or finish.", color = Slate500)
                }
            },
            confirmButton = {
                Button(
                    onClick = { showSuccess = false; clearForm() },
                    colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
                ) { Text("Add Another") }
            },
            dismissButton = {
                TextButton(onClick = onDone) { Text("Finish", color = Slate700) }
            },
            containerColor = WarmWhite
        )
    }
}
