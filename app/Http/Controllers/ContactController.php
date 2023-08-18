<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactCollection;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function create(ContactCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $contact = new Contact($data);
        $contact->userId = $user->id;
        $contact->save();

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    public function get(int $id): ContactResource
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)
            ->where('userId', $user->id)->first();

        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Contact not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        return new ContactResource($contact);
    }

    public function update(int $id, ContactUpdateRequest $request): ContactResource
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)
            ->where('userId', $user->id)->first();

        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Contact not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        $data = $request->validated();
        $contact->fill($data);
        $contact->save();

        return new ContactResource($contact);
    }

    public function delete(int $id): JsonResponse
    {
        $user = Auth::user();
        $contact = Contact::where('id', $id)
            ->where('userId', $user->id)->first();

        if (!$contact) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    'message' => [
                        'Contact not found'
                    ]
                ]
            ])->setStatusCode(404));
        }

        $contact->delete();

        return response()->json([
            'data' => true
        ])->setStatusCode(200);
    }

    public function search(Request $request): ContactCollection
    {
        $user = Auth::user();
        $page = $request->input('page', 1);
        $limit = $request->input('limit', 10);

        $contacts = Contact::query()->where('userId', $user->id);

        $contacts = $contacts->where(function (Builder $builder) use ($request) {
            $name = $request->input('name');
            if ($name) {
                $builder->where(function (Builder $builder) use ($name) {
                    $builder->orWhere('firstName', 'like', '%' . $name . '%');
                    $builder->orWhere('lastName', 'like', '%' . $name . '%');
                });
            }

            $email = $request->input('email');
            if ($email) {
                $builder->where('email', 'like', '%' . $email . '%');
            }

            $phone = $request->input('phone');
            if ($phone) {
                $builder->where('phone', 'like', '%' . $phone . '%');
            }
        });

        $contacts = $contacts->paginate(perPage: $limit, page: $page);

        return new ContactCollection($contacts);
    }
}
