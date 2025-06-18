@extends('fe.master')
@section('content.hero')
    @include('fe.hero')
@endsection
@section('loss_calculator')
    @include('fe.loss_calculator')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
@section('topology-simulation')
    @include('fe.topology-simulation')
@endsection
