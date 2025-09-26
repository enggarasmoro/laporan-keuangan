<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

abstract class BaseRepository
{
    protected $model;
    protected $statusColumn = 'is_active'; // Default status column name

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get the status column name for this model
     */
    protected function getStatusColumn(): string
    {
        return $this->statusColumn;
    }

    /**
     * Get all records for current user
     */
    public function getAllForUser(int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)->get();
    }

    /**
     * Find record by ID for current user
     */
    public function findForUser(int $id, int $userId = null): ?Model
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('id', $id)->where('user_id', $userId)->first();
    }

    /**
     * Create new record for current user
     */
    public function create(array $data): Model
    {
        $data['user_id'] = $data['user_id'] ?? Auth::id();
        return $this->model->create($data);
    }

    /**
     * Update record for current user
     */
    public function update(int $id, array $data, int $userId = null): bool
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('id', $id)->where('user_id', $userId)->update($data);
    }

    /**
     * Delete record for current user
     */
    public function delete(int $id, int $userId = null): bool
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('id', $id)->where('user_id', $userId)->delete();
    }

    /**
     * Get paginated records for current user
     */
    public function getPaginated(int $perPage = 15, int $userId = null): LengthAwarePaginator
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)->paginate($perPage);
    }

    /**
     * Get active records for current user
     */
    public function getActiveForUser(int $userId = null): Collection
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)
                          ->where($this->getStatusColumn(), true)
                          ->get();
    }

    /**
     * Toggle status for record
     */
    public function toggleStatus(int $id, int $userId = null): bool
    {
        $userId = $userId ?: Auth::id();
        $record = $this->findForUser($id, $userId);

        if (!$record) {
            return false;
        }

        $statusColumn = $this->getStatusColumn();
        return $record->update([$statusColumn => !$record->$statusColumn]);
    }

    /**
     * Bulk delete records for current user
     */
    public function bulkDelete(array $ids, int $userId = null): int
    {
        $userId = $userId ?: Auth::id();
        return $this->model->whereIn('id', $ids)
                          ->where('user_id', $userId)
                          ->delete();
    }

    /**
     * Count records for current user
     */
    public function countForUser(int $userId = null): int
    {
        $userId = $userId ?: Auth::id();
        return $this->model->where('user_id', $userId)->count();
    }
}
