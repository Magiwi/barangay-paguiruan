@extends('layouts.public')

@section('title', $page->title)

@section('content')
<section class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
    <h1 class="text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl">{{ $page->title }}</h1>
    @if ($page->published_at)
        <p class="mt-2 text-xs text-gray-500">Published {{ $page->published_at->timezone(config('app.timezone'))->format('M j, Y') }}</p>
    @endif
    <article class="cms-body mt-8 max-w-none text-gray-800 [&_a]:text-blue-600 [&_a]:underline [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-semibold [&_h3]:mt-6 [&_h3]:text-lg [&_h3]:font-semibold [&_li]:my-1 [&_ol]:my-4 [&_ol]:list-decimal [&_ol]:pl-6 [&_p]:leading-relaxed [&_ul]:my-4 [&_ul]:list-disc [&_ul]:pl-6">
        {!! $html !!}
    </article>
</section>
@endsection
