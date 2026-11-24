package com.example.rbim.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.PaddingValues
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Group
import androidx.compose.material.icons.outlined.HomeWork
import androidx.compose.material.icons.outlined.PersonAdd
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.OutlinedTextFieldDefaults
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.example.rbim.data.model.Household
import com.example.rbim.data.repository.HouseholdRepository
import com.example.rbim.ui.components.FormTopBar
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.Slate100
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.Slate900
import com.example.rbim.ui.theme.SoftMint
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.ViewAmber
import com.example.rbim.ui.theme.WarmWhite

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ViewHouseholdsScreen(
    onBack: () -> Unit,
    onAddMember: (Household) -> Unit,
    onViewMembers: (Household) -> Unit,
    onRegisterHousehold: () -> Unit = {},
    refreshKey: Int = 0
) {
    val context = LocalContext.current
    val repository = remember { HouseholdRepository(context) }
    var query by remember { mutableStateOf("") }

    val households = remember(refreshKey) { repository.getHouseholds() }
    val memberCounts = remember(households, refreshKey) {
        households.associate { it.localId to repository.getMembers(it.localId).size }
    }
    val filtered = remember(households, query) {
        val q = query.trim().lowercase()
        if (q.isEmpty()) households
        else households.filter {
            it.displayNumber.lowercase().contains(q) ||
                it.householdHead.lowercase().contains(q) ||
                it.address.lowercase().contains(q) ||
                it.barangay.lowercase().contains(q) ||
                it.respondentName.lowercase().contains(q)
        }
    }

    Scaffold(
        topBar = {
            FormTopBar(
                title = "Saved Households",
                subtitle = "${households.size} registered locally",
                onBack = onBack
            )
        },
        containerColor = CreamSurface
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
                .navigationBarsPadding()
        ) {
            OutlinedTextField(
                value = query,
                onValueChange = { query = it },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 8.dp),
                placeholder = { Text("Search by head, address, or number") },
                singleLine = true,
                shape = RoundedCornerShape(14.dp),
                colors = OutlinedTextFieldDefaults.colors(
                    focusedBorderColor = TealPrimary,
                    unfocusedBorderColor = Color(0xFFCBD5E1),
                    focusedContainerColor = WarmWhite,
                    unfocusedContainerColor = WarmWhite,
                    cursorColor = TealPrimary
                )
            )

            if (filtered.isEmpty()) {
                EmptyHouseholdsState(
                    hasHouseholds = households.isNotEmpty(),
                    onRegisterHousehold = onRegisterHousehold,
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(24.dp)
                )
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
                    verticalArrangement = Arrangement.spacedBy(12.dp)
                ) {
                    items(filtered, key = { it.localId }) { household ->
                        HouseholdListCard(
                            household = household,
                            registeredMembers = memberCounts[household.localId] ?: 0,
                            onViewMembers = { onViewMembers(household) },
                            onAddMember = { onAddMember(household) }
                        )
                    }
                    item { Spacer(modifier = Modifier.height(12.dp)) }
                }
            }
        }
    }
}

@Composable
private fun HouseholdListCard(
    household: Household,
    registeredMembers: Int,
    onViewMembers: () -> Unit,
    onAddMember: () -> Unit
) {
    val statusColor = when (household.result.lowercase()) {
        "complete" -> TealPrimary
        "incomplete" -> ViewAmber
        else -> Slate500
    }

    Column(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(
                elevation = 6.dp,
                shape = RoundedCornerShape(18.dp),
                ambientColor = Slate900.copy(alpha = 0.06f),
                spotColor = Slate900.copy(alpha = 0.08f)
            )
            .clip(RoundedCornerShape(18.dp))
            .background(WarmWhite)
            .border(1.dp, Slate100, RoundedCornerShape(18.dp))
            .padding(16.dp)
    ) {
        Row(
            modifier = Modifier.fillMaxWidth(),
            verticalAlignment = Alignment.CenterVertically
        ) {
            Box(
                contentAlignment = Alignment.Center,
                modifier = Modifier
                    .size(46.dp)
                    .clip(RoundedCornerShape(14.dp))
                    .background(SoftMint)
            ) {
                Icon(
                    imageVector = Icons.Outlined.HomeWork,
                    contentDescription = null,
                    tint = TealPrimary,
                    modifier = Modifier.size(24.dp)
                )
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = household.displayNumber,
                    style = MaterialTheme.typography.titleMedium,
                    color = TealDark,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = household.householdHead.ifBlank { "No household head" },
                    style = MaterialTheme.typography.bodyLarge,
                    color = Slate900,
                    fontWeight = FontWeight.SemiBold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis
                )
            }
            StatusChip(label = household.result.ifBlank { "Pending" }, color = statusColor)
        }

        Spacer(modifier = Modifier.height(12.dp))

        MetaRow(label = "Address", value = household.address.ifBlank { "—" })
        MetaRow(
            label = "Barangay",
            value = listOf(household.barangay, household.cityMunicipality)
                .filter { it.isNotBlank() }
                .joinToString(", ")
                .ifBlank { "—" }
        )
        MetaRow(label = "Visit date", value = household.visitDate.ifBlank { "—" })
        MetaRow(
            label = "Members",
            value = "$registeredMembers registered"
        )

        Spacer(modifier = Modifier.height(14.dp))

        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(10.dp)
        ) {
            OutlinedButton(
                onClick = onViewMembers,
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(12.dp),
                colors = ButtonDefaults.outlinedButtonColors(contentColor = TealDark)
            ) {
                Icon(
                    imageVector = Icons.Outlined.Group,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
                Spacer(modifier = Modifier.width(6.dp))
                Text("View Members")
            }
            Button(
                onClick = onAddMember,
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(12.dp),
                colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
            ) {
                Icon(
                    imageVector = Icons.Outlined.PersonAdd,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
                Spacer(modifier = Modifier.width(6.dp))
                Text("Add Member")
            }
        }
    }
}

@Composable
private fun MetaRow(label: String, value: String) {
    Row(
        modifier = Modifier
            .fillMaxWidth()
            .padding(vertical = 2.dp),
        horizontalArrangement = Arrangement.SpaceBetween
    ) {
        Text(
            text = label,
            style = MaterialTheme.typography.bodySmall,
            color = Slate500,
            modifier = Modifier.padding(end = 12.dp)
        )
        Text(
            text = value,
            style = MaterialTheme.typography.bodyMedium,
            color = Slate700,
            maxLines = 2,
            overflow = TextOverflow.Ellipsis,
            modifier = Modifier.weight(1f, fill = false)
        )
    }
}

@Composable
private fun StatusChip(label: String, color: Color) {
    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(999.dp))
            .background(color.copy(alpha = 0.12f))
            .padding(horizontal = 10.dp, vertical = 5.dp)
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Box(
                modifier = Modifier
                    .size(6.dp)
                    .clip(CircleShape)
                    .background(color)
            )
            Spacer(modifier = Modifier.width(6.dp))
            Text(
                text = label,
                style = MaterialTheme.typography.labelMedium,
                color = color,
                fontWeight = FontWeight.SemiBold
            )
        }
    }
}

@Composable
private fun EmptyHouseholdsState(
    hasHouseholds: Boolean,
    onRegisterHousehold: () -> Unit,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier,
        horizontalAlignment = Alignment.CenterHorizontally,
        verticalArrangement = Arrangement.Center
    ) {
        Box(
            contentAlignment = Alignment.Center,
            modifier = Modifier
                .size(72.dp)
                .clip(RoundedCornerShape(22.dp))
                .background(SoftMint)
        ) {
            Icon(
                imageVector = if (hasHouseholds) Icons.Outlined.SearchOff else Icons.Outlined.HomeWork,
                contentDescription = null,
                tint = TealPrimary,
                modifier = Modifier.size(34.dp)
            )
        }
        Spacer(modifier = Modifier.height(16.dp))
        Text(
            text = if (hasHouseholds) "No matches found" else "No households yet",
            style = MaterialTheme.typography.titleLarge,
            color = Slate900,
            fontWeight = FontWeight.SemiBold
        )
        Spacer(modifier = Modifier.height(6.dp))
        Text(
            text = if (hasHouseholds) {
                "Try a different search term."
            } else {
                "Register a household to see it listed here."
            },
            style = MaterialTheme.typography.bodyMedium,
            color = Slate500
        )
        if (!hasHouseholds) {
            Spacer(modifier = Modifier.height(20.dp))
            Button(
                onClick = onRegisterHousehold,
                shape = RoundedCornerShape(12.dp),
                colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
            ) {
                Text("Register Household")
            }
        }
    }
}
