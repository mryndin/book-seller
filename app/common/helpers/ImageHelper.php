<?php

namespace common\helpers;

use yii\imagine\Image;
use yii\web\UploadedFile;
use Yii;

class ImageHelper
{
    public const UPLOAD_ROOT = '/var/www/app/uploads';

    public static function getUploadPath(int $id, string $entity): string
    {
        $paddedId = str_pad((string)$id, 4, '0', STR_PAD_LEFT);
        $part1 = substr($paddedId, 0, 2);
        $part2 = substr($paddedId, 2, 2);
        return "{$entity}/{$part1}/{$part2}/{$id}";
    }

    public static function getUrl(int $id, string $entity, string $size = 'original'): string
    {
        $basePath = self::getUploadPath($id, $entity);
        $exts = ['jpg', 'png', 'gif', 'webp'];

        foreach ($exts as $ext) {
            if (file_exists(self::UPLOAD_ROOT . "/{$basePath}/{$size}.{$ext}")) {
                return "/uploads/{$basePath}/{$size}.{$ext}";
            }
        }
        return "/uploads/{$basePath}/{$size}.jpg";
    }

    public static function getThumbUrl(int $id, string $entity): string
    {
        return self::getUrl($id, $entity, 'thumb');
    }

    public static function saveImage(UploadedFile $file, int $id, string $entity): bool
    {
        $dir = self::UPLOAD_ROOT . '/' . self::getUploadPath($id, $entity);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                Yii::error("Не удалось создать директорию: $dir");
                return false;
            }
        }

        $extension = strtolower($file->extension);
        if ($extension === 'jpeg') $extension = 'jpg';
        if (!in_array($extension, ['jpg', 'png', 'gif', 'webp'])) {
            $extension = 'jpg';
        }

        $originalPath = $dir . '/original.' . $extension;
        $thumbPath = $dir . '/thumb.jpg';

        if (!$file->saveAs($originalPath)) {
            Yii::error("Не удалось сохранить файл: $originalPath");
            return false;
        }

        clearstatcache(true, $originalPath);

        try {
            $image = Image::thumbnail($originalPath, 300, 300);
            $image->save($thumbPath, ['quality' => 80]);
        } catch (\Exception $e) {
            Yii::error("Ошибка генерации thumbnail: " . $e->getMessage());
            if (file_exists($originalPath)) {
                copy($originalPath, $thumbPath);
            }
        }

        return true;
    }

    public static function deleteImage(int $id, string $entity): void
    {
        $dir = self::UPLOAD_ROOT . '/' . self::getUploadPath($id, $entity);
        if (is_dir($dir)) {
            array_map('unlink', glob("$dir/*.*"));
            rmdir($dir);
        }
    }
}