<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Auth;

class SessionsController extends Controller
{

    public function __construct()
    {
        //指定只允许未登录用户访问的页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }
    /*
    * 登录界面
    */
    public function create()
   {
       return view('sessions.create');
   }

   /*
   *验证登录信息
   */
   public function store(Request $request)
    {
       // 验证登录信息
       $credentials = $this->validate($request, [
           'email' => 'required|email|max:255',
           'password' => 'required'
       ]);

       // 如果验证信息和服务器中的数据一致就让他登录
       // 或者如果这个用户已经选择了记住我 的功能 就直接登录
       if (Auth::attempt($credentials, $request->has('remember')))
            //如果用户没有被激活，那么让他点击邮箱中的激活码激活！
           if(Auth::user()->activated){
               session()->flash('success', '欢迎回来！');
               //如果用户是以游客身份访问需要登录用户权限的页面时，在登录后重定向到之前的那个页面，如果前一个页面为空就直接跳转到默认页面
               return redirect()->intended(route('users.show', [Auth::user()]));
           }else{
               Auth::logout();
               session->flash('warning','你的账号未激活，请检查邮箱中的注册邮件进行激活。');
               return redirect('/');
           }

       } else {
           session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
           return redirect()->back();
       }
    }

   /*
   *退出登录功能
   */
   public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}