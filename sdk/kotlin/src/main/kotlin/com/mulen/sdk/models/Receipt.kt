package com.mulen.sdk.models

import kotlinx.serialization.SerialName
import kotlinx.serialization.Serializable

@Serializable
data class Receipt(
    val id: Int,
    @SerialName("daemon_code")
    val daemonCode: String,
    @SerialName("device_code")
    val deviceCode: String,
    val warnings: List<String>,
    val error: List<String>,
    @SerialName("ecr_registration_number")
    val ecrRegistrationNumber: String,
    @SerialName("fiscal_document_attribute")
    val fiscalDocumentAttribute: Int,
    @SerialName("fiscal_document_number")
    val fiscalDocumentNumber: Int,
    @SerialName("fiscal_receipt_number")
    val fiscalReceiptNumber: Int,
    @SerialName("fn_number")
    val fnNumber: String,
    @SerialName("fns_site")
    val fnsSite: String,
    @SerialName("receipt_datetime")
    val receiptDatetime: String,
    @SerialName("shift_number")
    val shiftNumber: Int,
    val total: Double,
    @SerialName("ofd_inn")
    val ofdInn: String,
    @SerialName("ofd_receipt_url")
    val ofdReceiptUrl: String,
    val status: String,
    val uuid: String,
    @SerialName("created_at")
    val createdAt: String,
    @SerialName("updated_at")
    val updatedAt: String
)

@Serializable
data class ReceiptResponse(
    val status: String,
    val items: List<Receipt> = emptyList(),
    val message: String? = null
)
