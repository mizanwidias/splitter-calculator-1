function previewLab(labId) {
    fetch(`/lab/preview/${labId}`)
        .then(res => {
            if (!res.ok) throw new Error('File not found');
            return res.json();
        })
        .then(data => {
            // Tampilkan isi JSON lab
            document.getElementById('preview-panel').innerHTML = `
                <pre class="bg-light p-3 rounded text-start" style="font-size: 0.9em; max-height: 400px; overflow-y:auto;">
${JSON.stringify(data, null, 2)}
                </pre>`;

            // Tampilkan author di kanan atas card-header
            document.getElementById('lab-author').innerHTML = `
                <i class="bi bi-person-fill me-1"></i> ${data.author || '-'}`;
        })
        .catch(() => {
            document.getElementById('preview-panel').innerHTML =
                `<p class="text-danger">File not found or missing on disk.</p>`;
            document.getElementById('lab-author').innerHTML = ''; // kosongkan author jika gagal
        });
}


        function openLabCreate() {
            const currentId = document.getElementById('currentFolderId').value;
            window.location.href = `/lab/create?parent=${currentId}`;
        }

        function openFolderCreate() {
            const currentId = document.getElementById('currentFolderId').value;
            window.location.href = `/lab-group/create?parent=${currentId}`;
        }

        function showRenamePrompt(folderId, currentName) {
            Swal.fire({
                title: 'Rename Folder',
                input: 'text',
                inputLabel: 'New name',
                inputValue: currentName,
                showCancelButton: true,
                confirmButtonText: 'Rename',
                preConfirm: (newName) => {
                    return fetch(`/lab-group/${folderId}/rename`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                name: newName
                            })
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success) throw new Error(data.message);
                            return data;
                        })
                        .catch(error => {
                            Swal.showValidationMessage(`Rename failed: ${error.message}`);
                        });
                }
            }).then(result => {
                if (result.isConfirmed && result.value?.success) {
                    // ✅ Refresh folder list
                    loadFolder(document.getElementById('currentFolderId').value);

                    // ✅ Toast sukses
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Folder berhasil diubah!',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true
                    });
                }
            });
        }

        function confirmDeleteFolder(folderId, folderName) {
            fetch(`/lab-group/${folderId}/check-contents`)
                .then(res => res.json())
                .then(data => {
                    let warnings = [];

                    if (data.hasLabs) {
                        warnings.push(`⚠ ${data.totalLabs} lab`);
                    }

                    if (data.hasFolders) {
                        warnings.push(`⚠ ${data.totalFolders} subfolder`);
                    }

                    const warningHtml = warnings.length > 0 ?
                        `<br><small class="text-danger">${warnings.join(' dan ')} akan ikut terhapus!</small>` :
                        '';

                    Swal.fire({
                        title: 'Delete Folder?',
                        html: `Folder <strong>${folderName}</strong> akan dihapus.${warningHtml}`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then(result => {
                        if (result.isConfirmed) {
                            document.getElementById(`form-delete-folder-${folderId}`).submit();
                        }
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'Gagal memeriksa isi folder.', 'error');
                });
        }

        function confirmDelete(id, name) {
            Swal.fire({
                title: 'Delete Lab?',
                html: `<strong>${name}</strong> will be removed.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`form-delete-${id}`).submit();
                }
            });
        }

        // Initial load
        window.addEventListener("DOMContentLoaded", () => loadFolder());

        function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
}

function restoreLab(id) {
    fetch(`/restore/lab/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Restored!', 'Lab berhasil direstore.', 'success');
            loadFolder(document.getElementById('currentFolderId').value);
        }
    });
}

function restoreFolder(id) {
    fetch(`/restore/folder/${id}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Restored!', 'Folder berhasil dibuat ulang.', 'success');
            loadFolder(document.getElementById('currentFolderId').value);
        }
    });
}

function deleteLabOnlyDb(id, name) {
    Swal.fire({
        title: `Hapus lab "${name}" dari database?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/delete-only-db/lab/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Lab dihapus dari database.', 'success');
                    loadFolder(document.getElementById('currentFolderId').value);
                }
            });
        }
    });
}

function deleteFolderOnlyDb(id, name) {
    Swal.fire({
        title: `Hapus folder "${name}" dari database?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hapus',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(`/delete-only-db/folder/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Deleted!', 'Folder dihapus dari database.', 'success');
                    loadFolder(document.getElementById('currentFolderId').value);
                }
            });
        }
    });
}
