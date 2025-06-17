    let inputSign = '+'; // Default tanda

    // Fungsi untuk ubah tanda +/- tanpa sentuh input
    function setSign(sign) {
        inputSign = sign;
        document.getElementById("plusMinusBtn").textContent = sign;
        calculateLoss(); // baru hitung kalau angka udah ada
    }

    function calculateLoss() {
        const inputField = document.getElementById("inputLoss");
        const value = inputField.value;

        if (value === "") {
            // Kalau kosong, reset hasil dan status
            document.getElementById("resultLoss").textContent = "-";
            document.getElementById("statusText").textContent = "Waiting for input...";
            return;
        }

        const rawInput = parseFloat(value);
        const inputLoss = inputSign === '-' ? -rawInput : rawInput;

        const splitterLoss = parseFloat(document.getElementById("splitter").value) || 0;
        const spliceLoss = parseFloat(document.getElementById("spliceLoss").value) || 0;
        const connectorLoss = parseFloat(document.getElementById("connectorLoss").value) || 0;
        const meter = parseFloat(document.getElementById("cableLength").value) || 0;

        const cableLoss = 0.3 * (meter / 1000); // Rumus redaman kabel

        const total = inputLoss - (cableLoss + spliceLoss + connectorLoss + splitterLoss);

        // Tampilkan hasil
        document.getElementById("resultLoss").textContent = `${total.toFixed(2)} dB`;

        const statusElement = document.getElementById("statusText");

if (total >= -8.0) {
    statusElement.textContent = "Connection Too Strong 😕";
    statusElement.className = "text-warning fw-bold";
} else if (total <= -28.0) {
    statusElement.textContent = "Connection Too Low ❌";
    statusElement.className = "text-danger fw-bold";
} else if (total <= -24.0) {
    statusElement.textContent = "Connection Low 😅";
    statusElement.className = "fw-bold";
    statusElement.style.color = "#ff82bd";
} else {
    statusElement.textContent = "Connection Good 👍";
    statusElement.className = "text-success fw-bold";
}
    }

    // Kalau kamu mau input realtime, kamu bisa tambahkan ini:
    document.getElementById("inputLoss").addEventListener("input", calculateLoss);
    document.getElementById("splitter").addEventListener("change", calculateLoss);
    document.getElementById("spliceLoss").addEventListener("input", calculateLoss);
    document.getElementById("connectorLoss").addEventListener("input", calculateLoss);
    document.getElementById("cableLength").addEventListener("input", () => {
        document.getElementById("cableLengthInput").value = document.getElementById("cableLength").value;
        calculateLoss();
    });
    document.getElementById("cableLengthInput").addEventListener("input", () => {
        document.getElementById("cableLength").value = document.getElementById("cableLengthInput").value;
        calculateLoss();
    });
