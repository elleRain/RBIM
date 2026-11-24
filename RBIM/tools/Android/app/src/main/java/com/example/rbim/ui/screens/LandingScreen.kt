package com.example.rbim.ui.screens

import androidx.compose.animation.core.Animatable
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.tween
import androidx.compose.foundation.Image
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.clickable
import androidx.compose.foundation.interaction.MutableInteractionSource
import androidx.compose.foundation.interaction.collectIsPressedAsState
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.navigationBarsPadding
import androidx.compose.foundation.layout.offset
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.statusBarsPadding
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.CircleShape
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.foundation.verticalScroll
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.CloudSync
import androidx.compose.material.icons.outlined.FolderOpen
import androidx.compose.material.icons.outlined.HomeWork
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.draw.scale
import androidx.compose.ui.draw.shadow
import androidx.compose.ui.graphics.Brush
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.graphicsLayer
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.layout.ContentScale
import androidx.compose.ui.res.painterResource
import androidx.compose.ui.text.style.TextAlign
import androidx.compose.ui.tooling.preview.Preview
import androidx.compose.ui.unit.dp
import com.example.rbim.R
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.RBIMTheme
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.Slate900
import com.example.rbim.ui.theme.SoftMint
import com.example.rbim.ui.theme.SoftSky
import com.example.rbim.ui.theme.SyncBlue
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealLight
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.ViewAmber
import com.example.rbim.ui.theme.WarmWhite
import kotlinx.coroutines.delay

@Composable
fun LandingScreen(
    onRegisterHousehold: () -> Unit = {},
    onViewSavedHousehold: () -> Unit = {},
    onSyncOnline: () -> Unit = {}
) {
    val headerAlpha = remember { Animatable(0f) }
    val headerOffset = remember { Animatable(24f) }
    val actionsAlpha = remember { Animatable(0f) }
    val actionsOffset = remember { Animatable(32f) }

    LaunchedEffect(Unit) {
        headerAlpha.animateTo(1f, tween(500, easing = FastOutSlowInEasing))
        headerOffset.animateTo(0f, tween(500, easing = FastOutSlowInEasing))
        delay(80)
        actionsAlpha.animateTo(1f, tween(550, easing = FastOutSlowInEasing))
        actionsOffset.animateTo(0f, tween(550, easing = FastOutSlowInEasing))
    }

    Box(
        modifier = Modifier
            .fillMaxSize()
            .background(
                brush = Brush.verticalGradient(
                    colors = listOf(
                        SoftMint,
                        CreamSurface,
                        SoftSky
                    )
                )
            )
    ) {
        // Decorative atmosphere blobs
        Box(
            modifier = Modifier
                .align(Alignment.TopEnd)
                .offset(x = 48.dp, y = 24.dp)
                .size(220.dp)
                .background(
                    brush = Brush.radialGradient(
                        colors = listOf(
                            TealLight.copy(alpha = 0.18f),
                            Color.Transparent
                        )
                    ),
                    shape = CircleShape
                )
        )
        Box(
            modifier = Modifier
                .align(Alignment.BottomStart)
                .offset(x = (-60).dp, y = (-40).dp)
                .size(260.dp)
                .background(
                    brush = Brush.radialGradient(
                        colors = listOf(
                            SyncBlue.copy(alpha = 0.08f),
                            Color.Transparent
                        )
                    ),
                    shape = CircleShape
                )
        )

        Column(
            modifier = Modifier
                .fillMaxSize()
                .statusBarsPadding()
                .navigationBarsPadding()
                .verticalScroll(rememberScrollState())
                .padding(horizontal = 24.dp)
                .padding(top = 32.dp, bottom = 28.dp),
            horizontalAlignment = Alignment.CenterHorizontally
        ) {
            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .graphicsLayer {
                        alpha = headerAlpha.value
                        translationY = headerOffset.value
                    },
                horizontalAlignment = Alignment.CenterHorizontally
            ) {
                BrandMark()
                Spacer(modifier = Modifier.height(20.dp))
                Text(
                    text = "RBIM",
                    style = MaterialTheme.typography.displayLarge,
                    color = TealDark,
                    textAlign = TextAlign.Center
                )
                Spacer(modifier = Modifier.height(8.dp))
                Text(
                    text = "Household Registry",
                    style = MaterialTheme.typography.titleMedium,
                    color = TealPrimary,
                    textAlign = TextAlign.Center
                )
                Spacer(modifier = Modifier.height(6.dp))
                Text(
                    text = "Commission on Population and Development",
                    style = MaterialTheme.typography.labelLarge,
                    color = Slate500,
                    textAlign = TextAlign.Center
                )
            }

            Spacer(modifier = Modifier.height(40.dp))

            Column(
                modifier = Modifier
                    .fillMaxWidth()
                    .graphicsLayer {
                        alpha = actionsAlpha.value
                        translationY = actionsOffset.value
                    },
                verticalArrangement = Arrangement.spacedBy(14.dp)
            ) {
                LandingActionButton(
                    title = "Register Household",
                    subtitle = "Add a new household profile",
                    icon = Icons.Outlined.HomeWork,
                    accent = TealPrimary,
                    accentSoft = TealPrimary.copy(alpha = 0.12f),
                    onClick = onRegisterHousehold
                )
                LandingActionButton(
                    title = "View Saved Household",
                    subtitle = "Browse locally stored records",
                    icon = Icons.Outlined.FolderOpen,
                    accent = ViewAmber,
                    accentSoft = ViewAmber.copy(alpha = 0.12f),
                    onClick = onViewSavedHousehold
                )
                LandingActionButton(
                    title = "Sync Online",
                    subtitle = "Upload and refresh cloud data",
                    icon = Icons.Outlined.CloudSync,
                    accent = SyncBlue,
                    accentSoft = SyncBlue.copy(alpha = 0.12f),
                    onClick = onSyncOnline
                )
            }

            Spacer(modifier = Modifier.height(48.dp))

            Text(
                text = "Commission on Population and Development · Philippines",
                style = MaterialTheme.typography.labelMedium,
                color = Slate500.copy(alpha = 0.85f),
                textAlign = TextAlign.Center
            )
        }
    }
}

@Composable
private fun BrandMark() {
    Box(
        contentAlignment = Alignment.Center,
        modifier = Modifier
            .size(128.dp)
            .shadow(
                elevation = 10.dp,
                shape = CircleShape,
                ambientColor = TealDark.copy(alpha = 0.18f),
                spotColor = TealDark.copy(alpha = 0.22f)
            )
            .clip(CircleShape)
            .background(WarmWhite)
            .border(2.dp, SoftMint, CircleShape)
            .padding(8.dp)
    ) {
        Image(
            painter = painterResource(id = R.drawable.system_logo),
            contentDescription = "Commission on Population and Development Philippines",
            contentScale = ContentScale.Fit,
            modifier = Modifier.fillMaxSize()
        )
    }
}

@Composable
private fun LandingActionButton(
    title: String,
    subtitle: String,
    icon: ImageVector,
    accent: Color,
    accentSoft: Color,
    onClick: () -> Unit
) {
    val interactionSource = remember { MutableInteractionSource() }
    val pressed by interactionSource.collectIsPressedAsState()
    val scale = if (pressed) 0.98f else 1f

    Row(
        modifier = Modifier
            .fillMaxWidth()
            .scale(scale)
            .shadow(
                elevation = if (pressed) 2.dp else 8.dp,
                shape = RoundedCornerShape(20.dp),
                ambientColor = Slate900.copy(alpha = 0.06f),
                spotColor = Slate900.copy(alpha = 0.10f)
            )
            .clip(RoundedCornerShape(20.dp))
            .background(WarmWhite)
            .border(
                width = 1.dp,
                color = Color(0xFFE2E8F0),
                shape = RoundedCornerShape(20.dp)
            )
            .clickable(
                interactionSource = interactionSource,
                indication = null,
                onClick = onClick
            )
            .padding(horizontal = 18.dp, vertical = 18.dp),
        verticalAlignment = Alignment.CenterVertically
    ) {
        Box(
            contentAlignment = Alignment.Center,
            modifier = Modifier
                .size(52.dp)
                .clip(RoundedCornerShape(16.dp))
                .background(accentSoft)
        ) {
            Icon(
                imageVector = icon,
                contentDescription = null,
                tint = accent,
                modifier = Modifier.size(26.dp)
            )
        }
        Spacer(modifier = Modifier.width(16.dp))
        Column(modifier = Modifier.weight(1f)) {
            Text(
                text = title,
                style = MaterialTheme.typography.titleLarge,
                color = Slate900
            )
            Spacer(modifier = Modifier.height(2.dp))
            Text(
                text = subtitle,
                style = MaterialTheme.typography.bodyMedium,
                color = Slate700.copy(alpha = 0.75f)
            )
        }
        Box(
            contentAlignment = Alignment.Center,
            modifier = Modifier
                .size(32.dp)
                .clip(CircleShape)
                .background(accentSoft)
        ) {
            Text(
                text = "›",
                style = MaterialTheme.typography.titleLarge,
                color = accent
            )
        }
    }
}

@Preview(showBackground = true, showSystemUi = true)
@Composable
private fun LandingScreenPreview() {
    RBIMTheme {
        LandingScreen()
    }
}
