package com.mulen.sdk

import com.mulen.sdk.models.PaymentRequest
import com.mulen.sdk.models.PaymentRequestItem
import io.ktor.client.*
import io.ktor.client.engine.mock.*
import io.ktor.client.plugins.contentnegotiation.*
import io.ktor.http.*
import io.ktor.serialization.kotlinx.json.*
import kotlinx.coroutines.test.runTest
import kotlinx.serialization.json.Json
import kotlin.test.Test
import kotlin.test.assertEquals

class ApiClientTest {
    private val apiKey = "test_api_key"
    private val secretKey = "test_secret_key"

    private fun createMockClient(expectedContent: String, expectedStatus: HttpStatusCode): HttpClient {
        val mockEngine = MockEngine {
            respond(
                content = expectedContent,
                status = expectedStatus,
                headers = headersOf(HttpHeaders.ContentType, "application/json")
            )
        }
        return HttpClient(mockEngine) {
            install(ContentNegotiation) {
                json(Json {
                    ignoreUnknownKeys = true
                })
            }
        }
    }

    @Test
    fun `test create payment success`() = runTest {
        val responseContent = """
            {
                "success": true,
                "paymentUrl": "https://mulenpay.ru/",
                "id": 1
            }
        """.trimIndent()
        val mockHttpClient = createMockClient(responseContent, HttpStatusCode.Created)
        val apiClient = ApiClient(apiKey, secretKey)
        
        val newApiClient = apiClient.apply {
            javaClass.getDeclaredField("httpClient").apply {
                isAccessible = true
                set(apiClient, mockHttpClient)
            }
        }

        val paymentRequest = PaymentRequest(
            currency = "rub",
            amount = "1000.50",
            uuid = "invoice_123",
            shopId = 5,
            description = "Покупка булочек",
            items = listOf(
                PaymentRequestItem(
                    description = "Булочка",
                    quantity = 1.0,
                    price = 1000.50,
                    vatCode = 0,
                    paymentSubject = 1,
                    paymentMode = 1
                )
            ),
            sign = ""
        )

        val response = newApiClient.createPayment(paymentRequest)

        assertEquals(true, response.success)
        assertEquals("https://mulenpay.ru/", response.paymentUrl)
        assertEquals(1, response.id)
    }

    @Test
    fun `test get payments success`() = runTest {
        val responseContent = """
            {
                "items": [
                    {
                        "id": 1,
                        "uuid": "invoice_123",
                        "amount": "1000.50",
                        "currency": "rub",
                        "description": "Покупка булочек",
                        "status": 3
                    }
                ]
            }
        """.trimIndent()
        val mockHttpClient = createMockClient(responseContent, HttpStatusCode.OK)
        val apiClient = ApiClient(apiKey, secretKey)

        val newApiClient = apiClient.apply {
            javaClass.getDeclaredField("httpClient").apply {
                isAccessible = true
                set(apiClient, mockHttpClient)
            }
        }

        val response = newApiClient.getPayments()

        assertEquals(1, response.items.size)
        assertEquals(1, response.items[0].id)
    }

    @Test
    fun `test get payment by id success`() = runTest {
        val responseContent = """
            {
                "success": true,
                "payment": {
                    "id": 1,
                    "uuid": "invoice_123",
                    "amount": "1000.50",
                    "currency": "rub",
                    "description": "Покупка булочек",
                    "status": 3
                }
            }
        """.trimIndent()
        val mockHttpClient = createMockClient(responseContent, HttpStatusCode.OK)
        val apiClient = ApiClient(apiKey, secretKey)

        val newApiClient = apiClient.apply {
            javaClass.getDeclaredField("httpClient").apply {
                isAccessible = true
                set(apiClient, mockHttpClient)
            }
        }

        val response = newApiClient.getPayment(1)

        assertEquals(true, response.success)
        assertEquals(1, response.payment.id)
    }
}
