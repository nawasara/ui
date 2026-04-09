<?php

namespace Nawasara\Ui\Traits;

trait HasFilter
{
    public function scopeFilter($query, $term = [])
    {
        // $term = "%$term%";

        // $query->where(function ($query) use ($term) {
        //     $query->where('name', 'like', $term)
        //           ->orWhere('email', 'like', $term)
        //           ->orWhere('username', 'like', $term);
        // });
    }

    public function scopeOrderByDefault($q)
    {
        $q->orderBy('name');
    }

    public function scopeRenderRoles($query)
    {
        return $this->roles->pluck('name')->map(function ($role) {
            return "<span class='inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 mr-1'>{$role}</span>";
        })->implode(' ');
    }
}