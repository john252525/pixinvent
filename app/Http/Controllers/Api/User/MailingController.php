<?php

namespace App\Http\Controllers\Api\User;

use App\Facades\WhatsApiFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Services\WhatsApi\EditRequest;
use App\Http\Requests\Services\WhatsApi\StoreRequest;

class MailingController extends Controller
{
    public function index()
    {
        return WhatsApiFacade::index();
    }

    public function getMessages(int $id)
    {
        return WhatsApiFacade::getMessages($id);
    }

    public function store(StoreRequest $request)
    {
        return WhatsApiFacade::store($request);
    }

    public function edit(EditRequest $request)
    {
        return WhatsApiFacade::edit($request);
    }

    public function editStatus(int $id, int $stateID)
    {
        return WhatsApiFacade::editStatus($id, $stateID);
    }

    public function destroy(int $id)
    {
        return WhatsApiFacade::destroy($id);
    }
}
