package com.mulen.sdk.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class PaymentRequest(
    val currency: String,
    val amount: String,
    val uuid: String,
    val shopId: Int,
    val description: String,
    @SerialName("website_url")
    val websiteUrl: String? = null,
    val subscribe: String? = null,
    val holdTime: Int? = null,
    val language: String? = null,
    val items: List<PaymentRequestItem>,
    val sign: String
)

@Serializable
data class PaymentRequestItem(
    val description: String,
    val quantity: Double,
    val price: Double,
    @SerialName("vat_code")
    val vatCode: Int,
    @SerialName("payment_subject")
    val paymentSubject: Int,
    @SerialName("payment_mode")
    val paymentMode: Int,
    @SerialName("product_code")
    val productCode: String? = null,
    @SerialName("country_of_origin_code")
    val countryOfOriginCode: String? = null,
    @SerialName("customs_declaration_number")
    val customsDeclarationNumber: String? = null,
    val excise: String? = null,
    @SerialName("measurement_unit")
    val measurementUnit: Int? = null
)

@Serializable
data class Payment(
    val id: Int,
    val uuid: String,
    val amount: String,
    val currency: String,
    val description: String,
    val status: Int
)

@Serializable
data class PaymentCreateResponse(
    val success: Boolean,
    val paymentUrl: String? = null,
    val id: Int? = null,
    val error: String? = null,
    val status: Int? = null
)

@Serializable
data class PaymentsListResponse(
    val items: List<Payment>
)

@Serializable
data class PaymentInfoResponse(
    val success: Boolean,
    val payment: Payment
)

@Serializable
data class SimpleSuccessResponse(
    val success: Boolean,
    val error: String? = null,
    val status: Int? = null
)
