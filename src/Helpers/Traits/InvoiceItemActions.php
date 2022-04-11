<?php


namespace Visanduma\LaravelInvoice\Helpers\Traits;


use Visanduma\LaravelInvoice\Models\InvoiceItem;

trait InvoiceItemActions
{
    private $name, $description, $price, $qty;
    private $unit = '';
    private $discount = 0;
    private $discountType;
    private $tag;


//    public function __construct(string $name, float $price, float $qty)
//    {
//        $this->name = $name;
//        $this->price = $price;
//        $this->qty = $qty;
//    }

    public function setName($value)
    {
        $this->name = $value;
        return $this;
    }

    public function setTag($value)
    {
        $this->tag = $value;
        return $this;
    }

    public function setDescription($value)
    {
        $this->description = $value;
        return $this;
    }

    public function setDiscount($amount)
    {
        $this->discount = $amount;
        $this->discountType = 'amount';
        return $this;
    }

    public function setDiscountPercentage($percentage)
    {
        $this->discount = $percentage;
        $this->discountType = 'percentage';
        return $this;
    }

    public function setPrice($amount)
    {
        $this->price = $amount;
        return $this;
    }

    public function setQty($value)
    {
        $this->qty = $value;
        return $this;
    }

    public function setUnit($value)
    {
        $this->unit = $value;
        return $this;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'qty' => $this->qty,
            'unit' => $this->unit,
            'tag' => $this->tag,
            'discount' => $this->discount,
            'discount_value' => $this->getDiscountValue(),
            'discount_type' => $this->discountType,
            'total' => $this->totalWithDiscount()
        ];
    }

    private function getDiscountValue()
    {
        if ($this->discountType == InvoiceItem::DISCOUNT_PERCENTAGE) {
            return $this->totalWithoutDiscount() * $this->discount / 100;
        }

        return $this->discount;
    }

    public function totalWithoutDiscount()
    {
        return ($this->price * $this->qty);
    }

    public function totalWithDiscount()
    {
        return ($this->price * $this->qty) - $this->getDiscountValue();
    }
}
