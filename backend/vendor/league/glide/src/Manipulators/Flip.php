<?php

declare(strict_types=1);

namespace League\Glide\Manipulators;

use Intervention\Image\Direction;
use Intervention\Image\Interfaces\ImageInterface;

class Flip extends BaseManipulator
{
    public function getApiParams(): array
    {
        return ['flip'];
    }

    /**
     * Perform flip image manipulation.
     *
     * @param ImageInterface $image The source image.
     *
     * @return ImageInterface The manipulated image.
     */
    public function run(ImageInterface $image): ImageInterface
    {
        $flip = $this->getFlip();

        if ($flip !== null) {
            return match ($flip) {
                'both' => $image->flip(Direction::HORIZONTAL)->flip(Direction::VERTICAL),
                'v' => $image->flip(Direction::VERTICAL),
                'h' => $image->flip(Direction::HORIZONTAL),
                default => $image,
            };
        }

        return $image;
    }

    /**
     * Resolve flip.
     *
     * @return string|null The resolved flip.
     */
    public function getFlip(): ?string
    {
        $flip = $this->getParam('flip');

        if (in_array($flip, ['h', 'v', 'both'], true)) {
            return $flip;
        }

        return null;
    }
}
