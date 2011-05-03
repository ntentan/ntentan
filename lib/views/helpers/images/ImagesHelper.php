<?php
namespace ntentan\views\helpers\images;

use ntentan\views\helpers\Helper;

class ImagesHelper extends Helper
{
    /**
     * Resizes an image
     * @param string $src Path to the image to be resized
     * @param string $dest Path to sore the resized image
     * @param integer $width New width of the image
     * @param integer $height New Height of the image
     */
    public function resize($src,$dest,$width,$height)
    {
        $im = imagecreatefromjpeg($src);
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

        @imagejpeg($dest_im, $dest, 90);
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
        $im = imagecreatefromjpeg($src);
        $o_width = imagesx($im);
        $o_height = imagesy($im);
        if($head==false) $top = ($o_height/2)-($height/2); else $top=0;
        $left = ($o_width/2)-($width/2);
        $im2 = imagecreatetruecolor ($width, $height);

        imagecopyresampled($im2,$im,0,0,$left,$top,$width,$height,$width,$height);
        imagejpeg($im2, $dest, 90);
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
            $image = imagecreatefromjpeg($source);
            $imageWidth = imagesx($image);
            $imageHeight = imagesy($image);
            imagedestroy($image);
            $tempImage = 'cache/' . uniqid() . ".jpeg";

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
        }
        return $destination;
    }

    public function help($arguments)
    {
        
    }
}
