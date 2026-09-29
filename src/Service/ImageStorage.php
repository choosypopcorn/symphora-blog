<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

class ImageStorage
{
    private $uploadDir = 'data/images';

    public function storeImage(UploadedFile $file)
    {
        $uuidName = Uuid::v4() . '_' . $file->getClientOriginalName();
        
        try {
            $file->move($this->uploadDir, $uuidName);
            return '/images/' . $uuidName; // URL path
        } catch (\Exception $e) {
            throw new \ServiceUnavailableHttpException(503); 
        }
    }
}
