<?php

namespace App\Http\Controllers\Account;

use App\Services\Accounts\TodoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TodoController extends Controller
{
    public function add(Request $request)
    {
        $service = new TodoService;
        $valid = $service->validateAdd($request);
        if ($valid) return $valid;
        $sort_by = $service->getSortNumber(auth()->user()->id);
        $todo = $service->createTodo(
            content: $request->content,
            dead_line: $request->date,
            sort_by: $sort_by
        );

        return $service->response('00', 'ok');
    }

    public function update(Request $request)
    {
        $service = new TodoService;
        $valid = $service->validUpdate($request);
        if($valid) return $valid;
        
        $service->deleteTodoWhereNotIn(array_column($request->data,'id'),auth()->user()->id);
        
        $update = $service->updateTodoList($request);
        return $service->response('00', 'ok');
    }

    public function list(Request $request)
    {
        $service = new TodoService;

        $todo_list = $service->getList(auth()->user()->id);
        return $todo_list;
    }

    public function delete(Request $request)
    {
        $service = new TodoService;
        $service->deleteTodo($request->id);
        return $service->response('00', 'ok');
    }
}
