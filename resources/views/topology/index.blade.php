@extends('fe.master')
@section('content')
    <div class="row">
        <div class="col-md-3">
            <h1 class="mb-4 fw-bold">Splitter Loss Calculator</h1>
            <div class="mb-2">
                <label for="inputLoss" class="form-label">Power OLT (dB)</label>
                <div class="input-group">
                    <button class="btn btn-outline-secondary dropdown-toggle fw-bold" type="button" data-bs-toggle="dropdown"
                        aria-expanded="false" id="plusMinusBtn">
                        +/-
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a class="dropdown-item" onclick="setSign('+')">+</a>
                        </li>
                        <li>
                            <a class="dropdown-item" onclick="setSign('-')">-</a>
                        </li>
                    </ul>
                    <input type="number" class="form-control" id="inputLoss" placeholder="7.00" step="any" />
                </div>
                <div class="form-text">
                    Isikan angka input loss (dB) terlebih dahulu, kemudian pilih tanda <span
                        class="text-danger fw-bold">+</span> atau <span class="text-danger fw-bold">-</span>
                </div>
            </div>
            <button class="btn btn-sm mb-2 w-100 text-black fw-bold" style="background-color: #10BC69;"
                onclick="addNode('OLT')">+
                OLT</button>
            <button class="btn btn-sm mb-2 w-100 text-black fw-bold" style="background-color: #10BC69;"
                onclick="addNode('Splitter')">+ Splitter</button>
            <button class="btn btn-sm mb-2 w-100 text-black fw-bold" style="background-color: #10BC69;"
                onclick="addNode('ODP')">+
                ODP</button>
            <button class="btn btn-sm mb-2 w-100 text-black fw-bold" style="background-color: #10BC69;"
                onclick="addNode('Client')">+ Client</button>
            <hr>
            <div class="mb-2">
                <label>Jenis Kabel</label>
                <select id="cable-type" class="form-control form-control-sm">
                    <option value="0.2">Dropcore (0.2 dB/km)</option>
                    <option value="0.35">Patchcord (0.35 dB/km)</option>
                </select>
            </div>
            <div class="mb-2">
                <label>Panjang Kabel (m)</label>
                <input type="number" id="cable-length" class="form-control form-control-sm" value="50">
            </div>
            <div class="mb-2">
                <label>Total Connector</label>
                <input type="number" id="connectors" class="form-control form-control-sm" value="2">
            </div>
            <div class="mb-2">
                <label>Total Splicing (0,1dB)</label>
                <input type="number" id="splicing" class="form-control form-control-sm" value="1">
            </div>
            <button class="btn btn-sm btn-secondary mb-2 w-100" onclick="connectNodes()">Sambungkan Node</button>
            <button class="btn btn-sm btn-danger mb-2 w-100" onclick="undoAction()">↩ Undo</button>
            <button class="btn btn-sm btn-warning mb-2 w-100" onclick="resetMap()">Reset</button>
            <button class="btn btn-sm w-100 text-black fw-bold" style="background-color: #10BC69;"
                onclick="saveTopology()">Hitung Loss</button>
        </div>

        <div class="col-md-6 position-relative" id="map-canvas"
            style="min-height: 500px; background-color: #f9f9f9; border: 1px solid #ccc;"></div>

        {{-- <div class="col-md-3">
        <h5>Informasi Loss</h5>
        <div id="info-card" class="card d-none">
            <div class="card-body">
                <h6 class="card-title">Total Loss</h6>
                <p class="card-text"><strong id="total-loss">-</strong> dB</p>
                <p class="card-text">"Output" <strong id="power-rx">-</strong> dBm</p>
                <p class="card-text">Jalur: <span id="jalur-text">-</span></p>
            </div>
        </div>
            </div> --}}
        <div class="col-md-3">
            <div class="green-box">
                <h5 class="text-black">Informasi Loss</h5>
                <div id="info-card" class="card d-none">
                    <div class="card-body">
                        <h6 class="card-title">Total Loss</h6>
                        <p class="card-text"><strong id="total-loss">-</strong> dB</p>
                        <p class="card-text">Power Terima: <strong id="power-rx">-</strong> dBm</p>
                        <p class="card-text">Jalur: <span id="jalur-text">-</span></p>
                    </div>
                </div>
                <div class="mt-4">
                    <h5 class="mb-3 text-black">Splitter Information</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-dark text-center align-middle rounded overflow-hidden">
                            <thead class="table-success text-dark">
                                <tr>
                                    <th>Splitter</th>
                                    <th>Redaman (dB)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1:2</td>
                                    <td>3.25</td>
                                </tr>
                                <tr>
                                    <td>1:4</td>
                                    <td>7.00</td>
                                </tr>
                                <tr>
                                    <td>1:8</td>
                                    <td>10.00</td>
                                </tr>
                                <tr>
                                    <td>1:16</td>
                                    <td>13.50</td>
                                </tr>
                                <tr>
                                    <td>1:32</td>
                                    <td>17.00</td>
                                </tr>
                                <tr>
                                    <td>1:64</td>
                                    <td>20.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leader-line/1.0.7/leader-line.min.js"></script>
    <script>
        let nodes = [],
            connections = [],
            lines = [],
            nodeId = 0,
            selectedNode = null,
            historyStack = [];

        const cableLossPerMeter = 0.003; // default redaman kabel per meter

        function makeDraggable(el) {
            let offsetX = 0,
                offsetY = 0,
                isDragging = false;
            el.addEventListener('mousedown', function(e) {
                isDragging = true;
                offsetX = e.clientX - el.getBoundingClientRect().left;
                offsetY = e.clientY - el.getBoundingClientRect().top;

                document.addEventListener('mousemove', onMouseMove);
                document.addEventListener('mouseup', onMouseUp);
            });

            function onMouseMove(e) {
                if (!isDragging) return;
                const map = document.getElementById("map-canvas");
                const rect = map.getBoundingClientRect();
                let x = e.clientX - rect.left - offsetX;
                let y = e.clientY - rect.top - offsetY;

                x = Math.max(0, Math.min(x, map.offsetWidth - el.offsetWidth));
                y = Math.max(0, Math.min(y, map.offsetHeight - el.offsetHeight));

                el.style.left = `${x}px`;
                el.style.top = `${y}px`;

                lines.forEach(line => line.position && line.position());
            }

            function onMouseUp() {
                isDragging = false;
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }
        }

        function addNode(type) {
            const el = document.createElement("div");
            el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
            el.style.top = "100px";
            el.style.left = "100px";
            el.setAttribute("id", `node-${nodeId}`);
            el.innerHTML =
                `<strong>${type}</strong><div class="output-power" style="font-size: 12px; color: green;"></div>`;

            let label = type;
            let loss = 0;
            if (type === 'Splitter') {
                const splitterType = prompt("Masukkan tipe Splitter (contoh: 1:2, 1:4, 1:8, 1:16, 1:32, 1:64)");
                label = `Splitter ${splitterType}`;
                el.dataset.splitter = splitterType;
                const splitterLosses = {
                    '1:2': 3.5,
                    '1:4': 7.2,
                    '1:8': 10.5,
                    '1:16': 13.8,
                    '1:32': 17.1,
                    '1:64': 19.6
                };
                loss = splitterLosses[splitterType] || 10.5;
            } else if (type === 'ODP') {
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
                    const length = parseFloat(prompt("Masukkan panjang kabel (meter):"));
                    if (!isNaN(length)) {
                        connectNodeElements(selectedNode, el, length);
                    }
                    selectedNode.classList.remove('border-primary');
                    selectedNode = null;
                } else {
                    selectedNode.classList.remove('border-primary');
                    selectedNode = null;
                }
            });

            makeDraggable(el);
            document.getElementById("map-canvas").appendChild(el);
            nodes.push({
                id: nodeId,
                type,
                el,
                label,
                loss,
                power: null
            });
            nodeId++;
        }

        function connectNodeElements(source, target, length) {
            const lossCable = length * cableLossPerMeter;
            const lossTarget = parseFloat(target.dataset.loss || 0);
            const sourcePower = parseFloat(source.dataset.power || document.getElementById("input-power").value || 0);
            const powerRx = sourcePower - lossCable - lossTarget;
            target.dataset.power = powerRx.toFixed(2);
            target.querySelector(".output-power").innerText = `${powerRx.toFixed(2)} dB`;

            const line = new LeaderLine(source, target, {
                color: 'blue',
                size: 2,
                path: 'fluid',
                startPlug: 'disc',
                endPlug: 'arrow',
                middleLabel: LeaderLine.pathLabel(`-${lossCable.toFixed(2)} dB`, {
                    color: 'red',
                    fontSize: '12px'
                })
            });

            const connection = {
                from: parseInt(source.id.replace('node-', '')),
                to: parseInt(target.id.replace('node-', '')),
                length,
                lossCable,
                line
            };

            line.path.addEventListener('contextmenu', function(e) {
                e.preventDefault();
                if (confirm("Hapus koneksi ini?")) {
                    line.remove();
                    const idx = lines.indexOf(line);
                    if (idx !== -1) {
                        lines.splice(idx, 1);
                        connections.splice(idx, 1);
                    }
                }
            });

            lines.push(line);
            connections.push(connection);
        }
    </script>
@endpush
