<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\ProductModel\Query;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Pim\Bundle\CatalogBundle\tests\fixture\EntityBuilder;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessGridFilterIntegration extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    public function testThatItFindsTheIncompleteVariantProductForARootProductModelWithOneLevel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('root_product_model_two_level');

        $result = $this->get('pim_catalog.doctrine.query.completeness_grid_filter')
            ->findNormalizedData($productModel);

        $this->assertEquals(
            [
                'at_least_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 1,
                        'zh_CN' => 1,
                    ],
                    'tablet' => [
                        'de_DE' => 0,
                        'en_US' => 1,
                        'fr_FR' => 1,
                    ],
                ],
                'at_least_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 0,
                        'zh_CN' => 0,
                    ],
                    'tablet' => [
                        'de_DE' => 1,
                        'en_US' => 1,
                        'fr_FR' => 0,
                    ],
                ],
            ],
            $result->value()
        );
    }

    public function testThatItFindsTheIncompleteVariantProductForARootProductModelWithTwoLevel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('sub_product_model');

        $result = $this->get('pim_catalog.doctrine.query.completeness_grid_filter')
            ->findNormalizedData($productModel);

        $this->assertEquals(
            [
                'at_least_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 1,
                        'zh_CN' => 1,
                    ],
                    'tablet' => [
                        'de_DE' => 0,
                        'en_US' => 1,
                        'fr_FR' => 1,
                    ],
                ],
                'at_least_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 0,
                        'zh_CN' => 0,
                    ],
                    'tablet' => [
                        'de_DE' => 1,
                        'en_US' => 1,
                        'fr_FR' => 0,
                    ],
                ],
            ],
            $result->value()
        );
    }

    public function testThatItFindsTheCompleteVariantProductForASubProductModel()
    {
        $productModel = $this->get('pim_catalog.repository.product_model')
            ->findOneByIdentifier('root_product_model_one_level');

        $result = $this->get('pim_catalog.doctrine.query.completeness_grid_filter')
            ->findNormalizedData($productModel);

        $this->assertEquals(
            [
                'at_least_complete' => [
                    'ecommerce' => [
                        'en_US' => 1,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 1,
                        'zh_CN' => 1,
                    ],
                    'tablet' => [
                        'de_DE' => 0,
                        'en_US' => 1,
                        'fr_FR' => 1,
                    ],
                ],
                'at_least_incomplete' => [
                    'ecommerce' => [
                        'en_US' => 0,
                    ],
                    'ecommerce_china' => [
                        'en_US' => 0,
                        'zh_CN' => 0,
                    ],
                    'tablet' => [
                        'de_DE' => 1,
                        'en_US' => 1,
                        'fr_FR' => 0,
                    ],
                ],
            ],
            $result->value()
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getTechnicalCatalogPath()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function loadFixtures(): void
    {
        $builder = new EntityBuilder(static::$kernel->getContainer());

        $builder->createFamilyVariant(
            [
                'code' => 'two_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text'],
                    ],
                    [
                        'level' => 2,
                        'axes' => ['a_yes_no'],
                        'attributes' => ['sku', 'a_localized_and_scopable_text_area'],
                    ],
                ],
            ]
        );
        
        $builder->createFamilyVariant(
            [
                'code' => 'one_level_family_variant',
                'family' => 'familyA3',
                'variant_attribute_sets' => [
                    [
                        'level' => 1,
                        'axes' => ['a_simple_select'],
                        'attributes' => ['a_text', 'sku', 'a_localized_and_scopable_text_area', 'a_yes_no'],
                    ],
                ],
            ]
        );

        $rootProductModel = $builder->createProductModel(
            'root_product_model_two_level',
            'two_level_family_variant',
            null,
            []
        );

        $subProductModel = $builder->createProductModel(
            'sub_product_model',
            'two_level_family_variant',
            $rootProductModel,
            [
                'values' => [
                    'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                ],
            ]
        );

        $builder->createVariantProduct(
            'variant_product_1',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_1', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $builder->createVariantProduct(
            'variant_product_2',
            'familyA3',
            'two_level_family_variant',
            $subProductModel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_2', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $rootProductModelOneLevel = $builder->createProductModel(
            'root_product_model_one_level',
            'one_level_family_variant',
            null,
            []
        );

        $builder->createVariantProduct(
            'variant_product_3',
            'familyA3',
            'one_level_family_variant',
            $rootProductModelOneLevel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_3', 'locale' => null, 'scope' => null]],
                    'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );

        $builder->createVariantProduct(
            'variant_product_4',
            'familyA3',
            'one_level_family_variant',
            $rootProductModelOneLevel,
            [
                'values' => [
                    'sku' => [['data' => 'variant_product_4', 'locale' => null, 'scope' => null]],
                    'a_simple_select' => [['data' => 'optionA', 'locale' => null, 'scope' => null]],
                    'a_text' => [['data' => 'text', 'locale' => null, 'scope' => null]],
                    'a_yes_no' => [['data' => '12345678', 'locale' => null, 'scope' => null]],
                    'a_localized_and_scopable_text_area' => [
                        ['data' => 'my text', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                        ['data' => null, 'locale' => 'en_US', 'scope' => 'tablet'],
                        ['data' => null, 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                        ['data' => 'my text', 'locale' => 'fr_FR', 'scope' => 'tablet'],
                    ],
                ],
            ]
        );
    }
}