<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    public const CATEGORIES = [
        'maintenance' => 'Maintenance',
        'repairs' => 'Repairs',
        'utilities' => 'Utilities',
        'supplies' => 'Supplies',
        'taxes' => 'Taxes',
        'insurance' => 'Insurance',
        'management' => 'Management',
        'miscellaneous' => 'Miscellaneous',
        'other' => 'Other',
    ];

    public const PAYMENT_METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'card' => 'Card',
        'mobile_money' => 'Mobile money',
        'other' => 'Other',
    ];

    protected $fillable = [
        'landlord_id',
        'property_id',
        'unit_id',
        'address',
        'currency_id',
        'created_by',
        'expense_date',
        'category',
        'amount',
        'payment_method',
        'vendor',
        'description',
        'notes',
        'receipt_path',
        'receipt_original_filename',
        'receipt_mime_type',
        'receipt_size',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'float',
        'receipt_size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Expense $expense): void {
            if ($expense->property_id) {
                $property = Property::find($expense->property_id);

                if (! $property || $property->landlord_id !== $expense->landlord_id) {
                    throw ValidationException::withMessages([
                        'property_id' => 'The selected property does not belong to this landlord.',
                    ]);
                }

                if (blank($expense->address)) {
                    $expense->address = $property->full_address;
                }
            }

            if ($expense->unit_id) {
                $unitBelongsToProperty = $expense->property_id
                    && Unit::whereKey($expense->unit_id)->where('property_id', $expense->property_id)->exists();

                if (! $unitBelongsToProperty) {
                    throw ValidationException::withMessages([
                        'unit_id' => 'The selected unit does not belong to this property.',
                    ]);
                }
            }
        });
    }

    public function landlord(): BelongsTo
    {
        return $this->belongsTo(User::class, 'landlord_id');
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForLandlord(Builder $query, int $landlordId): Builder
    {
        return $query->where('landlord_id', $landlordId);
    }

    public function getFormattedAmountAttribute(): string
    {
        return Money::format($this->amount, $this->currency);
    }

    public function getCategoryLabelAttribute(): string
    {
        return __(self::CATEGORIES[$this->category] ?? ucfirst($this->category));
    }

    public function hasReceipt(): bool
    {
        return filled($this->receipt_path);
    }
}
