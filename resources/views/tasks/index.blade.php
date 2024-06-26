@extends('layouts.app')

@section('content')
<div class="md:mx-4 relative overflow-hidden">
    <main class="h-full flex flex-col overflow-auto">
        <trello-board :initial-data="{{ $tasks }}"></trello-board>
    </main>
</div>
@endsection
