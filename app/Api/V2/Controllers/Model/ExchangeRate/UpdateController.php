<?php

/*
 * DestroyController.php
 * Copyright (c) 2024 james@firefly-iii.org.
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see https://www.gnu.org/licenses/.
 */

declare(strict_types=1);

namespace FireflyIII\Api\V2\Controllers\Model\ExchangeRate;

use FireflyIII\Api\V2\Controllers\Controller;
use FireflyIII\Api\V2\Request\Model\ExchangeRate\UpdateRequest;
use FireflyIII\Models\CurrencyExchangeRate;
use FireflyIII\Repositories\UserGroups\ExchangeRate\ExchangeRateRepositoryInterface;
use FireflyIII\Support\Http\Api\ValidatesUserGroupTrait;
use FireflyIII\Transformers\V2\ExchangeRateTransformer;
use Illuminate\Http\JsonResponse;

class UpdateController extends Controller
{
    use ValidatesUserGroupTrait;

    public const string RESOURCE_KEY = 'exchange-rates';

    private ExchangeRateRepositoryInterface $repository;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(
            function ($request, $next) {
                $this->repository = app(ExchangeRateRepositoryInterface::class);
                $this->repository->setUserGroup($this->validateUserGroup($request));

                return $next($request);
            }
        );
    }

    public function update(UpdateRequest $request, CurrencyExchangeRate $exchangeRate): JsonResponse
    {
        $date         = $request->getDate();
        $rate         = $request->getRate();
        $exchangeRate = $this->repository->updateExchangeRate($exchangeRate, $rate, $date);
        $transformer  = new ExchangeRateTransformer();
        $transformer->setParameters($this->parameters);

        return response()
            ->api($this->jsonApiObject(self::RESOURCE_KEY, $exchangeRate, $transformer))
            ->header('Content-Type', self::CONTENT_TYPE)
        ;
    }
}
