<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests;
use Auth;
use Mail;

class UsersController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',
            [
                'except'=>['show','create','store','index','confirmEmail']
            ]
        );
        //指定只允许非登录用户可以访问的页面或执行的动作
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    /*
    *展示所有的用户！
    */
    public function index()
    {
        $users = User::paginate(10);
        // compact()http://php.net/manual/zh/function.compact.php
        return view('users.index',compact('users'));
    }
    /**
    * 注册页面方法
    */
    public function create()
    {
        return view('users.create');
    }
    /**
    * 用户详情页面
    */
    public function show(User $user)
    {
        // 查出该用户关联的微博
        $statuses = $user->statuses()
                           ->orderBy('created_at', 'desc')
                           ->paginate(30);
        return view('users.show', compact('user', 'statuses'));
    }

    /**
    * 执行注册功能
    */
    public function store(Request $request)
    {
        //验证表单闯过来的数据
        $this->validate($request, [
           'name' => 'required|max:50',
           'email' => 'required|email|unique:users|max:255',
           'password' => 'required|confirmed|min:6'
       ]);
       //如果验证成功的话写入数据库
       $user = User::create([
           'name'=>$request->name,
           'email'=>$request->email,
           'password'=>bcrypt($request->password)
       ]);
       //激活邮箱操作！
       $this->sendEmailConfirmationTo($user);
       //提示登录成功信息
       session()->flash('success','验证邮件已发送到你的注册邮箱上，请注意查收。');
       //跳转到用户详情
       return redirect()->route('users.show',[$user]);
    }
    /*
    *  用户信息编辑页面
    *
    */
    public function edit(User $user)
    {
        $this->authorize('update', $user);//验证授权策略
        return view('users.edit',compact('user'));
    }
    /*
    *  用户信息信息更新
    *
    */
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            //nullable 这个验证机制指的是就算用户没有输入密码也可以通过验证！
            'password' => 'nullable|confirmed|min:6'
        ]);

        $this->authorize('update', $user);//验证授权策略
        $data = [];
        $data['name'] = $request->name;
        //这种方法可以在用户只需要修改某一个项目的时候，其他的项目的原有数据会自动填充！避免需要用户重复提交！提升用户的使用友好度！
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

    /*
    *删除用户功能
    */
    public function destroy(User $user)
    {
        // 用户删除安全策略
        $this->authorize('destory',$user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    /*
    *发送激活邮件功能
    */
    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'aufree@yousails.com';
        $name = 'Aufree';
        $to = $user->email;
        $subject = "感谢注册 Sample 应用！请确认你的邮箱。";
        Mail::send($view,$data,function($message) use ($from,$name,$to,$subject){
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    /*
    *激活邮箱验证！
    */
    public function confirmEmail($token)
    {
        //用网址传输过来的token 在表中的激活字段中匹配对应的用户 如果找到用户 就执行喜爱不 否则返回404
        $user = User::where('activation_token', $token)->firstOrFail();
        //如果找到这个用户 就将激活状态变为true
        $user->activated = true;
        //将激活token设置为空
        $user->activation_token = null;
        $user->save();
        //登录
        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user)
    {
        $users = $user->followings()->paginate(30);
        $title = '关注的人';
        return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
        $users = $user->followers()->paginate(30);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}