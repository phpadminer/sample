<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;
use Auth;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',
            [
                'except'=>['show','create','store','index']
            ]
        );
        //指定只允许非登录用户可以访问的页面或执行的动作
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        $users = User::paginate(10);
        // compact()http://php.net/manual/zh/function.compact.php
        return view('users.index',compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
           'name' => 'required|max:50',
           'email' => 'required|email|unique:users|max:255',
           'password' => 'required|confirmed|min:6'
       ]);
       $user = User::create([
           'name'=>$request->name,
           'email'=>$request->email,
           'password'=>bcrypt($request->password)
       ]);

       Auth::login($user);
       session()->flash('success','欢迎，您将在这里开启一段新的旅程~');
       return redirect()->route('users.show',[$user]);
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);//验证授权策略
        return view('users.edit',compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            //nullable 这个验证机制指的是就算用户没有输入密码也可以通过验证！
            'password' => 'nullable|confirmed|min:6'
        ]);
        //这种方法可以在用户只需要修改某一个项目的时候，其他的项目的原有数据会自动填充！避免需要用户重复提交！提升用户的使用友好度！
        $this->authorize('update', $user);//验证授权策略
        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        $this->authorize('destory',$user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}