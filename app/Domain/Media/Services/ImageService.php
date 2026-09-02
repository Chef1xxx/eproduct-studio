<?php

namespace App\Domain\Media\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class ImageService
{
    /**
     * @return array{image_path: string, thumbnail_path: string}
     */
    public function storeProductImage(UploadedFile $file): array
    {
        $uuid = (string) Str::uuid();

        $imagePath = Image::fromUpload($file)
            ->cover(1200, 1200)
            ->toWebp()
            ->quality(80)
            ->storePubliclyAs('products', "{$uuid}.webp", 'public');

        $thumbnailPath = Image::fromUpload($file)
            ->cover(300, 300)
            ->toWebp()
            ->quality(80)
            ->storePubliclyAs('products', "{$uuid}-thumb.webp", 'public');

        if ($imagePath === false || $thumbnailPath === false) {
            throw new \RuntimeException('Не удалось сохранить изображение товара.');
        }

        return [
            'image_path' => $imagePath,
            'thumbnail_path' => $thumbnailPath,
        ];
    }

    public function deleteIfExists(?string ...$paths): void
    {
        foreach ($paths as $path) {
            if ($path === null || $path === '') {
                continue;
            }

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
    }
}