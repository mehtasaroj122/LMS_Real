@extends('Admin.layouts.app')

@section('title', 'Manage Book Copies')

@push('styles')
    @include('shared.action-feedback.styles')
    @include('shared.student-portal-pagination.styles')
    @include('shared.table-skeleton.styles')
    @include('shared.book-copies.styles')
@endpush

@section('content')
    @include('shared.book-copies.page', ['role' => 'admin'])
@endsection

@push('scripts')
    @include('shared.action-feedback.scripts')
    @include('shared.book-copies.scripts')
@endpush
