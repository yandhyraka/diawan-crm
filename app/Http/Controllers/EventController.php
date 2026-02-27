<?php

namespace App\Http\Controllers;

use App\Models\DiawanEvent;
use App\Models\DiawanEventDetail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller {
    public static function eventUpdate(Request $request) {
        $return = initializeReturn();

        try {
            $return = customValidator($request->all(), [
                'human_uuid' => 'required|string|exists:diawan_humans,human_uuid',
                'event_type' => 'required|string|exists:diawan_event_types,event_type_name',
                'event_place_id' => 'required|integer|exists:diawan_places,place_id',
                'event_detail' => 'required|array|min:1',
                'event_detail.*.event_detail_item' => 'required|string',
                'event_detail.*.event_detail_amount' => 'required|integer|min:1',
                'event_detail.*.event_detail_price' => 'required|numeric|min:0',
            ]);

            DB::beginTransaction();

            $eventIdAdded = null;
            if ($return['status']) {
                $update['event_human_uuid'] = $request['human_uuid'];
                $update['event_event_type'] = $request['event_type'];
                $update['event_place_id'] = $request['event_place_id'];
                $update['deleted_at'] = null;

                $return = addData(DiawanEvent::class, array(), $update, 'event_id', 'event');
                if ($return['status']) {
                    $eventIdAdded = $return['data']['id'];
                }
            }

            if ($return['status']) {
                foreach ($request['event_detail'] as $i => $eventDetail) {
                    $updateDetail['event_detail_event_id'] = $eventIdAdded;
                    $updateDetail['event_detail_item'] = $eventDetail['event_detail_item'];
                    $updateDetail['event_detail_amount'] = $eventDetail['event_detail_amount'];
                    $updateDetail['event_detail_price'] = $eventDetail['event_detail_price'];

                    $return = addData(DiawanEventDetail::class, array(), $updateDetail, 'event_detail_id', 'event_detail');
                }
            }

            if ($return['status']) {
                DB::commit();

                $return['data']['id'] = $eventIdAdded;
                $return['message'][0] =  'Update event success';
            } else {
                DB::rollBack();

                $return['message'][0] = 'Update event failed, ' . $return['message'][0];
            }

            addInfoLog(__CLASS__, __FUNCTION__, -1, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }
}
