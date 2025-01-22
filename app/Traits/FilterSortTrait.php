<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait FilterSortTrait
{
    /**
     * Apply filters and sorting to a query builder instance.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function scopeFilterSort(Builder $query, Request $request): Builder
    {
        // Apply filters
        $filters = $request->input('filter', []);
        foreach ($filters as $field => $value) {
            if ($field === 'role_names') {
                $this->applyRoleFilter($query, $value);
            } else {
                $this->applyFilters($query, $field, $value);
            }
        }

        // Apply sorting
        $sorts = $request->input('sort', '');
        if (!empty($sorts)) {
            $sortFields = explode(',', $sorts);
            foreach ($sortFields as $sortField) {
                $sortDirection = Str::startsWith($sortField, '-') ? 'desc' : 'asc';
                $sortField     = ltrim($sortField, '-');

                if (strpos($sortField, '.') !== false) {
                    [$relation, $col] = explode('.', $sortField);

                    $table        = $query->getModel()->getTable();
                    $relatedTable = $query->getModel()->{$relation}()->getRelated()->getTable();

                    $query->join("{$relatedTable}", "{$relatedTable}.id_{$relatedTable}", '=', "{$table}.id_{$relatedTable}")->select("{$table}.*", "{$relatedTable}.{$col}")->orderBy("{$relatedTable}.{$col}", $sortDirection);
                } else {
                    $query->orderBy($sortField, $sortDirection);
                }
            }
        }

        return $query;
    }

    /**
     * Apply a search to a query builder instance based on specified columns.
     *
     * @param Builder $query
     * @param Request $request
     * @param array $columns
     * @return Builder
     */
    public function scopeSearch(Builder $query, Request $request, $columns = [])
    {
        $keyword = $request->query('search');
        $columns = !empty($columns) ? $columns : ['tahun', 'status'];

        if (!empty($keyword)) {
            $query->where(function ($query) use ($keyword, $columns) {
                foreach ($columns as $column) {
                    if (strpos($column, '.') !== false) {
                        // If the column is a relation
                        $relations     = explode('.', $column);
                        $finalColumn   = array_pop($relations);
                        $relationChain = implode('.', $relations);

                        $query->orWhereHas($relationChain, function ($query) use ($finalColumn, $keyword) {
                            $query->where($finalColumn, 'LIKE', "%$keyword%");
                        });
                    } else {
                        $currentTable = $query->getModel()->getTable();
                        $query->orWhere("{$currentTable}.{$column}", 'LIKE', "%$keyword%");
                    }
                }
            });
        }
    }
}
