<?php

namespace Workdo\MobileServiceManagement\Database\Seeders;

use Illuminate\Database\Seeder;
use Workdo\MobileServiceManagement\Models\MobileServicePayment;
use Workdo\MobileServiceManagement\Models\MobileServiceRequest;
use Workdo\Account\Models\BankAccount;
use Workdo\ProductService\Models\ProductServiceTax;

class DemoMobileServicePaymentSeeder extends Seeder
{
    public function run($userId): void
    {
        if (MobileServicePayment::where('created_by', $userId)->exists()) {
            return; // Skip seeding if data already exists
        }
        
        if (!empty($userId)) {
            // Get completed service requests that need payment
            $completedRequests = MobileServiceRequest::where('created_by', $userId)
                ->where('is_approve', 1)
                ->where('is_repair_end', 1)
                ->get();
            
            // Get available bank accounts
            $bankAccounts = BankAccount::where('created_by', $userId)->where('is_active', 1)->pluck('id')->toArray();
            
            if ($completedRequests->isEmpty()) {
                return;
            }

            foreach ($completedRequests as $index => $request) {
                // Load the mobile service relationship if not loaded
                $request->load('mobileService');
                
                // Get service price from related mobile service
                $servicePrice = $request->mobileService ? $request->mobileService->sale_price : 100;
                
                // Calculate tax amount from service tax_ids
                $taxAmount = 0;
                if ($request->mobileService && $request->mobileService->tax_ids) {
                    $taxIds = $request->mobileService->tax_ids;
                    if (!empty($taxIds) && is_array($taxIds)) {
                        $taxes = ProductServiceTax::whereIn('id', $taxIds)->get(['rate']);
                        $totalTaxRate = $taxes->sum('rate');
                        $taxAmount = ($servicePrice * $totalTaxRate) / 100;
                    }
                }
                $repairCharge = 20 + ($index % 60); // Fixed repair charge based on index
                $extraCharges = [];
                
                // Add extra charges for some payments based on index
                if ($index % 3 == 0) {
                    $extraCharges = [
                        ['text' => 'Screen Protector', 'price' => 15],
                        ['text' => 'Express Service', 'price' => 25]
                    ];
                }
                
                $extraTotal = collect($extraCharges)->sum('price');
                $totalAmount = $servicePrice + $taxAmount + $repairCharge + $extraTotal;
                
                // Set payment status - create mix of statuses for demo
                if ($index % 5 == 0) {
                    $status = 'cancelled';
                    $request->update(['payment_status' => 2]); // Update service request to cancelled
                } elseif ($index % 3 == 0) {
                    $status = 'cleared';
                    $request->update(['payment_status' => 1]); // Update service request to paid
                } else {
                    $status = 'pending';
                    $request->update(['payment_status' => 0]); // Update service request to unpaid
                }
                
                // Don't update service request status here - it should already be set correctly
                
                MobileServicePayment::create([
                    'payment_number' => 'MSP-2025-01-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'payment_date' => $request->updated_at,
                    'mobile_service_id' => $request->id,
                    'service_price' => $servicePrice,
                    'tax_amount' => $taxAmount,
                    'repair_charge' => $repairCharge,
                    'extra_charges' => $extraCharges,
                    'payment_amount' => $totalAmount,
                    'payment_status' => $status,
                    'bank_account_id' => !empty($bankAccounts) ? $bankAccounts[$index % count($bankAccounts)] : null,
                    'notes' => 'Payment for service request #' . $request->service_id,
                    'creator_id' => $userId,
                    'created_by' => $userId,
                    'created_at' => $request->updated_at,
                    'updated_at' => $request->updated_at,
                ]);
            }
        }
    }
}