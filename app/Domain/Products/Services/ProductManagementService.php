<?php

namespace App\Domain\Products\Services;

use App\Domain\Media\Services\ImageService;
use App\DTO\ProductData;
use App\Models\Product;
use App\Models\User;

final class ProductManagementService
{
    public function __construct(
        private readonly ImageService $images,
    ) {}

    public function create(User $user, ProductData $data): Product
    {
        $paths = $data->image !== null
            ? $this->images->storeProductImage($data->image)
            : ['image_path' => null, 'thumbnail_path' => null];

        return Product::query()->create([
            'user_id' => $user->id,
            'category_id' => $data->category_id,
            'name' => $data->name,
            'price' => $data->price,
            'short_description' => $data->short_description,
            'description' => $data->description,
            'advantages' => $this->parseAdvantages($data->advantages),
            'image_path' => $paths['image_path'],
            'thumbnail_path' => $paths['thumbnail_path'],
        ]);
    }

    public function update(Product $product, ProductData $data): Product
    {
        $payload = [
            'category_id' => $data->category_id,
            'name' => $data->name,
            'price' => $data->price,
            'short_description' => $data->short_description,
            'description' => $data->description,
            'advantages' => $this->parseAdvantages($data->advantages),
        ];

        if ($data->image !== null) {
            $oldImage = $product->image_path;
            $oldThumb = $product->thumbnail_path;

            $paths = $this->images->storeProductImage($data->image);
            $payload['image_path'] = $paths['image_path'];
            $payload['thumbnail_path'] = $paths['thumbnail_path'];

            $product->update($payload);

            $this->images->deleteIfExists($oldImage, $oldThumb);

            return $product->refresh();
        }

        $product->update($payload);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $image = $product->image_path;
        $thumb = $product->thumbnail_path;

        $product->delete();

        $this->images->deleteIfExists($image, $thumb);
    }

    /**
     * @return list<string>|null
     */
    private function parseAdvantages(?string $advantages): ?array
    {
        if ($advantages === null || trim($advantages) === '') {
            return null;
        }

        $parts = array_map(
            static fn (string $part): string => trim($part),
            explode(',', $advantages),
        );

        $parts = array_values(array_filter(
            $parts,
            static fn (string $part): bool => $part !== '',
        ));

        return $parts === [] ? null : $parts;
    }
}