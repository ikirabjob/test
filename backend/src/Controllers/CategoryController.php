<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Category;
use App\Middleware\AuthMiddleware;
use App\Utils\Response;

final class CategoryController
{
    private Category $categoryModel;
    private AuthMiddleware $authMiddleware;

    public function __construct() {
        $this->categoryModel = new Category();
        $this->authMiddleware = new AuthMiddleware();
    }

    public function getCategories(): void {
        $categories = $this->categoryModel->findAll();
        Response::success(['categories' => $categories]);
    }

    /**
     * @throws \JsonException
     */
    public function getCategory(int $id): void {
        $category = $this->categoryModel->findById($id);

        if ($category === null) {
            Response::error('Category not found', 404);
        }

        Response::success(['category' => $category]);
    }

    /**
     * @throws \JsonException
     */
    public function createCategory(): void {
        $payload = $this->authMiddleware->requireAdmin();

        if ($payload === null) {
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data['name'])) {
            Response::error('Category name is required');
        }

        $categoryData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null
        ];

        $categoryId = $this->categoryModel->create($categoryData);

        if (!$categoryId) {
            Response::error('Failed to create category', 500);
        }

        $category = $this->categoryModel->findById($categoryId);
        Response::success(['category' => $category], 201);
    }

    /**
     * @throws \JsonException
     */
    public function updateCategory(int $id): void {
        $payload = $this->authMiddleware->requireAdmin();

        if ($payload === null) {
            return;
        }

        $category = $this->categoryModel->findById($id);

        if ($category === null) {
            Response::error('Category not found', 404);
        }

        $data = json_decode(file_get_contents('php://input'), true, 512, JSON_THROW_ON_ERROR);

        if (empty($data['name'])) {
            Response::error('Category name is required');
        }

        $updateData = [
            'name' => $data['name'],
            'description' => $data['description'] ?? $category['description']
        ];

        $success = $this->categoryModel->update($id, $updateData);

        if (!$success) {
            Response::error('Failed to update category', 500);
        }

        $updatedCategory = $this->categoryModel->findById($id);
        Response::success(['category' => $updatedCategory]);
    }

    /**
     * @throws \JsonException
     */
    public function deleteCategory(int $id): void {
        $payload = $this->authMiddleware->requireAdmin();

        if ($payload === null) {
            return;
        }

        $category = $this->categoryModel->findById($id);

        if ($category === null) {
            Response::error('Category not found', 404);
        }

        $success = $this->categoryModel->delete($id);

        if (!$success) {
            Response::error('Failed to delete category', 500);
        }

        Response::success(['message' => 'Category deleted successfully']);
    }
}