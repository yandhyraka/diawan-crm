<?php

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

if (!function_exists('testHelper')) {
    function testHelper($model = '') {
        $query = $model::where(['user_email' => 'admin@custom.com'])->first();
        return $query;
    }
}

if (!function_exists('initializeReturn')) {
    function initializeReturn() {
        return array(
            'status' => false,
            'code' => 400,
            'time' => time(),
            'message' => array(),
            'error' => array(),
            'data' => array(),
        );
    }
}

if (!function_exists('customValidator')) {
    function customValidator($request = array(), $rules = array()) {
        $return = initializeReturn();
        $validate = Validator::make($request, $rules);

        if ($validate->passes()) {
            $return['status'] = true;
            $return['code'] = 200;
        } else {
            $fails = $validate->failed();

            $return['status'] = false;
            $return['code'] = 200;

            foreach ($fails as $i => $fail) {
                foreach ($fail as $j => $fail_detail) {
                    if ($j == 'Required' || $j == 'RequiredIf' || $j == 'RequiredWith' || $j == 'RequiredWithout') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' required');
                    } else if ($j == 'Alpha') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' must be an alphabetical');
                    } else if ($j == 'Integer') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' must be a numerical');
                    } else if ($j == 'Email') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' must be an email');
                    } else if ($j == 'Exists') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' does not exist');
                    } else if ($j == 'In') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' value is not correct');
                    } else if ($j == 'Unique') {
                        $return['message'][] = ucwords(str_replace('_', ' ', $i) . ' is already in database');
                    } else {
                        $return['message'][] = ucwords('Something wrong with ' . str_replace('_', ' ', $i));
                    }
                    $return['error'][] = $i . '-' . $j;
                }
            }
        }

        http_response_code($return['code']);
        return $return;
    }
}

if (!function_exists('switchLanguageToggle')) {
    function switchLanguageToggle() {
        if (!session()->has('language')) {
            session(['language' => 'english']);
        }

        return "
        <div class='float-right'>
            <a href='javascript:switchLanguage(\"english\");'>English</a> | <a href='javascript:switchLanguage(\"indonesia\");'>Bahasa Indonesia</a>
        </div>
        <br>
        <script>
        function switchLanguage(language) {
            $.ajax({
                type: 'POST',
                url: '" . Route('switchLanguage') . "',
                data: {
                    language:language,
                },
                dataType: 'json',
                success: function(response){
                    if(response.status){   
                        location.reload();
                    } else {
                        alert(response.message);
                    }
                },
            });
        }
        </script>";
    }
}

if (!function_exists('swalDereactivate')) {
    function swalDereactivate($nameEnglish = '', $nameIndonesia = '', $deactivate = true, $urlAjax = '', $dataAjax = '', $afterAjax = '') {
        $languageEnglish = session('language') == 'english' ? true : false;

        return "
        Swal.fire({
            title: '" . (($deactivate ? 'Deactivate ' : 'Activate ') . $nameEnglish) . "',
            text: '" . ('Are you sure to ' . ($deactivate ? 'deactivate ' : 'activate ') . 'this ' . $nameEnglish . '?') . "',
            icon: '" . ($deactivate ? 'error' : 'warning') . "',
            showCancelButton: true,
            cancelButtonText: '" . ('Back') . "',
            cancelButtonColor: '#e3342f',
            confirmButtonText: '" . (($deactivate ? 'Deactivate' : 'Activate')) . "',
            confirmButtonColor: '#1e375b',
        }).then((result) => {
            if (result.isConfirmed) {
            " . $dataAjax . "
                $.ajax({
                    type: 'POST',
                    url: '" . $urlAjax . "',
                    data: data_ajax,
                    dataType: 'json',
                    success: function(response){
                        " . $afterAjax . "
                        Swal.fire({
                            title: '" . (($deactivate ? 'Deactivate ' : 'Activate ') . $nameEnglish) . "',
                            text: response.message,
                            icon: 'info',
                            confirmButtonText: 'Ok',
                        });
                    },
                    fail: function(response){
                        Swal.fire({
                            title: '" . (($deactivate ? 'Deactivate ' : 'Activate ') . $nameEnglish) . "',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Ok',
                        });
                    },
                });
            }
        })";
    }
}

if (!function_exists('swalDelete')) {
    function swalDelete($nameEnglish = '', $urlAjax = '', $dataAjax = '', $afterAjax = '') {
        return "
        Swal.fire({
            title: '" . (('Delete' . $nameEnglish)) . "',
            text: '" . ('Are you sure to delete this ' . $nameEnglish . '?') . "',
            icon: 'error',
            showCancelButton: true,
            cancelButtonText: '" . ('Back') . "',
            cancelButtonColor: '#e3342f',
            confirmButtonText: '" . ('Delete') . "',
            confirmButtonColor: '#1e375b',
        }).then((result) => {
            if (result.isConfirmed) {
            " . $dataAjax . "
                $.ajax({
                    type: 'POST',
                    url: '" . $urlAjax . "',
                    data: data_ajax,
                    dataType: 'json',
                    success: function(response){
                        " . $afterAjax . "
                        Swal.fire({
                            title: '" . (('Delete' . $nameEnglish)) . "',
                            text: response.message,
                            icon: 'info',
                            confirmButtonText: 'Ok',
                        });
                    },
                    fail: function(response){
                        Swal.fire({
                            title: '" . (('Delete' . $nameEnglish)) . "',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Ok',
                        });
                    },
                });
            }
        })";
    }
}

if (!function_exists('returnErrorException')) {
    function returnErrorException($class = '', $function = '', $exception) {
        $return['status'] = false;
        $return['code'] = 400;
        $return['message'][] = ucwords(str_replace('_', ' ', __FUNCTION__)) . ' error';
        $return['error'][] = $exception->getMessage();

        Log::error($class . ' | ' . $function . ' | ' . json_encode($return));

        return $return;
    }
}

if (!function_exists('addInfoLog')) {
    function addInfoLog($class = 'Helper', $function = __FUNCTION__, $userId = '', $return = array()) {
        $returnLog = $return;
        if ($return['status']) {
            unset($returnLog['data']);
        }

        Log::info($class . ' | ' . $function . ' | ' . 'User = ' . $userId . ' | ' . json_encode($returnLog));
    }
}

if (!function_exists('appUrlFromFullUrl')) {
    function appUrlFromFullUrl($url = '') {
        $url = explode('?', $url);
        $url = explode('/', $url[0]);

        return $url[1];
    }
}

if (!function_exists('addOrUpdateData')) {
    function addOrUpdateData($model = array(), $searchArrays = array(), $updateArrays = array(), $withTrashed = false, $id = '', $name = '', $userId = -1, $uniqueUserId = false, $setVisible = '') {
        $return = initializeReturn();

        try {
            if (!$uniqueUserId) {
                $updateArrays['updated_by'] = $userId;
                $updateArrays['created_by'] = $userId;
            }

            $modelResponse = new $model();
            if ($withTrashed) {
                $modelResponse = $modelResponse->withTrashed();
            }

            if (isset($setVisible) && !empty($setVisible)) {
                $modelResponse = $modelResponse->makeVisible($setVisible);
            } //blom bisa

            $modelResponse = $modelResponse->updateOrCreate($searchArrays, $updateArrays);
            if (isset($modelResponse) && !empty($modelResponse)) {
                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Insert/Update ' . $name . ' success';
                $return['data']['id'] = $modelResponse->$id;
            } else {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Insert/Update ' . $name . ' failed';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('addOrUpdateDataBatch')) {
    function addOrUpdateDataBatch($model = array(), $dataArrays = array(), $searchArrays = array(), $updateArrays = array(), $withTrashed = false, $name = '', $userId = -1, $uniqueUserId = false) {
        $return = initializeReturn();

        try {
            if (isset($dataArrays) && !empty($dataArrays)) {
                if (!$uniqueUserId) {
                    foreach ($dataArrays as $i => $dataArray) {
                        $dataArrays[$i]['updated_by'] = $userId;
                        $dataArrays[$i]['created_by'] = $userId;
                    }
                }

                if (!isset($updateArrays) || empty($updateArrays)) {
                    $updateArrays = array_keys($dataArrays[0]);
                    foreach ($searchArrays as $i => $searchArray) {
                        if (array_search($searchArray, $updateArrays) !== false) {
                            $key = array_search($searchArray, $updateArrays);
                            unset($updateArrays[$key]);
                        }
                    }
                }

                $modelResponse = new $model();
                if ($withTrashed) {
                    $modelResponse = $modelResponse->withTrashed();
                }

                //$searchArrays MUST BE UNIQUE INDEX
                $modelResponse = $modelResponse->upsert($dataArrays, $searchArrays, $updateArrays);
                if (isset($modelResponse) && !empty($modelResponse)) {
                    $return['status'] = true;
                    $return['code'] = 200;
                    $return['message'][] = 'Insert/Update batch ' . $name . ' success';
                } else {
                    $return['status'] = false;
                    $return['code'] = 200;
                    $return['message'][] = 'Insert/Update batch ' . $name . ' failed';
                }
            } else {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Insert/Update batch ' . $name . ' failed, data array empty';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('addData')) {
    function addData($model = array(), $checkDuplicates = array(), $dataArrays = array(), $id = '', $name = '', $userId = -1, $relationSync = false, $relationFuncName = null, $relationIds = []) {
        $return = initializeReturn();

        try {
            $modelResponse = new $model();
            foreach ($checkDuplicates as $i => $checkDuplicate) {
                foreach ($dataArrays as $j => $dataArray) {
                    if ($checkDuplicate == $j) {
                        $modelResponse = $modelResponse->where($checkDuplicate, $dataArray);
                        break;
                    }
                }
            }

            $modelResponse = $modelResponse->get()->toArray();
            if ((isset($modelResponse) && !empty($modelResponse)) && (isset($checkDuplicates) && !empty($checkDuplicates))) {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Data ' . $name . ' already recorded';
            } else {
                $modelResponse = new $model();
                foreach ($dataArrays as $i => $dataArray) {
                    $modelResponse->$i = $dataArray;
                }

                // $modelResponse->updated_by = $userId;
                // $modelResponse->created_by = $userId;
                $modelResponse->save();

                if ($relationSync == true) {
                    $modelResponse->$relationFuncName()->sync($relationIds);
                }

                if (isset($modelResponse) && !empty($modelResponse)) {
                    $return['status'] = true;
                    $return['code'] = 200;
                    $return['message'][] = 'Insert ' . $name . ' success';
                    $return['data']['id'] = $modelResponse->$id;
                } else {
                    $return['status'] = false;
                    $return['code'] = 200;
                    $return['message'][] = 'Insert ' . $name . ' failed';
                }
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('updateData')) {
    function updateData($model = array(), $checkDuplicates = array(), $dataArrays = array(), $whereArrays = array(), $id = '', $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            $canUpdate = true;
            $modelResponse = new $model();
            foreach ($checkDuplicates as $i => $checkDuplicate) {
                foreach ($dataArrays as $j => $dataArray) {
                    if ($checkDuplicate == $j) {
                        $modelResponse = $modelResponse->where($checkDuplicate, $dataArray);
                        break;
                    }
                }
            }

            $modelResponse = $modelResponse->get()->toArray();
            if ((isset($modelResponse) && !empty($modelResponse)) && (isset($checkDuplicates) && !empty($checkDuplicates))) {
                if ($modelResponse[0][$id] != $dataArrays[$id]) {
                    $canUpdate = false;
                    $return['message'][] = 'Data ' . $name . ' already recorded';
                }

                $return['status'] = false;
                $return['code'] = 200;
            }
            if ($canUpdate) {
                $modelResponse = new $model();
                foreach ($whereArrays as $i => $whereArray) {
                    foreach ($dataArrays as $j => $dataArray) {
                        if (is_array($whereArray) && $whereArray[0] == $j) {
                            $modelResponse = $modelResponse->where($whereArray[0], $whereArray[1], $dataArray);
                            unset($dataArrays[$j]);
                        } else if ($whereArray == $j) {
                            $modelResponse = $modelResponse->where($whereArray, $dataArray);
                            unset($dataArrays[$j]);
                        }

                        if ($whereArray == $id) {
                            $modelResponse = $modelResponse->withTrashed();
                        }
                    }
                }

                $whereResponse = $modelResponse->get()->toArray();
                if (isset($whereResponse) && !empty($whereResponse)) {
                    // $dataArrays['updated_by'] = $userId;
                    $modelResponse->update($dataArrays);
                    if (isset($modelResponse) && !empty($modelResponse)) {
                        $return['status'] = true;
                        $return['code'] = 200;
                        $return['message'][] = 'Update ' . $name . ' success';
                    } else {
                        $return['status'] = false;
                        $return['code'] = 200;
                        $return['message'][] = 'Update ' . $name . ' failed';
                    }
                } else {
                    $return['status'] = false;
                    $return['code'] = 200;
                    $return['message'][] = 'No ' . $name . ' data to update';
                }
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('getAllData')) {
    function getAllData($model, $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            $modelResponse = $model::all()->toArray();
            if (isset($modelResponse) && !empty($modelResponse)) {
                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Get ' . $name . ' success';
                $return['data'] = $modelResponse;
            } else {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Data ' . $name . ' empty';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
} //

if (!function_exists('getData')) {
    function getData($model = array(), $whereArrays = array(), $withArrays = array(), $propertyArrays = array(), $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            if (array_key_exists($name . '_id', $whereArrays)) {
                $propertyArrays['withTrashed'] = true;
            }

            $modelResponse = new $model();
            foreach ($whereArrays as $i => $whereArray) {
                if (is_array($whereArray)) {
                    if (isset($whereArray[2]) && !empty($whereArray[2])) {
                        $modelResponse = $modelResponse->orWhere($i, $whereArray[0], $whereArray[1]);
                    } else {
                        $modelResponse = $modelResponse->where($i, $whereArray[0], $whereArray[1]);
                    }
                } else {
                    $modelResponse = $modelResponse->where($i, $whereArray);
                }
            }

            if (isset($withArrays) && !empty($withArrays)) {
                $modelResponse = $modelResponse->with($withArrays);
            }

            if (isset($propertyArrays) && !empty($propertyArrays)) {
                if (isset($propertyArrays['offset']) && !empty($propertyArrays['offset'])) {
                    $modelResponse = $modelResponse->offset($propertyArrays['offset']);
                }

                if (isset($propertyArrays['limit']) && !empty($propertyArrays['limit'])) {
                    $modelResponse = $modelResponse->limit($propertyArrays['limit']);
                }

                if (isset($propertyArrays['groupBy']) && !empty($propertyArrays['groupBy'])) {
                    $modelResponse = $modelResponse->groupBy($propertyArrays['groupBy']);
                }

                if (isset($propertyArrays['orderBy']) && !empty($propertyArrays['orderBy'])) {
                    foreach ($propertyArrays['orderBy'] as $i => $orderBy) {
                        if (isset($propertyArrays['orderDir'][$i]) && !empty($propertyArrays['orderDir'][$i])) {
                            $modelResponse = $modelResponse->orderBy($orderBy, $propertyArrays['orderDir'][$i]);
                        } else {
                            $modelResponse = $modelResponse->orderBy($orderBy, 'ASC');
                        }
                    }
                }

                if (isset($propertyArrays['whereNotNull']) && !empty($propertyArrays['whereNotNull'])) {
                    foreach ($propertyArrays['whereNotNull'] as $i => $whereNotNull) {
                        $modelResponse = $modelResponse->whereNotNull($whereNotNull);
                    }
                }

                if (isset($propertyArrays['withTrashed']) && !empty($propertyArrays['withTrashed'])) {
                    $modelResponse = $modelResponse->withTrashed();
                }

                if (isset($propertyArrays['makeVisible']) && !empty($propertyArrays['makeVisible'])) {
                    $modelResponse = $modelResponse->makeVisible($propertyArrays['makeVisible']);
                }
            }

            $modelResponse = $modelResponse->get();
            $modelResponseArray = $modelResponse->toArray();
            if (isset($modelResponseArray) && !empty($modelResponseArray)) {
                $temp = array();

                foreach ($modelResponse as $i => $response) {
                    $temp[$i] = $response->toArray();

                    if ($response->trashed()) {
                        $temp[$i]['deleted'] = true;
                    } else {
                        $temp[$i]['deleted'] = false;
                    }
                }

                $to_return = $temp;

                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Get ' . $name . ' success';
                $return['data'] = $to_return;
            } else {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Data ' . $name . ' empty';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('getDataForTable')) {
    function getDataForTable($modelResponse, $request = array(), $searchColumn = array(), $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            //SEARCH////////////////////////////////////////////////////////////////////////////////////////////////////
            $searchValue = $request['searchValue'];
            if (isset($searchValue) && !empty($searchValue)) {
                $modelResponse = $modelResponse->where(function ($query) use ($searchColumn, $searchValue) {
                    foreach ($searchColumn as $i => $column) {
                        if ($i == 0) {
                            $query = $query->where($column, 'like', '%' . $searchValue . '%');
                        } else {
                            $query = $query->orWhere($column, 'like', '%' . $searchValue . '%');
                        }
                    }
                });
            }

            //ORDER/////////////////////////////////////////////////////////////////////////////////////////////////////
            $orderColumn = $searchColumn[0];
            $orderDir = 'asc';
            if (isset($request['order']) && !empty($request['order'])) {
                $order = explode('|', $request['order']);
                $orderColumn = $order[0];
                $orderDir = $order[1];
            }

            $totalData = $modelResponse->count();
            $modelResponse = $modelResponse->orderBy($orderColumn, $orderDir);

            //LIMIT/////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($request['length'] != -1) {
                $offsetData = $request['page'] * $request['length'];
                $modelResponse = $modelResponse->offset($offsetData)->limit($request['length']);
            }

            $modelResponse = $modelResponse->get()->toArray();
            if (isset($modelResponse) && !empty($modelResponse)) {
                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Get ' . $name . ' success';
                $return['current_page'] = $request['page'];
                $return['row_per_page'] = $request['length'];
                $return['total_data'] = $totalData;
                $return['data']['table'] = $modelResponse;
            } else {
                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Data ' . $name . ' empty';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
} //

if (!function_exists('dereactivateData')) {
    function dereactivateData($model = array(), $whereArrays = array(), $deactivate = true, $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            $modelResponse = new $model();
            foreach ($whereArrays as $i => $whereArray) {
                $modelResponse = $modelResponse->where($i, $whereArray);
            }

            $modelResponse->update(['updated_by' => $userId, 'status_active' => !$deactivate]);
            if (isset($modelResponse) && !empty($modelResponse)) {
                $return['status'] = true;
                $return['code'] = 200;

                if ($deactivate) {
                    $return['message'][] = 'Deactivate ' . $name . ' success';
                } else {
                    $return['message'][] = 'Reactivate ' . $name . ' success';
                }
            } else {
                $return['status'] = false;
                $return['code'] = 200;

                if ($deactivate) {
                    $return['message'][] = 'Deactivate ' . $name . ' failed';
                } else {
                    $return['message'][] = 'Reactivate ' . $name . ' failed';
                }
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('deleteData')) {
    function deleteData($model = array(), $whereArrays = array(), $name = '', $userId = -1) {
        $return = initializeReturn();

        try {
            $modelResponse = new $model();
            foreach ($whereArrays as $i => $whereArray) {
                $modelResponse = $modelResponse->where($i, $whereArray);
            }

            $modelResponse->update(['status_active' => 0, 'updated_by' => $userId, 'deleted_by' => $userId]);
            $modelResponse->delete();
            if (isset($modelResponse) && !empty($modelResponse)) {
                $return['status'] = true;
                $return['code'] = 200;
                $return['message'][] = 'Delete ' . $name . ' success';
            } else {
                $return['status'] = false;
                $return['code'] = 200;
                $return['message'][] = 'Delete ' . $name . ' failed';
            }

            addInfoLog(__CLASS__, __FUNCTION__, $userId, $return);
        } catch (Exception $exception) {
            $return = returnErrorException(__CLASS__, __FUNCTION__, $exception);
        }

        return $return;
    }
}

if (!function_exists('snakeToTitle')) {
    function snakeToTitle($string = '', $start = 0) {
        $returnName = '';
        $arrayName = explode('_', $string);

        for ($i = $start; $i < sizeof($arrayName); $i++) {
            if ($i != $start) {
                $returnName .= ' ';
            }
            $returnName .= $arrayName[$i];
        }

        return ucwords($returnName);
    }
} //

if (!function_exists('decrementAlphabet')) {
    function decrementAlphabet($alphabet = 'A') {
        if (strlen($alphabet) > 1) {
            $arrayAlphabet = str_split($alphabet);
            $alphabetSize = sizeof($arrayAlphabet) - 1;

            if (chr(ord($arrayAlphabet[$alphabetSize]) - 1) == '@') {
                unset($arrayAlphabet[$alphabetSize]);
                $arrayAlphabet[$alphabetSize - 1] = 'Z';
            } else {
                $arrayAlphabet[$alphabetSize] = chr(ord($arrayAlphabet[$alphabetSize]) - 1);
            }
            return implode('', $arrayAlphabet);
        } else if (chr(ord($alphabet) - 1) == '@') {
            return 'A';
        } else {
            return chr(ord($alphabet) - 1);
        }
    }
} //

if (!function_exists('cleanString')) {
    function cleanString($string) {
        $utf8 = array(
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        );
        return preg_replace(array_keys($utf8), array_values($utf8), $string);
    }
} //

if (!function_exists('cleanSpecialChar')) {
    function cleanSpecialChar($string) {
        $string = preg_replace('/[^A-Za-z0-9\-]/', ' ', $string);
        $string = preg_replace('/\s+/', ' ', $string);

        return $string;
    }
} //

if (!function_exists('cleanPhone')) {
    function cleanPhone($phoneNum) {
        if (isset($phoneNum) && !empty($phoneNum)) {
            $phoneNum = str_replace("+620", '0', $phoneNum);
            $phoneNum = str_replace("+62", '0', $phoneNum);
            $phoneNum = preg_replace('/^62/', '0', $phoneNum);
            $phoneNum = str_replace('-', '', $phoneNum);
            $phoneNum = preg_replace('/\s+/', '', $phoneNum);
        }
        return $phoneNum;
    }
}//