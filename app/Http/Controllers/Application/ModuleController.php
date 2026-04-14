<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\System\Module;
use Illuminate\Support\Facades\Auth;

class ModuleController extends Controller {

    public function index(Request $request, $slug) {
        $employee = Auth::user();

        // $module = Module::where('slug', $slug)
        //                 ->with([
        //                     'menuGroups' => function ($gq) use ($employee) {
        //                         $gq->orderBy('sequence')
        //                         ->with(['menus'=>function($mq)use ($employee){
        //                             $mq->orderBy('sequence');
        //                         }])
                                //     ->with(['menus' => function ($mq) use ($employee) {
                                //         $mq->whereHas('permissions', function ($pq) use ($employee) {
                                //             $pq->where('employee_id', $employee->id)
                                //                 ->where('can_read', true);
                                //         }
                                //     )
                                //     ->with(['permissions' => function ($pq) use ($employee) {
                                //         // $pq->where('employee_id', $employee->id);
                                //     }])
                                //     ->orderBy('sequence');
                                // }]);
                                // ->with(['menus'=>function($mq) use ])
                            // }]);
                        // ->firstOrFail();

        $module = Module::where('slug', $slug)
                ->with([
                'menuGroups' => function ($gq) use ($employee) {
                    $gq->where('is_active', 1)            
                    ->orderBy('sequence')
                    ->with([
                        'menus' => function ($mq) use ($employee) {
                            $mq->where('is_active', 1)
                                ->orderBy('sequence');
                        }
                    ]);
                }
                ])
                ->firstOrFail();

        $response =[
            'module' => $module,
            'groups' => $module->menuGroups,
        ];

        return view('pages.application.module.index', $response);
    }
}
