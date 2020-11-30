<?php

namespace app\test\controller;

use app\test\model\Cart;
use app\test\model\Goods;
use app\test\model\User;
use think\Controller;
use think\Request;

class Test extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return view();
    }

    /**
     * 用户登录验证
     *
     * @return \think\Response
     */
    public function login(Request $request)
    {
        $param=$request->param();
        //非空验证
        $result = $this->validate(
            $param,
            [
                'name'  => 'require',
                'pwd'   => 'require',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        $pwd=md5(md5($param['pwd']));
        $data=User::where('name',$param['name'])->where('pwd',$pwd)->find();
        if ($data){
            session_start();
            session('user',$data);
            return redirect("Test/show");
        }else{
            $this->error('账户或密码错误');
        }
    }

    //展示所有商品
    public function show(){
        $data=Goods::select()->toArray();
       // dump($data);
       $name= session('user')['name'];
        return view('',['list'=>$data,'name'=>$name]);
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function cart(Request $request)
    {
        //判断登录状态
        if (empty(session('user'))){
            $this->error('请先登录',"Test/index");
        }
        //登录成功了，接值验证发送入cart
        $param=$request->param();
        $result = $this->validate(
            $param,
            [
                'number'  => 'require|number',
            ]);
        if(true !== $result){
            // 验证失败 输出错误信息
            $this->error($result);
        }
        //dump($param);
        //根据ID，查出库存对比
        $data=Goods::find($param['goods_id'])->toArray();
        if ($param['number']>$data['inventory']){
            $this->error("没有更多存货");
        }
        //添加进入cart表，展示成功加入页面
       // dump($data);
        $user_id=session('user')['id'];
        //判断购物车列表是否已经存在改商品
        $cart=Cart::where('goods_id',$param['goods_id'])->where('user_id',$user_id)->find()->toArray();
        //dump($cart);
        $arr=[];
        if ($cart){
            //存在就商品数量增加
            $cart['goods_number']+=$param['number'];
            $res=Cart::update($cart);
        }else{
            //不存在就新添加商品
            $arr=[
                'user_id'=>$user_id,
                'goods_id'=>$param['goods_id'],
                'goods_number'=>$param['number']
            ];
            $res=Cart::create($arr);
        }
        if ($res){
            return view('',['vo'=>$data,'vp'=>$param['number']]);
        }
    }

    //购物车列表
    public function cartlist(){
        $list=Cart::with('cartUser,cartGoods')->select()->toArray();
       // dump($list);
        return view('',['list'=>$list]);
    }
    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($goods_id)
    {
        //dump($goods_id);
        $data=Goods::find($goods_id);
        return view('',['vo'=>$data]);
    }

    /**
     * 订单页面
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function order(Request $request)
    {
        $param=$request->param();
        return view('',['list'=>$param]);
    }


}
