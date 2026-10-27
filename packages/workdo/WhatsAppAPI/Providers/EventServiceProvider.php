<?php

namespace Modules\WhatsAppAPI\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as Provider;
use Modules\WhatsAppAPI\Listeners\CompanyPaymentLis;
use Modules\WhatsAppAPI\Listeners\CreatePaymentInvoiceLis;
use Modules\SSPay\Events\SSpayPaymentStatus;
use Modules\Paypal\Events\PaypalPaymentStatus;
use Modules\PayTab\Events\PaytabPaymentStatus;
use Modules\Coingate\Events\CoingatePaymentStatus;
use Modules\Paytm\Events\PaytmPaymentStatus;
use Modules\Mercado\Events\MercadoPaymentStatus;
use Modules\Flutterwave\Events\FlutterwavePaymentStatus;
use Modules\Payfast\Events\PayfastPaymentStatus;
use Modules\Stripe\Events\StripePaymentStatus;
use Modules\Mollie\Events\MolliePaymentStatus;
use Modules\Paystack\Events\PaystackPaymentStatus;
use Modules\Razorpay\Events\RazorpayPaymentStatus;
use Modules\Skrill\Events\SkrillPaymentStatus;
use Modules\Iyzipay\Events\IyzipayPaymentStatus;
use Modules\Toyyibpay\Events\ToyyibpayPaymentStatus;
use Modules\YooKassa\Events\YooKassaPaymentStatus;
use Modules\PayTR\Events\PaytrPaymentStatus;
use Modules\AamarPay\Events\AamarPaymentStatus;
use App\Events\CreatePaymentInvoice;
use App\Events\SentInvoice;
use App\Events\PaymentDestroyInvoice;


class EventServiceProvider extends Provider
{
    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */

    protected $listen = [
        SSpayPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PaypalPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PaytabPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        CoingatePaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PaytmPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        MercadoPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        FlutterwavePaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PayfastPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        StripePaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        MolliePaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PaystackPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        RazorpayPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        SkrillPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        IyzipayPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        ToyyibpayPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        YooKassaPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        PaytrPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        AamarPaymentStatus::class => [
            CompanyPaymentLis::class
        ],
        CreatePaymentInvoice::class => [
            CreatePaymentInvoiceLis::class
        ],
        SentInvoice::class => [
            CreatePaymentInvoiceLis::class
        ],
        PaymentDestroyInvoice::class => [
            CreatePaymentInvoiceLis::class
        ],

    ];
    public function shouldDiscoverEvents()
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array
     */
    protected function discoverEventsWithin()
    {
        return [
            __DIR__ . '/../Listeners',
        ];
    }
}
