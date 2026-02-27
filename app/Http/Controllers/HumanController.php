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
    public static function humanUpdate(Request $request) {
        $return = initializeReturn();

        try {
            $return = customValidator($request->all(), [
                'first_name' => 'required',
                'last_name' => 'nullable|alpha',
                'ktp' => 'nullable|integer',
                'birth_date' => 'nullable|date',
                'email' => 'nullable|email',
                'phone_number' => 'nullable|string',
                'source' => 'required|string|exists:diawan_input_sources,input_source_name',
            ]);

            DB::beginTransaction();

            $humanUuid = null;
            $humanUuidAdded = null;
            $existingHuman = null;

            if ($return['status']) {
                $existingHuman = DiawanHuman::where('human_ktp', $request['ktp'])
                    ->orWhere('human_phone_number', $request['phone_number'])
                    ->orWhere('human_email', $request['email'])
                    ->get()->toArray();
                if (isset($existingHuman) && !empty($existingHuman)) {
                    $humanUuid = $existingHuman[0]['human_uuid'];
                }
            }

            if ($return['status']) {
                if (!empty($humanUuid)) {
                    $search['human_uuid'] = $humanUuid;
                }
                $update['human_first_name'] = $request['first_name'];
                $update['human_last_name'] = $request['last_name'];
                $update['human_birth_date'] = $request['birth_date'];
                $update['human_ktp'] = $request['ktp'];
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
                    $addLog['human_log_before'] = json_encode($existingHuman[0]);
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

    public static function humanGet($humanUuid) {
        $return = initializeReturn();

        try {
            $request['humanUuid'] = $humanUuid;
            $return = customValidator($request, [
                'humanUuid' => 'required|string|exists:diawan_humans,human_uuid',
            ]);

            DB::beginTransaction();

            $humanUuid = null;
            $existingHuman = null;

            if ($return['status']) {
                $existingHuman = DiawanHuman::selectRaw('
                    human_uuid,
                    human_first_name as first_name,
                    human_last_name as last_name,
                    human_ktp as ktp,
                    human_birth_date as birth_date,
                    human_phone_number as phone_number,
                    human_email as email
                ')
                ->where('human_uuid', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($existingHuman) && !empty($existingHuman)) {
                    $return['message'][0] = 'Get human success';
                } else {
                    $return['status'] =  false;
                    $return['message'][0] = 'Get human failed, human not found';
                }
            }

            if ($return['status']) {
                $existingHumanRelation = DiawanHumanRelation::selectRaw('
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
                if (isset($existingHumanRelation) && !empty($existingHumanRelation)) {
                    $relations = [];
                    foreach ($existingHumanRelation as $i => $relation) {
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
                    $existingHuman[0]['relations'] = $relations;
                    $return['message'][0] = 'Get human relation success';
                }
            }

            if ($return['status']) {
                $existingEvent = DiawanEvent::selectRaw('
                    event_id,
                    event_event_type as event_type,
                    diawan_places.place_name as event_place_name
                ')
                    ->leftJoin('diawan_places', 'event_place_id', '=', 'diawan_places.place_id')
                    ->where('event_human_uuid', $request['humanUuid'])
                    ->get()->toArray();
                if (isset($existingEvent) && !empty($existingEvent)) {
                    $existingHuman[0]['events'] = $existingEvent;
                    $return['message'][0] = 'Get events success';
                }
            }

            if ($return['status']) {
                foreach ($existingHuman[0]['events'] as $i => $event) {
                    $existingEventDetail = DiawanEventDetail::where('event_detail_event_id', $event['event_id'])
                        ->get()->toArray();
                    if (isset($existingEventDetail) && !empty($existingEventDetail)) {
                        $existingHuman[0]['events'][$i]['event_detail'] = $existingEventDetail;
                        $return['message'][0] = 'Get event details success';
                    }
                }
            }

            if ($return['status']) {
                $return['data'] =  $existingHuman;
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
                $existingRelation = DiawanHumanRelation::where('human_relation_relation_type', 'HUSBAND-WIFE')
                    ->where(function ($query) use ($request) {
                        $query->where('human_relation_human_uuid1', $request['human_uuid1'])
                            ->orWhere('human_relation_human_uuid1', $request['human_uuid2'])
                            ->orWhere('human_relation_human_uuid2', $request['human_uuid1'])
                            ->orWhere('human_relation_human_uuid2', $request['human_uuid2']);
                    })
                    ->get()->toArray();
                if (isset($existingRelation) && !empty($existingRelation)) {
                    if ($request['human_uuid1'] == $existingRelation[0]['human_relation_human_uuid1'] || $request['human_uuid2'] == $existingRelation[0]['human_relation_human_uuid1']) {
                        $humanParentUuid = $existingRelation[0]['human_relation_human_uuid1'];
                        $humanSpouseUuid = $existingRelation[0]['human_relation_human_uuid2'];
                    } else if ($request['human_uuid1'] == $existingRelation[0]['human_relation_human_uuid2'] || $request['human_uuid2'] == $existingRelation[0]['human_relation_human_uuid2']) {
                        $humanParentUuid = $existingRelation[0]['human_relation_human_uuid2'];
                        $humanSpouseUuid = $existingRelation[0]['human_relation_human_uuid1'];
                    }
                }
            }

            $humanRelationId = null;
            $humanRelationIdAdded = null;
            $existingHumanRelation = null;
            if ($return['status']) {
                $existingHumanRelation = DiawanHumanRelation::where(function ($query) use ($request) {
                    $query->where('human_relation_human_uuid1', $request['human_uuid1'])
                        ->where('human_relation_human_uuid2', $request['human_uuid2']);
                })->orWhere(function ($query) use ($request) {
                    $query->where('human_relation_human_uuid1', $request['human_uuid2'])
                        ->where('human_relation_human_uuid2', $request['human_uuid1']);
                })
                    ->get()->toArray();
                if (isset($existingHumanRelation) && !empty($existingHumanRelation)) {
                    $humanRelationId = $existingHumanRelation[0]['human_relation_id'];
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
                    $addLog['human_relation_log_before'] = json_encode($existingHumanRelation[0]);
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
                $existingHumanRelation = null;
                if ($return['status']) {
                    $existingHumanRelation = DiawanHumanRelation::where(function ($query) use ($humanSpouseUuid, $humanChildUuid) {
                        $query->where('human_relation_human_uuid1', $humanSpouseUuid)
                            ->where('human_relation_human_uuid2', $humanChildUuid);
                    })->orWhere(function ($query) use ($humanSpouseUuid, $humanChildUuid) {
                        $query->where('human_relation_human_uuid1', $humanChildUuid)
                            ->where('human_relation_human_uuid2', $humanSpouseUuid);
                    })
                        ->get()->toArray();
                    if (isset($existingHumanRelation) && !empty($existingHumanRelation)) {
                        $humanRelationIdParent = $existingHumanRelation[0]['human_relation_id'];
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
                        $addLog['human_relation_log_before'] = json_encode($existingHumanRelation[0]);
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
