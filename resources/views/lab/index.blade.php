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
                        <table class="table table-hover align-middle text-center" style="border-color: #10BC69">
                            <thead>
                                <tr>
                                    <th style="border-right: 2px solid #10BC69">#</th> <!-- Garis vertikal pemisah -->
                                    <th>Author</th>
                                    <th>Lab Name</th>
                                    <th>Description</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($labs as $no => $lab)
                                    <tr>
                                        <td style="font-weight: bold; border-right: 2px solid #10BC69">
                                            {{ ($labs->currentPage() - 1) * $labs->perPage() + $no + 1 . '.' }}
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-online rounded-pill px-3 py-2">{{ $lab->author }}</span>
                                        </td>
                                        <td>
                                            <div class="text-muted">{{ $lab->name }}</div>
                                        </td>
                                        @if (strlen($lab->description ?? '-') > 20)
                                            <td data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ $lab->description }}">
                                                <small>{{ substr($lab->description, 0, 20) . '...' }}</small>
                                            </td>
                                        @else
                                            <td><small>{{ $lab->description ?? '-' }}</small></td>
                                        @endif
                                        <td>{{ $lab->created_at->translatedFormat('d F Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('lab.canvas', $lab->id) }}"
                                                class="text-primary me-3 text-decoration-none">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>

                                            <form id="form-delete-{{ $lab->id }}"
                                                action="{{ route('lab.destroy', $lab->id) }}" method="POST"
                                                class="d-none">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="button"
                                                class="btn btn-link text-danger p-0 m-0 align-baseline text-decoration-none btn-delete"
                                                data-id="{{ $lab->id }}" data-name="{{ $lab->name }}">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <div class="d-flex flex-column align-items-center text-muted"
                                                style="animation: float 2s ease-in-out infinite;">
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

                        <!-- Hanya Tampilkan Text Showing di kiri -->
                        <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                            <div class="text-muted">
                                Showing <span style="color: #10BC69">{{ $labs->firstItem() }}</span> to <span
                                    style="color: #10BC69">{{ $labs->lastItem() }}</span> of <span
                                    style="color: #10BC69">{{ $labs->total() }}</span>
                                results
                            </div>
                            <div>
                                {{-- Hanya tampilkan pagination --}}
                                {!! $labs->links() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');

                    Swal.fire({
                        title: 'Delete Lab?',
                        html: `<div class="swal-float"><strong>${name}</strong> will be removed.</div>`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        },
                        didOpen: () => {
                            // Tambahkan class float saat muncul
                            document.querySelector('.swal2-popup')?.classList.add(
                                'float-style');
                        },
                        willClose: () => {
                            // Hapus class float saat ditutup supaya fadeOut tidak terganggu
                            document.querySelector('.swal2-popup')?.classList.remove(
                                'float-style');
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById(`form-delete-${id}`).submit();
                        }
                    });
                });
            });
        });
    </script>
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });

                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            });
        </script>
    @endif
@endsection
