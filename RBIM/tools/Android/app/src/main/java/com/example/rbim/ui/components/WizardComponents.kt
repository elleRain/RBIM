package com.example.rbim.ui.components

import androidx.compose.animation.animateColorAsState
import androidx.compose.animation.core.FastOutSlowInEasing
import androidx.compose.animation.core.animateFloatAsState
import androidx.compose.animation.core.tween
import androidx.compose.foundation.background
import androidx.compose.foundation.border
import androidx.compose.foundation.horizontalScroll
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.size
import androidx.compose.foundation.layout.width
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.automirrored.filled.ArrowBack
import androidx.compose.material.icons.automirrored.filled.ArrowForward
import androidx.compose.material3.Button
import androidx.compose.material3.ButtonDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.LinearProgressIndicator
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.OutlinedButton
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.graphics.StrokeCap
import androidx.compose.ui.graphics.vector.ImageVector
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.text.style.TextOverflow
import androidx.compose.ui.unit.dp
import com.example.rbim.ui.theme.CreamSurface
import com.example.rbim.ui.theme.Slate100
import com.example.rbim.ui.theme.Slate500
import com.example.rbim.ui.theme.Slate700
import com.example.rbim.ui.theme.Slate900
import com.example.rbim.ui.theme.SoftMint
import com.example.rbim.ui.theme.SyncBlue
import com.example.rbim.ui.theme.TealDark
import com.example.rbim.ui.theme.TealPrimary
import com.example.rbim.ui.theme.WarmWhite

data class WizardStep(
    val id: String,
    val title: String,
    val subtitle: String,
    val iconLabel: String = ""
)

@Composable
fun WizardProgressHeader(
    steps: List<WizardStep>,
    currentIndex: Int,
    completionProgress: Float,
    modifier: Modifier = Modifier
) {
    val progress by animateFloatAsState(
        targetValue = completionProgress.coerceIn(0f, 1f),
        animationSpec = tween(durationMillis = 280, easing = FastOutSlowInEasing),
        label = "wizardProgress"
    )
    val current = steps.getOrNull(currentIndex)
    val titleScroll = rememberScrollState()

    LaunchedEffect(currentIndex) {
        val approxItemWidth = 88
        titleScroll.animateScrollTo((currentIndex * approxItemWidth).coerceAtLeast(0))
    }

    Column(
        modifier = modifier
            .fillMaxWidth()
            .background(CreamSurface)
            .padding(horizontal = 16.dp)
            .padding(top = 8.dp, bottom = 10.dp)
    ) {
        Column(
            modifier = Modifier
                .fillMaxWidth()
                .clip(RoundedCornerShape(18.dp))
                .background(WarmWhite)
                .border(1.dp, Slate100, RoundedCornerShape(18.dp))
                .padding(horizontal = 14.dp, vertical = 12.dp)
        ) {
            Row(
                modifier = Modifier.fillMaxWidth(),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Text(
                    text = current?.title.orEmpty(),
                    style = MaterialTheme.typography.titleMedium,
                    color = Slate900,
                    fontWeight = FontWeight.SemiBold,
                    maxLines = 1,
                    overflow = TextOverflow.Ellipsis,
                    modifier = Modifier.weight(1f)
                )
                Text(
                    text = "${(progress * 100).toInt()}%",
                    style = MaterialTheme.typography.labelLarge,
                    color = TealPrimary,
                    fontWeight = FontWeight.Bold
                )
            }

            if (!current?.subtitle.isNullOrBlank()) {
                Spacer(modifier = Modifier.height(2.dp))
                Text(
                    text = current!!.subtitle,
                    style = MaterialTheme.typography.bodySmall,
                    color = Slate500,
                    maxLines = 2,
                    overflow = TextOverflow.Ellipsis
                )
            }

            Spacer(modifier = Modifier.height(10.dp))
            LinearProgressIndicator(
                progress = { progress },
                modifier = Modifier
                    .fillMaxWidth()
                    .height(7.dp)
                    .clip(RoundedCornerShape(999.dp)),
                color = TealPrimary,
                trackColor = SoftMint,
                strokeCap = StrokeCap.Round
            )

            Spacer(modifier = Modifier.height(12.dp))
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .horizontalScroll(titleScroll),
                horizontalArrangement = Arrangement.spacedBy(8.dp),
                verticalAlignment = Alignment.CenterVertically
            ) {
                steps.forEachIndexed { index, step ->
                    val isDone = index < currentIndex
                    val isCurrent = index == currentIndex
                    val titleColor by animateColorAsState(
                        targetValue = when {
                            isCurrent -> TealDark
                            isDone -> TealPrimary
                            else -> Slate500
                        },
                        animationSpec = tween(250),
                        label = "titleColor$index"
                    )
                    val underlineColor by animateColorAsState(
                        targetValue = if (isCurrent) TealPrimary else Color.Transparent,
                        animationSpec = tween(250),
                        label = "underline$index"
                    )

                    Column(
                        horizontalAlignment = Alignment.CenterHorizontally,
                        modifier = Modifier.padding(horizontal = 4.dp)
                    ) {
                        Text(
                            text = step.title,
                            style = MaterialTheme.typography.labelLarge,
                            color = titleColor,
                            fontWeight = if (isCurrent) FontWeight.SemiBold else FontWeight.Medium,
                            maxLines = 1
                        )
                        Spacer(modifier = Modifier.height(4.dp))
                        Box(
                            modifier = Modifier
                                .width(if (isCurrent) 28.dp else 16.dp)
                                .height(3.dp)
                                .clip(RoundedCornerShape(999.dp))
                                .background(underlineColor)
                        )
                    }
                }
            }
        }
    }
}

@Composable
fun WizardStepCard(
    modifier: Modifier = Modifier,
    content: @Composable () -> Unit
) {
    Column(
        modifier = modifier
            .fillMaxWidth()
            .clip(RoundedCornerShape(20.dp))
            .background(WarmWhite)
            .border(1.dp, Slate100, RoundedCornerShape(20.dp))
            .padding(18.dp),
        verticalArrangement = Arrangement.spacedBy(12.dp)
    ) {
        content()
    }
}

@Composable
fun WizardNavigationBar(
    isFirstStep: Boolean,
    isLastStep: Boolean,
    onPrevious: () -> Unit,
    onNext: () -> Unit,
    onAutoFill: () -> Unit,
    nextLabel: String = "Next",
    nextIcon: ImageVector = Icons.AutoMirrored.Filled.ArrowForward,
    modifier: Modifier = Modifier
) {
    Column(
        modifier = modifier
            .fillMaxWidth()
            .background(WarmWhite)
            .padding(horizontal = 16.dp, vertical = 12.dp),
        verticalArrangement = Arrangement.spacedBy(8.dp)
    ) {
        TextButton(
            onClick = onAutoFill,
            modifier = Modifier.align(Alignment.CenterHorizontally)
        ) {
            Text("Auto-Fill (Testing)", color = SyncBlue)
        }
        Row(
            modifier = Modifier.fillMaxWidth(),
            horizontalArrangement = Arrangement.spacedBy(12.dp)
        ) {
            OutlinedButton(
                onClick = onPrevious,
                enabled = !isFirstStep,
                modifier = Modifier.weight(1f),
                shape = RoundedCornerShape(14.dp)
            ) {
                Icon(
                    Icons.AutoMirrored.Filled.ArrowBack,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
                Spacer(modifier = Modifier.width(6.dp))
                Text("Previous", color = if (isFirstStep) Slate500 else Slate700)
            }
            Button(
                onClick = onNext,
                modifier = Modifier.weight(1.25f),
                shape = RoundedCornerShape(14.dp),
                colors = ButtonDefaults.buttonColors(containerColor = TealPrimary)
            ) {
                Text(nextLabel)
                Spacer(modifier = Modifier.width(6.dp))
                Icon(
                    nextIcon,
                    contentDescription = null,
                    modifier = Modifier.size(18.dp)
                )
            }
        }
    }
}
