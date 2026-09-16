<?php

namespace App\Http\Requests\Seller;

use App\Domain\AI\DTO\ProductGenerationData;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ProductGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $product = $this->product();

        return $product === null || $this->user()->can('update', $product);
    }

    public function rules(): array
    {
        return [
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'advantages' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function product(): ?Product
    {
        $id = $this->input('product_id');

        return is_numeric($id) ? Product::query()->find((int) $id) : null;
    }

    public function toGenerationData(): ProductGenerationData
    {
        return ProductGenerationData::fromValidated($this->validated());
    }
}
