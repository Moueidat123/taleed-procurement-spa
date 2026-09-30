<?php

declare(strict_types=1);

namespace League\Glide\Manipulators;

use Intervention\Image\Direction;
use Intervention\Image\Interfaces\ImageInterface;

class Orientation extends BaseManipulator
{
    public function getApiParams(): array
    {
        return ['or'];
    }

    /**
     * Perform orientation image manipulation.
     *
     * @param ImageInterface $image The source image.
     *
     * @return ImageInterface The manipulated image.
     */
    public function run(ImageInterface $image): ImageInterface
    {
        $orientation = $this->getOrientation();

        if ($orientation === 'auto') {
            return match ($image->exif('Orientation')) {
                2 => $image->flip(Direction::VERTICAL),
                3 => $image->rotate(180),
                4 => $image->rotate(180)->flip(Direction::VERTICAL),
                5 => $image->rotate(270)->flip(Direction::VERTICAL),
                6 => $image->rotate(270),
                7 => $image->rotate(90)->flip(Direction::VERTICAL),
                8 => $image->rotate(90),
                default => $image,
            };
        }

        return $image->rotate((float) $orientation);
    }

    /**
     * Resolve orientation.
     *
     * @return string The resolved orientation.
     */
    public function getOrientation(): string
    {
        $or = (string) $this->getParam('or');

        if (in_array($or, ['0', '90', '180', '270'], true)) {
            return $or;
        }

        return 'auto';
    }
}
