<?php

namespace App\Services\Providers;

use Illuminate\Pagination\Paginator;

class PaginationServiceProvider extends AbstractLaravelProvider
{
    public function register()
    {
        Paginator::currentPathResolver(function () {
            return rtrim(\Request::url(), '/');
        });

        Paginator::currentPageResolver(function ($pageName = 'page') {
            $page = \Request::get($pageName);

            if (filter_var($page, FILTER_VALIDATE_INT) !== false && (int) $page >= 1) {
                return (int) $page;
            }

            return 1;
        });
    }
}
