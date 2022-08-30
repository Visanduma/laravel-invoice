<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Illuminate\Support\Str;
use Visanduma\LaravelInvoice\Helpers\Services\InvoiceItemService;
use Visanduma\LaravelInvoice\Models\InvoiceItem;

trait InvoiceActions
{
    private $invoice_date;
    private $due_date;
    private $status_value;
    private $note_value;
    private $invoice_items = [];
    private $invoice_number = '';
    private $createdBy;
    private $ownerType, $ownerId;
    private $invoiceToData = [];
    private $extra = [];
    private $tag = null;

    public function setOwner($model, $id)
    {
        $this->ownerType = $model;
        $this->ownerId = $id;
        return $this;
    }

    public function invoiceDate($date)
    {
        $this->invoice_date = $date;
        return $this;
    }

    public function dueDate($date)
    {
        $this->due_date = $date;
        return $this;
    }

    public function status($value)
    {
        $this->status_value = $value;
        return $this;
    }

    public function setNote($value)
    {
        $this->note_value = $value;
        return $this;
    }

    public function setDiscount($amount)
    {
        if (Str::endsWith($amount, "%")) {
            return $this->setDiscountPercentage((double)$amount);
        }

        $this->update([
            'discount_type' => 'amount',
            'discount_value' => $amount,
            'discount' => $amount,
        ]);

        return $this;
    }

    public function setDiscountPercentage($percentage)
    {
        $this->update([
            'discount_type' => 'percentage',
            'discount_value' => $percentage,
            'discount' => $this->total() * $percentage / 100,
        ]);

        return $this;
    }

    public function total()
    {
        return $this->items()->sum('total');
    }

    public function setInvoiceNumber($value)
    {
        $this->invoice_number = $value;
        return $this;
    }

    public function setCreatedBy($id)
    {
        $this->createdBy = $id;
        return $this;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    public function addItems(array $items)
    {
//        $this->invoice_items = $items;
        $this->items()->saveMany($items);
    }

    public function addItemsAsArray(array $items = [])
    {
        /*
         *  array keys
         *  name,price,qty
         */
        foreach ($items as $item) {
            $this->invoice_items[] = InvoiceItem::make($item['name'], $item['price'], $item['qty']);
        }
    }

    public function getItemCount()
    {
        return $this->items->count();
    }

    public function invoiceToName(string $name)
    {
        $this->extra()->create([
            'name' => 'name',
            'value' => $name
        ]);
    }

    public function invoiceToAddress(string $address)
    {
        $this->invoiceToData['address'] = $address;
    }

    public function invoiceToContact(string $contact)
    {
        $this->invoiceToData['contact'] = $contact;
    }

    public function setExtraValue($key, $value)
    {
        $this->extra()->create([
            'key' => $key,
            'value' => $value
        ]);
    }

    public function setExtraValues(array $values)
    {
        foreach ($values as $key => $value) {
            $this->extra[$key] = $value;
        }
    }

    public function toArray()
    {
        return [
            'invoice' => [
                'invoice_date' => $this->invoice_date,
                'tag' => $this->tag,
                'due_data' => $this->due_date,
                'invoice_number' => $this->invoice_number,
                'status' => 'CREATED',
                'paid_status' => 'PENDING',
                'note' => $this->note_value,
                'discount' => $this->discount,
                'discount_value' => $this->getDiscountValue(),
                'discount_type' => $this->discount_type,
                'sub_total' => 0,
                'total' => $this->getTotalWithDiscount(),
                'due_amount' => $this->getTotalWithDiscount(),
                'created_by' => $this->createdBy
            ],

            'extra' => array_merge($this->generateInvoiceExtraData(), $this->generateAdditionalInvoiceData()),
            'items' => $this->generateItemList()
        ];


    }

    private function getDiscountValue()
    {
        if ($this->discount_type == 'percentage') {
            return $this->getItemsTotal() * $this->discount / 100;
        }

        return $this->discount;
    }

    public function getItemsTotal()
    {
        return array_sum(array_map(function (InvoiceItem $item) {
            return $item->totalWithDiscount();
        }, $this->invoice_items));
    }

    private function generateInvoiceExtraData()
    {
        return array_map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value
            ];
        }, $this->extra, array_keys($this->extra));
    }

    private function generateAdditionalInvoiceData()
    {
        return array_map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value
            ];
        }, $this->invoiceToData, array_keys($this->invoiceToData));
    }

    private function generateItemList()
    {
        return array_map(function (InvoiceItemService $item) {

            return $item->toArray();

        }, $this->invoice_items);
    }

    public function totalWithDiscount()
    {
        return $this->total() - $this->discount;
    }

    public function paidAmount()
    {
        return $this->payments()->sum('amount');
    }


}
