package com.mulen.sdk.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class Subscribe(
    val id: Int,
    val description: String,
    val status: Int,
    val currency: String,
    val amount: String,
    @SerialName("start_date")
    val startDate: String,
    @SerialName("next_pay_date")
    val nextPayDate: String,
    val interval: String
)

@Serializable
data class SubscribesListResponse(
    val items: List<Subscribe>
)
