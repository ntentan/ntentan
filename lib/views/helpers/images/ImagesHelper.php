<?php
namespace ntentan\views\helpers\images;

use ntentan\views\helpers\Helper;

/**
 * 
 * 
 * @todo Completely rewrite this helper to make it more efficient. Expose the
 * interfaces as they are. Allow for the specification of the output format.
 */
class ImagesHelper extends Helper
{
    private $quality = 90;
    
    private function loadImage($path)
    {
        switch(strtolower(end(explode(".", $path))))
        {
            case 'png':
                $image = imagecreatefrompng($path);
                break;

            case 'jpeg':
            case 'jpg':
                $image = imagecreatefromjpeg($path);
                break;
        }
        return $image;
    }
    
    private function writeImage($image, $path)
    {
        switch(strtolower(end(explode(".", $path))))
        {
            case 'png':
                $image = imagepng($image, $path);
                break;

            case 'jpeg':
            case 'jpg':
                $image = imagejpeg($image, $path, $this->quality);
                break;
        }
        return $image;        
    }
    
    /**
     * Resizes an image.
     * 
     * @param string $src Path to the image to be resized
     * @param string $dest Path to sore the resized image
     * @param integer $width New width of the image
     * @param integer $height New Height of the image
     */
    public function resize($src,$dest,$width,$height)
    {
        $im = $this->loadImage($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);

        $aspect = $o_width / $o_height;

        if($width<=0)
        {
            $width = $aspect * $height;
        }
        else if($height<=0)
        {
            $height = $width / $aspect;
        }

        $dest_im = imagecreatetruecolor($width, $height);
        imagecopyresampled($dest_im, $im, 0,0,0,0,$width,$height,$o_width,$o_height);

        $this->writeImage($dest_im, $dest);
        
        imagedestroy($im);
        imagedestroy($dest_im);

        return $dest;
    }

    /**
     * Crops an image. This function crops by fitting the image into the center
     * of a new cropping area. If the cropping area is smaller than the image
     * the image is scaled to fit.
     *
     * @param string $src The path to the source image
     * @param string $dest The path to the destination image
     * @param int $width The cropping width
     * @param int $height The cropping height
     * @param boolean $head Place the cropping area on top of the image
     */
    public function crop($src, $dest, $width, $height,$head=false)
    {
        $im = $this->loadImage($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);
        if($head==false) $top = ($o_height/2)-($height/2); else $top=0;
        $left = ($o_width/2)-($width/2);
        $im2 = imagecreatetruecolor ($width, $height);

        imagecopyresampled($im2,$im,0,0,$left,$top,$width,$height,$width,$height);
        $this->writeImage($im2, $dest);
        imagedestroy($im);
        imagedestroy($im2);
    }

    /**
     * A smart thumbnailing function. Generates thumbnail images without
     * distorting the output of the final image.
     * @param string $file
     * @param string $width
     * @param string $height
     * @param string $head
     * @return string
     */
    public function thumbnail($source, $destination, $width, $height, $head=false, $overwrite=false)
    {
        if(!is_file($source)) return;

        if(!is_file($destination) || (filectime($source)>filectime($destination)) || $overwrite === true)
        {
            $image = $this->loadImage($source);
            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);
            imagedestroy($image);
            $tempImage = 'cache/' . uniqid() . ".png";

            $aspect = $imageWidth / $imageHeight;

            if($aspect * $height >= $width)
            {
                $this->resize($source, $tempImage, 0, $height);
            }
            else
            {
                $this->resize($source, $tempImage, $width, 0);
            }

            $this->crop( $tempImage, $destination, $width, $height, $head);
            unlink($tempImage);
        }
        return $destination;
    }
    
    public function quality($quality)
    {
        return $this;
    }

    public function help($arguments)
    {

    }
}
