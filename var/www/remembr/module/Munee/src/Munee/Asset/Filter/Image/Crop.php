<?php

namespace Munee\Asset\Filter\Image;

use Munee\Asset\Filter;
use \Imagine\Image\Box;
use \Imagine\Image\Point;

class Crop extends Filter {
    /**
     * List of allowed params for this particular filter
     *
     * @var array
     */
    protected $allowedParams = array(
        'crop' => array(
            'arguments' => array(
                'x' => array(
                    'regex' => '\d+',
                    'cast' => 'integer'
                ),
                'y' => array(
                    'regex' => '\d+',
                    'cast' => 'integer'
                ),
                'width' => array(
                    'regex' => '\d+',
                    'cast' => 'integer'
                ),
                'height' => array(
                    'regex' => '\d+',
                    'cast' => 'integer'
                )
            )
        )
    );

    /**
     * Use Imagine to crop an image and do not return it's new path
     *
     * @param string $originalImage
     * @param array $arguments
     * @param array $imageOptions
     *
     * @return void
     *
     * @throws ErrorException
     */
    public function doFilter($originalImage, $arguments, $imageOptions)
    {
        switch (strtolower($imageOptions['imageProcessor'])) {
            case 'gd':
                $Imagine = new \Imagine\Gd\Imagine();
                break;
            case 'imagick':
                $Imagine = new \Imagine\Imagick\Imagine();
                break;
            case 'gmagick':
                $Imagine = new \Imagine\Gmagick\Imagine();
                break;
            default:
                throw new ErrorException('Unsupported imageProcessor config value: ' . $imageOptions['imageProcessor']);
        }
        $image = $Imagine->open($originalImage);

        $size = $image->getSize();
        $width= empty($arguments['width']) ? $size->getWidth() : $arguments['width'];
        $height = empty($arguments['height']) ? $size->getHeight() : $arguments['height'];
        $x= empty($arguments['x']) ? 0 : $arguments['x'];
        $y = empty($arguments['y']) ? 0 : $arguments['y'];
        
        if ($width > $imageOptions['maxAllowedResizeWidth']) {
            $width = $imageOptions['maxAllowedResizeWidth'];
        }

        if ($height > $imageOptions['maxAllowedResizeHeight']) {
            $height = $imageOptions['maxAllowedResizeHeight'];
        }

        $newImage = $image->crop(new Point($x, $y), new Box($width, $height));
        $newImage->save($originalImage, array('jpeg_quality' => $arguments['quality']));
    }
}
