package com.mulen.sdk.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class Balance(
    @SerialName("shop_id")
    val shopId: Int,
    val currency: String,
    val balance: Double,
    val hold: Double,
    val available: Double
)

@Serializable
data class BalancesResponse(
    val success: Boolean,
    val data: BalancesData? = null
)

@Serializable
data class BalancesData(
    val balances: List<Balance>
)
