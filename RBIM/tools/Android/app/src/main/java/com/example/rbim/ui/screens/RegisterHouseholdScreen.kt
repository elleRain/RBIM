package com.example.rbim.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.imePadding
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.AutoFixHigh
import androidx.compose.material.icons.outlined.Save
import androidx.compose.material3.AlertDialog
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Scaffold
import androidx.compose.material3.SnackbarHost
import androidx.compose.material3.SnackbarHostState
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
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
import com.example.rbim.data.repository.HouseholdRepository
import com.example.rbim.ui.components.FormDropdown
import com.example.rbim.ui.components.FormSection
import com.example.rbim.ui.components.FormTextField
import com.example.rbim.ui.components.FormTopBar
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.SyncBlue
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.WarmWhite
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import kotlinx.coroutines.launch

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun RegisterHouseholdScreen(
    onBack: () -> Unit,
    onSaved: (Household) -> Unit
) {
    val context = LocalContext.current
    val repository = remember { HouseholdRepository(context) }
    val today = remember {
        SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date())
    }

    var barangay by remember { mutableStateOf("") }
    var address by remember { mutableStateOf("") }
    var respondentName by remember { mutableStateOf("") }
    var householdHead by remember { mutableStateOf("") }
    var visitNumber by remember { mutableStateOf("1") }
    var visitDate by remember { mutableStateOf(today) }
    var startTime by remember { mutableStateOf("") }
    var endTime by remember { mutableStateOf("") }
    var result by remember { mutableStateOf("") }
    var nextVisitDate by remember { mutableStateOf("") }
    var interviewerName by remember { mutableStateOf("") }
    var supervisorName by remember { mutableStateOf("") }
    var encodingSupervisor by remember { mutableStateOf("") }

    var errors by remember { mutableStateOf(mapOf<String, String>()) }
    var savedHousehold by remember { mutableStateOf<Household?>(null) }
    val snackbarHostState = remember { SnackbarHostState() }
    val scope = rememberCoroutineScope()

    fun autoFill() {
        val sample = TestDataFaker.householdSample()
        barangay = sample.barangay
        address = sample.address
        respondentName = sample.respondentName
        householdHead = sample.householdHead
        visitNumber = sample.visitNumber
        visitDate = sample.visitDate
        startTime = sample.startTime
        endTime = sample.endTime
        result = sample.result
        nextVisitDate = sample.nextVisitDate
        interviewerName = sample.interviewerName
        supervisorName = sample.supervisorName
        encodingSupervisor = sample.encodingSupervisor
        errors = emptyMap()
        scope.launch {
            snackbarHostState.showSnackbar("Form auto-filled with test data")
        }
    }

    fun validate(): Boolean {
        val next = mutableMapOf<String, String>()
        if (address.isBlank()) next["address"] = "Address is required"
        if (respondentName.isBlank()) next["respondent_name"] = "Respondent Name is required"
        if (householdHead.isBlank()) next["household_head"] = "Household Head is required"
        if (visitNumber.toIntOrNull() == null || visitNumber.toInt() < 1) {
            next["visit_number"] = "Visit Number must be a number ≥ 1"
        }
        if (visitDate.isBlank()) next["visit_date"] = "Visit Date is required"
        if (result.isBlank()) next["result"] = "Result is required"
        if (result == "Incomplete" && nextVisitDate.isBlank()) {
            next["next_visit_date"] = "Next Visit Date is required when result is Incomplete"
        }
        if (interviewerName.isBlank()) next["interviewer_name"] = "Interviewer Name is required"
        errors = next
        return next.isEmpty()
    }

    fun save() {
        if (!validate()) return
        val household = repository.createHousehold(
            address = address,
            barangay = barangay,
            respondentName = respondentName,
            householdHead = householdHead,
            totalMembers = 0,
            visitNumber = visitNumber.toInt(),
            visitDate = visitDate,
            startTime = startTime,
            endTime = endTime,
            result = result,
            nextVisitDate = if (result == "Incomplete") nextVisitDate else "",
            interviewerName = interviewerName,
            supervisorName = supervisorName,
            encodingSupervisor = encodingSupervisor
        )
        savedHousehold = household
    }

    Scaffold(
        topBar = {
            FormTopBar(
                title = "Register Household",
                subtitle = "Survey session details",
                onBack = onBack
            )
        },
        bottomBar = {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(WarmWhite)
                    .navigationBarsPadding()
                    .padding(16.dp),
                verticalArrangement = Arrangement.spacedBy(10.dp)
            ) {
                OutlinedButton(
                    onClick = { autoFill() },
                    modifier = Modifier.fillMaxWidth(),
                    shape = RoundedCornerShape(12.dp),
                    colors = ButtonDefaults.outlinedButtonColors(contentColor = SyncBlue)
                ) {
                    Icon(Icons.Outlined.AutoFixHigh, contentDescription = null)
                    Spacer(modifier = Modifier.padding(4.dp))
                    Text("Auto-Fill (Testing)")
                }
                Row(horizontalArrangement = Arrangement.spacedBy(12.dp)) {
                    OutlinedButton(
                        onClick = onBack,
                        modifier = Modifier.weight(1f),
                        shape = RoundedCornerShape(12.dp)
                    ) {
                        Text("Cancel", color = Slate700)
                    }
                    Button(
                        onClick = { save() },
                        modifier = Modifier.weight(1.2f),
                        shape = RoundedCornerShape(12.dp),
                        colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
                    ) {
                        Icon(Icons.Outlined.Save, contentDescription = null)
                        Spacer(modifier = Modifier.padding(4.dp))
                        Text("Save Household")
                    }
                }
            }
        },
        snackbarHost = { SnackbarHost(snackbarHostState) },
        containerColor = CreamSurface
    ) { padding ->
        Column(
            modifier = Modifier
                .padding(padding)
                .imePadding()
                .fillMaxWidth()
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 16.dp, vertical = 12.dp),
            verticalArrangement = Arrangement.spacedBy(14.dp)
        ) {
                FormSection(title = "Location Information") {
                    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        FormTextField(
                            value = "Negros Occidental",
                            onValueChange = {},
                            label = "Province",
                            required = true,
                            readOnly = true,
                            enabled = false,
                            supportingText = "Pre-set"
                        )
                        FormTextField(
                            value = "Bago City",
                            onValueChange = {},
                            label = "City/Municipality",
                            required = true,
                            readOnly = true,
                            enabled = false,
                            supportingText = "Pre-set"
                        )
                        FormTextField(
                            value = barangay,
                            onValueChange = { barangay = it },
                            label = "Barangay",
                            supportingText = "Assigned barangay"
                        )
                        FormTextField(
                            value = address,
                            onValueChange = { address = it },
                            label = "Complete Address",
                            required = true,
                            singleLine = false,
                            minLines = 3,
                            isError = errors.containsKey("address"),
                            errorText = errors["address"]
                        )
                    }
                }

                FormSection(title = "Household Details") {
                    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        FormTextField(
                            value = respondentName,
                            onValueChange = { respondentName = it },
                            label = "Respondent Name",
                            required = true,
                            isError = errors.containsKey("respondent_name"),
                            errorText = errors["respondent_name"]
                        )
                        FormTextField(
                            value = householdHead,
                            onValueChange = { householdHead = it },
                            label = "Household Head",
                            required = true,
                            supportingText = "Mandatory — cannot be empty",
                            isError = errors.containsKey("household_head"),
                            errorText = errors["household_head"]
                        )
                        FormTextField(
                            value = "0",
                            onValueChange = {},
                            label = "Total Members",
                            readOnly = true,
                            enabled = false,
                            keyboardType = KeyboardType.Number,
                            supportingText = "Auto-updates from registered members"
                        )
                    }
                }

                FormSection(title = "Visit Information") {
                    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        FormTextField(
                            value = visitNumber,
                            onValueChange = { visitNumber = it.filter { c -> c.isDigit() } },
                            label = "Visit Number",
                            required = true,
                            keyboardType = KeyboardType.Number,
                            isError = errors.containsKey("visit_number"),
                            errorText = errors["visit_number"]
                        )
                        FormTextField(
                            value = visitDate,
                            onValueChange = { visitDate = it },
                            label = "Visit Date",
                            required = true,
                            supportingText = "Format: YYYY-MM-DD",
                            isError = errors.containsKey("visit_date"),
                            errorText = errors["visit_date"]
                        )
                        FormTextField(
                            value = startTime,
                            onValueChange = { startTime = it },
                            label = "Start Time",
                            supportingText = "Format: HH:MM"
                        )
                        FormTextField(
                            value = endTime,
                            onValueChange = { endTime = it },
                            label = "End Time",
                            supportingText = "Format: HH:MM"
                        )
                        FormDropdown(
                            value = result,
                            onValueChange = {
                                result = it
                                if (!FieldVisibility.showNextVisitDate(it)) nextVisitDate = ""
                            },
                            label = "Result",
                            options = CensusOptions.visitResults,
                            required = true,
                            isError = errors.containsKey("result"),
                            errorText = errors["result"]
                        )
                        if (FieldVisibility.showNextVisitDate(result)) {
                            FormTextField(
                                value = nextVisitDate,
                                onValueChange = { nextVisitDate = it },
                                label = "Next Visit Date",
                                required = true,
                                supportingText = "Required when result is Incomplete",
                                isError = errors.containsKey("next_visit_date"),
                                errorText = errors["next_visit_date"]
                            )
                        }
                    }
                }

                FormSection(title = "Personnel Information") {
                    Column(verticalArrangement = Arrangement.spacedBy(10.dp)) {
                        FormTextField(
                            value = interviewerName,
                            onValueChange = { interviewerName = it },
                            label = "Interviewer Name",
                            required = true,
                            isError = errors.containsKey("interviewer_name"),
                            errorText = errors["interviewer_name"]
                        )
                        FormTextField(
                            value = supervisorName,
                            onValueChange = { supervisorName = it },
                            label = "Supervisor Name"
                        )
                        FormTextField(
                            value = encodingSupervisor,
                            onValueChange = { encodingSupervisor = it },
                            label = "Encoding Supervisor"
                        )
                    }
                }

            Spacer(modifier = Modifier.height(24.dp))
        }
    }

    savedHousehold?.let { household ->
        AlertDialog(
            onDismissRequest = { },
            title = {
                Text("Household Saved", fontWeight = FontWeight.SemiBold, color = TealDark)
            },
            text = {
                Column {
                    Text(
                        "Household added successfully.",
                        color = Slate700
                    )
                    Spacer(modifier = Modifier.height(12.dp))
                    Text(
                        "Household Number: ${household.displayNumber}",
                        fontWeight = FontWeight.Bold,
                        color = TealPrimary
                    )
                    Spacer(modifier = Modifier.height(8.dp))
                    Text(
                        "Continue to register household members?",
                        color = Slate500
                    )
                }
            },
            confirmButton = {
                Button(
                    onClick = { onSaved(household) },
                    colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
                ) {
                    Text("Register Members")
                }
            },
            dismissButton = {
                TextButton(onClick = onBack) {
                    Text("Back to Home", color = Slate700)
                }
            }
        )
    }
}
