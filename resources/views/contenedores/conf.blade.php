@extends('layouts.argon')

@section('content')


    <div class="row">
        <div class="col-md-6 order-2 order-md-1">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>
            </div>
        </div>

        <div class="col-md-6 order-1 order-md-2">
            <div class="card shadow-lg">
                <div class="card-body">

                </div>

            </div>
        </div>
    </div>


    <div class="d-flex flex-wrap gap-2 widgets-panel mt-2 mb-2">
        @foreach($widgets as $widget)
            <div class="widget-item" data-id="{{ $widget->id }}">
                <div class="widget-pill" style="cursor: grab;">
                    <span class="widget-name">{{ $widget->nombre }}</span>
                </div>
            </div>
        @endforeach
    </div>

    @include('contenedores._constructor')
@endsection