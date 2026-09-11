<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreCatalogueRequest;
use App\Http\Requests\UpdateCatalogueRequest;
use App\Http\Resources\CatalogueCollection;
use App\Http\Resources\CatalogueResource;
use App\Models\Catalogue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogueController extends BaseApiController
{
    protected function modelClass(): string
    {
        return Catalogue::class;
    }

    protected function resourceClass(): string
    {
        return CatalogueResource::class;
    }

    protected function collectionClass(): string
    {
        return CatalogueCollection::class;
    }

    protected function storeRequestClass(): string
    {
        return StoreCatalogueRequest::class;
    }

    protected function updateRequestClass(): string
    {
        return UpdateCatalogueRequest::class;
    }

    protected function searchableColumns(): array
    {
        return ['title'];
    }

    protected function defaultRelations(): array
    {
        return ['category', 'user'];
    }

    protected function filterableColumns(): array
    {
        return ['status', 'price'];
    }

    public function store(Request $request): JsonResponse
    {
        if (false) {
            $request->validate((new StoreCatalogueRequest())->rules());
        }

        return parent::store($request);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        if (false) {
            $request->validate((new UpdateCatalogueRequest())->rules());
        }

        return parent::update($request, $id);
    }
}