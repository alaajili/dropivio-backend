<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\User;
use App\Exception\Handler\ControllerExceptionHandlerInterface;
use App\Factory\ProductDtoFactoryInterface;
use App\Service\Product\ProductServiceInterface;
use App\Service\Response\JsonResponseBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Repository\ProductRepository;
use App\Entity\Product;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly ProductServiceInterface $productService,
        private readonly ProductDtoFactoryInterface $productDtoFactory,
        private readonly JsonResponseBuilderInterface $responseBuilder,
        private readonly ControllerExceptionHandlerInterface $exceptionHandler
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $page = max(1, $request->query->getInt('page', 1));
            $limit = min(100, max(1, $request->query->getInt('limit', 20)));

            $products = $this->productService->getProducts($page, $limit);
            $total = $this->productService->getProductsCount();

            return $this->responseBuilder->buildPaginatedResponse($products, $page, $limit, $total);
        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->productService->getProductById($id);
            
            if ($product === null) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }
            
            return $this->responseBuilder->buildProductResponse($product);
        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $productDto = $this->productDtoFactory->createFromRequest($request);
            $product = $this->productService->createProduct($productDto, $user);

            return $this->responseBuilder->buildProductResponse($product, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request): JsonResponse
    {
        try {
            // We still need the entity for permission checks and updates
            $productEntity = $this->getProductEntity($id);
            if ($productEntity === null) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }
            
            /** @var User $user */
            $user = $this->getUser();
            
            $this->productService->checkUpdatePermission($productEntity, $user);
            
            $productDto = $this->productDtoFactory->createUpdateFromRequest($request);
            $updatedProduct = $this->productService->updateProduct($productEntity, $productDto);

            return $this->responseBuilder->buildProductResponse($updatedProduct);
        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        try {
            // We still need the entity for permission checks and deletion
            $productEntity = $this->getProductEntity($id);
            if ($productEntity === null) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }
            
            /** @var User $user */
            $user = $this->getUser();
            
            $this->productService->checkDeletePermission($productEntity, $user);
            $this->productService->deleteProduct($productEntity);

            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        } catch (\Exception $e) {
            return $this->exceptionHandler->handle($e);
        }
    }

    /**
     * Helper method to get Product entity directly from repository
     * This is needed for operations that require the actual entity
     */
    private function getProductEntity(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }
}
