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
import androidx.compose.foundation.lazy.itemsIndexed
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Group
import androidx.compose.material.icons.outlined.PersonAdd
import androidx.compose.material.icons.outlined.PersonOff
import androidx.compose.material.icons.outlined.SearchOff
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
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
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.platform.LocalContext
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.example.rbim.data.model.CensusOptions
import com.example.rbim.data.model.Household
import com.example.rbim.data.model.HouseholdMember
import com.example.rbim.data.repository.HouseholdRepository
import com.example.rbim.ui.components.FormTopBar
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.Slate100
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.Slate900
import com.example.rbim.ui.theme.SoftMint
import com.example.rbim.ui.theme.SoftSky
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealLight
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.WarmWhite

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun ViewMembersScreen(
    household: Household,
    onBack: () -> Unit,
    onAddMember: () -> Unit,
    refreshKey: Int = 0
) {
    val context = LocalContext.current
    val repository = remember { HouseholdRepository(context) }
    var query by remember { mutableStateOf("") }

    val latestHousehold = remember(household.localId, refreshKey) {
        repository.getHousehold(household.localId) ?: household
    }
    val members = remember(latestHousehold.localId, refreshKey) {
        repository.getMembers(latestHousehold.localId)
            .sortedWith(
                compareBy<HouseholdMember> { it.relationship != "01" }
                    .thenBy { it.name.lowercase() }
            )
    }
    val filtered = remember(members, query) {
        val q = query.trim().lowercase()
        if (q.isEmpty()) members
        else members.filter {
            it.name.lowercase().contains(q) ||
                relationshipLabel(it.relationship).lowercase().contains(q) ||
                sexLabel(it.sex).lowercase().contains(q)
        }
    }

    Scaffold(
        topBar = {
            FormTopBar(
                title = "Household Members",
                subtitle = "${latestHousehold.displayNumber} · ${latestHousehold.householdHead.ifBlank { "No head listed" }}",
                onBack = onBack
            )
        },
        bottomBar = {
            Box(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(WarmWhite)
                    .border(1.dp, SoftSky)
                    .navigationBarsPadding()
                    .padding(horizontal = 16.dp, vertical = 12.dp)
            ) {
                Button(
                    onClick = onAddMember,
                    modifier = Modifier.fillMaxWidth().height(52.dp),
                    shape = RoundedCornerShape(14.dp),
                    colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
                ) {
                    Icon(
                        imageVector = Icons.Outlined.PersonAdd,
                        contentDescription = null,
                        modifier = Modifier.size(20.dp)
                    )
                    Spacer(modifier = Modifier.width(8.dp))
                    Text("Add Member", fontWeight = FontWeight.SemiBold)
                }
            }
        },
        containerColor = CreamSurface
    ) { padding ->
        Column(
            modifier = Modifier
                .fillMaxSize()
                .padding(padding)
        ) {
            MembersSummaryHeader(
                address = latestHousehold.address,
                barangay = listOf(latestHousehold.barangay, latestHousehold.cityMunicipality)
                    .filter { it.isNotBlank() }
                    .joinToString(", "),
                totalCount = members.size,
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp, vertical = 10.dp)
            )

            OutlinedTextField(
                value = query,
                onValueChange = { query = it },
                modifier = Modifier
                    .fillMaxWidth()
                    .padding(horizontal = 16.dp)
                    .padding(bottom = 8.dp),
                placeholder = { Text("Search by name, relationship, or sex") },
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
                EmptyMembersState(
                    hasMembers = members.isNotEmpty(),
                    onAddMember = onAddMember,
                    modifier = Modifier
                        .fillMaxSize()
                        .padding(24.dp)
                )
            } else {
                LazyColumn(
                    modifier = Modifier.fillMaxSize(),
                    contentPadding = PaddingValues(horizontal = 16.dp, vertical = 8.dp),
                    verticalArrangement = Arrangement.spacedBy(10.dp)
                ) {
                    itemsIndexed(filtered, key = { _, m -> m.localId }) { index, member ->
                        MemberListCard(
                            index = index + 1,
                            member = member
                        )
                    }
                    item { Spacer(modifier = Modifier.height(8.dp)) }
                }
            }
        }
    }
}

@Composable
private fun MembersSummaryHeader(
    address: String,
    barangay: String,
    totalCount: Int,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .shadow(
                elevation = 4.dp,
                shape = RoundedCornerShape(16.dp),
                ambientColor = TealDark.copy(alpha = 0.08f),
                spotColor = TealDark.copy(alpha = 0.1f)
            )
            .clip(RoundedCornerShape(16.dp))
            .background(
                brush = Brush.linearGradient(
                    colors = listOf(TealDark, TealPrimary, TealLight.copy(alpha = 0.9f))
                )
            )
            .padding(16.dp)
    ) {
        Row(verticalAlignment = Alignment.CenterVertically) {
            Box(
                contentAlignment = Alignment.Center,
                modifier = Modifier
                    .size(44.dp)
                    .clip(RoundedCornerShape(12.dp))
                    .background(WarmWhite.copy(alpha = 0.18f))
            ) {
                Icon(
                    imageVector = Icons.Outlined.Group,
                    contentDescription = null,
                    tint = WarmWhite,
                    modifier = Modifier.size(24.dp)
                )
            }
            Spacer(modifier = Modifier.width(12.dp))
            Column(modifier = Modifier.weight(1f)) {
                Text(
                    text = if (totalCount == 1) "1 member registered" else "$totalCount members registered",
                    style = MaterialTheme.typography.titleMedium,
                    color = WarmWhite,
                    fontWeight = FontWeight.Bold
                )
                Text(
                    text = listOf(address, barangay).filter { it.isNotBlank() }.joinToString(" · ")
                        .ifBlank { "No address on file" },
                    style = MaterialTheme.typography.bodySmall,
                    color = WarmWhite.copy(alpha = 0.85f),
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
            }
        }
    }
}

@Composable
private fun MemberListCard(
    index: Int,
    member: HouseholdMember
) {
    val relationship = relationshipLabel(member.relationship)
    val sex = sexLabel(member.sex)
    val initials = memberInitials(member.name)

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .shadow(
                elevation = 4.dp,
                shape = RoundedCornerShape(16.dp),
                ambientColor = Slate900.copy(alpha = 0.05f),
                spotColor = Slate900.copy(alpha = 0.07f)
            )
            .clip(RoundedCornerShape(16.dp))
            .background(WarmWhite)
            .border(1.dp, Slate100, RoundedCornerShape(16.dp))
            .padding(14.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            contentAlignment = Alignment.Center,
            modifier = Modifier
                .size(48.dp)
                .clip(CircleShape)
                .background(SoftMint)
        ) {
            Text(
                text = initials,
                style = MaterialTheme.typography.titleMedium,
                color = TealDark,
                fontWeight = FontWeight.Bold
            )
        }
        Spacer(modifier = Modifier.width(12.dp))
        Column(modifier = Modifier.weight(1f)) {
            Row(verticalAlignment = Alignment.CenterVertically) {
                Text(
                    text = member.name.ifBlank { "Unnamed member" },
                    style = MaterialTheme.typography.titleSmall,
                    color = Slate900,
                    fontWeight = FontWeight.SemiBold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f, fill = false)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text(
                    text = "#$index",
                    style = MaterialTheme.typography.labelSmall,
                    color = Slate500
                )
            }
            Spacer(modifier = Modifier.height(4.dp))
            Text(
                text = relationship,
                style = MaterialTheme.typography.bodyMedium,
                color = TealPrimary,
                fontWeight = FontWeight.Medium
            )
            Spacer(modifier = Modifier.height(6.dp))
            Row(horizontalArrangement = Arrangement.spacedBy(8.dp)) {
                InfoPill(text = sex)
                InfoPill(text = if (member.dateOfBirth.isNotBlank()) "Age ${member.age}" else "Age —")
                if (member.dateOfBirth.isNotBlank()) {
                    InfoPill(text = member.dateOfBirth)
                }
            }
        }
    }
}

@Composable
private fun InfoPill(text: String) {
    Box(
        modifier = Modifier
            .clip(RoundedCornerShape(999.dp))
            .background(Slate100)
            .padding(horizontal = 8.dp, vertical = 3.dp)
    ) {
        Text(
            text = text,
            style = MaterialTheme.typography.labelSmall,
            color = Slate700,
            maxLines = 1,
            overflow = TextOverflow.Ellipsis
        )
    }
}

@Composable
private fun EmptyMembersState(
    hasMembers: Boolean,
    onAddMember: () -> Unit,
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
                imageVector = if (hasMembers) Icons.Outlined.SearchOff else Icons.Outlined.PersonOff,
                contentDescription = null,
                tint = TealPrimary,
                modifier = Modifier.size(34.dp)
            )
        }
        Spacer(modifier = Modifier.height(16.dp))
        Text(
            text = if (hasMembers) "No matches found" else "No members yet",
            style = MaterialTheme.typography.titleLarge,
            color = Slate900,
            fontWeight = FontWeight.SemiBold
        )
        Spacer(modifier = Modifier.height(6.dp))
        Text(
            text = if (hasMembers) {
                "Try a different search term."
            } else {
                "Register the first household member to begin the roster."
            },
            style = MaterialTheme.typography.bodyMedium,
            color = Slate500
        )
        if (!hasMembers) {
            Spacer(modifier = Modifier.height(20.dp))
            Button(
                onClick = onAddMember,
                shape = RoundedCornerShape(12.dp),
                colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
            ) {
                Icon(
                    imageVector = Icons.Outlined.PersonAdd,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
                Spacer(modifier = Modifier.width(8.dp))
                Text("Add First Member")
            }
        }
    }
}

private fun relationshipLabel(code: String): String =
    CensusOptions.relationships.find { it.first == code }?.second
        ?: code.ifBlank { "Relationship not set" }

private fun sexLabel(sex: Int): String =
    CensusOptions.sexes.find { it.first == sex }?.second ?: "Sex —"

private fun memberInitials(name: String): String {
    val parts = name.trim().split(Regex("\\s+")).filter { it.isNotBlank() }
    return when {
        parts.isEmpty() -> "?"
        parts.size == 1 -> parts[0].take(2).uppercase()
        else -> "${parts.first().first()}${parts.last().first()}".uppercase()
    }
}
