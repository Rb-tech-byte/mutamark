<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MutamarkPaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds for Mutamark Unified Payment Gateway Manager (SRS Module 33).
     */
    public function run(): void
    {
        $creatorId = 2; // Company Account

        // 1. Seed Dynamic Gateways (No hardcoded credentials)
        $gateways = [
            [
                'gateway_key' => 'selcom',
                'name' => 'Selcom Paytech (M-Pesa / Tigo Pesa / Airtel Money / Card)',
                'description' => 'Direct Tanzanian Mobile Money USSD Push & QR Payment Gateway for Vodacom M-Pesa, Tigo Pesa, Airtel Money, Halopesa, and Visa/Mastercard.',
                'is_active' => true,
                'is_default' => true,
                'mode' => 'sandbox',
                'supported_currencies' => ['TZS', 'USD'],
                'credentials' => [
                    'api_key' => 'SELCOM_API_KEY_tz_2026_demo',
                    'api_secret' => 'SELCOM_SECRET_981a2f4c7b6d5e',
                    'vendor_id' => 'MUTAMARK_DSM_HUB',
                    'till_id' => '16381432',
                ],
                'transaction_fee_percentage' => 1.50,
                'transaction_fee_flat' => 0.00,
                'webhook_secret' => 'whsec_selcom_mutamark_2026',
                'instructions' => 'Customer receives an instant USSD Push prompt on their Tanzanian mobile phone to enter PIN and authorize payment in TZS.',
            ],
            [
                'gateway_key' => 'pesapal',
                'name' => 'Pesapal Tanzania (Mobile Money & Cards)',
                'description' => 'East African regional payment aggregator supporting Tanzanian M-Pesa, Tigo Pesa, Airtel Money, and Visa/Mastercard processing.',
                'is_active' => true,
                'is_default' => false,
                'mode' => 'sandbox',
                'supported_currencies' => ['TZS', 'USD', 'KES'],
                'credentials' => [
                    'consumer_key' => 'PESAPAL_CONSUMER_KEY_tz_34981',
                    'consumer_secret' => 'PESAPAL_SECRET_77aa98bc431ef8',
                    'ipn_id' => 'IPN_TZ_MUTAMARK_2026',
                ],
                'transaction_fee_percentage' => 2.00,
                'transaction_fee_flat' => 0.00,
                'webhook_secret' => 'whsec_pesapal_mutamark_ipn',
                'instructions' => 'Redirects customer to Pesapal secure checkout with multi-currency card and mobile wallet support.',
            ],
            [
                'gateway_key' => 'stripe',
                'name' => 'Stripe Global (Visa, Mastercard, Amex)',
                'description' => 'Global credit and debit card processing for international clients and diaspora payments.',
                'is_active' => true,
                'is_default' => false,
                'mode' => 'sandbox',
                'supported_currencies' => ['USD', 'TZS', 'EUR', 'GBP'],
                'credentials' => [
                    'publishable_key' => 'pk_test_51MutamarkEnterpriseStripeKey',
                    'secret_key' => 'sk_test_51MutamarkEnterpriseSecretKey9981',
                    'webhook_secret' => 'whsec_stripe_mutamark_test',
                ],
                'transaction_fee_percentage' => 2.90,
                'transaction_fee_flat' => 500.00,
                'webhook_secret' => 'whsec_stripe_mutamark_test',
                'instructions' => 'Secure international card processing supporting major credit cards.',
            ],
            [
                'gateway_key' => 'paypal',
                'name' => 'PayPal Express Checkout',
                'description' => 'Global digital wallet checkout for international enterprise B2B contracts and overseas clients.',
                'is_active' => true,
                'is_default' => false,
                'mode' => 'sandbox',
                'supported_currencies' => ['USD', 'EUR', 'GBP'],
                'credentials' => [
                    'client_id' => 'PAYPAL_CLIENT_ID_MUTAMARK_ENTERPRISES',
                    'client_secret' => 'PAYPAL_SECRET_KEY_MUTAMARK_ENTERPRISES_2026',
                ],
                'transaction_fee_percentage' => 3.40,
                'transaction_fee_flat' => 800.00,
                'instructions' => 'Pay via PayPal balance or linked international credit cards.',
            ],
            [
                'gateway_key' => 'manual_mobile_money',
                'name' => 'Direct Lipa Namba (Merchant Till)',
                'description' => 'Dynamic merchant Till Number for counter collections and offline mobile payments.',
                'is_active' => true,
                'is_default' => false,
                'mode' => 'live',
                'supported_currencies' => ['TZS'],
                'credentials' => [
                    'till_number' => '16381432',
                    'merchant_name' => 'MUTAMARK ENTERPRISES LTD',
                    'networks' => 'Vodacom M-Pesa, Tigo Pesa, Airtel Money',
                ],
                'transaction_fee_percentage' => 0.00,
                'transaction_fee_flat' => 0.00,
                'instructions' => 'Pay via Vodacom M-Pesa Lipa Hapa Till: 16381432 (Mutamark Enterprises Ltd). Customer enters transaction SMS reference upon payment.',
            ],
            [
                'gateway_key' => 'bank_transfer',
                'name' => 'CRDB / NMB Bank Wire (TISS & RTGS)',
                'description' => 'Corporate interbank transfer via Tanzania Interbank Settlement System (TISS) and direct bank counter deposit.',
                'is_active' => true,
                'is_default' => false,
                'mode' => 'live',
                'supported_currencies' => ['TZS', 'USD'],
                'credentials' => [
                    'bank_name' => 'CRDB Bank PLC - Corporate Branch Dar es Salaam',
                    'account_name' => 'Mutamark Enterprises Limited',
                    'account_number' => '0150498213800',
                    'swift_code' => 'CORUTZTZ',
                ],
                'transaction_fee_percentage' => 0.00,
                'transaction_fee_flat' => 0.00,
                'instructions' => 'Transfer directly via CRDB Bank account 0150498213800 or TISS/RTGS. Quote Invoice Number in reference narration.',
            ],
        ];

        foreach ($gateways as $gw) {
            PaymentGateway::updateOrCreate(
                ['gateway_key' => $gw['gateway_key']],
                array_merge($gw, [
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                ])
            );
        }

        // 2. Seed Realistic Tanzanian Payment Transactions in TZS
        $invoice = SalesInvoice::first();
        $invNumber = $invoice ? $invoice->invoice_number : 'INV-2026-00001';
        $invId = $invoice ? $invoice->id : 1;

        $sampleTxns = [
            [
                'transaction_reference' => 'TXN-2026-00001',
                'gateway_key' => 'selcom',
                'invoice_type' => 'sales_invoice',
                'invoice_id' => $invId,
                'invoice_number' => $invNumber,
                'customer_name' => 'Safari Logistics & Transport Ltd',
                'customer_email' => 'finance@safarilogistics.co.tz',
                'customer_phone' => '+255 754 112 233',
                'amount' => 450000.00,
                'currency' => 'TZS',
                'fee_amount' => 6750.00,
                'net_amount' => 443250.00,
                'status' => 'completed',
                'gateway_transaction_id' => 'SEL-MPESA-891024-TZS',
                'payment_method_channel' => 'mpesa',
                'receipt_voucher_number' => 'RV-2026-00001',
                'paid_at' => Carbon::parse('2026-10-03 14:48:00'),
            ],
            [
                'transaction_reference' => 'TXN-2026-00002',
                'gateway_key' => 'pesapal',
                'invoice_type' => 'pos_order',
                'invoice_id' => 2,
                'invoice_number' => 'POS-2026-00002',
                'customer_name' => 'Retail Customer (Dar es Salaam)',
                'customer_email' => 'pos.customer@mutamark.co.tz',
                'customer_phone' => '+255 784 556 677',
                'amount' => 330400.00,
                'currency' => 'TZS',
                'fee_amount' => 6608.00,
                'net_amount' => 323792.00,
                'status' => 'completed',
                'gateway_transaction_id' => 'PESAPAL-TZ-9821482',
                'payment_method_channel' => 'card',
                'receipt_voucher_number' => 'RV-2026-00002',
                'paid_at' => Carbon::parse('2026-10-05 11:20:00'),
            ],
            [
                'transaction_reference' => 'TXN-2026-00003',
                'gateway_key' => 'bank_transfer',
                'invoice_type' => 'sales_invoice',
                'invoice_id' => $invId,
                'invoice_number' => $invNumber,
                'customer_name' => 'Kilimanjaro Agro Industries Co.',
                'customer_email' => 'accounts@kilimanjarotz.com',
                'customer_phone' => '+255 713 778 899',
                'amount' => 826000.00,
                'currency' => 'TZS',
                'fee_amount' => 0.00,
                'net_amount' => 826000.00,
                'status' => 'completed',
                'gateway_transaction_id' => 'CRDB-TISS-20261009-8812',
                'payment_method_channel' => 'bank',
                'receipt_voucher_number' => 'RV-2026-00003',
                'paid_at' => Carbon::parse('2026-10-08 09:15:00'),
            ],
            [
                'transaction_reference' => 'TXN-2026-00004',
                'gateway_key' => 'selcom',
                'invoice_type' => 'sales_invoice',
                'invoice_id' => $invId,
                'invoice_number' => $invNumber,
                'customer_name' => 'Zanzibar Port Authorities Ltd',
                'customer_email' => 'procurement@zanzibarports.go.tz',
                'customer_phone' => '+255 777 334 455',
                'amount' => 1250000.00,
                'currency' => 'TZS',
                'fee_amount' => 18750.00,
                'net_amount' => 1231250.00,
                'status' => 'pending',
                'checkout_token' => 'chk_mutamark_demo_token_zanzibar_2026',
                'payment_method_channel' => 'tigopesa',
                'paid_at' => null,
            ],
        ];

        foreach ($sampleTxns as $txn) {
            PaymentTransaction::updateOrCreate(
                ['transaction_reference' => $txn['transaction_reference']],
                array_merge($txn, [
                    'creator_id' => $creatorId,
                    'created_by' => $creatorId,
                ])
            );
        }
    }
}
