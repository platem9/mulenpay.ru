package com.mulen.sdk

import com.mulen.sdk.models.*
import io.ktor.client.*
import io.ktor.client.call.*
import io.ktor.client.engine.cio.*
import io.ktor.client.plugins.contentnegotiation.*
import io.ktor.client.request.*
import io.ktor.http.*
import io.ktor.serialization.kotlinx.json.*
import kotlinx.serialization.json.Json
import java.security.MessageDigest

class ApiClient(private val apiKey: String, private val secretKey: String) {
    private val httpClient = HttpClient(CIO) {
        install(ContentNegotiation) {
            json(Json {
                ignoreUnknownKeys = true
                prettyPrint = true
            })
        }
    }

    private val baseUrl = "https://mulenpay.ru/api"

    // Payments
    suspend fun createPayment(request: PaymentRequest): PaymentCreateResponse {
        val signedRequest = request.copy(sign = generateSignature(
            request.currency,
            request.amount.toString(),
            request.shopId.toString()
        ))
        return httpClient.post("$baseUrl/v2/payments") {
            contentType(ContentType.Application.Json)
            header("Authorization", "Bearer $apiKey")
            setBody(signedRequest)
        }.body()
    }

    suspend fun getPayments(): PaymentsListResponse {
        return httpClient.get("$baseUrl/v2/payments") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    suspend fun getPayment(id: Int): PaymentInfoResponse {
        return httpClient.get("$baseUrl/v2/payments/$id") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    suspend fun confirmPaymentHold(id: Int): SimpleSuccessResponse {
        return httpClient.put("$baseUrl/v2/payments/$id/hold") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    suspend fun cancelPaymentHold(id: Int): SimpleSuccessResponse {
        return httpClient.delete("$baseUrl/v2/payments/$id/hold") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    suspend fun refundPayment(id: Int): SimpleSuccessResponse {
        return httpClient.put("$baseUrl/v2/payments/$id/refund") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    // Receipts
    suspend fun getReceipt(id: Int): ReceiptResponse {
        return httpClient.get("$baseUrl/v2/payments/$id/receipt") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    // Subscriptions
    suspend fun getSubscriptions(): SubscribesListResponse {
        return httpClient.get("$baseUrl/v2/subscribes") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    suspend fun cancelSubscription(id: Int): SimpleSuccessResponse {
        return httpClient.delete("$baseUrl/v2/subscribes/$id") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    // Balances
    suspend fun getShopBalances(shopId: Int): BalancesResponse {
        return httpClient.get("$baseUrl/v2/shops/$shopId/balances") {
            header("Authorization", "Bearer $apiKey")
        }.body()
    }

    private fun generateSignature(currency: String, amount: String, shopId: String): String {
        val data = "$currency$amount$shopId$secretKey"
        val sha1 = MessageDigest.getInstance("SHA-1")
        return sha1.digest(data.toByteArray()).joinToString("") {
            "%02x".format(it)
        }
    }
}
