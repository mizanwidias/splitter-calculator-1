@extends('fe.lab')
@section('title', 'Lab - ' . $lab['name'])
@section('content')
    <!-- sidebar -->
    <div id="sidebar" class="position-fixed sidebar-hidden">
        <div class="inner p-3">
            <!-- konten sidebar -->
            <a href="{{ route('lab') }}" class="btn w-100 text-white fw-bold"
                style="background: linear-gradient(87deg, #627594 0, #8898aa 100%);
           border-radius: 25px;
           border: none;
           pointer-events: none;">
                <i class="bi bi-person-circle me-1"></i>
                {{ $lab['author'] }}
            </a>
            <div class="divider my-3"></div>
            <div class="mb-2">
                <h5 class="fw-bold text-dark">Input Power OLT</h5>
                <input type="number" id="input-power" class="form-control form-control-sm mb-2" value="7">
            </div>
            <div class="mb-2">
                <h5 class="fw-bold text-dark">Splicing</h5>
                <input type="number" id="splicing" class="form-control form-control-sm mb-2" value="1">
            </div>
            <h5 class="fw-bold text-dark">Add Node</h5>
            <div class="d-grid gap-2 mb-2">
                <button class="btn btn-sm btn-light text-dark fw-bold" onclick="addNode('OLT')">
                    <i class="fas fa-broadcast-tower me-1"></i> OLT
                </button>
                <button class="btn btn-sm btn-light text-dark fw-bold" onclick="addNode('Splitter')">
                    <i class="fas fa-code-branch me-1"></i> Splitter
                </button>
                <!-- d-none hidden -->
                <button class="btn btn-sm btn-outline-primary text-dark d-none" onclick="addNode('ODP')">
                    <i class="fas fa-network-wired me-1"></i> ODP
                </button>
                <button class="btn btn-sm btn-light text-dark fw-bold" onclick="addNode('Client')">
                    <i class="fas fa-user me-1"></i> Client
                </button>
            </div>
            <h5 class="fw-bold text-dark">Cable Type</h5>
            <div class="d-flex gap-2 mb-2">
                <div class="cable-option p-2 bg-light text-dark rounded text-center flex-fill cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: black; margin: auto;"></div>
                    <small>Dropcore</small>
                </div>
                <div class="cable-option p-2 bg-warning text-dark rounded text-center flex-fill cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: yellow; margin: auto;"></div>
                    <small>Patchcord</small>
                </div>
            </div>
            <div class="divider my-3"></div>
            <div class="hidden-ui" style="display: none">


                <label class="form-label text-white">Panjang Kabel (m)</label>
                <input type="number" id="cable-length" class="form-control form-control-sm mb-2" value="50">

                <label class="form-label text-white">Connector</label>
                <input type="number" id="connectors" class="form-control form-control-sm mb-2" value="2">

                <label class="form-label text-white">Splicing</label>
                <input type="number" id="splicing" class="form-control form-control-sm mb-2" value="1">
            </div>

            <div class="d-grid gap-2 mb-2">
                <button class="btn btn-sm btn-light w-100 fw-bold" onclick="gatherAndSaveTopology()"><i
                        class="bi bi-bookmark-plus-fill"></i> Save Topology</button>
                <button class="btn btn-sm btn-light w-100 fw-bold" onclick="resetTopology()">Reset</button>
                <button class="btn btn-sm btn-light w-100 fw-bold" onclick="undoAction()">↩ Undo</button>
            </div>
            <div class="divider my-3"></div>
            <h5 class="fw-bold text-dark"><i class="bi bi-file-earmark-code-fill"></i> Manage File</h5>
            <div class="d-grid gap-2 mb-2">
                <button class="btn btn-sm"
                    style="background: linear-gradient(87deg, #2d93ce 0, #107abc 100%); border: none;"
                    onclick="exportTopology()">⬇️ Export (.json)</button>
                <input type="file" id="import-file" accept=".json" class="form-control form-control-sm"
                    onchange="importTopology(this.files[0])">
            </div>
            <div class="divider my-3"></div>
            <a href="{{ route('lab') }}" class="btn w-100 text-white fw-bold mb-2"
                style="background: linear-gradient(87deg, #627594 0, #8898aa 100%);border-radius: 25px; border: none;"><i
                    class="bi bi-x-circle"></i> Keluar</a>
            <img src="{{ asset('fe/img/hyp-set.png') }}" class="w-100 h-25 mt-3" alt="">
        </div>
    </div>


    <!-- Modal Pilih Splitter -->
    <div class="modal fade" id="splitterModal" tabindex="-1" aria-labelledby="splitterModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="splitterModalLabel">Pilih Tipe Splitter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <select id="splitterTypeSelect" class="form-select">
                        <option value="1:2">1:2</option>
                        <option value="1:4">1:4</option>
                        <option value="1:8">1:8</option>
                        <option value="1:16">1:16</option>
                        <option value="1:32">1:32</option>
                        <option value="1:64">1:64</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="confirmSplitter()">Pilih</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Canvas Full Area -->
    <div id="map-canvas" data-lab-id="{{ $lab['id'] }}" style="position: relative; height: 100vh; ">
    </div>

    {{-- <!-- Jadi style baru: -->
<div id="map-canvas" data-lab-id="{{ $lab['id'] }}"
     style="position: relative; height: 100vh; overflow: hidden; border: 2px dashed #ccc;">
</div> --}}

    <!-- Informasi Loss Panel -->
    <div id="info-card" class="card shadow-sm border border-info d-none"
        style="position: fixed; top: 360px; right: 20px; min-width: 250px;transition: all 0.3s ease-in-out; z-index: 1;">
        <div class="card-body">
            <h5 class="card-title text-info">📊 Informasi Loss</h5>
            <hr>
            <p>Total Loss: <strong id="total-loss" class="text-danger">-</strong> dB</p>
            <p>Output Power: <strong id="power-rx" class="text-primary">-</strong> dBm</p>
            <p>Jalur: <span id="jalur-text" class="text-muted">-</span></p>
            <div id="loss-status" class="mt-3"></div>
        </div>
    </div>

    <!-- Button toggle -->
    <button onclick="toggleStatusTable()" class="btn fw-bold"
        style="background: linear-gradient(87deg, #2dce89 0, #10BC69 100%); position: fixed; bottom: 20px; right: 20px; border-radius: 25px; color: #323233;">
        <i class="bi bi-table"></i> Tabel Status
    </button>

    <!-- Tabel Status Power Loss -->
    <div id="status-table-box" class="bg-white border rounded shadow-lg p-3 mt-3"
        style="width: 350px;display: none; animation: float 2s ease-in-out infinite; z-index: 2;">
        <h5 class="mb-3 text-black text-center">📋 Tabel Status Power Loss</h5>
        <div class="table-responsive">
            <table class="table table-striped table-dark text-center align-middle rounded overflow-hidden">
                <thead class="table-success text-dark">
                    <tr>
                        <th style="font-size: 13px;">Power Loss</th>
                        <th style="font-size: 13px;">Keterangan</th>
                        <th style="font-size: 13px;">Status</th>
                    </tr>
                </thead>
                <tbody style="font-size: 13px;">
                    <tr>
                        <td>&gt; 0</td>
                        <td>Invalid</td>
                        <td><span class="badge bg-secondary px-3 py-1">⚪️</span></td>
                    </tr>
                    <tr>
                        <td>&gt; -1 s/d ≤ -10</td>
                        <td>Too Strong</td>
                        <td><span class="badge px-3 py-1" style="background-color: #ebe79a;">⚠️</span></td>
                    </tr>
                    <tr>
                        <td>&gt; -11 s/d ≤ -22</td>
                        <td>Good</td>
                        <td><span class="badge px-3 py-1" style="background-color: #b8eb9a;">✅</span></td>
                    </tr>
                    <tr>
                        <td>&gt; -24 s/d ≤ -32</td>
                        <td>Too Low</td>
                        <td><span class="badge px-3 py-1" style="background-color: #eb9ac4;">🛑</span></td>
                    </tr>
                    <tr>
                        <td>≤ -40</td>
                        <td>Bad</td>
                        <td><span class="badge px-3 py-1" style="background-color: #eb9a9a;">❌</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleStatusTable() {
            const box = document.getElementById("status-table-box");
            box.style.display = (box.style.display === "none") ? "block" : "none";
        }
    </script>

    {{-- Form untuk update name lab, author & description --}}
    <form id="lab-update-form" method="POST" action="{{ route('lab.update', $lab['id']) }}" style="display: none;">
        @csrf
        @method('PUT')
        <input type="hidden" name="name" id="lab-update-name">
        <input type="hidden" name="author" id="lab-update-author">
        <input type="hidden" name="description" id="lab-update-description">
    </form>

    {{-- Session untuk "Success" yang memunculkan swall --}}
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
                    title: '{{ session('success') }}',
                });
            });
        </script>
    @endif

    {{-- Modal untuk update name lab, author & description --}}
    <script>
        function showEditLabForm() {
            Swal.fire({
                title: '🛠 Edit Informasi Lab',
                html: `
        <div class="text-start mb-2"><label class="form-label fw-bold">Lab Name</label>
            <input id="lab-name" class="form-control" value="{{ $lab['name'] }}">
        </div>
        <div class="text-start mb-2"><label class="form-label fw-bold">Author</label>
            <input id="lab-author" class="form-control" value="{{ $lab['author'] }}">
        </div>
        <div class="text-start mb-2"><label class="form-label fw-bold">Description</label>
            <textarea id="lab-description" class="form-control" rows="3">{{ $lab['description'] }}</textarea>
        </div>
    `,
                width: 600,
                confirmButtonText: '💾 Save Changes',
                confirmButtonColor: '#10BC69',
                cancelButtonText: 'Batal',
                showCancelButton: true,
                focusConfirm: false,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                },
                customClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                didOpen: () => {
                    document.querySelector('.swal2-popup')?.classList.add('float-style');
                },
                willClose: () => {
                    document.querySelector('.swal2-popup')?.classList.remove('float-style');
                },
                preConfirm: () => {
                    const name = document.getElementById('lab-name').value.trim();
                    const author = document.getElementById('lab-author').value.trim();
                    const description = document.getElementById('lab-description').value.trim();

                    if (!name || !author) {
                        Swal.showValidationMessage('name dan Author tidak boleh kosong!');
                        return false;
                    }

                    return {
                        name,
                        author,
                        description
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('lab-update-name').value = result.value.name;
                    document.getElementById('lab-update-author').value = result.value.author;
                    document.getElementById('lab-update-description').value = result.value.description;
                    document.getElementById('lab-update-form').submit();
                }
            });
        }
    </script>
@endsection
{{-- Script untuk membuat dan mengedit canvas --}}
@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leader-line/1.0.7/leader-line.min.js"></script>
    <script>
        let nodes = [],
            lines = [],
            nodeId = 0,
            selectedNode = null,
            pendingSplitter = false;

        let selectedCableLoss = 0.2 / 1000;
        let selectedCableColor = 'black';
        let selectedCableName = 'Dropcore';
        let actions = [];
        let connections = [];


        function selectCable(lossPerKM, type, el) {
            selectedCableLoss = parseFloat(lossPerKM) / 1000;
            selectedCableColor = type === 'patchcord' ? 'yellow' : 'black';
            selectedCableName = type.charAt(0).toUpperCase() + type.slice(1);
            document.querySelectorAll('.cable-option').forEach(opt => opt.classList.remove('selected'));
            el.classList.add('selected');
        }

        function makeDraggable(el) {
            let isDragging = false,
                offsetX, offsetY;
            el.addEventListener('mousedown', function(e) {
                isDragging = true;
                offsetX = e.offsetX;
                offsetY = e.offsetY;
                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            function onMouseMove(e) {
                if (!isDragging) return;
                const canvas = document.getElementById('map-canvas');
                const canvasRect = canvas.getBoundingClientRect();

                // Hitung posisi baru
                let newLeft = e.clientX - canvasRect.left - offsetX;
                let newTop = e.clientY - canvasRect.top - offsetY;

                // AMANKAN: Batas kiri, atas, kanan, bawah
                const maxLeft = canvas.clientWidth - el.offsetWidth;
                const maxTop = canvas.clientHeight - el.offsetHeight;

                if (newLeft < 0) newLeft = 0;
                if (newTop < 0) newTop = 0;
                if (newLeft > maxLeft) newLeft = maxLeft;
                if (newTop > maxTop) newTop = maxTop;

                // Set posisi aman
                el.style.left = `${newLeft}px`;
                el.style.top = `${newTop}px`;
                el.dataset.left = el.style.left;
                el.dataset.top = el.style.top;

                // Update garis
                lines.forEach(link => {
                    if (link.from === el.id || link.to === el.id) {
                        link.line.position();
                    }
                });
            }

            function onMouseUp() {
                isDragging = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);

                // Tambahkan ini:
                LeaderLine.positionAll();
            }
        }

        function addNode(type) {
            if (type === 'Splitter') {
                pendingSplitter = true;
                new bootstrap.Modal(document.getElementById('splitterModal')).show();
                return;
            }

            const el = document.createElement("div");
            el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
            el.setAttribute("id", `node-${nodeId}`);

            const canvas = document.getElementById("map-canvas");
            const centerX = canvas.clientWidth / 2;
            const centerY = canvas.clientHeight / 2;

            el.style.left = `${centerX - 50}px`;
            el.style.top = `${centerY - 25}px`;

            let label = type;
            let loss = 0;
            let power = "";

            if (type.startsWith('Splitter')) {
                const splitRatio = type.split(' ')[1];
                const splitLoss = {
                    '1:2': 3.5,
                    '1:4': 7.2,
                    '1:8': 10.5,
                    '1:16': 13.5,
                    '1:32': 17.0,
                    '1:64': 20.5,
                };
                loss = splitLoss[splitRatio] || 0;
            }

            if (type === 'ODP') {
                const odpType = prompt("Masukkan jenis ODP (Mini/Besar)");
                label = `ODP ${odpType}`;
                el.dataset.odp = odpType;
                loss = odpType.toLowerCase() === 'besar' ? 0.5 : 0.2;
            }

            if (type === 'OLT') {
                power = document.getElementById("input-power")?.value || 0;
            }

            el.dataset.loss = loss;
            el.dataset.power = power;

            const powerDisplay = power ? `${parseFloat(power).toFixed(2)} dB` : '';
            el.innerHTML =
                `<strong>${label}</strong><div class="output-power" style="font-size: 12px; color: green;">${powerDisplay}</div>`;

            // Click to connect node
            el.addEventListener('click', function() {
                const clickedEl = this;

                if (!selectedNode) {
                    selectedNode = clickedEl;
                    clickedEl.classList.add('border-primary');
                } else if (selectedNode !== clickedEl) {
                    Swal.fire({
                        title: 'Hubungkan Node',
                        html: `
                    <div class="text-start mb-2">Panjang Kabel (meter)</div>
                    <input id="swal-length" type="number" class="swal2-input" value="${document.getElementById("cable-length")?.value || 50}">

                    <div class="text-start mb-2 mt-2">Jenis Kabel</div>
                    <select id="swal-cable" class="swal2-select">
                        <option value="dropcore" selected>Dropcore (0.2 dB/km)</option>
                        <option value="patchcord">Patchcord (0.3 dB/km)</option>
                    </select>
                `,
                        focusConfirm: false,
                        confirmButtonText: 'Hubungkan',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const length = parseFloat(document.getElementById('swal-length').value);
                            const type = document.getElementById('swal-cable').value;

                            if (!length || length <= 0) {
                                Swal.showValidationMessage('Panjang kabel tidak valid!');
                                return;
                            }

                            return {
                                length,
                                type
                            };
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            const {
                                length,
                                type
                            } = result.value;

                            if (type === 'dropcore') {
                                window.selectedCableLoss = 0.2 / 1000;
                                window.selectedCableColor = 'black';
                                window.selectedCableName = 'Dropcore';
                            } else if (type === 'patchcord') {
                                window.selectedCableLoss = 0.3 / 1000;
                                window.selectedCableColor = 'yellow';
                                window.selectedCableName = 'Patchcord';
                            }

                            connectNodeElements(selectedNode, clickedEl, length);
                            selectedNode.classList.remove('border-primary');
                            selectedNode = null;
                        }
                    });
                } else {
                    clickedEl.classList.remove('border-primary');
                    selectedNode = null;
                }
            });

            makeDraggable(el);
            document.getElementById("map-canvas").appendChild(el);
            nodeId++;

            actions.push({
                type: "add-node",
                node: el
            });
            isTopologyChanged = true;
        }


        function addLineContextMenu(lineObj) {
            const lineEl = lineObj.line;
            if (!lineEl?.svg) {
                console.warn('Line SVG belum siap saat addLineContextMenu dipanggil');
                return;
            }

            lineEl.svg.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                if (confirm('Hapus kabel ini?')) {
                    lineEl.remove();
                    lines = lines.filter(l => l !== lineObj);
                }
            });
        }

        function confirmSplitter() {
            const splitterType = document.getElementById('splitterTypeSelect').value;
            bootstrap.Modal.getInstance(document.getElementById('splitterModal')).hide();
            addNode(`Splitter ${splitterType}`);
        }

        function connectNodeElements(source, target, length) {
            const lossCable = length * window.selectedCableLoss;
            const lossTarget = parseFloat(target.dataset.loss || 0);
            const totalConnectors = parseInt(document.getElementById("connectors").value || 0);
            const totalSplicing = parseInt(document.getElementById("splicing").value || 0);
            const connectorLoss = totalConnectors * 0.2;
            const splicingLoss = totalSplicing * 0.1;
            const totalLoss = lossCable + lossTarget + connectorLoss + splicingLoss;

            const sourcePower = parseFloat(source.dataset.power || document.getElementById("input-power").value || 0);
            const powerRx = sourcePower - totalLoss;
            target.dataset.power = powerRx.toFixed(2);
            target.querySelector(".output-power").innerText = `${powerRx.toFixed(2)} dB`;

            const line = new LeaderLine(
                LeaderLine.pointAnchor(source, {
                    x: '50%',
                    y: '50%'
                }),
                LeaderLine.pointAnchor(target, {
                    x: '50%',
                    y: '50%'
                }), {
                    color: window.selectedCableColor,
                    size: 2,
                    path: 'straight',
                    startPlug: 'none',
                    endPlug: 'none',
                    dash: {
                        animation: true
                    },
                    middleLabel: LeaderLine.pathLabel(`-${lossCable.toFixed(2)} dB`, {
                        color: 'red',
                        fontSize: '12px'
                    }),
                    // startLabel: window.selectedCableName
                }
            );

            lines.push({
                from: source.id,
                to: target.id,
                cable: window.selectedCableName,
                loss: lossCable, // ⬅️ SIMPAN loss kabel di sini!
                length: length,
                line
            });

            // Update info panel
            document.getElementById("info-card").classList.remove("d-none");
            document.getElementById("total-loss").innerText = totalLoss.toFixed(2);
            document.getElementById("power-rx").innerText = powerRx.toFixed(2);
            document.getElementById("jalur-text").innerText =
                `${source.querySelector('strong').innerText} → ${target.querySelector('strong').innerText}`;

            addLineContextMenu({
                from: source.id,
                to: target.id,
                cable: window.selectedCableName,
                line
            });

            actions.push({
                type: 'add-connection',
                line: line,
                from: source.id,
                to: target.id
            });
            isTopologyChanged = true;
        }


        function undoAction() {
            if (actions.length === 0) return;

            const last = actions.pop();

            if (last.type === "add-connection") {
                const lineIndex = lines.findIndex(l => l.line === last.line);
                if (lineIndex !== -1) {
                    lines[lineIndex].line.remove();
                    lines.splice(lineIndex, 1);
                }
            }

            if (last.type === "add-node") {
                const node = last.node;
                lines = lines.filter(conn => {
                    if (conn.from === node.id || conn.to === node.id) {
                        conn.line.remove();
                        return false;
                    }
                    return true;
                });
                node.remove();
            }

            if (last.type === 'reset') {
                // Bersihkan sebelum kembalikan node/kabel
                document.getElementById("map-canvas").innerHTML = '';
                lines.forEach(link => link.line.remove());
                lines = [];

                last.nodes.forEach(addNodeFromDB);
                last.connections.forEach(drawConnectionFromDB);
                calculateAllLoss();
            }

            document.getElementById("info-card").classList.add("d-none");
            isTopologyChanged = true;
        }


        function resetMap() {
            actions.push({
                type: 'reset',
                nodes: Array.from(document.querySelectorAll("#map-canvas > div")).map(el => ({
                    id: el.id,
                    type: el.querySelector('strong')?.innerText || '',
                    loss: parseFloat(el.dataset.loss || 0),
                    power: parseFloat(el.dataset.power || 0),
                    top: el.style.top,
                    left: el.style.left
                })),
                connections: lines.map(link => ({
                    from: link.from,
                    to: link.to,
                    cable: link.cable,
                    loss: link.loss,
                    length: link.length
                }))
            });
            // ⛔️ Ini yang kurang sebelumnya:
            lines.forEach(link => link.line.remove());
            lines = [];

            // Hapus semua node dari canvas
            const canvas = document.getElementById("map-canvas");
            canvas.innerHTML = '';

            // Reset variabel
            nodeId = 0;
            selectedNode = null;
            // actions = [];
            connections = [];

            // Sembunyikan info panel
            document.getElementById("info-card").classList.add("d-none");
        }

        function resetTopology() {
            Swal.fire({
                title: 'Are you sure you want to reset?',
                text: 'All nodes and connections will be deleted!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, reset!',
                confirmButtonColor: 'red',
                cancelButtonText: 'Cancel',
            }).then((result) => {
                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true,
                });

                if (result.isConfirmed) {
                    actions.push({
                        type: 'reset',
                        nodes: Array.from(document.querySelectorAll("#map-canvas > div")).map(el => ({
                            id: el.id,
                            type: el.querySelector('strong')?.innerText || '',
                            loss: parseFloat(el.dataset.loss || 0),
                            power: parseFloat(el.dataset.power || 0),
                            top: el.style.top,
                            left: el.style.left
                        })),
                        connections: lines.map(link => ({
                            from: link.from,
                            to: link.to,
                            cable: link.cable,
                            loss: link.loss,
                            length: link.length
                        }))
                    });
                    // ⛔️ Ini yang kurang sebelumnya:
                    lines.forEach(link => link.line.remove());
                    lines = [];

                    // Hapus semua node dari canvas
                    const canvas = document.getElementById("map-canvas");
                    canvas.innerHTML = '';

                    // Reset variabel
                    nodeId = 0;
                    selectedNode = null;
                    // actions = [];
                    connections = [];

                    // Sembunyikan info panel
                    document.getElementById("info-card").classList.add("d-none");
                    isTopologyChanged = true;
                    Toast.fire({
                        icon: 'success',
                        title: 'Topologi berhasil direset!'
                    });
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Toast.fire({
                        icon: 'info',
                        title: 'Reset dibatalkan'
                    });
                }
            });
        }

        const labId = "{{ $lab['id'] }}";

        function updateLossInfo(loss, rx, jalur) {
            document.getElementById('total-loss').textContent = loss.toFixed(2);
            document.getElementById('power-rx').textContent = rx.toFixed(2);
            document.getElementById('jalur-text').textContent = jalur.join(" → ");
            document.getElementById('info-card').classList.remove("d-none");
            document.getElementById('info-panel').classList.remove("d-none");

            const status = document.getElementById('loss-status');
            if (loss <= 28) {
                status.innerHTML = `<span class="badge bg-success">✅ Aman digunakan</span>`;
            } else {
                status.innerHTML = `<span class="badge bg-danger">⚠️ Loss terlalu tinggi!</span>`;
            }
        }

        function loadTopology(id) {
            fetch(`/topologi/load/${id}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.nodes || !data.connections) return;

                    // Clear canvas dulu
                    resetMap();

                    // Tambahkan node dari data DB
                    data.nodes.forEach(node => {
                        addNodeFromDB(node); // fungsi ini perlu kamu buat
                    });

                    // Tambahkan koneksi dari DB
                    data.connections.forEach(conn => {
                        drawConnectionFromDB(conn); // fungsi ini juga perlu kamu sesuaikan
                    });

                    // Update Power dan total loss
                    document.getElementById('input-power').value = data.power || 0;
                    calculateAllLoss(); // fungsi ini sesuai punyamu
                });
        }

        function addNodeFromDB(node) {
            const el = document.createElement("div");
            el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
            el.setAttribute("id", node.id);
            el.style.top = node.top;
            el.style.left = node.left;

            el.innerHTML =
                `<strong>${node.type}</strong><div class="output-power" style="font-size: 12px; color: green;">${node.power.toFixed(2)} dB</div>`;
            el.dataset.loss = node.loss;
            el.dataset.power = node.power;

            el.addEventListener('click', function() {
                const clickedEl = this;

                if (!selectedNode) {
                    selectedNode = clickedEl;
                    clickedEl.classList.add('border-primary');
                } else if (selectedNode !== clickedEl) {
                    Swal.fire({
                        title: 'Hubungkan Node',
                        html: `
                    <div class="text-start mb-2">Panjang Kabel (meter)</div>
                    <input id="swal-length" type="number" class="swal2-input" value="50">

                    <div class="text-start mb-2 mt-2">Jenis Kabel</div>
                    <select id="swal-cable" class="swal2-select">
                        <option value="dropcore" selected>Dropcore (0.2 dB/km)</option>
                        <option value="patchcord">Patchcord (0.3 dB/km)</option>
                    </select>
                `,
                        focusConfirm: false,
                        confirmButtonText: 'Hubungkan',
                        showCancelButton: true,
                        cancelButtonText: 'Batal',
                        preConfirm: () => {
                            const length = parseFloat(document.getElementById('swal-length').value);
                            const type = document.getElementById('swal-cable').value;

                            if (!length || length <= 0) {
                                Swal.showValidationMessage('Panjang kabel tidak valid!');
                                return;
                            }

                            return {
                                length,
                                type
                            };
                        }
                    }).then(result => {
                        if (result.isConfirmed) {
                            const {
                                length,
                                type
                            } = result.value;

                            if (type === 'dropcore') {
                                selectedCableLoss = 0.2 / 1000;
                                selectedCableColor = 'black';
                                selectedCableName = 'Dropcore';
                            } else if (type === 'patchcord') {
                                selectedCableLoss = 0.3 / 1000;
                                selectedCableColor = 'yellow';
                                selectedCableName = 'Patchcord';
                            }

                            connectNodeElements(selectedNode, clickedEl, length);
                            selectedNode.classList.remove('border-primary');
                            selectedNode = null;
                        }
                    });
                } else {
                    clickedEl.classList.remove('border-primary');
                    selectedNode = null;
                }
            });

            makeDraggable(el);
            document.getElementById("map-canvas").appendChild(el);
            isTopologyChanged = true;

        }

        function drawConnectionFromDB(conn) {
            const fromEl = document.getElementById(conn.from);
            const toEl = document.getElementById(conn.to);

            if (!fromEl || !toEl) return;

            const line = new LeaderLine(
                LeaderLine.pointAnchor(fromEl, {
                    x: '50%',
                    y: '50%'
                }),
                LeaderLine.pointAnchor(toEl, {
                    x: '50%',
                    y: '50%'
                }), {
                    color: conn.cable === 'Dropcore' ? 'black' : 'yellow',
                    size: 2,
                    path: 'straight',
                    startPlug: 'none',
                    endPlug: 'none',
                    dash: {
                        animation: true
                    },
                    middleLabel: LeaderLine.pathLabel(`-${(conn.loss || 0).toFixed(2)} dB`, {
                        color: 'red',
                        fontSize: '12px'
                    })
                    // startLabel: conn.cable
                }
            );

            lines.push({
                from: fromEl.id,
                to: toEl.id,
                cable: conn.cable,
                loss: conn.loss,
                length: conn.length,
                line
            });

            addLineContextMenu({
                from: conn.from,
                to: conn.to,
                cable: conn.cable,
                line
            });
            isTopologyChanged = true;
        }

        function calculateAllLoss() {
            lines.forEach(conn => {
                const from = document.getElementById(conn.from);
                const to = document.getElementById(conn.to);

                const loss = parseFloat(conn.loss || 0);
                const sourcePower = parseFloat(from.dataset.power || document.getElementById("input-power").value ||
                    0);
                const powerRx = sourcePower - loss;

                to.dataset.power = powerRx.toFixed(2);
                to.querySelector(".output-power").innerText = `${powerRx.toFixed(2)} dB`;
            });
        }

        async function saveTopology(topology, labId) {
            const {
                nodes,
                connections
            } = topology;

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true,
            });

            if (nodes.length === 0 || connections.length === 0) {
                Toast.fire({
                    icon: 'warning',
                    title: 'There are no nodes or connections to save!'
                });
                return;
            }


            const power = parseFloat(document.getElementById('input-power').value || 0);

            try {
                const response = await fetch(`/topologi/save/${labId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content')
                    },
                    body: JSON.stringify({
                        name: "{{ $lab['name'] ?? '' }}",
                        author: "{{ $lab['author'] ?? '' }}",
                        description: "{{ $lab['description'] ?? '' }}",
                        nodes,
                        connections,
                        power,
                    })
                });

                const data = await response.json();
            } catch (error) {
                console.error(error);
                Swal.fire('Gagal', 'Happened an error.', 'error');
            }
            isTopologyChanged = false;
        }

        function gatherAndSaveTopology() {
            const topology = {
                nodes: [],
                connections: [],
                power: parseFloat(document.getElementById('input-power')?.value || 0),
                name: "{{ $lab['name'] ?? '' }}",
                author: "{{ $lab['author'] ?? '' }}",
                description: "{{ $lab['description'] ?? '' }}"
            };

            const nodeElements = Array.from(document.querySelectorAll("#map-canvas > div"));

            nodeElements.forEach(node => {
                topology.nodes.push({
                    id: node.id,
                    type: node.querySelector('strong')?.innerText || '',
                    loss: parseFloat(node.dataset.loss || 0),
                    power: parseFloat(node.dataset.power || 0),
                    top: node.style.top,
                    left: node.style.left
                });
            });

            lines.forEach(conn => {
                topology.connections.push({
                    from: conn.from,
                    to: conn.to,
                    cable: conn.cable,
                    color: conn.line?.options?.color || 'black',
                    loss: conn.loss,
                    length: conn.length
                });
            });

            const labId = document.getElementById('map-canvas').dataset.labId;

            // ⛔ Skip semuanya kalau kosong
            if (topology.nodes.length === 0 || topology.connections.length === 0) {
                saveTopology(topology, labId); // tetap panggil ini karena dia yang munculin warning Swal
                return;
            }

            // ✅ Simpan ke database
            saveTopology(topology, labId);

            // ✅ Simpan ke file local.json di server
            fetch(`/lab/${labId}/update-json`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(topology)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'The topology is saved into a lab file!',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to save lab file',
                            text: 'Please try again in a moment.'
                        });
                    }
                })
        }


        function exportTopology() {
            const topology = {
                nodes: [],
                connections: [],
                power: parseFloat(document.getElementById('input-power').value || 0),
                name: "{{ $lab['name'] }}",
                author: "{{ $lab['author'] }}",
                description: "{{ $lab['description'] }}"
            };

            document.querySelectorAll("#map-canvas > div").forEach(node => {
                topology.nodes.push({
                    id: node.id,
                    type: node.querySelector('strong')?.innerText || '',
                    loss: parseFloat(node.dataset.loss || 0),
                    power: parseFloat(node.dataset.power || 0),
                    top: node.style.top,
                    left: node.style.left
                });
            });

            lines.forEach(conn => {
                topology.connections.push({
                    from: conn.from,
                    to: conn.to,
                    cable: conn.cable,
                    color: conn.line?.options?.color || 'black',
                    loss: conn.loss,
                    length: conn.length
                });
            });

            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(topology, null, 2));
            const dlAnchor = document.createElement('a');
            dlAnchor.setAttribute("href", dataStr);
            dlAnchor.setAttribute("download", `topologi-${topology.name.replace(/\s+/g, '_')}.json`);
            document.body.appendChild(dlAnchor);
            dlAnchor.click();
            dlAnchor.remove();
            isTopologyChanged = false;
        }

        function importTopology(file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = JSON.parse(e.target.result);
                    resetMap();

                    if (data.nodes) {
                        data.nodes.forEach(addNodeFromDB);
                    }

                    if (data.connections) {
                        data.connections.forEach(drawConnectionFromDB);
                    }

                    if (data.power !== undefined) {
                        document.getElementById('input-power').value = data.power;
                    }

                    calculateAllLoss();

                    Swal.fire('Berhasil', 'Topologi berhasil dimuat dari file!', 'success');
                } catch (err) {
                    console.error(err);
                    Swal.fire('Gagal', 'Format file tidak valid!', 'error');
                }
            };
            reader.readAsText(file);
        }


        window.onload = function() {
            const labId = "{{ $lab['id'] }}";
            loadTopology(labId);
        };

        window.onbeforeunload = function() {
            if (isTopologyChanged) {
                return "Perubahan Anda belum disimpan. Yakin ingin keluar?";
            }
        };

        document.getElementById('map-canvas').addEventListener('scroll', LeaderLine.positionAll);
        // Biar kabel gak geser saat scroll atau resize
        window.addEventListener('scroll', LeaderLine.positionAll);
        window.addEventListener('resize', LeaderLine.positionAll);
    </script>
@endpush
