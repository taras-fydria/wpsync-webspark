<?php

namespace WpsyncWebspark\Inc;

class ProductInput
{
    public string $sku = '';
    public string $name = '';
    public string $description = '';
    public string $price = '';
    public string $picture_url = '';
    public int $stock_count = 0;

    public function __construct(array $item)
    {
        $this->sku = $item['sku'] ?? '';
        $this->name = $item['name'] ?? '';
        $this->description = $item['description'] ?? '';
        $this->price = $item['price'] ?? '';
        $this->picture_url = $item['picture'] ?? '';
        $this->stock_count = $item['in_stock'] ?? 0;
    }

    public static function build_item(array $item): self
    {
        return new self($item);
    }
}