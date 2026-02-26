<?php

namespace App\Http\Controllers;

use App\Models\DiawanHuman;
use App\Models\DiawanHumanLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HumanController extends Controller {
    public static function humanUpdate(Request $request) {
        $return = initializeReturn();
        $userId = session('user_id');

        try {
            $return = customValidator($request->all(), [
                'first_name' => 'required',
                'last_name' => 'nullable|alpha',
                'ktp' => 'nullable|integer',
                'birth_date' => 'nullable|date',
                'email' => 'nullable|email',
                'phone_number' => 'nullable|string',
                'source' => 'required|string',
            ]);

            DB::beginTransaction();

            $humanUuid = null;
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

            if ($return['status'] && empty($humanUuid)) {
                $add['human_first_name'] = $request['first_name'];
                $add['human_last_name'] = $request['last_name'];
                $add['human_birth_date'] = $request['birth_date'];
                $add['human_ktp'] = $request['ktp'];
                $add['human_phone_number'] = $request['phone_number'];
                $add['human_email'] = $request['email'];
                $add['deleted_at'] = null;

                $return = addData(DiawanHuman::class, array(), $add, 'human_uuid', 'human');
            } else if ($return['status'] && !empty($humanUuid)) {
                $search['human_uuid'] = $humanUuid;

                $update['human_first_name'] = $request['first_name'];
                $update['human_last_name'] = $request['last_name'];
                $update['human_birth_date'] = $request['birth_date'];
                $update['human_ktp'] = $request['ktp'];
                $update['human_phone_number'] = $request['phone_number'];
                $update['human_email'] = $request['email'];
                $update['deleted_at'] = null;

                $return = updateData(DiawanHuman::class, array(), $update, $search);
            }

            if ($return['status']) {
                if (empty($humanUuid)) {
                    $addLog['human_log_log_type'] = 'INSERT';
                    $addLog['human_log_before'] = null;
                    $addLog['human_log_after'] = json_encode($add);
                } else {
                    $addLog['human_log_log_type'] = 'UPDATE';
                    $addLog['human_log_before'] = json_encode($existingHuman[0]);
                    $addLog['human_log_after'] = json_encode($update);
                }
                $addLog['human_log_human_uuid'] = $humanUuid;
                $addLog['human_log_input_source'] = $request['source'];

                $return = addData(DiawanHumanLog::class, array(), $addLog, 'human_log_id', 'human_log');
            }

            if ($return['status']) {
                DB::commit();

                $return['message'][0] =  'Update human success';
            } else {
                DB::rollBack();

                $return['message'][0] = 'Update human failed, ' . $return['message'][0];
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        http_response_code($return['code']);
        return $return;
    }
}
