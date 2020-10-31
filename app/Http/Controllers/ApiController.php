<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Auth;
use Arr;

use App\Models\User;

class ApiController extends Controller
{

  /**
  * Get User by the token
  * @param Request $request
  * @return boolen $result
  */
  public function checkAdmin(Request $request)
  {
    $user = $request->user();
    if ($user->tokenCan('admin')) {
      return response()->json([
        'status' => 200,
        'message' => $user->name." is an Admin"
      ],200);
    }
    return response()->json([
      'status' => 401,
      'message' => "Unauthorized"
    ],401);
  }

  /**
  * Get User by the token
  * @param Request $request
  * @return boolen $result
  */
  public function logout(Request $request)
  {
    $user = $request->user();
    $user->currentAccessToken()->delete();
    return response()->json([
      'status' => 200,
      'message' => "User logout"
    ],200);
  }

  /**
  * Get User by the token
  * @param Request $request
  * @return User $user
  */
  public function getUser(Request $request)
  {
    return response()->json([
      'status' => 200,
      'user'=>$request->user()
    ]);
  }

  /**
  * Login user
  * @param Request $request
  * @return User $user with token
  */
  public function login(Request $request)
  {
    $validator = Validator::make($request->all(),[
      "email" => "required|email",
      "password" => "required"
    ]);
    if($validator->fails()){
      return response()->json([
        'status' => 400,
        'message' => "Bad Request"
      ],400);
    }
    if(!Auth::attempt($request->only('email','password'))){
      return response()->json([
        'status' => 401,
        'message' => "Unauthorized"
      ],401);
    }
    $user = User::where("email",$request->email)->select('id','name','email','roles')->first();
    $token = $user->createToken('user-token',$user->roles)->plainTextToken;
    Arr::add($user,'token',$token);
    return response()->json($user);
  }


  /**
  * Register user
  * @param Request $request
  * @return Bolean $result
  */
  public function register(Request $request)
  {
    $validator = Validator::make($request->all(),[
      "name" => "required",
      "email" => "required|email",
      "password" => "required|min:8"
    ]);
    if($validator->fails()){
      return response()->json([
        'status' => 400,
        'message' => "Bad Request"
      ],400);
    }
    $user = new User();
    $user->name = $request->name;
    $user->email = $request->email;
    $user->password = bcrypt($request->password);
    $user->roles = ['user'];
    $user->save();
    return response()->json([
      'status' => 200,
      'message' => "User registered"
    ],200);
  }

}
