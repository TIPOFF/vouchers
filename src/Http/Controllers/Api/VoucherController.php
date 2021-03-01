<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Tipoff\Support\Http\Controllers\Api\BaseApiController;
use Tipoff\Vouchers\Http\Requests\Voucher\IndexRequest;
use Tipoff\Vouchers\Http\Requests\Voucher\ShowRequest;
use Tipoff\Vouchers\Models\Voucher;
use Tipoff\Vouchers\Transformers\VoucherTransformer;

class VoucherController extends BaseApiController
{
    protected VoucherTransformer $transformer;

    public function __construct(VoucherTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->authorizeResource(Voucher::class);
    }

    public function index(IndexRequest $request): JsonResponse
    {
        $vouchers = Voucher::query()->byUser($request->user())->paginate($request->getPageSize());

        return fractal($vouchers, $this->transformer)
            ->respond();
    }

    public function show(ShowRequest $request, Voucher $voucher): JsonResponse
    {
        return fractal($voucher, $this->transformer)
            ->respond();
    }
}
