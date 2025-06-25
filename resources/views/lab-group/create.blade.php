@extends('fe.master')
@section('content.create')
    <section id="create_folder" class="hero section" data-aos="fade-up">
        <div class="container section-title" style="animation: float 2s ease-in-out infinite;" data-aos="fade-up">
            <span>Create New Folder<br></span>
            <h1 class="fw-bold">Create New Folder</h1>
            <p>Create a folder to organize your labs</p>
        </div>

        <div class="container">
            <div class="row">
                <form method="POST" action="{{ route('lab-group.store') }}">
                    @csrf

                    @if ($breadcrumbs ?? false)
                        <div class="mb-4">
                            <strong>📂 Parent Folder:</strong>
                            <span>
                                root
                                @foreach ($breadcrumbs as $b)
                                    / {{ $b['name'] }}
                                @endforeach
                            </span>
                        </div>
                        <input type="hidden" name="parent_id" value="{{ $selectedGroup }}">
                    @endif

                    <div class="mb-4">
                        <label for="name" class="form-label">Folder Name</label>
                        <input type="text" class="form-control" name="name" placeholder="My Folder" required>
                        @error('name')
                            <div class="text-danger mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn text-white fw-bold"
                        style="background: linear-gradient(87deg, #2dce89 0, #10BC69 100%)">
                        <i class="bi bi-folder-plus me-1"></i> Create Folder
                    </button>

                    <a href="{{ route('lab') }}" class="btn text-white fw-bold"
                        style="background: linear-gradient(87deg, #627594 0, #8898aa 100%); border: none;">
                        Back
                    </a>
                </form>
            </div>
        </div>
    </section>
@endsection
