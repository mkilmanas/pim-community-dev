<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Elasticsearch\Filter\Field;

use Pim\Component\Catalog\Query\Filter\FieldFilterInterface;

/**
 * Filter in/complete product model and product depending their completeness. This filter mixes
 * CompletenessFilter and CompleteFilter to display result in the datagrid.
 *
 * The supported operator are:
 *   - COMPLETE
 *   - INCOMPLETE
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessFilter extends AbstractFieldFilter implements FieldFilterInterface
{
    /**
     * @param array $supportedFields
     * @param array $supportedOperators
     */
    public function __construct(array $supportedFields = [], array $supportedOperators = [])
    {
        $this->supportedFields = $supportedFields;
        $this->supportedOperators = $supportedOperators;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value, $locale = null, $channel = null, $options = [])
    {
        if (empty($locale)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid locale', static::class);
        }

        if (empty($channel)) {
            throw InvalidPropertyException::dataExpected('completeness', 'a valid channel', static::class);
        }
    }
}
