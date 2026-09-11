<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryCollection;
use App\Http\Resources\CategoryResource;
use App\Http\Responses\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Category::class;
    }

    protected function resourceClass(): string
    {
        return CategoryResource::class;
    }

    protected function collectionClass(): string
    {
        return CategoryCollection::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreCategoryRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateCategoryRequest::class;
    }

    protected function searchableColumns(): array
    {
        return ['name'];
    }

    protected function defaultRelations(): array
    {
        return ['catalogues'];
    }

    protected function filterableColumns(): array
    {
        return [
            // Belum ada yang benar-benar berguna sebagai filter
            // kecuali nanti ditambah status, gender, department_id, dll.
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        // Adding an empty, unreachable validation signature forces Scramble's
        // static analyzer to extract the body fields automatically.
        if (false) {
            $request->validate((new StoreCategoryRequest())->rules());
        }

        return parent::store($request);
    }

    /**
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        // Forces Scramble to extract PUT/PATCH body parameters automatically
        if (false) {
            $request->validate((new UpdateCategoryRequest())->rules());
        }

        return parent::update($request, $id);
    }
}