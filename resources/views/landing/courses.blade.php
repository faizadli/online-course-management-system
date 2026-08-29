@extends('layouts.landing')

@section('title', 'Courses — SkillForge')

@section('content')

@include('partials.page-header', [
    'eyebrow' => 'Our Courses',
    'title' => 'Trending Courses This Week',
    'description' => 'Discover expert-led courses across design, technology, business, and personal development — built to help you learn by doing.',
])

@include('partials.sections.courses-catalog')

@endsection
