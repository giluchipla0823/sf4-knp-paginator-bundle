<?php

namespace App\Serializer\Exclusion;

use JMS\Serializer\Context;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

class RemoveFieldsListExclusionStrategy implements ExclusionStrategyInterface
{

    /**
     * @var array
     */
    private $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    public function shouldSkipClass(ClassMetadata $metadata, Context $context): bool
    {
//        if ($context->getDepth() == 1) {
//            return false;
//        }

        return false;
    }

    public function shouldSkipProperty(PropertyMetadata $property, Context $context): bool {
//        $this->fields = [
//            'title',
//        ];

        if (empty($this->fields)) {
            return false;
        }

//        $depth = $context->getDepth();
//        $metadataStack = $context->getMetadataStack();
//
//        $nthProperty = 0;
//        // iterate from the first added items to the lasts
//        for ($i = $metadataStack->count() - 1; $i > 0; $i--) {
//            $metadata = $metadataStack[$i];
//            if ($metadata instanceof PropertyMetadata) {
//                $metadata->maxDepth = 1;
//                $nthProperty++;
//                $relativeDepth = $depth - $nthProperty;
//
//                if (null !== $metadata->maxDepth && $relativeDepth > $metadata->maxDepth) {
//                    $metadata->skipWhenEmpty = false;
//                }
//            }
//        }

        $name = $property->serializedName ?: $property->name;

        return in_array($name, $this->fields);
    }
}