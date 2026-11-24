package com.example.rbim.data.model

/**
 * Visibility rules for household + member registration.
 */
object FieldVisibility {
    fun showNextVisitDate(result: String): Boolean = result == "Incomplete"

    fun isFemale(sex: String): Boolean = sex == "2"

    fun isNonMigrant(typeOfResident: String): Boolean =
        typeOfResident.startsWith("Non-Migrant")

    fun isMigrant(typeOfResident: String): Boolean =
        typeOfResident.startsWith("Migrant")

    /** Q11 — hide if age 0–4 */
    fun showQ11(age: Int?): Boolean = age == null || age >= 5

    /** Q12–Q14 — hide if age 0–2 */
    fun showQ12to14(age: Int?): Boolean = age == null || age >= 3

    /** Q15–Q18 — hide if age 0–14 */
    fun showQ15to18(age: Int?): Boolean = age == null || age >= 15

    /** Q19–Q21 — hide if age 1 and above (show only for under 1 year) */
    fun showQ19to21(age: Int?): Boolean = age == null || age < 1

    /**
     * Q22–Q25 — hide if age 0–9, or female aged 55+.
     */
    fun showQ22to25(sex: String, age: Int?): Boolean {
        if (age == null) return true
        if (age in 0..9) return false
        if (isFemale(sex) && age >= 55) return false
        return true
    }

    /** Q30 — hide if age 0–9 */
    fun showQ30(age: Int?): Boolean = age == null || age >= 10

    /** Q31 — hide if age 0–59 (seniors 60+) */
    fun showQ31(age: Int?): Boolean = age == null || age >= 60

    /** Q32 — hide if age 0–14 */
    fun showQ32(age: Int?): Boolean = age == null || age >= 15

    /** Q37–Q41 — hide if Non-Migrant */
    fun showMigrationDetailFields(typeOfResident: String): Boolean =
        typeOfResident.isBlank() || isMigrant(typeOfResident)

    /** Q42A–Q42B — hide if age 0–17 */
    fun showQ42(age: Int?): Boolean = age == null || age >= 18

    /** Q43–Q44 — hide if age 0–14 */
    fun showQ43Q44(age: Int?): Boolean = age == null || age >= 15
}
