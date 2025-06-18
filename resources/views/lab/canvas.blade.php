@extends('fe.lab')
@section('title', 'FTTH Topologi - ' . $lab['nama'])
@section('content')
    <!-- Sidebar -->
    <div id="sidebar">

        <div class="inner">
            {{-- Nama Lab --}}
            <h2 class="fw-bold mb-3" style="color: #fff;">
                {{ $lab['name'] }}
            </h2>

            {{-- Nama Author --}}
            <p class="btn w-5 text-white fw-bold" style="background: linear-gradient(87deg, #627594 0, #8898aa 100%); border-radius: 50px">
                <i class="bi bi-person-circle me-1"></i>
                {{ $lab['author'] }}
            </p>

            {{-- Deskripsi --}}
            <p style="font-size: 1rem; line-height: 1.6;">
                {{ $lab['description'] ?? '🧪 No description provided for this lab yet.' }}
            </p>
            <hr>
            <h5 class="text-white">Tambah Perangkat</h5>
            <label class="form-label text-white">Power OLT</label>
            <input type="number" id="input-power" class="form-control form-control-sm mb-2" value="7">

            <button class="btn btn-sm btn-light w-100 mb-1" onclick="addNode('OLT')">+ OLT</button>
            <button class="btn btn-sm btn-light w-100 mb-1" onclick="addNode('Splitter')">+ Splitter</button>
            <button class="btn btn-sm btn-light w-100 mb-1" onclick="addNode('ODP')">+ ODP</button>
            <button class="btn btn-sm btn-light w-100 mb-1" onclick="addNode('Client')">+ Client</button>

            <hr class="text-white">

            <label class="form-label text-white">Jenis Kabel</label>
            <div class="d-flex gap-2 mb-2">
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

            <label class="form-label text-white">Panjang Kabel (m)</label>
            <input type="number" id="cable-length" class="form-control form-control-sm mb-2" value="50">

            <label class="form-label text-white">Connector</label>
            <input type="number" id="connectors" class="form-control form-control-sm mb-2" value="2">

            <label class="form-label text-white">Splicing</label>
            <input type="number" id="splicing" class="form-control form-control-sm mb-2" value="1">

            <div class="">
                <button class="btn btn-sm btn-success w-50 mb-1" onclick="saveTopology()">💾 Simpan Topologi</button>
                <button class="btn btn-sm btn-warning w-50 mb-1" onclick="resetMap()">Reset</button>
                <button class="btn btn-sm btn-danger w-50 mb-1" onclick="undoAction()">↩ Undo</button>
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
    <div id="map-canvas"></div>

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
            el.style.top = "100px";
            el.style.left = "100px";
            el.setAttribute("id", `node-${nodeId}`);

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

            el.addEventListener('click', () => {
                if (!selectedNode) {
                    selectedNode = el;
                    el.classList.add('border-primary');
                } else if (selectedNode !== el) {
                    const length = parseFloat(prompt("Masukkan panjang kabel (meter):", document.getElementById(
                        "cable-length").value));
                    if (!isNaN(length)) {
                        connectNodeElements(selectedNode, el, length);
                    }
                    selectedNode.classList.remove('border-primary');
                    selectedNode = null;
                } else {
                    selectedNode.classList.remove('border-primary');
                    selectedNode = null;
                }
                // Klik kanan untuk hapus
                el.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    if (confirm('Hapus node ini beserta kabel yang terhubung?')) {
                        deleteNode(el);
                    }
                });
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
            const lossCable = length * selectedCableLoss;
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
                    color: selectedCableColor,
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
                    startLabel: selectedCableName
                }
            );

            lines.push({
                from: source.id,
                to: target.id,
                cable: selectedCableName,
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
                cable: selectedCableName,
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

        async function saveTopology(topology, customUrl = null) {
            const {
                nodes,
                connections
            } = topology;
            const power = parseFloat(document.getElementById('input-power').value || 0);

            const payload = {
                nama: "{{ $lab['nama'] }}",
                deskripsi: "{{ $lab['deskripsi'] }}",
                nodes,
                connections,
                power
            };

            try {
                fetch(`/topologi/save/1`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        nama: "Simulasi FTTH A",
                        deskripsi: "Simulasi dari OLT ke ONT rumah A",
                        power: 15,
                        nodes: [{
                                id: 'OLT1',
                                type: 'OLT',
                                name: 'OLT Pusat',
                                x: 100,
                                y: 200
                            },
                            {
                                id: 'ONT1',
                                type: 'ONT',
                                name: 'Rumah A',
                                x: 300,
                                y: 350
                            }
                        ],
                        connections: [{
                            from: 'OLT1',
                            to: 'ONT1',
                            length: 120,
                            fiberType: 'G652',
                            loss: 2.4
                        }]
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
                    updateLossCalculation(); // fungsi ini sesuai punyamu
                });
        }


        function saveTopology() {
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
                    cable: conn.cable
                });
            });

            if (topology.nodes.length === 0 || topology.connections.length === 0) {
                Swal.fire('Error', 'Tidak ada node atau koneksi yang bisa disimpan!', 'warning');
                return;
            }

            submitTopology(topology);
        }


        async function submitTopology(topology) {
            const {
                nodes,
                connections
            } = topology;
            const power = parseFloat(document.getElementById('input-power').value || 0);
            const labId = "{{ $lab['id'] }}"; // ambil dari Blade

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
                        power
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

        window.onload = function() {
            loadTopology();
        };

        window.onbeforeunload = function() {
            if (isTopologyChanged) {
                return "Perubahan Anda belum disimpan. Yakin ingin keluar?";
            }
        };
    </script>
@endpush
