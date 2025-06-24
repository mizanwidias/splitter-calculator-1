@extends('fe.master')
@section('content')
    <div class="container mt-5">
        <div class="row">
            <!-- Sidebar: Workspace -->
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-folder2-open me-2"></i> Workspace</strong>
                        <div>
                            <div class="dropdown">
                                <button class="btn fw-bold dropdown-toggle text-white" type="button" data-bs-toggle="dropdown"
                                    style="border-color: #10BC69; border-radius: 25px; background: #10BC69;">
                                    + New
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="openLabCreate()">🧪 New Lab</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" onclick="openFolderCreate()">📁 New
                                            Folder</a></li>
                                </ul>
                            </div>
                            <input type="hidden" id="currentFolderId" value="0">
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div><strong>📂 Path:</strong> <span id="breadcrumb-path">root</span></div>
                            <button onclick="loadFolder(0)" class="btn btn-sm btn-outline-secondary">⬅ Back to Root</button>
                        </div>
                        <input type="text" id="searchInput" class="form-control mb-3" placeholder="Search Labs...">
                        <ul class="list-group" id="lab-list">
                            <li class="list-group-item text-center text-muted">
                                <i class="bi bi-hourglass-split"></i> Loading...
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="col-md-7">
                <div class="card h-100 shadow">
                    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                        <div class="fw-bold"><i class="bi bi-grid"></i> Lab Preview</div>
                        <div id="lab-author" class="fw-bold text-end text-white">
                        </div>
                    </div>
                    <div class="card-body" id="preview-panel">
                        <p class="text-muted">No preview available.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert Toast -->
    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    @endif

    <!-- JS Logic -->
    <script>
        function loadFolder(folderId = 0, search = '') {
            // Simpan folderId ke input hidden
            document.getElementById('currentFolderId').value = folderId;

            const url = `/lab/folder/${folderId}?search=${encodeURIComponent(search)}`;

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    // Update breadcrumb
                    const breadcrumb = res.breadcrumbs.map(b =>
                        `<a href="#" onclick="loadFolder(${b.id})" class="text-primary">${b.name}</a>`
                    ).join(" / ");
                    document.getElementById('breadcrumb-path').innerHTML = 'root' + (breadcrumb ? ' / ' + breadcrumb :
                        '');

                    const list = [];

                    // Tombol kembali ke atas folder
                    if (res.currentFolder && res.currentFolder.id !== 0) {
                        const parentId = res.currentFolder.parent_id ?? 0;
                        list.push(`<li class="list-group-item" onclick="loadFolder(${parentId})" style="cursor: pointer;">
                        <i class="bi bi-folder-fill text-warning me-2"></i> ..
                    </li>`);
                    }

                    // Folder render
                    res.folders.forEach(folder => {
                        const isMissing = folder.is_missing;
                        list.push(`
<li class="list-group-item d-flex justify-content-between align-items-center ${isMissing ? 'bg-warning-subtle' : ''}"
    ${isMissing ? 'style="cursor: not-allowed;"' : `onclick="loadFolder(${folder.id})" style="cursor: pointer;"`}>
    <div>
        <i class="bi bi-folder-fill text-warning me-2"></i>
        ${isMissing
            ? `<i class="bi bi-info-circle-fill text-danger me-1" data-bs-toggle="tooltip" title="Folder ini tidak ditemukan di file system."></i>`
            : ''
        }
        <span>${folder.name} ${isMissing ? '<span class="badge bg-danger">Missing</span>' : ''}</span>
    </div>
    <div>
        ${isMissing
            ? `
                    <a href="#" onclick="event.stopPropagation(); restoreFolder(${folder.id})" data-bs-toggle="tooltip" title="Restore Folder" class="text-success me-2">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                    <a href="#" onclick="event.stopPropagation(); deleteFolderOnlyDb(${folder.id}, '${folder.name}')" data-bs-toggle="tooltip" title="Delete from DB" class="text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>`
            : `
                    <a href="#" onclick="event.stopPropagation(); showRenamePrompt(${folder.id}, '${folder.name}')" data-bs-toggle="tooltip" title="Rename Folder" class="text-primary me-2">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="#" onclick="event.stopPropagation(); confirmDeleteFolder(${folder.id}, '${folder.name}')" data-bs-toggle="tooltip" title="Delete Folder" class="text-danger">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                    <form id="form-delete-folder-${folder.id}" action="/lab-group/${folder.id}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>`
        }
    </div>
</li>`);
                    });

                    // Lab render
                    res.labs.forEach(lab => {
                        const isMissing = lab.is_missing;
                        list.push(`
<li class="list-group-item d-flex justify-content-between align-items-center ${isMissing ? 'bg-warning-subtle' : ''}"
    ${isMissing ? '' : `onclick="previewLab(${lab.id})" style="cursor: pointer;"`}>
    <div>
        <i class="bi bi-file-earmark-fill text-primary me-2"></i>
        ${isMissing
            ? `<i class="bi bi-info-circle-fill text-danger me-1" data-bs-toggle="tooltip" title="File lab hilang di file system."></i>`
            : ''
        }
        <span>${lab.name} ${isMissing ? '<span class="badge bg-danger">Missing</span>' : ''}</span>
    </div>
    <div>
        ${isMissing
            ? `
                    <a href="#" onclick="event.preventDefault(); event.stopPropagation(); restoreLab(${lab.id})" data-bs-toggle="tooltip" title="Restore Lab" class="text-success me-2">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                    <a href="#" onclick="event.preventDefault(); event.stopPropagation(); deleteLabOnlyDb(${lab.id}, '${lab.name}')" data-bs-toggle="tooltip" title="Delete from DB" class="text-danger">
                        <i class="bi bi-x-circle-fill"></i>
                    </a>`
            : `
                    <a href="/lab/${lab.id}/topologi" class="text-success me-2" onclick="event.stopPropagation()" data-bs-toggle="tooltip" title="Edit Lab">
                        <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="#" class="text-danger" onclick="event.preventDefault(); event.stopPropagation(); confirmDelete(${lab.id}, '${lab.name}')" data-bs-toggle="tooltip" title="Delete Lab">
                        <i class="bi bi-trash-fill"></i>
                    </a>
                    <form id="form-delete-${lab.id}" action="/lab/${lab.id}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>`
        }
    </div>
</li>`);
                    });

                    if (list.length === 0) {
                        list.push(`<li class="list-group-item text-center text-muted">
                        <i class="bi bi-emoji-frown"></i> No labs or folders found.
                    </li>`);
                    }

                    document.getElementById('lab-list').innerHTML = list.join("");
                    document.getElementById('preview-panel').innerHTML =
                        `<p class="text-muted">No preview available.</p>`;

                    // Aktifkan tooltip
                    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                    tooltipTriggerList.map(function(el) {
                        return new bootstrap.Tooltip(el);
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            searchInput.addEventListener('input', function() {
                const currentFolderId = document.getElementById('currentFolderId').value || 0;
                loadFolder(currentFolderId, this.value.trim());
            });

            // Inisialisasi pertama
            loadFolder(0);
        });
    </script>
@endsection
