<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetStatRequest;
use App\Services\JournalStatService;
use Carbon\Carbon;

class BookStatController extends Controller
{
    /**
     * @var \App\Services\JournalStatService
     */
    protected $service;

    /**
     * BookStatController constructor.
     *
     * @param \App\Services\JournalStatService $service
     */
    public function __construct(JournalStatService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function __invoke(GetStatRequest $request): array
    {
			$dateFrom = Carbon::parse($request->get('dateFrom'))->startOfDay();
			$dateTo = Carbon::parse($request->get('dateTo'))->endOfDay();

			return $this->service->getPeriodStatistic($dateFrom, $dateTo);
    }
}
