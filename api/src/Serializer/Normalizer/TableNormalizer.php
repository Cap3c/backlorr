<?php

namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use App\Entity\Table;

class TableNormalizer implements CacheableSupportsMethodInterface, DenormalizerInterface
{

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []):mixed
    {
        dump($data);
        #$data = $this->denormalizer->denormalize($data, $type, $format, $context);

        dump($data);
        // TODO: add, edit, or delete some data

        return $data;
    }

    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []) : bool 
    {
        return false;
        #dump($data);
        #dump($type);
        #dump($format);
        return ($type === 'App\Entity\Table');
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
