<script>
    let nodes = [],
        connections = [],
        lines = [],
        nodeId = 0,
        selectedNode = null;

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
            lines.forEach(line => line.position());
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
        el.style.top = "50px";
        el.style.left = "50px";
        el.setAttribute("id", "node-" + nodeId);
        el.dataset.type = type;
        let label = type,
            loss = 0;

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

        el.innerText = label;
        el.dataset.loss = loss;
        el.addEventListener('click', () => {
            if (!selectedNode) {
                selectedNode = el;
                el.classList.add('border-primary');
            } else if (selectedNode !== el) {
                connectNodeElements(selectedNode, el);
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            } else {
                selectedNode.classList.remove('border-primary');
                selectedNode = null;
            }
        });

        el.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (confirm('Hapus node ini?')) {
                removeNode(el);
            }
        });

        makeDraggable(el);
        document.getElementById("map-canvas").appendChild(el);
        nodes.push({
            id: nodeId,
            type,
            el,
            label,
            loss
        });
        nodeId++;
    }

    function connectNodeElements(source, target) {
        const line = new LeaderLine(source, target, {
            color: 'blue',
            size: 2
        });
        line.svg.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            if (confirm("Hapus koneksi ini?")) {
                const idx = lines.indexOf(line);
                if (idx !== -1) {
                    line.remove();
                    lines.splice(idx, 1);
                    connections.splice(idx, 1);
                }
            }
        });
        lines.push(line);
        connections.push({
            from: parseInt(source.id.replace('node-', '')),
            to: parseInt(target.id.replace('node-', '')),
            line
        });
    }

    function saveTopology() {
        const powerInput = parseFloat(document.getElementById("input-power").value);
        const cableLength = parseFloat(document.getElementById("cable-length").value);
        const cableLoss = parseFloat(document.getElementById("cable-type").value);
        const connectorCount = parseInt(document.getElementById("connectors").value);
        const spliceCount = parseInt(document.getElementById("splicing").value);

        nodes.forEach(n => n.el.querySelector('.info-loss')?.remove());

        const graph = {};
        connections.forEach(conn => {
            if (!graph[conn.from]) graph[conn.from] = [];
            graph[conn.from].push(conn.to);
        });

        const olt = nodes.find(n => n.type === 'OLT');
        if (!olt) return;

        const paths = [];

        function dfs(path, currentId, accLoss) {
            const currentNode = nodes.find(n => n.id === currentId);
            const thisLoss = parseFloat(currentNode.loss || 0);
            const newLoss = accLoss + thisLoss;
            const newPath = [...path, currentNode];
            if (currentNode.type === 'Client') {
                paths.push({
                    path: newPath,
                    totalLoss: newLoss
                });
            }
            (graph[currentId] || []).forEach(nextId => {
                dfs(newPath, nextId, newLoss);
            });
        }

        dfs([], olt.id, 0);
        if (paths.length === 0) return;

        const additionalLoss = (cableLoss * (cableLength / 1000)) + connectorCount * 0.02 + spliceCount * 0.01;
        let maxLoss = -Infinity,
            mainDisplayPath = null;

        paths.forEach(p => {
            const totalLoss = p.totalLoss + additionalLoss;
            const rx = powerInput - totalLoss;
            const clientNode = p.path[p.path.length - 1];
            const info = document.createElement("div");
            info.className = "info-loss small text-muted mt-1";
            info.innerText = `Rx: ${rx.toFixed(2)} dBm`;
            clientNode.el.appendChild(info);
            if (totalLoss > maxLoss) {
                maxLoss = totalLoss;
                mainDisplayPath = p;
            }
        });

        const rxMain = powerInput - (mainDisplayPath.totalLoss + additionalLoss);
        document.getElementById("total-loss").innerText = (mainDisplayPath.totalLoss + additionalLoss).toFixed(2);
        document.getElementById("power-rx").innerText = rxMain.toFixed(2);
        document.getElementById("jalur-text").innerText = 'OLT → ' + mainDisplayPath.path.map(n => n.label).join(" → ");
        document.getElementById("info-card").classList.remove("d-none");
    }

    function resetMap() {
        document.getElementById("map-canvas").innerHTML = "";
        document.getElementById("info-card").classList.add("d-none");
        nodes = [];
        connections = [];
        lines.forEach(line => line.remove());
        lines = [];
        selectedNode = null;
        nodeId = 0;
    }

    function loadJsonFile(event) {
        const file = event.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            const data = JSON.parse(e.target.result);
            resetMap();
            data.nodes.forEach(n => {
                const el = document.createElement("div");
                el.classList.add("position-absolute", "p-2", "bg-white", "border", "rounded", "text-center");
                el.style.top = n.position.top;
                el.style.left = n.position.left;
                el.setAttribute("id", "node-" + n.id);
                el.dataset.type = n.type;
                el.dataset.loss = n.loss;
                el.innerText = n.label;

                el.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                    if (confirm('Hapus node ini?')) {
                        removeNode(el);
                    }
                });

                el.addEventListener('click', () => {
                    if (!selectedNode) {
                        selectedNode = el;
                        el.classList.add('border-primary');
                    } else if (selectedNode !== el) {
                        connectNodeElements(selectedNode, el);
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
                    id: n.id,
                    type: n.type,
                    el,
                    label: n.label,
                    loss: n.loss
                });
                nodeId = Math.max(nodeId, n.id + 1);
            });

            setTimeout(() => {
                data.connections.forEach(conn => {
                    const fromEl = document.getElementById("node-" + conn.from);
                    const toEl = document.getElementById("node-" + conn.to);
                    if (fromEl && toEl) {
                        connectNodeElements(fromEl, toEl);
                    }
                });
            }, 200);
        };
        reader.readAsText(file);
    }

    function removeNode(el) {
        const nodeIndex = nodes.findIndex(n => n.el === el);
        if (nodeIndex === -1) return;
        const nodeId = nodes[nodeIndex].id;
        const toRemove = connections.filter(conn => conn.from === nodeId || conn.to === nodeId);
        toRemove.forEach(conn => {
            const idx = connections.findIndex(c => c.from === conn.from && c.to === conn.to);
            if (idx !== -1) {
                connections.splice(idx, 1);
                lines[idx].remove();
                lines.splice(idx, 1);
            }
        });
        el.remove();
        nodes.splice(nodeIndex, 1);
    }
</script>