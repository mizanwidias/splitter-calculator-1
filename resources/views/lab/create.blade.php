@extends('fe.master')
@section('content.create')
    <section id="create_lab" class="hero section" data-aos="fade-up">

        <div class="container section-title" style="animation: float 2s ease-in-out infinite;
" data-aos="fade-up">
            <span>Make Your Own Lab<br></span>
            <h1 class="fw-bold">Make Your Own Lab</h1>
            <p>Create your own lab with your own name</p>
        </div>

        <div class="container">
            <div class="row">
                <form method="POST" action="{{ route('lab.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="form-label">Lab Name</label>
                        <input type="text" class="form-control" id="spliceLoss" placeholder="My Lab" required
                            name="name" value="{{ old('name') }}" />
                        @error('name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="author" class="form-label">Author Name</label>
                        <input type="text" class="form-control" name="author" placeholder="John Doe" required
                            value="{{ old('author') }}" />
                        @error('author')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn w-5 text-white fw-bold"
                        style="background: linear-gradient(87deg, #2dce89 0, #10BC69 100%);">
                        Create
                    </button>

                    <a href="{{ route('lab') }}" class="btn w-5 text-white fw-bold"
                        style="background: linear-gradient(87deg, #627594 0, #8898aa 100%);">
                        Back
                    </a>
                </form>
            </div>
        </div>
    </section>
@endsection
