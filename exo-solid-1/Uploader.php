<?php

class Uploader
{
    public $directory = '';
    public $validTypes = [];
    private $info;

    public function getInfo(){
        return $this->info;
    }

    public function __construct($file)
    {
        $this->info = new FileInformation();
        $fileData = $_FILES[$file];
        $this->info->setTemporaryName($fileData['tmp_name']);
        $this->info->setName($fileData['name']);
        $this->info->setType($fileData['type']);
        $this->validTypes = ['PNG', 'png', 'jpeg', 'jpg', 'JPG'];
        

    }

    public function uploadFile()
    {
        if (!in_array($this->info->getType(), $this->validTypes)) {
            $this->info->setError('Le fichier ' . $this->info->getName() . ' n\'est pas d\'un type valide');

            return false;
        } else {
            return true;
        }
    }

}

class Resize {
    private $getExtention;
    public function __construct() {
        $this->getExtention = new FileInformation();
    }

    public function resize($origin, $destination, $width, $maxHeight)
    {
        $type = $this->getExtention->getExtension();
        $pngFamily = ['PNG', 'png'];
        $jpegFamily = ['jpeg', 'jpg', 'JPG'];
        if (in_array($type, $jpegFamily)) {
            $type = 'jpeg';
        } elseif (in_array($type, $pngFamily)) {
            $type = 'png';
        }
        $function = 'imagecreatefrom' . $type;

        if (!is_callable($function)) {
            return false;
        }

        $image = $function($origin);

        $imageWidth = \imagesx($image);
        if ($imageWidth < $width) {
            if (!copy($origin, $destination)) {
                throw new Exception("Impossible de copier le fichier {$origin} vers {$destination}");
            }
        } else {
            $imageHeight = \imagesy($image);
            $height = (int) (($width * $imageHeight) / $imageWidth);
            if ($height > $maxHeight) {
                $height = $maxHeight;
                $width = (int) (($height * $imageWidth) / $imageHeight);
            }
            $newImage = \imagecreatetruecolor($width, $height);

            if ($newImage !== false) {
                \imagecopyresampled($newImage, $image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);

                $function = 'image' . $type;

                if (!is_callable($function)) {
                    return false;
                }

                $function($newImage, $destination);

                \imagedestroy($newImage);
                \imagedestroy($image);
            }
        }
    }

}

class FileInformation {
    private $name;
    private $temporaryName;
    private $type;
    private $error;
    public function __construct(){
        $this->name = "name";
    }
    public function getExtension()
    {
        return pathinfo($this->name, PATHINFO_EXTENSION);
    }
    public function setName($name)
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getName()
    {
        return $this->name;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function setTemporaryName($tempName)
    {
        $this->type = $tempName;
    }
    public function setError($e)
    {
        $this->error = $e;
    }
    public function getError()
    {
        return $this->error;
    }
}
