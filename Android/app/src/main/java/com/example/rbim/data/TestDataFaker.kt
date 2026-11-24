package com.example.rbim.data

import com.example.rbim.data.model.CensusOptions
import java.text.SimpleDateFormat
import java.util.Calendar
import java.util.Locale
import kotlin.random.Random

object TestDataFaker {
    private val firstNames = listOf(
        "Juan", "Maria", "Pedro", "Ana", "Jose", "Rosa", "Carlos", "Sofia",
        "Miguel", "Elena", "Antonio", "Luisa", "Ricardo", "Teresa", "Eduardo"
    )
    private val lastNames = listOf(
        "Santos", "Reyes", "Cruz", "Garcia", "Mendoza", "Torres", "Ramos",
        "Flores", "Gonzales", "Diaz", "Aquino", "Pascual", "Robles", "Bautista"
    )
    private val streets = listOf(
        "Main St.", "Rizal St.", "Mabini St.", "Bonifacio St.", "National Highway",
        "Quezon Ave.", "Magsaysay Blvd.", "Aguinaldo St.", "Laurel St.", "Luna St."
    )
    private val barangays = listOf(
        "Poblacion", "Ma-ao", "Lag-asan", "Sampinit", "Busay", "Ilijan", "Binubuhan"
    )
    private val cities = listOf(
        "Bago City", "Bacolod City", "Silay City", "Talisay City", "Iloilo City", "Cebu City"
    )
    private val religions = listOf(
        "Roman Catholic", "Iglesia ni Cristo", "Born Again Christian", "Baptist", "Islam"
    )
    private val ethnicities = listOf(
        "Ilonggo", "Cebuano", "Tagalog", "Waray", "Ilocano"
    )
    private val occupations = listOf(
        "Farmer", "Teacher", "Driver", "Vendor", "Nurse", "Laborer", "Clerk", "Business owner"
    )
    private val schools = listOf(
        "Bago City National High School", "University of St. La Salle",
        "Carlos Hilado Memorial State University", "Ma-ao Elementary School"
    )

    private fun <T> List<T>.pick(): T = this[Random.nextInt(size)]

    fun randomName(): String {
        val middle = ('A'..'Z').random()
        return "${firstNames.pick()} $middle. ${lastNames.pick()}"
    }

    fun formatDate(calendar: Calendar): String =
        SimpleDateFormat("yyyy-MM-dd", Locale.US).format(calendar.time)

    fun formatTime(hour: Int, minute: Int): String =
        String.format(Locale.US, "%02d:%02d", hour, minute)

    fun daysFromToday(offsetDays: Int): String {
        val cal = Calendar.getInstance().apply { add(Calendar.DAY_OF_YEAR, offsetDays) }
        return formatDate(cal)
    }

    data class HouseholdSample(
        val barangay: String,
        val address: String,
        val respondentName: String,
        val householdHead: String,
        val totalMembers: String,
        val visitNumber: String,
        val visitDate: String,
        val startTime: String,
        val endTime: String,
        val result: String,
        val nextVisitDate: String,
        val interviewerName: String,
        val supervisorName: String,
        val encodingSupervisor: String
    )

    fun householdSample(): HouseholdSample {
        val barangay = barangays.pick()
        val result = when (Random.nextFloat()) {
            in 0f..0.7f -> "Complete"
            in 0.7f..0.9f -> "Incomplete"
            in 0.9f..0.95f -> "Refused"
            else -> "No Response"
        }
        return HouseholdSample(
            barangay = barangay,
            address = "${Random.nextInt(1, 999)} ${streets.pick()}, $barangay",
            respondentName = randomName(),
            householdHead = randomName(),
            totalMembers = Random.nextInt(1, 9).toString(),
            visitNumber = Random.nextInt(1, 4).toString(),
            visitDate = daysFromToday(-Random.nextInt(0, 15)),
            startTime = formatTime(Random.nextInt(8, 11), Random.nextInt(0, 60)),
            endTime = formatTime(Random.nextInt(10, 13), Random.nextInt(0, 60)),
            result = result,
            nextVisitDate = if (result == "Incomplete") daysFromToday(Random.nextInt(1, 8)) else "",
            interviewerName = randomName(),
            supervisorName = randomName(),
            encodingSupervisor = randomName()
        )
    }

    data class MemberSample(
        val name: String,
        val relationship: String,
        val sex: String,
        val age: String,
        val dateOfBirth: String,
        val placeOfBirth: String,
        val nationality: String,
        val maritalStatus: String,
        val religion: String,
        val ethnicity: String,
        val highestEducation: String,
        val currentlyEnrolled: String,
        val schoolLevel: String,
        val placeOfSchool: String,
        val monthlyIncome: String,
        val sourceOfIncome: String,
        val occupation: String,
        val workStatus: String,
        val placeOfWork: String,
        val placeOfDelivery: String,
        val birthAttendant: String,
        val immunization: String,
        val livingChildren: String,
        val fpMethod: String,
        val sourceOfFp: String,
        val intentionToUseFp: String,
        val healthInsurance: String,
        val nutritionalStatus: String,
        val facilityVisited: String,
        val reasonForVisit: String,
        val disability: String,
        val soloParent: String,
        val registeredSenior: String,
        val registeredVoter: String,
        val previousResidence5yrs: String,
        val previousResidence: String,
        val lengthOfStay: String,
        val typeOfResident: String,
        val dateOfTransfer: String,
        val reasonLeaving1: String,
        val reasonLeaving2: String,
        val reasonLeaving3: String,
        val returnPreviousResidence: String,
        val reasonTransferring1: String,
        val reasonTransferring2: String,
        val reasonTransferring3: String,
        val durationOfStay: String,
        val ctcPossession: String,
        val ctcIssuedInBarangay: String,
        val skillsTrainingInterest: String,
        val skills: String,
        val housingTenure: String,
        val lotTenure: String,
        val fuelForLighting: String,
        val fuelForCooking: String,
        val drinkingWaterSource: String,
        val garbageDisposalMethod: String,
        val garbageSegregation: String,
        val toiletFacility: String,
        val buildingType: String,
        val outerWallMaterial: String,
        val femaleDeathDetails: String,
        val childDeathDetails: String,
        val commonDiseases: String,
        val primaryNeeds: String,
        val futureResidence: String,
        val hungerExperience: String,
        val cannotRead: String,
        val evacuationCenter: String,
        val emergencyKit: String
    )

    fun memberSample(): MemberSample {
        val ageYears = Random.nextInt(1, 80)
        val birth = Calendar.getInstance().apply {
            add(Calendar.YEAR, -ageYears)
            set(Calendar.MONTH, Random.nextInt(0, 12))
            set(Calendar.DAY_OF_MONTH, Random.nextInt(1, 28))
        }
        val isMale = Random.nextBoolean()
        val enrolled = when {
            ageYears < 5 -> "No"
            ageYears < 22 && Random.nextFloat() > 0.35f -> listOf("Yes, public", "Yes, private").pick()
            else -> "No"
        }
        val maternal = !isMale && ageYears in 15..49

        return MemberSample(
            name = randomName(),
            relationship = CensusOptions.relationships.pick().first,
            sex = if (isMale) "1" else "2",
            age = ageYears.toString(),
            dateOfBirth = formatDate(birth),
            placeOfBirth = cities.pick(),
            nationality = "Filipino",
            maritalStatus = when {
                ageYears < 18 -> "Single"
                else -> CensusOptions.maritalStatuses.pick()
            },
            religion = religions.pick(),
            ethnicity = ethnicities.pick(),
            highestEducation = CensusOptions.educationLevels.pick(),
            currentlyEnrolled = enrolled,
            schoolLevel = if (enrolled == "No") "" else CensusOptions.schoolLevels.pick(),
            placeOfSchool = if (enrolled == "No") "" else schools.pick(),
            monthlyIncome = if (ageYears < 15) "" else CensusOptions.incomeRanges.pick(),
            sourceOfIncome = if (ageYears < 15) "" else CensusOptions.incomeSources.pick(),
            occupation = if (ageYears < 15) "" else occupations.pick(),
            workStatus = if (ageYears < 15) "" else CensusOptions.workStatuses.pick(),
            placeOfWork = if (ageYears < 15) "" else cities.pick(),
            placeOfDelivery = if (maternal) CensusOptions.placeOfDelivery.pick() else "",
            birthAttendant = if (maternal) CensusOptions.birthAttendants.pick() else "",
            immunization = listOf("Complete", "Incomplete", "None").pick(),
            livingChildren = if (maternal) Random.nextInt(0, 5).toString() else "",
            fpMethod = if (maternal) CensusOptions.fpMethods.pick() else "",
            sourceOfFp = if (maternal) CensusOptions.fpSources.pick() else "",
            intentionToUseFp = if (maternal) CensusOptions.fpIntention.pick() else "",
            healthInsurance = CensusOptions.healthInsurance.pick(),
            nutritionalStatus = listOf("Normal", "Underweight", "Overweight", "Not assessed").pick(),
            facilityVisited = CensusOptions.healthFacilities.pick(),
            reasonForVisit = CensusOptions.visitReasons.pick(),
            disability = CensusOptions.disabilities.pick(),
            soloParent = if (!isMale) CensusOptions.yesNo.pick() else "No",
            registeredSenior = if (ageYears >= 60) CensusOptions.yesNo.pick() else "Not applicable",
            registeredVoter = if (ageYears >= 15) CensusOptions.yesNo.pick() else "Not applicable",
            previousResidence5yrs = "${barangays.pick()}, ${cities.pick()}",
            previousResidence = "${barangays.pick()}, ${cities.pick()}",
            lengthOfStay = "${Random.nextInt(1, 20)} years",
            typeOfResident = CensusOptions.residentTypes.pick(),
            dateOfTransfer = if (ageYears >= 18) "01/${2010 + Random.nextInt(0, 14)}" else "",
            reasonLeaving1 = listOf("Employment", "Marriage", "Education", "Family").pick(),
            reasonLeaving2 = "",
            reasonLeaving3 = "",
            returnPreviousResidence = CensusOptions.yesNo.pick(),
            reasonTransferring1 = listOf("Availability of jobs", "Presence of schools", "Family").pick(),
            reasonTransferring2 = "",
            reasonTransferring3 = "",
            durationOfStay = "${Random.nextInt(6, 60)} months",
            ctcPossession = if (ageYears >= 18) CensusOptions.yesNo.pick() else "Not applicable",
            ctcIssuedInBarangay = if (ageYears >= 18) CensusOptions.yesNo.pick() else "Not applicable",
            skillsTrainingInterest = listOf("Carpentry", "Baking", "Computer Graphics", "None").pick(),
            skills = listOf("Farming", "Cooking", "Driving", "Sewing").pick(),
            housingTenure = CensusOptions.housingTenure.pick(),
            lotTenure = CensusOptions.housingTenure.pick(),
            fuelForLighting = listOf("Electricity", "Kerosene", "Solar").pick(),
            fuelForCooking = listOf("LPG", "Wood", "Charcoal", "Electricity").pick(),
            drinkingWaterSource = CensusOptions.drinkingWater.pick(),
            garbageDisposalMethod = listOf(
                "Collected by Garbage Truck", "Burning", "Composting", "Burying"
            ).pick(),
            garbageSegregation = CensusOptions.yesNo.pick(),
            toiletFacility = CensusOptions.toiletFacilities.pick(),
            buildingType = CensusOptions.buildingTypes.pick(),
            outerWallMaterial = CensusOptions.wallMaterials.pick(),
            femaleDeathDetails = "None",
            childDeathDetails = "None",
            commonDiseases = listOf("None", "Common cold, flu", "Hypertension", "Diabetes").pick(),
            primaryNeeds = listOf(
                "Stable water supply", "Health services", "Livelihood", "Road improvement"
            ).pick(),
            futureResidence = listOf(
                "Stay in current residence", "Move within the barangay", "Move to another city/municipality"
            ).pick(),
            hungerExperience = CensusOptions.yesNo.pick(),
            cannotRead = CensusOptions.yesNo.pick(),
            evacuationCenter = CensusOptions.yesNo.pick(),
            emergencyKit = CensusOptions.yesNo.pick()
        )
    }
}
