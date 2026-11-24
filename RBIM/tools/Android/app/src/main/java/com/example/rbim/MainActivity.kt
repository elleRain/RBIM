package com.example.rbim

import android.os.Bundle
import android.widget.Toast
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.enableEdgeToEdge
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.material3.Surface
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import com.example.rbim.data.model.Household
import com.example.rbim.ui.screens.LandingScreen
import com.example.rbim.ui.screens.RegisterHouseholdScreen
import com.example.rbim.ui.screens.RegisterMemberScreen
import com.example.rbim.ui.screens.ViewHouseholdsScreen
import com.example.rbim.ui.screens.ViewMembersScreen
import com.example.rbim.ui.theme.RBIMTheme

private sealed class Screen {
    data object Landing : Screen()
    data object RegisterHousehold : Screen()
    data object ViewHouseholds : Screen()
    data class ViewMembers(val household: Household) : Screen()
    data class RegisterMember(
        val household: Household,
        val returnToList: Boolean = false,
        val returnToMembers: Boolean = false
    ) : Screen()
}

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        enableEdgeToEdge()
        setContent {
            RBIMTheme {
                Surface(modifier = Modifier.fillMaxSize()) {
                    var screen by remember { mutableStateOf<Screen>(Screen.Landing) }
                    var listRefreshKey by remember { mutableIntStateOf(0) }

                    fun goAfterMember(current: Screen.RegisterMember) {
                        listRefreshKey++
                        screen = when {
                            current.returnToMembers -> Screen.ViewMembers(current.household)
                            current.returnToList -> Screen.ViewHouseholds
                            else -> Screen.Landing
                        }
                    }

                    when (val current = screen) {
                        is Screen.Landing -> LandingScreen(
                            onRegisterHousehold = {
                                screen = Screen.RegisterHousehold
                            },
                            onViewSavedHousehold = {
                                screen = Screen.ViewHouseholds
                            },
                            onSyncOnline = {
                                Toast.makeText(
                                    this@MainActivity,
                                    "Sync Online — coming soon",
                                    Toast.LENGTH_SHORT
                                ).show()
                            }
                        )

                        is Screen.RegisterHousehold -> RegisterHouseholdScreen(
                            onBack = { screen = Screen.Landing },
                            onSaved = { household ->
                                listRefreshKey++
                                screen = Screen.RegisterMember(
                                    household = household,
                                    returnToList = false
                                )
                            }
                        )

                        is Screen.ViewHouseholds -> ViewHouseholdsScreen(
                            refreshKey = listRefreshKey,
                            onBack = { screen = Screen.Landing },
                            onRegisterHousehold = {
                                screen = Screen.RegisterHousehold
                            },
                            onViewMembers = { household ->
                                screen = Screen.ViewMembers(household)
                            },
                            onAddMember = { household ->
                                screen = Screen.RegisterMember(
                                    household = household,
                                    returnToList = true
                                )
                            }
                        )

                        is Screen.ViewMembers -> ViewMembersScreen(
                            household = current.household,
                            refreshKey = listRefreshKey,
                            onBack = {
                                listRefreshKey++
                                screen = Screen.ViewHouseholds
                            },
                            onAddMember = {
                                screen = Screen.RegisterMember(
                                    household = current.household,
                                    returnToMembers = true
                                )
                            }
                        )

                        is Screen.RegisterMember -> RegisterMemberScreen(
                            household = current.household,
                            onBack = { goAfterMember(current) },
                            onDone = { goAfterMember(current) }
                        )
                    }
                }
            }
        }
    }
}
