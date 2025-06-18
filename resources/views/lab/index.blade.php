@extends('fe.master')
@section('content')
    <section id="portfolio" class="portfolio section">

        <!-- Section Title -->
        <div class="container section-title" style="animation: float 2s ease-in-out infinite;
" data-aos="fade-up">
            <span>Simulation Labs Collection</span>
            <h2>Simulation Labs Collection</h2>
            <p>Discover simulation labs focused on fiber loss measurement.</p>
        </div><!-- End Section Title -->

        <div class="container">

            <div class="card card-rounded shadow" style="border: 0" data-aos="fade-up">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold" style="color: #10BC69">Labs Table</h3>
                        <a href="{{ route('lab.create') }}" class="btn w-5 text-white fw-bold"
                            style="background: linear-gradient(87deg, #2dce89 0, #10BC69 100%)">
                            <i class="bi bi-plus-circle me-1"></i> Create My Lab
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle" style="border-color: #10BC69">
                            <thead>
                                <tr>
                                    <th>Author</th>
                                    <th>Lab Name</th>
                                    <th>Description</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($labs as $lab)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <span
                                                        class="badge badge-online rounded-pill px-3 py-2">{{ $lab->author }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-muted">{{ $lab->name }}</div>
                                        </td>
                                        <td>
                                            <small>{{ $lab->description }}</small>
                                        </td>
                                        <td>{{ $lab->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="#" class="text-primary me-3 text-decoration-none">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                            <a href="#" class="text-danger text-decoration-none">
                                                <i class="bi bi-trash-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center text-muted"
                                                style="animation: float 2s ease-in-out infinite;
">
                                                <i class="bi bi-emoji-frown" style="font-size: 3rem; color: #ccc;"></i>
                                                <h5 class="mt-3">No Labs Found</h5>
                                                <p class="mb-0">🧪 Let’s create your first simulation lab!</p>
                                                <a href="{{ route('lab.create') }}" class="btn mt-3 text-white fw-bold"
                                                    style="background: linear-gradient(87deg, #2dce89 0, #10BC69 100%)">
                                                    <i class="bi bi-plus-circle me-1"></i> Create Lab Now
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>

    </section>
@endsection
