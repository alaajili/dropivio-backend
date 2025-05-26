<?php

/*
 * This file is part of the Dropivio company.
 * (c) Dropivio <it@dropivio.com>
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Dto\ProductCreateDto;
use App\Entity\User;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/products', name: 'api_products_')]
class ProductController extends AbstractController
{
    public function __construct(
        private readonly ProductService $productService,
        private readonly ProductRepository $productRepository,
        private readonly SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 20)));
        $offset = ($page - 1) * $limit;

        $products = $this->productRepository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
        $total = $this->productRepository->count([]);

        $data = [
            'products' => json_decode($this->serializer->serialize($products, 'json', ['groups' => ['product:read']])),
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => ceil($total / $limit)
            ]
        ];

        return new JsonResponse($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        $data = json_decode($this->serializer->serialize($product, 'json', ['groups' => ['product:read']]));
        return new JsonResponse($data);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $productDto = $this->createProductDtoFromRequest($request);
            $product = $this->productService->createProduct($productDto, $user);

            $data = json_decode($this->serializer->serialize($product, 'json', ['groups' => ['product:read']]));
            return new JsonResponse($data, Response::HTTP_CREATED);

        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while creating the product'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{id}', name: 'update', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function update(int $id, Request $request): JsonResponse
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user can update this product
        if (!$this->isGranted('ROLE_ADMIN') && $product->getSeller() !== $user) {
            throw new AccessDeniedHttpException('You can only update your own products');
        }

        try {
            $productDto = $this->createProductDtoFromRequest($request);
            $updatedProduct = $this->productService->updateProduct($product, $productDto);

            $data = json_decode($this->serializer->serialize($updatedProduct, 'json', ['groups' => ['product:read']]));
            return new JsonResponse($data);

        } catch (BadRequestHttpException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while updating the product'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
    public function delete(int $id): JsonResponse
    {
        $product = $this->productRepository->find($id);
        
        if (!$product) {
            throw new NotFoundHttpException('Product not found');
        }

        /** @var User $user */
        $user = $this->getUser();
        
        // Check if user can delete this product
        if (!$this->isGranted('ROLE_ADMIN') && $product->getSeller() !== $user) {
            throw new AccessDeniedHttpException('You can only delete your own products');
        }

        try {
            $this->productService->deleteProduct($product);
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);

        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'An error occurred while deleting the product'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function createProductDtoFromRequest(Request $request): ProductCreateDto
    {
        $title = $request->request->get('title');
        $shortDescription = $request->request->get('shortDescription');
        $description = $request->request->get('description');
        $about = $request->request->get('about');
        $price = $request->request->get('price');
        $categoryId = $request->request->get('categoryId');

        // Validate required fields
        if (empty($title)) {
            throw new BadRequestHttpException('Title is required');
        }
        if (empty($shortDescription)) {
            throw new BadRequestHttpException('Short description is required');
        }
        if (empty($description)) {
            throw new BadRequestHttpException('Description is required');
        }
        if (empty($price) || !is_numeric($price)) {
            throw new BadRequestHttpException('Valid price is required');
        }
        if (empty($categoryId) || !is_numeric($categoryId)) {
            throw new BadRequestHttpException('Valid category ID is required');
        }

        $thumbnailFile = $request->files->get('thumbnailFile');
        $productFile = $request->files->get('productFile');

        return new ProductCreateDto(
            title: $title,
            shortDescription: $shortDescription,
            description: $description,
            about: $about,
            price: (float) $price,
            categoryId: (int) $categoryId,
            thumbnailFile: $thumbnailFile,
            productFile: $productFile
        );
    }
}
