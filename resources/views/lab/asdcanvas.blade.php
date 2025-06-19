@extends('fe.lab')

@section('title', 'Lab - ' . $lab['nama'])

@section('content')
    <!-- sidebar -->
    <div id="sidebar" class="position-fixed sidebar-hidden">
        <div class="inner p-3">
            <!-- konten sidebar -->
            <h5>Lab: {{ $lab['name'] }}</h5>
            <p><small>{{ $lab['description'] }}</small></p>
            <hr>
            <h5 class="text-white">Tambah Perangkat</h5>
            <label class="form-label text-white">Power OLT</label>
            <input type="number" id="input-power" class="form-control form-control-sm mb-2" value="7">

            <div class="d-grid gap-2 mb-2">
                <button class="btn btn-sm btn-outline-primary text-dark" onclick="addNode('OLT')">
                    <i class="fas fa-broadcast-tower me-1"></i> OLT
                </button>
                <button class="btn btn-sm btn-outline-primary text-dark" onclick="addNode('Splitter')">
                    <i class="fas fa-code-branch me-1"></i> Splitter
                </button>
                <button class="d-none btn btn-sm btn-outline-primary text-dark" onclick="addNode('ODP')">
                    <i class="fas fa-network-wired me-1"></i> ODP
                </button>
                <button class="btn btn-sm btn-outline-primary text-dark" onclick="addNode('Client')">
                    <i class="fas fa-user me-1"></i> Client
                </button>
            </div>


            <hr class="text-white">

            <!-- <label class="form-label text-white">Jenis Kabel</label> -->
            <div class="d-none gap-2 mb-2">
                <div onclick="selectCable('0.2', 'dropcore', this)"
                    class="cable-option p-2 bg-light text-dark rounded text-center flex-fill cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: black; margin: auto;"></div>
                    <small>Dropcore</small>
                </div>
                <div onclick="selectCable('0.3', 'patchcord', this)"
                    class="cable-option p-2 bg-warning text-dark rounded text-center flex-fill cursor-pointer">
                    <div style="width: 30px; height: 5px; background-color: yellow; margin: auto;"></div>
                    <small>Patchcord</small>
                </div>
            </div>

            <div class="d-none">
                <label class="form-label text-white">Connector</label>
                <input type="number" id="connectors" class="form-control form-control-sm mb-2" value="2">
            </div>

            <div class="d-none">
                <label class="form-label text-white">Panjang Kabel (m)</label>
                <input type="number" id="connectors" class="form-control form-control-sm mb-2" value="2">
            </div>


            <label class="form-label text-white">Splicing</label>
            <input type="number" id="splicing" class="form-control form-control-sm mb-2" value="1">

            <div class="">
                <button class="btn btn-sm btn-success w-100 mb-1" onclick="gatherAndSaveTopology()">💾 Simpan</button>
                <button class="btn btn-sm btn-warning w-100 mb-1" onclick="resetMap()">Reset</button>
                <button class="btn btn-sm btn-danger w-100 mb-1" onclick="undoAction()">↩ Undo</button>

            </div>
            <hr>
            <label class="form-label text-white">Manajemen File</label>
            <div class="d-grid gap-2 mb-2">
                <button class="btn btn-sm btn-info" onclick="exportTopology()">⬇️ Export (.json)</button>
                <input type="file" id="import-file" accept=".json" class="form-control form-control-sm"
                    onchange="importTopology(this.files[0])">
            </div>
            <hr>
            <a href="/lab" class="btn btn-sm btn-secondary w-100">🚪 Keluar</a>
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
    <div id="map-canvas" data-lab-id="{{ $lab['id'] }}"></div>

    <!-- Informasi Loss Panel -->
    <div id="info-card" class="card shadow-sm border border-info d-none">
        <div class="card-body">
            <h5 class="card-title text-info">📊 Informasi Loss</h5>
            <hr>
            <p>Total Loss: <strong id="total-loss" class="text-danger">-</strong> dB</p>
            <p>Output Power: <strong id="power-rx" class="text-primary">-</strong> dBm</p>
            <p>Jalur: <span id="jalur-text" class="text-muted">-</span></p>
            <div id="loss-status" class="mt-3"></div>
        </div>
    </div>

    <!-- Tabel Status Power Loss -->
    <div id="status-table-box" class="bg-white border rounded shadow-sm p-2 d-none d-md-block">
        <h6 class="text-center mb-2">📋 Tabel Status Power Loss</h6>
        <table class="table table-bordered table-sm mb-0 text-center">
            <thead class="table-dark">
                <tr>
                    <th style="font-size: 12px;">Power Loss</th>
                    <th style="font-size: 12px;">Keterangan</th>
                    <th style="font-size: 12px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>&gt; 0</td>
                    <td>Invalid</td>
                    <td><span class="badge bg-secondary">⚪️</span></td>
                </tr>
                <tr>
                    <td>&gt; -1 s/d ≤ -10</td>
                    <td>Too Strong</td>
                    <td><span class="badge bg-warning">⚡</span></td>
                </tr>
                <tr>
                    <td>&gt; -11 s/d ≤ -22</td>
                    <td>Good</td>
                    <td><span class="badge bg-success">✅</span></td>
                </tr>
                <tr>
                    <td>&gt; -24 s/d ≤ -32</td>
                    <td>Too Low</td>
                    <td><span class="badge bg-warning">⚠️</span></td>
                </tr>
                <tr>
                    <td>≤ -40</td>
                    <td>Bad</td>
                    <td><span class="badge bg-danger">❌</span></td>
                </tr>
            </tbody>
        </table>
    </div>

@endsection

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
                el.style.left = (e.clientX - canvasRect.left - offsetX) + 'px';
                el.style.top = (e.clientY - canvasRect.top - offsetY) + 'px';
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
            const canvasRect = canvas.getBoundingClientRect();
            const centerX = canvas.clientWidth / 2;
            const centerY = canvas.clientHeight / 2;

            el.style.left = `${centerX - 50}px`; // sesuaikan offset jika ukuran node berbeda
            el.style.top = `${centerY - 25}px`;

            let label = type;
            let loss = 0;

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

            el.innerHTML =
                `<strong>${label}</strong><div class="output-power" style="font-size: 12px; color: green;"></div>`;
            el.dataset.loss = loss;
            el.dataset.power = "";

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

                            // ⬇️ INI YANG KRUSIAL: pakai clickedEl sebagai node tujuan
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
                    startLabel: window.selectedCableName
                }
            );

            lines.push({
                from: source.id,
                to: target.id,
                cable: window.selectedCableName,
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
                // Hapus kabel yang terhubung ke node
                lines = lines.filter(conn => {
                    if (conn.from === node.id || conn.to === node.id) {
                        conn.line.remove();
                        return false;
                    }
                    return true;
                });
                node.remove();
            }

            // Reset tampilan info
            document.getElementById("info-card").classList.add("d-none");
        }


        function resetMap() {
            // Hapus semua garis
            lines.forEach(link => link.line.remove());
            lines = [];

            // Hapus semua node
            const canvas = document.getElementById("map-canvas");
            canvas.innerHTML = '';

            // Reset node ID
            nodeId = 0;
            selectedNode = null;

            // Sembunyikan info
            document.getElementById("info-card").classList.add("d-none");
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
                    middleLabel: LeaderLine.pathLabel(`${conn.cable}`, {
                        color: 'red',
                        fontSize: '12px'
                    }),
                    startLabel: conn.cable
                }
            );

            lines.push({
                from: source.id,
                to: target.id,
                cable: window.selectedCableName,
                loss: lossCable, // ⬅️ simpan loss
                panjang: length, // ⬅️ simpan panjang
                line
            });

            addLineContextMenu({
                from: conn.from,
                to: conn.to,
                cable: conn.cable,
                line
            });
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

            if (nodes.length === 0 || connections.length === 0) {
                Swal.fire('Error', 'Tidak ada node atau koneksi yang bisa disimpan!', 'warning');
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
                        nodes,
                        connections,
                        power,
                        nama: "{{ $lab['nama'] ?? '' }}",
                        deskripsi: "{{ $lab['deskripsi'] ?? '' }}"
                    })
                });

                const data = await response.json();

                if (data.success) {
                    Swal.fire('Berhasil', data.message || 'Topologi berhasil disimpan!', 'success');
                } else {
                    Swal.fire('Gagal', (data.errors || ['Gagal menyimpan topologi']).join('<br>'), 'error');
                }
            } catch (error) {
                console.error(error);
                Swal.fire('Gagal', 'Terjadi kesalahan saat menyimpan.', 'error');
            }
        }

        function gatherAndSaveTopology() {
            const topology = {
                nodes: [],
                connections: []
            };

            const nodeElements = Array.from(document.querySelectorAll("#map-canvas > div"));

            nodeElements.forEach(node => {
                topology.nodes.push({
                    id: node.id,
                    type: node.querySelector('strong')?.innerText || '',
                    loss: parseFloat(node.dataset.loss || 0),
                    power: parseFloat(node.dataset.power || 0),
                    top: node.style.top,
                    left: node.style.left,
                });
            });

            lines.forEach(conn => {
                topology.connections.push({
                    from: conn.from,
                    to: conn.to,
                    cable: conn.cable,
                    loss: conn.loss, // ⬅️ simpan
                    panjang: conn.panjang // ⬅️ simpan
                });
            });

            const labId = document.getElementById('map-canvas').dataset.labId;
            saveTopology(topology, labId);
        }

        function exportTopology() {
            const topology = {
                nodes: [],
                connections: [],
                power: parseFloat(document.getElementById('input-power').value || 0),
                nama: "{{ $lab['nama'] }}",
                deskripsi: "{{ $lab['deskripsi'] }}"
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
                    panjang: conn.panjang
                });
            });

            const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(topology, null, 2));
            const dlAnchor = document.createElement('a');
            dlAnchor.setAttribute("href", dataStr);
            dlAnchor.setAttribute("download", `topologi-${topology.nama.replace(/\s+/g, '_')}.json`);
            document.body.appendChild(dlAnchor);
            dlAnchor.click();
            dlAnchor.remove();
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
    </script>
@endpush
