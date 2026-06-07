<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PersonalStockEntry;
use App\Models\PersonalPaymentReceived;
use App\Models\PersonalReturnInvoice;
use App\Models\PersonalSupplier;
use App\Models\PersonalCustomer;
use App\Models\User;

class PersonalModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Ensure we have an admin user
        if (!User::where('email', 'bscs2312405@szabist.pk')->exists()) {
            User::create([
                'name' => 'ERP Admin',
                'email' => 'bscs2312405@szabist.pk',
                'password' => bcrypt('abcd.1234'),
            ]);
        }

        // Clear existing personal tables to refresh
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        PersonalStockEntry::truncate();
        PersonalPaymentReceived::truncate();
        PersonalReturnInvoice::truncate();
        PersonalSupplier::truncate();
        PersonalCustomer::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. Seed Suppliers
        $suppliers = ['Syed Ahmed', 'Muhammad Shaffan', 'Nageen Pardeep', 'Qamar Zaman', 'Jabbar', 'Sher Khan'];
        foreach ($suppliers as $index => $supplier) {
            PersonalSupplier::create([
                'name' => $supplier,
                'phone' => '0345-' . rand(1000000, 9999999),
                'email' => strtolower(str_replace(' ', '', $supplier)) . '@gmail.com',
                'status' => 'Active',
                'city' => 'Lahore',
                'address' => 'Lorem Ipsum Dolor Sit Amet',
                'opening_balance' => rand(1000, 5000) * 10,
                'notes' => 'Primary supplier of cotton products',
            ]);
        }

        // Seed Supplier Stock Entries
        foreach ($suppliers as $index => $supplier) {
            $entry = PersonalStockEntry::create([
                'supplier_name' => $supplier,
                'container_no' => '2536479', // set invoice to same as Design 1/2
                'serial_no' => '2536479', // set serial to same as Design 1/2
                'date_added' => '2025-05-29', // matching Design 1/2 date
                'notes' => 'Lorem Ipsum Dolor Sit Amet',
            ]);

            // Add small bales
            $entry->items()->create([
                'bale_type' => 'small',
                'no_of_bales' => 2300,
                'item_name' => 'BABY BLANKETS',
                'company' => 'ms',
                'weight' => 2300,
                'rate' => 22000,
            ]);

            // Add big bales
            $entry->items()->create([
                'bale_type' => 'big',
                'no_of_bales' => 2300,
                'item_name' => 'BATH MATE',
                'company' => 'pak',
                'weight' => 2200,
                'rate' => 22000,
            ]);

            // Add extra items to match the Detailed Data of Supplier table
            $entry->items()->create([
                'bale_type' => 'big',
                'no_of_bales' => 2200,
                'item_name' => 'BED COVER',
                'company' => 'ms',
                'weight' => 2200,
                'rate' => 22000,
            ]);

            $entry->items()->create([
                'bale_type' => 'big',
                'no_of_bales' => 2200,
                'item_name' => 'CARPET',
                'company' => 'pak',
                'weight' => 2200,
                'rate' => 22000,
            ]);

            $entry->items()->create([
                'bale_type' => 'small',
                'no_of_bales' => 2200,
                'item_name' => 'ELASTIC BEDSHEET',
                'company' => 'ms',
                'weight' => 2200,
                'rate' => 22000,
            ]);
        }

        // 3. Seed Customers
        $customers = ['Al Barkat Traders', 'Khalid Fabrics', 'Noman Textiles', 'Usman & Sons', 'Hassan Fabrics'];
        foreach ($customers as $index => $customer) {
            PersonalCustomer::create([
                'name' => $customer,
                'phone' => '0345-' . rand(1000000, 9999999),
                'email' => strtolower(str_replace(' ', '', $customer)) . '@gmail.com',
                'status' => 'Active',
                'city' => 'Karachi',
                'address' => 'Lorem Ipsum Dolor Sit Amet',
                'opening_balance' => rand(2000, 10000) * 10,
                'notes' => 'Major distribution customer',
            ]);
        }

        // Seed Customer Payments / Invoices to match Design 3 and 4
        foreach ($customers as $index => $customer) {
            // Seed a payment with items
            $payment = PersonalPaymentReceived::create([
                'invoice_no' => 'SAL-' . (7 + $index),
                'customer_name' => $customer,
                'to_name' => 'MS TRADERS',
                'date_received' => '2025-05-12', // match Design 3 date
                'cash_amount' => 150000,
                'total_amount' => 2860260,
                'paid_amount' => 2860260,
                'due_amount' => 0,
                'description' => 'Lorem Ipsum Dolor Sit Amet',
                'notes' => 'Payment settled.',
            ]);

            // Add cheque record
            $payment->cheques()->create([
                'bank_name' => 'Meezan Bank',
                'check_no' => 'CHQ-748392',
                'due_date' => '2025-05-19',
                'to_name' => 'MS TRADERS',
                'amount' => 80000,
            ]);

            // Add online record
            $payment->onlines()->create([
                'bank_name' => 'Habib Bank',
                'name' => 'Transfer Ref: 894389',
                'payment_date' => '2025-05-12',
                'from_name' => $customer,
                'to_name' => 'MS TRADERS',
                'amount' => 70000,
            ]);
        }

        // 4. Dummy Return Invoices (Payment Sent)
        foreach ($customers as $index => $customer) {
            $return = PersonalReturnInvoice::create([
                'invoice_no' => 'RET-' . (20000 + $index),
                'customer_name' => $customer,
                'to_name' => 'MS TRADERS',
                'date_returned' => date('Y-m-d', strtotime('-' . ($index * 4) . ' days')),
                'description' => 'Returned items due to size variance - ' . $customer,
                'total_amount' => 25000 + ($index * 5000),
                'paid_amount' => 25000 + ($index * 5000),
                'due_amount' => 0,
                'notes' => 'Refund processed successfully.',
            ]);

            // Add return items
            $return->items()->create([
                'item_name' => 'Polyester Bales Return',
                'is_small_bales' => rand(0, 1),
                'is_big_bales' => rand(0, 1),
                'no_of_bales' => rand(2, 8),
                'amount' => 25000 + ($index * 5000),
            ]);
        }
    }
}
