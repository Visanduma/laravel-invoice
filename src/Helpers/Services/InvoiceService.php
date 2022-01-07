<?php
/**
 * Created by PhpStorm.
 * User: lahiru
 * Date: 12/13/21
 * Time: 12:51 PM
 */

namespace Visanduma\LaravelInvoice\Helpers\Services;


use Illuminate\Support\Str;

class InvoiceService
{
    private $invoice_date;
    private $due_date;
    private $status_value;
    private $note_value;
    private $discount = 0;
    private $discount_value = 0;
    private $discount_type;
    private $invoice_items = [];
    private $invoice_number = '';
    private $createdBy;
    private $ownerType, $ownerId;
    private $invoiceToData = [];
    private $extra = [];
    private $tag = null;


    public function __construct()
    {
        $this->invoice_date = now()->toDateString();
    }

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

        $this->discount_type = 'amount';
        $this->discount = $amount;
        return $this;
    }

    public function setDiscountPercentage($percentage)
    {
        $this->discount_type = 'percentage';
        $this->discount = $percentage;
        return $this;
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
        $this->invoice_items = $items;
    }

    public function getItemCount()
    {
        return count($this->invoice_items);
    }

    public function invoiceToName(string $name)
    {
        $this->invoiceToData['name'] = $name;
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
        $this->extra[$key] = $value;
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

    private function generateItemList()
    {
        return array_map(function (InvoiceItemService $item) {

            return $item->toArray();

        }, $this->invoice_items);
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

    private function generateInvoiceExtraData()
    {
        return array_map(function ($value, $key) {
            return [
                'key' => $key,
                'value' => $value
            ];
        }, $this->extra, array_keys($this->extra));
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
        return array_sum(array_map(function (InvoiceItemService $item) {
            return $item->totalWithDiscount();
        }, $this->invoice_items));
    }

    public function getTotalWithDiscount()
    {
        return $this->getItemsTotal() - $this->getDiscountValue();
    }

}
