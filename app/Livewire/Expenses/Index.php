<?php

namespace App\Livewire\Expenses;

use App\Models\Currency;
use App\Models\Expense;
use App\Models\Property;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $filterCategory = '';

    public ?int $filterPropertyId = null;

    public string $filterMonth = '';

    public ?int $editingExpenseId = null;

    public ?int $landlord_id = null;

    public ?int $property_id = null;

    public ?int $currency_id = null;

    public ?string $address = null;

    public string $expense_date = '';

    public string $category = 'miscellaneous';

    public string $amount = '';

    public ?string $payment_method = 'cash';

    public ?string $vendor = null;

    public string $description = '';

    public ?string $notes = null;

    public $receipt = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Expense::class);
        $this->filterMonth = now()->format('Y-m');
        $this->resetExpenseForm();
    }

    protected function rules(): array
    {
        $user = auth()->user();

        return [
            'landlord_id' => $user->hasRole('admin')
                ? ['required', 'integer', Rule::exists('users', 'id')]
                : ['required', 'integer', Rule::in([$user->id])],
            'property_id' => ['nullable', 'integer', Rule::exists('properties', 'id')],
            'address' => ['nullable', 'string', 'max:500'],
            'currency_id' => ['required', 'integer', Rule::exists('currencies', 'id')],
            'expense_date' => ['required', 'date'],
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999999999.99'],
            'payment_method' => ['nullable', Rule::in(array_keys(Expense::PAYMENT_METHODS))],
            'vendor' => ['nullable', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'receipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp,heic,heif', 'max:10240'],
        ];
    }

    public function updatedLandlordId(): void
    {
        $this->property_id = null;
        $this->address = null;
        $this->currency_id = $this->defaultCurrencyId();
        $this->clearDependentComputedProperties();
    }

    public function updatedPropertyId(): void
    {
        if ($this->property_id) {
            $property = $this->properties->firstWhere('id', $this->property_id);
            $this->currency_id = $property?->currency_id ?? $this->currency_id;
            $this->address = $property?->full_address;
        } else {
            $this->address = null;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPropertyId(): void
    {
        $this->resetPage();
    }

    public function updatingFilterMonth(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $landlordId = auth()->user()->hasRole('admin')
            ? (int) $validated['landlord_id']
            : auth()->id();

        abort_unless(
            User::role('landlord')->whereKey($landlordId)->exists(),
            422,
            'The selected user is not a landlord.',
        );

        if ($validated['property_id']) {
            abort_unless(
                Property::whereKey($validated['property_id'])->where('landlord_id', $landlordId)->exists(),
                422,
                'The selected property does not belong to this landlord.',
            );
        }

        $expense = $this->editingExpenseId
            ? Expense::findOrFail($this->editingExpenseId)
            : new Expense;

        $this->authorize($expense->exists ? 'update' : 'create', $expense->exists ? $expense : Expense::class);

        $oldReceiptPath = null;
        $receiptData = [];

        if ($this->receipt) {
            $oldReceiptPath = $expense->receipt_path;
            $receiptData = [
                'receipt_path' => $this->receipt->store("expenses/{$landlordId}", 'private'),
                'receipt_original_filename' => $this->receipt->getClientOriginalName(),
                'receipt_mime_type' => $this->receipt->getMimeType(),
                'receipt_size' => $this->receipt->getSize(),
            ];
        }

        $expense->fill([
            ...collect($validated)->except('receipt')->toArray(),
            ...$receiptData,
            'landlord_id' => $landlordId,
            'created_by' => $expense->created_by ?: auth()->id(),
        ])->save();

        if ($oldReceiptPath && $oldReceiptPath !== $expense->receipt_path) {
            Storage::disk('private')->delete($oldReceiptPath);
        }

        $message = $this->editingExpenseId ? 'Expense updated successfully.' : 'Expense recorded successfully.';

        $this->resetExpenseForm();
        $this->clearExpenseComputedProperties();
        Flux::toast($message, 'success');
    }

    public function edit(int $id): void
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('update', $expense);

        $this->editingExpenseId = $expense->id;
        $this->landlord_id = $expense->landlord_id;
        $this->property_id = $expense->property_id;
        $this->address = $expense->address ?: $expense->property?->full_address;
        $this->currency_id = $expense->currency_id;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
        $this->category = $expense->category;
        $this->amount = (string) $expense->amount;
        $this->payment_method = $expense->payment_method;
        $this->vendor = $expense->vendor;
        $this->description = $expense->description;
        $this->notes = $expense->notes;
        $this->receipt = null;
        $this->resetValidation();
        $this->clearDependentComputedProperties();

        $this->dispatch('expense-form-focused');
    }

    public function cancelEdit(): void
    {
        $this->resetExpenseForm();
    }

    public function delete(int $id): void
    {
        $expense = Expense::findOrFail($id);
        $this->authorize('delete', $expense);
        $expense->delete();

        if ($this->editingExpenseId === $id) {
            $this->resetExpenseForm();
        }

        $this->clearExpenseComputedProperties();
        Flux::toast('Expense deleted.', 'success');
    }

    #[Computed]
    public function expenses()
    {
        return $this->expenseQuery()
            ->with(['landlord:id,name', 'property:id,name', 'currency:id,code,symbol,decimals'])
            ->latest('expense_date')
            ->latest('id')
            ->paginate(12);
    }

    #[Computed]
    public function summaryByCurrency()
    {
        return $this->expenseQuery(false)
            ->selectRaw('currency_id, SUM(amount) as total_amount, COUNT(*) as expense_count')
            ->with('currency:id,code,symbol,decimals')
            ->groupBy('currency_id')
            ->get();
    }

    #[Computed]
    public function landlords()
    {
        return User::role('landlord')->select('id', 'name', 'email')->orderBy('name')->get();
    }

    #[Computed]
    public function properties()
    {
        if (! $this->landlord_id) {
            return collect();
        }

        return Property::forLandlord($this->landlord_id)
            ->select('id', 'name', 'currency_id', 'address_line_1', 'address_line_2', 'city', 'region', 'postal_code', 'country')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function filterProperties()
    {
        $query = Property::query()->select('id', 'name', 'landlord_id')->orderBy('name');

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    #[Computed]
    public function currencies()
    {
        return Currency::active()->orderBy('code')->get(['id', 'code', 'symbol', 'decimals']);
    }

    #[Computed]
    public function editingExpense(): ?Expense
    {
        return $this->editingExpenseId ? Expense::find($this->editingExpenseId) : null;
    }

    public function render()
    {
        return view('livewire.expenses.index', [
            'categories' => Expense::CATEGORIES,
            'paymentMethods' => Expense::PAYMENT_METHODS,
        ])->layout('layouts.app')->title(__('Expenses'));
    }

    private function expenseQuery(bool $withSearch = true): Builder
    {
        $query = Expense::query();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query
            ->when($withSearch && $this->search, function (Builder $query) {
                $query->where(function (Builder $query) {
                    $query->where('description', 'like', "%{$this->search}%")
                        ->orWhere('vendor', 'like', "%{$this->search}%")
                        ->orWhere('notes', 'like', "%{$this->search}%")
                        ->orWhere('address', 'like', "%{$this->search}%")
                        ->orWhereHas('property', fn (Builder $query) => $query->where('name', 'like', "%{$this->search}%"));
                });
            })
            ->when($this->filterCategory, fn (Builder $query) => $query->where('category', $this->filterCategory))
            ->when($this->filterPropertyId, fn (Builder $query) => $query->where('property_id', $this->filterPropertyId))
            ->when($this->filterMonth, function (Builder $query) {
                [$year, $month] = array_pad(explode('-', $this->filterMonth, 2), 2, null);

                if ($year && $month) {
                    $query->whereYear('expense_date', (int) $year)->whereMonth('expense_date', (int) $month);
                }
            });
    }

    private function resetExpenseForm(): void
    {
        $this->reset([
            'editingExpenseId',
            'property_id',
            'address',
            'amount',
            'vendor',
            'description',
            'notes',
            'receipt',
        ]);

        $this->landlord_id = auth()->user()->hasRole('landlord') ? auth()->id() : null;
        $this->currency_id = $this->defaultCurrencyId();
        $this->expense_date = now()->format('Y-m-d');
        $this->category = 'miscellaneous';
        $this->payment_method = 'cash';
        $this->resetValidation();
        $this->clearDependentComputedProperties();
    }

    private function defaultCurrencyId(): ?int
    {
        $propertyCurrency = $this->landlord_id
            ? Property::forLandlord($this->landlord_id)->whereNotNull('currency_id')->value('currency_id')
            : null;

        return $propertyCurrency
            ?? Currency::where('code', 'DJF')->value('id')
            ?? Currency::active()->value('id');
    }

    private function clearDependentComputedProperties(): void
    {
        unset($this->properties, $this->editingExpense);
    }

    private function clearExpenseComputedProperties(): void
    {
        unset($this->expenses, $this->summaryByCurrency, $this->editingExpense);
    }
}
