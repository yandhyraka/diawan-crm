<?php

namespace App\Http\Controllers;

use App\Models\DiawanEvent;
use App\Models\DiawanEventDetail;
use App\Models\DiawanHuman;
use App\Models\DiawanHumanLog;
use App\Models\DiawanHumanRelation;
use App\Models\DiawanHumanRelationLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HumanController extends Controller {
    public static function _humanUpdate(Request $request) {
        $return = initializeReturn();

        try {
            $return = customValidator($request->all(), [
                'first_name' => 'nullable|alpha',
                'last_name' => 'nullable|alpha',
                'sex' => 'nullable|in:MALE,FEMALE',
                'ktp' => 'nullable|integer',
                'birth_date' => 'nullable|date',
                'email' => 'nullable|email',
                'phone_number' => 'nullable|string',
                'source' => 'required|string|exists:diawan_input_sources,input_source_name',
            ]);

            DB::beginTransaction();

            $humanUuid = null;
            $humanUuidAdded = null;
            $getHuman = null;

            if ($return['status']) {
                if (
                    (!isset($request['ktp']) && !isset($request['phone_number']) && !isset($request['email'])) ||
                    (empty($request['ktp']) && empty($request['phone_number']) && empty($request['email']))
                ) {
                    $return['status'] = false;
                    $return['message'][0] = 'At least a ktp, phone number, or email must be provided';
                }
            }

            if ($return['status']) {
                $getHuman = DiawanHuman::where('human_ktp', $request['ktp'])
                    ->orWhere('human_phone_number', $request['phone_number'])
                    ->orWhere('human_email', $request['email'])
                    ->get()->toArray();
                if (isset($getHuman) && !empty($getHuman)) {
                    $humanUuid = $getHuman[0]['human_uuid'];
                }
            }

            if ($return['status']) {
                if (!empty($humanUuid)) {
                    $search['human_uuid'] = $humanUuid;
                }
                $update['human_first_name'] = $request['first_name'];
                $update['human_last_name'] = $request['last_name'];
                $update['human_sex'] = $request['sex'];
                $update['human_ktp'] = $request['ktp'];
                $update['human_birth_date'] = $request['birth_date'];
                $update['human_phone_number'] = $request['phone_number'];
                $update['human_email'] = $request['email'];
                $update['deleted_at'] = null;

                if (empty($humanUuid)) {
                    $return = addData(DiawanHuman::class, array(), $update, 'human_uuid', 'human');
                    if ($return['status']) {
                        $humanUuidAdded = $return['data']['id'];
                    }
                } else {
                    $return = updateData(DiawanHuman::class, array(), $update, $search);
                }
            }

            if ($return['status']) {
                if (empty($humanUuid)) {
                    $addLog['human_log_log_type'] = 'INSERT';
                    $addLog['human_log_before'] = null;
                    $addLog['human_log_human_uuid'] = $humanUuidAdded;
                } else {
                    $addLog['human_log_log_type'] = 'UPDATE';
                    $addLog['human_log_before'] = json_encode($getHuman[0]);
                    $addLog['human_log_human_uuid'] = $humanUuid;
                }

                $addLog['human_log_after'] = json_encode($update);
                $addLog['human_log_input_source'] = $request['source'];

                $return = addData(DiawanHumanLog::class, array(), $addLog, 'human_log_id', 'human_log');
            }

            if ($return['status']) {
                DB::commit();

                $return['data']['id'] = $humanUuidAdded ?? $humanUuid;
                $return['message'][0] =  'Update human success';
            } else {
                DB::rollBack();

                $return['message'][0] = 'Update human failed, ' . $return['message'][0];
            }

            addInfoLog(__CLASS__, __FUNCTION__, -1, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }

    public static function humanInsert(Request $request) {
        $return = initializeReturn();

        try {
            $return = customValidator($request->all(), [
                'first_name' => 'nullable|alpha',
                'last_name' => 'nullable|alpha',
                'sex' => 'nullable|in:MALE,FEMALE',
                'ktp' => 'nullable|integer',
                'birth_date' => 'nullable|date',
                'email' => 'nullable|email',
                'phone_number' => 'nullable|string',
                'source' => 'required|string|exists:diawan_input_sources,input_source_name',
            ]);

            DB::beginTransaction();

            $humanUuidAdded = null;
            $getHuman = null;

            if ($return['status']) {
                $update['human_first_name'] = $request['first_name'];
                $update['human_last_name'] = $request['last_name'];
                $update['human_sex'] = $request['sex'];
                $update['human_ktp'] = $request['ktp'];
                $update['human_birth_date'] = $request['birth_date'];
                $update['human_email'] = $request['email'];
                $update['human_phone_number'] = $request['phone_number'];
                $update['deleted_at'] = null;

                $return = addData(DiawanHuman::class, array(), $update, 'human_uuid', 'human');
                if ($return['status']) {
                    $humanUuidAdded = $return['data']['id'];
                }
            }

            if ($return['status']) {
                $addLog['human_log_log_type'] = 'INSERT';
                $addLog['human_log_before'] = null;
                $addLog['human_log_human_uuid'] = $humanUuidAdded;
                $addLog['human_log_after'] = json_encode($update);
                $addLog['human_log_input_source'] = $request['source'];

                $return = addData(DiawanHumanLog::class, array(), $addLog, 'human_log_id', 'human_log');
            }

            if ($return['status']) {
                DB::commit();

                $return['data']['id'] = $humanUuidAdded;
                $return['message'][0] =  'Insert human success';
            } else {
                DB::rollBack();

                $return['message'][0] = 'Insert human failed, ' . $return['message'][0];
            }

            if ($return['status']) {
                $getHuman = DiawanHuman::selectRaw('
                    human_uuid,
                    human_first_name as first_name,
                    human_last_name as last_name,
                    human_sex as sex,
                    human_ktp as ktp,
                    human_birth_date as birth_date,
                    human_email as email,
                    human_phone_number as phone_number,
                    "MERGE/USE/DELETE" as action,
                    "" as merge_to_human_uuid
                ')
                    ->where('human_first_name', $request['first_name'])
                    ->orWhere('human_last_name', $request['last_name'])
                    ->orWhere('human_ktp', $request['ktp'])
                    ->orWhere('human_phone_number', $request['phone_number'])
                    ->orWhere('human_email', $request['email'])
                    ->get()->toArray();
                if (isset($getHuman) && !empty($getHuman) && sizeof($getHuman) > 1) {
                    $return['message'][0] = 'Theres is already a human with the same first name/last name/ktp/phone number/email, please decide the action to be taken for the duplicate data (MERGE/USE/DELETE)';
                    $return['data'] = $getHuman;
                }
            }

            addInfoLog(__CLASS__, __FUNCTION__, -1, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }

    public static function humanGet($humanUuid) {
        $return = initializeReturn();

        try {
            $request['humanUuid'] = $humanUuid;
            $return = customValidator($request, [
                'humanUuid' => 'required|string|exists:diawan_humans,human_uuid',
            ]);

            DB::beginTransaction();

            $humanUuid = null;
            $getHuman = null;

            if ($return['status']) {
                $getHuman = DiawanHuman::selectRaw('
                    human_uuid,
                    human_first_name as first_name,
                    human_last_name as last_name,
                    human_sex as sex,
                    human_ktp as ktp,
                    human_birth_date as birth_date,
                    human_phone_number as phone_number,
                    human_email as email
                ')
                    ->where('human_uuid', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($getHuman) && !empty($getHuman)) {
                    $return['message'][0] = 'Get human success';
                } else {
                    $return['status'] =  false;
                    $return['message'][0] = 'Get human failed, human not found';
                }
            }

            if ($return['status']) {
                $getHumanRelation = DiawanHumanRelation::selectRaw('
                    human_relation_human_uuid1 as human_uuid1,
                    human1.human_first_name as human_first_name1,
                    human1.human_last_name as human_last_name1,
                    human_relation_human_uuid2 as human_uuid2,
                    human2.human_first_name as human_first_name2,
                    human2.human_last_name as human_last_name2,
                    human_relation_relation_type, 
                    human_relation_data
                ')
                    ->leftJoin('diawan_humans as human1', 'human_relation_human_uuid1', '=', 'human1.human_uuid')
                    ->leftJoin('diawan_humans as human2', 'human_relation_human_uuid2', '=', 'human2.human_uuid')
                    ->where('human_relation_human_uuid1', $request['humanUuid'])
                    ->orWhere('human_relation_human_uuid2', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($getHumanRelation) && !empty($getHumanRelation)) {
                    $relations = [];
                    foreach ($getHumanRelation as $i => $relation) {
                        $relationTemp = [];
                        if ($request['humanUuid'] == $relation['human_uuid1']) {
                            $relationTemp['human_uuid'] = $relation['human_uuid2'];
                            $relationTemp['first_name'] = $relation['human_first_name2'];
                            $relationTemp['last_name'] = $relation['human_last_name2'];
                        } else {
                            $relationTemp['human_uuid'] = $relation['human_uuid1'];
                            $relationTemp['first_name'] = $relation['human_first_name1'];
                            $relationTemp['last_name'] = $relation['human_last_name1'];
                        }
                        $relationTemp['relation_type'] = $relation['human_relation_relation_type'];
                        $relationTemp['other_data'] = json_decode($relation['human_relation_data'], true);
                        $relations[] = $relationTemp;
                    }
                    $getHuman[0]['relations'] = $relations;
                    $return['message'][0] = 'Get human relation success';
                }
            }

            if ($return['status']) {
                $getEvent = DiawanEvent::selectRaw('
                    event_id,
                    event_event_type as event_type,
                    diawan_places.place_name as event_place_name
                ')
                    ->leftJoin('diawan_places', 'event_place_id', '=', 'diawan_places.place_id')
                    ->where('event_human_uuid', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($getEvent) && !empty($getEvent)) {
                    $getHuman[0]['events'] = $getEvent;
                    $return['message'][0] = 'Get events success';
                }
            }

            if ($return['status']) {
                $getEventDetail = DiawanEventDetail::where('event_detail_human_uuid', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($getEventDetail) && !empty($getEventDetail)) {
                    foreach ($getHuman[0]['events'] as $i => $event) {
                        $eventDetails = [];

                        foreach ($getEventDetail as $eventDetail) {
                            if ($eventDetail['event_detail_event_id'] == $event['event_id']) {
                                $eventDetails[] = [
                                    'event_detail_data' => $eventDetail['event_detail_data'],
                                ];
                            }
                        }

                        $getHuman[0]['events'][$i]['event_detail'] = $eventDetails;
                    }
                    $return['message'][0] = 'Get event details success';
                }
            }

            // if ($return['status']) {
            //     $getEventDetailHighestCount = DiawanEventDetail::selectRaw('
            //         count(*) as total
            //     ')
            //         ->where('event_detail_human_uuid', $request['humanUuid'])
            //         ->groupBy('event_detail_item')
            //         ->orderByRaw('count(*) DESC')
            //         ->limit(1)
            //         ->value('total');

            //     $getEventDetailtopItems = DiawanEventDetail::selectRaw('
            //         event_detail_item as items,
            //         count(*) as total
            //     ')
            //         ->where('event_detail_human_uuid', $request['humanUuid'])
            //         ->groupBy('event_detail_item')
            //         ->having('total', '=', $getEventDetailHighestCount)
            //         ->get()->toArray();
            //     if (isset($getEventDetailtopItems) && !empty($getEventDetailtopItems)) {
            //         $getHuman[0]['top_items'] = $getEventDetailtopItems;
            //         $return['message'][0] = 'Get top items success';
            //     }
            // }

            if ($return['status']) {
                $return['data'] =  $getHuman;
            }

            addInfoLog(__CLASS__, __FUNCTION__, -1, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }

    public static function humanRelationUpdate(Request $request) {
        $return = initializeReturn();

        try {
            $return = customValidator($request->all(), [
                'human_uuid1' => 'required|string|exists:diawan_humans,human_uuid',
                'human_uuid2' => 'required|string|exists:diawan_humans,human_uuid',
                'relation_type' => 'required|string|exists:diawan_relation_types,relation_type_name',
                'other_data' => 'nullable|string',
                'source' => 'required|string',
            ]);

            DB::beginTransaction();

            $humanParentUuid = null;
            $humanSpouseUuid = null;
            if ($request['relation_type'] == 'PARENT-CHILD') {
                $getRelation = DiawanHumanRelation::where('human_relation_relation_type', 'HUSBAND-WIFE')
                    ->where(function ($query) use ($request) {
                        $query->where('human_relation_human_uuid1', $request['human_uuid1'])
                            ->orWhere('human_relation_human_uuid1', $request['human_uuid2'])
                            ->orWhere('human_relation_human_uuid2', $request['human_uuid1'])
                            ->orWhere('human_relation_human_uuid2', $request['human_uuid2']);
                    })
                    ->get()->toArray();
                if (isset($getRelation) && !empty($getRelation)) {
                    if ($request['human_uuid1'] == $getRelation[0]['human_relation_human_uuid1'] || $request['human_uuid2'] == $getRelation[0]['human_relation_human_uuid1']) {
                        $humanParentUuid = $getRelation[0]['human_relation_human_uuid1'];
                        $humanSpouseUuid = $getRelation[0]['human_relation_human_uuid2'];
                    } else if ($request['human_uuid1'] == $getRelation[0]['human_relation_human_uuid2'] || $request['human_uuid2'] == $getRelation[0]['human_relation_human_uuid2']) {
                        $humanParentUuid = $getRelation[0]['human_relation_human_uuid2'];
                        $humanSpouseUuid = $getRelation[0]['human_relation_human_uuid1'];
                    }
                }
            }

            $humanRelationId = null;
            $humanRelationIdAdded = null;
            $getHumanRelation = null;
            if ($return['status']) {
                $getHumanRelation = DiawanHumanRelation::where(function ($query) use ($request) {
                    $query->where('human_relation_human_uuid1', $request['human_uuid1'])
                        ->where('human_relation_human_uuid2', $request['human_uuid2']);
                })->orWhere(function ($query) use ($request) {
                    $query->where('human_relation_human_uuid1', $request['human_uuid2'])
                        ->where('human_relation_human_uuid2', $request['human_uuid1']);
                })
                    ->get()->toArray();
                if (isset($getHumanRelation) && !empty($getHumanRelation)) {
                    $humanRelationId = $getHumanRelation[0]['human_relation_id'];
                }
            }

            if ($return['status']) {
                if (!empty($humanRelationId)) {
                    $search['human_relation_id'] = $humanRelationId;
                }
                $update['human_relation_human_uuid1'] = $request['human_uuid1'];
                $update['human_relation_human_uuid2'] = $request['human_uuid2'];
                $update['human_relation_relation_type'] = $request['relation_type'];
                $update['human_relation_data'] = json_encode($request['other_data']);
                $update['deleted_at'] = null;

                if (empty($humanRelationId)) {
                    $return = addData(DiawanHumanRelation::class, array(), $update, 'human_relation_id', 'human_relation');
                    if ($return['status']) {
                        $humanRelationIdAdded = $return['data']['id'];
                    }
                } else {
                    $return = updateData(DiawanHumanRelation::class, array(), $update, $search);
                }
            }

            if ($return['status']) {
                if (empty($humanRelationId)) {
                    $addLog['human_relation_log_log_type'] = 'INSERT';
                    $addLog['human_relation_log_before'] = null;
                    $addLog['human_relation_log_human_relation_id'] = $humanRelationIdAdded;
                } else {
                    $addLog['human_relation_log_log_type'] = 'UPDATE';
                    $addLog['human_relation_log_before'] = json_encode($getHumanRelation[0]);
                    $addLog['human_relation_log_human_relation_id'] = $humanRelationId;
                }
                $addLog['human_relation_log_after'] = json_encode($update);
                $addLog['human_relation_log_input_source'] = $request['source'];

                $return = addData(DiawanHumanRelationLog::class, array(), $addLog, 'human_relation_log_id', 'human_relation_log');
            }

            if ($request['relation_type'] == 'PARENT-CHILD' && isset($humanParentUuid) && isset($humanSpouseUuid)) {
                $humanChildUuid = $humanParentUuid == $request['human_uuid1'] ? $request['human_uuid2'] : $request['human_uuid1'];
                $humanRelationIdParent = null;
                $humanRelationIdParentAdded = null;
                $getHumanRelation = null;
                if ($return['status']) {
                    $getHumanRelation = DiawanHumanRelation::where(function ($query) use ($humanSpouseUuid, $humanChildUuid) {
                        $query->where('human_relation_human_uuid1', $humanSpouseUuid)
                            ->where('human_relation_human_uuid2', $humanChildUuid);
                    })->orWhere(function ($query) use ($humanSpouseUuid, $humanChildUuid) {
                        $query->where('human_relation_human_uuid1', $humanChildUuid)
                            ->where('human_relation_human_uuid2', $humanSpouseUuid);
                    })
                        ->get()->toArray();
                    if (isset($getHumanRelation) && !empty($getHumanRelation)) {
                        $humanRelationIdParent = $getHumanRelation[0]['human_relation_id'];
                    }
                }

                if ($return['status']) {
                    if (!empty($humanRelationIdParent)) {
                        $search['human_relation_id'] = $humanRelationIdParent;
                    }
                    $update['human_relation_human_uuid1'] = $humanSpouseUuid;
                    $update['human_relation_human_uuid2'] = $humanChildUuid;
                    $update['human_relation_relation_type'] = 'PARENT-CHILD';
                    $update['human_relation_data'] = json_encode($request['other_data']);
                    $update['deleted_at'] = null;

                    if (empty($humanRelationIdParent)) {
                        $return = addData(DiawanHumanRelation::class, array(), $update, 'human_relation_id', 'human_relation');
                        if ($return['status']) {
                            $humanRelationIdParentAdded = $return['data']['id'];
                        }
                    } else {
                        $return = updateData(DiawanHumanRelation::class, array(), $update, $search);
                    }
                }

                if ($return['status']) {
                    if (empty($humanRelationIdParent)) {
                        $addLog['human_relation_log_log_type'] = 'INSERT';
                        $addLog['human_relation_log_before'] = null;
                        $addLog['human_relation_log_human_relation_id'] = $humanRelationIdParentAdded;
                    } else {
                        $addLog['human_relation_log_log_type'] = 'UPDATE';
                        $addLog['human_relation_log_before'] = json_encode($getHumanRelation[0]);
                        $addLog['human_relation_log_human_relation_id'] = $humanRelationIdParent;
                    }
                    $addLog['human_relation_log_after'] = json_encode($update);
                    $addLog['human_relation_log_input_source'] = $request['source'];

                    $return = addData(DiawanHumanRelationLog::class, array(), $addLog, 'human_relation_log_id', 'human_relation_log');
                }
            }

            if ($return['status']) {
                DB::commit();

                $return['data']['id'] = $humanRelationIdAdded ?? $humanRelationId;
                $return['message'][0] =  'Update human relation success';
            } else {
                DB::rollBack();

                $return['message'][0] = 'Update human relation failed, ' . $return['message'][0];
            }

            addInfoLog(__CLASS__, __FUNCTION__, -1, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }
}
