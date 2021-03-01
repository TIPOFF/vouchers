<?php

declare(strict_types=1);

namespace Tipoff\Vouchers\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Tipoff\Support\Http\Controllers\Api\BaseApiController;
use Tipoff\Vouchers\Http\Requests\VoucherType\IndexRequest;
use Tipoff\Vouchers\Http\Requests\VoucherType\ShowRequest;
use Tipoff\Vouchers\Models\VoucherType;
use Tipoff\Vouchers\Transformers\VoucherTypeTransformer;

class VoucherTypeController extends BaseApiController
{
    protected VoucherTypeTransformer $transformer;

    public function __construct(VoucherTypeTransformer $transformer)
    {
        $this->transformer = $transformer;

        $this->authorizeResource(VoucherType::class);
    }

    public function index(IndexRequest $request): JsonResponse
    {
        $vouchers = VoucherType::query()->visibleBy($request->user())->isSellable()->paginate($request->getPageSize());

        return fractal($vouchers, $this->transformer)
            ->respond();
    }

    public function show(ShowRequest $request, VoucherType $voucherType): JsonResponse
    {
        return fractal($voucherType, $this->transformer)
            ->respond();
    }
}
