<?php

declare(strict_types=1);

namespace Letkode\DataProviderBundle\Controller;

use Letkode\DataProviderBundle\Exception\ProviderGroupNotFoundException;
use Letkode\DataProviderBundle\Exception\ProviderMethodNotAllowedException;
use Letkode\DataProviderBundle\Exception\ProviderNotFoundException;
use Letkode\DataProviderBundle\Registry\ProviderRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/providers')]
final class DataProviderController extends AbstractController
{
    public function __construct(
        private readonly ProviderRegistry $registry,
    ) {
    }

    #[Route('/{providerGroup}/{classAlias}/{methodAlias}', name: 'letkode_data_provider', methods: ['GET'])]
    public function __invoke(
        string $providerGroup,
        string $classAlias,
        string $methodAlias,
        Request $request,
    ): JsonResponse {
        try {
            $callable = $this->registry->resolve($providerGroup, $classAlias, $methodAlias);
        } catch (ProviderGroupNotFoundException|ProviderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        } catch (ProviderMethodNotAllowedException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e);
        }

        $params = $request->query->all();
        $result = empty($params) ? $callable() : $callable($params);

        return $this->json($result);
    }

    #[Route('/{providerGroup}', name: 'letkode_data_provider_group_map', methods: ['GET'])]
    public function group(string $providerGroup): JsonResponse
    {
        try {
            $map = $this->registry->getGroupMap($providerGroup);
        } catch (ProviderGroupNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage(), $e);
        }

        return $this->json($map);
    }
}
