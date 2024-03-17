<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function addAdmin(Request $request){

        $registerAdminData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users|unique:admins',
            'password'=>'required|min:8|confirmed',
            'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        $admin =new Admin;

        if($request->hasFile('image')){
        /*
         $image = $request->file('image');
         $image_name=time() . '.' . $image->getClientOriginalExtension();
         Storage::putFileAs('ProfileImage/',$image,$image_name);
         $admin->query()->update([
            'image'=>"ProfileImage/$image_name"
          ]);
        */
            $image = $request->file('image');
            $image_name=time() . '.' . $image->getClientOriginalExtension();
            $image->move('ProfileImage/',$image_name);
       /* 
         Storage::putFileAs('ProfileImage/',$image,$image_name);
           $user->query()->update([
                'image'=>"ProfileImage/$image_name"
            ]);
        */   
            $admin->image="ProfileImage/".$image_name;
        }

        $admin->name=$registerAdminData['name'];
        $admin->email=$registerAdminData['email'];
        $admin->password= Hash::make($registerAdminData['password']);
        $admin->save();

        $token = $admin->createToken('token')->plainTextToken;
        
        return response()->json([
            'data'=>$admin,
            'token'=>$token
        ],200);
    }

    public function login(Request $request){
        $loginAdminData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);

        $admin = Admin::where('email',$loginAdminData['email'])->first();

        if(!$admin || !Hash::check($loginAdminData['password'],$admin->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $token = $admin->createToken('token')->plainTextToken;
        return response()->json([
            'message'=> 'login done',
            'token' => $token,
        ],200);
    }

    public function logout(Request $request){

         //auth()->admins()->tokens()->delete();
         $request->user()->tokens()->delete();
       // Auth::guard('web')->logout();
        return response()->json([
        'message' => 'Successfully logged out'
        ],200);
    }

    public function profile(){

        return response()->json([
            'data'=>auth()->user(),
        ],200);
    }

}
