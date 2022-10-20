<?php

namespace App\Http\Controllers;

use App\Modules\winkelmandje\services\KortingService;
use Illuminate\Http\Request;

class KortingController extends Controller
{
    private KortingService $KortingService;

    public function __construct(KortingService $kortingService) {
        $this->KortingService = $kortingService;
    }

    public function winkelmandje(Request $request) {
        return $this->KortingService->winkelmandje($request);
    }
}
