<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth', [
            'only' => ['edit', 'update', 'destroy']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);

    }

    public function index()
    {
        $users = User::paginate(5);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show($id)
    {
        $user = User::findOrfail($id);

        $statuses = $user->statuses()
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

        return view('users.show', compact('user', 'statuses'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        /*Auth::login($user);
        session()->flash('success', '注册成功，欢迎加入我的Laravel网站');*/
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收, 并激活！');
        return redirect('/');

    }


    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update($id, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'confirmed|min:6'
        ]);

        $user = User::findOrFail($id);

        $this->authorize('update', $user);

        $updateData = [];

        $updateData['name'] = $request->name;

        if($request->password){
            $updateData['password'] = bcrypt($request->password);
        }

        $user->update($updateData);

        session()->flash('success', '更新个人资料成功！');

        return redirect()->route('users.show', $id);

    }

    public function destroy($id)
    {
        $user = User::find($id);

        $this->authorize('destroy', $user);

        $user->delete();

        session()->flash('success', '删除用户成功！');

        return back();
    }


    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'onenameneo@gmail.com';
        $name = 'Neo';
        $to = $user->email;
        $subject = '感谢注册 Sample 应用！请确认你的邮箱。';


        Mail::send($view, $data, function($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }


    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activation_token = null;
        $user->activated = true;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings($id)
    {
        $user = User::findOrFail($id);

        $users = $user->followings()->paginate(10);

        $title = '关注的人';

        return view('users.show_follow', compact('users', 'title'));
    }


    public function followers($id)
    {
        $user = User::findOrFail($id);

        $users = $user->followers()->paginate(10);

        $title = '我的粉丝';

        return view('users.show_follow', compact('users', 'title'));
    }


}
