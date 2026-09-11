<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

abstract class BaseApiController extends Controller
{
    abstract protected function modelClass(): string;

    abstract protected function resourceClass(): string;

    abstract protected function collectionClass(): string;

    abstract protected function storeRequestClass(): string;

    abstract protected function updateRequestClass(): string;

    /** @return array<int, string> */
    abstract protected function searchableColumns(): array;

    /** @return array<int, string> */
    abstract protected function defaultRelations(): array;

    /** @return array<int, string> */
    abstract protected function filterableColumns(): array;

    protected function resourceName(): string
    {
        return class_basename($this->modelClass());
    }

    public function __construct(
        protected readonly AuthFactory $auth,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            // Otomatis Cek Policy 'viewAny'
            Gate::authorize('viewAny', $this->modelClass());

            $query = $this->newQuery();

            if ($search = $request->string('search')->toString()) {
                $query->where(function (Builder $builder) use ($search): void {
                    foreach ($this->searchableColumns() as $column) {
                        $builder->orWhere($column, 'ilike', '%'.$search.'%');
                    }
                });
            }

            foreach ($request->query('filter', []) as $field => $value) {
                if (in_array($field, $this->filterableColumns(), true) && $value !== null && $value !== '') {
                    $query->where($field, $value);
                }
            }

            $records = $query
                ->with($this->defaultRelations())
                ->latest()
                ->paginate(15);

            $collectionClass = $this->collectionClass();

            return ApiResponse::paginated(
                new $collectionClass($records),
                $this->resourceName().' list retrieved'
            );
        } catch (ValidationException|AuthorizationException|ModelNotFoundException $exception) {
            throw $exception; // Lewatkan exception otorisasi/validasi ke Exception Handler Laravel
        } catch (\Throwable $exception) {
            Log::error($this->resourceName().' index failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to retrieve '.$this->resourceName().' records.', null, 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Otomatis Cek Policy 'create'
            Gate::authorize('create', $this->modelClass());

            $formRequestClass = $this->storeRequestClass();
            /** @var FormRequest $formRequest */
            $formRequest = $formRequestClass::createFrom($request);
            $formRequest->setContainer(app())->validateResolved();

            $modelClass = $this->modelClass();
            /** @var Model $record */
            $record = $modelClass::query()->create($formRequest->validated());
            $record->load($this->defaultRelations());

            $resourceClass = $this->resourceClass();

            return ApiResponse::success(
                new $resourceClass($record),
                $this->resourceName().' created successfully',
                201
            );
        } catch (ValidationException|AuthorizationException|ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error($this->resourceName().' store failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to create '.$this->resourceName().'.', null, 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $record = $this->findRecord($id);

            // Otomatis Cek Policy 'view'
            Gate::authorize('view', $record);

            $record->load($this->defaultRelations());

            $resourceClass = $this->resourceClass();

            return ApiResponse::success(
                new $resourceClass($record),
                $this->resourceName().' retrieved successfully'
            );
        } catch (ValidationException|AuthorizationException|ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error($this->resourceName().' show failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to retrieve '.$this->resourceName().'.', null, 500);
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $record = $this->findRecord($id);

            // Otomatis Cek Policy 'update'
            Gate::authorize('update', $record);

            $formRequestClass = $this->updateRequestClass();
            /** @var FormRequest $formRequest */
            $formRequest = $formRequestClass::createFrom($request);
            $formRequest->setContainer(app())->validateResolved();

            $record->update($formRequest->validated());
            $record->load($this->defaultRelations());

            $resourceClass = $this->resourceClass();

            return ApiResponse::success(
                new $resourceClass($record),
                $this->resourceName().' updated successfully'
            );
        } catch (ValidationException|AuthorizationException|ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error($this->resourceName().' update failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to update '.$this->resourceName().'.', null, 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $record = $this->findRecord($id);

            // Otomatis Cek Policy 'delete'
            Gate::authorize('delete', $record);

            if (method_exists($record, 'trashed') && $record->trashed()) {
                return ApiResponse::error($this->resourceName().' already deleted.', null, 404);
            }

            if (method_exists($record, 'delete')) {
                $record->delete();
            }

            return response()->json([
                'success' => true,
                'message' => $this->resourceName().' deleted successfully',
                'data' => null,
                'errors' => null,
            ], 204);
        } catch (ValidationException|AuthorizationException|ModelNotFoundException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            Log::error($this->resourceName().' destroy failed', ['exception' => $exception]);

            return ApiResponse::error('Unable to delete '.$this->resourceName().'.', null, 500);
        }
    }

    protected function newQuery(): Builder
    {
        $modelClass = $this->modelClass();

        return $modelClass::query();
    }

    protected function findRecord(string $id): Model
    {
        $query = $this->newQuery();

        return $query->findOrFail($id);
    }
}