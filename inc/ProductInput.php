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
        if (isset($item['sku'])) $this->sku = $item['sku'];

        if (isset($item['name'])) $this->name = $item['name'];

        if (isset($item['description'])) $this->description = $item['description'];

        if (isset($item['price'])) $this->price = $item['price'];

        if (isset($item['picture'])) $this->picture_url = $item['picture'];

        if (isset($item['in_stock'])) $this->stock_count = $item['in_stock'];
    }

    public static function build_item(array $item): self
    {
        return new self($item);
    }
}