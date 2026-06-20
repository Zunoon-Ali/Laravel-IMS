<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Bank;
use App\Models\BankLedger;
use App\Models\Container;
use App\Models\OpenedBale;
use App\Models\SmallBale;
use App\Models\DailyProduction;
use App\Models\PersonalSupplier;
use App\Models\PersonalCustomer;
use App\Models\PersonalStockEntry;
use App\Models\PersonalPaymentReceived;
use App\Models\PersonalPaymentSent;
use App\Models\PersonalReturnInvoice;

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

        // Disable foreign key constraints to safely truncate
        Schema::disableForeignKeyConstraints();
        
        BankLedger::truncate();
        Bank::truncate();
        DailyProduction::truncate();
        SmallBale::truncate();
        OpenedBale::truncate();
        Container::truncate();
        
        PersonalSupplier::truncate();
        PersonalCustomer::truncate();
        
        PersonalStockEntry::truncate();
        DB::table('personal_stock_entry_items')->truncate();
        
        PersonalPaymentReceived::truncate();
        DB::table('personal_payment_cheques')->truncate();
        DB::table('personal_payment_onlines')->truncate();
        
        PersonalPaymentSent::truncate();
        DB::table('personal_payment_sent_cheques')->truncate();
        DB::table('personal_payment_sent_onlines')->truncate();
        
        PersonalReturnInvoice::truncate();
        DB::table('personal_return_invoice_items')->truncate();
        
        Schema::enableForeignKeyConstraints();

        // 2. Seed 10 Pakistani Banks
        $banksData = [
            ['name' => 'Meezan Bank', 'account' => 'MEEZAN-990812345', 'opening' => 8500000.00, 'logo' => 'meezan.png', 'branch' => 'Main Boulevard, Gulberg, Lahore'],
            ['name' => 'Bank Alfalah', 'account' => 'ALFALAH-776239401', 'opening' => 4500000.00, 'logo' => 'alfalah.png', 'branch' => 'I.I. Chundrigar Road, Karachi'],
            ['name' => 'Bank Al Habib', 'account' => 'BAHL-884392019', 'opening' => 6200000.00, 'logo' => 'bahl.png', 'branch' => 'Blue Area, Islamabad'],
            ['name' => 'Habib Bank Limited', 'account' => 'HBL-110029384', 'opening' => 9500000.00, 'logo' => 'hbl.png', 'branch' => 'Mall Road, Rawalpindi'],
            ['name' => 'United Bank Limited', 'account' => 'UBL-449382012', 'opening' => 5800000.00, 'logo' => 'ubl.png', 'branch' => 'Karkhana Bazar, Faisalabad'],
            ['name' => 'MCB Bank Limited', 'account' => 'MCB-229384019', 'opening' => 3800000.00, 'logo' => 'mcb.png', 'branch' => 'Cantt Branch, Peshawar'],
            ['name' => 'Allied Bank Limited', 'account' => 'ABL-559384729', 'opening' => 4100000.00, 'logo' => 'abl.png', 'branch' => 'Satyana Road, Faisalabad'],
            ['name' => 'National Bank of Pakistan', 'account' => 'NBP-100293847', 'opening' => 7500000.00, 'logo' => 'nbp.png', 'branch' => 'GPO Chowk, Multan'],
            ['name' => 'Faysal Bank', 'account' => 'FAYSAL-663920194', 'opening' => 3200000.00, 'logo' => 'faysal.png', 'branch' => 'DHA Phase 6, Karachi'],
            ['name' => 'Askari Bank', 'account' => 'ASKARI-339201948', 'opening' => 5000000.00, 'logo' => 'askari.png', 'branch' => 'Saddar, Hyderabad'],
        ];

        $seededBanks = [];
        foreach ($banksData as $index => $b) {
            $seededBanks[] = Bank::create([
                'bank_name' => $b['name'],
                'logo' => $b['logo'],
                'account_number' => $b['account'],
                'opening_balance' => $b['opening'],
                'current_balance' => $b['opening'],
                'status' => 'Active',
                'branch' => $b['branch'],
            ]);
        }

        // 3. Seed 12 Suppliers with Pakistani details
        $suppliersData = [
            ['name' => 'Muhammad Yousuf', 'phone' => '0300-4829102', 'email' => 'yousuf.textiles@supplier.pk', 'city' => 'Faisalabad', 'address' => 'P-12, Gole Kiryana, Faisalabad'],
            ['name' => 'Tariq Mahmood', 'phone' => '0321-9988771', 'email' => 'tariq.mahmood@yarnhub.pk', 'city' => 'Lahore', 'address' => '45-B, Industrial Area, Kot Lakhpat, Lahore'],
            ['name' => 'Zahid Iqbal', 'phone' => '0333-8877665', 'email' => 'zahid.iqbal@cottontraders.pk', 'city' => 'Multan', 'address' => 'Chowk Shaheedan, Multan'],
            ['name' => 'Bilal Siddiqui', 'phone' => '0345-1234567', 'email' => 'bilal.siddiqui@siddiquifibers.pk', 'city' => 'Karachi', 'address' => 'Plot 234, Sector 15, Korangi Industrial Area, Karachi'],
            ['name' => 'Shaffan Ahmed', 'phone' => '0312-5554433', 'email' => 'shaffan.ahmed@punjabcotton.pk', 'city' => 'Sargodha', 'address' => 'Near Grain Market, Sargodha'],
            ['name' => 'Syed Ahmed Khalid', 'phone' => '0301-7766554', 'email' => 'khalid.weaving@supplier.pk', 'city' => 'Gujranwala', 'address' => 'Small Industrial Estate, Gujranwala'],
            ['name' => 'Imran Khan', 'phone' => '0315-9988223', 'email' => 'imran.khan@kpexports.pk', 'city' => 'Peshawar', 'address' => 'Jamrud Road, Peshawar'],
            ['name' => 'Yasir Arafat', 'phone' => '0322-1122334', 'email' => 'yasir.arafat@sindhfiber.pk', 'city' => 'Hyderabad', 'address' => 'Site Area, Hyderabad'],
            ['name' => 'Faisal Shah', 'phone' => '0334-4455667', 'email' => 'faisal.shah@shahyarns.pk', 'city' => 'Jhang', 'address' => 'Yousuf Shah Road, Jhang'],
            ['name' => 'Kamran Akmal', 'phone' => '0305-6677889', 'email' => 'kamran.akmal@balochfiber.pk', 'city' => 'Quetta', 'address' => 'Double Road, Quetta'],
            ['name' => 'Jabbar Sher', 'phone' => '0346-7788990', 'email' => 'jabbar.sher@shertextiles.pk', 'city' => 'Sialkot', 'address' => 'Kashmir Road, Sialkot'],
            ['name' => 'Qamar Zaman', 'phone' => '0311-2233445', 'email' => 'qamar.zaman@qamarfabrics.pk', 'city' => 'Kasur', 'address' => 'Railway Road, Kasur'],
        ];

        $seededSuppliers = [];
        foreach ($suppliersData as $index => $s) {
            $seededSuppliers[] = PersonalSupplier::create([
                'name' => $s['name'],
                'phone' => $s['phone'],
                'email' => $s['email'],
                'status' => 'Active',
                'city' => $s['city'],
                'address' => $s['address'],
                'opening_balance' => rand(100, 800) * 1000.00, // 100k to 800k
                'notes' => 'Reliable primary supplier of textile items.',
            ]);
        }

        // 4. Seed 12 Customers with Pakistani details
        $customersData = [
            ['name' => 'Al Barkat Traders', 'phone' => '0300-8843920', 'email' => 'info@albarkat.pk', 'city' => 'Karachi', 'address' => 'M.A. Jinnah Road, Karachi'],
            ['name' => 'Khalid Fabrics', 'phone' => '0321-4453920', 'email' => 'khalid.fabrics@retail.pk', 'city' => 'Lahore', 'address' => 'Anarkali Bazaar, Lahore'],
            ['name' => 'Noman Textiles', 'phone' => '0333-5566778', 'email' => 'noman.textiles@yarnmarket.pk', 'city' => 'Faisalabad', 'address' => 'Tata Bazar, Faisalabad'],
            ['name' => 'Usman & Sons', 'phone' => '0345-9988112', 'email' => 'usmanandsons@distribution.pk', 'city' => 'Rawalpindi', 'address' => 'Raja Bazar, Rawalpindi'],
            ['name' => 'Hassan Fabrics', 'phone' => '0312-3344556', 'email' => 'hassan@hassanfabrics.pk', 'city' => 'Gujranwala', 'address' => 'Gondlanwala Road, Gujranwala'],
            ['name' => 'Chawla Garments', 'phone' => '0301-2233445', 'email' => 'chawla@garments.pk', 'city' => 'Sialkot', 'address' => 'Shahabpura Road, Sialkot'],
            ['name' => 'Pak Cotton Palace', 'phone' => '0315-7788991', 'email' => 'pakcotton@palace.pk', 'city' => 'Multan', 'address' => 'Hussain Agahi, Multan'],
            ['name' => 'Khyber Weaving', 'phone' => '0322-9988776', 'email' => 'khyber@weaving.pk', 'city' => 'Peshawar', 'address' => 'Industrial Estate, Hayatabad, Peshawar'],
            ['name' => 'Sindh Textile Hub', 'phone' => '0334-1122334', 'email' => 'sindh@textilehub.pk', 'city' => 'Hyderabad', 'address' => 'Latifabad No. 7, Hyderabad'],
            ['name' => 'Balochistan Fiber Traders', 'phone' => '0305-9988443', 'email' => 'balochistan@fibertraders.pk', 'city' => 'Quetta', 'address' => 'Sariab Road, Quetta'],
            ['name' => 'Rawalpindi Hosiery', 'phone' => '0346-3344221', 'email' => 'rwp@hosiery.pk', 'city' => 'Rawalpindi', 'address' => 'Saidpur Road, Rawalpindi'],
            ['name' => 'Multan Yarn Mart', 'phone' => '0311-7788112', 'email' => 'multan@yarnmart.pk', 'city' => 'Multan', 'address' => 'Vehari Road, Multan'],
        ];

        $seededCustomers = [];
        foreach ($customersData as $index => $c) {
            $seededCustomers[] = PersonalCustomer::create([
                'name' => $c['name'],
                'phone' => $c['phone'],
                'email' => $c['email'],
                'status' => 'Active',
                'city' => $c['city'],
                'address' => $c['address'],
                'opening_balance' => rand(150, 900) * 1000.00, // 150k to 900k
                'balance_type' => $index % 2 === 0 ? 'debit' : 'credit',
                'notes' => 'Key regional customer for finished bales.',
            ]);
        }

        // 5. Seed 12 Containers
        $seededContainers = [];
        for ($i = 1; $i <= 12; $i++) {
            $weightLbs = rand(25000, 48000);
            $perBundle = rand(110, 160);
            $bales = (int) ($weightLbs / $perBundle);
            $weightKg = round($weightLbs / 2.20462, 2);
            
            $seededContainers[] = Container::create([
                'no' => 'PK-CON-' . (1000 + $i),
                'type' => $i % 2 === 0 ? 'HQ' : 'Standard',
                'bales' => $bales,
                'weightLbs' => $weightLbs,
                'per_bundle_lbs' => $perBundle,
                'weightKg' => $weightKg,
                'actual_weight' => $weightKg,
                'price' => rand(150, 800) * 10000.00, // 1.5M to 8.0M
                'company' => ['Chenab Cotton', 'Nishat Fabrics', 'Gul Ahmed Export', 'Crescent Textile'][$i % 4],
                'date' => date('Y-m-d', strtotime('-' . ($i * 12) . ' days')),
                'description' => 'Cotton fabric shipment container PK-CON-' . (1000 + $i),
            ]);
        }

        // 6. Seed 15 Small Bales
        $baleNames = [
            'BABY BLANKETS', 'BATH MATS', 'BED COVERS', 'CARPETS', 'ELASTIC BEDSHEETS',
            'COTTON TOWELS', 'WOOLEN SOCKS', 'FANCY CURTAINS', 'PILLOW CASES', 'JUTE BAGS',
            'COMFORTERS', 'FLANNEL SHEETS', 'POLYESTER BLANKETS', 'DENIM ROLLS', 'VELVET FABRIC'
        ];

        $seededSmallBales = [];
        foreach ($baleNames as $index => $name) {
            $stock = rand(100, 450);
            $production = rand(100, 450);
            $sale = rand(30, $stock - 20);
            $rate = rand(22, 48) * 1000; // 22k to 48k
            
            $seededSmallBales[] = SmallBale::create([
                'name' => $name,
                'stock' => $stock,
                'production' => $production,
                'sale' => $sale,
                'amount' => $stock * $rate,
                'weight' => $stock * 52,
                'weight_lbs' => $stock * 115,
                'rate' => $rate,
                'date' => date('Y-m-d', strtotime('-' . ($index * 5) . ' days')),
                'supplier' => $seededSuppliers[$index % count($seededSuppliers)]->name,
                'category' => $index % 2 === 0 ? 'small-bales' : 'big-bales',
                'warehouseLocation' => 'Warehouse Block ' . chr(65 + ($index % 4)),
                'sku' => 'SKU-' . substr($name, 0, 3) . '-' . (100 + $index),
                'status' => 'Active',
                'quantity' => $stock,
                'notes' => 'High demand seasonal goods.',
                'image' => 'bale_' . ($index + 1) . '.png'
            ]);
        }

        // 7. Seed 12 Opened Bales
        for ($i = 0; $i < 12; $i++) {
            $container = $seededContainers[$i];
            $opened = rand(15, 60);
            $remaining = $container->bales - $opened;
            $stockLbs = $container->weightLbs;
            $remainingLbs = round(($remaining / $container->bales) * $stockLbs, 2);
            $pricePerBale = $container->price / $container->bales;
            
            OpenedBale::create([
                'container_id' => $container->id,
                'containerNo' => $container->no,
                'date' => date('Y-m-d', strtotime($container->date . ' + 3 days')),
                'opened' => $opened,
                'remaining' => $remaining,
                'stockLbs' => $stockLbs,
                'remainingLbs' => $remainingLbs,
                'openValue' => round($opened * $pricePerBale, 2),
                'remainingValue' => round($remaining * $pricePerBale, 2),
            ]);
        }

        // 8. Seed 12 Daily Productions
        for ($i = 0; $i < 12; $i++) {
            $smallBale = $seededSmallBales[$i];
            $bales = rand(5, 20);
            
            DailyProduction::create([
                'small_bale_id' => $smallBale->id,
                'name' => $smallBale->name,
                'bales' => $bales,
                'weight' => $bales * 52,
                'supplier' => $smallBale->supplier,
                'date' => date('Y-m-d', strtotime('-' . ($i * 4) . ' days')),
            ]);
        }

        // 9. Seed 12 Personal Stock Entries
        for ($i = 1; $i <= 12; $i++) {
            $supplier = $seededSuppliers[$i - 1];
            $entry = PersonalStockEntry::create([
                'supplier_name' => $supplier->name,
                'container_no' => 'SE-CON-' . (2000 + $i),
                'serial_no' => 'SN-STOCK-' . (50000 + $i),
                'date_added' => date('Y-m-d', strtotime('-' . ($i * 7) . ' days')),
                'notes' => 'Stock entry shipment from ' . $supplier->name,
            ]);

            // Add small bales item
            $entry->items()->create([
                'bale_type' => 'small',
                'no_of_bales' => rand(15, 45),
                'item_name' => $seededSmallBales[$i - 1]->name,
                'company' => 'PML',
                'weight' => rand(800, 2200),
                'rate' => rand(25, 40) * 1000,
            ]);

            // Add big bales item
            $entry->items()->create([
                'bale_type' => 'big',
                'no_of_bales' => rand(10, 30),
                'item_name' => $seededSmallBales[($i) % count($seededSmallBales)]->name,
                'company' => 'MS-FIBERS',
                'weight' => rand(1200, 3500),
                'rate' => rand(35, 55) * 1000,
            ]);
        }

        // Helper tracker for Bank Ledger running entries
        $ledgerCounter = 1;

        // 10. Seed 12 Payments Received (Sales Invoices) (PAR)
        for ($i = 1; $i <= 12; $i++) {
            $customer = $seededCustomers[$i - 1];
            
            // Total amount between 200k and 5M
            $totalAmount = rand(20, 500) * 10000.00;
            $cashAmount = $i % 3 === 0 ? $totalAmount * 0.2 : 0;
            $bankAmount = $totalAmount - $cashAmount;
            
            $payment = PersonalPaymentReceived::create([
                'invoice_no' => 'SAL-' . (10000 + $i),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'to_name' => 'MS TRADERS',
                'date_received' => date('Y-m-d', strtotime('-' . ($i * 8) . ' days')),
                'cash_amount' => $cashAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0.00,
                'description' => 'Full payment received for wholesale fabrics',
                'notes' => 'Payment settled in full.',
            ]);

            $selectedBank = $seededBanks[($i - 1) % count($seededBanks)];

            if ($i % 2 === 0) {
                // Seed Online Transfer
                $payment->onlines()->create([
                    'bank_name' => $selectedBank->bank_name,
                    'name' => 'IBFT Ref: ' . rand(100000, 999999),
                    'payment_date' => $payment->date_received,
                    'from_name' => $customer->name,
                    'to_name' => 'MS TRADERS',
                    'amount' => $bankAmount,
                ]);

                // Create Bank Ledger entry (Credit)
                BankLedger::create([
                    'bank_id' => $selectedBank->id,
                    'invoice_no' => $payment->invoice_no,
                    'transaction_type' => 'credit',
                    'payment_type' => 'online',
                    'amount' => $bankAmount,
                    'balance_after' => 0.00, // calculated later
                    'description' => 'Payment Received - Online Transfer (SAL-' . (10000 + $i) . ')',
                    'reference_type' => 'payment_received',
                    'reference_id' => $payment->id,
                    'transaction_date' => $payment->date_received,
                ]);
            } else {
                // Seed Cheque
                $payment->cheques()->create([
                    'bank_name' => $selectedBank->bank_name,
                    'check_no' => 'CHQ-REC-' . (70000 + $i),
                    'due_date' => date('Y-m-d', strtotime($payment->date_received . ' + 3 days')),
                    'to_name' => 'MS TRADERS',
                    'amount' => $bankAmount,
                ]);

                // Create Bank Ledger entry (Credit)
                BankLedger::create([
                    'bank_id' => $selectedBank->id,
                    'invoice_no' => $payment->invoice_no,
                    'transaction_type' => 'credit',
                    'payment_type' => 'cheque',
                    'amount' => $bankAmount,
                    'balance_after' => 0.00, // calculated later
                    'description' => 'Payment Received - Cheque ' . 'CHQ-REC-' . (70000 + $i),
                    'reference_type' => 'payment_received',
                    'reference_id' => $payment->id,
                    'transaction_date' => $payment->date_received,
                ]);
            }
        }

        // 11. Seed 12 Payments Sent (Supplier / General Payments) (PAS)
        for ($i = 1; $i <= 12; $i++) {
            $customer = $seededCustomers[($i) % count($seededCustomers)];
            
            // Total amount between 150k and 3M
            $totalAmount = rand(15, 300) * 10000.00;
            $cashAmount = $i % 4 === 0 ? $totalAmount * 0.15 : 0;
            $bankAmount = $totalAmount - $cashAmount;
            
            $payment = PersonalPaymentSent::create([
                'invoice_no' => 'PAS-' . (10000 + $i),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'to_name' => 'MS TRADERS',
                'date_sent' => date('Y-m-d', strtotime('-' . ($i * 9) . ' days')),
                'cash_amount' => $cashAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0.00,
                'description' => 'Payment sent against yarn supply',
                'notes' => 'Payment cleared through bank.',
            ]);

            $selectedBank = $seededBanks[($i + 2) % count($seededBanks)];

            if ($i % 2 === 0) {
                // Seed Online Transfer
                $payment->onlines()->create([
                    'bank_name' => $selectedBank->bank_name,
                    'name' => 'IBFT Outward Ref: ' . rand(200000, 899999),
                    'payment_date' => $payment->date_sent,
                    'from_name' => 'MS TRADERS',
                    'to_name' => $customer->name,
                    'amount' => $bankAmount,
                ]);

                // Create Bank Ledger entry (Debit)
                BankLedger::create([
                    'bank_id' => $selectedBank->id,
                    'invoice_no' => $payment->invoice_no,
                    'transaction_type' => 'debit',
                    'payment_type' => 'online',
                    'amount' => $bankAmount,
                    'balance_after' => 0.00, // calculated later
                    'description' => 'Payment Sent - Online Transfer (PAS-' . (10000 + $i) . ')',
                    'reference_type' => 'payment_sent',
                    'reference_id' => $payment->id,
                    'transaction_date' => $payment->date_sent,
                ]);
            } else {
                // Seed Cheque
                $payment->cheques()->create([
                    'bank_name' => $selectedBank->bank_name,
                    'check_no' => 'CHQ-SEN-' . (80000 + $i),
                    'due_date' => date('Y-m-d', strtotime($payment->date_sent . ' + 5 days')),
                    'to_name' => $customer->name,
                    'amount' => $bankAmount,
                ]);

                // Create Bank Ledger entry (Debit)
                BankLedger::create([
                    'bank_id' => $selectedBank->id,
                    'invoice_no' => $payment->invoice_no,
                    'transaction_type' => 'debit',
                    'payment_type' => 'cheque',
                    'amount' => $bankAmount,
                    'balance_after' => 0.00, // calculated later
                    'description' => 'Payment Sent - Cheque ' . 'CHQ-SEN-' . (80000 + $i),
                    'reference_type' => 'payment_sent',
                    'reference_id' => $payment->id,
                    'transaction_date' => $payment->date_sent,
                ]);
            }
        }

        // 12. Seed 12 Return Invoices (RET)
        for ($i = 1; $i <= 12; $i++) {
            $customer = $seededCustomers[($i + 1) % count($seededCustomers)];
            
            // Total amount between 20k and 150k
            $totalAmount = rand(20, 150) * 1000.00;
            
            $return = PersonalReturnInvoice::create([
                'invoice_no' => 'RET-' . (20000 + $i),
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'to_name' => 'MS TRADERS',
                'date_returned' => date('Y-m-d', strtotime('-' . ($i * 10) . ' days')),
                'description' => 'Returned defective / surplus bales from order.',
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount,
                'due_amount' => 0.00,
                'notes' => 'Return items processed and stock returned to warehouse.',
            ]);

            // Add Return Invoice Items
            $return->items()->create([
                'item_name' => $seededSmallBales[($i + 3) % count($seededSmallBales)]->name,
                'is_small_bales' => true,
                'is_big_bales' => false,
                'no_of_bales' => rand(2, 6),
                'amount' => $totalAmount,
            ]);
        }

        // 13. Recalculate balances for all Banks
        foreach ($seededBanks as $bank) {
            $bank->recalculateBalance();
        }
    }
}
