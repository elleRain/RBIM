package com.example.rbim.data.repository

import android.content.Context
import com.example.rbim.data.model.Household
import com.example.rbim.data.model.HouseholdMember
import org.json.JSONArray
import org.json.JSONObject
import java.io.File
import java.text.SimpleDateFormat
import java.util.Date
import java.util.Locale
import java.util.UUID

class HouseholdRepository(context: Context) {
    private val householdsFile = File(context.filesDir, "households.json")
    private val membersFile = File(context.filesDir, "members.json")

    fun getHouseholds(): List<Household> {
        if (!householdsFile.exists()) return emptyList()
        val array = JSONArray(householdsFile.readText())
        return (0 until array.length()).map { householdFromJson(array.getJSONObject(it)) }
    }

    fun getHousehold(localId: String): Household? =
        getHouseholds().find { it.localId == localId }

    fun getMembers(householdLocalId: String): List<HouseholdMember> {
        if (!membersFile.exists()) return emptyList()
        val array = JSONArray(membersFile.readText())
        return (0 until array.length())
            .map { memberFromJson(array.getJSONObject(it)) }
            .filter { it.householdLocalId == householdLocalId }
    }

    fun nextHouseholdNumber(): Int {
        val yearMonth = SimpleDateFormat("yyMM", Locale.US).format(Date())
        val prefix = yearMonth.toInt()
        val maxCounter = getHouseholds()
            .map { it.householdNumber }
            .filter { it / 100 == prefix }
            .maxOfOrNull { it % 100 } ?: 0
        return prefix * 100 + (maxCounter + 1)
    }

    fun saveHousehold(household: Household): Household {
        val list = getHouseholds().toMutableList()
        val index = list.indexOfFirst { it.localId == household.localId }
        if (index >= 0) list[index] = household else list.add(0, household)
        writeHouseholds(list)
        return household
    }

    fun createHousehold(
        address: String,
        barangay: String,
        respondentName: String,
        householdHead: String,
        totalMembers: Int,
        visitNumber: Int,
        visitDate: String,
        startTime: String,
        endTime: String,
        result: String,
        nextVisitDate: String,
        interviewerName: String,
        supervisorName: String,
        encodingSupervisor: String
    ): Household {
        val today = SimpleDateFormat("yyyy-MM-dd", Locale.US).format(Date())
        val household = Household(
            localId = UUID.randomUUID().toString(),
            householdNumber = nextHouseholdNumber(),
            barangay = barangay,
            address = address.trim(),
            respondentName = respondentName.trim(),
            householdHead = householdHead.trim(),
            totalMembers = totalMembers,
            visitNumber = visitNumber,
            visitDate = visitDate,
            startTime = startTime,
            endTime = endTime,
            result = result,
            nextVisitDate = nextVisitDate,
            interviewerName = interviewerName.trim(),
            supervisorName = supervisorName.trim(),
            dateEncoded = today,
            encodingSupervisor = encodingSupervisor.trim()
        )
        return saveHousehold(household)
    }

    fun saveMember(member: HouseholdMember): HouseholdMember {
        val list = getAllMembers().toMutableList()
        val index = list.indexOfFirst { it.localId == member.localId }
        if (index >= 0) list[index] = member else list.add(0, member)
        writeMembers(list)
        return member
    }

    fun createMember(member: HouseholdMember): HouseholdMember {
        val saved = saveMember(
            member.copy(localId = member.localId.ifBlank { UUID.randomUUID().toString() })
        )
        // Keep household total_members in sync with registered member count
        getHousehold(member.householdLocalId)?.let { household ->
            val count = getMembers(household.localId).size
            if (household.totalMembers != count) {
                saveHousehold(household.copy(totalMembers = count))
            }
        }
        return saved
    }

    private fun getAllMembers(): List<HouseholdMember> {
        if (!membersFile.exists()) return emptyList()
        val array = JSONArray(membersFile.readText())
        return (0 until array.length()).map { memberFromJson(array.getJSONObject(it)) }
    }

    private fun writeHouseholds(list: List<Household>) {
        val array = JSONArray()
        list.forEach { array.put(householdToJson(it)) }
        householdsFile.writeText(array.toString())
    }

    private fun writeMembers(list: List<HouseholdMember>) {
        val array = JSONArray()
        list.forEach { array.put(memberToJson(it)) }
        membersFile.writeText(array.toString())
    }

    private fun householdToJson(h: Household) = JSONObject().apply {
        put("localId", h.localId)
        put("householdNumber", h.householdNumber)
        put("province", h.province)
        put("cityMunicipality", h.cityMunicipality)
        put("barangay", h.barangay)
        put("barangayId", h.barangayId)
        put("address", h.address)
        put("respondentName", h.respondentName)
        put("householdHead", h.householdHead)
        put("totalMembers", h.totalMembers)
        put("visitNumber", h.visitNumber)
        put("visitDate", h.visitDate)
        put("startTime", h.startTime)
        put("endTime", h.endTime)
        put("result", h.result)
        put("nextVisitDate", h.nextVisitDate)
        put("interviewerName", h.interviewerName)
        put("supervisorName", h.supervisorName)
        put("dateEncoded", h.dateEncoded)
        put("encoderName", h.encoderName)
        put("encodingSupervisor", h.encodingSupervisor)
        put("synced", h.synced)
    }

    private fun householdFromJson(o: JSONObject) = Household(
        localId = o.getString("localId"),
        householdNumber = o.getInt("householdNumber"),
        province = o.optString("province", "Negros Occidental"),
        cityMunicipality = o.optString("cityMunicipality", "Bago City"),
        barangay = o.optString("barangay"),
        barangayId = o.optInt("barangayId"),
        address = o.optString("address"),
        respondentName = o.optString("respondentName"),
        householdHead = o.optString("householdHead"),
        totalMembers = o.optInt("totalMembers", 1),
        visitNumber = o.optInt("visitNumber", 1),
        visitDate = o.optString("visitDate"),
        startTime = o.optString("startTime"),
        endTime = o.optString("endTime"),
        result = o.optString("result"),
        nextVisitDate = o.optString("nextVisitDate"),
        interviewerName = o.optString("interviewerName"),
        supervisorName = o.optString("supervisorName"),
        dateEncoded = o.optString("dateEncoded"),
        encoderName = o.optString("encoderName", "Mobile User"),
        encodingSupervisor = o.optString("encodingSupervisor"),
        synced = o.optBoolean("synced", false)
    )

    private fun memberToJson(m: HouseholdMember) = JSONObject().apply {
        put("localId", m.localId)
        put("householdLocalId", m.householdLocalId)
        put("householdNumber", m.householdNumber)
        put("name", m.name)
        put("relationship", m.relationship)
        put("sex", m.sex)
        put("age", m.age)
        put("dateOfBirth", m.dateOfBirth)
        put("placeOfBirth", m.placeOfBirth)
        put("nationality", m.nationality)
        put("maritalStatus", m.maritalStatus)
        put("religion", m.religion)
        put("ethnicity", m.ethnicity)
        put("highestEducation", m.highestEducation)
        put("currentlyEnrolled", m.currentlyEnrolled)
        put("schoolLevel", m.schoolLevel)
        put("placeOfSchool", m.placeOfSchool)
        put("monthlyIncome", m.monthlyIncome)
        put("sourceOfIncome", m.sourceOfIncome)
        put("occupation", m.occupation)
        put("workStatus", m.workStatus)
        put("placeOfWork", m.placeOfWork)
        put("placeOfDelivery", m.placeOfDelivery)
        put("birthAttendant", m.birthAttendant)
        put("immunization", m.immunization)
        put("livingChildren", m.livingChildren)
        put("fpMethod", m.fpMethod)
        put("sourceOfFp", m.sourceOfFp)
        put("intentionToUseFp", m.intentionToUseFp)
        put("healthInsurance", m.healthInsurance)
        put("nutritionalStatus", m.nutritionalStatus)
        put("facilityVisited", m.facilityVisited)
        put("reasonForVisit", m.reasonForVisit)
        put("disability", m.disability)
        put("soloParent", m.soloParent)
        put("registeredSenior", m.registeredSenior)
        put("registeredVoter", m.registeredVoter)
        put("previousResidence5yrs", m.previousResidence5yrs)
        put("previousResidence", m.previousResidence)
        put("lengthOfStay", m.lengthOfStay)
        put("typeOfResident", m.typeOfResident)
        put("dateOfTransfer", m.dateOfTransfer)
        put("reasonLeaving1", m.reasonLeaving1)
        put("reasonLeaving2", m.reasonLeaving2)
        put("reasonLeaving3", m.reasonLeaving3)
        put("returnPreviousResidence", m.returnPreviousResidence)
        put("reasonTransferring1", m.reasonTransferring1)
        put("reasonTransferring2", m.reasonTransferring2)
        put("reasonTransferring3", m.reasonTransferring3)
        put("durationOfStay", m.durationOfStay)
        put("ctcPossession", m.ctcPossession)
        put("ctcIssuedInBarangay", m.ctcIssuedInBarangay)
        put("skillsTrainingInterest", m.skillsTrainingInterest)
        put("skills", m.skills)
        put("housingTenure", m.housingTenure)
        put("lotTenure", m.lotTenure)
        put("fuelForLighting", m.fuelForLighting)
        put("fuelForCooking", m.fuelForCooking)
        put("drinkingWaterSource", m.drinkingWaterSource)
        put("garbageDisposalMethod", m.garbageDisposalMethod)
        put("garbageSegregation", m.garbageSegregation)
        put("toiletFacility", m.toiletFacility)
        put("buildingType", m.buildingType)
        put("outerWallMaterial", m.outerWallMaterial)
        put("femaleDeathDetails", m.femaleDeathDetails)
        put("childDeathDetails", m.childDeathDetails)
        put("commonDiseases", m.commonDiseases)
        put("primaryNeeds", m.primaryNeeds)
        put("futureResidence", m.futureResidence)
        put("hungerExperience", m.hungerExperience)
        put("cannotRead", m.cannotRead)
        put("evacuationCenter", m.evacuationCenter)
        put("emergencyKit", m.emergencyKit)
        put("synced", m.synced)
    }

    private fun memberFromJson(o: JSONObject) = HouseholdMember(
        localId = o.getString("localId"),
        householdLocalId = o.getString("householdLocalId"),
        householdNumber = o.getInt("householdNumber"),
        name = o.optString("name"),
        relationship = o.optString("relationship"),
        sex = o.optInt("sex"),
        age = o.optInt("age"),
        dateOfBirth = o.optString("dateOfBirth"),
        placeOfBirth = o.optString("placeOfBirth"),
        nationality = o.optString("nationality", "Filipino"),
        maritalStatus = o.optString("maritalStatus"),
        religion = o.optString("religion"),
        ethnicity = o.optString("ethnicity"),
        highestEducation = o.optString("highestEducation"),
        currentlyEnrolled = o.optString("currentlyEnrolled"),
        schoolLevel = o.optString("schoolLevel"),
        placeOfSchool = o.optString("placeOfSchool"),
        monthlyIncome = o.optString("monthlyIncome"),
        sourceOfIncome = o.optString("sourceOfIncome"),
        occupation = o.optString("occupation"),
        workStatus = o.optString("workStatus"),
        placeOfWork = o.optString("placeOfWork"),
        placeOfDelivery = o.optString("placeOfDelivery"),
        birthAttendant = o.optString("birthAttendant"),
        immunization = o.optString("immunization"),
        livingChildren = o.optString("livingChildren"),
        fpMethod = o.optString("fpMethod"),
        sourceOfFp = o.optString("sourceOfFp"),
        intentionToUseFp = o.optString("intentionToUseFp"),
        healthInsurance = o.optString("healthInsurance"),
        nutritionalStatus = o.optString("nutritionalStatus"),
        facilityVisited = o.optString("facilityVisited"),
        reasonForVisit = o.optString("reasonForVisit"),
        disability = o.optString("disability"),
        soloParent = o.optString("soloParent"),
        registeredSenior = o.optString("registeredSenior"),
        registeredVoter = o.optString("registeredVoter"),
        previousResidence5yrs = o.optString("previousResidence5yrs"),
        previousResidence = o.optString("previousResidence"),
        lengthOfStay = o.optString("lengthOfStay"),
        typeOfResident = o.optString("typeOfResident"),
        dateOfTransfer = o.optString("dateOfTransfer"),
        reasonLeaving1 = o.optString("reasonLeaving1"),
        reasonLeaving2 = o.optString("reasonLeaving2"),
        reasonLeaving3 = o.optString("reasonLeaving3"),
        returnPreviousResidence = o.optString("returnPreviousResidence"),
        reasonTransferring1 = o.optString("reasonTransferring1"),
        reasonTransferring2 = o.optString("reasonTransferring2"),
        reasonTransferring3 = o.optString("reasonTransferring3"),
        durationOfStay = o.optString("durationOfStay"),
        ctcPossession = o.optString("ctcPossession"),
        ctcIssuedInBarangay = o.optString("ctcIssuedInBarangay"),
        skillsTrainingInterest = o.optString("skillsTrainingInterest"),
        skills = o.optString("skills"),
        housingTenure = o.optString("housingTenure"),
        lotTenure = o.optString("lotTenure"),
        fuelForLighting = o.optString("fuelForLighting"),
        fuelForCooking = o.optString("fuelForCooking"),
        drinkingWaterSource = o.optString("drinkingWaterSource"),
        garbageDisposalMethod = o.optString("garbageDisposalMethod"),
        garbageSegregation = o.optString("garbageSegregation"),
        toiletFacility = o.optString("toiletFacility"),
        buildingType = o.optString("buildingType"),
        outerWallMaterial = o.optString("outerWallMaterial"),
        femaleDeathDetails = o.optString("femaleDeathDetails"),
        childDeathDetails = o.optString("childDeathDetails"),
        commonDiseases = o.optString("commonDiseases"),
        primaryNeeds = o.optString("primaryNeeds"),
        futureResidence = o.optString("futureResidence"),
        hungerExperience = o.optString("hungerExperience"),
        cannotRead = o.optString("cannotRead"),
        evacuationCenter = o.optString("evacuationCenter"),
        emergencyKit = o.optString("emergencyKit"),
        synced = o.optBoolean("synced", false)
    )
}
