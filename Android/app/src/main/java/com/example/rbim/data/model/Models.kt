package com.example.rbim.data.model

data class Household(
    val localId: String,
    val householdNumber: Int,
    val province: String = "Negros Occidental",
    val cityMunicipality: String = "Bago City",
    val barangay: String = "",
    val barangayId: Int = 0,
    val address: String = "",
    val respondentName: String = "",
    val householdHead: String = "",
    val totalMembers: Int = 1,
    val visitNumber: Int = 1,
    val visitDate: String = "",
    val startTime: String = "",
    val endTime: String = "",
    val result: String = "",
    val nextVisitDate: String = "",
    val interviewerName: String = "",
    val supervisorName: String = "",
    val dateEncoded: String = "",
    val encoderName: String = "Mobile User",
    val encodingSupervisor: String = "",
    val synced: Boolean = false
) {
    val displayNumber: String
        get() = "#${householdNumber.toString().padStart(6, '0')}"
}

/**
 * Household member fields aligned with Question Assessment Tool / RBIM-Tool-Updated.docx (Q1–Q62).
 */
data class HouseholdMember(
    val localId: String,
    val householdLocalId: String,
    val householdNumber: Int,
    // Q1–Q10 Demographic
    val name: String = "",
    val relationship: String = "",
    val sex: Int = 0,
    val age: Int = 0,
    val dateOfBirth: String = "",
    val placeOfBirth: String = "",
    val nationality: String = "Filipino",
    val maritalStatus: String = "",
    val religion: String = "",
    val ethnicity: String = "",
    // Q11–Q14 Education
    val highestEducation: String = "",
    val currentlyEnrolled: String = "",
    val schoolLevel: String = "",
    val placeOfSchool: String = "",
    // Q15–Q18 Economic
    val monthlyIncome: String = "",
    val sourceOfIncome: String = "",
    val occupation: String = "",
    val workStatus: String = "",
    val placeOfWork: String = "",
    // Q19–Q25 Maternal & Child
    val placeOfDelivery: String = "",
    val birthAttendant: String = "",
    val immunization: String = "",
    val livingChildren: String = "",
    val fpMethod: String = "",
    val sourceOfFp: String = "",
    val intentionToUseFp: String = "",
    // Q26–Q29 Health
    val healthInsurance: String = "",
    val nutritionalStatus: String = "",
    val facilityVisited: String = "",
    val reasonForVisit: String = "",
    val disability: String = "",
    // Q30–Q44 Socio-Civic & Migration
    val soloParent: String = "",
    val registeredSenior: String = "",
    val registeredVoter: String = "",
    val previousResidence5yrs: String = "",
    val previousResidence: String = "",
    val lengthOfStay: String = "",
    val typeOfResident: String = "",
    val dateOfTransfer: String = "",
    val reasonLeaving1: String = "",
    val reasonLeaving2: String = "",
    val reasonLeaving3: String = "",
    val returnPreviousResidence: String = "",
    val reasonTransferring1: String = "",
    val reasonTransferring2: String = "",
    val reasonTransferring3: String = "",
    val durationOfStay: String = "",
    val ctcPossession: String = "",
    val ctcIssuedInBarangay: String = "",
    val skillsTrainingInterest: String = "",
    val skills: String = "",
    // Q45–Q53 Housing
    val housingTenure: String = "",
    val lotTenure: String = "",
    val fuelForLighting: String = "",
    val fuelForCooking: String = "",
    val drinkingWaterSource: String = "",
    val garbageDisposalMethod: String = "",
    val garbageSegregation: String = "",
    val toiletFacility: String = "",
    val buildingType: String = "",
    val outerWallMaterial: String = "",
    // Q54–Q62 Mortality & Needs
    val femaleDeathDetails: String = "",
    val childDeathDetails: String = "",
    val commonDiseases: String = "",
    val primaryNeeds: String = "",
    val futureResidence: String = "",
    val hungerExperience: String = "",
    val cannotRead: String = "",
    val evacuationCenter: String = "",
    val emergencyKit: String = "",
    val synced: Boolean = false
)

object CensusOptions {
    val visitResults = listOf("Complete", "Incomplete", "Refused", "No Response")

    val relationships = listOf(
        "01" to "Head",
        "02" to "Spouse",
        "03" to "Son",
        "04" to "Daughter",
        "05" to "Stepson",
        "06" to "Stepdaughter",
        "07" to "Son-in-law",
        "08" to "Daughter-in-law",
        "09" to "Grandson",
        "10" to "Granddaughter",
        "11" to "Father",
        "12" to "Mother",
        "13" to "Brother",
        "14" to "Sister",
        "15" to "Uncle",
        "16" to "Aunt",
        "17" to "Nephew",
        "18" to "Niece",
        "19" to "Other relative",
        "20" to "Non-relative",
        "21" to "Boarder",
        "22" to "Domestic helper"
    )

    val sexes = listOf(1 to "Male", 2 to "Female")

    val maritalStatuses = listOf(
        "Single", "Married", "Living-in", "Widowed", "Separated", "Annulled", "Unknown"
    )

    val educationLevels = listOf(
        "No education", "Pre-school", "Elementary level", "Elementary graduate",
        "High school level", "High school graduate", "Junior HS level", "Junior HS graduate",
        "Senior HS level", "Senior HS graduate", "Vocational/Tech", "College level",
        "College graduate", "Post-graduate"
    )

    val enrolledOptions = listOf("Yes, public", "Yes, private", "No")

    val schoolLevels = listOf(
        "Pre-school", "Elementary", "Junior High School", "Senior High School",
        "Vocational/Technical", "College/University"
    )

    val incomeRanges = listOf(
        "Below 5,000", "5,000 - 10,000", "10,001 - 15,000", "15,001 - 20,000",
        "20,001 - 30,000", "30,001 - 40,000", "40,001 - 50,000", "Above 50,000"
    )

    val incomeSources = listOf(
        "Employment", "Business", "Remittance", "Investments", "Others"
    )

    val workStatuses = listOf(
        "Permanent Work", "Casual Work", "Contractual Work",
        "Individually Owned Business", "Shared/Partnership Business", "Corporate Business"
    )

    val healthInsurance = listOf(
        "PhilHealth paying member",
        "PhilHealth dependent of paying member",
        "PhilHealth indigent member",
        "PhilHealth dependent of indigent member",
        "Others / None"
    )

    val healthFacilities = listOf(
        "Government hospital", "RHU/Health center", "Brgy. Health Station",
        "Private hospital", "Private clinic", "Others"
    )

    val visitReasons = listOf(
        "Sick/Injured", "Prenatal/Postnatal", "Gave birth", "Dental", "Medical check-up", "Others"
    )

    val disabilities = listOf("None", "Sensory", "Physical", "Mental", "Others")

    val placeOfDelivery = listOf(
        "Public hospital", "Private hospital", "Brgy. Health Station", "Home", "Others"
    )

    val birthAttendants = listOf("Doctor", "Nurse", "Midwife", "Hilot", "Others")

    val fpMethods = listOf(
        "Female sterilization/Ligation", "Male sterilization/Vasectomy", "IUD",
        "Injectables", "Pills", "Condom", "Natural / Rhythm", "Withdrawal", "None", "Others"
    )

    val fpSources = listOf(
        "Government hospital", "RHU/Health center", "Brgy. Health Station",
        "Private hospital/clinic", "Pharmacy", "Others"
    )

    val fpIntention = listOf("Yes", "No", "Already using", "Not applicable")

    val yesNo = listOf("Yes", "No")

    val yesNoNa = listOf("Yes", "No", "Not applicable")

    val residentTypes = listOf(
        "Non-Migrant (Length of Stay is over 5 years)",
        "Migrant (Length of Stay is between 6 months and 1 day to 5 years)"
    )

    val housingTenure = listOf(
        "Owned/being amortized", "Rented",
        "Rent-free with consent of owner", "Rent-free without consent of owner"
    )

    val drinkingWater = listOf(
        "Lake, river, rain, others", "Dug well", "Unprotected spring", "Protected spring",
        "Peddler", "Tubed/Piped shallow well", "Shared, tubed/piped deep well",
        "Own use, tubed/piped deep well", "Shared, faucet community water system",
        "Own use, faucet community water system", "Bottled water", "Others, please specify"
    )

    val toiletFacilities = listOf(
        "None", "Open pit", "Closed pit",
        "Water-sealed, other depository, shared",
        "Water-sealed, other depository, exclusive",
        "Water-sealed, sewer septic tank, shared",
        "Water-sealed, sewer septic tank, exclusive",
        "Others, specify"
    )

    val buildingTypes = listOf(
        "Single house", "Duplex", "Multi-unit residential (three units or more)",
        "Commercial/industrial/agricultural",
        "Institutional living quarter (hotel, hospital)",
        "Other housing units (boat, cave, others)"
    )

    val wallMaterials = listOf(
        "No walls", "Makeshift/salvaged/improvised materials", "Glass", "Asbestos",
        "Bamboo/Sawali/Cogon/Nipa", "Galvanized iron/aluminum",
        "Half concrete/brick/stone and half wood", "Wood",
        "Concrete/brick/stone", "Others, please specify"
    )
}
