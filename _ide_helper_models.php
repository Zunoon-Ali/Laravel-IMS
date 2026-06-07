<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $no
 * @property string $type
 * @property int $bales
 * @property numeric $weightLbs
 * @property numeric $per_bundle_lbs
 * @property numeric $weightKg
 * @property numeric $actual_weight
 * @property numeric $price
 * @property string $company
 * @property string $date
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OpenedBale> $openedBales
 * @property-read int|null $opened_bales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereActualWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container wherePerBundleLbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereWeightKg($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Container whereWeightLbs($value)
 */
	class Container extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $small_bale_id
 * @property string $name
 * @property int $bales
 * @property numeric $weight
 * @property string|null $supplier
 * @property \Illuminate\Support\Carbon $date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SmallBale|null $smallBale
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereSmallBaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DailyProduction whereWeight($value)
 */
	class DailyProduction extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $container_id
 * @property string $containerNo
 * @property string $date
 * @property int $opened
 * @property int $remaining
 * @property numeric $stockLbs
 * @property numeric $remainingLbs
 * @property numeric $openValue
 * @property numeric $remainingValue
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Container|null $container
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereContainerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereContainerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereOpenValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereOpened($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereRemaining($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereRemainingLbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereRemainingValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereStockLbs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OpenedBale whereUpdatedAt($value)
 */
	class OpenedBale extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $personal_payment_received_id
 * @property string $bank_name
 * @property string $check_no
 * @property \Illuminate\Support\Carbon $due_date
 * @property string $to_name
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PersonalPaymentReceived $paymentReceived
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereCheckNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque wherePersonalPaymentReceivedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentCheque whereUpdatedAt($value)
 */
	class PersonalPaymentCheque extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $personal_payment_received_id
 * @property string $bank_name
 * @property string $name
 * @property \Illuminate\Support\Carbon $payment_date
 * @property string $from_name
 * @property string $to_name
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PersonalPaymentReceived $paymentReceived
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereBankName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline wherePersonalPaymentReceivedId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentOnline whereUpdatedAt($value)
 */
	class PersonalPaymentOnline extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $invoice_no
 * @property string $customer_name
 * @property string $to_name
 * @property \Illuminate\Support\Carbon $date_received
 * @property float $cash_amount
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $due_amount
 * @property string|null $description
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalPaymentCheque> $cheques
 * @property-read int|null $cheques_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalPaymentOnline> $onlines
 * @property-read int|null $onlines_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereCashAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereDateReceived($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalPaymentReceived whereUpdatedAt($value)
 */
	class PersonalPaymentReceived extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $invoice_no
 * @property string $customer_name
 * @property string $to_name
 * @property \Illuminate\Support\Carbon $date_returned
 * @property string|null $description
 * @property float $total_amount
 * @property float $paid_amount
 * @property float $due_amount
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalReturnInvoiceItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereCustomerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereDateReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereDueAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereInvoiceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoice whereUpdatedAt($value)
 */
	class PersonalReturnInvoice extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $personal_return_invoice_id
 * @property string $item_name
 * @property bool $is_small_bales
 * @property bool $is_big_bales
 * @property int $no_of_bales
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PersonalReturnInvoice $returnInvoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereIsBigBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereIsSmallBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereNoOfBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem wherePersonalReturnInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalReturnInvoiceItem whereUpdatedAt($value)
 */
	class PersonalReturnInvoiceItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $supplier_name
 * @property string|null $container_no
 * @property string|null $serial_no
 * @property \Illuminate\Support\Carbon $date_added
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PersonalStockEntryItem> $items
 * @property-read int|null $items_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereContainerNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereDateAdded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereSerialNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereSupplierName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntry whereUpdatedAt($value)
 */
	class PersonalStockEntry extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $personal_stock_entry_id
 * @property string $bale_type
 * @property int $no_of_bales
 * @property string $item_name
 * @property string $company
 * @property float $weight
 * @property float $rate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PersonalStockEntry $stockEntry
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereBaleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereItemName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereNoOfBales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem wherePersonalStockEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PersonalStockEntryItem whereWeight($value)
 */
	class PersonalStockEntryItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property int $stock
 * @property int $production
 * @property int $sale
 * @property numeric $amount
 * @property numeric $weight
 * @property numeric|null $weight_lbs
 * @property numeric $rate
 * @property string $date
 * @property string|null $supplier
 * @property string|null $category
 * @property string|null $warehouseLocation
 * @property string|null $sku
 * @property string|null $status
 * @property int $quantity
 * @property string|null $notes
 * @property string|null $image
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereProduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereSale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereSupplier($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereWarehouseLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmallBale whereWeightLbs($value)
 */
	class SmallBale extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 */
	class User extends \Eloquent {}
}

