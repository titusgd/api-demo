<?php

namespace App\Services\Accounts;

use App\Models\Account\Todo;
use App\Services\Service;
use App\Traits\DateTrait;
use Illuminate\Support\Facades\DB;

class TodoService extends Service
{
    use DateTrait;
    public function validateAdd(object $request)
    {
        $valid = Service::validatorAndResponse($request->all(), [
            "content" => "required|string|max:500",
            "date" => "required|date",
            "finish" => "required|boolean"
        ], [
            'content.required' => '01 content',
            'content.max' => '01 content',
            'content.string' => '01 content',
            'date.required' => '01 date',
            'date.date' => '01 date',
            "finish.required" => "01 finish",
            "finish.boolean" => "01 finish"
        ]);

        if ($valid) return $valid;
    }

    public function validUpdate(object $request)
    {
        $rules = [];
        if (!empty($request->data)) {
            $rules = [
                'data' => 'required|array',
                'data.*.id' => 'required|integer|exists:todos,id',
                'data.*.date' => 'required|array',
                'data.*.content' => 'required|string|max:500',
                'data.*.finish' => 'required|boolean'
            ];
        }
        $valid = Service::validatorAndResponse($request->all(), $rules, [
            'data.required' => '01 data',
            'data.array' => '01 data',
            'data.*.id.required' => '01 id',
            'data.*.id.integer' => '01 id',
            'data.*.id.exists' => '01 id',
            'data.*.date.required' => '01 date',
            // 'data.*.date.date' => '01 date',
            'data.*.date.array' => '01 date',
            'data.*.content.required' => '01 content',
            'data.*.content.integer' => '01 content',
            'data.*.finish.required' => '01 finish',
            'data.*.finish.boolean' => '01 finish',
        ]);
        return $valid;
    }

    public function createTodo(string $content, string $dead_line, int $sort_by, string $title = '', bool $status = false)
    {
        $todo = new Todo;
        $todo->title = $title;
        $todo->content = $content;
        $todo->dead_line = $dead_line;
        $todo->sort_by = $sort_by;
        $todo->user_id = auth()->user()->id;
        $todo->status = $status;
        $todo->save();
    }

    public function getList(int $user_id): object
    {
        // 資料查詢
        $todo_query = TODO::select('id', 'dead_line', 'content', 'status');
        $todo_query->where('user_id', '=', $user_id);
        $todo_query->orderBy('sort_by');

        $result = $todo_query->get()->toArray();

        $response_data = [];
        foreach ($result as $key => $value) {
            $date = self::dateFormat($value['dead_line']);
            $response_data[$key]['id'] = $value['id'];
            $response_data[$key]['date'] = $date;
            $response_data[$key]['content'] = $value['content'];
            $response_data[$key]['finish'] = (bool)$value['status'];
        }

        return Service::response('00', 'ok', $response_data);
    }

    public function deleteTodo(int $todo_id)
    {
        $del = Todo::where('id', '=', $todo_id)->delete();
    }

    public function getSortNumber(int $user_id): int
    {
        $todo_query = TODO::select('id')->where('user_id', '=', $user_id);
        $result = $todo_query->get()->toArray();
        $sort_by_number = count($result) + 1;
        return $sort_by_number;
    }

    public function deleteTodoWhereNotIn(array $todo_ids, $user_id)
    {
        $delete = TODO::where('user_id', '=', $user_id)->whereNotIn('id', $todo_ids)->delete();
    }

    public function updateTodoList(object $request)
    {
        $todo_list = $request->all();
        foreach ($todo_list['data'] as $key => $value) {

            $todo = TODO::find($value['id']);
            $todo->dead_line = (is_array($value['date'])) ? $value['date'][0] : $value['date'];

            $todo->content = $value['content'];
            $todo->status = $value['finish'];
            $todo->sort_by = $key + 1;
            $todo->save();
        }
    }
}
