package com.example.rbim.data.model

/**
 * Realtime form completion from filled vs applicable fields (Q1–Q62).
 */
object MemberFormProgress {

    private fun MutableList<Boolean>.track(value: String) {
        add(value.isNotBlank())
    }

    private fun MutableList<Boolean>.trackIf(condition: Boolean, value: String) {
        if (condition) add(value.isNotBlank())
    }

    fun calculate(member: HouseholdMember, sexCode: String = member.sex.toString()): Float {
        val age = member.age.takeIf { member.dateOfBirth.isNotBlank() || member.name.isNotBlank() }
        val ageInt = age
        val filled = mutableListOf<Boolean>()

        filled.track(member.name)
        filled.track(member.relationship)
        filled.track(if (sexCode in listOf("1", "2")) "ok" else "")
        filled.track(if (member.age in 0..120 && member.dateOfBirth.isNotBlank()) "ok" else "")
        filled.track(member.dateOfBirth)
        filled.track(member.placeOfBirth)
        filled.track(member.nationality)
        filled.track(member.maritalStatus)
        filled.track(member.religion)
        filled.track(member.ethnicity)

        filled.trackIf(FieldVisibility.showQ11(ageInt), member.highestEducation)
        if (FieldVisibility.showQ12to14(ageInt)) {
            filled.track(member.currentlyEnrolled)
            filled.track(member.schoolLevel)
            filled.track(member.placeOfSchool)
        }

        if (FieldVisibility.showQ15to18(ageInt)) {
            filled.track(member.monthlyIncome)
            filled.track(member.sourceOfIncome)
            filled.track(member.occupation)
            filled.track(member.workStatus)
            filled.track(member.placeOfWork)
        }

        filled.track(member.healthInsurance)
        filled.track(member.nutritionalStatus)
        filled.track(member.facilityVisited)
        filled.track(member.reasonForVisit)
        filled.track(member.disability)
        filled.trackIf(FieldVisibility.showQ19to21(ageInt), member.immunization)

        if (FieldVisibility.showQ19to21(ageInt)) {
            filled.track(member.placeOfDelivery)
            filled.track(member.birthAttendant)
        }
        if (FieldVisibility.showQ22to25(sexCode, ageInt)) {
            filled.track(member.livingChildren)
            filled.track(member.fpMethod)
            filled.track(member.sourceOfFp)
            filled.track(member.intentionToUseFp)
        }

        filled.trackIf(FieldVisibility.showQ30(ageInt), member.soloParent)
        filled.trackIf(FieldVisibility.showQ31(ageInt), member.registeredSenior)
        filled.trackIf(FieldVisibility.showQ32(ageInt), member.registeredVoter)
        filled.track(member.previousResidence5yrs)
        filled.track(member.previousResidence)
        filled.track(member.lengthOfStay)
        filled.track(member.typeOfResident)
        if (FieldVisibility.showMigrationDetailFields(member.typeOfResident)) {
            filled.track(member.dateOfTransfer)
            filled.track(member.reasonLeaving1)
            filled.track(member.reasonLeaving2)
            filled.track(member.reasonLeaving3)
            filled.track(member.returnPreviousResidence)
            filled.track(member.reasonTransferring1)
            filled.track(member.reasonTransferring2)
            filled.track(member.reasonTransferring3)
            filled.track(member.durationOfStay)
        }
        if (FieldVisibility.showQ42(ageInt)) {
            filled.track(member.ctcPossession)
            filled.track(member.ctcIssuedInBarangay)
        }
        if (FieldVisibility.showQ43Q44(ageInt)) {
            filled.track(member.skillsTrainingInterest)
            filled.track(member.skills)
        }

        filled.track(member.housingTenure)
        filled.track(member.lotTenure)
        filled.track(member.fuelForLighting)
        filled.track(member.fuelForCooking)
        filled.track(member.drinkingWaterSource)
        filled.track(member.garbageDisposalMethod)
        filled.track(member.garbageSegregation)
        filled.track(member.toiletFacility)
        filled.track(member.buildingType)
        filled.track(member.outerWallMaterial)

        filled.track(member.femaleDeathDetails)
        filled.track(member.childDeathDetails)
        filled.track(member.commonDiseases)
        filled.track(member.primaryNeeds)
        filled.track(member.futureResidence)
        filled.track(member.hungerExperience)
        filled.track(member.cannotRead)
        filled.track(member.evacuationCenter)
        filled.track(member.emergencyKit)

        if (filled.isEmpty()) return 0f
        return filled.count { it }.toFloat() / filled.size.toFloat()
    }
}
