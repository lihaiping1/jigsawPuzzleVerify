<?php

declare(strict_types=1);

namespace Lhp\JigsawPuzzleVerify;


/**
 * 图片管理
 * @package Lhp\JigsawPuzzleVerify
 */
class Image
{
    /**
     * 获取随机的一张背景图片
     * @return string
     */
    public function getRandomBackground(): string
    {
        return dirname(__FILE__).'/asset/background/background_'.rand(1, 10).'.png';
    }

    /**
     * 获取凹面选择图片
     * @return string
     */
    public function getConcaveImage(): string
    {
        return dirname(__FILE__).'/asset/concave/concave1.png';
    }

    /**
     * 截取图片
     */
    protected function interceptFormBackground(): array
    {
        //选取图片
        $concaveImageSrc = $this->getConcaveImage();
        list($concaveWidth, $concaveHeight) = getimagesize($concaveImageSrc);
        $_concaveImage = imagecreatefrompng($concaveImageSrc);
        $concaveImage = imagecreatetruecolor($concaveWidth, $concaveHeight);
        imagesavealpha($concaveImage, true);
        $concaveColor = imagecolorallocatealpha($_concaveImage, 255, 0, 0, 127);
        imagefill($concaveImage, 0, 0, $concaveColor);
        
        //背景图片
        $backgroundImageSrc = $this->getRandomBackground();
        list($backgroundWidth, $backgroundHeight) = getimagesize($backgroundImageSrc);
        $backgroundImage = imagecreatefrompng($backgroundImageSrc);
        
        $postionMaxX = $backgroundWidth - $concaveWidth - 20;
        $postionMaxY = $backgroundHeight - $concaveHeight - 20;
        
        //随机获取坐标
        $postionXStart = rand(20, $postionMaxX);
        $postionXEnd = $postionXStart + $concaveWidth;
        $postionYStart = rand(20, $postionMaxY);
        $postionYEnd = $postionYStart + $concaveHeight;
        for ($x = $postionXStart; $x < $postionXEnd; $x++) {
            for ($y = $postionYStart; $y < $postionYEnd; $y++) {
                $_backgroundColor = imagecolorat($backgroundImage, $x, $y);
                $_concaveColor = null;
                
                //判断索引值区分具体的遮盖区域
                $concaveImageX = $x - $postionXStart;
                $concaveImageY = $y - $postionYStart;
                if(imagecolorat($_concaveImage, $concaveImageX, $concaveImageY) == 0){
                    $_concaveColor = imagecolorat($_concaveImage, $concaveImageX, $concaveImageY);
                    imagesetpixel($concaveImage, $concaveImageX, $concaveImageY, $_backgroundColor);
                }
                
                if ($_concaveColor === null) {
                    continue;
                }
               
                $_backgroundColor = imagecolorallocatealpha($backgroundImage, 192, 192, 192, 45);
                imagesetpixel($backgroundImage, $x, $y, $_backgroundColor);
            }
        }
        
        ob_start();
        imagepng($concaveImage);
        $concaveImageData = ob_get_contents();
        ob_end_clean();
        
        ob_start();
        imagepng($backgroundImage);
        $backgroundImageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($_concaveImage);
        imagedestroy($concaveImage);
        imagedestroy($backgroundImage);
        
        return [
            'concave' => 'data:image/png;base64,'.base64_encode($concaveImageData),
            'background' => 'data:image/png;base64,'.base64_encode($backgroundImageData),
            'concavePosition' => [
                $postionXStart,
                $postionYStart
            ]
        ];
    }

    /**
     * 生成一张图片
     * @return array
     */
    public function createImage(): array
    {
        return $this->interceptFormBackground();
    }
}