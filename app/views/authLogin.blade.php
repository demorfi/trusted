@extends('template')

@section('content')
    <div class="page-header">
        <h1>Please sign in</h1>
    </div>

    {{ Form::open(['route' => 'auth-login-path', 'class' => 'form-horizontal', 'role' => 'form']) }}
		<div class="form-group">
			{{ Form::label('username', 'Username', ['class' => 'col-sm-1 control-label']) }}
            <div class="col-sm-11">
                {{ Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Username', 'required' => true]) }}
            </div>
		</div>
		<div class="form-group">
		    {{ Form::label('password', 'Password', ['class' => 'col-sm-1 control-label']) }}
            <div class="col-sm-11">
                {{ Form::password('password', ['class' => 'form-control', 'placeholder' => 'Password', 'required' => true]) }}
            </div>
	    </div>
        <div class="form-group">
            <div class="col-sm-offset-1 col-sm-11">
                {{ Form::submit('Sign In', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
    {{ Form::close() }}
@stop

@section('js')
    {{ HTML::script('js/require.js', ['data-main' => 'js/init']) }}
@stop