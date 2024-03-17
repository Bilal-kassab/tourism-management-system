<?php

namespace App\Http\Controllers;

use App\Models\ConfirmCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function register(Request $request){
        
        $registerUserData = $request->validate([
            'name'=>'required|string',
            'email'=>'required|string|email|unique:users|unique:admins',
            'password'=>'required|min:8|confirmed',
            'image'=> 'image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        /*
         
         $user = User::query()->create([
            'name' => $registerUserData['name'],
            'email' => $registerUserData['email'],
            'password' => Hash::make($registerUserData['password']),
        ]); 
         */
        $user=new User;
        if($request->hasFile('image')){

            $image = $request->file('image');
            $image_name=time() . '.' . $image->getClientOriginalExtension();
            $image->move('ProfileImage/',$image_name);
         /*
           Storage::putFileAs('ProfileImage/',$image,$image_name);
           $user->query()->update([
                'image'=>"ProfileImage/$image_name"
            ]);
            */
            $user->image="ProfileImage/".$image_name;
        }

        $user->name=$registerUserData['name'];
        $user->email=$registerUserData['email'];
        $user->password= Hash::make($registerUserData['password']);
        $user->save();
        $token = $user->createToken('token')->plainTextToken;

        $registerUserData['code']=mt_rand(100000,999999);

        while(ConfirmCode::firstWhere('code', $registerUserData['code'])){
            $registerUserData['code']=mt_rand(100000,999999);
        }
        $confdetails=ConfirmCode::create([
            'code'=>$registerUserData['code'],
            'email'=>$registerUserData['email'],
        ]);

        //Mail::to($registerUserData['email'])->send(new ConfirmationEmail($confdeatiles));

        return response()->json([
            'data'=>$user,
            'code'=>$registerUserData['code'],
            'token'=>$token
        ],200);
    }

    public function login(Request $request){
        $loginUserData = $request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8'
        ]);

        $user = User::where('email',$loginUserData['email'])->first();

        if(!$user || !Hash::check($loginUserData['password'],$user->password)){
            return response()->json([
                'message' => 'Invalid Credentials'
            ],401);
        }
        $token = $user->createToken('token')->plainTextToken;
        return response()->json([
            'message'=> 'login done',
            'token' => $token,
        ],200);
    }

    public function logout(Request $request){
        
        $request->user()->tokens()->delete();

        return response()->json([
        'message' => 'Successfully logged out'
        ],200);
    }

    public function confirmCode(Request $request){
        $registerUserData = $request->validate([
            'email'=>'required|string|email',
            'code'=>'required|min:6'
        ]);
        //$registerUserData['email']
        $myCode=ConfirmCode::firstWhere('email', $registerUserData['email']);

        if($registerUserData['code'] !=  $myCode['code'] ){
            return response()->json([
                'message'=> 'worng code'
            ],200);
        }
        $user=User::where('email',$registerUserData['email'])->first();

        $user->email_verified_at=Carbon::now();
        $user->save();

        return response()->json([
            'message'=> 'welcome to here'
        ],200);
    }

    public function forgetPassword_SendEmail(Request $request){
        $registerUserData = $request->validate([
            'email'=>'required|string|email',
        ]);

        $dataUser=User::firstWhere('email',$registerUserData['email']);

        if(!$dataUser){
            return response()->json([
                'message'=> 'there is no email like this'
            ],200);
        }

        ConfirmCode::firstWhere('email',$registerUserData['email'])->delete();

        $registerUserData['code']=mt_rand(100000,999999);

        while(ConfirmCode::firstWhere('code', $registerUserData['code'])){
            $registerUserData['code']=mt_rand(100000,999999);
        }

        $confdetails=ConfirmCode::create([
            'code'=>$registerUserData['code'],
            'email'=>$registerUserData['email'],
        ]);

       // Mail::to($registerUserData['email'])->send(new ConfirmationEmail($confdetails));

        return response()->json([
            'meseage'=> 'we have sent to ur email a code please check it',
            'code'=> $registerUserData['code']
        ],200);

    }

    public function forgetPassword_SetPassword(Request $request){

        $userinfo=$request->validate([
            'email'=>'required|string|email',
            'password'=>'required|min:8|confirmed'
        ]);

       $user=User::firstWhere('email',$userinfo['email']);

        $user->update([
            'password'=>$userinfo['password']
        ]);

        return response()->json([
            'message'=>'password updated'     
        ],200);
    }
 
    public function profile(){

        return response()->json([
            'data'=>auth()->user(),
        ],200);
    }


}
