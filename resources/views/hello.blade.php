@extends('plugin::layout')

@section('content')
    <div class="aui-page-panel">
        <section class="aui-page-panel-content">
            <h1>Congratulations, Add-on works!</h1>
            <p>
                You are authenticated using <strong>JWT</strong> to tenant with <strong>ID {{ Auth::id() }}</strong>
                ({{ Auth::user()->isDummy() ? 'dummy' : 'not dummy' }})
            </p>
            <p>
                That's up to you.
            </p>
        </section>
    </div>
@endsection