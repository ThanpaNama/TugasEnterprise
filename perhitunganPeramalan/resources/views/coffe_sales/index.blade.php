@extends('layout.app')

@section('content')

   <h2>Upload Coffee Sales Excel</h2>

    @if(session('success'))
        <div style="color: green; margin: 10px 0;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('coffee_sales.upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="file" required>
        <button type="submit">Upload</button>
    </form>
@endsection
