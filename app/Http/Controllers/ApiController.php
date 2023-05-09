<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ApiController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|min:8"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Bad Request",
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->registerUser($validator->validated());
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => "Bad Request",
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => "Unauthorized"
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->authenticateUser($validator->validated());
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        return response()->json(['message' => "User logged out!"], Response::HTTP_OK);
    }

    public function checkAdmin(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->tokenCan('admin')) {
            return response()->json([
                'message' => $user->name . " is an Admin"
            ], Response::HTTP_OK);
        }

        return response()->json([
            'message' => "Unauthorized"
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function getUser(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()
        ], Response::HTTP_OK);
    }

    protected function registerUser(array $fields): JsonResponse
    {
        $user = new User();
        $user->name = $fields['name'];
        $user->email = $fields['email'];
        $user->password = bcrypt($fields['password']);
        $user->roles = ['user'];
        $user->save();

        return response()->json([
            'message' => "User registered",
        ], Response::HTTP_CREATED);
    }

    protected function authenticateUser(array $fields): JsonResponse
    {
        $user = User::query()
            ->whereEmail($fields['email'])
            ->select('id', 'name', 'email', 'roles')
            ->first();

        $token = $user->createToken('user-token', $user->roles)->plainTextToken;

        Arr::add($user, 'token', $token);

        return response()->json($user, Response::HTTP_OK);
    }
}
