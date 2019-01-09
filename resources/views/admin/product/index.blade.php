@extends('layouts.admin')

@section('title', '产品列表')

@section('css')
    <link rel="stylesheet" href="{{ asset('statics/editormd/css/editormd.min.css') }}">
    <link rel="stylesheet" href="{{ asset('statics/iCheck-1.0.2/skins/all.css') }}">
    <link rel="stylesheet" href="{{ asset('statics/gentelella/vendors/switchery/dist/switchery.min.css') }}">
    <link href="{{ asset('statics/jasny-bootstrap/css/jasny-bootstrap.min.css') }}" rel="stylesheet">
@endsection

@section('nav', '产品列表')

@section('description', '已发布的产品列表')

@section('content')

    <!-- 导航栏结束 -->
    <ul id="myTab" class="nav nav-tabs bar_tabs">
        <li class="active">
            <a href="{{ url('admin/product/index') }}">产品列表</a>
        </li>
        <li>
            <a href="{{ url('admin/product/create') }}">添加产品</a>
        </li>
    </ul>
    <table class="table table-striped table-bordered table-hover">
        <tr>
            <th>产品序号</th>
            <th>排序</th>
            <th>产品标题</th>
            <th>产品封面图</th>
            <th>产品简介</th>
            <th>是否热卖</th>
            <th>产品原价</th>
            <th>产品销量</th>
            <th>创建时间</th>
        </tr>
        @foreach($data as $k => $v)
            <tr>
                <td>{{ $v->id }}</td>
                <td width="5%">
                    <input class="form-control" type="text" name="{{ $v->id }}" value="{{ $v->sort }}">
                </td>
                <td>{{ $v->title }}</td>
                <td>{{ $v->cover }}</td>
                <td>{{ $v->description }}</td>
                <td>
                    @if($v->is_hot)
                        √
                    @else
                        ×
                    @endif
                </td>
                <td>{{ $v->origin_price }}</td>
                <td>{{ $v->sales_count }}</td>
                <td>{{ $v->created_at }}</td>
                <td>
                    <a href="{{ url('admin/article/edit', [$v->id]) }}">编辑</a>
                    |
                    @if($v->trashed())
                        <a href="javascript:if(confirm('确认恢复?'))location.href='{{ url('admin/article/restore', [$v->id]) }}'">恢复</a>
                        |
                        <a href="javascript:if(confirm('彻底删除?'))location.href='{{ url('admin/article/forceDelete', [$v->id]) }}'">彻底删除</a>
                    @else
                        <a href="javascript:if(confirm('确认删除?'))location.href='{{ url('admin/article/destroy', [$v->id]) }}'">删除</a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
    <div class="text-center">
        {{ $data->links('vendor.pagination.default') }}
    </div>

@endsection
