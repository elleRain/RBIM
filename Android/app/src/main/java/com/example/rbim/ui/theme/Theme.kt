package com.example.rbim.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.darkColorScheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable
import androidx.compose.ui.graphics.Color

private val DarkColorScheme = darkColorScheme(
    primary = Teal80,
    onPrimary = TealDark,
    primaryContainer = TealDark,
    onPrimaryContainer = TealContainer,
    secondary = TealGrey80,
    onSecondary = Slate900,
    tertiary = Mist80,
    background = DarkBackground,
    onBackground = Color(0xFFE8EEF0),
    surface = DarkSurface,
    onSurface = Color(0xFFE8EEF0),
    surfaceVariant = Color(0xFF1A2A2C),
    onSurfaceVariant = Color(0xFFB0C4C3),
    outline = Color(0xFF4A6362)
)

private val LightColorScheme = lightColorScheme(
    primary = TealPrimary,
    onPrimary = WarmWhite,
    primaryContainer = TealContainer,
    onPrimaryContainer = TealDark,
    secondary = Slate700,
    onSecondary = WarmWhite,
    tertiary = AccentCoral,
    background = CreamSurface,
    onBackground = Slate900,
    surface = WarmWhite,
    onSurface = Slate900,
    surfaceVariant = SoftMint,
    onSurfaceVariant = Slate700,
    outline = Color(0xFFB8C9C7)
)

@Composable
fun RBIMTheme(
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit
) {
    val colorScheme = if (darkTheme) DarkColorScheme else LightColorScheme

    MaterialTheme(
        colorScheme = colorScheme,
        typography = Typography,
        content = content
    )
}
