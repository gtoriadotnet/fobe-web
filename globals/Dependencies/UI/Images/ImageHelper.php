<?php

namespace Alphaland\UI {

    use GdImage;

    class ImageHelper
    {
        public static function CopyMergeImageAlpha(GdImage $dst_image, GdImage $src_image, int $dst_x, int $dst_y, int $src_x, int $src_y, int $src_w, int $src_h, int $pct): void
        {
            $img = imagecreatetruecolor($src_w, $src_h);
            imagecopy($img, $dst_image, 0, 0, $dst_x, $dst_y, $src_w, $src_h);
            imagecopy($img, $src_image, 0, 0, $src_x, $src_y, $src_w, $src_h);
            imagecopymerge($dst_image, $img, $dst_x, $dst_y, 0, 0, $src_w, $src_h, $pct);
        }

        public static function IsBase64PNGImage(string $base64): bool
        {
            $mime = finfo_buffer(finfo_open(), $base64, FILEINFO_MIME_TYPE);

            if (in_array($mime, array("image/png"))) {
                return true;
            }

            return false;
        }

        public static function ResizeImageFromString(int $newWidth, int $newHeight, string $targetFile, string $originalFile): bool
        {
            $img = imagecreatefromstring($originalFile);
            $width = imagesx($img);
            $height = imagesy($img);
            $tmp = imagecreatetruecolor($newWidth, $newHeight);
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            if (imagepng($tmp, "$targetFile")) {
                return true;
            }

            return false;
        }
    }
}
