<?php

namespace Pim\Bundle\CatalogBundle\tests\assert;

use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

class AssertEntityWithValues
{
    /** @var array */
    private $exceptedEntityIdentifier;

    /** @var array */
    private $actualEntities;

    /**
     * @param EntityWithValuesInterface[] $exceptedEntityIdentifier
     * @param string[]                    $actualEntities
     */
    public function __construct(array $exceptedEntityIdentifier, array $actualEntities)
    {
        $this->exceptedEntityIdentifier = $exceptedEntityIdentifier;
        $this->actualEntities = $actualEntities;
    }

    /**
     * Check if $actualEntities contains the following products identifiers ($exceptedEntities)
     */
    public function sameProducts(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductInterface) {
                $id = $entity->getIdentifier();
            }

            return $id;
        });
    }

    /**
     * Check if $actualEntities contains the following product models code ($exceptedEntities)
     */
    public function sameProductModels(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductModelInterface) {
                $id = $entity->getCode();
            }

            return $id;
        });
    }

    /**
     * Check if $actualEntities contains the following product models / products $exceptedEntities
     */
    public function same(): void
    {
        $this->compareResult(function ($entity) {
            $id = null;
            if ($entity instanceof ProductInterface) {
                $id = $entity->getIdentifier();
            }

            if ($entity instanceof ProductModelInterface) {
                $id = $entity->getCode();
            }

            return $id;
        });
    }

    /**
     * Compare the result
     *
     * @param callable $function
     *
     * @throws \PHPUnit_Framework_ExpectationFailedException
     */
    private function compareResult(callable $function): void
    {
        $actualEntities = $this->actualEntities;
        $exceptedEntities = array_map($function, $this->exceptedEntityIdentifier);

        sort($actualEntities);
        sort($exceptedEntities);

        assertSame($exceptedEntities, $actualEntities);
    }
}