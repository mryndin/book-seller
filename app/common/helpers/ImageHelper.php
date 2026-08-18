<?php

namespace common\helpers;

use yii\imagine\Image;
use yii\web\UploadedFile;

class ImageHelper
{
    public static function getUploadPath(int $id, string $entity): string
    {
        $paddedId = str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        $part1 = substr($paddedId, 0, 2);
        $part2 = substr($paddedId, 2, 2);

        return "uploads/{$entity}/{$part1}/{$part2}/{$id}";
    }

    public static function getUrl(int $id, string $entity, string $size = 'original'): string
    {
        $basePath = self::getUploadPath($id, $entity);
        $webRoot = \Yii::getAlias('@frontend/web');

        if ($size === 'original') {
            if (file_exists($webRoot . "/{$basePath}/original.jpg")) {
                return "/{$basePath}/original.jpg";
            } elseif (file_exists($webRoot . "/{$basePath}/original.png")) {
                return "/{$basePath}/original.png";
            } elseif (file_exists($webRoot . "/{$basePath}/original.gif")) {
                return "/{$basePath}/original.gif";
            } elseif (file_exists($webRoot . "/{$basePath}/original.webp")) {
                return "/{$basePath}/original.webp";
            }
        } else {
            // thumbnail
            if (file_exists($webRoot . "/{$basePath}/thumb.jpg")) {
                return "/{$basePath}/thumb.jpg";
            } elseif (file_exists($webRoot . "/{$basePath}/thumb.png")) {
                return "/{$basePath}/thumb.png";
            } elseif (file_exists($webRoot . "/{$basePath}/thumb.webp")) {
                return "/{$basePath}/thumb.webp";
            }
        }

        return "/{$basePath}/{$size}.jpg"; // fallback
    }

    public static function getThumbUrl(int $id, string $entity): string
    {
        return self::getUrl($id, $entity, 'thumb');
    }

    public static function saveImage(UploadedFile $file, int $id, string $entity): void
    {
        $dir = \Yii::getAlias('@frontend/web/' . self::getUploadPath($id, $entity));
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $extension = strtolower($file->extension);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            $extension = 'jpg'; // default
        }

        $originalPath = $dir . '/original.' . $extension;
        $thumbPath = $dir . '/thumb.jpg'; // thumbnail всегда JPG для экономии

        $file->saveAs($originalPath);

        // Генерируем thumbnail
        $image = Image::thumbnail($originalPath, 300, 300);
        $image->save($thumbPath, ['quality' => 80]);
    }

    public static function deleteImage(int $id, string $entity): void
    {
        $dir = \Yii::getAlias('@frontend/web/' . self::getUploadPath($id, $entity));
        if (is_dir($dir)) {
            array_map('unlink', glob("$dir/*.*"));
            rmdir($dir);
        }
    }
}