@extends('fe.master')
@section('content')
    <div class="row">
        <div class="col-md-3">
            <h5>Tambah Perangkat</h5>
            <button class="btn btn-sm btn-outline-primary mb-2" onclick="addNode('OLT')">+ OLT</button>
            <button class="btn btn-sm btn-outline-secondary mb-2" onclick="addNode('Splitter')">+ Splitter</button>
            <button class="btn btn-sm btn-outline-success mb-2" onclick="addNode('ODP')">+ ODP</button>
            <button class="btn btn-sm btn-outline-dark mb-2" onclick="addNode('Client')">+ Client</button>
            <hr>
            <button class="btn btn-danger btn-sm" onclick="resetMap()">Reset</button>
            <button class="btn btn-primary btn-sm" onclick="saveTopology()">Simpan</button>
        </div>

        <div class="col-md-6 position-relative" id="map-canvas"
            style="min-height: 500px; background-color: #f9f9f9; border: 1px solid #ccc;">
            <!-- Node elements akan dimasukkan dengan JS -->
        </div>

        <div class="col-md-3">
            <h5>Informasi Loss</h5>
            <div id="info-card" class="card d-none">
                <div class="card-body">
                    <h6 class="card-title">Total Loss</h6>
                    <p class="card-text"><strong id="total-loss">-</strong> dB</p>
                    <p class="card-text">Power Terima: <strong id="power-rx">-</strong> dBm</p>
                    <p class="card-text">Jalur: <span id="jalur-text">-</span></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leader-line/1.0.7/leader-line.min.js"></script>
    <script>
        // Simulasi data
        let nodes = [],
            connections = [],
            lines = [];
        let nodeId = 0;

        function addNode(type) {
            const el = document.createElement("div");
            el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded");
            el.style.top = "50px";
            el.style.left = "50px";
            el.setAttribute("draggable", true);
            el.setAttribute("id", "node-" + nodeId);
            el.dataset.type = type;
            el.innerText = type;
            document.getElementById("map-canvas").appendChild(el);

            nodes.push({
                id: nodeId,
                type: type,
                x: 50,
                y: 50
            });
            nodeId++;
        }

        function saveTopology() {
            // Simulasikan perhitungan loss dan tampilkan
            const totalLoss = 7.5; // simulasi dulu
            const powerRx = -2.5; // contoh

            document.getElementById("total-loss").innerText = totalLoss.toFixed(2);
            document.getElementById("power-rx").innerText = powerRx.toFixed(2);
            document.getElementById("jalur-text").innerText = "OLT → Splitter → ODP → Client";
            document.getElementById("info-card").classList.remove("d-none");
        }

        function resetMap() {
            document.getElementById("map-canvas").innerHTML = "";
            document.getElementById("info-card").classList.add("d-none");
            nodes = [];
            connections = [];
            lines = [];
        }
    </script>
@endpush
