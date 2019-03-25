<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Hash;
use SebastianBergmann\Environment\Console;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return User::latest()->paginate(10);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $this->validate($request,[
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
        ]);
        return User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => Hash::make($request['password']),
            'type' => $request['type'],
            'bio' => $request['bio'],
            'photo' => $request['photo']
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function getCurrentUser()
    {
        //
        return auth('api')->user();
    }

    public function profile()
    {
        //
        return auth('api')->user();
    }

    public function updateProfile(Request $request)
    {
        //
        $user = auth('api')->user();
        $photoName = $user->photo;
        if($request->photo && $request->photo != $photoName){
            $extenstion = explode('/', explode(':', 
            substr($request->photo, 0, strpos($request->photo, ';')))[1])[1];
            
            $photoName = time().'.'.$extenstion;
            \Image::make($request->photo)->
            resize(128,128)->
            save(public_path('img/profile/').$photoName);
            
            $request->merge(['photo' => $photoName]);
            $userPhoto = public_path('img/profile/').$user->photo;
            if(file_exists($userPhoto)){
                @unlink($userPhoto);
            }
        }
        
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'sometimes|string|min:8',
        ]);
        if($request->password != ""){
            Hash::make($request['password']);
        }else{
            $request->merge(['password' => $user->password]);
        }
        $user->update($request->all());
        return ['message' => 'Updated user successfully', 'photoName' => $photoName];
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $user = User::findOrFail($id);
        $this->validate($request,[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8',
        ]);
        if($request->password != ""){
            Hash::make($request['password']);
        }else{
            $request->merge(['password' => $user->password]);
        }
        $user->update($request->all());
        return ['message' => 'Updated user successfully'];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->authorize('isAdmin');
        //
        $user = User::findOrFail($id);

        //delete the user
        $user->delete();

        return ['message' => 'User Deleted Successfully!'];
    }
}
