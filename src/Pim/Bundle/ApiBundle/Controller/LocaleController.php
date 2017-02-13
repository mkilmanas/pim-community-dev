<?php

namespace Pim\Bundle\ApiBundle\Controller;

use Pim\Component\Api\Exception\PaginationParametersException;
use Pim\Component\Api\Pagination\HalPaginator;
use Pim\Component\Api\Pagination\ParameterValidatorInterface;
use Pim\Component\Api\Repository\ApiResourceRepositoryInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class LocaleController
{
    /** @var ApiResourceRepositoryInterface */
    protected $repository;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var HalPaginator */
    protected $paginator;

    /** @var ParameterValidatorInterface */
    protected $parameterValidator;

    /** @var array */
    protected $apiConfiguration;

    /** @var string[] */
    protected $authorizedFieldFilters = ['enabled'];

    /**
     * @param ApiResourceRepositoryInterface $repository
     * @param NormalizerInterface            $normalizer
     * @param HalPaginator                   $paginator
     * @param ParameterValidatorInterface    $parameterValidator
     * @param array                          $apiConfiguration
     */
    public function __construct(
        ApiResourceRepositoryInterface $repository,
        NormalizerInterface $normalizer,
        HalPaginator $paginator,
        ParameterValidatorInterface $parameterValidator,
        array $apiConfiguration
    ) {
        $this->repository = $repository;
        $this->normalizer = $normalizer;
        $this->paginator = $paginator;
        $this->parameterValidator = $parameterValidator;
        $this->apiConfiguration = $apiConfiguration;
    }

    /**
     * @param Request $request
     * @param string  $code
     *
     * @throws NotFoundHttpException
     *
     * @return JsonResponse
     */
    public function getAction(Request $request, $code)
    {
        $locale = $this->repository->findOneByIdentifier($code);
        if (null === $locale) {
            throw new NotFoundHttpException(sprintf('Locale "%s" does not exist.', $code));
        }

        $localeApi = $this->normalizer->normalize($locale, 'external_api');

        return new JsonResponse($localeApi);
    }

    /**
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @return JsonResponse
     */
    public function listAction(Request $request)
    {
        $criterias = $this->prepareSearchCriterias($request);

        $queryParameters = [
            'page'  => $request->query->get('page', 1),
            'limit' => $request->query->get('limit', $this->apiConfiguration['pagination']['limit_by_default']),
        ];

        try {
            $this->parameterValidator->validate($queryParameters);
        } catch (PaginationParametersException $e) {
            throw new UnprocessableEntityHttpException($e->getMessage(), $e);
        }

        $offset = $queryParameters['limit'] * ($queryParameters['page'] - 1);
        $locales = $this->repository->searchAfterOffset(
            $criterias,
            ['code' => 'ASC'],
            $queryParameters['limit'],
            $offset
        );

        $paginatedLocales = $this->paginator->paginate(
            $this->normalizer->normalize($locales, 'external_api'),
            array_merge($request->query->all(), $queryParameters),
            $this->repository->count($criterias),
            'pim_api_locale_list',
            'pim_api_locale_get',
            'code'
        );

        return new JsonResponse($paginatedLocales);
    }

    /**
     * Prepares criterias from search parameters
     * It throws exceptions if search parameters are not correctly filled
     * Only activated = filter is authorized today
     *
     * @param Request $request
     *
     * @throws UnprocessableEntityHttpException
     * @throws BadRequestHttpException
     * @return array
     */
    protected function prepareSearchCriterias(Request $request)
    {
        $criterias = [];
        if (false === $request->query->has('search')) {
            return $criterias;
        }
        $searchString = $request->query->get('search', '');
        $searchParameters = json_decode($searchString, true);

        if (null === $searchParameters) {
            throw new BadRequestHttpException('Search query parameter should be valid JSON.');
        }
        foreach ($searchParameters as $searchKey => $searchParameter) {
            if (0 === count($searchParameter)) {
                throw new UnprocessableEntityHttpException(
                    sprintf('Operator and value are missing for the property "%s".', $searchKey)
                );
            }

            foreach ($searchParameter as $searchOperator) {
                if (!isset($searchOperator['operator'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Operator is missing for the property "%s".', $searchKey)
                    );
                }
                if (!isset($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf('Value is missing for the property "%s".', $searchKey)
                    );
                }

                if (!in_array($searchKey, $this->authorizedFieldFilters) || '=' !== $searchOperator['operator']) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter on property "%s" is not supported or does not support operator "%s".',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }
                if (!is_bool($searchOperator['value'])) {
                    throw new UnprocessableEntityHttpException(
                        sprintf(
                            'Filter "%s" with operator "%s" expects a boolean value',
                            $searchKey,
                            $searchOperator['operator']
                        )
                    );
                }

                $criterias['activated'] = $searchOperator['value'];
            }
        }

        return $criterias;
    }
}
